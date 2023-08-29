<?php

/**
 * This is the model class for table "eng_shift_ticket".
 *
 * The followings are the available columns in table 'eng_shift_ticket':
 * @property integer $id
 * @property string $date
 * @property string $start_miles
 * @property string $end_miles
 * @property string $safety_meeting_comments
 * @property integer $locked
 * @property integer $user_id
 * @property integer $submitted_by_user_id
 * @property integer $eng_scheduling_id
 * @property string $start_location
 * @property string $end_location
 * @property string $equipment_check
 *
 * The followings are the available model relations:
 * @property EngScheduling $engScheduling
 * @property EngShiftTicketStatus[] $statuses
 * @property EngShiftTicketActivity[] $engShiftTicketActivities
 */
class EngShiftTicket extends CActiveRecord
{
    const SHIFT_TICKET_FILTER = 'shift-ticket-table-filter';

    // virtual/relation attributes
    public $status;
    public $submittedBy;
    public $lastUpdatedBy;
    public $totalActivityTime;
    public $activities;
    public $eng_schedule_assignment;
    public $eng_engine_name;
    public $eng_schedule_ro;
    public $eng_schedule_crew = array();
    public $eng_schedule_clients = array();
    public $fire_name;
    public $completedStatuses = array();
    public $isSubmitted;
    public $eng_schedule_crew_type = array();
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_shift_ticket';
    }

    public function behaviors()
    {
        return array(
            'history' => array(
                'class' => 'HistoryBehavior',
                'historyBehaviorCallback' => array($this, 'historyBehaviorCallback'),
                'historyBehaviorCallbackUserID' => array($this, 'historyBehaviorCallbackUserID')
            )
        );
    }

    public function historyBehaviorCallback()
    {
        $historyAttributes = $this->attributes;
        $historyAttributes['activities'] = array();
        $historyAttributes['statuses'] = array();
        $historyAttributes['notes'] = array();
        foreach ($this->engShiftTicketActivities as $activity)
            $historyAttributes['activities'][] = $activity->attributes;
        foreach ($this->statuses as $status)
            $historyAttributes['statuses'][] = $status->attributes;
        foreach ($this->notes as $note)
            $historyAttributes['notes'][] = $note->attributes;

        return json_encode($historyAttributes);
    }

    public function historyBehaviorCallbackUserID()
    {
        return $this->user_id;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('date, eng_scheduling_id, submitted_by_user_id', 'required'),
            array('safety_meeting_comments, start_miles, end_miles, start_location, end_location, equipment_check', 'required', 'on' => 'update'),
            array('locked, user_id, submitted_by_user_id, eng_scheduling_id', 'numerical', 'integerOnly' => true),
            array('start_miles, end_miles', 'numerical', 'integerOnly' => true, 'min' => -1),
            array('start_miles, end_miles', 'checkMilesLogic'),
            array('date', 'type', 'type'=>'datetime', 'datetimeFormat'=>'yyyy-MM-dd'),
            array('safety_meeting_comments, equipment_check', 'length', 'max' => 500),
            array('start_location, end_location', 'length', 'max' => 50),
            array('id, date, start_miles, end_miles, locked, user_id, submitted_by_user_id, eng_scheduling_id, eng_schedule_assignment, eng_schedule_clients, eng_schedule_crew, eng_schedule_ro, fire_name, eng_engine_name, completedStatuses, start_location, end_location', 'safe', 'on' => 'search')
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'engScheduling' => array(self::BELONGS_TO, 'EngScheduling', 'eng_scheduling_id'),
            'statuses' => array(self::HAS_MANY, 'EngShiftTicketStatus', 'shift_ticket_id'),
            'notes' => array(self::HAS_MANY, 'EngShiftTicketNotes', 'eng_shift_ticket_id'),
            'engShiftTicketActivities' => array(self::HAS_MANY, 'EngShiftTicketActivity', 'eng_shift_ticket_id'),
            'submittedByUser' => array(self::BELONGS_TO, 'User', 'submitted_by_user_id'),
            'lastUpdatedByUser' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'date' => 'Date',
            'start_miles' => 'Start Miles',
            'end_miles' => 'End Miles',
            'safety_meeting_comments' => 'Safety Meeting Comments',
            'locked' => 'Locked',
            'user_id' => 'Last Updated By',
            'submitted_by_user_id' => 'Crew Member',
            'eng_scheduling_id' => 'Schedule',
            'totalActivityTime' => 'Total Activity Time',
            'totalMiles' => 'Total Miles',
            'activities' => 'Activities',
            'eng_schedule_assignment' => 'Assignment',
            'eng_engine_name' => 'Engine',
            'eng_schedule_ro' => 'RO #',
            'eng_schedule_crew' => 'Engine Crew',
            'eng_schedule_clients' => 'Client(s)',
            'fire_name' => 'Fire',
            'start_location' => 'Start Location',
            'end_location' => 'End Location',
            'equipment_check' => 'Equipment Check',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($shiftTicketGridAdvSearch = null, $shiftTicketGridPageSize = 20, $shiftTicketGridSort = null)
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;
        $criteria->with = array('submittedByUser', 'lastUpdatedByUser', 'engShiftTicketActivities', 'engScheduling');

        $criteria->compare('t.id', $this->id);
        $criteria->compare('date',$this->date);
        $criteria->compare('start_miles', $this->start_miles, true);
        $criteria->compare('end_miles', $this->end_miles, true);
        $criteria->compare('safety_meeting_comments', $this->safety_meeting_comments, true);
        $criteria->compare('locked', $this->locked);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('submitted_by_user_id', $this->submitted_by_user_id);
        $criteria->compare('engScheduling.assignment', $this->eng_schedule_assignment);
        $criteria->compare('engScheduling.resource_order_id', $this->eng_schedule_ro,true);
        $criteria->compare('start_location', $this->start_location, true);
        $criteria->compare('end_location', $this->end_location, true);
        $criteria->compare('equipment_check', $this->equipment_check, true);
        $criteria->join = '';
        if($this->eng_schedule_clients)
        {
            $criteria->join .= ' INNER JOIN eng_scheduling_client AS c ON c.engine_scheduling_id = t.eng_scheduling_id ';
            $criteria->addCondition('c.client_id = :client_id');
            $criteria->params[':client_id'] = $this->eng_schedule_clients;
        }
        if($this->eng_schedule_crew)
        {
            $criteria->join .= ' INNER JOIN eng_scheduling_employee AS e ON e.engine_scheduling_id = t.eng_scheduling_id ';
            $criteria->addCondition('e.crew_id = ' . $this->eng_schedule_crew);
        }
        if($this->eng_engine_name)
        {
            $criteria->join .= ' INNER JOIN eng_engines AS eng ON eng.id = (SELECT engine_id FROM eng_scheduling WHERE id = t.eng_scheduling_id) ';
            $criteria->addCondition("eng.engine_name = '" . $this->eng_engine_name . "'");
        }
        if($this->fire_name)
        {
            $criteria->join .= ' INNER JOIN res_fire_name AS f ON f.Fire_ID = (SELECT fire_id FROM eng_scheduling WHERE id = t.eng_scheduling_id) ';
            $criteria->addCondition("f.Name LIKE '%" . $this->fire_name . "%'");
        }

        if($this->completedStatuses)
        {
            $criteria->join .= ' INNER JOIN eng_shift_ticket_status AS status ON status.shift_ticket_id = t.id';
            $criteria->addCondition('status.status_type_id = :status_type_id AND status.completed = 1');
            $criteria->params[':status_type_id'] = $this->completedStatuses;
        }

        if(isset($shiftTicketGridAdvSearch['dateBegin'], $shiftTicketGridAdvSearch['dateEnd']))
        {
            $criteria->addBetweenCondition('date', $shiftTicketGridAdvSearch['dateBegin'], $shiftTicketGridAdvSearch['dateEnd']);
        }

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'eng_schedule_assignment' => array(
                        'asc' => 'engScheduling.assignment',
                        'desc' => 'engScheduling.assignment DESC'
                    ),
                    'eng_schedule_ro' => array(
                        'asc' => 'engScheduling.resource_order_id',
                        'desc' => 'engScheduling.resource_order_id DESC'
                    ),
                    'submitted_by_user_id' => array(
                        'asc' => 'submittedByUser.name',
                        'desc' => 'submittedByUser.name DESC'
                    ),
                    'user_id' => array(
                        'asc' => 'lastUpdatedByUser.name',
                        'desc' => 'lastUpdatedByUser.name DESC'
                    ),
                    '*'
                )
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => $shiftTicketGridPageSize)
        ));
    }

    public function getSubmittedBy()
    {
        if (isset($this->submittedByUser->name))
        {
            return $this->submittedByUser->name;
        }
        else
        {
            return '';
        }
    }

    public function getLastUpdatedBy()
    {
        if (isset($this->lastUpdatedByUser->name))
        {
            return $this->lastUpdatedByUser->name;
        }
        else
        {
            return '';
        }
    }

    public function getIsSubmitted()
    {
        $submittedStatusCompleted = EngShiftTicketStatus::model()->find(array(
            'condition' => 'shift_ticket_id = :shift_ticket_id AND status_type_id = (SELECT id FROM eng_shift_ticket_status_type WHERE type = \'Submitted\') AND completed = 1',
            'params' => array(
                ':shift_ticket_id' => $this->id
            )
        ));

        if(isset($submittedStatusCompleted))
        {
            return 1;
        }

        return 0;
    }

    /**
     * Retrieves an array of completed statuses.
     * Returns the Type name strings by default, or IDs if specified
     *
     * @param bool $returnIDs //false by default
     * @return array
     *
     */
    public function getCompletedStatuses($returnIDs = false)
    {
        $this->completedStatuses = array();

        $completedStatuses = EngShiftTicketStatus::model()
            ->with(array('statusType','completedByUser'))
            ->findAllByAttributes(array('shift_ticket_id' => $this->id, 'completed' => 1));

        foreach($completedStatuses as $status)
        {
            if($returnIDs)
            {
                $this->completedStatuses[] = $status->statusType->id;
            }
            else
            {
                $label = $status->statusType->type;
                if($status->completedByUser)
                {
                    $label .= ' ('.$status->completedByUser->name.')';
                }
                $this->completedStatuses[] = $label;
            }
        }
        return $this->completedStatuses;
    }

    private function setScheduleAttributes()
    {
        $schedule = EngScheduling::model()->findByPk($this->eng_scheduling_id);
        if(isset($schedule))
        {
            $this->eng_schedule_assignment = $schedule->assignment;
            $this->eng_schedule_clients = $schedule->client_names;
            $this->eng_schedule_crew =  $schedule->crew_names;
            $this->eng_schedule_crew_type =  $schedule->crew_types;
            $this->eng_schedule_ro = $schedule->resource_order_num;
            $this->fire_name = $schedule->fire_name;
            $this->eng_engine_name = $schedule->engine_name;
        }
    }

    public function getActivitiesHTMLList()
    {
        $fullList = $shortList = "<ul>";
        $lineCounter = 0;

        if(isset($this->engShiftTicketActivities))
        {
            foreach($this->engShiftTicketActivities as $activity)
            {
                $lineCounter++;
                $activityListItem = '<li>';
                if($activity->eng_shift_ticket_activity_type_id == 4 && !empty($activity->res_ph_visit_id))
                {
                    $pHV = ResPhVisit::model()->findByPk($activity->res_ph_visit_id);
                    $activityListItem .= $pHV->client->name.' Policyholder ('.$pHV->memberLastName.', '.$pHV->property->address_line_1.')';
                    $activityListItem .= ' | '.date_format(date_create($activity->start_time), 'H:i').' - '.date_format(date_create($activity->end_time), 'H:i');
                    //sublist of actions with qty and units
                    if(!empty($pHV->phActions))
                    {
                        $activityListItem .=  '<ul>';
                        foreach($pHV->phActions as $phaction)
                        {
                            if($phaction->phActionType->units != "")
                            {
                                $lineCounter++;
                                $activityListItem .= '<li type="circle">';
                                $activityListItem .= $phaction->phActionType->name." ( ".$phaction->qty." ".$phaction->phActionType->units.")";
                                $activityListItem .= '</li>';
                            }
                        }
                        $activityListItem .= '</ul>';
                    }
                }
                else
                {
                    $activityListItem .= $activity->engShiftTicketActivityType->type;
                    $activityListItem .= ' | '.date_format(date_create($activity->start_time), 'H:i').' - '.date_format(date_create($activity->end_time), 'H:i');
                    $activity_location = '';
                    if($activity->tracking_location != '')
                        {
                           $activityListItem .= " | ".$activity->tracking_location;
                        }
                        if($activity->tracking_location_end != '')
                        {
                           $activityListItem .= " - ".$activity->tracking_location_end;
                        }

                }

                $activityListItem .= '</li>';

                if($lineCounter < 5)
                {
                    $shortList .= $activityListItem;
                }
                $fullList .= $activityListItem;
            }
        }
        $fullList .= '</ul>';
        $shortList .= '</ul>';

        if($fullList !== $shortList)
        {
            $shortList .= CHtml::link('More', '#', array(
                'class' => 'activities-popup',
                'data-activities' => $fullList,
                'data-activities-title' => 'ShiftTicket #' . $this->id . ' Activities'
            ));
        }
        return $shortList;
    }

    public function getClientsHTMLList()
    {
        $return = '<ul>';
        foreach($this->eng_schedule_clients as $client)
        {
            $return .= '<li>'.$client.'</li>';
        }
        $return .= '</ul>';
        return $return;
    }

    public function getCrewHTMLList()
    {
        $return = '<ul>';
        foreach($this->eng_schedule_crew as $crew)
        {
            $return .= '<li>'.$crew.'</li>';
        }
        $return .= '</ul>';
        return $return;
    }

    public function getTotalActivityTime($activityType="")
    {
        $totalTime = new DateTime('00:00');
        $diffBase = clone $totalTime;
        $activityTypes = explode(",",$activityType);
        foreach($this->engShiftTicketActivities as $activity)
        {
            if($activity->billable == 1)
            {
                if(empty($activityType) || in_array($activity->eng_shift_ticket_activity_type_id,$activityTypes))
                {
                        $interval = date_create($activity->end_time)->diff(date_create($activity->start_time));
                        $totalTime->add($interval);
                }
            }
        }
        return $diffBase->diff($totalTime)->format("%H:%I");
    }

    public function getTotalMiles()
    {
        return (int)$this->end_miles - (int)$this->start_miles;
    }

    protected function afterFind()
    {
        $this->date = date('Y-m-d', strtotime($this->date));

        $this->setScheduleAttributes();

        $this->isSubmitted = $this->getIsSubmitted();

        return parent::afterFind();
    }

    protected function afterSave()
    {
        //setup statuses that need to be completed for the shift ticket
        if($this->isNewRecord)
        {
            $statusTypes = EngShiftTicketStatusType::model()->getAllActiveStatuses();
            foreach($statusTypes as $statusType)
            {
                $stStatus = new EngShiftTicketStatus;
                $stStatus->shift_ticket_id = $this->id;
                $stStatus->status_type_id = $statusType->id;
                $stStatus->completed = 0;
                $stStatus->save();
            }
        }

        return parent::afterSave();
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EngShiftTicket the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Validation method that checks if start/end miles are in logical order
     */
    public function checkMilesLogic()
    {
        $start_miles = (int)$this->start_miles;
        $end_miles = (int)$this->end_miles;

        if ($start_miles > $end_miles)
        {
            $this->addError('start_miles', 'Start miles must be less than end miles');
            $this->addError('end_miles', 'End miles must be more than start miles');
        }
    }

    /**
     * Get all shift tickets for today
     * "eng_shift_ticket_id" will be null if shift ticket entry does not exist
     * @param string $date
     * @param array|null $filterData
     * @return array
     */
    public static function getShiftTickets($date, $filterData = null)
    {
        $sql = "
            DECLARE @date DATE = :date

            SELECT
                s.id,
                e.engine_name,
                f.Name fire_name,
                st.id eng_shift_ticket_id,
                st.eng_scheduling_id,
                st.user_id,
                clients.names clients,
                employees.names employees,
                NULL history
            FROM eng_scheduling s
            LEFT OUTER JOIN eng_engines e ON s.engine_id = e.id
            LEFT OUTER JOIN res_fire_name f ON f.Fire_ID = s.fire_id
            LEFT OUTER JOIN eng_shift_ticket st ON (s.id = st.eng_scheduling_id AND st.date = @date)
            LEFT OUTER JOIN (
                SELECT
                    s.id,
                    STUFF((SELECT ', ' + CONVERT(VARCHAR(100), c.name)
                        FROM eng_scheduling_client sc
                        INNER JOIN client c ON c.id = sc.client_id
                        WHERE sc.engine_scheduling_id = s.id
                        FOR XML PATH('')), 1, 1, '') names
                FROM eng_scheduling s
            ) clients ON s.id = clients.id
            -- get list of employee names on one row
            LEFT OUTER JOIN (
                SELECT
                    s.id,
                    STUFF((SELECT ', ' + CONVERT(VARCHAR(100), u.name)
                        FROM eng_scheduling_employee e
                        INNER JOIN eng_crew_management c ON c.id = e.crew_id
                        INNER JOIN [user] u ON c.user_id = u.id
                        WHERE e.engine_scheduling_id = s.id
                        FOR XML PATH('')), 1, 1, '') names
                FROM eng_scheduling s
            ) employees ON s.id = employees.id
            -- Getting completed boolean
            LEFT OUTER JOIN (
                SELECT id,
                    CASE
                        WHEN 0 IN (
                            SELECT s.completed FROM eng_shift_ticket_status s
                            INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                            WHERE s.shift_ticket_id = st.id AND (t.disabled != 1 OR t.disabled IS NULL)
                        ) THEN 0 ELSE 1
                    END completed,
                    CASE
                        WHEN 1 IN (
                            SELECT s.completed FROM eng_shift_ticket_status s
                            INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                            WHERE s.shift_ticket_id = st.id AND t.type = 'Submitted'
                        ) THEN 1 ELSE 0
                    END submitted
                FROM eng_shift_ticket st
            ) completed ON st.id = completed.id
            WHERE CONVERT(DATE, s.start_date) <= @date
                AND CONVERT(DATE, s.end_date) >= @date
        ";

        // Filter was submitted or is already in session
        if ($filterData || isset($_SESSION[self::SHIFT_TICKET_FILTER]))
        {
            if ($filterData)
            {
                $filterData = json_decode($filterData);
            }
            else
            {
                $filterData = $_SESSION[self::SHIFT_TICKET_FILTER];
            }

            $_SESSION[self::SHIFT_TICKET_FILTER] = $filterData;

            // Clients filter
            if ($filterData->clients)
            {
                $sql .= '
                    AND s.id IN (
                        SELECT engine_scheduling_id FROM eng_scheduling_client WHERE engine_scheduling_id = s.id AND client_id IN (' . implode(',', $filterData->clients) . ')
                    )
                ';
            }

            // Filter by submitted
            if ($filterData->submitted)
            {
                // If use select both options, don't bother filtering
                if (count($filterData->submitted) === 1)
                {
                    // Only sumbitted shift tickets
                    if (current($filterData->submitted) == 1)
                    {
                        $sql .= ' AND (completed.submitted IS NOT NULL AND completed.submitted = 1)';
                    }

                    // Only NOT sumbitted shift tickets
                    if (current($filterData->submitted) == 0)
                    {
                        $sql .= ' AND (completed.submitted IS NULL OR completed.submitted = 0)';
                    }
                }
            }

            // Filter by completed
            if ($filterData->completed)
            {
                // If use select both options, don't bother filtering
                if (count($filterData->completed) === 1)
                {
                    // Only sumbitted shift tickets
                    if (current($filterData->completed) == 1)
                    {
                        $sql .= ' AND completed.completed = 1';
                    }

                    // Only NOT sumbitted shift tickets
                    if (current($filterData->completed) == 0)
                    {
                        $sql .= ' AND (completed.completed IS NULL OR completed.completed = 0)';
                    }
                }
            }

            // Filter fire fires
            if ($filterData->fires)
            {
                $sql .= ' AND s.fire_id IN (' . implode(',', $filterData->fires) . ')';
            }
        }

        $sql .= ' ORDER BY e.engine_name ASC';

        //print_r($sql);
        //var_dump($date);
        //die();

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':date', $date, PDO::PARAM_STR)
            ->queryAll();
    }

    /**
     * Return a history array of shift ticket models from a shift ticket id
     * @param integer $shiftTicketID
     * @return EngShiftTicket[] array stack of what the model used to look like with the date and time it was changed to that as the key
     */
    public static function getShiftTicketHistory($shiftTicketID)
    {
        $historyModels = ModelHistory::model()->findAll(array(
            'select' => 'data, date',
            'condition' => '[table] = :table AND [table_pk] = :table_pk',
            'params' => array(':table' => 'eng_shift_ticket', ':table_pk' => $shiftTicketID),
            'order' => 'date DESC',
        ));

        $shiftTicketHistoryArray = array();

        foreach ($historyModels as $history)
        {
            $dataArray = json_decode($history->data, true);

            $shiftTicket = new EngShiftTicket;
            $shiftTicket->attributes = $dataArray;

            // Load Activities
            $activities = array();
            if (isset($dataArray['activities']))
            {
                foreach ($dataArray['activities'] as $activityData)
                {
                    $activity = new EngShiftTicketActivity;
                    $activity->attributes = $activityData;
                    $activities[] = $activity;
                }
            }
            $shiftTicket->engShiftTicketActivities = $activities;

            // Load Statuses

            $statuses = array();
            if (isset($dataArray['statuses']))
            {
                foreach ($dataArray['statuses'] as $statusData)
                {
                    $status = new EngShiftTicketStatus;
                    $status->attributes = $statusData;
                    $statuses[] = $status;
                }
            }
            $shiftTicket->statuses = $statuses;

            // Load Notes

            $notes = array();
            if (isset($dataArray['notes']))
            {
                foreach ($dataArray['notes'] as $noteData)
                {
                    $note = new EngShiftTicketNotes;
                    $note->attributes = $noteData;
                    $notes[] = $note;
                }
            }
            $shiftTicket->notes = $notes;

            $historyDate = strtotime($history->date);
            $shiftTicketHistoryArray[$historyDate] = $shiftTicket;
        }

        return $shiftTicketHistoryArray;
    }

    /**
     * Returns a list of Policyholder Visits that were done by the any user on the same schedule and on the same date as the current shift ticket
     * @return ResPhVisit[]
     */
    public function getAvailiblePhVisitsList()
    {
        $resPhVisits = ResPhVisit::model()->findAll('date_action >= CONVERT(DATE, :start_date) AND date_action < DATEADD(DAY, 1, CONVERT(DATE, :end_date))', array(
            ':start_date' => $this->date,
            ':end_date' => $this->date,
        ));

        return $resPhVisits;
    }

    /**
     * Display shift ticket PDFs in the browser or download the document to disk
     * @param string[] $ids
     * @param boolean $forceDownload
     * @return string
     */
    public function downloadShiftTicketPDFs($ids, $forceDownload = true)
    {
        $criteria = new CDbCriteria;
        $criteria->addInCondition('t.id', $ids);
        $criteria->select = array('t.id','t.date','t.start_miles','t.end_miles','t.safety_meeting_comments','t.eng_scheduling_id','t.start_location','t.end_location','t.equipment_check');
        $criteria->order = 't.date DESC';
        // Eager loading data to make DB call less intensive
        $criteria->with = array(
            'engShiftTicketActivities',
            'engShiftTicketActivities.engShiftTicketActivityType',
            'engScheduling' => array(
                'select' => array('assignment','city','state')
            ),
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
        );

        $shiftTickets = EngShiftTicket::model()->findAll($criteria);

        Yii::import('application.vendors.tcpdf.*');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Wildfire Defense Systems');
        $pdf->SetTitle('Shift Tickets');
        $pdf->SetSubject('Shift Tickets');
        $pdf->SetKeywords('PDF, Shift Tickets');
        $pdf->SetFont('times', '', 12);

		//set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 16, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        foreach ($shiftTickets as $shiftTicket)
        {
            $pdf->AddPage('P');

            // Variables for view in PDF

            $assignment = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->assignment : '';
            $fireName = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->fire_name : '';
            $location = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->city.', '.$shiftTicket->engScheduling->state : '';
            $engineName = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->engine_name : '';
            $crew = isset($shiftTicket->engScheduling) ? implode(', ', $shiftTicket->engScheduling->crew_names) : '';
            $resourceOrder = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->resource_order_num : '';
            $alliance = isset($shiftTicket->engScheduling->engine->alliancepartner) ? $shiftTicket->engScheduling->engine->alliancepartner->name : '';

            // Creating HTML markup for shift ticket activites

            $activitiesTableHtml = '';

            foreach($shiftTicket->engShiftTicketActivities as $activity)
            {
                $time = date('H:i', strtotime($activity->start_time)) . ' - ' . date('H:i', strtotime($activity->end_time));
                $type = $activity->engShiftTicketActivityType ? $activity->engShiftTicketActivityType->type : '';
                $comments = $activity->comment;

                $details = '';

                // Detail text for mob/demob
                if ($type === 'MOB/DEMOB')
                {
                    $details = $activity->tracking_location . ' to ' . $activity->tracking_location_end;
                }
                // Detail text for policy visit
                elseif ($type === 'Policyholder')
                {
                    $policyVisit = ResPhVisit::model()->find(array(
                        'condition' => 't.id = :id',
                        'params' => array(':id' => $activity->res_ph_visit_id),
                        // Eager loading data to make DB call less intensive
                        'with' => array(
                            'phActions',
                            'phActions.phActionType',
                            'property' => array(
                                'select' => array('pid','address_line_1')
                            ),
                            'property.member' => array(
                                'select' => array('mid','last_name','first_name')
                            )
                        )
                    ));

                    if ($policyVisit)
                    {
                        if ($policyVisit->property)
                        {
                            $details .= $policyVisit->property->address_line_1 . ' - ';
                        }

                        if ($policyVisit->property->member)
                        {
                            $details .= $policyVisit->property->member->last_name . ', ' . $policyVisit->property->member->first_name;
                        }

                        if ($details)
                        {
                            $details .= '<br />';
                        }

                        foreach ($policyVisit->phActions as $index => $action)
                        {
                            if ($action->phActionType)
                            {
                                $details .= ($index === 0 ? '' : '<br />') . $action->qty . ' ' . $action->phActionType->name;
                            }
                        }
                    }
                }

                $activitiesTableHtml .= '
                <tr>
                    <td>' . $time . '</td>
                    <td>' . $type . '</td>
                    <td>' . $comments . '</td>
                </tr>';

                if ($details)
                {
                    $activitiesTableHtml .= '
                    <tr>
                        <td style="text-align: right;"><strong>' . $type . ' Details:</strong></td>
                        <td colspan="2">' . $details . '</td>
                    </tr>';
                }
            }

            $activitiesHtml = $activitiesTableHtml ? '
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><u>Time</u></th>
                        <th><u>Item</u></th>
                        <th><u>Comment</u></th>
                    </tr>
                </thead>
                <tbody>' . $activitiesTableHtml . '</tbody>
            </table>
            ' : '<p style="color:red;">No Time Entries</p>';

            // Creating HTML markup for shift ticket

            $html = '
            <p style="text-align: center; font-size: 1.6em; font-weight: 500;">' . ($alliance ? $alliance : 'WILDFIRE DEFENSE SYSTEMS') . '</p>

            <hr />

            <p><b><u>Details</u></b></p>

            <table style="width:100%;">
                <tbody>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>Date:</strong></td>
                        <td width="30%" style="text-align: left;">' . $shiftTicket->date . '</td>
                        <td width="20%" style="text-align: right;"><strong>Assignment:</strong></td>
                        <td width="30%" style="text-align: left;">' . $assignment . '</td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>Fire:</strong></td>
                        <td width="30%" style="text-align: left;">' . $fireName . '</td>
                        <td width="20%" style="text-align: right;"><strong>Location:</strong></td>
                        <td width="30%" style="text-align: left;">' . $location . '</td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>Engine:</strong></td>
                        <td width="30%" style="text-align: left;">' . $engineName . '</td>
                        <td width="20%" style="text-align: right;"><strong>Crew:</strong></td>
                        <td width="30%" style="text-align: left;">' . $crew . '</td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>Start Mileage:</strong></td>
                        <td width="30%" style="text-align: left;">' . $shiftTicket->start_miles . ' miles</td>
                        <td width="20%" style="text-align: right;"><strong>Start Location:</strong></td>
                        <td width="30%" style="text-align: left;">' . $shiftTicket->start_location . '</td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>End Mileage:</strong></td>
                        <td width="30%" style="text-align: left;">' . $shiftTicket->end_miles . ' miles</td>
                        <td width="20%" style="text-align: right;"><strong>End Location:</strong></td>
                        <td width="30%" style="text-align: left;">' . $shiftTicket->end_location . '</td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>RO:</strong></td>
                        <td width="30%" style="text-align: left;">' . $resourceOrder . '</td>
                        <td width="20%" style="text-align: right;"></td>
                        <td width="30%" style="text-align: left;"></td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>Safety Meeting Comments:</strong></td>
                        <td width="80%" colspan="3" style="text-align: left;">' . $shiftTicket->safety_meeting_comments . '</td>
                    </tr>
                    <tr>
                        <td width="20%" style="text-align: right;"><strong>Equipment Check:</strong></td>
                        <td width="80%" colspan="3" style="text-align: left;">' . $shiftTicket->equipment_check . '</td>
                    </tr>
                </tbody>
            </table>

            <br />

            <p><b><u>Time Entries</u></b></p>

            ' . $activitiesHtml;

            $pdf->writeHTML($html, true, false, true, false, '');
        }

        if ($forceDownload)
        {
            //Inline
            $pdf->Output('Shift Tickets ' . date('Y-m-d H:i') . '.pdf', 'I');
            Yii::app()->end();
        }

        //Allow user to choose where to save
        $fileName = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . 'Shift Tickets.pdf';
        $pdf->Output($fileName, 'F');
        return $fileName;
    }
}
