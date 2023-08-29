<?php

/**
 * This is the model class for table "res_fire_name".
 *
 * The followings are the available columns in table 'res_fire_name':
 * @property integer $Fire_ID
 * @property string $Name
 * @property string $Alternate_Name
 * @property string $City
 * @property string $State
 * @property string $Start
 * @property string $Start_Month
 * @property string $Start_Day
 * @property string $Start_Year
 * @property string $Timezone
 * @property integer $Chubb
 * @property integer $Liberty_Mutual
 * @property integer $USAA
 * @property integer $Contained
 * @property datetime $Contained_Date
 * @property string $Coord_Lat
 * @property string $Coord_Long
 * @property integer $Smoke_Check
 * @property integer $Crestbrook
 * @property string $Start_Date
 * @property string $Date_Created
 * @property string $Date_Updated
 * @property datetime $Cause
 * @property datetime $Estimated_Containment_Date
 * @property datetime $Location_Description
 *
 * The followings are the available model relations:
 * @property ResFuel[] $ResFuel
 * @property ResNotice[] $ResNotice
 * @property EngScheduling[] $EngScheduling
 * @property ResPerimeters[] $resPerimeters
 */
class ResFireName extends CActiveRecord
{
    public $perimeterExists;
    public $threatExists;
    public $fireObsExists;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_fire_name';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('Name, City, State, Contained', 'required'),
            array('Chubb, Liberty_Mutual, USAA, Contained, Smoke_Check, Crestbrook', 'numerical', 'integerOnly'=>true),
            array('City', 'length', 'max'=>50),
            array('State, Start_Month, Start_Day', 'length', 'max'=>2),
            array('Start', 'length', 'max'=>11),
            array('Start_Year', 'length', 'max'=>4),
            array('Timezone, Coord_Long, Cause', 'length', 'max'=>30),
            array('Coord_Lat', 'length', 'max'=>8),
            array('Location_Description', 'length', 'max'=>75),
            array('Name', 'length', 'max'=>200),
            array('Alternate_Name', 'length', 'max'=>100),
            array('Start_Date, Contained_Date, Date_Created, Date_Updated, Estimated_Containment_Date, Alternate_Name, DisasterGUID', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('Fire_ID, Name, City, State, Start, Start_Month, Start_Day, Start_Year, Timezone, Chubb, Liberty_Mutual, USAA, Contained, Contained_Date, '
                . 'Coord_Lat, Coord_Long, Smoke_Check, Crestbrook, Start_Date, Date_Created, Date_Updated, Cause, Estimated_Containment_Date, Location_Description, Alternate_Name', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'ResFuel' => array(self::HAS_MANY, 'ResFuel', 'Fire_ID'),
            'ResNotice' => array(self::HAS_MANY, 'ResNotice', 'fire_id'),
            'EngineScheduling' => array(self::HAS_MANY, 'EngScheduling', 'fire_id'),
            'resPerimeters' => array(self::HAS_MANY, 'ResPerimeters', 'fire_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'Fire_ID' => 'Fire',
            'Name' => 'Name',
            'Alternate_Name' => 'Alternate Name', 
            'City' => 'City',
            'State' => 'State',
            'Start' => 'Start',
            'Start_Month' => 'Start Month',
            'Start_Day' => 'Start Day',
            'Start_Year' => 'Start Year',
            'Timezone' => 'Timezone',
            'Chubb' => 'Chubb',
            'Liberty_Mutual' => 'Liberty Mutual',
            'USAA' => 'Usaa',
            'Contained' => 'Contained',
            'Contained_Date' => 'Contained Date',
            'Coord_Lat' => 'Coord Lat',
            'Coord_Long' => 'Coord Long',
            'Smoke_Check' => 'Smoke Check',
            'Crestbrook' => 'Crestbrook',
            'Start_Date' => 'Start Date',
            'Date_Created' => 'Date Created',
            'Date_Updated' => 'Date Updated',
            'Cause' => 'Cause',
            'Location_Description' => 'Location Description',
            'Estimated_Containment_Date' => 'Estimated Containment Date',
            'DisasterGUID' => 'Disaster GUID'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->alias = 'f';

        $criteria->select = array(
            '[f].[Fire_ID]',
            '[f].[Name]',
            '[f].[City]',
            '[f].[State]',
            '[f].[Start_Date]',
            '[f].[Date_Updated]',
            '[f].[Contained]',
            // Set based queries to determine is a perimeter/threat/fireObs exists
            '(SELECT TOP 1 1 FROM [res_perimeters] [p] WHERE [p].[fire_id] = [f].[Fire_ID]) [perimeterExists]',
            '(SELECT TOP 1 1 FROM [res_perimeters] [t] WHERE [t].[fire_id] = [f].[Fire_ID] AND [t].[threat_location_id] IS NOT NULL) [threatExists]',
            '(SELECT TOP 1 1 FROM [res_fire_obs] [o] WHERE [o].[Fire_ID] = [f].[Fire_ID]) [fireObsExists]'
        );

        $criteria->compare('[f].[Fire_ID]', $this->Fire_ID, true);
        $criteria->compare('[f].[Name]', $this->Name, true);
        $criteria->compare('[f].[City]', $this->City, true);
        $criteria->compare('[f].[State]', $this->State, true);
        $criteria->compare('[f].[Contained]', $this->Contained);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('Fire_ID' => CSort::SORT_DESC),
                'attributes' => array('*')
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    //----------------------------------------------------Standard Yii--------------------------------------------------
    #region Standard Yii

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResFireName the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        $this->Date_Updated = date('Y-m-d H:i');
        $this->DisasterGUID = '{00000000-0000-0000-0000-1000000000d1}';

        if ($this->isNewRecord)
            $this->Date_Created = date('Y-m-d H:i');

        // If contained date is blank, than we don't know it so set to null
        if (empty($this->Contained_Date))
        {
            $this->Contained_Date = null;
        }

        if(empty($this->Estimated_Containment_Date))
        {
            $this->Estimated_Containment_Date = null;
        }

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        // Saving Fuel Types to their related models
        $fuel_types = $_POST['ResFireName']['res_fuel_type'];

        if ($this->ResFuel)
        {
            // Delete current fuel record, if exists
            $criteria_delete = new CDbCriteria;
            $criteria_delete->addCondition('Fire_ID = ' . $this->Fire_ID);

            if (ResFuel::model()->exists($criteria_delete))
            {
                ResFuel::model()->deleteAll($criteria_delete);
            }
        }

        if (is_array($fuel_types))
        {
            // Create new Model Attributes
            foreach ($fuel_types as $fuel_type)
            {
                $criteria_fuel = new CDbCriteria;
                $criteria_fuel->select = 'ID';
                $criteria_fuel->addCondition("Type = '$fuel_type'");
                $id_object = ResFuelType::model()->find($criteria_fuel);

                $res_fuel = new ResFuel();
                $res_fuel->Fire_ID = $this->Fire_ID;
                $res_fuel->fuel_type_id = $id_object->ID;
                $res_fuel->save();
            }
        }

        if (isset(Yii::app()->session['firePerimeter']) && Yii::app()->session['firePerimeter'] && $this->isNewRecord)
        {
            // Saving perimeter to DB

            Yii::app()->user->setFlash('success', 'Perimeter for ' . CHtml::encode($this->Name) . ' added successfully!');

            $location = new Location;
            $location->geog = Yii::app()->session['firePerimeter'];
            $location->type = 'perimeter';
            $location->save();

            // If user has just added a new fire, than there is a perimeter in memory that needs to get saved
            $perimeter = new ResPerimeters;
            $perimeter->fire_id = $this->Fire_ID;
            $perimeter->perimeter_location_id = $location->getPrimaryKey();
            $perimeter->save();
        }

        if (isset(Yii::app()->session['centroidLong']))
            unset(Yii::app()->session['centroidLong']);

        if (isset(Yii::app()->session['centroidLat']))
            unset(Yii::app()->session['centroidLat']);

        unset(Yii::app()->session['firePerimeter']);

        Yii::app()->session['fireID'] = $this->Fire_ID;

        return parent::afterSave();
    }

    #endregion

    //---------------------------------------------------------------Virtual Attributes ------------------------------------------
    #region Virtual Attributes

    /**
     * Virtual attribute for Fuel Types (from the resFuelType model) as an array
     */
    public function getRes_fuel_type()
    {
        if (!isset($this->ResFuel))
            return null;

        return array_map(function($fuel) { return $fuel->resFuelType->Type; }, $this->ResFuel);
    }

    #endregion

    //------------------------------------------------------------General Functions ---------------------------------------------------
    #region General Functions

    public static function countFiresByDate($dateStart = null, $dateEnd = null)
    {
        if ($dateStart || $dateEnd)
        {
            $criteria = new CDbCriteria;
            $params = array();

            if ($dateStart)
            {
                $criteria->addCondition("Date_Created >= :dateStart");
                $params[':dateStart'] = $dateStart;
            }

            if ($dateEnd)
            {
                $criteria->addCondition("Date_Created <= :dateEnd");
                $params[':dateEnd'] = $dateEnd;
            }

            $criteria->params = $params;
            $total = ResFireName::model()->count($criteria);
        }
        else
        {
            $total = ResFireName::model()->count();
        }

        return $total;
    }

    /**
     * Goes through month by month and summarizes program fire activity
     * @param mixed $startDate - date to start query
     * @param mixed $endDate - date to end query
     * @param mixed $clientID - optional, client ID for program fires
     * @return array results for each month
     */

    public static function getHistoricalTally($startDate, $endDate, $clientID = null)
    {

        $returnArray = array();
        $clientID = (int) $clientID;
        $date1 = date('Y-m-d', strtotime($startDate));
        $date2 = date('Y-m-d', strtotime('+1 months', strtotime($date1)));

        //makes selections for each month
        while ($date2 <= $endDate)
        {
            $whereAllFires = ($clientID) ? "where date_created >= '$date1' and date_created <= '$date2' and client_id = $clientID" : "where date_created >= '$date1' and date_created <= '$date2'";
            $whereDispatchedFires = ($clientID) ? "where date_created >= '$date1' and date_created <= '$date2' and client_id = $clientID and wds_status = 1" : "where date_created >= '$date1' and date_created <= '$date2' and wds_status = 1";

            $sql = "fire_id in
            (
                select fire_id from res_notice
                $whereAllFires
                group by fire_id
            )";

            //Add totals per month into return array
            $monthEntry = array(
                'date'=> date('M', strtotime($date1)),
                'fires' => ResFireName::model()->count($sql)
            );

            $sql = "fire_id in
            (
                select fire_id from res_notice
                $whereDispatchedFires
                group by fire_id
            )";

            $monthEntry['dispatched_fires'] = ResFireName::model()->count($sql);

            //Add totals per month into return array
            $returnArray[] = $monthEntry;

            //incriment dates to the next month
            $date1 = $date2;
            $date2 = date('Y-m-d', strtotime('+1 months', strtotime($date1)));
        }

        return $returnArray;
    }

    /**
     * Get's the program fires along with dispatched, triggered, and location information for the given timeframe
     * @param mixed $startDate - date to start query
     * @param mixed $endDate - date to end query
     * @param mixed $clientID - optional, client ID for program fires
     * @return array - count and data for all the fires
     */
    public static function getProgramFiresByDate($startDate, $endDate, $clientID = null, $countTriggered = null)
    {
        //Sanatizing input parameters
        $clientID = (int) $clientID;
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));

        //Either a specific client, or all clients
        $clientStatement = ($clientID) ? "client_id = $clientID" : "client_id > 0";

        $sqlDispatched = "select * from res_fire_name where fire_id in
            (
                select fire_id from res_notice
                where date_created >= '$startDate' and date_created <= '$endDate' and $clientStatement
                group by fire_id
            )
            and fire_id in (select fire_id from res_notice where date_created >= '$startDate' and date_created <= '$endDate' and $clientStatement and wds_status = 1)";

        $sqlNotDispatched = "select * from res_fire_name where fire_id in
            (
                select fire_id from res_notice
                where date_created >= '$startDate' and date_created <= '$endDate' and $clientStatement and date_created >= '$startDate' and date_created <= '$endDate' and $clientStatement
                group by fire_id
            )
            and fire_id not in (select fire_id from res_notice where date_created >= '$startDate' and date_created <= '$endDate' and $clientStatement and wds_status = 1)";

        //Get all program fires
        $dispatchedFires = ResFireName::model()->findAllBySql($sqlDispatched);
        $dispatchedFiresTally = self::getProgramFiresList($dispatchedFires, 1, $startDate, $endDate, $clientID, $countTriggered);

        //Get all program fires
        $notDispatchedFires = ResFireName::model()->findAllBySql($sqlNotDispatched);
        $notDispatchedFiresTally = self::getProgramFiresList($notDispatchedFires, 0, $startDate, $endDate, $clientID, $countTriggered);

        $returnArray = array_merge($dispatchedFiresTally, $notDispatchedFiresTally);

        return $returnArray;

    }

