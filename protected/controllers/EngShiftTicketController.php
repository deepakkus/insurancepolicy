<?php

class EngShiftTicketController extends Controller
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
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetShiftTicket',
                        'apiGetShiftTicketsByIds',
                        'apiCreateShiftTicket',
                        'apiUpdateShiftTicket',
                        'apiGetShiftTickets',
                        'apiGetUncreatedShiftTickets',
                        'apiGetAllianceShiftTicketsQueryCount',
                        'apiGetAllianceShiftTickets',
                        'apiGetShiftTicketsPDF'
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
                'actions' => array(
                    'admin',
                    'review',
                    'shiftTicketTable',
                    'viewShiftTicketPDF'
                ),
                'users' => array('@')
            ),
            array('allow',
                'actions' => array(
                    'apiGetShiftTicket',
                    'apiGetShiftTicketsByIds',
                    'apiCreateShiftTicket',
                    'apiUpdateShiftTicket',
                    'apiGetShiftTickets',
                    'apiGetUncreatedShiftTickets',
                    'apiGetAllianceShiftTicketsQueryCount',
                    'apiGetAllianceShiftTickets',
                    'apiGetShiftTicketsPDF'
                ),
                'users' => array('*')
            ),
            array('deny',
                'users' => array('*')
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EngEngines the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=EngShiftTicket::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    public function actionAdmin($shiftTicketGridReset = false)
    {
        // ------- shift ticket calendar setup

        $date = null;

        if (isset($_SESSION['shift-tickets-table-date']))
        {
            $date = $_SESSION['shift-tickets-table-date'];
        }
        else
        {
            $date = date('Y-m-d');
        }

        $shiftTickets = EngShiftTicket::getShiftTickets($date);

        foreach ($shiftTickets as $index => $shiftTicket)
        {
            $shiftTicketStatuses = array();
            if ($shiftTicket['eng_shift_ticket_id'] !== null)
            {
                $shiftTicketStatuses = EngShiftTicketStatus::getStatusesForShiftTicket($shiftTicket['eng_shift_ticket_id']);
            }
            $shiftTickets[$index]['statuses'] = $shiftTicketStatuses;
        }

        $filterData = isset($_SESSION[EngShiftTicket::SHIFT_TICKET_FILTER]) ? $_SESSION[EngShiftTicket::SHIFT_TICKET_FILTER] : null;

        // ------- shift ticket grid setup

        $gridShiftTickets = new EngShiftTicket('search');
        $gridShiftTickets->unsetAttributes();

        if ($shiftTicketGridReset !== false)
        {
            unset($_SESSION['shiftTicketGridFilters'],
            $_SESSION['shiftTicketGridColumnsToShow'],
            $_SESSION['shiftTicketGridPageSize'],
            $_SESSION['shiftTicketGridSort'],
            $_SESSION['shiftTicketGridAdvSearch'],
            $_COOKIE['shiftTicketGridFilters'],
            $_COOKIE['shiftTicketGridColumnsToShow'],
            $_COOKIE['shiftTicketGridPageSize'],
            $_COOKIE['shiftTicketGridSort'],
            $_COOKIE['shiftTicketGridAdvSearch']);
            $this->redirect(array('admin'));
        }

        if (isset($_GET['EngShiftTicket']))
        {
            $gridShiftTickets->attributes = $_GET['EngShiftTicket'];
            $_SESSION['shiftTicketGridFilters'] = $_GET['EngShiftTicket'];
        }
        elseif (isset($_SESSION['shiftTicketGridFilters']))
        {
            $gridShiftTickets->attributes = $_SESSION['shiftTicketGridFilters'];
        }

        // grid column order
        $shiftTicketGridColumnOrder = array(
            100 => 'id',
            200 => 'date',
            250 => 'submitted_by_user_id',
            300 => 'completedStatuses',
            400 => 'user_id',
            430 => 'activities',
            440 => 'totalActivityTime',
            444 => 'fire_name',
            445 => 'eng_schedule_clients',
            450 => 'eng_schedule_assignment',
            460 => 'eng_engine_name',
            470 => 'eng_schedule_ro',
            480 => 'eng_schedule_crew',
            485 => 'start_location',
            486 => 'end_location',
            490 => 'totalMiles',
            500 => 'start_miles',
            600 => 'end_miles',
            //700 => 'safety_meeting',
            800 => 'safety_meeting_comments',
            801 => 'equipment'
        );

        // default cols
        $shiftTicketGridColumnsToShow = array(
            100 => 'id',
            200 => 'date',
            250 => 'submitted_by_user_id',
            300 => 'completedStatuses',
            400 => 'user_id',
            430 => 'activities',
            440 => 'totalActivityTime',
            444 => 'fire_name',
            445 => 'eng_schedule_clients',
            450 => 'eng_schedule_assignment',
            460 => 'eng_engine_name',
            470 => 'eng_schedule_ro',
            490 => 'totalMiles'
        );

        $defaultActivitySubColumns = EngShiftTicketActivityType::model()->getAllTypes();
        $shiftTicketGridSubColumnsToShow = array();

        foreach($defaultActivitySubColumns as $defaultActivity)
        {
            $shiftTicketGridSubColumnsToShow[] = $defaultActivity->id;
        }

        // remembered Total ActivityTime sub columns setup
        if (isset($_GET['activitytypes']))
        {
            $_SESSION['activitytypes'] = $_GET['activitytypes'];
            $_COOKIE['activitytypes'] = $_GET['activitytypes'];
            $shiftTicketGridSubColumnsToShow = $_GET['activitytypes'];
        }
        elseif (isset($_SESSION['activitytypes']))
        {
            $shiftTicketGridSubColumnsToShow = $_SESSION['activitytypes'];
        }
        elseif (isset($_COOKIE['activitytypes']))
        {
            $shiftTicketGridSubColumnsToShow = $_COOKIE['activitytypes'];
        }

        $activityTypes = "";
        foreach ($shiftTicketGridSubColumnsToShow as $activity_type)
        {
            $activityTypes .= $activity_type.',';
        }
        $activityTypes = trim($activityTypes,",");

        // remembered columns setup
        if (isset($_GET['shiftTicketGridColumnsToShow']))
        {
            $_SESSION['shiftTicketGridColumnsToShow'] = $_GET['shiftTicketGridColumnsToShow'];
            $_COOKIE['shiftTicketGridColumnsToShow'] = $_GET['shiftTicketGridColumnsToShow'];
            $shiftTicketGridColumnsToShow = $_GET['shiftTicketGridColumnsToShow'];
        }
        elseif (isset($_SESSION['shiftTicketGridColumnsToShow']))
        {
            $shiftTicketGridColumnsToShow = $_SESSION['shiftTicketGridColumnsToShow'];
        }
        elseif (isset($_COOKIE['shiftTicketGridColumnsToShow']))
        {
            $shiftTicketGridColumnsToShow = $_COOKIE['shiftTicketGridColumnsToShow'];
        }

        // Default grid page size
        $shiftTicketGridPageSize = 25;
        // pagesize remembered setup
        if (isset($_GET['shiftTicketGridPageSize']))
        {
            $_SESSION['shiftTicketGridPageSize'] = $_GET['shiftTicketGridPageSize'];
            $_COOKIE['shiftTicketGridPageSize'] = $_GET['shiftTicketGridPageSize'];
            $shiftTicketGridPageSize = $_GET['shiftTicketGridPageSize'];
        }
        elseif (isset($_SESSION['shiftTicketGridPageSize']))
        {
            $shiftTicketGridPageSize = $_SESSION['shiftTicketGridPageSize'];
        }
        elseif (isset($_COOKIE['shiftTicketGridPageSize']))
        {
            $shiftTicketGridPageSize = $_COOKIE['shiftTicketGridPageSize'];
        }

        // Default Shift Ticket Grid Sort
        $shiftTicketGridSort = 'id DESC';
        // Remembered Sort Setup
        if (isset($_GET['Property_sort']))
        {
            $_SESSION['shiftTicketGridSort'] = $_GET['shiftTicketGridSort'];
            $_COOKIE['shiftTicketGridSort'] = $_GET['shiftTicketGridSort'];
            $sort = $_GET['shiftTicketGridSort'];
        }
        elseif (isset($_SESSION['shiftTicketGridSort']))
        {
            $sort = $_SESSION['shiftTicketGridSort'];
        }
        elseif (isset($_COOKIE['shiftTicketGridSort']))
        {
            $sort = $_COOKIE['shiftTicketGridSort'];
        }

        // Advanced search Setup
        $shiftTicketGridAdvSearch = NULL;
        if (isset($_GET['shiftTicketGridAdvSearch']))
        {
            $_SESSION['shiftTicketGridAdvSearch'] = $_GET['shiftTicketGridAdvSearch'];
            $_COOKIE['shiftTicketGridAdvSearch'] = $_GET['shiftTicketGridAdvSearch'];
            $shiftTicketGridAdvSearch = $_GET['shiftTicketGridAdvSearch'];
        }
        elseif (isset($_SESSION['shiftTicketGridAdvSearch']))
        {
            $shiftTicketGridAdvSearch = $_SESSION['shiftTicketGridAdvSearch'];
        }
        elseif (isset($_COOKIE['shiftTicketGridAdvSearch']))
        {
            $shiftTicketGridAdvSearch = $_COOKIE['shiftTicketGridAdvSearch'];
        }
        else
        {
            $shiftTicketGridAdvSearch = array();
            $shiftTicketGridAdvSearch['statuses'] = array();
            $shiftTicketGridAdvSearch['dateBegin'] = NULL;
            $shiftTicketGridAdvSearch['dateEnd'] = NULL;
        }

        return $this->render('admin', array(
            // calendar vars
            'filterData' => $filterData,
            'shiftTickets' => $shiftTickets,
            'date' => $date,
            // grid vars
            'gridShiftTickets' => $gridShiftTickets,
            'shiftTicketGridColumnsToShow' => $shiftTicketGridColumnsToShow,
            'shiftTicketGridPageSize' => $shiftTicketGridPageSize,
            'shiftTicketGridAdvSearch' => $shiftTicketGridAdvSearch,
            'shiftTicketGridSort' => $shiftTicketGridSort,
            'shiftTicketGridColumnOrder' => $shiftTicketGridColumnOrder,
            'activityTypes' => $activityTypes,
            'shiftTicketGridSubColumnsToShow' => $shiftTicketGridSubColumnsToShow
        ));
    }

    /**
     * Review a shift ticket
     * @param integer $id ID of shift ticket
     * @return mixed
     */
    public function actionReview($id)
    {
        $shiftTicket = EngShiftTicket::model()->find(array(
            'condition' => 't.id = :id',
            'params' => array(':id' => $id),
            'with' => array(
                'engScheduling.resourceOrder' => array(
                    'select' => array('id')
                ),
                'engScheduling.engineClient.client' => array(
                    'select' => array('id','name')
                ),
                'engScheduling.fire' => array(
                    'select' => array('Fire_ID','Name')
                ),
                'engScheduling.engine' => array(
                    'select' => array('id','engine_name')
                ),
                'engScheduling.engine.alliancepartner' => array(
                    'select' => array('id','name')
                )
            )
        ));

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'shift-ticket-review-form')
        {
            echo CActiveForm::validate($shiftTicket);
            Yii::app()->end();
        }

        if (isset($_POST['EngShiftTicket']))
        {
            $shiftTicket->attributes = $_POST['EngShiftTicket'];
            $shiftTicket->user_id = Yii::app()->user->id;
            if ($shiftTicket->save())
            {
                // Save statuses
                if (isset($_POST['CompletedStatuses']))
                {
                    $shiftTicketStatuses = EngShiftTicketStatus::model()->findAllByAttributes(array('shift_ticket_id' => $shiftTicket->id));

                    foreach ($shiftTicketStatuses as $status)
                    {
                        // Status was checked
                        if (in_array($status->status_type_id, $_POST['CompletedStatuses']['status']))
                        {
                            // Already checked, no action ...
                            if ($status->completed == 1)
                            {
                                continue;
                            }
                            // Needs to be saved as completed
                            else
                            {
                                $status->completed_by_user_id = Yii::app()->user->id;
                                $status->completed = 1;
                            }
                        }
                        // Status was not checked
                        else
                        {
                            // Status is currently completed, need to undo
                            if ($status->completed == 1)
                            {
                                $status->completed_by_user_id = null;
                                $status->completed = 0;
                            }
                            // Status is still unchecked, no action taken.
                            else
                            {
                                continue;
                            }
                        }

                        $status->save();
                    }
                }

                return $this->redirect(array('admin'));
            }
        }

        $shiftTicketActivities = EngShiftTicketActivity::model()->findAll(array(
            'condition' => 'eng_shift_ticket_id = :id',
            'params' => array(':id' => $shiftTicket->id),
            'with' => array(
                'engShiftTicketActivityType' => array(
                    'select' => 'type'
                )
            ),
            'order' => 'start_time ASC'
        ));

        $shiftTicketNotes = EngShiftTicketNotes::model()->findAll(array(
            'condition' => 'eng_shift_ticket_id = :id',
            'params' => array(':id' => $shiftTicket->id),
            'with' => array(
                'user' => array(
                    'select' => 'id,name'
                )
            ),
            'order'=>'t.id DESC'
        ));

        return $this->render('review', array(
            'shiftTicket' => $shiftTicket,
            'shiftTicketActivities' => $shiftTicketActivities,
            'shiftTicketNotes' => $shiftTicketNotes
        ));
    }

    /**
     * Ajax call to get table contents for shift tickets
     * @param string $date
     * @param array|null $filterData
     * @return string
     */
    public function actionShiftTicketTable($date, $filterData = null)
    {
        $_SESSION['shift-tickets-table-date'] = $date;

        $shiftTickets = EngShiftTicket::getShiftTickets($date, $filterData);

        foreach ($shiftTickets as $index => $shiftTicket)
        {
            $shiftTicketStatuses = array();
            if ($shiftTicket['eng_shift_ticket_id'] !== null)
            {
                $shiftTicketStatuses = EngShiftTicketStatus::getStatusesForShiftTicket($shiftTicket['eng_shift_ticket_id']);
            }
            $shiftTickets[$index]['statuses'] = $shiftTicketStatuses;
        }

        return $this->renderPartial('_shift_ticket_table', array(
            'shiftTickets' => $shiftTickets,
            'date' => $date
        ));
    }

    /**
     * API Method: engShiftTicket/apiGetShiftTicket
     * Description: Get shift ticket model instance
     *
     * Post data parameters:
     * @param integer id
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 37
     *     }
     * }
     */
    public function actionApiGetShiftTicket()
    {
        $data = null;

        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        $shiftTicket = EngShiftTicket::model()->findByPk($data['id']);
        $schedule = EngScheduling::model()->findByPk($shiftTicket->eng_scheduling_id);
        $returnData = array(
            'id' => $shiftTicket->id,
            'date' => $shiftTicket->date,
            'start_miles' => $shiftTicket->start_miles,
            'end_miles' => $shiftTicket->end_miles,
            'safety_meeting_comments' => $shiftTicket->safety_meeting_comments,
            'eng_scheduling_id' => $shiftTicket->eng_scheduling_id,
            'assignment' => isset($schedule) ? $schedule->assignment : '',
            'clients' => isset($schedule) ? implode(', ',$schedule->client_names) : '',
            'fire_name' => isset($schedule) ? $schedule->fire_name : '',
            'ro' => isset($schedule) ? $schedule->resource_order_num : '',
            'engine_name' => isset($schedule) ? $schedule->engine_name : '',
            'alliance_partner' => isset($schedule) ? $schedule->engine_alliance_partner : '',
            'crew' => isset($schedule) ? implode(', ', $schedule->crew_names) : '',
            'crew_type' => isset($schedule) ? implode(', ', $schedule->crew_types) : '',
            'location' => isset($schedule) ? $schedule->city.', '.$schedule->state : '',
            'start_location' => $shiftTicket->start_location,
            'end_location' => $shiftTicket->end_location,
            'equipment_check' => $shiftTicket->equipment_check,
        );

        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $returnData));
    }

    /**
     * API Method: engShiftTicket/apiCreateShiftTicket
     * Description: Create a shift ticket
     *
     * Post data parameters:
     * @param string date
     * @param integer start_miles
     * @param integer end_miles
     * @param string safety_meeting_comments
     * @param integer user_id
     * @param integer crew_id
     * @param integer eng_scheduling_id
     * @param string start_location
     * @param string end_location
     *
     * Post data example:
     * {
     *     "data": {
     *         "date": "2016-12-26",
     *         "user_id": 854,
     *         "eng_scheduling_id": 111,
     *     }
     * }
     */
    public function actionApiCreateShiftTicket()
    {
        $data = null;

        $requiredFields = array('date','user_id','eng_scheduling_id');

        if (!WDSAPI::getInputDataArray($data, $requiredFields))
            return;

        $shiftTicket = new EngShiftTicket;
        $shiftTicket->scenario = 'create';

        $shiftTicket->date = $data['date'];
        $shiftTicket->user_id = $data['user_id'];
        $shiftTicket->submitted_by_user_id = $data['user_id'];
        $shiftTicket->eng_scheduling_id = $data['eng_scheduling_id'];

        $returnArray = array(
            'error' => 1,
            'data' => null
        );

        if ($shiftTicket->save())
        {
            $returnArray['error'] = 0;
            $returnArray['data']['id'] = $shiftTicket->id;
        }
        else
        {
            return WDSAPI::echoJsonError('There was an issue saving the shift ticket', var_export($shiftTicket->getErrors(), true));
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engShiftTicket/apiUpdateShiftTicket
     * Description: Updates a shift ticket
     *
     * Post data parameters:
     * @param integer id
     * @param integer start_miles
     * @param integer end_miles
     * @param string safety_meeting_comments
     * @param integer user_id
     * @param integer submitted
     * @param string start_location
     * @param string end_location
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": "4076",
     *         "start_miles": "50",
     *         "end_miles": "350",
     *         "safety_meeting_comments": "be safe",
     *         "user_id": 854,
     *         "submitted": 0,
     *         "start_location": "Denver, CO",
     *         "end_location": "Ft. Colins, CO"
     *     }
     * }
     */
    public function actionApiUpdateShiftTicket()
    {
        $data = null;

        if (!WDSAPI::getInputDataArray($data, array('id','start_miles','end_miles','safety_meeting_comments','user_id', 'submitted', 'start_location', 'end_location','equipment_check')))
            return;

        $shiftTicket = EngShiftTicket::model()->findByPk($data['id']);
        $shiftTicket->scenario = 'update';

        //Date and Schduling ID should already be set and should not ever be updated after creating.
        //other "updatable" params
        $shiftTicket->start_miles = $data['start_miles'];
        $shiftTicket->end_miles = $data['end_miles'];
        $shiftTicket->safety_meeting_comments = $data['safety_meeting_comments'];
        $shiftTicket->user_id = $data['user_id'];
        $shiftTicket->start_location = $data['start_location'];
        $shiftTicket->end_location = $data['end_location'];
        $shiftTicket->equipment_check = $data['equipment_check'];

        if ($data['submitted'] == 1)
        {
            $shiftTicket->submitted_by_user_id = $data['user_id'];

            // Shift ticket has been submitted, wipe any previous statuses
            EngShiftTicketStatus::model()->updateAll(array('completed' => '0'), 'shift_ticket_id = :shift_ticket_id', array(':shift_ticket_id' => $shiftTicket->id));

            // Get reference to the submitted status
            $submittedStatus = EngShiftTicketStatus::model()->find(array(
                'condition' => "shift_ticket_id = :shift_ticket_id AND status_type_id = (SELECT id FROM eng_shift_ticket_status_type WHERE [type] = 'Submitted')",
                'params' => array(':shift_ticket_id' => $shiftTicket->id)
            ));

            $submittedStatus->completed = 1;
            $submittedStatus->completed_by_user_id = $data['user_id'];
            $submittedStatus->save();
        }

        $returnArray = array(
            'error' => 1,
            'data' => array(),
        );

        if ($shiftTicket->save())
        {
            $returnArray = array(
                'error' => 0,
                'data' => array(),
            );
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engShiftTicket/apiGetShiftTickets
     * Description: Get all Submitted or Unsubmitted shift tickets for a given crew member
     *
     * Post data parameters:
     * @param integer user_id
     * @param bool submitted
     *
     * Post data example:
     * {
     *     "data": {
     *         "user_id" : 17,
     *         "submitted" : true,
     *     }
     * }
     */
    public function actionApiGetShiftTickets()
    {
        $data = null;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('user_id', 'submitted')))
            return;

        $condition = "submitted_by_user_id = :user_id";
        $order = "[date] DESC";
        $params = array(':user_id' => $data['user_id'], );

        $shiftTickets = EngShiftTicket::model()->with('engScheduling')->findAll(array('condition'=>$condition, 'order'=>$order, 'params'=>$params));

        //create return array
        foreach($shiftTickets as $shiftTicket)
        {
            if($data['submitted'] == $shiftTicket->isSubmitted)
            {
                $assignment = 'not set';
                $engineName = $fireName = $location = $clients = '';
                if(isset($shiftTicket->engScheduling))
                {
                    $schedule = EngScheduling::model()->with('engine','fire')->findByPk($shiftTicket->eng_scheduling_id);
                    $engineName = $schedule->engine->engine_name;
                    $fireName = isset($schedule->fire) ? $schedule->fire->Name : '';
                    $location = isset($schedule->fire) ? ($schedule->fire->City.', '.$schedule->fire->State) : '';
                    $assignment = $schedule->assignment;
                    $clients = implode(',',$schedule->client_names);
                }
                $returnData[] = array(
                    'id' => $shiftTicket['id'],
                    'date' => $shiftTicket['date'],
                    'engineName' => $engineName,
                    'assignment' => $assignment,
                    'fireName' => $fireName,
                    'location' => $location,
                    'clients' => $clients,
                    'isSubmitted' => $shiftTicket->isSubmitted,
                );
            }
        }

        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => array_values($returnData)));
    }

    /**
     * API Method: engShiftTicket/apiGetUncreatedShiftTickets
     * Description: Get all [recent] avail shift tickets for a given crew member that have not yet been created.
     *              These are by date, by assignment for the given crew member that do not yet have a ST created for them from the past 3 days and one day in the future
     *
     * Post data parameters:
     * @param integer user_id
     * @return object json object with array of results in data field
     *
     * Post data example:
     * {
     *     "data": {
     *         "user_id" : 17,
     *     }
     * }
     *
     * Return data example:
     * {
     *     "error" : 0,
     *     "data" : [
     *
     */
    public function actionApiGetUncreatedShiftTickets()
    {
        $data = null;
        $returnData = array();
        if (!WDSAPI::getInputDataArray($data, array('user_id')))
            return;

        //we want to show assignments for the last 3 days plus tomorrow
        $startDate = new DateTime('-3 days');
        $endDate = new DateTime('+2 days');
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($startDate, $interval, $endDate);

        //get crew id
        $crew = EngCrewManagement::model()->findByAttributes(array('user_id'=>$data['user_id']));

        foreach($period as $day)
        {
            //look up all the engine schedules (assignments) this crew member was on for this day that don't already have a ST created for them
            $wherePart = "
                CONVERT(DATE, :date) BETWEEN CONVERT(DATE, t.[start_date]) AND CONVERT(DATE, t.[end_date])
                AND t.id IN (SELECT engine_scheduling_id FROM eng_scheduling_employee WHERE crew_id = :crew_id)
            ";
            $schedules = EngScheduling::model()->with('engine','fire')->findAll($wherePart, array(':crew_id'=>$crew->id, 'date'=>$day->format('Y-m-d')));

            //usually only one of these, but need a loop for possibly multi assignment day
            foreach($schedules as $schedule)
            {
                //check if it already has a shift ticket
                $existingSTCondition = 'eng_scheduling_id = :schedule_id AND [date] = :date';
                $existingSTParams = array(':schedule_id'=>$schedule->id, ':date'=>$day->format('Y-m-d'));
                if( !EngShiftTicket::model()->exists($existingSTCondition, $existingSTParams) )
                {
                    //ST Doesn't exist yet for this schedule (assignment) for this day, so add it to the return array
                    $engineName = $schedule->engine->engine_name;
                    $fireName = isset($schedule->fire) ? $schedule->fire->Name : '';
                    $location = isset($schedule->fire) ? ($schedule->fire->City.', '.$schedule->fire->State) : '';
                    $assignment = $schedule->assignment;
                    $clients = implode(',',$schedule->client_names);

                    $returnData[] = array(
                        'eng_scheduling_id' => $schedule->id,
                        'date' => $day->format('Y-m-d'),
                        'engineName' => $engineName,
                        'assignment' => $assignment,
                        'fireName' => $fireName,
                        'location' => $location,
                        'clients' => $clients,
                    );
                }
            }
        }

        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => array_values($returnData)));
    }

    /**
     * API Method: engShiftTicket/apiGetAllianceShiftTicketsQueryCount
     * Description: Gets count alliance shift ticket data for a given alliance id
     *
     * Post data parameters:
     * @param string allianceID
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "allianceID": "6",
     *         "compareArray": {
     *             "column1": "text",
     *             "column2": "text"
     *         }
     *     }
     * }
     */
    public function actionApiGetAllianceShiftTicketsQueryCount()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('allianceID','startDate','endDate')))
            return;

        $sql = "
            SELECT COUNT(DISTINCT st.id)
            FROM eng_shift_ticket st
            INNER JOIN eng_scheduling es ON st.eng_scheduling_id = es.id
            INNER JOIN eng_engines e ON es.engine_id = e.id
            INNER JOIN alliance a ON e.alliance_id = a.id
            LEFT OUTER JOIN res_fire_name f ON es.fire_id = f.Fire_ID
            -- Join each shift ticket on it's completed boolean status
            LEFT OUTER JOIN (
                SELECT id, CASE
                    WHEN 0 IN (
                        SELECT s.completed FROM eng_shift_ticket_status s
                        INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                        WHERE s.shift_ticket_id = st.id AND (t.disabled != 1 OR t.disabled IS NULL)
                    ) THEN 0 ELSE 1 END completed
                FROM eng_shift_ticket st
            ) completed ON st.id = completed.id
            WHERE es.assignment = 'Response'
                AND completed.completed = 1
                AND a.id = :alliance_id";

        $params = array();
        $params[':alliance_id'] = $data['allianceID'];

        // Advanced Searching

        if (!empty($data['startDate']))
        {
            $sql .= ' AND st.date >= :start_date';
            $params[':start_date'] = date('Y-m-d', strtotime($data['startDate']));
        }

        if (!empty($data['endDate']))
        {
            $sql .= ' AND st.date <= :end_date';
            $params[':end_date'] = date('Y-m-d', strtotime($data['endDate']));
        }

        // Searching

        foreach ($data['compareArray'] as $key => $value)
        {
            if ($key === 'date')
            {
                $sql .= " AND :1$key >= CONVERT(DATE,n.date) AND :2$key < CONVERT(DATE,DATEADD(DAY,1,n.date))";
                $params[":1$key"] = $value;
                $params[":2$key"] = $value;
            }
            else if ($key === 'engine_name')
            {
                $sql .= " AND e.engine_name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'fire_name')
            {
                $sql .= " AND f.Name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'ro')
            {
                $sql .= " AND es.resource_order_id = :$key";
                $params[":$key"] = "%$value%";
            }
        }

        $count = Yii::app()->db->createCommand($sql)->queryScalar($params);

        $returnArray['data'] = $count;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engShiftTicket/apiGetAllianceShiftTickets
     * Description: Gets alliance shift ticket data for a given alliance id
     *
     * Post data parameters:
     * @param string allianceID
     * @param integer limit - used for limiting number of results
     * @param integer offset - used for pagination
     * @param array sortArray - associative array of column => SORT_ASC/SORT_DESC for sorting
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "allianceID": "6",
     *         "limit": 20,
     *         "offset": 60,
     *         "sortArray": {
     *             "date": 3
     *         },
     *         "compareArray": {
     *             "column1": "text",
     *             "column2": "text"
     *         }
     *     }
     * }
     */
    public function actionApiGetAllianceShiftTickets()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('allianceID','startDate','endDate')))
            return;

        $sortDirection = function($sort)
        {
            return ($sort == SORT_ASC) ? 'ASC' : 'DESC';
        };

        $sql = "
            SELECT
                st.id,
                st.date,
                e.engine_name,
                f.Name fire_name,
                es.resource_order_id ro
            FROM eng_shift_ticket st
            INNER JOIN eng_scheduling es ON st.eng_scheduling_id = es.id
            INNER JOIN eng_engines e ON es.engine_id = e.id
            INNER JOIN alliance a ON e.alliance_id = a.id
            LEFT OUTER JOIN res_fire_name f ON es.fire_id = f.Fire_ID
            LEFT OUTER JOIN (
                SELECT id, CASE
                    WHEN 0 IN (
                        SELECT s.completed FROM eng_shift_ticket_status s
                        INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                        WHERE s.shift_ticket_id = st.id AND (t.disabled != 1 OR t.disabled IS NULL)
                    ) THEN 0 ELSE 1 END completed
                FROM eng_shift_ticket st
            ) completed ON st.id = completed.id
            WHERE es.assignment = 'Response'
                AND completed.completed = 1
                AND a.id = :alliance_id
            ";

        $params = array();
        $params[':alliance_id'] = $data['allianceID'];

        // Advanced Searching

        if (!empty($data['startDate']))
        {
            $sql .= ' AND st.date >= :start_date';
            $params[':start_date'] = date('Y-m-d', strtotime($data['startDate']));
        }

        if (!empty($data['endDate']))
        {
            $sql .= ' AND st.date <= :end_date';
            $params[':end_date'] = date('Y-m-d', strtotime($data['endDate']));
        }

        // Searching

        foreach ($data['compareArray'] as $key => $value)
        {
            if ($key === 'date')
            {
                $sql .= " AND :1$key >= CONVERT(DATE,n.date) AND :2$key < CONVERT(DATE,DATEADD(DAY,1,n.date))";
                $params[":1$key"] = $value;
                $params[":2$key"] = $value;
            }
            else if ($key === 'engine_name')
            {
                $sql .= " AND e.engine_name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'fire_name')
            {
                $sql .= " AND f.Name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'ro')
            {
                $sql .= " AND es.resource_order_id = :$key";
                $params[":$key"] = "$value";
            }
        }

        // Sorting

        if (count($data['sortArray']) > 0)
        {
            $sortArray = array();
            foreach ($data['sortArray'] as $key => $sort)
            {
                if ($key === 'date')
                    $sortArray[] = 'st.date ' . $sortDirection($sort);
                else if ($key === 'engine_name')
                    $sortArray[] = 'e.engine_name ' . $sortDirection($sort);
                else if ($key === 'fire_name')
                    $sortArray[] = 'f.Name ' . $sortDirection($sort);
                else if ($key === 'ro')
                    $sortArray[] = 'es.resource_order_id ' . $sortDirection($sort);
            }

            $sql .= ' ORDER BY ' . implode(',', $sortArray);
        }

        // Limit - Offset

        $offset  = isset($data['offset']) ? (int)$data['offset'] : -1;
        $limit  = isset($data['limit']) ? (int)$data['limit'] : -1;

        if ($limit > 0 && $offset <= 0)
        {
            $sql .= " OFFSET 0 ROWS FETCH NEXT $limit ROWS ONLY";
        }
        else if ($limit > 0 && $offset > 0)
        {
            $sql .= " OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
        }
        else if ($limit <= 0 && $offset > 0)
        {
            $sql .= " OFFSET $offset ROWS";
        }

        $results = Yii::app()->db->createCommand($sql)->queryAll(true, $params);

        $returnArray['data'] = $results;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * View a PDF of the shift ticket associated with the ids passed in
     * @param string[] $ids
     */
    public function actionViewShiftTicketPDF($ids)
    {
        $ids = json_decode($ids, true);

        EngShiftTicket::model()->downloadShiftTicketPDFs($ids);
        Yii::app()->end();
    }

    /**
     * API Method: engShiftTicket/apiGetShiftTicketsPDF
     * Description: Get shift tickets in PDF format for the given string array of shift ticket ids
     *
     * Post data parameters:
     * @param string[] ids - shift ticket ids
     *
     * Post data example:
     * {
     *     "data": {
     *         "ids" : [
     *             "732",
     *             "733"
     *         ]
     *     }
     * }
     */
    public function actionApiGetShiftTicketsPDF()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('ids')))
            return;

        $filepath = EngShiftTicket::model()->downloadShiftTicketPDFs($data['ids'], false);
        $fp = fopen($filepath, 'rb');
        $content = fread($fp, filesize($filepath));
        $content = unpack('H*hex', $content)['hex'];
        fclose($fp);

        $returnArray = array(
            'error' => 0,
            'data' => array()
        );

        if ($content)
        {
            $returnArray['data']['name'] = 'Shift Tickets ' . date('Y-m-d H:i') . '.pdf';
            $returnArray['data']['type'] = 'application/pdf';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}
