<?php

class EngSchedulingController extends Controller
{
    const CALENDAR_SEARCH = 'wds_engines_calendar_searchAttr';

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
                        'apiDownloadKMZ',
                        'apiGetEngineAssignments'
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
                    'view',
                    'resourceOrder',
                    'engineModal',
                    'formGetAvailibleFires',
                    'formGetResourceOrders',
                    'formPopulateFireInformation',
                    'geocode',
                    'calendarEngineFeed'
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array(
                    'apiDownloadKMZ',
                    'apiGetEngineAssignments'
                ),
                'users'=>array('*'),
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
     * @return EngScheduling the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = EngScheduling::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param EngScheduling $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax']==='eng-scheduling-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new EngScheduling('search');
        $model->unsetAttributes();
        if (isset($_GET['EngScheduling']))
            $model->attributes = $_GET['EngScheduling'];
        $x = Yii::app()->request->getParam('EngScheduling');
        $searchClientID = isset($_POST['searchClientID']) ? $_POST['searchClientID'] : null;

        $dataProvider = $model->search($searchClientID);
        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'x' => $x
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'admin' page.
     */
    public function actionCreate($id = null, $date = null)
    {
        $engScheduling = new EngScheduling;

        if (isset($_POST['EngScheduling']))
        {
            try {
                $engScheduling->attributes = $_POST['EngScheduling'];
                if ($engScheduling->save())
                {
                    $engSchedulingId = Yii::app()->db->getLastInsertID();
                    $resourceOrderId = $this->saveResourceOrder($engSchedulingId);
                    if($resourceOrderId)
                    {
                        $sql = 'UPDATE [dbo].[eng_scheduling] SET resource_order_id ='.$resourceOrderId.'where id = '.$resourceOrderId; 
                        $success = $command = Yii::app()->db->createCommand($sql)->execute();
                        if($success)
                        {
                            Yii::app()->user->setFlash('success', 'Engine Scheduled Successfully!');
                            $this->redirect(array('update','id'=>$engSchedulingId));  
                        }
                    }
                }
            }catch(ErrorException $e)
            {
                Yii::warning("Ooops...something went wrong.");
            }
        }

        if ($id !== null && $date !== null)
        {
            $engScheduling->start_date = date('m/d/Y', strtotime($date));
            $engScheduling->engine_id = $id;
        }

        // Hardcode start/end times to typical work times on create
        if ($engScheduling->start_time === null) { $engScheduling->start_time = '09:00'; }
        if ($engScheduling->end_time   === null) { $engScheduling->end_time = '18:00';   }

        $this->render('create', array(
            'model' => $engScheduling
        ));
    }
    