    public static function getProgramFiresList($noticedFires, $wasDispatched, $startDate, $endDate, $clientID = null, $countTriggered = null){

        $returnArray = array();

        //Figure out which ones were dispatched and the total triggered
        foreach($noticedFires as $model){

            //Only get the total if we need it
            $totalTriggered = ($countTriggered) ? self::countTriggered($model->Fire_ID, $startDate, $endDate, $clientID) : 0;

            $modelAttributes = array();

            $modelAttributes['fire_name'] = $model->Name;
            $modelAttributes['fire_city'] = $model->City;
            $modelAttributes['fire_state'] = $model->State;
            $modelAttributes['was_dispatched'] = $wasDispatched;
            $modelAttributes['total_triggered'] = $totalTriggered;
            if(strtotime($model->Start_Date) < strtotime($startDate))
            {
                $modelAttributes['new'] = 0;
            }
            else
            {
                $modelAttributes['new'] = 1;
            }

            $returnArray[] = $modelAttributes;

        }

        return $returnArray;
    }

    public static function countTriggered($fireID, $startDate, $endDate, $clientID = null){

        if($clientID){
            $sql = "select count(distinct property_pid) from res_triggered where notice_id in (
                select notice_id from res_notice
                where
                    fire_id = :fireID and date_created >= :startDate
                    and date_created < :endDate
                    and client_id = :clientID
            )";

            $params = array(":fireID"=>$fireID, ":startDate"=>$startDate, ":endDate"=>$endDate, ":clientID"=>$clientID);

        }
        else{
            $sql = "select count(distinct property_pid) from res_triggered where notice_id in (
                select notice_id from res_notice
                where
                    fire_id = :fireID
                    and date_created >= :startDate
                    and date_created < :endDate
            )";

