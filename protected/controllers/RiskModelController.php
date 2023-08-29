<?php

class RiskModelController extends Controller
{
	/**
     * @return array action filters
     */
	public function filters()
	{
		return array(
			'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetRisk',
                        'apiGetRiskSaveScore'
                    )
                )
            )
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
                    'risk',
                    'riskQuery',
                    'riskQueryData',
                    'riskReport',
                    'geocode',
                    'testApi'
                ),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array(
                    'apiGetRisk',
                    'apiGetRiskSaveScore'
                ),
				'users'=>array('*'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
            )
        );
    }

	/**
	 * Action runs the WDS Risk Model query
	 */
	public function actionRisk()
	{
        $reportForm = new RiskReportForm;

		$this->render('risk', array(
            'reportForm' => $reportForm
        ));
	}

	/**
     * Returns an array of Risk Model classes
     * @param float $lat coordinte in the WGS84 Coordinate System
     * @param float $lon coordinte in the WGS84 Coordinate System
     * @param int $type query return type
     *      1 -> Score Only     (RiskModel::RISK_QUERY_TABULAR)
     *      2 -> Map Only       (RiskModel::RISK_QUERY_MAP)
     *      3 -> Score and Map  (RiskModel::RISK_QUERY_BOTH)
     */
    public function actionRiskQuery($lat, $lon, $type)
    {
        $model = new RiskModel();

        $results = $model->executeRiskModel($lat, $lon, $type);

        echo json_encode($results);
    }

    /**
     * Display raw tabular risk data and export a csv of that data.
     * @param integer $export
     */
    public function actionRiskQueryData($export = null)
    {
        $model = new RiskDataForm();

        $tabular_data = null;
        $tabular_data_clusters = null;

		if (isset($_POST['ajax']) && $_POST['ajax'] === 'risk-query-data-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST['RiskDataForm']))
		{
			$model->attributes = $_POST['RiskDataForm'];

            if ($model->validate())
            {
                $riskModel = new RiskModel();
                $results = $riskModel->executeRiskModel($model->lat, $model->lon, RiskModel::RISK_QUERY_TABULAR, true);
                list($tabular_data, $tabular_data_clusters) = $results;

                if (!is_null($export))
                {
                    $model->exportCSV($tabular_data, $tabular_data_clusters);
                }
            }
		}

		$this->render('risk_data',array(
			'model' => $model,
            'tabular_data' => $tabular_data,
            'tabular_data_clusters' => $tabular_data_clusters
		));
    }

    /**
     * Renders risk report
     */
    public function actionRiskReport()
    {
        if (isset($_POST['RiskReportForm']))
        {
            $reportForm = new RiskReportForm;
            $reportForm->attributes = $_POST['RiskReportForm'];

            $stateMeanModel = RiskStateMeans::loadModelByLatLong($reportForm->lat, $reportForm->lon);

            if (!$stateMeanModel)
            {
                die('There is no recorded state mean for this region yet!');
            }

            $this->renderPartial('risk_report', array(
                'stateMeanModel' => $stateMeanModel,
                'reportForm' => $reportForm
            ), false, true);
        }
    }

    /**
     * Returns a geocode result as json.  Intended for asynchronous use.
     * @param string $address
     */
    public function actionGeocode($address)
    {
        echo CJSON::encode(Geocode::getLocation($address));
    }

    /**
     * Perform a test of the outwards facing risk api
     */
    public function actionTestApi()
    {
		if (isset($_POST['RiskTest']) && Yii::app()->request->isAjaxRequest)
		{
            $response = RiskApiTest::executeTest();

            echo json_encode(array(
                'success' => true,
                'data' => htmlspecialchars($response, ENT_QUOTES)
            ));

            Yii::app()->end();
		}

        $this->render('risk_api_test');
    }

    /**
     * API Method: riskModel/apiGetRisk
     * Description: Gets WDS Risk score data for given lat/lon and type
     *
     * Post data parameters:
     * @param float $lat coordinate in the WGS84 Coordinate System
     * @param float $lon coordinate in the WGS84 Coordinate System
     * @param int $type query return type
     *      1 -> Score Only     (RiskModel::RISK_QUERY_TABULAR)
     *      2 -> Map Only       (RiskModel::RISK_QUERY_MAP)
     *      3 -> Score and Map  (RiskModel::RISK_QUERY_BOTH)
     * @return array with the following structure
     * {
     *      score_v: v_score,
     *      score_whp: whp_score,
     *      score_wds: wds_score,
     *      map: geojson_feature_collection
     * }
     *
     * Post data example:
     * {
     *      "data": {
     *          "lat": 41.990,
     *          "lon": -124.198,
     *          "type": 3
     *      }
     * }
     */
    public function actionApiGetRisk()
	{
        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('lat','lon','type')))
            return;

        $model = new RiskModel();

        try
        {
            $results = $model->executeRiskModel($data['lat'], $data['lon'], $data['type']);
        }
        catch (Exception $e)
        {
            return WDSAPI::echoJsonError('ERROR: Something went wrong!' . PHP_EOL .  $e->getMessage());
        }

        $returnArray = array(
            'data' => $results
        );

        $returnArray['error'] = ($results['score_v'] === -1 && $results['score_whp'] === -1 && $results['score_wds'] === -1) ? 1 : 0;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: riskModel/apiGetRiskSaveScore
     * Description: Gets WDS Risk score data for given lat/lon and type.
     * Also logs a risk score entry.
     *
     * Post data parameters:
     * @param float $lat coordinate in the WGS84 Coordinate System
     * @param float $lon coordinate in the WGS84 Coordinate System
     * @param integer $type query return type
     *      1 -> Score Only     (RiskModel::RISK_QUERY_TABULAR)
     *      2 -> Map Only       (RiskModel::RISK_QUERY_MAP)
     *      3 -> Score and Map  (RiskModel::RISK_QUERY_BOTH)
     * @param string $address
     * @param string $city
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param integer $client_id
     * @param string $match_type
     * @param string $match_score
     * @param string $match_address
     * @param integer $geocoded
     * @param integer $user_id
     *
     * @return array with the following structure
     * {
     *      data: {
     *          score_v: v_score,
     *          score_whp: whp_score,
     *          score_wds: wds_score,
     *          map: geojson_feature_collection,
     *          error: 0
     *      },
     *      error: 0
     * }
     *
     * Post data example:
     * {
     *      "data": {
     *          "lat": "40.865968"
     *          "lon": "-124.087959",
     *          "type": "3",
     *          "address": "514 H St",.
     *          "city": "Arcata",
     *          "state": "CA",
     *          "zip": "95521",
     *          "client_id": "1",
     *          "match_type": "address",
     *          "match_score": "0.990",
     *          "match_address": "514 H St, Arcata, California 95521, United States",
     *          "geocoded": "1",
     *          "user_id": "56"
     *      }
     * }
     */
    public function actionApiGetRiskSaveScore()
	{
        $data = null;

        $postVars = array(
            'lat',
            'lon',
            'type',
            'address',
            'city',
            'state',
            'zip',
            'client_id',
            'match_type',
            'match_score',
            'match_address',
            'geocoded',
            'user_id'
        );

		if (!WDSAPI::getInputDataArray($data, $postVars))
            return;

        $stateAbbr = Helper::convertStateToAbbr(strtoupper($data['state']));

        $data['state'] = $stateAbbr ? $stateAbbr : $data['state'];

        $model = new RiskModel($data['state']);

        try
        {
            $results = $model->executeRiskModel($data['lat'], $data['lon'], $data['type']);
        }
        catch (Exception $e)
        {
            return WDSAPI::echoJsonError('ERROR: Something went wrong!' . PHP_EOL .  $e->getMessage());
        }

        $returnArray = array(
            'data' => $results
        );

        $returnArray['error'] = ($results['score_v'] === -1 && $results['score_whp'] === -1 && $results['score_wds'] === -1) ? 1 : 0;

        $riskScore = new RiskScore();
        $riskScore->address = $data['address'];
        $riskScore->city = $data['city'];
        $riskScore->state = $data['state'];
        $riskScore->zip = $data['zip'];
        $riskScore->lat = round($data['lat'], 5);
        $riskScore->long = round($data['lon'], 5);
        $riskScore->score_v = number_format(round($results['score_v'], 8), 8);
        $riskScore->score_whp = number_format(round($results['score_whp'], 8), 8);
        $riskScore->score_wds = number_format(round($results['score_wds'], 8), 8);
        $riskScore->score_type = RiskScoreType::TYPE_WEB;
        $riskScore->client_id = $data['client_id'];
        $riskScore->geojson = json_encode($results['map']);
        $riskScore->geocoded = $data['geocoded'];
        $riskScore->match_type = $data['match_type'];
        $riskScore->match_score = $data['match_score'];
        $riskScore->match_address = $data['match_address'];
        $riskScore->date_created = date('Y-m-d H:i');
        $riskScore->user_id = $data['user_id'];

        // User did not use geocode, populate these from geog tables
        if (empty($data['city']) || empty($data['state']))
        {
            $command = Yii::app()->db->createCommand();
            $latLonArray = array(':lat' => $data['lat'], ':lon' => $data['lon']);
            $riskScore->state = $command->setText('SELECT abbr FROM geog_states WHERE geography::Point(:lat, :lon, 4326).STIntersects(geog) = 1')->queryScalar($latLonArray);
            $riskScore->zip = $command->setText('SELECT zipcode FROM geog_zipcodes WHERE geography::Point(:lat, :lon, 4326).STIntersects(geog) = 1')->queryScalar($latLonArray);
        }

        try
        {
            if (!$riskScore->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $riskScore->getErrors());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }
}
