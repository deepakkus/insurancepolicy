<?php

/**
 * This is the model class for table "eng_scheduling".
 *
 * The followings are the available columns in table 'eng_scheduling':
 * @property integer $id
 * @property string $start_date
 * @property string $end_date
 * @property string $arrival_date
 * @property integer $engine_id
 * @property integer $fire_officer_id
 * @property string $comment
 * @property string $assignment
 * @property integer $fire_id
 * @property string $city
 * @property string $state
 * @property string $lat
 * @property string $lon
 * @property integer $resource_order_id
 * @property string $specific_instructions
 */
class EngScheduling extends CActiveRecord
{
    public $start_time;
    public $end_time;
    public $arrival_time;
    public $engine_name;
    public $engine_source;
    public $engine_alliance_partner;
    public $fire_name;
    public $resource_order_num;

    // Attribute to search many to one clients row by
    public $client_id;

    // Array Variable for many to one clients column
    public $client_names;

    // Array Variable for many to one crew column
    public $crew_names;
    // Array Variable for many to one crew type column
    public $crew_types;
    const ENGINE_ASSIGNMENT_DEDICATED = 'Dedicated Service';
    const ENGINE_ASSIGNMENT_PRERISK = 'Pre Risk';
    const ENGINE_ASSIGNMENT_RESPONSE = 'Response';
    const ENGINE_ASSIGNMENT_ONHOLD = 'On Hold';
    const ENGINE_ASSIGNMENT_STAGED = 'Staged';
    const ENGINE_ASSIGNMENT_OUTOFSERVICE = 'Out of Service';
    const ENGINE_ASSIGNMENT_INSTORAGE = 'In Storage';

    const ENGINE_COLOR_DEDICATED = '#4DAF4A';
    const ENGINE_COLOR_PRERISK = '#377EB8';
    const ENGINE_COLOR_RESPONSE = '#E41A1C';
    const ENGINE_COLOR_ONHOLD = '#B8B8B8';
    const ENGINE_COLOR_STAGED = '#FF6600';
    const ENGINE_COLOR_OUTOFSERVICE = '#707070';
    const ENGINE_COLOR_INSTORAGE = '#32CBCC';

    // Reporting Variables
    public $enginecount;
    public $daycount;
    public $engine_utilization;
    public $total_utilization;
    public $non_utilization;

