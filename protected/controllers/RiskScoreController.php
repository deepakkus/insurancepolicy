<?php

class RiskScoreController extends Controller
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
                        'apiGetBatchesCount',
                        'apiGetBatches',
                        'apiGetRiskScoresCount',
                        'apiGetRiskScores',
                        'apiGetRiskReportComponents',
                        'apiGetRiskReportComponentsFromBulk',
                        'apiGetBatchScoreAnalytics',
                        'apiGetBatchStateAnalytics'
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
                    'riskScores'
                ),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetBatchesCount',
                    'apiGetBatches',
                    'apiGetRiskScoresCount',
                    'apiGetRiskScores',
                    'apiGetRiskReportComponents',
                    'apiGetRiskReportComponentsFromBulk',
                    'apiGetBatchScoreAnalytics',
                    'apiGetBatchStateAnalytics'
                ),
                'users' => array('*')
            ),
            array('deny',
                'users' => array('*'),
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

    public function actionRiskScores($export = false)
    {
        $riskScore = new RiskScore('search');
        $riskScore->unsetAttributes();
        if (isset($_GET['RiskScore']))
            $riskScore->attributes = $_GET['RiskScore'];

        $columnsToShow = array(
            'score_wds',
            'address',
            'city',
            'state',
            'scoreType',
            'userName',
            'clientName',
            'geocoded',
            'wds_geocode_level',
            'date_created'
        );

        if (isset($_GET['columnsToShow']))
        {
            $_SESSION['wds_risk_score_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        else if (isset($_SESSION['wds_risk_score_columnsToShow']))
        {
            $columnsToShow = $_SESSION['wds_risk_score_columnsToShow'];
        }

        if ($export)
            $riskScore->exportCSV($columnsToShow);

        $this->render('risk_scores',array(
            'riskScore' => $riskScore,
            'columnsToShow' => $columnsToShow
        ));
    }

    //-------------------------------------------------------------------API Calls----------------------------------------------------------------

    /**
     * API Method: riskScore/apiGetBatchesCount
     * Description: Gets total count for all the risk batches that have been run for a particular client.
     *
     * Post data parameters:
     * @param integer client_id
     * @param string date_start - (optional) batches that fall after this given date
     * @param string date_end - (optional) batches that fall before the given date
     *
     * Post data example:
     * {
     *     "data": {
     *         "client_id": 1,
     *         "date_start": "2016-01-01",
     *         "date_end": "2016-03-01"
     *     }
     * }
     */
    public function actionApiGetBatchesCount()
    {
        $data = NULL;
        $returnArray = array();
        $count = 0;

        if (!WDSAPI::getInputDataArray($data, array('client_id')))
            return;

        $dateStart = isset($data['date_start']) && !empty($data['date_start']) ? $data['date_start'] : null;
        $dateEnd = isset($data['date_end']) && !empty($data['date_end']) ? $data['date_end'] : null;

        // Optional - complete batches with date filter start and end dates
        if ($dateStart && $dateEnd)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition("status = 'complete'");
            $criteria->addCondition('date_created >= :date_start');
            $criteria->addCondition('date_created < :date_end');
            $criteria->params = array(':client_id' => $data['client_id'], ':date_start' => $dateStart, ':date_end' => $dateEnd);
            $count = RiskBatchFile::model()->count($criteria);
        }
        else
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition("status = 'complete'");
            $criteria->params = array(':client_id' => $data['client_id']);
            $count = RiskBatchFile::model()->count($criteria);
        }

        $returnArray['data'] = $count;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: riskScore/apiGetBatches
     * Description: Gets all the risk batches that have been run for a particular client.
     *
     * Post data parameters:
     * @param integer client_id - client to filter the results by
     * @param string date_start - (optional) batches that fall after this given date
     * @param string date_end - (optional) batches that fall before the given date
     *
     * Post data example:
     * {
     *     "data": {[
     *          {
     *              "id": 20,
     *              "date_run": '2015-01-12 19:23',
     *              "batch_id": 1,
     *              "states": ["CA", "WA"],
     *              "number_run": 2389
     *          }, {
     *              "id": 24,
     *              "date_run": '2015-01-13 19:23',
     *              "batch_id": 2,
     *              "states": ["NM"],
     *              "number_run": 1234
     *          }
     *      ]}
     * }
    */
    public function actionApiGetBatches()
    {
        $data = NULL;
        $returnData = array();
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('client_id')))
            return;

        $dateStart = isset($data['date_start']) && !empty($data['date_start']) ? $data['date_start'] : null;
        $dateEnd = isset($data['date_end']) && !empty($data['date_end']) ? $data['date_end'] : null;

        // Optional - complete batches with date filter start and end dates
        if ($dateStart && $dateEnd)
        {
            $criteria = new CDbCriteria;
            $criteria->select = array('id', 'date_run', 'batch_id', 'version_id');
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition("status = 'complete'");
            $criteria->addCondition('date_created >= :date_start');
            $criteria->addCondition('date_created < :date_end');
            $criteria->order = 'id DESC';
            $criteria->params = array(':client_id' => $data['client_id'], ':date_start' => $dateStart, ':date_end' => $dateEnd);
            $models = RiskBatchFile::model()->findAll($criteria);
        }
        else
        {
            // Get the complete batches with no date filter
            $criteria = new CDbCriteria;
            $criteria->select = array('id', 'date_run', 'batch_id', 'version_id');
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition("status = 'complete'");
            $criteria->order = 'id DESC';
            $criteria->params = array(':client_id' => $data['client_id']);
            $models = RiskBatchFile::model()->findAll($criteria);
        }

        // For each batch get some stats
        foreach ($models as $model)
        {
            // Number of entries processed
            $numRun = RiskScore::model()->countByAttributes(array('batch_file_id' => $model->id));
            // Distinct states the batch was run for
            $stateModels = RiskScore::model()->findAll(array(
                'select' => 't.state',
                'group' => 't.state',
                'distinct' => true,
                'condition' => 't.batch_file_id = ' . $model->id
            ));

            $states = array_map(function($stateModel) { return $stateModel->state; }, $stateModels);

            $returnData[] = array(
                'id' => $model->id,
                'date_run' => $model->date_run,
                'batch_id' => $model->batch_id,
                'states' => $states,
                'number_run' => $numRun,
                'version' => $model->riskVersion->version
            );
        }

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: riskScore/apiGetRiskScoresCount
     *
     * Description: Gets total count for all the risk scores for given risk queries.
     *
     * This query accepts limit, offset, and a compareArray for the purpose of sending data
     * through the API to a yii grid widget.
     *
     * Post data parameters:
     * @param integer client_id - client to filter the results by
     * @param integer type - the type of query (1 = web query, 2 = batch)
     * @param string date_start - (optional) entries that fall after this given date
     * @param string date_end - (optional) entries that fall before the given date
     * @param integer batch_file_id - (optional) the id of the batch
     * @param integer limit - (optional) used for limiting number of results
     * @param integer offset - (optional) used for pagination
     * @param array compareArray - (optional) associative array of column => text for searching
     *
     * Post data example:
     * {
     *      "data": {
     *          "client_id": 1,
     *          "type": 1,
     *          "date_start": "",
     *          "date_end": "",
     *          "batch_file_id": 1,
     *          "limit": 20,
     *          "offset": 60,
     *          "compareArray": {
     *              "column1": "text",
     *              "column2": "text"
     *          }
     *      }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "1050"
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetRiskScoresCount()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('client_id', 'type')))
            return;

        $dateStart = isset($data['date_start']) && !empty($data['date_start']) ? $data['date_start'] : null;
        $dateEnd = isset($data['date_end']) && !empty($data['date_end']) ? date('Y-m-d', strtotime($data['date_end'] . ' +1 day')) : null;
        $compareArray = isset($data['compare_array']) ? $data['compare_array'] : array();

        if (($data['type'] == 2) && isset($data['batch_file_id']))
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('batch_file_id = :batch_file_id');
            $criteria->params = array(':client_id' => $data['client_id'], ':batch_file_id' => $data['batch_file_id']);
            foreach ($compareArray as $key => $value)
                $criteria->addSearchCondition($key, $value);
            $count = RiskScore::model()->count($criteria);
        }
        //Select web query results with date range
        else if ($data['type'] != 2 && $dateStart && $dateEnd)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('score_type = :score_type');
            $criteria->addCondition('date_created >= :date_start');
            $criteria->addCondition('date_created < :date_end');
            $criteria->params = array(':client_id' => $data['client_id'], ':score_type' => $data['type'],  ':date_start' => $dateStart, ':date_end' => $dateEnd);
            foreach ($compareArray as $key => $value)
                $criteria->addSearchCondition($key, $value);
            $count = RiskScore::model()->count($criteria);
        }
        //Select web query results without date range
        else if ($data['type'] != 2)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('score_type = :score_type');
            $criteria->params = array(':client_id' => $data['client_id'], ':score_type' => $data['type']);
            foreach ($compareArray as $key => $value)
                $criteria->addSearchCondition($key, $value);
            $count = RiskScore::model()->count($criteria);
        }
        //Appropriate params were not supplied
        else
        {
            return WDSAPI::echoResultsAsJson(array(
                'error' => 1,
                'errorMessage' => 'The proper combination of parameters were not supplied. If the type is bulk, a batch id needs to exist.',
                'errorFriendlyMessage' => 'Something went wrong. If the problem persists, please contact technical support'
            ));
        }

        $returnArray['data'] = $count;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: riskScore/apiGetRiskScores
     * Description: Gets all the risk scores for given risk queries.
     *
     * This query accepts limit, offset, and a compareArray for the purpose of sending data
     * through the API to a yii grid widget.
     *
     * Post data parameters:
     * @param integer client_id - client to filter the results by
     * @param integer type - the type of query (1=web query, 2=batch)
     * @param string date_start - (optional) entries that fall after this given date
     * @param string date_end - (optional) entries that fall before the given date
     * @param integer batch_file_id - (optional) the id of the batch
     * @param integer limit - (optional) used for limiting number of results
     * @param integer offset - (optional) used for pagination
     * @param array compareArray - (optional) associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "client_id": 1,
     *         "type": 1,
     *         "date_start": "",
     *         "date_end": "",
     *         "batch_file_id": 1,
     *         "limit": 20,
     *         "offset": 60,
     *         "compareArray": {
     *             "column1": "text",
     *             "column2": "text"
     *         }
     *     }
     * }
     *
     * @return array
     * {
     *     "data": {
     *      [
     *          {
     *              "id": 1,
     *              "first_name":"John",
     *              "last_name":"Smith",
     *              "address" : "123 something",
     *              "city" : "Denver",
     *              "state" : "CO",
     *              "client_property_number" : "H320934",
     *              "client_member_number" : "A234998",
     *              "score_wds" : 0.00914,
     *              "wds_geocode_level":"address",
     *              "date_created": "2016-01-02 13:23:23",
     *              "user_id": "381",
     *              "user_name": "wdsrisk"
     *          },
     *          {
     *              "id": 2,
     *              "first_name":"Craig",
     *              "last_name":"Smith",
     *              "address" : "123 something else",
     *              "city" : "Colorado Springs",
     *              "state" : "CO",
     *              "client_property_number" : "H320933",
     *              "client_member_number" : "A234966",
     *              "score_wds" : -1,
     *              "wds_geocode_level":"unmatched",
     *              "date_created": "2016-01-02 13:23:24",
     *              "user_id": "381",
     *              "user_name": "wdsrisk"
     *          }
     *     ],
     *     "error": 0
     * }
     */
    public function actionApiGetRiskScores()
    {
        $data = NULL;
        $returnData = array();
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('client_id', 'type')))
            return;

        $offset = (isset($data['offset']) && !empty($data['offset'])) ? $data['offset'] : 0;
        $limit = (isset($data['limit']) && !empty($data['limit'])) ? $data['limit'] : 25;
        $dateStart = isset($data['date_start']) && !empty($data['date_start']) ? $data['date_start'] : null;
        $dateEnd = isset($data['date_end']) && !empty($data['date_end']) ? date('Y-m-d', strtotime($data['date_end'] . ' +1 day')) : null;
        $compareArray = isset($data['compare_array']) ? $data['compare_array'] : array();
        $sortArray = isset($data['sort_array']) ? $data['sort_array'] : array();

        $sortCriteria = array();

        $sortDirection = function($sort)
        {
            return ($sort == SORT_ASC) ? 'ASC' : 'DESC';
        };

        // Select a given batch (bulk) run
        if (($data['type'] == 2) && isset($data['batch_file_id']))
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('batch_file_id = :batch_file_id');
            $criteria->limit = $limit;
            $criteria->offset = $offset;
            $criteria->order = 'id DESC';
            $criteria->params = array(':client_id' => $data['client_id'], ':batch_file_id' => $data['batch_file_id']);
            foreach ($compareArray as $key => $value)
            {
                $criteria->addSearchCondition($key, $value);
            }
            foreach ($sortArray as $key => $sort)
            {
                $sortCriteria[] = $key . ' ' . $sortDirection($sort);
            }
            $criteria->order = implode(',', $sortCriteria);
            $models = RiskScore::model()->findAll($criteria);
        }
        // Select web/api query results with date range
        elseif ($data['type'] != 2 && $dateStart && $dateEnd)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('score_type = :score_type');
            $criteria->addCondition('date_created >= :date_start');
            $criteria->addCondition('date_created < :date_end');
            $criteria->limit = $limit;
            $criteria->offset = $offset;
            $criteria->order = 'id DESC';
            $criteria->params = array(':client_id' => $data['client_id'], ':score_type' => $data['type'], ':date_start' => $dateStart, ':date_end' => $dateEnd);
            foreach ($compareArray as $key => $value)
                $criteria->addSearchCondition($key, $value);
            foreach ($sortArray as $key => $sort)
                $sortCriteria[] = $key . ' ' . $sortDirection($sort);
            $criteria->order = implode(',', $sortCriteria);
            $models = RiskScore::model()->findAll($criteria);
        }
        // Select web query results without date range
        elseif ($data['type'] != 2)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('score_type = :score_type');
            $criteria->limit = $limit;
            $criteria->offset = $offset;
            $criteria->order = 'id DESC';
            $criteria->params = array(':client_id' => $data['client_id'], ':score_type' => $data['type']);
            foreach ($compareArray as $key => $value)
                $criteria->addSearchCondition($key, $value);
            foreach ($sortArray as $key => $sort)
                $sortCriteria[] = $key . ' ' . $sortDirection($sort);
            $criteria->order = implode(',', $sortCriteria);
            $models = RiskScore::model()->findAll($criteria);
        }
        // Appropriate params were not supplied
        else
        {
            return WDSAPI::echoResultsAsJson(array(
                'error' => 1,
                'errorMessage' => 'The proper combination of parameters were not supplied. If the type is bulk, a batch id needs to exist.',
                'errorFriendlyMessage' => 'Something went wrong. If the problem persists, please contact technical support'
            ));
        }

        // For each batch get some stats
        foreach ($models as $model)
        {
            $returnData[] = array(
                'id' => $model->id,
                'first_name' => $model->first_name,
                'last_name' => $model->last_name,
                'address' => $model->address,
                'city' => $model->city,
                'state' => $model->state,
                'match_address' => $model->match_address,
                'client_property_number' => (isset($model->property_pid) && isset($model->property)) ? $model->property->policy : $model->client_property_id,
                'client_member_number' => (isset($model->property_pid) && isset($model->property->member)) ? $model->property->member->member_num : $model->client_member_id,
                'score_wds' => $model->score_wds,
                'wds_geocode_level' => $model->wds_geocode_level,
                'date_created' => $model->date_created,
                'user_id' => $model->user_id,
                'user_name' => $model->userName,
                'version' => $model->version
            );
        }

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: riskScore/apiGetRiskReportComponents
     * Description: Gets all the components necessary to create a WDSRisk report.
     *
     * Post data parameters:
     * @param integer id - risk_score table id
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 1819
     *     }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "risk_v": "0.42984948",
     *         "risk_whp": "0.00207872",
     *         "risk_wds": "0.00089354",
     *         "geojson": "<geojson escaped feature collection string here>",
     *         "match_address": "awesome street address",
     *         "lat": "40.5138",
     *         "lon": "-121.37695",
     *         "geocoded": "0",
     *         "date_created": "02/17/2016"
     *     },
     *     error: 0
     * }
     */
    public function actionApiGetRiskReportComponents()
    {
        $data = NULL;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        $riskScore = RiskScore::model()->findByPk($data['id']);

        if (!$riskScore)
        {
            return WDSAPI::echoJsonError('No risk score entry was found for this id: ' . $data['id'], 'Something went wrong. If the problem persists, please contact technical support.');
        }

        $returnData = array(
            'risk_v' => $riskScore->score_v,
            'risk_whp' => $riskScore->score_whp,
            'risk_wds' => $riskScore->score_wds,
            'geojson' => $riskScore->geojson,
            'match_address' => $riskScore->match_address,
            'lat' => $riskScore->lat,
            'lon' => $riskScore->long,
            'geocoded' => $riskScore->geocoded,
            'date_created' => date('m/d/Y', strtotime($riskScore->date_created))
        );

        if (empty($riskScore->geojson))
        {
            $model = new RiskModel();
            $results = $model->executeRiskModel($riskScore->lat, $riskScore->long, RiskModel::RISK_QUERY_MAP);
            $riskScore->geojson = json_encode($results['map']);

            try
            {
                if (!$riskScore->save())
                    return WDSAPI::echoJsonError('ERROR: There was a database error.', $riskScore->getErrors());
            }
            catch (CDbException $e)
            {
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
            }

            $returnData['geojson'] = $riskScore->geojson;
        }

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: riskScore/apiGetRiskReportComponentsFromBulk
     * Description: Gets all the components necessary to create a WDSRisk report from a bulk entry.
     * A new risk_score "web" type table entry will be made.
     *
     * Post data parameters:
     * @param integer id - risk_score table id
     * @param integer user_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 1819,
     *         "user_id": "381"
     *     }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "risk_v": "0.42984948",
     *         "risk_whp": "0.00207872",
     *         "risk_wds": "0.00089354",
     *         "geojson": "<geojson escaped feature collection string here>",
     *         "match_address": "awesome street address",
     *         "lat": "40.5138",
     *         "lon": "-121.37695",
     *         "geocoded": "0",
     *         "date_created": "02/17/2016"
     *     },
     *     error: 0
     * }
     */
    public function actionApiGetRiskReportComponentsFromBulk()
    {
        $data = NULL;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('id','user_id')))
            return;

        $riskScore = RiskScore::model()->findByPk($data['id']);

        if (!$riskScore)
        {
            return WDSAPI::echoJsonError('No risk score entry was found for this id: ' . $data['id'], 'Something went wrong. If the problem persists, please contact technical support.');
        }

        $model = new RiskModel();
        $results = $model->executeRiskModel($riskScore->lat, $riskScore->long, RiskModel::RISK_QUERY_BOTH);

        $newRiskScore = new RiskScore();
        $newRiskScore->attributes = $riskScore->attributes;
        $newRiskScore->id = null;
        $newRiskScore->score_v = number_format(round($results['score_v'], 8), 8);
        $newRiskScore->score_whp = number_format(round($results['score_whp'], 8), 8);
        $newRiskScore->score_wds = number_format(round($results['score_wds'], 8), 8);
        $newRiskScore->score_type = RiskScoreType::TYPE_WEB;
        $newRiskScore->batch_file_id = null;
        $newRiskScore->geojson = json_encode($results['map']);
        $newRiskScore->geocoded = null;
        $newRiskScore->date_created = date('Y-m-d H:i:s');
        $newRiskScore->user_id = $data['user_id'];

        try
        {
            if (!$newRiskScore->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $riskScore->getErrors());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        $returnData = array(
            'risk_v' => $newRiskScore->score_v,
            'risk_whp' => $newRiskScore->score_whp,
            'risk_wds' => $newRiskScore->score_wds,
            'geojson' => $newRiskScore->geojson,
            'match_address' => $newRiskScore->match_address,
            'lat' => $newRiskScore->lat,
            'lon' => $newRiskScore->long,
            'geocoded' => $riskScore->geocoded,
            'date_created' => date('m/d/Y', strtotime($riskScore->date_created))
        );

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }


    /**
     * API method: riskScore/apiGetBatchScoreAnalytics
     * Description: Gets all the risk scores for given risk queries.
     *
     *
     * Post data parameters:
     * @param integer client_id - client to filter the results by
     * @param integer batch_id - the batch to select
     *
     * Post data example:
     * {
     *     "data": {
     *         "client_id": 1,
     *         "batch_id": 1
     *      }
     * }
     *
    *  @return array
     * {
     *     "data": {
     *         "std_dev_score": "1",
     *         "num": "200",
     *         "std_dev_text": "Low concern",
     *     },
     *     {
     *         "std_dev_score": "2",
     *         "num": "440",
     *         "std_dev_text": "Moderate concern",
     *     }
     *     error: 0
     * }
     */
    public function actionApiGetBatchScoreAnalytics()
    {
        $data = NULL;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('client_id','batch_id')))
            return;

        $returnData = RiskScore::getRiskBatchScoreAnalytics($data['batch_id'], $data['client_id']);

        if ($returnData)
        {
            $returnArray = array(
                'data' => $returnData,
                'error' => 0
            );
        }
        else
        {
            $returnArray = array(
                'data' => null,
                'error' => 1,
                'errorMessage' => 'No analytics availible for the requested risk batch',
                'errorFriendlyMessage' => 'No analytics availible for the requested risk batch'
            );
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: riskScore/apiGetBatchStateAnalytics
     * Description: Gets the state distribution for a given risk batch.
     *
     * Post data parameters:
     * @param integer client_id - client to filter the results by
     * @param integer batch_id - the batch to select
     *
     * Post data example:
     * {
     *     "data": {
     *         "client_id": 1,
     *         "batch_id": 1
     *      }
     * }
     *
     *  @return array
     * {
     *     "data": {
     *         "state": "CA",
     *         "num": "200"
     *     },
     *     {
     *         "state": "CO",
     *         "num": "440"
     *     }
     *     error: 0
     * }
     */
    public function actionApiGetBatchStateAnalytics()
    {
        $data = NULL;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('client_id','batch_id')))
            return;

        $returnData = RiskScore::getRiskBatchStateAnalytics($data['batch_id'], $data['client_id']);

        if ($returnData)
        {
            $returnArray = array(
                'data' => $returnData,
                'error' => 0
            );
        }
        else
        {
            $returnArray = array(
                'data' => null,
                'error' => 1,
                'errorMessage' => 'No analytics availible for the requested risk batch',
                'errorFriendlyMessage' => 'No analytics availible for the requested risk batch'
            );
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}