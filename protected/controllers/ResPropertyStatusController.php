<?php

class ResPropertyStatusController extends Controller
{
    const ATTRIBUTES = 'wds_response_prop_status_searchAttr';
    const COLUMNS_TO_SHOW = 'wds_response_prop_status_columnsToShow';
    const PAGE_SIZE = 'wds_response_prop_status_pageSize';
    const SORT = 'wds_response_prop_status_sort';

    /**
     * Layout of views for this controller.
     */
	public $layout = '//layouts/main';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
            // Enable YiiBooster for only certain views.
            array('ext.bootstrap.filters.BootstrapFilter + admin'),
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
                    'update'
                ),
				'users'=>array('@'),
			),
			array('allow',
				'actions' => array(
                    'savePropertyStatus'
                ),
				'users' => array('*'),
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
	 * The main response property status admin grid.
	 */
	public function actionAdmin($resetFilters = NULL, $print = NULL)
	{
        $model = new ResTriggeredWithPropertyStatus('search');
        $model->unsetAttributes();

        if (isset($resetFilters))
        {
            $_SESSION[self::ATTRIBUTES] = NULL;
            $this->redirect(array('admin'));
        }
        elseif (filter_has_var(INPUT_GET, ResTriggeredWithPropertyStatus::modelName()))
        {
            $input = filter_input(INPUT_GET, ResTriggeredWithPropertyStatus::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $model->attributes = $input;
            $_SESSION[self::ATTRIBUTES] = $input;
        }
        elseif (isset($_SESSION[self::ATTRIBUTES]))
        {
            $model->attributes = $_SESSION[self::ATTRIBUTES];
        }

        if (filter_has_var(INPUT_GET, self::COLUMNS_TO_SHOW))
        {
            $columnsToShow = filter_input(INPUT_GET, self::COLUMNS_TO_SHOW, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            $_SESSION[self::COLUMNS_TO_SHOW] = $columnsToShow;
        }
        elseif (isset($_SESSION[self::COLUMNS_TO_SHOW]))
        {
            $columnsToShow = $_SESSION[self::COLUMNS_TO_SHOW];
        }
        else
        {
            // Default columns to show.
            $columnsToShow = array(
                10 => 'client_name',
                20 => 'fire_name',
                25 => 'notice_name',
                30 => 'member_last_name',
                40 => 'property_address_line_1',
                50 => 'property_response_status',
                60 => 'priority',
                70 => 'threat',
                80 => 'distance',
                90 => 'engine_name',
                100 => 'status',
                110 => 'date_visited',
            );
        }

        if (filter_has_var(INPUT_GET, self::PAGE_SIZE))
        {
            $pageSize = filter_input(INPUT_GET, self::PAGE_SIZE);
            $_SESSION[self::PAGE_SIZE] = $pageSize;
        }
        elseif (isset($_SESSION[self::PAGE_SIZE]))
        {
            $pageSize = $_SESSION[self::PAGE_SIZE];
        }
        else
        {
            $pageSize = 25; // Default
        }

        if (filter_has_var(INPUT_GET, self::SORT))
        {
            $sort = filter_input(INPUT_GET, self::SORT);
            $_SESSION[self::SORT] = $sort;
        }
        elseif (isset($_SESSION[self::SORT]))
        {
            $sort = $_SESSION[self::SORT];
        }
        else
        {
            $sort = 'priority'; // Default
        }

        $dataProvider = $model->search($pageSize, $sort);

        // Get the fire names.
        $fireNames = ResFireName::model()->getFireNames($model->client_name);

        // Get the engines.
        $engines = Engine::model()->findAll();

        // Get the client names.
        $clientCriteria = new CDbCriteria();
        $clientCriteria->order = 'name';
        $clients = Client::model()->findAll($clientCriteria);

        if (isset($print) && $print == 1)
        {
            $this->setPageTitle('Property Status Checklist | Wildfire Defense Systems');
            $this->layout = '//layouts/printable';

            $this->render('print', array(
                'data' => $dataProvider,
            ));
        }
        else
        {
            $this->render('admin', array(
                'dataProvider' => $dataProvider,
                'model' => $model,
                'columnsToShow' => $columnsToShow,
                'columnsToShowName' => self::COLUMNS_TO_SHOW,
                'pageSize' => $pageSize,
                'pageSizeName' => self::PAGE_SIZE,
                'engines' => $engines,
                'fireNames' => $fireNames,
                'clients' => $clients,
            ));
        }
    }

    /**
     * Update action for a property status entry.
     */
    public function actionUpdate($id = NULL)
    {
        if (isset($id) && filter_has_var(INPUT_POST, ResTriggeredWithPropertyStatus::modelName()))
        {
            $model = ResPropertyStatus::model()->findByAttributes(array('res_triggered_id' => $id));
            
            if (!isset($model))
            {
                $model = new ResPropertyStatus();
                $model->res_triggered_id = $id;
            }

            $model->attributes = filter_input(INPUT_POST, ResTriggeredWithPropertyStatus::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            if ($model->save())
            {
				Yii::app()->user->setFlash('success', "Property Status $id was updated successfully!");
				$this->redirect(array('admin'));
            }
            else
            {
                $model = $this->loadModel($id);
            }
        }
        else
        {
            $model = $this->loadModel($id);
        }

        // Get the engines.
        $engineData = Engine::model()->findAll(array('select' => 'id, name'));
        $engines = CHtml::listData($engineData, 'id', 'name');
        
        $this->render('update', array(
            'model' => $model,
            'engines' => $engines,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
	private function loadModel($id)
	{
        $model = new ResTriggeredWithPropertyStatus();

        if (isset($id))
            $model = $model->with('property', 'propertyStatus', 'notice', 'fire', 'engine')->findByPk($id);

		return $model;
	}
    
    /**
     * Saves property status records.
     */
    public function actionSavePropertyStatus() 
    {
		if (!WDSAPI::getInputDataArray($data))
			return;
                
        foreach ($data['property_statuses'] as $propertyStatusData)
        {
            $propertyStatus = ResPropertyStatus::model()->findByAttributes(array('res_triggered_id' => $propertyStatusData['id']));
            
            if (!isset($propertyStatus))
            {
                $propertyStatus = new ResPropertyStatus();
                $propertyStatus->res_triggered_id = $propertyStatusData['id'];
            }

            if (isset($propertyStatusData['engine_id']))
                $propertyStatus->engine_id = $propertyStatusData['engine_id'];

            if (isset($propertyStatusData['division']))
                $propertyStatus->division = $propertyStatusData['division'];

            if (isset($propertyStatusData['status']))
                $propertyStatus->status = $propertyStatusData['status'];

            if (isset($propertyStatusData['actions']))
                $propertyStatus->actions = $propertyStatusData['actions'];

            if (isset($propertyStatusData['has_photo']))
                $propertyStatus->has_photo = $propertyStatusData['has_photo'];

            if (isset($propertyStatusData['other_issues']))
                $propertyStatus->other_issues = $propertyStatusData['other_issues'];

            if (isset($propertyStatusData['date_visited']))
                $propertyStatus->date_visited = $propertyStatusData['date_visited'];
            
            if (!$propertyStatus->save())
                return WDSAPI::echoJsonError('ERROR: failed to save property status!');
        }
                
        $returnArray['error'] = 0; // success

        WDSAPI::echoResultsAsJson($returnArray);
    }    
}