<?php

class ResFireNameController extends Controller
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
                        'apiGetAllFiresByMonth',
                        'apiGetAllFiresByStatus',
                        'apiGetAllFires',
                        'apiGetFire',
                        'apiCreateFire',
                        'apiUpdateFire',
                        'apiGetProgramFiresByDate',
                        'apiGetMonitoredFiresByDate',
                        'apiCountFiresByDate',
                        'apiGetHistoricalTally',
                        'apiGetFiresByProperty'
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
                    'admin',
                    'update',
                    'create',
                    'formMostRecentFires'
                ),
				'users' => array('@'),
			),
			array('allow',
				'actions' => array(
                    'apiGetAllFiresByMonth', 
                    'apiGetAllFiresByStatus', 
                    'apiGetAllFires', 
                    'apiGetFire', 
                    'apiCreateFire', 
                    'apiUpdateFire', 
                    'apiGetProgramFiresByDate',
                    'apiGetMonitoredFiresByDate',
                    'apiCountFiresByDate',
                    'apiGetHistoricalTally',
                    'apiGetFiresByProperty'
                ),
				'users' => array('*')),
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
    
    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    #region Crud Controllers
    
    /**
	 * Fire Name Grid
	 */
	public function actionAdmin()
	{
        $model = new ResFireName('search');
        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['ResFireName']))
        {
            $model->attributes = $_GET['ResFireName'];
        }

        $this->render('admin',array(
            'model' => $model
        ));
    }
    
    /**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new ResFireName;

		if (isset($_POST['ResFireName']))
		{
			$model->attributes = $_POST['ResFireName'];
			if ($model->save())
			{
				Yii::app()->user->setFlash('success', CHtml::encode($model->Name) . ' Created Successfully!');
				$this->redirect(array('admin'));
			}
		}
        
        // Autofill model information with information from 
        
        if (isset(Yii::app()->session['centroidLat'])  && Yii::app()->session['centroidLat']  !== null &&
            isset(Yii::app()->session['centroidLong']) && Yii::app()->session['centroidLong'] !== null)
        {
            $model->Coord_Lat = Yii::app()->session['centroidLat'];
            $model->Coord_Long = Yii::app()->session['centroidLong'];
            $model->Smoke_Check = 0;
            $model->Start_Date = date('Y-m-d');
            
            $url = 'https://maps.googleapis.com/maps/api/timezone/json?location=' . $model->Coord_Lat . ',' . $model->Coord_Long . '&timestamp=0';
            
            $result = CJSON::decode(CurlRequest::getRequest($url), false);
            
            if (isset($result->timeZoneId))
            {
                $model->Timezone = $result->timeZoneId;
            }
        }
        
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
        //Retreive fire
		$model=$this->loadModel($id);
        
        if(isset($_POST['ResFireName']))
		{
			$model->attributes=$_POST['ResFireName'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', CHtml::encode($model->Name)." Updated Successfully!");
				$this->redirect(array('admin'));
			}
		}
        
         //Get Form type from URL
        $type = Yii::app()->request->getQuery('type');
        
        //Show form
        $this->render('update',array(
			'model'=>$model,
            'type'=>$type,
		));
    }
    
    public function actionFormMostRecentFires()
    {
        $models = ResFireName::model()->findAll(array(
            'select' => 'Name, City, State, Coord_Lat, Coord_Long',
            'condition' => 'Date_Created >= DATEADD(week,-2,GETDATE())'
        ));
        
        $feature_collection = array();
        $feature_collection['type'] = 'FeatureCollection';
        $feature_collection['features'] = array();
        
        foreach($models as $model)
        {
            $feature_collection['features'][] = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array($model->Coord_Long, $model->Coord_Lat)
                ),
                'properties' => array(
                    'marker-size' => 'medium',
                    'marker-color' => '#FF0000',
                    'marker-symbol' => 'fire-station',
                    'popup' => "<b>$model->Name</b><br />$model->City, $model->State<br />"
                )
            );
        }
        
        echo CJSON::encode($feature_collection);
    }
    
    /**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return ResFireName the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=ResFireName::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
    
    #endregion
    
    //-------------------------------------------------------------API Methods------------------------------------------------------------
    # region API Methods

    /**
     * API Method: resFireName/apiGetAllFiresByStatus
     * Description: Gets all fires by status for a given client and year. Results are ordered by status.
     * 
     * Post data parameters:
     * @param int clientID - filters the results by a client
     * @param int year - filters the results by year
     * @param varchar(2) state - filters the results by state (optional)
     * 
     * Post data example: {"data": {"clientID": 1, "year": 2014}}
     */    
    public function actionApiGetAllFiresByStatus()
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('clientID', 'year')))
            return;
        
        $state = (isset($data['state']))? $data['state'] : null;
        
        $returnData = ResFireName::model()->getFiresByStatus($data['clientID'], $data['year'], $state);
        
        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);        
    }
    
        /**
     * API Method: resFireName/apiGetAllFires
     * Description: Gets all fires with optional filters
     * 
     * Post data parameters:
     * @param int year - filters the results by year (optional)
     * @param limit - limits the results (optional)
     * 
     * Post data example:
     * {
     *     "data": {
     *         "limit": 20, 
     *         "year": 2014
     *     }
     * }
     */    
    public function actionApiGetAllFires()
    {
        $data = NULL;
        $returnData = array();
        $returnArray = array();
        
        WDSAPI::getInputDataArray($data);

        $year = (isset($data['year']))? $data['year'] : null;
        $limit = (isset($data['limit']))? $data['limit'] : null;
        $smokeCheck = (isset($data['smokeCheck']))? $data['smokeCheck'] : null;

        $criteria = new CDbCriteria();
        
        $criteria->order = "Fire_ID DESC";
        
        if($year)
            $criteria->addCondition('Start_Year = ' . $year, "AND");
        if($smokeCheck)
            $criteria->addCondition('Smoke_Check = ' . $smokeCheck, "AND");
        if($limit)
            $criteria->limit = $limit;
        $fires = ResFireName::model()->findAll($criteria);
 

        foreach($fires as $fire)
        {
            $returnData[]=array(
                "Fire_ID"=>$fire->Fire_ID,
                "Name"=>$fire->Name,
                "City"=>$fire->City,
                "State"=>$fire->State
            );
            
        }
        
        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);        
    }
    
    
    /**
     * API Method: resFireName/apiGetFire
     * Description: Gets a fire by ID.
     * 
     * Post data parameters:
     * @param int fireID - ID of the fire
     * 
     * Post data example: {"data": {"fireID": 123}}
     */    
    public function actionApiGetFire() 
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('fireID')))
            return;
        
        $fireID = $data['fireID'];
        
        $fire = ResFireName::model()->findByPk($fireID);

        if (!isset($fire))
            return WDSAPI::echoJsonError("ERROR: a fire was not found for ID = $fireID.");
        
        // Also look up the fire statuses for this fire.
        $fireStatuses = ResFireStatus::model()->findAllByAttributes(array('fire_id' => $fireID));
        
        $fireStatusData = array();
        foreach ($fireStatuses as $fireStatus)
        {
            $fireStatusData[] = $fireStatus->attributes;
        }
        
        // Lookup the associated fuels for this fire.
        $fireFuels = ResFuel::model()->findAllByAttributes(array('Fire_ID' => $fireID));
        
        $fireFuelsData = array();
        foreach ($fireFuels as $fireFuel)
        {
            $fireFuelsData[] = $fireFuel->attributes;
        }
        
        $returnData = $fire->attributes;
        $returnData['fireStatuses'] = $fireStatusData;
        $returnData['fireFuels'] = $fireFuelsData;
        
        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resFireName/apiCreateFire
     * Description: Creates a new fire.
     * 
     * Post data parameters:
     * @param string Name - name of the fire
     * @param string City - city name where the fire exists
     * @param string State - state name where the fire exists
     * @param int Contained - flag indicating whether or not the fire is contained
     * @param array fireStatuses - (optional) array of {client ID, status ID} to associate with this fire
     * @param int[] fuelTypes - (optional) array of fuel types IDs to associate with this fire
     * 
     * Post data example: 
     * { "data": {
     *       "Name": "Test Fire",
     *       "City": "Los Angeles",
     *       "State": "CA",
     *       "Contained": 0,
     *       "fireStatuses": [ {
     *          "client_id": 1,
     *          "status": 1 }, {
     *          "client_id": 3,
     *          "status": 1 }
     *       ],
     *       "fuelTypes": [1, 3, 4]
     *    }
     * }
     */    
    public function actionApiCreateFire() 
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('Name', 'City', 'State', 'Contained')))
            return;
        
        $returnArray = $this->saveFire($data, true);

        WDSAPI::echoResultsAsJson($returnArray);
    }    

    /**
     * API Method: resFireName/apiUpdateFire
     * Description: Updates a new fire.
     * 
     * Post data parameters:
     * @param int Fire_ID - ID of the fire
     * @param string Name - (optional) name of the fire
     * @param string City - (optional) city name where the fire exists
     * @param string State - (optional) state name where the fire exists
     * @param int Contained - (optional) flag indicating whether or not the fire is contained
     * @param array fireStatuses - (optional) array of {client ID, status ID} to associate with this fire
     * @param int[] fuelTypes - (optional) array of fuel types IDs to associate with this fire
     * 
     * Post data example: 
     * { "data": { "Fire_ID": 123, "Name": "Test Fire", ... } }
     */    
    public function actionApiUpdateFire() 
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('Fire_ID')))
            return;

        $returnArray = $this->saveFire($data, false);        

        WDSAPI::echoResultsAsJson($returnArray);
    }
    
    #endregion
    
    //-------------------------------------------------------------General Functions------------------------------------------------------------
    #region General Functions

    /**
     * Save a fire.
     * @param array $data JSON data input
     * @return array returnArray
     */
    private function saveFire($data, $isNewRecord)
    {
        if (isset($data['fireStatuses']))
        {
            $fireStatuses = $data['fireStatuses'];
            unset($data['fireStatuses']);
        }

        if (isset($data['fuelTypes']))
        {
            $fuelTypes = $data['fuelTypes'];
            unset($data['fuelTypes']);
        }

        if ($isNewRecord)
        {
            $fire = new ResFireName();
        }
        else
        {
            if (isset($data['Fire_ID']))
            {
                $fireID = $data['Fire_ID'];
                
                $fire = ResFireName::model()->findByPk($fireID);

                if (!isset($fire))
                    return WDSAPI::echoJsonError("ERROR: could not find a fire with ID: $fireID");
            }
            else
            {
                return WDSAPI::echoJsonError("ERROR: Fire_ID was not provided!");
            }
        }
        
        try
        {
            foreach ($data as $key => $value)
            {
                $fire[$key] = $value;
            }
        }
        catch (Exception $ex)
        {
            return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
        }
        
        $now = new DateTime();
        $nowFormatted = $now->format("Y-m-d H:i:s");
        $fire->Date_Updated = $nowFormatted;
                
        if ($isNewRecord)
            $fire->Date_Created = $nowFormatted;
        
        // Save the fire.        
        if (!$fire->save())
        {
            $errorMessage = WDSAPI::getFormattedErrors($fire);
            return WDSAPI::echoJsonError("ERROR: Failed to save the fire! $errorMessage");
        }
        
        if (isset($fireStatuses))
        {            
            // Save the fire status entries.
            foreach ($fireStatuses as $status)
            {
                // Delete existing fire status entries for the fire and client. (There can only be one status per client/fire.)
                ResFireStatus::model()->deleteAllByAttributes(array('fire_id' => $fire->Fire_ID, 'client_id' => $status['client_id']));
                
                $fireStatus = new ResFireStatus();
                $fireStatus->fire_id = $fire->Fire_ID;
                $fireStatus->client_id = $status['client_id'];
                $fireStatus->status = $status['status'];
                
                if (!$fireStatus->save())
                {
                    $errorMessage = WDSAPI::getFormattedErrors($fireStatus);
                    return WDSAPI::echoJsonError("ERROR: Failed to save a fire status for Client ID = $status[0] and status = $status[1]. $errorMessage");
                }
            }
        }

        if (isset($fuelTypes))
        {
            // Delete existing fuel types.
            ResFuel::model()->deleteAllByAttributes(array('Fire_ID' => $fire->Fire_ID));
            
            // Save the fuel types.
            foreach ($fuelTypes as $fuelTypeID)
            {
                $fuel = new ResFuel();
                $fuel->Fire_ID = $fire->Fire_ID;
                $fuel->fuel_type_id = $fuelTypeID;
                
                if (!$fuel->save())
                {
                    $errorMessage = WDSAPI::getFormattedErrors($fuel);
                    return WDSAPI::echoJsonError("ERROR: Failed to save a fuel type for ID = $fuelTypeID. $errorMessage");
                }
            }
        }

        $returnArray = array();
        $returnArray['error'] = 0; // success
        
        if ($isNewRecord)
            $returnArray['fireID'] = $fire->Fire_ID;
        
        return $returnArray;
    }
    
    /**
     * API method: resFireName/apiGetProgramFiresByDate
     * Description: Gets the fires and fire stats from a time range, grouped by fire. Includes whether the fire was dispatched, the location and number triggered
     * 
     * Post data parameters:
     * @param string startDate - start range, typically start of month (notice created) 
     * @param string endDate - end range, typically end of month (notice created)
     * @param integer clientID - client to select for
     * 
     * Post data example: 
     * {
     *     "data": {
     *         "startDate": "2015-01-02", 
     *         "endDate": "2015-02-01", 
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetProgramFiresByDate() 
    {
        $data = NULL;
        $returnArray = array();
        
        if (!WDSAPI::getInputDataArray($data, array('startDate')))
            return;
        
        if (!WDSAPI::getInputDataArray($data, array('endDate')))
            return;
        
        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;
        
        //Get results from model function
        WDSAPI::echoResultsAsJson(
            array(
                'error'=>0, 
                'data'=>ResFireName::getProgramFiresByDate(
                    $data['startDate'], 
                    $data['endDate'], 
                    $data['clientID'],
                    1
                )
            )
        );
        
    }
    
    
    /**
     * API method: resFireName/apiGetMonitoredFiresByDate
     * Description: Gets the monitored fires and fire stats from a time range, grouped by fire.
     * 
     * Post data parameters:
     * @param string startDate - start range, typically start of month (notice created) 
     * @param string endDate - end range, typically end of month (notice created)
     * @param integer clientID - client to select for
     * 
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2015-01-02", 
     *         "endDate": "2015-02-01", 
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetMonitoredFiresByDate() 
    {
        $data = NULL;
        $returnArray = NULL;

        if (!WDSAPI::getInputDataArray($data, array('startDate','endDate','clientID')))
            return;
        
        //$allFires = ResFireName::getMonitoredFiresByDate($data['startDate'], $data['endDate'], $data['clientID']);
        //$triggeredFires = ResMonitorLog::getFiresTriggering($data['startDate'], $data['endDate'], $data['clientID']);

        $result = ResMonitorLog::getMonitoredFireSummary($data['startDate'], $data['endDate'], $data['clientID']);

        if($result['error']){
            //Get results from model function
            $returnArray = array(
                "error"=>1, 
                "errorFriendlyMessage"=> "We could not process your request. Please contact the service provider if this problem persists.",
                "errorMessage" => "Something went wrong while fetching data from the model."
            );

        }
        else{
            //Get results from model function
            $returnArray = array("error"=>0, "data"=>array(array("all_fires"=>$result["data"])));
        }

        WDSAPI::echoResultsAsJson($returnArray);
        
    }
    
    /**
     * API method: resFireName/apiCountFiresByDate
     * Description: Counts all fires monitored for a certain date range
     * 
     * Post data parameters:
     * @param string startDate - start range, typically start of month (notice created) 
     * @param string endDate - end range, typically end of month (notice created)
     * 
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2015-01-02", 
     *         "endDate": "2015-02-01"
     *     }
     * }
     */
    public function actionApiCountFiresByDate() 
    {
        $data = NULL;
        $returnArray = array();
        
        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

         $sql = '
            declare @dateStart varchar(10) = :dateStart;
            declare @dateEnd varchar(10) = :dateEnd;
            declare @clientID int = :clientID;

        SELECT COUNT(t.Obs_ID) FROM (
         SELECT MAX(o.Obs_ID) Obs_ID
         FROM res_monitor_log m
          INNER JOIN res_fire_obs o ON o.Obs_ID = m.Obs_ID
          INNER JOIN res_monitor_triggered rt on rt.monitor_id = m.Monitor_ID
                   WHERE m.monitored_date >= @dateStart AND m.monitored_date < @dateEnd
                   and rt.client_id = @clientID
                   and m.monitor_id in (
                                        select max(l.monitor_id) as monitor_id
                                        from res_monitor_log l
                                        inner join res_fire_obs o on o.obs_id = l.obs_id
                                        where
                                            l.monitored_date >= @dateStart
                                            and l.monitored_date < @dateEnd
                                        group by o.fire_id
                                        )
                 GROUP BY o.Fire_ID
         ) t
        ';

        $count = (int) Yii::app()->db->createCommand($sql)
            ->bindParam(':dateStart', $data['startDate'], PDO::PARAM_STR)
            ->bindParam(':dateEnd', $data['endDate'], PDO::PARAM_STR)
            ->bindParam(':clientID', $data['clientID'], PDO::PARAM_INT)
            ->queryScalar();

        $returnArray['error'] = 0;
        $returnArray['data'] = array(
            'count' => $count
        );

        WDSAPI::echoResultsAsJson($returnArray);
    }
    
    /**
     * API method: resFireName/apiGetHistoricalTally
     * Description: Gets a tally of the # fires per month for the given client and timeframe.
     * 
     * Post data parameters:
     * @param string startDate - start range, typically start of month (notice created) 
     * @param string endDate - end range, typically end of month (notice created)
     * @param integer clientID
     * 
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1,
     *         "startDate": "2015-01-02",
     *         "endDate": "2015-02-01"
     *     }
     * }
     */
    public function actionApiGetHistoricalTally() 
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('startDate')))
            return;
        
        if (!WDSAPI::getInputDataArray($data, array('endDate')))
            return;
        
        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;
        
        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>ResFireName::getHistoricalTally($data['startDate'], $data['endDate'], $data['clientID'])));
        
    }

    /**
     * API method: resFireName/apiGetFiresByProperty
     * Description: Get's all the fires that the given property has been near
     * 
     * Post data parameters:
     * @param string pid
     * @param integer clientID
     * @param integer type - 1 = recent fires, 2 = all program fires
     * @param string date - the date to search back to (required if searching all fires)
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1,
     *         "pid": "13452",
     *         "type": 1,
     *         "date": "2015-05-04"
     *     }
     * }
     */
    public function actionApiGetFiresByProperty() 
    {
        $data = NULL;
        $result = null;
        
        if (!WDSAPI::getInputDataArray($data, array('pid')))
            return;
        
        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        if (!WDSAPI::getInputDataArray($data, array('type')))
            return;
        

        if($data['type'] == 1){
            $result = ResFireName::getFiresByProperty($data['pid'], $data['clientID'], $data['date']);
        }
        elseif($data['type'] == 2){
            $result = ResFireName::getProgramFiresByProperty($data['pid'], $data['clientID']);
        }

        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>$result));
    }
    
    #endregion
}