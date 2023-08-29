<?php

class EngEnginesController extends Controller
{
    const CREW_ANALYTICS_ID = 'eng_engines_crew_analytics_id';
    const CREW_ANALYTICS_STARTDATE = 'eng_engines_crew_analytics_start_date';
    const CREW_ANALYTICS_ENDDATE = 'eng_engines_crew_analytics_end_date';

    const CLIENT_ANALYTICS_ID = 'eng_engines_client_analytics_clientid';
    const CLIENT_ANALYTICS_STARTDATE = 'eng_engines_client_analytics_start_date';
    const CLIENT_ANALYTICS_ENDDATE = 'eng_engines_client_analytics_end_date';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
			'postOnly + delete',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetAllianceEngines',
                        'apiGetAllianceEngine',
                        'apiUpdateAllianceEngine',
                        'apiCreateAllianceEngine'
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
                    'index',
                    'admin',
                    'sendAllianceReminderEmail',
                    'indexEngineForms',
                    'indexAnalytics',
                    'indexAnalyticsDayRender',
                    'indexAnalyticsDayGetFires',
                    'indexAnalyticsBreakdown',
                    'indexAnalyticsMap',
                    'indexAnalyticsCrew',
                    'indexAnalyticsClient',
                    'indexAnalyticsDays',
                    'indexAnalyticsPolicyholder',
                    'enginesFeatureCollection',
                    'getClientFires',
                ),
                'users'=>array('@'),
            ),
			array('allow', // Do Not allow 'Engine View' user type to access these actions
				'actions'=>array(
                    'update',
                    'delete'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Engine",$user->types) || in_array("Engine Manager",$user->types)'
			),
			array('allow', // Only allow 'Engine Manager' user type to access these actions
				'actions'=>array(
                    'create'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Engine Manager",$user->types)'
			),
            array('allow',
                'actions'=>array(
                    'apiGetAllianceEngines',
                    'apiGetAllianceEngine',
                    'apiUpdateAllianceEngine',
                    'apiCreateAllianceEngine'
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EngEngines the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=EngEngines::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
     * Landing page
     */
	public function actionIndex()
	{
        $this->render('index');
	}

	/**
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model=new EngEngines('search');
		$model->unsetAttributes();
		if(isset($_GET['EngEngines']))
			$model->attributes=$_GET['EngEngines'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new EngEngines;

		if(isset($_POST['EngEngines']))
		{
			$model->attributes=$_POST['EngEngines'];
			if($model->save())
				$this->redirect(array('admin'));
		}

        // Form defaults when creating a new engine
        $model->availible = true;
        $model->engine_source = $model::ENGINE_SOURCE_WDS;

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['EngEngines']))
		{
			$model->attributes=$_POST['EngEngines'];
			if($model->save())
				$this->redirect(array('admin'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

    /**
     * Sends a reminder email to the corresponding Alliance Partner to update
     * their Alliance Tracking website.
     * @param integer $id the ID of the model
     */
    public function actionSendAllianceReminderEmail($id)
    {
        $model = $this->loadModel($id);
        $alliance = Alliance::model()->findByPk($model->alliance_id);

        //Create Text for email
        $body = '<img src ="http://www.wildfire-defense.com/images/wds-header.jpg" alt="Wildfire Defense Systems" /><br />';
        $body .= "$alliance->name,<br /><br />";
        $body .= 'Please remember to log into WDS Engines at <a href="https://engines.wildfire-defense.com/" target="_blank">https://engines.wildfire-defense.com/</a> to update your engines!<br /><br /><br />';
        $body .= '<font color="#777777">CONFIDENTIALITY NOTE : The information in this e-mail is confidential and privileged; it is intended for use solely by the individual or entity named as the recipient hereof. Disclosure, copying, distribution, or use of the contents of this e-mail by persons other than the intended recipient is strictly prohibited and may violate applicable laws. If you have received this e-mail in error, please delete the original message and notify us by return email or phone call immediately. </font>';

        $result = Helper::sendEmail('WDS Engines Checkin Reminder', $body, $alliance->email);

        if ($result)
        {
            // Save email date without updating entire model (this would show Alliance as updating their engines)
            $params = array(':date' => date('Y-m-d H:i'), 'alliance_id' => $alliance->id);
            Yii::app()->db->createCommand('UPDATE eng_engines SET date_email = :date WHERE alliance_id = :alliance_id')->execute($params);
            Yii::app()->user->setFlash('success', "Email sent to <b>$alliance->contact_first $alliance->contact_last</b> at <b>$alliance->email</b>.");
        }
        else
        {
            Yii::app()->user->setFlash('success', "There was a problem sending email to <b>$alliance->contact_first $alliance->contact_last</b> at <b>$alliance->email</b>.");
        }

        $this->redirect(Yii::app()->request->urlReferrer);
    }

    public function actionIndexEngineForms($fileName = null)
    {
        // Download file by searching for the fileName in the directed subdirectories.

        if ($fileName)
        {
            $dir = new RecursiveDirectoryIterator(Yii::app()->basePath . DIRECTORY_SEPARATOR . 'response');
            $iter = new RecursiveIteratorIterator($dir);
            $absoluteFilePath = null;
            foreach($iter as $iterfile)
            {
                if ($iterfile->getFilename() === $fileName)
                    $absoluteFilePath = $iterfile->getPath() . DIRECTORY_SEPARATOR . $iterfile->getFilename();
            }

            if ($absoluteFilePath !== null)
                Yii::app()->request->sendFile($fileName, file_get_contents($absoluteFilePath));
        }

        $dedicatedEngineForms = array_map(function($file) { return basename($file); }, glob(Yii::app()->basePath . '\response\dedicated_engineforms\*'));
        $responseEngineForms = array_map(function($file) { return basename($file); }, glob(Yii::app()->basePath . '\response\response_engineforms\*'));
        $mouNames = array_map(function($file) { return basename($file); }, glob(Yii::app()->basePath . '\response\mou\*'));

        $this->render('indexEngineForms', array(
            'dedicatedEngineForms' => $dedicatedEngineForms,
            'responseEngineForms' => $responseEngineForms,
            'mouNames' => $mouNames
        ));
    }

    /**
     * Renders main engine anlytics landing view
     * @return mixed
     */
    public function actionIndexAnalytics()
    {
        $engineReportForm = new EngineReportForm;
        $engineReportDayForm = new EngineReportDayForm;

		if (isset($_POST['ajax']) && $_POST['ajax'] === 'engine-report-form')
		{
			echo CActiveForm::validate($engineReportForm);
			Yii::app()->end();
		}

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'engine-report-day-form')
        {
            echo CActiveForm::validate($engineReportDayForm);
            Yii::app()->end();
        }

        $engineReportForm->startdate = date('Y-01-01');
        $engineReportForm->enddate = date('Y-m-d');
        $engineReportForm->onhold = true;

        $tallyEngineResponse = array();
        $tallyEngineDedicated = array();
        $tallyEngineOnHold = array();
        $tallyEngineTotal = array();
        $tallyPoliciesTriggered = array();
        $tallyDispatchedFires = array();

        if (isset($_POST['EngineReportForm']))
        {
            $engineReportForm->attributes = $_POST['EngineReportForm'];
            if ($engineReportForm->validate())
            {
                list(
                    $tallyEngineResponse,
                    $tallyEngineDedicated,
                    $tallyEngineOnHold,
                    $tallyEngineTotal,
                    $tallyPoliciesTriggered,
                    $tallyDispatchedFires
                ) = $engineReportForm->getTallies();
                $engineReportDayForm->clientids = json_encode($engineReportForm->clientids);
                $engineReportDayForm->sources = json_encode($engineReportForm->sources);
                $engineReportDayForm->onhold = $engineReportForm->onhold;
            }
        }

        $this->render('indexAnalytics', array(
            'engineReportForm' => $engineReportForm,
            'engineReportDayForm' => $engineReportDayForm,
            'tallyEngineResponse' => $tallyEngineResponse,
            'tallyEngineDedicated' => $tallyEngineDedicated,
            'tallyEngineOnHold' => $tallyEngineOnHold,
            'tallyEngineTotal' => $tallyEngineTotal,
            'tallyPoliciesTriggered' => $tallyPoliciesTriggered,
            'tallyDispatchedFires' => $tallyDispatchedFires
        ));
    }

    /**
     * Renders detailed day stats view into the main engines anaytics view
     * Intended to be rendered asynchronously.
     */
    public function actionIndexAnalyticsDayRender()
    {
        $engineReportDayForm = new EngineReportDayForm;

        if (isset($_POST['EngineReportDayForm']))
        {
            $engineReportDayForm->attributes = $_POST['EngineReportDayForm'];

            $results = $engineReportDayForm->getEngineDayResults();

            echo $this->renderPartial('_indexAnalyticsDay', array(
                'engineReportDayForm' => $engineReportDayForm,
                'results' => $results
            ));
        }
    }

    /**
     * Returns dispatched fires with notices created on the given date
     * Returned as options for a select menu dropdown
     * @param string $date
     * @param array $clientids
     */
    public function actionIndexAnalyticsDayGetFires($date, $clientids)
    {
        foreach (EngineReportDayForm::getFires($date, json_decode($clientids)) as $fireid => $name)
            echo CHtml::tag('option', array('value' => $fireid), CHtml::encode($name), true);
    }

    public function actionIndexAnalyticsBreakdown()
    {
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');

        if (isset($_POST['EnginesAnalytics']))
        {
            $start_date = date_create($_POST['EnginesAnalytics']['reporting_start_date'])->format('Y-m-d');
            $end_date = date_create($_POST['EnginesAnalytics']['reporting_end_date'])->format('Y-m-d');
        }

		$model = new EngScheduling('search');
		$model->unsetAttributes();
		if (isset($_GET['EngScheduling']))
            $model->attributes=$_GET['EngScheduling'];

        $dataProvider = $model->searchAnalytics($start_date, $end_date);

        $month_results_array = EngScheduling::reportingIndexMonthBreakDown($start_date, $end_date);

        $utilization = EngScheduling::reportingTotalEngineUtilization($start_date, $end_date);
        $utilization_wds = EngScheduling::reportingTotalEngineUtilization($start_date, $end_date, EngEngines::ENGINE_SOURCE_WDS);
        $utilization_alliance = EngScheduling::reportingTotalEngineUtilization($start_date, $end_date, EngEngines::ENGINE_SOURCE_ALLIANCE);

        $this->render('indexAnalyticsBreakdown', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'analytics_start_date' => $start_date,
            'analytics_end_date' => $end_date,
            'month_results_array' => $month_results_array,
            'utilization' => $utilization,
            'utilization_wds' => $utilization_wds,
            'utilization_alliance' => $utilization_alliance
        ));
    }

    public function actionIndexAnalyticsMap($print = false, $startDate = null)
    {
		$searchDate = date('Y-m-d H:m');
		$startSearchDate = date('Y-m-d H:i', strtotime('tomorrow') - 1);

		$startDate = ($startDate) ? $startDate : date('Y-m-d');
		$endDate = date('Y-m-d', strtotime('+1 day', strtotime($startDate)));

        $not_active_array = array(
            EngScheduling::ENGINE_ASSIGNMENT_STAGED,
            EngScheduling::ENGINE_ASSIGNMENT_OUTOFSERVICE,
            EngScheduling::ENGINE_ASSIGNMENT_INSTORAGE
        );

        // Active engines grouped by engine id to only show most recent assignment for today
        $criteria = new CDbCriteria;
        $criteria->with = array('employees','fire','engine');
        $criteria->condition = "t.id IN (
	        SELECT min(s.id) as id FROM eng_scheduling s
            INNER JOIN eng_engines e ON s.engine_id = e.id
	        WHERE s.start_date <= '$endDate' AND s.end_date > '$startDate' AND s.assignment NOT IN ('" . implode("','", $not_active_array) . "') AND e.active = 1
            GROUP BY s.engine_id
        )";
        $criteria->order = 't.assignment ASC, fire.Name ASC, engine.engine_source, engine.alliance_id';
        $models_active = EngScheduling::model()->findAll($criteria);

        // Not active engines grouped by engine id to only show most recent assignment for today
        $criteria = new CDbCriteria;
        $criteria->with = array('employees');
        $criteria->condition = "t.id IN (
	        SELECT max(s.id) as id FROM eng_scheduling s
            INNER JOIN eng_engines e ON s.engine_id = e.id
	        WHERE s.start_date <= '$endDate' AND s.end_date > '$startDate' AND e.engine_source = 1 AND s.assignment IN ('" . implode("','", $not_active_array) . "') AND e.active = 1
            GROUP BY s.engine_id
        )";
        $criteria->order = 't.assignment ASC';
        $models_notactive = EngScheduling::model()->findAll($criteria);

        $models_all = array_merge($models_active, $models_notactive);
        $ids = array_map(function($model) { return $model->engine_id; }, $models_all);

        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('id', $ids);
        $criteria->addCondition('active = 1 and engine_source = 2');
        $unused_engines = EngEngines::model()->findAll($criteria);

        if ($print)
        {
            return $this->renderPartial('indexAnalyticsMap',array(
			'models_active' => $models_active,
            'models_notactive' => $models_notactive,
            'models_all' => $models_all,
            'unused_engines' => $unused_engines,
            'print' => $print
            ),false,true);
        }

		$this->render('indexAnalyticsMap',array(
			'models_active' => $models_active,
            'models_notactive' => $models_notactive,
            'models_all' => $models_all,
            'unused_engines' => $unused_engines,
            'print' => $print,
			'startDate' => $startDate
		));
    }

    public function actionIndexAnalyticsCrew()
    {
        $startdate = date('Y-01-01');
        $enddate = date('Y-m-d');
        $crewmodel = new EngCrewManagement();

        if (isset($_POST['EngCrewManagement']) && !empty($_POST['EngCrewManagement']['id']))
        {
            $_SESSION[self::CREW_ANALYTICS_ID] = $_POST['EngCrewManagement']['id'];
            $_SESSION[self::CREW_ANALYTICS_STARTDATE] = $_POST['EngCrewManagement']['crew_start_date'];
            $_SESSION[self::CREW_ANALYTICS_ENDDATE] = $_POST['EngCrewManagement']['crew_end_date'];

            $crewmodel = EngCrewManagement::model()->findByPk($_POST['EngCrewManagement']['id']);
            $startdate = $_POST['EngCrewManagement']['crew_start_date'];
            $enddate = $_POST['EngCrewManagement']['crew_end_date'];
        }
        else if (isset($_POST['EngCrewManagement']) && empty($_POST['EngCrewManagement']['id']))
        {
            $crewmodel = new EngCrewManagement();
        }
        else if (isset($_SESSION[self::CREW_ANALYTICS_ID]))
        {
            $crewmodel = EngCrewManagement::model()->findByPk($_SESSION[self::CREW_ANALYTICS_ID]);
            $startdate = $_SESSION[self::CREW_ANALYTICS_STARTDATE];
            $enddate = $_SESSION[self::CREW_ANALYTICS_ENDDATE];
        }

        $employeeModel = new EngSchedulingEmployee('search');
		$employeeModel->unsetAttributes();
		if(isset($_GET['EngSchedulingEmployee']))
            $employeeModel->attributes = $_GET['EngSchedulingEmployee'];

        $dataProvider = $employeeModel->searchCrewAnalytics($crewmodel->id, $startdate, $enddate);

        $this->render('indexAnalyticsCrew', array(
            'crewstartdate' => $startdate,
            'crewenddate' => $enddate,
            'crewmodel' => $crewmodel,
            'employeeModel' => $employeeModel,
            'dataProvider' => $dataProvider
        ));
    }

    public function actionIndexAnalyticsClient()
    {
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');
        $client_id = '';

        if (isset($_POST['EngAnalyticsClient']))
        {
            $start_date = $_POST['EngAnalyticsClient']['client_start_date'];
            $end_date = $_POST['EngAnalyticsClient']['client_end_date'];
            if(date('Y-m-d',strtotime($start_date)) == date('Y-m-d', strtotime('1969-12-31')))
            { 
                Yii::app()->user->setFlash('error', "Invalid Date Range");
            }

            if (!empty($_POST['EngAnalyticsClient']['client_id']))
                $client_id = $_POST['EngAnalyticsClient']['client_id'];

            $_SESSION[self::CLIENT_ANALYTICS_ID] = $client_id;
            $_SESSION[self::CLIENT_ANALYTICS_STARTDATE] = $start_date;
            $_SESSION[self::CLIENT_ANALYTICS_ENDDATE] = $end_date;
        }
        else if (isset($_POST['EngAnalyticsClient']) && empty($_POST['EngAnalyticsClient']['client_id']))
        {
            unset($_SESSION[self::CLIENT_ANALYTICS_ID]);
        }
        else if (isset($_SESSION[self::CLIENT_ANALYTICS_ID]))
        {
            $start_date = $_SESSION[self::CLIENT_ANALYTICS_STARTDATE];
            $end_date = $_SESSION[self::CLIENT_ANALYTICS_ENDDATE];
            $client_id = $_SESSION[self::CLIENT_ANALYTICS_ID];
        }

		$model = new EngScheduling('search');
		$model->unsetAttributes();
		if(isset($_GET['EngScheduling']))
            $model->attributes=$_GET['EngScheduling'];

        if (isset($client_id))
            $dataProvider = $model->searchAnalytics($start_date, $end_date, $client_id);
        else
            $dataProvider = $model->searchAnalytics($start_date, $end_date);

        $this->render('indexAnalyticsClient', array(
            'clientstartdate' => $start_date,
            'clientenddate' => $end_date,
            'clientid' => $client_id,
            'model' => $model,
            'dataProvider' => $dataProvider
        ));
    }

    public function actionIndexAnalyticsDays()
    {
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');

        if (isset($_POST['EngAnalyticsDays']))
        {
            $start_date = $_POST['EngAnalyticsDays']['start_date'];
            $end_date = $_POST['EngAnalyticsDays']['end_date'];
        }

        $results_company = EngScheduling::reportingIndexDaysByCompany($start_date, $end_date);
        $results_engines = EngScheduling::reportingIndexDaysByEngine($start_date, $end_date);

        $this->render('indexAnalyticsDays', array(
            'results_company' => $results_company,
            'results_engines' => $results_engines,
            'start_date' => $start_date,
            'end_date' => $end_date
        ));
    }

    /* Action to show Policyholder Report form and results
     * @return mixed
     */
    public function actionIndexAnalyticsPolicyholder()
    {
        $responseClients = Client::model()->findAllByAttributes(array('wds_fire' => 1), array('order' => 'name ASC'));
        $responseClientsList = CHtml::listData($responseClients, 'id', 'name');

        $this->render('indexAnalyticsPolicyholder', array(
            'responseClientsList' => $responseClientsList,

        ));
    }

    /* Action for ajax call to fill in fire dropdown based on client
     * @return mixed
     */
    public function actionGetClientFires($clientID)
    {
        echo CHtml::tag('option', array('value' => false), 'Select a fire', true);

        $clientNotices = ResNotice::model()->findAllBySql('
            SELECT fire_id FROM res_notice WHERE notice_id IN (
	            SELECT MAX(notice_id) FROM res_notice WHERE client_id = :client_id GROUP BY client_id, fire_id
            )
            ORDER BY notice_id DESC', array(
                ':client_id' => $clientID
            )
        );
        $fires = CHtml::listData($clientNotices, 'fire_id', 'fire_name');
 
        foreach ($fires as $key => $value)
        {
            echo CHtml::tag('option', array('value' => $key), CHtml::encode($value), true);
        }
    }

    public function actionEnginesFeatureCollection($startDate = null)
    {

		$startDate = ($startDate) ? $startDate : date('Y-m-d');
		$endDate = date('Y-m-d', strtotime('+1 day', strtotime($startDate)));

        $engines = EngScheduling::model()->findAllBySql("select * from eng_scheduling where id in(
				select min(id) from eng_scheduling where
				start_date < :end_date
				and end_date >= :start_date
				group by engine_id
			)",
            array(
                ':start_date' => $startDate,
                ':end_date' => $endDate
            )
        );

        $featureCollection = array();
        $featureCollection['type'] = 'FeatureCollection';
        $featureCollection['features'] = array();

        foreach($engines as $engine)
        {
            $clients = array();
            foreach ($engine->engineClient as $engineClient)
                $clients[$engineClient->client_id] = $engineClient->client_name;

            $featureCollection['features'][] = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array($engine->lon, $engine->lat)
                ),
                'properties' => array(
                    'id' => $engine->id,
                    'marker-size' => 'medium',
                    'marker-color' => $this->markerColor($engine->assignment),
                    'marker-symbol' => $this->markerIcon($engine->assignment),
                    'popup' => $this->markerDescription($engine),
                    'clients' => $clients
                )
            );
        }

        echo json_encode($featureCollection);
    }

    private function markerDescription($engine)
    {
        $retval = '<table>';
        $retval .= '<tr><th colspan="2" style="text-align: center;">' . $engine->engine_name . '</th></tr>';
        $retval .= '<tr><th style="text-align: right;">Assignment &nbsp;- &nbsp;</th><td>' . $engine->assignment . '</td></tr>';
        $retval .= '<tr><th style="text-align: right;">Source &nbsp;- &nbsp;</th><td>' . $engine->getEngineSource($engine->engine_source) . '</td></tr>';
        if ($engine->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE)
            $retval .= '<tr><th style="text-align: right;">Partner &nbsp;- &nbsp;</th><td>' . $engine->engine->alliance_partner . '</td></tr>';
        $retval .= '<tr><th style="text-align: right;">Client &nbsp;- &nbsp;</th><td>' . join(' / ', $engine->client_names) . '</td></tr>';
        $retval .= '<tr><th style="text-align: right;">Start &nbsp;- &nbsp;</th><td>' . date('m-d-Y H:i', strtotime($engine->start_date . ' ' . $engine->start_time)) . '</td></tr>';
        $retval .= '<tr><th style="text-align: right;">End &nbsp;- &nbsp;</th><td>' . date('m-d-Y H:i', strtotime($engine->end_date . ' ' . $engine->end_time)) . '</td></tr>';
        $retval .= '</table>';
        return $retval;
    }

    private function markerColor($assignment)
    {
        switch ($assignment) {
            case EngScheduling::ENGINE_ASSIGNMENT_DEDICATED: return EngScheduling::ENGINE_COLOR_DEDICATED;
            case EngScheduling::ENGINE_ASSIGNMENT_PRERISK: return EngScheduling::ENGINE_COLOR_PRERISK;
            case EngScheduling::ENGINE_ASSIGNMENT_RESPONSE: return EngScheduling::ENGINE_COLOR_RESPONSE;
            case EngScheduling::ENGINE_ASSIGNMENT_ONHOLD: return EngScheduling::ENGINE_COLOR_ONHOLD;
            case EngScheduling::ENGINE_ASSIGNMENT_STAGED: return EngScheduling::ENGINE_COLOR_STAGED;
            case EngScheduling::ENGINE_ASSIGNMENT_OUTOFSERVICE: return EngScheduling::ENGINE_COLOR_OUTOFSERVICE;
            case EngScheduling::ENGINE_ASSIGNMENT_INSTORAGE: return EngScheduling::ENGINE_COLOR_INSTORAGE;
        }
    }

    private function markerIcon($assignment)
    {
        switch ($assignment) {
            case EngScheduling::ENGINE_ASSIGNMENT_DEDICATED: return 'warehouse';
            case EngScheduling::ENGINE_ASSIGNMENT_PRERISK: return 'building';
            case EngScheduling::ENGINE_ASSIGNMENT_RESPONSE: return 'fire-station';
            case EngScheduling::ENGINE_ASSIGNMENT_ONHOLD: return 'roadblock';
            case EngScheduling::ENGINE_ASSIGNMENT_STAGED: return 'bus';
            case EngScheduling::ENGINE_ASSIGNMENT_OUTOFSERVICE: return 'danger';
            case EngScheduling::ENGINE_ASSIGNMENT_INSTORAGE: return 'prison';
        }
    }

    public function getOlderThanOneWeek($data)
    {
        if ($data->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE)
            if (new DateTime($data->date_updated) < new DateTime('-1 week'))
                return true;

        return false;
    }

    /**
     * API Method: engEngines/apiGetAllianceEngines
     * Description: Gets all alliance engines for a specific alliance partner.
     *
     * Post data parameters:
     * @param integer allianceID - filters the results by a alliance partner
     *
     * Post data example:
     * {
     *     "data": {
     *         "allianceID": 1
     *     }
     * }
     */
    public function actionApiGetAllianceEngines()
    {
        $data = NULL;
        $returnData = array();

        if(!WDSAPI::getInputDataArray($data, array('allianceID')))
            return;

        $models = EngEngines::model()->findAllByAttributes(array('alliance_id' => $data['allianceID']));

        foreach($models as $model)
            $returnData[] = $model->attributes;

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }


    /**
     * API Method: engEngines/apiGetAllianceEngine
     * Description: Gets an alliance engine for a specific alliance partner.
     *
     * Post data parameters:
     * @param integer allianceID - filters the results by a alliance partner
     * @param integer id - the id of the engine
     *
     * Post data example:
     * {
     *     "data": {
     *         "allianceID": 1,
     *         "id": 14
     *     }
     * }
     */
    public function actionApiGetAllianceEngine()
    {
        $data = NULL;
        $returnData = array();

        if(!WDSAPI::getInputDataArray($data, array('allianceID', 'id')))
            return;

        $model = EngEngines::model()->findByAttributes(array('alliance_id' => $data['allianceID'], 'id' => $data['id']));

        $returnData[] = (!empty($model)) ? $model->attributes : null;

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engEngines/apiUpdateAllianceEngine
     * Description: Updates an alliance engines for a specific alliance partner.
     *
     * Post data parameters:
     * @param integer allianceID - filters the results by a alliance partner
     * @param integer id - the id of the engine
     * @param array attributes - array of attributes for engine model update
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 14,
     *         "allianceID": 1
     *         "attributes": {
     *             "engine_name": "E1234",
     *             "make": "Ford",
     *             "model": "F350",
     *             "vin": "T45E681S8WBG12WA9",
     *             "plate": "63J378",
     *             "type": "Type 3 Engine",
     *             "availible": 1,
     *             "reason": "",
     *             "comment": "",
     *             "alliance_id": 6,
     *             "engine_source": 2
     *         }
     *     }
     * }
     */
    public function actionApiUpdateAllianceEngine()
    {
        $data = NULL;
        $returnData = array();

        if(!WDSAPI::getInputDataArray($data, array('allianceID', 'id')))
            return;

        $model = EngEngines::model()->findByAttributes(array('alliance_id' => $data['allianceID'], 'id'=>$data['id']));

        try
        {
            foreach ($data['attributes'] as $key => $value)
                $model->$key = $value;
        }
        catch (Exception $ex)
        {
            return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
        }

        if ($model->save())
        {
            return WDSAPI::echoResultsAsJson(array('error' => 0));
        }
        else
        {
            $errorMessage = WDSAPI::getFormattedErrors($model);
            return WDSAPI::echoJsonError("ERROR: Failed to save! $errorMessage");
        }

    }


    /**
     * API Method: engEngines/apiCreateAllianceEngine
     * Description: Creates an alliance engine for a specific alliance partner.
     *
     * Post data parameters:
     * @param integer allianceID - filters the results by a alliance partner
     * @param array attributes - array of attributes for engine model update
     *
     * Post data example:
     * {
     *     "data": {
     *         "allianceID": 1
     *         "attributes": {
     *             "engine_name": "E1234",
     *             "make": "Ford",
     *             "model": "F350",
     *             "vin": "T45E681S8WBG12WA9",
     *             "plate": "63J378",
     *             "type": "Type 3 Engine",
     *             "availible": 1,
     *             "reason": "",
     *             "comment": "",
     *             "alliance_id": 6,
     *             "engine_source": 2
     *         }
     *     }
     * }
     */
    public function actionApiCreateAllianceEngine()
    {
        $data = NULL;
        $returnData = array();

        if(!WDSAPI::getInputDataArray($data, array('allianceID')))
            return;

        $model = new EngEngines();

        try
        {
            foreach ($data['attributes'] as $key => $value)
                $model->$key = $value;
        }
        catch (Exception $ex)
        {
            return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
        }

        if ($model->save())
        {
            return WDSAPI::echoResultsAsJson(array('error' => 0));
        }
        else
        {
            $errorMessage = WDSAPI::getFormattedErrors($model);
            return WDSAPI::echoJsonError("ERROR: Failed to save! $errorMessage");
        }

    }
}