            $params = array(":fireID"=>$fireID, ":startDate"=>$startDate, ":endDate"=>$endDate);

        }

        $totalTriggered = ResTriggered::model()->countBySql($sql, $params);

        return $totalTriggered;
    }

    public static function getMonitoredFiresByDate($startDate, $endDate, $clientID = null)
    {

        $returnArray = array();

        $sql = "select * from res_fire_name where fire_id in
        (
            select o.fire_id from res_fire_obs o
            inner join res_monitor_log m on m.obs_id = o.obs_id
            where m.monitored_date >= :startDate and m.monitored_date < :endDate
        )";

        $params = array(':startDate'=>$startDate, ':endDate'=>$endDate);

        //Get all program fires
        $monitoredFires = ResFireName::model()->findAllBySql($sql, $params);

        //Figure out which ones were dispatched and the total triggered
        foreach($monitoredFires as $model){

            $modelAttributes = array();

            $modelAttributes['fire_name'] = $model->Name;
            $modelAttributes['fire_city'] = $model->City;
            $modelAttributes['fire_state'] = $model->State;

            $returnArray[] = $modelAttributes;

        }

        return $returnArray;

    }

    /*
     *  Tallys the fires by state
     *  Params:
     *  $models - (array) - array of notice objects
     */

    public static function getStateTally($models){
        $states = [];

        foreach($models as $model){
            if(isset($states[$model['fire_state']])){
                $states[$model['fire_state']]['fires'] +=1;
                $states[$model['fire_state']]['dispatched_fires'] += ($model['was_dispatched']) ? 1 : 0;
                $states[$model['fire_state']]['triggered'] +=  $model['total_triggered'];
            }

            else{
                $states[$model['fire_state']]['fires'] = 1;
                $states[$model['fire_state']]['dispatched_fires'] = ($model['was_dispatched']) ? 1 : 0;
                $states[$model['fire_state']]['triggered'] = $model['total_triggered'];
            }
        }

        //Sort by greatest to smallest
        arsort($states);

        return $states;

    }

    /*
     *  Get's the grand total of all dispatched, not-dispatched and all fires
     *  Params:
     *  $models - (array) - array of notice objects
     */
    public static function getTotalFiresAllStates($states){

        $total = [
            'fires'=>0,
            'dispatched_fires'=>0,
        ];

        foreach($states as $state){
            $total['fires'] += $state['fires'];
            $total['dispatched_fires'] += $state['dispatched_fires'];
        }

        return $total;
    }

    /*
     *  Get's fires that were near the given house, within the given timeframe
     *  Params:
     *  $pid - (int) - the id of the property
     *  $clientID (int) -  the id of the client
     *  $date (string) - the date to search back until
     */
    public static function getFiresByProperty($pid, $clientID, $date)
    {
        $sql = '
            declare @clientID int = :clientID
            declare @date datetime = :date
            declare @pid int = :pid
            declare @buffer float(24) = :buffer
            select
                f.fire_id,
                f.name,
                f.city,
                f.state,
                f.contained,
                o.size,
                o.containment,
                m.monitored_date,
                m.monitor_id,
                m.comments,
                n.notice_id,
                p.distance,
                t.threat,
                str(t.distance, 4, 2) as triggered_distance
            from
                res_monitor_log m
            inner join
                res_fire_obs o on m.obs_id = o.obs_id
            inner join
                res_fire_name f on f.fire_id = o.fire_id
            left outer join
            (
                select fire_id, publish, max(notice_id) as notice_id
                from res_notice
                where client_id = @clientID and date_created >= @date
                group by fire_id, publish
            ) n on n.fire_id = o.fire_id
            left outer join
            (
                select p.id, l.geog.STDistance((select geog from properties where pid = @pid and client_id = @clientID)) as distance
                from res_perimeters p
                inner join location l ON p.perimeter_location_id = l.id
                where l.geog.STIntersects((select geog.STBuffer(@buffer) from properties where pid = @pid and client_id = @clientID)) = 1
            ) p on p.id = m.perimeter_id
            left outer join
            (
                select t.threat, t.distance, n.fire_id from res_triggered t
                inner join res_notice n on n.notice_id = t.notice_id
                where t.notice_id in (
                        select
                            max(n.notice_id) as notice_id
                        from
                            res_triggered t
                        inner join
                            res_notice n on n.notice_id = t.notice_id
                        where
                            t.property_pid = @pid  and t.client = @clientID
                        group by
                            n.fire_id
                ) and t.property_pid = @pid
            ) t on t.fire_id = f.fire_id
            where
                m.monitor_id in (
                    select t.monitor_id from (
                        select
                            max(monitor_id) as monitor_id, fire_id
                        from
                            res_monitor_log m
                        inner join
                            res_fire_obs o on o.obs_id = m.obs_id
                        where
                            m.perimeter_id in (
                                select p.id
                                from res_perimeters p
                                inner join location l ON p.perimeter_location_id = l.id
                                where l.geog.STIntersects((select geog.STBuffer(@buffer) from properties where pid = @pid and client_id = @clientID)) = 1
                            )
                            and m.monitored_date >= @date
                        group by
                            o.fire_id
                        ) t
                )
            and n.publish = 1
            order by m.monitor_id desc
        ';

        $buffer = Helper::milesToMeters(10);

        $result = Yii::app()->db->createCommand($sql)
            ->bindParam(':pid', $pid)
            ->bindParam(':clientID', $clientID)
            ->bindParam(':buffer', $buffer)
            ->bindParam(':date', $date)
            ->queryAll();

        $i=0;
        foreach($result as $row)
        {
            $fireId = $row['fire_id'];       
            $sqlmaxNotice = "
                            declare @clientID int = :clientID;
                            select max(notice_id) as notice_id from res_notice 
                            where fire_id = ".$fireId." and client_id = @clientID;";
            $result_max_notice = Yii::app()->db->createCommand($sqlmaxNotice)
                ->bindParam(':clientID', $clientID)
                ->queryRow();
            $noticeId = $result_max_notice['notice_id'];
            if($noticeId > 0)
            {
            $sqlmaxNotice = "
                            declare @pid int = :pid;
                            select * from res_triggered 
                            where notice_id = ".$noticeId." and property_pid = @pid;";
            $getThreatDetails = Yii::app()->db->createCommand($sqlmaxNotice)
                ->bindParam(':pid', $pid)
                ->queryRow();
                if(isset($getThreatDetails['threat']) && ($getThreatDetails['threat'] == 1))
                {
                    $result[$i]['threat'] = 1;
                }
                else
                {
                    $result[$i]['threat'] = 0;
                }
          }
             $i++; 
        }

        //Format the meters to miles
        array_walk($result, function(&$row){
            $row['distance'] = ($row['triggered_distance']) ? $row['triggered_distance'] : Helper::metersToMiles($row['distance'], true);
        });

        return $result;
    }


    /*
     *  Get's fires that the given home has been triggered by on program fires (in the triggered table)
     *  Params:
     *  $pid - (int) - the id of the property
     *  $clientID (int) -  the id of the client
     */
    public static function getProgramFiresByProperty($pid, $clientID){
        $sql = "
            declare @clientID int = :clientID;
            declare @pid int = :pid;

            select
                n.notice_id,
                n.wds_status,
                n.date_created as monitored_date,
                n.date_updated,
                n.comments,
                f.name,
                f.city,
                f.state,
                f.fire_id,
                o.size,
                o.containment,
                t.threat,
                str(t.distance, 4, 2) as distance
            from
                res_notice n
            inner join
                res_fire_name f on f.fire_id = n.fire_id
            inner join
                res_fire_obs o on n.obs_id = o.obs_id
            inner join
            (
                select threat, distance, notice_id from res_triggered
                where notice_id in (
                        select
                            max(n.notice_id) as notice_id
                        from
                            res_triggered t
                        inner join
                            res_notice n on n.notice_id = t.notice_id
                        where
                            t.property_pid = @pid  and t.client = @clientID and n.publish = 1
                        group by
                            n.fire_id
                ) and property_pid = @pid
            ) t on t.notice_id = n.notice_id
            where
                n.notice_id in(
                    select t.notice_id from (
                        select
                            max(n.notice_id) as notice_id, n.fire_id
                        from
                            res_triggered t
                        inner join
                            res_notice n on n.notice_id = t.notice_id
                        where
                            t.property_pid = @pid  and t.client = @clientID and n.publish = 1
                        group by
                            n.fire_id
                    ) t
                )
            order by n.notice_id desc;
            ";

        $result = Yii::app()->db->createCommand($sql)
            ->bindParam(':pid', $pid, PDO::PARAM_INT)
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->queryAll();

        $i = 0;
        foreach($result as $row)
        {
            $fireId = $row['fire_id'];       
            $sqlmaxNotice = "
                            declare @clientID int = :clientID;
                            select max(notice_id) as notice_id from res_notice 
                            where fire_id = ".$fireId." and client_id = @clientID;";
            $resultMaxNotice = Yii::app()->db->createCommand($sqlmaxNotice)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
                ->queryRow();
            $noticeId = $resultMaxNotice['notice_id'];
            if($noticeId > 0)
            {
                $sqlmaxNotice = "
                                declare @pid int = :pid;
                                select * from res_triggered 
                                where notice_id = ".$noticeId." and property_pid = @pid;";
                $getThreatDetails = Yii::app()->db->createCommand($sqlmaxNotice)
                    ->bindParam(':pid', $pid, PDO::PARAM_INT)
                    ->queryRow();
                    if(isset($getThreatDetails['threat']) && ($getThreatDetails['threat'] == 1))
                    {
                        $result[$i]['threat'] = 1;
                    }
                    else
                    {
                        $result[$i]['threat'] = 0;
                    }
          }
             $i++; 
        }
        return $result;


    }


    #endregion
}