    private function saveResourceOrder($scheduleId)
    {
        $sql = "SET IDENTITY_INSERT [dbo].[eng_resource_order] ON;";
        //$success = Yii::app()->db->createCommand($sql)->execute();
        $dateOrdered = Yii::app()->format->datetime(date('Y-m-d') . ' ' . date('H:i'));
        Yii::app()->format->datetimeFormat = 'Y-m-d H:i';
        $userId = Yii::app()->user->id;
        $dateCreated = date('Y-m-d H:i:s');
        $sql .= "INSERT INTO [dbo].[eng_resource_order](id,user_id,date_ordered,date_created) values(:scheduleId,:userId,:dateOrdered,:dateCreated);";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':scheduleId',$scheduleId);
        $command->bindParam(':dateOrdered',$dateOrdered);
        $command->bindParam(':userId',$userId);
        $command->bindParam(':dateCreated',$dateCreated);
        $response = $command->execute();
        if($response)
        {
            return Yii::app()->db->getLastInsertID();
        }
        return false;
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id, $engineclientmodelID = null, $employeeID = null)
    {
        $model = $this->loadModel($id);

        $engineclientmodel = $engineclientmodelID ? EngSchedulingClient::model()->findByPk($engineclientmodelID) : new EngSchedulingClient;
        $employeemodel = $employeeID ? EngSchedulingEmployee::model()->findByPk($employeeID) : new EngSchedulingEmployee;

        // SAVE ENGINE CLIENTS

        if (isset($_POST['EngSchedulingClient']))
        {
            if (isset($_POST['EngSchedulingClient']['client_id']) && $_POST['EngSchedulingClient']['client_id'][0]>0){
                foreach($_POST['EngSchedulingClient']['client_id'] as $clientId){
                    $engineclientmodel = $engineclientmodelID ? EngSchedulingClient::model()->findByPk($engineclientmodelID) : new EngSchedulingClient;
                    $engineclientmodel->attributes = $_POST['EngSchedulingClient'];
                    $engineclientmodel->client_id = $clientId;
                    $engineclientmodel->save();
                }

                //$engineclientmodel->attributes = $_POST['EngSchedulingClient'];
               // if ($engineclientmodel->save())
                //{
                    Yii::app()->user->setFlash('success', 'Engine schedule client successfully saved!');
                    $this->redirect(array('update','id'=>$engineclientmodel->engine_scheduling_id,'engineclientmodelID'=>$engineclientmodelID));
                //}
            }
        }

        // SAVE ENGINE EMPLOYEE SCHEDULING

        if (isset($_POST['EngSchedulingEmployee']))
        {
             $employeemodel->attributes = $_POST['EngSchedulingEmployee'];
             $start_date = date('Y-m-d',strtotime($employeemodel->start_date));
             $end_date = date('Y-m-d',strtotime($employeemodel->end_date));
             if ($start_date > $end_date)
             {
                Yii::app()->user->setFlash('error', 'End date cannot precede the start date!');
             }
             else 
             {
                //if everything is validated as per scenario
                if($employeemodel ->validate())
                {
                     $employeemodel->save();
                     Yii::app()->user->setFlash('success', 'Employee Schedule successfully saved!');
                     $this->redirect(array('update','id'=>$employeemodel->engine_scheduling_id,'employeeID'=>$employeeID));
                }
             }
        }

        // SAVE ENGINE SCHEDULE
        // Hardcode start/end times to typical work times on update
        if ($model->start_time === null) { $model->start_time = '09:00'; }
        if ($model->end_time   === null) { $model->end_time = '18:00';   }
        if (isset($_POST['EngScheduling']))
        {
            $model->attributes=$_POST['EngScheduling'];

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', "Engine Schedule Updated Successfully!");
                $this->redirect(array('admin'));
            }
        }

        Yii::app()->format->dateFormat = 'm/d/Y';
        Yii::app()->format->timeFormat = 'H:i';

        // Set start/end dates of EngineSchedulingClient model to be same as scheduled engine

        if ($engineclientmodel->isNewRecord)
        {
            $engineclientmodel->start_time = Yii::app()->format->time($model->start_date);
            $engineclientmodel->start_date = Yii::app()->format->date($model->start_date);

            $engineclientmodel->end_time = Yii::app()->format->time($model->end_date);
            $engineclientmodel->end_date = Yii::app()->format->date($model->end_date);
        }

        // Set end date of Employee Model to be the same as scheduled engine

        if ($employeemodel->isNewRecord)
        {
            $employeemodel->start_time = Yii::app()->format->time($model->start_date);
            $employeemodel->start_date = Yii::app()->format->date($model->start_date);

            $employeemodel->end_time = Yii::app()->format->time($model->end_date);
            $employeemodel->end_date = Yii::app()->format->date($model->end_date);
        }

        // Format dates for Bootstrap date/time widgets

        $model->start_time = Yii::app()->format->time($model->start_date);
        $model->start_date = Yii::app()->format->date($model->start_date);

        $model->end_time = Yii::app()->format->time($model->end_date);
        $model->end_date = Yii::app()->format->date($model->end_date);

        $model->arrival_time = Yii::app()->format->time($model->arrival_date);
        $model->arrival_date = Yii::app()->format->date($model->arrival_date);

        $this->render('update', array(
            'model' => $model,
            'engineclientmodel' => $engineclientmodel,
            'employeemodel' => $employeemodel
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);

        if (!empty($model->engineClient))
        {
            EngSchedulingClient::model()->deleteAll('engine_scheduling_id = :id', array(':id' => $model->id));
        }

        if (!empty($model->employees))
        {
            EngSchedulingEmployee::model()->deleteAll('engine_scheduling_id = :id', array(':id' => $model->id));
        }

        if (!empty($model->shiftTickets))
        {
            foreach ($model->shiftTickets as $shiftTicket)
            {
                if (!empty($shiftTicket->engShiftTicketActivities))
                {
                    EngShiftTicketActivity::model()->deleteAll('eng_shift_ticket_id = :id', array(':id' => $shiftTicket->id));
                }

                if (!empty($shiftTicket->statuses))
                {
                    EngShiftTicketStatus::model()->deleteAll('shift_ticket_id = :id', array(':id' => $shiftTicket->id));
                }
            }
        }

        EngShiftTicket::model()->deleteAll('eng_scheduling_id = :id', array(':id' => $model->id));

        $model->delete();

        if (!isset($_GET['ajax']))
        {
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
    }

    /**
     * Displays a the model for a date in a printable compatible form.
     */
    public function actionView($print = false)
    {
        $searchDate = date('Y-m-d');
        $startSearchDate = date('Y-m-d H:i', strtotime('tomorrow') - 1);

        if (isset($_POST['EngSchedulingView']))
        {
            $_SESSION['eng_scheduling_viewdate'] = date('Y-m-d', strtotime($_POST['EngSchedulingView']['view-date']));
            $searchDate = $_SESSION['eng_scheduling_viewdate'];
            $startSearchDate = date('Y-m-d H:i', strtotime($_SESSION['eng_scheduling_viewdate'] . ' 23:59'));
        }
        else if (isset($_SESSION['eng_scheduling_viewdate']))
        {
            $searchDate = $_SESSION['eng_scheduling_viewdate'];
            $startSearchDate = date('Y-m-d H:i', strtotime($_SESSION['eng_scheduling_viewdate'] . ' 23:59'));
        }

        $not_active_array = array(
            EngScheduling::ENGINE_ASSIGNMENT_STAGED,
            EngScheduling::ENGINE_ASSIGNMENT_OUTOFSERVICE,
            EngScheduling::ENGINE_ASSIGNMENT_INSTORAGE
        );

        // Getting engine models and related empoloyees models for today's date

        $criteria = new CDbCriteria;
        $criteria->with = array('employees');
        $criteria->condition = "t.id IN (
            SELECT max(s.id) as id FROM eng_scheduling s
            INNER JOIN eng_engines e ON s.engine_id = e.id
            WHERE s.start_date <= '$startSearchDate' AND '$searchDate' <= s.end_date AND s.assignment NOT IN ('" . implode("','", $not_active_array) . "') AND e.active = 1
            GROUP BY s.engine_id
        )";
        $criteria->order = 't.assignment ASC';
        $models_active = EngScheduling::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->with = array('employees');
        $criteria->condition = "t.id IN (
            SELECT max(s.id) as id FROM eng_scheduling s
            INNER JOIN eng_engines e ON s.engine_id = e.id
            WHERE s.start_date <= '$startSearchDate' AND '$searchDate' <= s.end_date AND e.engine_source = 1 AND s.assignment IN ('" . implode("','", $not_active_array) . "') AND e.active = 1
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
            return $this->renderPartial('view',array(
                'models_active'=>$models_active,
                'models_notactive'=>$models_notactive,
                'unused_engines'=>$unused_engines,
                'print'=>$print,
                'searchDate'=>$searchDate
            ),false,true);
        }

        $this->render('view',array(
            'models_active'=>$models_active,
            'models_notactive'=>$models_notactive,
            'unused_engines'=>$unused_engines,
            'print'=>$print,
            'searchDate'=>$searchDate
        ));
    }

    /**
     * Displays resource order for this specific model.
     */
    public function actionResourceOrder($id, $print = false)
    {
        $model = $this->loadModel($id);

        if ($print)
        {
            EngResourceOrder::model()->downloadResourceOrderPDF($model);
            Yii::app()->end();
        }

        return $this->renderPartial('view_resource',array(
            'model' => $model,
            'print' => $print
        ), false, true);
    }

    /**
     * Render Content and new Model Instance for Schedule Engine Model
     */
    public function actionEngineModal($id)
    {
        $model = $this->loadModel($id);

        $employeeDataProvider = EngSchedulingEmployee::model()->search($model->id);

        return $this->renderPartial('_calendar_modal',array(
            'model'=>$model,
            'employeeDataProvider'=>$employeeDataProvider
        ));
    }

    /**
     * API method: engScheduling/apiDownloadKMZ
     * Description: Constructs and sends the KMZ for a given perimeter and clients
     *
     * Post data parameters:
     * @param array clientIDs - an array of client ids for which to include
     * @param integer perimeterID - the id of the perimeter to use for the KMZ
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientIDs": [
     *             "3",
     *             "7"
     *         ],
     *         "perimeterID": 145
     *     }
     * }
     */
    public function actionApiDownloadKMZ()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('perimeterID', 'clientIDs')))
            return;

        if (!is_array($data['clientIDs']))
        {
            return WDSAPI::echoJsonError('ERROR: incorrect attributes recieved.', '"clientIDs" must be an array of client IDs');
        }

        $clientIDs = $data['clientIDs'];
        $perimeterID = $data['perimeterID'];

        $kmz = new KMZEngine($perimeterID, $clientIDs);

        $filepath = $kmz->createKMZ();

        $fp = fopen($filepath, 'rb');
        $content = fread($fp, filesize($filepath));
        $content = unpack('H*hex', $content)['hex'];
        fclose($fp);

        $returnArray = array(
            'error' => 0,
            'data' => array()
        );

        $fileName = 'All Companies KMZ.kmz';
        if (count($data['clientIDs']) === 1)
        {
            $fileName = Yii::app()->db->createCommand('SELECT name FROM client WHERE id = :id')->queryScalar(array(':id' => $data['clientIDs'][0]));
            $fileName .= '.kmz';
        }

        if ($content)
        {
            $returnArray['data']['name'] = $fileName;
            $returnArray['data']['type'] = 'application/vnd.google-earth.kmz';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
        }

        WDSAPI::echoResultsAsJson($returnArray);

        $kmz->removeKMZ();
    }

    /**
     * API method: engScheduling/apiGetEngineAssignments
     * Description: Gets Engine Schedule Entries (assignments) for a given Date and Crew Member
     *
     * Post data parameters:
     * @param string crewID
     * @param string date
     */
    public function actionApiGetEngineAssignments()
    {
        $data = null;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('crewID', 'date')))
            return;

        $crewID = $data['crewID'];
        $date = $data['date'];

        $wherePart = "
        :date BETWEEN start_date AND end_date
        AND id IN (SELECT engine_scheduling_id FROM eng_scheduling_employee WHERE crew_id = :crew_id)
        ";

        $schedules = EngScheduling::model()->findAll($wherePart, array(':crew_id'=>$crewID, 'date'=>$date));
        //Go through each assignment and pull the data we need
        foreach($schedules as $schedule)
        {
            $returnData[] = array(
                'assignment' => $schedule->assignment,
                'scheduling_id' => $schedule->id,
                'city' => $schedule->city,
                'state' => $schedule->state,
                'engine_name' => $schedule->engine->engine_name,
                'start_date' => $schedule->start_date,
                'end_date' => $schedule->end_date,
                'client' => array_map(function($client) { return $client->client->name; }, $schedule->engineClient ),
                'crew' => array_map(function ($crew) { return ucwords(strtolower($crew->crew_first_name . ' ' . $crew->crew_last_name)); }, $schedule->employees),
                'duty_officer' => isset($schedule->engineFireOfficer) ? ucwords(strtolower($schedule->engineFireOfficer->first_name . ' ' . $schedule->engineFireOfficer->last_name)) : '',
                'fire_name' => $schedule->fire_name,
                'resource_order_id' => $schedule->resource_order_id,
            );
        }

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * Returns a list of fires for the Fire dropdown menu.
     */
    public function actionFormGetAvailibleFires($schedule_id = null)
    {
        if (Yii::app()->request->isPostRequest)
        {
            $fireslist = $this->formGetAvailibleFiresUpdate($schedule_id);

            echo CHtml::tag('option', array('value' => ''), '', true);
            foreach($fireslist as $value => $name)
            {
                echo CHtml::tag('option', array('value'=>$value), CHtml::encode($name), true);
            }
        }
    }

    /**
     * Returns a list of fires for the Fire dropdown menu.
     */
    public function actionFormGetResourceOrders()
    {
        if (Yii::app()->request->isPostRequest)
        {
            $resourceOrderList = CHtml::listData(EngScheduling::model()->getAvailibleResourceOrders(), 'id', function($data) {
                if ($data->engineScheduling) return "$data->id - Assigned";
                return "$data->id - Not Assigned";
            });

            echo CHtml::tag('option', array('value'=>''), '', true);
            foreach($resourceOrderList as $value => $name)
                echo CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
        }
    }

    public function formGetAvailibleFiresUpdate($schedule_id)
    {
        $availibleFiresList = CHtml::listData(EngScheduling::model()->getAvailibleFires(), 'Fire_ID', 'Name');
        if(!empty($schedule_id))
        {
            $schedule = EngScheduling::model()->findByPk($schedule_id);
            if(!array_key_exists($schedule->fire_id, $availibleFiresList))
            {
                $availibleFiresList[$schedule->fire_id] = $schedule->fire_name;
            }
        }
        return $availibleFiresList;
    }

    /**
     * Returns information on the fire requested.
     * @param integer $fire_id Fire_ID of the information to be returned
     */
    public function actionFormPopulateFireInformation()
    {
        if (Yii::app()->request->isPostRequest)
        {
            $fire_id = Yii::app()->request->getPost('fireid');
            $fire = ResFireName::model()->findByPk($fire_id);
            if ($fire)
            {
                $returnArray = array(
                    'error' => 0,
                    'data' => array(
                        'city' => isset($fire->City) ? $fire->City : '',
                        'state' => isset($fire->State) ? $fire->State : '',
                        'lat' => isset($fire->Coord_Lat) ? $fire->Coord_Lat : '',
                        'lon' => isset($fire->Coord_Long) ? $fire->Coord_Long : '',
                    )
                );
            }
            else
            {
                $returnArray = array(
                    'error' => 1,
                    'data' => array()
                );
            }
            echo CJSON::encode($returnArray);
        }
    }

    /**
     * Returns information and coordinates of a address provided
     * @param string $address Address string to be geocoded
     */
    public function actionGeocode($address)
    {
        echo CJSON::encode(Geocode::getLocation($address, Geocode::TYPE_PLACE));
    }

    /**
     * Returns html string of the calendar view for the engScheduling/admin view
     * @param string $datestring
     * @param string $searchdata
     * @return string
     */
    public function actionCalendarEngineFeed($datestring, $searchdata = null)
    {
        // ---------------------------------------------------------
        // Create Start and end dates for this week

        $date = new DateTime($datestring);
        $date_start = clone $date;
        $date_end = clone $date;

        // Deciding start and end dates to query

        if ($date_start->format('D') === 'Sun')
            $date_start->modify('midnight');
        else
            $date_start->modify('last Sunday');

        if ($date_end->format('D') === 'Sat')
            $date_end->modify('midnight + 23 hours 59 minutes');
        else
            $date_end->modify('next Saturday + 23 hours 59 minutes');

        $start = $date_start->format('Y-m-d H:i');
        $end = $date_end->format('Y-m-d H:i');

        if ($searchdata || isset($_SESSION[self::CALENDAR_SEARCH]))
        {
            if ($searchdata)
                $searchdata = json_decode($searchdata);
            else
                $searchdata = $_SESSION[self::CALENDAR_SEARCH];

            $_SESSION[self::CALENDAR_SEARCH] = $searchdata;

            $sql = "
                DECLARE @startdate datetime = '" . $date_start->format('Y-m-d H:i') . "';
                DECLARE @enddate datetime = '" . $date_end->format('Y-m-d H:i') . "';

                SELECT [t].[id], [t].[start_date], [t].[end_date],
                    [t].[engine_id], [t].[comment], [t].[assignment], [t].[fire_id],
                    [t].[city], [t].[state], [t].[lat], [t].[lon], [t].[arrival_date],
                    [t].[fire_officer_id], [t].[resource_order_id], [t].[specific_instructions]
                FROM [eng_scheduling] [t]
                INNER JOIN eng_engines ON t.engine_id = eng_engines.id";

                if ($searchdata->clients)
                    $sql .= ' INNER JOIN eng_scheduling_client ON t.id = eng_scheduling_client.engine_scheduling_id';

                $sql .=  '
                WHERE ((t.start_date BETWEEN @startdate AND @enddate) OR (t.end_date BETWEEN @startdate AND @enddate) OR
                       (@startdate BETWEEN t.start_date AND t.end_date) OR (@enddate BETWEEN  t.start_date AND t.end_date))';

                if ($searchdata->assignments)
                    $sql .= " AND assignment IN ('" . implode("','", $searchdata->assignments) . "')";

                if ($searchdata->clients)
                    $sql .= ' AND eng_scheduling_client.client_id IN (' . implode(',', $searchdata->clients) . ')';

                $sql .= ' ORDER BY engine_name, start_date';
        }
        else
        {
            $sql = "
                DECLARE @startdate datetime = '" . $date_start->format('Y-m-d H:i') . "';
                DECLARE @enddate datetime = '" . $date_end->format('Y-m-d H:i') . "';

                SELECT [t].[id], [t].[start_date], [t].[end_date],
                    [t].[engine_id], [t].[comment], [t].[assignment], [t].[fire_id],
                    [t].[city], [t].[state], [t].[lat], [t].[lon], [t].[arrival_date],
                    [t].[fire_officer_id], [t].[resource_order_id], [t].[specific_instructions]
                FROM [eng_scheduling] [t]
                INNER JOIN eng_engines ON t.engine_id = eng_engines.id
                WHERE (start_date BETWEEN @startdate AND @enddate) OR (end_date BETWEEN @startdate AND @enddate) OR
                      (@startdate BETWEEN start_date AND end_date) OR (@enddate BETWEEN  start_date AND end_date)
                ORDER BY engine_name, start_date;
            ";
        }

        $models = EngScheduling::model()->findAllBySql($sql);

        // ---------------------------------------------------------
        // Get all engines scheduled during this week, reorder them into the following format
        // {
        //     [
        //         engine1Model
        //     ], [
        //         engine2Model,
        //         engine2Model,
        //     ], [
        //         engine3Model
        //     ]
        // }

        $engine_ids = array_unique(array_map(function($data) { return $data->engine_id; }, $models));
        $engineArray = array();

        foreach ($engine_ids as $id)
        {
            $engineArray[] = array_filter($models, function($model) use ($id) { return $model->engine_id === $id; });
        }

        // ---------------------------------------------------------
        // Create Calendar Table

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($date_start, $interval, $date_end);

        $html = '
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>
                        <a href="'. $this->createUrl('engScheduling/calendarEngineFeed') . '" class="arrow-left pull-left" data-date="' . date_create($datestring)->modify('-1 week')->format(DateTime::ISO8601) . '"></a>
                        <div class="calendar-loading clearfix" style="height: 30px; width: 30px;"></div>
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th><a href="'. $this->createUrl('engScheduling/calendarEngineFeed') . '" class="arrow-right pull-right" data-date="' . date_create($datestring)->modify('+1 week')->format(DateTime::ISO8601) . '"></a></th>
                </tr>
                <tr>
                    <th style="border: none;"></th>';

        // Making table th tags - date headers

        $date = date('D, M d, Y');

        foreach ($period as $dt)
        {
            $weekdate = $dt->format('D, M d, Y');

            if ($date == $weekdate)
                $html .= '<th class="paddingBottom10 paddingTop10 today">' . $weekdate . '</th>';
            else
                $html .= '<th class="paddingBottom10 paddingTop10">' . $weekdate . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        // First row - Engine name and source

        foreach ($engineArray as $engines)
        {
            $html .= '<tr>';

            $model = current($engines);

            $html .= '<td class="engine-cell"><b>' . $model->engine_name . '</b><br />' . $model->engine->getEngineSource($model->engine_source);

            if ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE)
                $html .= '<br />(' . $model->engine->alliance_partner . ')';

            $html .= '</td>';

            // Loop through each day

            foreach ($period as $dt)
            {
                $timestamp_start = $dt->getTimestamp();
                $timestamp_end = $dt->modify('+ 23 hours 59 minutes')->getTimestamp();

                $html .= '<td>';

                // Loop through each engine if that engine is scheduled multiple times in one week

                $engine_id = null;
                $cell_rendered = false;
                $count_engines = count($engines);

                foreach ($engines as $index => $model)
                {
                    $model_start = strtotime($model->start_date);
                    $model_end = strtotime($model->end_date);

                    // Checking if the engine falls anywhere in this day regardless of hours - using unix timestamp comparisons

                    if (($model_start >= $timestamp_start && $model_start <= $timestamp_end) ||
                        ($model_end >= $timestamp_start   && $model_end <= $timestamp_end  ) ||
                        ($timestamp_start >= $model_start && $timestamp_start <= $model_end) ||
                        ($timestamp_end > $model_start    && $timestamp_end <= $model_end  ))
                    {
                        // Prevent engine with multiple clients not showing up multiple times on same day

                        if ($engine_id !== $model->id)
                        {
                            $html .= '<a href="'. $this->createUrl('engScheduling/engineModal', array('id' => $model->id)) .'" class="engine-calendar">
                                <div class="' . $this->calendarClassname($model->assignment) . '">' . $this->calendarTitle($model) . '</div>
                            </a>';

                            $engine_id = $model->id;
                            $cell_rendered = true;
                        }
                    }
                }

                // No cell rendered, offer new calendar entry
                if ($cell_rendered === false)
                {
                    // No engine on this day
                    $html .= ' <div class="engine-calendar-new">
                            <a href="' . $this->createUrl('engScheduling/create', array('id' => $model->engine_id, 'date' => date(DateTime::ISO8601, $timestamp_start))) . '"></a>
                        </div>';
                    // Add class to the last <td> tag
                    $html = substr_replace($html, '<td class="engine-new">', strrpos($html, '<td>'), 4);
                }

                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        echo $html;
    }

    private function calendarTitle($model)
    {
        $title = '<b>' . $model->assignment;
        if ($model->engineClient)
        {
            $title .= ' (<i>' . join('</i> / <i>', $model->client_names) . '</i>)';
        }
        if ($model->assignment === 'Response')
        {
            $title .= '<br />' . CHtml::encode($model->fire_name);
        }
        $title .= '</b><br />' . $model->city . ', ' . $model->state;
        $title .= '<br />RO ' . $model->resource_order_num;
        return $title;
    }

    private function calendarClassname($assignment)
    {
        switch ($assignment) {
            case EngScheduling::ENGINE_ASSIGNMENT_DEDICATED: return 'engine-dedicated';
            case EngScheduling::ENGINE_ASSIGNMENT_PRERISK: return 'engine-prerisk';
            case EngScheduling::ENGINE_ASSIGNMENT_RESPONSE: return 'engine-response';
            case EngScheduling::ENGINE_ASSIGNMENT_ONHOLD: return 'engine-onhold';
            case EngScheduling::ENGINE_ASSIGNMENT_STAGED: return 'engine-staged';
            case EngScheduling::ENGINE_ASSIGNMENT_OUTOFSERVICE: return 'engine-outofservice';
            case EngScheduling::ENGINE_ASSIGNMENT_INSTORAGE: return 'engine-instorage';
            default: return 'engine-dedicated';
        }
    }
}
