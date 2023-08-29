<?php

class ResDedicatedController extends Controller
{
    const CLIENT_ANALYTICS_ID = 'res_dedicated_client_analytics_clientid';
    const CLIENT_ANALYTICS_STARTDATE = 'res_dedicated_client_analytics_start_date';
    const CLIENT_ANALYTICS_ENDDATE = 'res_dedicated_client_analytics_end_date';

    const CLIENT_ANALYTICS_ALLCLIENTS_STARTDATE = 'res_dedicated_client_analytics_allclients_start_date';
    const CLIENT_ANALYTICS_ALLCLIENTS_ENDDATE = 'res_dedicated_client_analytics_allclients_end_date';

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
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetDedicatedHours',
                        'apiGetDedicatedHoursAnalytics',
                        'apiGetDedicatedHoursMonthBreakdownAnalytics'
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
                    'create',
                    'createHours',
                    'update',
                    'updateHours',
                    'delete',
                    'admin',
                    'index',
                    'indexAllClients'
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array(
                    'apiGetDedicatedHours',
                    'apiGetDedicatedHoursAnalytics',
                    'apiGetDedicatedHoursMonthBreakdownAnalytics'
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
     * @return ResDedicated the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=ResDedicated::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'admin' page.
	 */
	public function actionCreate($clientid, $hoursid)
	{
		$model = new ResDedicated;

		if(isset($_POST['ResDedicated']))
		{
			$model->attributes=$_POST['ResDedicated'];
			if($model->save())
				$this->redirect(array('admin'));
		}

        $hours_model = ResDedicatedHours::model()->findByPk($hoursid);

        $dedicated_start_date = $hours_model->dedicated_start_date;
        $dedicated_hours = $hours_model->dedicated_hours;

        // Getting an array of months not yet filled out for the calendar year following the 'dedicated_start_date'

        $months = $model->dedicatedMonthsToFill($dedicated_start_date, $clientid);

        $type = 'dedicated';

		$this->render('create',array(
			'model' => $model,
            'clientid' => $clientid,
            'hoursid' => $hoursid,
            'months' => $months,
            'type' => $type
		));
	}

	/**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'admin' page.
     */
	public function actionCreateHours($clientid)
	{
		$model=new ResDedicatedHours;

		if(isset($_POST['ResDedicatedHours']))
		{
			$model->attributes=$_POST['ResDedicatedHours'];
			if($model->save())
				$this->redirect(array('admin'));
		}

        $type = 'dedicated_hours';

		$this->render('create',array(
			'model'=>$model,
            'clientid' => $clientid,
            'type' => $type
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if(isset($_POST['ResDedicated']))
		{
			$model->attributes = $_POST['ResDedicated'];
			if ($model->save())
				$this->redirect(array('admin'));
		}

        $type = 'dedicated';

		$this->render('update',array(
			'model'=>$model,
            'type' => $type
		));
	}

	/**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be updated
     */
	public function actionUpdateHours($id)
	{
        $model = ResDedicatedHours::model()->findByPk($id);

		if(isset($_POST['ResDedicatedHours']))
		{
			$model->attributes = $_POST['ResDedicatedHours'];
			if ($model->save())
				$this->redirect(array('admin'));
		}

        if ($model->dedicated_start_date)
            $model->dedicated_start_date = date('Y-m-d', strtotime($model->dedicated_start_date));

        $type = 'dedicated_hours';

		$this->render('update',array(
			'model'=>$model,
            'type' => $type
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
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$dedicated = new ResDedicated('search');
		$dedicated->unsetAttributes();
		if(isset($_GET['ResDedicated']))
			$dedicated->attributes=$_GET['ResDedicated'];

        $hours = new ResDedicatedHours('search');
		$hours->unsetAttributes();
		if(isset($_GET['ResDedicatedHours']))
			$hours->attributes=$_GET['ResDedicatedHours'];

		$this->render('admin',array(
			'dedicated' => $dedicated,
            'hours' => $hours
		));
	}

    /**
     * renders dedicated analytics view
     */
    public function actionIndex()
    {
        $clients = Client::model()->findAll(array(
            'select' => 'id, name',
            'order' => 'name ASC',
            'condition' => 'wds_fire = 1 AND active = 1 AND dedicated = 1'
        ));

        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');
        $client_id = current($clients)->id;

        if (isset($_POST['ResDedicatedAnalytics']))
        {
            $start_date = $_POST['ResDedicatedAnalytics']['client_start_date'];
            $end_date = $_POST['ResDedicatedAnalytics']['client_end_date'];
            $client_id = $_POST['ResDedicatedAnalytics']['client_id'];

            $_SESSION[self::CLIENT_ANALYTICS_ID] = $client_id;
            $_SESSION[self::CLIENT_ANALYTICS_STARTDATE] = $start_date;
            $_SESSION[self::CLIENT_ANALYTICS_ENDDATE] = $end_date;
        }
        else if (isset($_SESSION[self::CLIENT_ANALYTICS_ID]))
        {
            $start_date = $_SESSION[self::CLIENT_ANALYTICS_STARTDATE];
            $end_date = $_SESSION[self::CLIENT_ANALYTICS_ENDDATE];
            $client_id = $_SESSION[self::CLIENT_ANALYTICS_ID];
        }

        $dedicatedHours = ResDedicated::getDedicatedHoursAnalytics($client_id, $start_date, $end_date);
        $dedicatedHoursMonthBreakdown = ResDedicated::getDedicatedHoursMonthBreakdownAnalytics($client_id, $start_date, $end_date);

        $this->render('index', array(
            'clients' => $clients,
            'clientstartdate' => $start_date,
            'clientenddate' => $end_date,
            'clientid' => $client_id,
            'dedicatedHours' => $dedicatedHours,
            'dedicatedHoursMonthBreakdown' => $dedicatedHoursMonthBreakdown
        ));
    }

    /**
     * renders dedicated analytics view for all clients
     */
    public function actionIndexAllClients()
    {
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');

        if (isset($_POST['ResDedicatedAnalytics']))
        {
            $start_date = $_POST['ResDedicatedAnalytics']['client_start_date'];
            $end_date = $_POST['ResDedicatedAnalytics']['client_end_date'];

            $_SESSION[self::CLIENT_ANALYTICS_STARTDATE] = $start_date;
            $_SESSION[self::CLIENT_ANALYTICS_ENDDATE] = $end_date;
        }
        else if (isset($_SESSION[self::CLIENT_ANALYTICS_STARTDATE]))
        {
            $start_date = $_SESSION[self::CLIENT_ANALYTICS_STARTDATE];
            $end_date = $_SESSION[self::CLIENT_ANALYTICS_ENDDATE];
        }

        $dedicatedHoursMonthBreakdown = ResDedicated::getDedicatedHoursMonthBreakdownAllAnalytics($start_date, $end_date);

        $this->render('indexAllClients', array(
            'clientstartdate' => $start_date,
            'clientenddate' => $end_date,
            'dedicatedHoursMonthBreakdown' => $dedicatedHoursMonthBreakdown
        ));
    }

    //---------------------------------------------------- API Calls -------------------------------------------------

    /**
     * API Method: resDedicated/apiGetDedicatedHours
     * Description: Get dedicated service hour stats based off client and startdate
     *
     * Post data parameters:
     * @param integer $clientID - Client ID
     * @param string $startDate - start date of dedicated search
     *
     * Post data example:
     * {
     *      "data": {
     *          "clientID": 1,
     *          "startDate": "2015-05-23"
     *      }
     * }
     */
    public function actionApiGetDedicatedHours()
	{
        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('clientID', 'startDate')))
            return;

        $returnData = array();

        // Check if ResDedicatedHours model entry exists in the given dedicated year.

        $criteria = new CDbCriteria();
        $criteria->addCondition('client_id = ' . $data['clientID']);
        $criteria->addCondition("'" . $data['startDate'] . "' BETWEEN dedicated_start_date AND DATEADD(day, -1, DATEADD(MONTH, 12, dedicated_start_date))");
        $criteria->order = 'dedicated_start_date DESC';
        $criteria->limit = 1;

        if (!ResDedicatedHours::model()->exists($criteria))
        {
            return WDSAPI::echoJsonError('No Year entry exist in the given time for client: ' . $data['clientID'] . '.');
        }
        else
        {
            $hours_model = ResDedicatedHours::model()->find($criteria);

            // Check if ResDedicated model entry exists in the given month

            $startdate = date('Y-m-01', strtotime($data['startDate']));

            $criteria = new CDbCriteria();
            $criteria->addCondition("hours_id = $hours_model->id");
            $criteria->addCondition("MONTH('$startdate') = MONTH(date)");
            $criteria->order = 'date DESC';

            if (!ResDedicated::model()->exists($criteria))
            {
                return WDSAPI::echoJsonError('No dedicated entry exists in that month for client ' . $data['clientID'] . '.');
            }
            else
            {
                $dedicated_model = ResDedicated::model()->find($criteria);

                $total_hrs_used = Yii::app()->db->createCommand("SELECT SUM(CAST(hours_used AS NUMERIC(8,2))) FROM res_dedicated WHERE hours_id = $hours_model->id")->queryScalar();

                $returnData['dedicated_hours_year'] = $hours_model->dedicated_hours;
                $returnData['dedicated_hours_year_used'] = $total_hrs_used;
                $returnData['dedicated_hours_year_date'] = date('Y-m-d', strtotime($hours_model->dedicated_start_date));

                $returnData['dedicated_month'] = array(
                    'dedicated_hours_month_used' => $dedicated_model->hours_used,
                    'dedicated_date' => date('Y-m-d', strtotime($dedicated_model->date)),
                    'dedicated_date_updated' => date('Y-m-d', strtotime($dedicated_model->date_updated)),
                    'dedicated_states' => array(
                        'AZ' => (float)$dedicated_model->AZ,
                        'CA' => (float)$dedicated_model->CA,
                        'CO' => (float)$dedicated_model->CO,
                        'ID' => (float)$dedicated_model->ID,
                        'MT' => (float)$dedicated_model->MT,
                        'ND' => (float)$dedicated_model->ND,
                        'NM' => (float)$dedicated_model->NM,
                        'NV' => (float)$dedicated_model->NV,
                        'OR' => (float)$dedicated_model->OR,
                        'SD' => (float)$dedicated_model->SD,
                        'TX' => (float)$dedicated_model->TX,
                        'UT' => (float)$dedicated_model->UT,
                        'WA' => (float)$dedicated_model->WA,
                        'WY' => (float)$dedicated_model->WY
                    )
                );
            }
        }

        WDSAPI::echoResultsAsJson(array(
            'error' => 0,
            'data' => $returnData
        ));
    }

    /**
     * API Method: resDedicated/apiGetDedicatedHoursAnalytics
     * Description: Get dedicated service hour stats based of client and date range
     *
     * Post data parameters:
     * @param integer $clientID - Client ID
     * @param string $startDate - start date of dedicated search
     * @param string $endDate - end date of dedicated search
     *
     * Post data example:
     * {
     *      "data": {
     *          "clientID": 1,
     *          "startDate": "2015-04-17",
     *          "endDate": "2015-06-18"
     *      }
     * }
     */
    public function actionApiGetDedicatedHoursAnalytics()
    {
        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('clientID', 'startDate', 'endDate')))
            return;

        $returnData = ResDedicated::getDedicatedHoursAnalytics($data['clientID'], $data['startDate'], $data['endDate']);

        WDSAPI::echoResultsAsJson(array(
            'error' => 0,
            'data' => $returnData
        ));
    }

    /**
     * API Method: resDedicated/apiGetDedicatedHoursMonthBreakdownAnalytics
     * Description: Get dedicated service hour stats based of client and date range in a month breakdown format
     *
     * Post data parameters:
     * @param integer $clientID - Client ID
     * @param string $startDate - start date of dedicated search
     * @param string $endDate - end date of dedicated search
     *
     * Post data example:
     * {
     *      "data": {
     *          "clientID": 1,
     *          "startDate": "2015-04-17",
     *          "endDate": "2015-06-18"
     *      }
     * }
     */
    public function actionApiGetDedicatedHoursMonthBreakdownAnalytics()
    {
        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('clientID', 'startDate', 'endDate')))
            return;

        $returnData = ResDedicated::getDedicatedHoursMonthBreakdownAnalytics($data['clientID'], $data['startDate'], $data['endDate']);

        WDSAPI::echoResultsAsJson(array(
            'error' => 0,
            'data' => $returnData
        ));
    }
}
