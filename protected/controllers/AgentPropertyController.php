<?php

class AgentPropertyController extends Controller
{
    const AGENT_PROPERTY = 'AgentProperty';
    const AGENT_PROPERTY_COLUMNS_TO_SHOW = 'wds_agent_property_columnsToShow';
    const AGENT_PROPERTY_PAGE_SIZE = 'wds_agent_property_pageSize';
    const AGENT_PROPERTY_SORT = 'wds_agent_property_sort';

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
                    WDSAPI::SCOPE_FIRESHIELD => array(
                        'apiGetUnprocessedGeoRisk',
                        'apiUpdateGeoRisks'
                    )
                )
            )
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
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
                    'admin',
                    'update',
                    'create'
                ),
				'users' => array('@'),
			),
			array('allow',
				'actions' => array(
                    'apiGetUnprocessedGeoRisk',
                    'apiUpdateGeoRisks'
                ),
				'users' => array('*')),
			array('deny',
				'users' => array('*'),
			),
		);
	}

    /**
	 * The main agents grid.
	 */
	public function actionAdmin()
	{
        $agentProperties = new AgentProperty('search');
        $agentProperties->unsetAttributes();

        if (filter_has_var(INPUT_GET, self::AGENT_PROPERTY))
        {
            $agentProperties->attributes = filter_input(INPUT_GET, self::AGENT_PROPERTY, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }

        if (filter_has_var(INPUT_GET, 'columnsToShow'))
        {
            $columnsToShow = filter_input(INPUT_GET, 'columnsToShow', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            $_SESSION[self::AGENT_PROPERTY_COLUMNS_TO_SHOW] = $columnsToShow;
        }
        elseif (isset($_SESSION[self::AGENT_PROPERTY_COLUMNS_TO_SHOW]))
        {
            $columnsToShow = $_SESSION[self::AGENT_PROPERTY_COLUMNS_TO_SHOW];
        }
        else
        {
            // Default columns to show.
            $columnsToShow = array(
                10=>'id',
                20=>'agent_id',
                30=>'agent_first_name',
                40=>'agent_last_name',
                50=>'address_line_1',
                60=>'city',
                70=>'state',
                80=>'zip',
                90=>'geo_risk',
            );
        }

        if (filter_has_var(INPUT_GET, 'pageSize'))
        {
            $pageSize = filter_input(INPUT_GET, 'pageSize');
            $_SESSION[self::AGENT_PROPERTY_PAGE_SIZE] = $pageSize;
        }
        elseif (isset($_SESSION[self::AGENT_PROPERTY_PAGE_SIZE]))
        {
            $pageSize = $_SESSION[self::AGENT_PROPERTY_PAGE_SIZE];
        }
        else
        {
            $pageSize = 25;
        }

        if (filter_has_var(INPUT_GET, 'AgentProperty_sort'))
        {
            $sort = filter_input(INPUT_GET, 'AgentProperty_sort');
            $_SESSION[self::AGENT_PROPERTY_SORT] = $sort;
        }
        elseif (isset($_SESSION[self::AGENT_PROPERTY_SORT]))
        {
            $sort = $_SESSION[self::AGENT_PROPERTY_SORT];
        }
        else
        {
            $sort = 'id';
        }

        $this->render('admin',array(
            'agentProperties' => $agentProperties,
            'columnsToShow' => $columnsToShow,
            'pageSize' => $pageSize,
            'sort' => $sort,
        ));
	}

    /**
	 * Creates a new Agent Property.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($agentID)
	{
		$agentProperty = new AgentProperty;
		$agent = Agent::model()->findByPk($agentID);

        if (filter_has_var(INPUT_POST, self::AGENT_PROPERTY))
        {
            $agentProperty->attributes = filter_input(INPUT_POST, self::AGENT_PROPERTY, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            if ($agentProperty->save())
            {
				Yii::app()->user->setFlash('success', "Agent property ".$agentProperty->id." was created successfully!");
				$this->redirect(array('admin'));
            }
        }

		$agentProperty->agent_id = $agent->id;
        $agentProperty->agent_first_name = $agent->first_name;
        $agentProperty->agent_last_name = $agent->last_name;
        $agentProperty->agent_num = $agent->agent_num;

        $this->render('create',array(
			'agentProperty' => $agentProperty,
		));
	}

    /**
	 * Updates an Agent Property model.
	 * @param integer $id ID of the Agent Property to be updated
	 */
	public function actionUpdate($id)
	{
		$agentProperty = $this->loadModel($id);

        if (filter_has_var(INPUT_POST, self::AGENT_PROPERTY))
        {
            $agentProperty->attributes = filter_input(INPUT_POST, self::AGENT_PROPERTY, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            if ($agentProperty->save())
            {
				Yii::app()->user->setFlash('success', "Agent property $id was updated successfully!");
				$this->redirect(array('admin'));
            }
        }

		$this->render('update',array(
			'agentProperty' => $agentProperty,
		));
	}

    /**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	private function loadModel($id)
	{
		$model = AgentProperty::model()->findByPk($id);

        if ($model === null)
			throw new CHttpException(404,'The requested page does not exist.');

		return $model;
	}

    /**
     * API Method: Gets unprocessed agent properties for GeoHazard processing.
     */
    public function actionApiGetUnprocessedGeoRisk()
	{
        $returnArray['error'] = 0; // success
        $returnArray['data'] = array(
            'agentProperties' => $this->getUnprocessedGeoRisk(),
        );

        WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
     * API Method: Updates the GeoRisk values for the given agent properties.
     * Input data JSON should be in the following format:
     * {"data": {"agentProperties": [{"id": 123, "geoRisk": 1}, ...]}}
     */
    public function actionApiUpdateGeoRisks()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data))
            return;

        $dataProperties = $data['agentProperties'];

        if (!isset($dataProperties) || empty($dataProperties))
            return WDSAPI::echoJsonError ("ERROR: no agent properties were found!");

        foreach ($dataProperties as $dataProperty)
        {
            $agentPropertyID = $dataProperty['id'];
            $geoRisk = $dataProperty['geoRisk'];

            // Safety checks: save -9999 (unmatched) as NULL.
            if ($geoRisk < 0 || empty($geoRisk))
                $geoRisk = NULL;

            $agentProperty = AgentProperty::model()->findByPk($agentPropertyID);

            if (!isset($agentProperty))
                return WDSAPI::echoJsonError("ERROR: agent property not found for ID = $agentPropertyID.");

            $agentProperty->geo_risk = $geoRisk;
            $agentProperty->save();
        }

        $returnArray['error'] = 0; // success

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * Retrieves unprocessed agent properties for GeoHazard processing.
     */
    private function getUnprocessedGeoRisk()
    {
        // Retrieve the data from the view.
        $sql = "SELECT * FROM view_agent_properties_to_be_processed";

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);

        return $command->queryAll();
    }
}