    // After Save Variables
    public $aftersave_startdate;
    public $aftersave_enddate;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_scheduling';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('start_date, end_date, engine_id, assignment, city, state, lat, lon', 'required'),
            array('engine_id, fire_officer_id, fire_id, resource_order_id', 'numerical', 'integerOnly' => true),
            array('comment, specific_instructions', 'length', 'max' => 200),
            array('assignment', 'length', 'max' => 20),
            array('city', 'length', 'max' => 30),
            array('state', 'length', 'max' => 2),
            array('lat, lon', 'length', 'max' => 12),
            // Custom Date Scheduling Validation Rules
            array('start_date', 'checkStartDate'),
            array('end_date', 'checkEndDate'),
            // Allow these public attributes to be assigned
            array('start_time, end_time, arrival_time, arrival_date', 'safe'),
            // The following rule is used by search().
            array('id, start_date, end_date, arrival_date, engine_id, comment, assignment, fire_id, ' .
                  'city, state, lat, lon, engine_name, engine_source, fire_name, resource_order_num, client_id', 'safe', 'on'=>'search'),
        );
    }

    /**
     * Validation method that check if the chosen start date is already taken by this engine
     * @param string $attribute attribute to be validated
     */
    public function checkStartDate($attribute)
    {
        $start_date_formatted = date('Y-m-d H:i', strtotime($this->start_date . ' ' . $this->start_time));

        $engine_id = $this->engine_id;

        if ($start_date_formatted && $engine_id)
        {
            $criteria = new CDbCriteria();
            $criteria->addCondition("'$start_date_formatted' BETWEEN start_date AND end_date AND engine_id = $engine_id");
            if (!$this->isNewRecord)
            {
                $criteria->addCondition('id != ' . $this->id);
            }

            $engineScheduled = self::model()->find($criteria);

            if ($engineScheduled)
            {
                $this->addError($attribute, "This engine is already scheduled from <br /><b>{$engineScheduled->start_date}</b> to <b>{$engineScheduled->end_date}</b>");
            }
        }
    }

    /**
     * Validation method that check if the chosen end date is already taken by this engine
     * @param string $attribute attribute to be validated
     */
    public function checkEndDate($attribute)
    {
        $start_date = strtotime($this->start_date . ' ' . $this->start_time);
        $end_date = strtotime($this->end_date . ' ' . $this->end_time);

        $end_date_formatted = date('Y-m-d H:i', $end_date);

        if ($start_date > $end_date)
        {
            return $this->addError($attribute,"<b>End date cannot precede the start date!</b>");
        }

        $engine_id = $this->engine_id;

        if ($end_date_formatted && $engine_id)
        {
            $criteria = new CDbCriteria();
            $criteria->addCondition("'$end_date_formatted' BETWEEN start_date AND end_date AND engine_id = $engine_id");
            if (!$this->isNewRecord)
            {
                $criteria->addCondition('id != ' . $this->id);
            }

            $engineScheduled = self::model()->find($criteria);

            if ($engineScheduled)
            {
                $this->addError($attribute, "This engine is already scheduled from <br /><b>{$engineScheduled->start_date}</b> to <b>{$engineScheduled->end_date}</b>");
            }
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'fire' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'engine' => array(self::BELONGS_TO, 'EngEngines', 'engine_id'),
            'engineFireOfficer' => array(self::BELONGS_TO, 'EngCrewManagement', 'fire_officer_id'),
            'resourceOrder' => array(self::BELONGS_TO, 'EngResourceOrder', 'resource_order_id'),
            'engineClient' => array(self::HAS_MANY, 'EngSchedulingClient', 'engine_scheduling_id'),
            'employees' => array(self::HAS_MANY, 'EngSchedulingEmployee', 'engine_scheduling_id'),
            'shiftTickets' => array(self::HAS_MANY, 'EngShiftTicket', 'eng_scheduling_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'arrival_date' => 'Arrival Date (RO use)',
            'engine_id' => 'Engine',
            'fire_officer_id' => 'Fire Officer',
            'resource_order_id' => 'RO ID',
            'comment' => 'Client Comments',
            'assignment' => 'Assignment',
            'fire_id' => 'Fire',
            'city' => 'City',
            'state' => 'State',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'arrival_time' => 'Arrival Time (RO use)',
            'engine_name' => 'Engine Name',
            'engine_source' => 'Engine Source',
            'fire_name' => 'Fire Name',
            'client_names' => 'Client Names',
            'crew_names' => 'Crew Names',
            'crew_types' => 'Crew Types',
            'resource_order_num' => 'RO #',
            'specific_instructions' => 'RO Specific Instructions',
            // Analytics Labels
            'enginecount' => 'Engine Count',
            'daycount' => 'Day Count'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($searchClientID)
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('engine','fire','resourceOrder');

        $criteria->compare('id',$this->id);
        $criteria->compare('t.engine_id',$this->engine_id);
        $criteria->compare('t.comment',$this->comment,true);
        $criteria->compare('assignment',$this->assignment,true);
        $criteria->compare('fire_id',$this->fire_id);
        $criteria->compare('fire_officer_id',$this->fire_officer_id);
        $criteria->compare('t.city',$this->city,true);
        $criteria->compare('t.state',$this->state,true);
        $criteria->compare('lat',$this->lat,true);
        $criteria->compare('lon',$this->lon,true);
        $criteria->compare('engine.engine_name',$this->engine_name,true);
        $criteria->compare('engine.engine_source',$this->engine_source,true);
        $criteria->compare('fire.Name',$this->fire_name,true);

        $criteria->compare('resourceOrder.id',($this->resource_order_num),true);
        if ($this->start_date)
            $criteria->addCondition("t.start_date >= '" . date('Y-m-d', strtotime($this->start_date)) . "'");
        if ($this->end_date)
            $criteria->addCondition("t.end_date < '" . date('Y-m-d', strtotime($this->end_date . ' + 1 day')) . "'");
        if ($this->arrival_date)
            $criteria->addCondition("arrival_date < '" . date('Y-m-d', strtotime($this->arrival_date . ' + 1 day')) . "'");

        if ($this->client_id)
        {
            $criteria->join = 'INNER JOIN eng_scheduling_client c ON c.engine_scheduling_id = t.id 
            AND c.id IN (SELECT MAX(c.id) FROM eng_scheduling_client c WHERE c.client_id = '.$this->client_id.' 
            GROUP BY c.engine_scheduling_id)';
            $criteria->addCondition('c.client_id = ' . $this->client_id);
        }

        // If search form option is selected, override previous sorts
        if (!is_null($searchClientID))
        {
            $criteria=new CDbCriteria;
            $criteria->with = array('engine','fire','resourceOrder');
            $criteria->join = 'INNER JOIN eng_scheduling_client AS c ON c.engine_scheduling_id = t.id';
            $criteria->condition = 'GETDATE() >= c.start_date AND GETDATE() <= c.end_date AND c.client_id = ' . $searchClientID;
        }

        return new WDSCActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder'=>array('start_date'=>CSort::SORT_DESC),
                'attributes' => array(
                    'engine_name' => array(
                        'asc' => 'engine.engine_name ASC',
                        'desc' => 'engine.engine_name DESC'
                    ),
                    'engine_source' => array(
                        'asc' => 'engine.engine_source ASC',
                        'desc' => 'engine.engine_source DESC'
                    ),
                    'fire_name' => array(
                        'asc' => 'fire.Name ASC',
                        'desc' => 'fire.Name DESC'
                    ),
                    'resource_order_num' => array(
                        'asc' => 'resourceOrder.id ASC',
                        'desc' => 'resourceOrder.id DESC'
                    ),
                    '*',
                ),
            ),
            'criteria'=>$criteria,
            'pagination' => array('PageSize'=>20)
        ));
    }

    public function searchAnalytics($start_date, $end_date, $client_id = null)
    {
        $end_date1 = date_create($end_date)->modify('+1 day')->format('Y-m-d');
        $start_date = date('Y-m-d', strtotime($start_date));
      
        $criteria = new CDbCriteria;
        if($start_date > date('Y-m-d', strtotime('1900-01-01')))
        {
        if ($client_id)
        {
            $criteria->join = 'left join eng_scheduling_client as client on t.id = client.engine_scheduling_id';
            $criteria->addCondition('client.client_id = ' . $client_id);
        }

        // Note: The + 1.0 is because the DATEDIFF function doesn't include the last day

        $criteria->select = array(
            'COUNT(DISTINCT engine_id) as enginecount',
            'state',
            'assignment',
            "SUM(CEILING(DATEDIFF(hour,
                CASE WHEN t.start_date < '$start_date' THEN '$start_date' ELSE t.start_date END,
                CASE WHEN t.end_date > '$end_date1' THEN '$end_date1' ELSE t.end_date END
            )/24.0)) AS daycount"
        );

        $criteria->compare('t.assignment',$this->assignment,true);
        $criteria->compare('t.state',$this->state,true);

        $criteria->addCondition("(t.start_date BETWEEN '$start_date' AND '$end_date')
            OR (t.end_date BETWEEN '$start_date' AND '$end_date')
            OR ('$start_date' BETWEEN FORMAT(t.start_date,'yyyy-MM-dd') AND t.end_date)
            OR ('$end_date' BETWEEN FORMAT(t.start_date,'yyyy-MM-dd') AND t.end_date)");

        $criteria->group = 'state, assignment';

        $criteria->order = 't.state ASC';
        $criteria->order = 't.assignment ASC';
        }
        return new CActiveDataProvider($this, array(
            'sort' => array(
                'attributes' => array('*'),
            ),
            'criteria' => $criteria,
            'pagination' => array('PageSize'=>20)
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EngScheduling the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        Yii::app()->format->datetimeFormat = 'Y-m-d H:i';

        if (isset($this->start_date, $this->start_time))
        {
            $this->start_date = Yii::app()->format->datetime($this->start_date . ' ' . $this->start_time);
            $this->arrival_date = Yii::app()->format->datetime($this->start_date);
        }

        if (isset($this->end_date, $this->end_time))
        {
            $this->end_date = Yii::app()->format->datetime($this->end_date . ' ' . $this->end_time);
        }

        if ($this->city)
        {
            $this->city = ucwords($this->city);
        }
        return parent::beforeSave();
    }

    protected function afterFind()
    {
        Yii::app()->format->datetimeFormat = 'Y-m-d H:i';

        $this->start_date = Yii::app()->format->datetime($this->start_date);
        $this->end_date = Yii::app()->format->datetime($this->end_date);
        $this->arrival_date = Yii::app()->format->datetime($this->arrival_date);

        // These are stored to check in the aftersave if an engines scheduled time has changed.
        // Used in automatically ajusting the related scheduled models.
        $this->aftersave_startdate = $this->start_date;
        $this->aftersave_enddate = $this->end_date;

        if ($this->engine)
        {
            $this->engine_name = $this->engine->engine_name;
            $this->engine_source = $this->engine->engine_source;
            $this->engine_alliance_partner = $this->engine->alliance_partner;
        }

        if ($this->fire)
            $this->fire_name = $this->fire->Name;

        if ($this->resourceOrder)
            $this->resource_order_num = $this->resourceOrder->id;

        $this->client_names = array();
        if ($this->engineClient)
        {
            foreach($this->engineClient as $client)
                $this->client_names[] = $client->client_name;
        }

        //have to use query builder here rather than employees relation because causes a circular reference via afterFind functions
        $this->crew_names = array();
        $this->crew_types = array();
        $employees = Yii::app()->db->createCommand()
            ->select('first_name, last_name, crew_type')
            ->from('eng_crew_management ecm')
            ->join('eng_scheduling_employee ese', 'ecm.id = ese.crew_id')
            ->where('ese.engine_scheduling_id = :schedule_id', array(':schedule_id'=>$this->id))
            ->queryAll();
        foreach($employees as $employee)
        {
            $this->crew_names[] = $employee['first_name'].' '.$employee['last_name'];
            $this->crew_types[] = $employee['crew_type'];
        }

        return parent::afterFind();
    }

    /**
     * This method is used to adjust the related models if this engine's scheduled time has been changed
     */
    protected function afterSave()
    {
        if (!$this->isNewRecord)
        {
            $newEndDate = new DateTime($this->end_date);
            $oldEndDate = new DateTime($this->aftersave_enddate);

            // Engine Shortens Schedule
            if ($newEndDate < $oldEndDate)
            {
                // Adjust Eng Crew
                foreach($this->employees as $crew)
                {
                    $crewStartDate = new DateTime($crew->start_date . ' ' . $crew->start_time);
                    $crewEndDate = new DateTime($crew->end_date . ' ' . $crew->end_time);

                    // Check if crew in new time range, if not ... deleting
                    if ( $crewStartDate >= $newEndDate )
                    {
                        $crew->delete();
                    }

                    // Check if date is in range, but end date is outside ... if so ... truncating
                    if ( $crewStartDate < $newEndDate && $crewEndDate > $newEndDate )
                    {
                        $crew->saveAttributes(array('end_date' => $this->end_date));
                    }
                }

                // Adjust Eng Client
                foreach($this->engineClient as $client)
                {
                    $clientStartDate = new DateTime($client->start_date . ' ' . $client->start_time);
                    $clientEndDate = new DateTime($client->end_date . ' ' . $client->end_time);

                    // Check if client in new time range, if not ... deleting
                    if ( $clientStartDate >= $newEndDate )
                    {
                        $client->delete();
                    }

                    // Check if date is in range, but end date is outside ... if so ... truncating
                    if ( $clientStartDate < $newEndDate && $clientEndDate > $newEndDate )
                    {
                        $client->saveAttributes(array('end_date' => $this->end_date));
                    }
                }
            }

            // Engine Lengthens Schedule
            if ($newEndDate > $oldEndDate)
            {
                // Any related model with a end date matching up to the old end date is extended to match the new end date

                foreach($this->employees as $crew)
                {
                    $crewEndDate = new DateTime($crew->end_date . ' ' . $crew->end_time);
                    if ($crewEndDate == $oldEndDate)
                    {
                        $crew->saveAttributes(array('end_date' => $this->end_date));
                    }
                }

                foreach($this->engineClient as $client)
                {
                    $clientEndDate = new DateTime($client->end_date . ' ' . $client->end_time);
                    if ($clientEndDate == $oldEndDate)
                    {
                        $client->saveAttributes(array('end_date' => $this->end_date));
                    }
                }
            }
        }

        return parent::afterSave();
    }

    public function getAvailibleFireClients()
    {
        return Client::model()->findAll(array('order' => 'name ASC','condition' => 'wds_fire = 1'));
    }

    public function getEngineSource()
    {
        if ($this->engine_source == 1) return 'WDS';
        if ($this->engine_source == 2) return 'Alliance';
        if ($this->engine_source == 3) return 'Rental';
        return '';
    }

    public function getEngineAssignments()
    {
        return array(
            self::ENGINE_ASSIGNMENT_DEDICATED    => self::ENGINE_ASSIGNMENT_DEDICATED,
            self::ENGINE_ASSIGNMENT_PRERISK      => self::ENGINE_ASSIGNMENT_PRERISK,
            self::ENGINE_ASSIGNMENT_RESPONSE     => self::ENGINE_ASSIGNMENT_RESPONSE,
            self::ENGINE_ASSIGNMENT_ONHOLD       => self::ENGINE_ASSIGNMENT_ONHOLD,
            self::ENGINE_ASSIGNMENT_STAGED       => self::ENGINE_ASSIGNMENT_STAGED,
            self::ENGINE_ASSIGNMENT_OUTOFSERVICE => self::ENGINE_ASSIGNMENT_OUTOFSERVICE,
            self::ENGINE_ASSIGNMENT_INSTORAGE    => self::ENGINE_ASSIGNMENT_INSTORAGE,
        );
    }

    /**
     * Get engines who are: availible, and who's alliance company is active
     * @return EngEngines[]
     */
    public function getAvailibleEngines()
    {
        return EngEngines::model()->findAllBySql('
            select
                e.id,
                e.engine_name,
                e.engine_source
            from
                eng_engines e
            left outer join
                alliance a on a.id = e.alliance_id
            where
                e.active = 1
                and e.availible >= 1
                and (a.active = 1 or e.alliance_id is null)
            order by
                e.engine_name asc, e.engine_source asc
        ');
    }

    public function getAvailibleFireOfficers()
    {
        return EngCrewManagement::model()->findAll(array(
            'select' => array('id', 'first_name', 'last_name'),
            'condition' => 'fire_officer = 1',
            'order' => 'last_name asc'
        ));
    }

    /**
     * Get all fires that have been smokechecked and notice updated within the last month
     */
    public function getAvailibleFires()
    {
        $sql = "
        DECLARE @pastMonth datetime = DATEADD(MONTH, -1, GETDATE())

        SELECT
            f.Fire_ID,
            f.Name
        FROM res_fire_name f
        INNER JOIN res_fire_obs o ON f.Fire_ID = o.Fire_ID
        INNER JOIN res_monitor_log l ON o.Obs_ID = l.Obs_ID
        LEFT OUTER JOIN res_notice n ON f.Fire_ID = n.fire_id
        WHERE l.Smoke_Check_Date > @pastMonth OR n.date_updated > @pastMonth
        ORDER BY f.Name ASC";
        return ResFireName::model()->findAllBySql($sql);
    }

    public function getAvailibleAssignments()
    {
        return self::model()->findAll(array(
            'select' => array('assignment'),
            'order' => 'assignment asc'
        ));
    }

    public function getAvailibleStates()
    {
        return self::model()->findAll(array(
            'select' => array('state'),
            'order' => 'state asc'
        ));
    }
    
    /**
     * Get all Resource Orders created within the last two months
     */
    public function getAvailibleResourceOrders()
    {
        return EngResourceOrder::model()->findAll(array(
            'order' => 'id desc',
            'condition' => 'date_created >= DATEADD(month,-2,GETDATE())'
        ));       
    }

    /**
     * Get Resource Orders created within last two months which are not assigned 
     * also get current resource assigned for engine if exist
     */
    public function getAvailibleResourceOrdersList($resourceID = NULL)
    {
        $sql = 'SELECT * FROM eng_resource_order WHERE id NOT IN 
            (SELECT id FROM eng_resource_order WHERE id IN 
            (SELECT resource_order_id FROM eng_scheduling)) 
            AND date_created >= DATEADD(month,-2,GETDATE())
            ORDER BY id DESC';

        $result =Yii::app()->db->createCommand($sql)->queryAll();
        if($resourceID)
        {
            $result[] = array('id' => $resourceID, 'user_id' => '','date_created'=>'','date_ordered'=>'');
        }
        return $result;       
    }

    public function resourceOrderGetAssignment()
    {
        if ($this->assignment === self::ENGINE_ASSIGNMENT_RESPONSE)
            return "$this->fire_name<br />$this->city, $this->state";
        else
            return "$this->assignment<br />$this->city, $this->state";
    }

    public function resourceOrderNearestQuarterHour($timestring)
    {
        $timestamp = date('m/d/Y', strtotime($timestring));
        return $timestamp;
    }

    public function resourceOrderGetCompanyInfo()
    {
        $retval = '';

        if ($this->engine->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE)
        {
            if ($this->engine->alliancepartner)
            {
                $retval .= $this->engine->alliancepartner->name . '<br />';
                $retval .= $this->engine->alliancepartner->contact_first . ' ' . $this->engine->alliancepartner->contact_last . '<br />';
                $retval .= $this->engine->alliancepartner->phone;
            }
        }
        else
        {
            $retval .= 'Wildfire Defense Systems<br />406-586-5400';
        }

        return $retval;
    }

    public function resourceOrderGetEngineBoss()
    {
        foreach($this->employees as $employee)
        {
            if ($employee->engine_boss)
            {
                $retval = $employee->crew_first_name . ' ' . $employee->crew_last_name . '<br />';
                $retval .= ($employee->crew->cell_phone ? $employee->crew->cell_phone : $employee->crew->work_phone) . '<br />';
                $retval .= $employee->crew->email;
                return $retval;
            }
        }

        return null;
    }

    public function resourceOrderGetFireOfficer()
    {
        if ($this->engineFireOfficer)
        {
            $retval = $this->engineFireOfficer->first_name . ' ' . $this->engineFireOfficer->last_name;
            if ($this->engineFireOfficer->work_phone)
                $retval .= ' | Work: ' . $this->engineFireOfficer->work_phone;
            if ($this->engineFireOfficer->cell_phone)
                $retval .= ' | Cell: ' . $this->engineFireOfficer->cell_phone;
            return $retval;
        }

        return null;
    }

    public static function reportingSiteCountActiveEngines()
    {
        $sql = "
            DECLARE @now datetime = GETDATE()
            SELECT e.engine_source, s.assignment, COUNT(s.id) [count]
            FROM eng_scheduling s
            INNER JOIN eng_engines e ON s.engine_id = e.id
            WHERE @now > s.start_date
                AND @now < s.end_date
                AND s.assignment IN ('Response','Dedicated Service','On Hold','Pre Risk')
            GROUP BY e.engine_source, s.assignment
            ORDER BY e.engine_source ASC, s.assignment ASC
        ";

        $results = Yii::app()->db->createCommand($sql)->queryAll();

        $returnArray = array(
            EngEngines::ENGINE_SOURCE_WDS => array(
                EngScheduling::ENGINE_ASSIGNMENT_RESPONSE => 0,
                EngScheduling::ENGINE_ASSIGNMENT_DEDICATED => 0,
                EngScheduling::ENGINE_ASSIGNMENT_PRERISK => 0,
                EngScheduling::ENGINE_ASSIGNMENT_ONHOLD => 0,
                'Total' => 0
            ),
            EngEngines::ENGINE_SOURCE_ALLIANCE => array(
                EngScheduling::ENGINE_ASSIGNMENT_RESPONSE => 0,
                EngScheduling::ENGINE_ASSIGNMENT_DEDICATED => 0,
                EngScheduling::ENGINE_ASSIGNMENT_PRERISK => 0,
                EngScheduling::ENGINE_ASSIGNMENT_ONHOLD => 0,
                'Total' => 0
            ),
            EngEngines::ENGINE_SOURCE_RENTAL => array(
                EngScheduling::ENGINE_ASSIGNMENT_RESPONSE => 0,
                EngScheduling::ENGINE_ASSIGNMENT_DEDICATED => 0,
                EngScheduling::ENGINE_ASSIGNMENT_PRERISK => 0,
                EngScheduling::ENGINE_ASSIGNMENT_ONHOLD => 0,
                'Total' => 0
            )
        );

        foreach ($results as $result)
        {
            $returnArray[$result['engine_source']][$result['assignment']] += (int)$result['count'];
            $returnArray[$result['engine_source']]['Total'] += (int)$result['count'];
        }

        return $returnArray;
    }

    public static function reportingSiteCurrentFleetStatistics()
    {
        $sql = '
            DECLARE @now datetime = GETDATE()
            SELECT e.engine_source, s.assignment, COUNT(s.assignment) [count]
            FROM eng_scheduling s
            INNER JOIN eng_engines e ON e.id = s.engine_id
            WHERE @now > s.start_date
                AND @now < s.end_date
            GROUP BY e.engine_source, s.assignment
        ';

        $scheduledEngines = Yii::app()->db->createCommand($sql)->queryAll();

        $sql = '
            DECLARE @now datetime = GETDATE()
            SELECT engine_source, COUNT(id) [count]
            FROM eng_engines
            WHERE id NOT IN (
            SELECT engine_id
                FROM eng_scheduling
                WHERE @now > start_date
                    AND @now < end_date
            ) AND active = 1
            GROUP BY engine_source
        ';

        $notScheduledEngines = Yii::app()->db->createCommand($sql)->queryAll();

        $activeAssignments = array(
            EngScheduling::ENGINE_ASSIGNMENT_RESPONSE,
            EngScheduling::ENGINE_ASSIGNMENT_DEDICATED,
            EngScheduling::ENGINE_ASSIGNMENT_PRERISK,
            EngScheduling::ENGINE_ASSIGNMENT_ONHOLD
        );

        $returnArray = array(
            'working_now' => array(
                EngEngines::ENGINE_SOURCE_WDS => 0,
                EngEngines::ENGINE_SOURCE_ALLIANCE => 0,
                EngEngines::ENGINE_SOURCE_RENTAL => 0
            ),
            'not_active' => array(
                EngEngines::ENGINE_SOURCE_WDS => 0,
                EngEngines::ENGINE_SOURCE_ALLIANCE => 0,
                EngEngines::ENGINE_SOURCE_RENTAL => 0
            ),
            'not_scheduled' => array(
                EngEngines::ENGINE_SOURCE_WDS => 0,
                EngEngines::ENGINE_SOURCE_ALLIANCE => 0,
                EngEngines::ENGINE_SOURCE_RENTAL => 0
            ),
            'total_engines_fleet' => array(
                EngEngines::ENGINE_SOURCE_WDS => 0,
                EngEngines::ENGINE_SOURCE_ALLIANCE => 0,
                EngEngines::ENGINE_SOURCE_RENTAL => 0
            )
        );

        foreach ($scheduledEngines as $engine)
        {
            if (in_array($engine['assignment'], $activeAssignments))
            {
                $returnArray['working_now'][$engine['engine_source']] += (int)$engine['count'];
                $returnArray['total_engines_fleet'][$engine['engine_source']] += (int)$engine['count'];
            }
            else
            {
                $returnArray['not_active'][$engine['engine_source']] += (int)$engine['count'];
                $returnArray['total_engines_fleet'][$engine['engine_source']] += (int)$engine['count'];
            }
        }

        foreach ($notScheduledEngines as $engine)
        {
            $returnArray['not_scheduled'][$engine['engine_source']] += (int)$engine['count'];
            $returnArray['total_engines_fleet'][$engine['engine_source']] += (int)$engine['count'];
        }

        return $returnArray;
    }

    public static function reportingIndexMonthBreakDown($start_date, $end_date)
    {
        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);
        $dateperiod = new DatePeriod($start_date, DateInterval::createFromDateString('1 month'), $end_date);

        $retval = array();

        $sql = "
        DECLARE @startdate datetime = :startdate;
        DECLARE @enddate datetime = :enddate;
        DECLARE @enddate1 datetime = DATEADD(day, 1, @enddate);

        SELECT COUNT(DISTINCT engine_id) as enginecount, assignment, FORMAT(@startdate, 'yyyy-MM-dd') as month,
            SUM(CEILING(DATEDIFF(hour,
                CASE WHEN start_date < @startdate THEN @startdate ELSE start_date END,
                CASE WHEN end_date > @enddate1 THEN @enddate1 ELSE end_date END
            )/24.0)) AS daycount
        FROM eng_scheduling
        WHERE
        (
            (start_date BETWEEN @startdate AND @enddate)
            OR (end_date BETWEEN @startdate AND @enddate)
            OR (@startdate BETWEEN FORMAT(start_date,'yyyy-MM-dd') AND end_date)
            OR (@enddate BETWEEN  FORMAT(start_date,'yyyy-MM-dd') AND end_date)
        )
        AND assignment IN ('Response','Pre Risk','Dedicated Service')
        GROUP BY assignment;";

        $currentmonth = date('m');

        foreach($dateperiod as $date)
        {
            $startdate = $date->modify('first day of this month')->format('Y-m-d');
            $enddate = $date->modify('last day of this month')->format('Y-m-d');

            if ($currentmonth === $date->format('m'))
                $enddate = $end_date->format('Y-m-d');

            $command = Yii::app()->db->createCommand($sql);
            $command->bindParam(':startdate', $startdate, PDO::PARAM_STR);
            $command->bindParam(':enddate', $enddate, PDO::PARAM_STR);

            $retval[] = $command->queryAll();
        }

        return $retval;
    }

    public static function reportingIndexDaysByCompany($start_date, $end_date)
    {
        $engine_models = EngEngines::model()->findAll(array(
            'select' => 'alliance_id, alliance.name',
            'distinct' => true,
            'join' => 'LEFT JOIN alliance ON t.alliance_id = alliance.id',
            'order' => 'alliance.name',
        ));

        $returnArray = array();

        foreach($engine_models as $engine_model)
        {
            $sql = "
                DECLARE @startdate datetime = :startdate;
                DECLARE @enddate datetime = :enddate;
                DECLARE @enddate1 datetime = DATEADD(day, 1, @enddate);
                DECLARE @allianceid int = :allianceid;

                SELECT SUM(CEILING(DATEDIFF(hour,
                    CASE WHEN start_date < @startdate THEN @startdate ELSE start_date END,
                    CASE WHEN end_date > @enddate1 THEN @enddate1 ELSE end_date END
                )/24.0)) AS daycount, assignment
                FROM eng_scheduling
                WHERE " . (($engine_model->alliance_id) ?
                    "engine_id IN (SELECT id FROM eng_engines WHERE alliance_id = @allianceid)" :
                    "engine_id IN (SELECT id FROM eng_engines WHERE alliance_id IS NULL)") . "
                AND
                (
                    (start_date BETWEEN @startdate AND @enddate)
                    OR (end_date BETWEEN @startdate AND @enddate)
                    OR (@startdate BETWEEN FORMAT(start_date,'yyyy-MM-dd') AND end_date)
                    OR (@enddate BETWEEN  FORMAT(start_date,'yyyy-MM-dd') AND end_date)
                )
                AND assignment IN ('Dedicated Service', 'Response')
                GROUP BY assignment;
            ";

            $results = self::model()->findAllBySql($sql, array(
                ':startdate' => $start_date,
                ':enddate' => $end_date,
                ':allianceid' => $engine_model->alliance_id
            ));

            $resultArray = array(
                'company' => $engine_model->alliance_id ? $engine_model->alliancepartner->name : 'WDS',
                'firedays' => 0,
                'dedicateddays' => 0
            );

            foreach($results as $result)
            {
                if ($result->assignment === 'Response')
                    $resultArray['firedays'] += (int)$result->daycount;
                if ($result->assignment === 'Dedicated Service')
                    $resultArray['dedicateddays'] += (int)$result->daycount;
            }

            $returnArray[] = $resultArray;
        }

        return $returnArray;
    }

    public static function reportingIndexDaysByEngine($start_date, $end_date)
    {
        $engine_models = EngEngines::model()->findAll(array(
            'with' => 'alliancepartner',
            'order' => 'alliancepartner.name, engine_name'
        ));

        $returnArray = array();

        foreach($engine_models as $engine_model)
        {
            $sql = "
                DECLARE @startdate datetime = :startdate;
                DECLARE @enddate datetime = :enddate;
                DECLARE @enddate1 datetime = DATEADD(day, 1, @enddate);
                DECLARE @engineid int = :engineid;

                SELECT SUM(CEILING(DATEDIFF(hour,
                    CASE WHEN start_date < @startdate THEN @startdate ELSE start_date END,
                    CASE WHEN end_date > @enddate1 THEN @enddate1 ELSE end_date END
                )/24.0)) AS daycount, assignment
                FROM eng_scheduling
                WHERE engine_id = @engineid
                AND
                (
                    (start_date BETWEEN @startdate AND @enddate)
                    OR (end_date BETWEEN @startdate AND @enddate)
                    OR (@startdate BETWEEN FORMAT(start_date,'yyyy-MM-dd') AND end_date)
                    OR (@enddate BETWEEN  FORMAT(start_date,'yyyy-MM-dd') AND end_date)
                )
                GROUP BY assignment;
            ";

            $results = self::model()->findAllBySql($sql, array(
                ':startdate' => $start_date,
                ':enddate' => $end_date,
                ':engineid' => $engine_model->id
            ));

            // Get Array of Assignments and set all their values to 0
            $resultsArray = self::model()->getEngineAssignments();
            array_walk($resultsArray, function(&$value, $key) { $value = 0; });

            // Modify based on results
            foreach($results as $result)
                $resultsArray[$result->assignment] = (int)$result->daycount;

            $resultsArray['engine_id'] = $engine_model->id;
            $resultsArray['engine_name'] = $engine_model->engine_name;
            $resultsArray['alliance_partner'] = $engine_model->alliance_partner;

            $returnArray[] = $resultsArray;
        }

        return $returnArray;
    }

    public static function reportingTotalEngineUtilization($start_date, $end_date, $source = null)
    {
        $sql = "
        -- NOTE: DATEDIFF function does not include the last day

        DECLARE @startdate datetime = :startdate;
        DECLARE @enddate datetime = :enddate;
        DECLARE @enddate1 datetime = DATEADD(day, 1, @enddate);
        DECLARE @engineid int = :engineid;
        DECLARE @totaldays float = DATEDIFF(day, @startdate, @enddate1);
        DECLARE @totalutilization float;

        -- Getting total hours between dates, dividing by 24, and rounding up to get days used for engine
        -- Divided by @totaldays in date query range to get utilized proportion

        SET @totalutilization = (SELECT SUM(CEILING(DATEDIFF(hour,
            CASE WHEN start_date < @startdate THEN @startdate ELSE start_date END,
            CASE WHEN end_date > @enddate1 THEN @enddate1 ELSE end_date END
        )/24.0)) / @totaldays
        FROM eng_scheduling WHERE engine_id = @engineid AND
        (
            (start_date BETWEEN @startdate AND @enddate)
            OR (end_date BETWEEN @startdate AND @enddate)
            OR (@startdate BETWEEN FORMAT(start_date,'yyyy-MM-dd') AND end_date)
            OR (@enddate BETWEEN  FORMAT(start_date,'yyyy-MM-dd') AND end_date)
        ))

        SELECT @totalutilization AS total_utilization

        SELECT SUM(CEILING(DATEDIFF(hour,
            CASE WHEN start_date < @startdate THEN @startdate ELSE start_date END,
            CASE WHEN end_date > @enddate1 THEN @enddate1 ELSE end_date END
        )/24.0)) / @totaldays AS engine_utilization, assignment
        FROM eng_scheduling
        WHERE engine_id = @engineid AND
        (
            (start_date BETWEEN @startdate AND @enddate)
            OR (end_date BETWEEN @startdate AND @enddate)
            OR (@startdate BETWEEN FORMAT(start_date,'yyyy-MM-dd') AND end_date)
            OR (@enddate BETWEEN  FORMAT(start_date,'yyyy-MM-dd') AND end_date)
        )
        GROUP BY assignment
        ";

        // Note:
        // This method uses the PDO library instead of Yii methods so the query can retrieve 2 selects back from the same commands

        $db = new PDO(Yii::app()->db->connectionString, Yii::app()->db->username, Yii::app()->db->password);
        $db->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);

        $criteria = new CDbCriteria();
        $criteria->select = 'id';
        if ($source)
            $criteria->condition = "engine_source = $source";

        $engines = EngEngines::model()->findAll($criteria);
        $engines_count = count($engines);

        $retval = array(
            'Dedicated Service' => 0.0,
            'Pre Risk' => 0.0,
            'Response' => 0.0,
            'On Hold' => 0.0,
            'Staged' => 0.0,
            'Out of Service' => 0.0,
            'In Storage' => 0.0,
            'Total Utilized' => 0.0,
            'Non Utilized' => 0.0,
            'Adjusted Total Utilized' => 0.0, // For adjusted % by assignment
            'Adjusted Non Utilized' => 0.0    // For adjusted % by assignment
        );

        // Getting Each Individual Engine's Utilization (total and by assignment) and adding it to a cumulative array

        $results_array = array();

        foreach($engines as $engine)
        {
            $id = $engine->id;

            $command  = $db->prepare($sql);
            $command->bindParam(':startdate', $start_date, PDO::PARAM_STR);
            $command->bindParam(':enddate', $end_date, PDO::PARAM_STR);
            $command->setFetchMode(PDO::FETCH_ASSOC);
            $command->bindParam(':engineid', $id, PDO::PARAM_INT);
            $command->execute();

            // Get Totals Utilization Query
            $totals = $command->fetch();
            $retval['Total Utilized'] += $totals['total_utilization'];
            $command->nextRowset();

            // Get Utiliztion grouped by assignment
            $results = $command->fetchAll();

            $results_array = array_merge($results_array, $results);
        }

        // Adding cumulative results of engine utilized proportions to the $retval array tallied by each assignment

        foreach($results_array as $result)
            $retval[$result['assignment']] += $result['engine_utilization'];


        // To get the Total Utilization:
        // Alter the array by dividing each utilization value by the total number of engines
        // This normalizes the utiliztion to a % score.

        foreach ($retval as $key => &$value)  // '&' directs value pointer to the original value ... altering the array
        {
            $value = ($engines_count > 0) ? $value / $engines_count : $value / 1;
        }

        $retval['Non Utilized'] = 1 - $retval['Total Utilized'];

        // Calculating Adjusted amounts by 'Active' assignment types

        $retval['Adjusted Total Utilized'] = $retval['Total Utilized'] - ($retval['Staged'] + $retval['Out of Service'] + $retval['In Storage']);
        $retval['Adjusted Non Utilized'] = 1 - $retval['Adjusted Total Utilized'];

        return $retval;
    }

    /**
     * Based on passed in user_id and date, returns the schedule the user was on that day
     * @param int $user_id
     * @param string $date
     * @return EngScheduling
     */
    public static function getScheduleForUserByDate($user_id, $date)
    {
        $sql = '
            SELECT s.* FROM eng_scheduling_employee e
            INNER JOIN eng_scheduling s ON e.engine_scheduling_id = s.id
            WHERE crew_id IN (
                SELECT id FROM eng_crew_management
                WHERE [user_id] = :user_id
            )
            AND :date BETWEEN s.start_date AND s.end_date
            ORDER BY e.start_date DESC;';

        return self::model()->findBySql($sql, array(
            ':user_id' => $user_id,
            ':date' => $date
        ));
    }

    public static function getSchedulesList($date, $crew_id = null)
    {
        $sql = "
            SELECT s.id,
            CONCAT
            (
                e.engine_name, ' | ' ,s.assignment,
                (CASE WHEN f.[Name] IS NOT NULL THEN CONCAT(' | ', f.[Name]) ELSE '' END),
                (CASE WHEN s.resource_order_id IS NOT NULL THEN CONCAT(' | RO# ', resource_order_id) ELSE '' END)
            ) AS assignment
            FROM eng_scheduling s
            INNER JOIN eng_engines e ON s.engine_id = e.id
            LEFT JOIN res_fire_name f ON s.fire_id = f.Fire_ID
            WHERE :date BETWEEN CONVERT(DATE, s.start_date) AND CONVERT(DATE, s.end_date)
            ";
        if(isset($crew_id))
        {
            $sql .= "AND s.id IN (SELECT engine_scheduling_id FROM eng_scheduling_employee WHERE crew_id = $crew_id)";
        }
        $schedules = self::model()->findAllBySql($sql, array(
            ':date' => $date
        ));
        return CHtml::listData($schedules, 'id', 'assignment');
    }

    /**
     * Return all user_ids tied to a schedule
     * @param int $schedule_id
     * @return int[] array of user_ids
     */
    public static function getUserIDsOnSchedule($schedule_id)
    {
        $schedule = self::model()->findByPk($schedule_id);
        $returnArray = array();

        if(isset($schedule->employees))
        {
            foreach($schedule->employees as $employee)
            {
                if(!empty($employee->crew->user_id))
                {
                    $returnArray[] = $employee->crew->user_id;
                }
            }
        }

        return $returnArray;
    }
}
