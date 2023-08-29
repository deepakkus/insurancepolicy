<?php

/**
 * This is the model class for table "res_monitor_log".
 *
 * The followings are the available columns in table 'res_monitor_log':
 * @property integer $Monitor_ID
 * @property string $Dispatcher
 * @property string $Monitor_Date
 * @property string $Monitor_Time
 * @property string $Name
 * @property string $Size
 * @property string $Fuel
 * @property string $Location
 * @property string $State
 * @property string $Coord_Lat
 * @property string $Coord_Long
 * @property string $Temperature
 * @property string $Wind_Direction
 * @property string $Wind_Speed
 * @property string $Wind_Gust
 * @property string $Humidity
 * @property string $Containment
 * @property string $Comments
 * @property integer $Update_Fire
 * @property integer $Obs_ID
 * @property string $monitored_date
 * @property integer $Smoke_Check
 * @property integer $Smoke_Check_DO_Review
 * @property string $Smoke_Check_Comments
 * @property string $Smoke_Check_Date
 * @property integer $Smokecheck_Email
 * @property integer $Perimeter_ID
 * @property integer $Alert_Distance
 * @property integer $Media_Event
 * @property integer $Zip_Codes
 * @property string $monitor_log_duty_officer_comments
 * @property integer $monitor_log_no_immediate_threat
 */
class ResMonitorLog extends CActiveRecord
{
    public $fire_id;
    public $fire_name;
    public $fire_alternate_name; 
    public $fire_size;
    public $fire_containment;
    public $fire_contained;
    public $fire_city;
    public $fire_state;
    public $fire_lat;
    public $fire_lon;

    public $monitored_time_stamp;
    public $monitored_date_stamp;

    // Array Variable for many to one clients column
    public $client_triggered;

    // Array Variable for many to one clients noteworthy column
    public $client_noteworthy;

    //For searching in grid
    public $client_id;

    //Hardcoding the grid
    public $smoke_check_only;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_monitor_log';
    }

    /**
     * @return string the name of this model
     */
    public static function modelName()
    {
        return __CLASS__;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('Dispatcher', 'required'),
            array('Update_Fire, Obs_ID, Smoke_Check, Smoke_Check_DO_Review, Smoke_Check_Email, Perimeter_ID, Alert_Distance, Media_Event, monitor_log_no_immediate_threat', 'numerical', 'integerOnly'=>true),
            array('Containment', 'length', 'max'=>25),
            array('Monitor_Date, Monitor_Time, Size', 'length', 'max'=>15),
            array('Name, Dispatcher', 'length', 'max'=>75),
            array('Smoke_Check_Date', 'length', 'max'=>20),
            array('Fuel', 'length', 'max'=>50),
            array('Location', 'length', 'max'=>125),
            array('State', 'length', 'max'=>2),
            array('Coord_Lat', 'length', 'max'=>9),
            array('Coord_Long', 'length', 'max'=>12),
            array('Temperature, Humidity', 'length', 'max'=>3),
            array('Wind_Direction, Wind_Speed, Wind_Gust', 'length', 'max'=>5),
            array('Smoke_Check_Comments', 'length', 'max'=>1000),
            array('Comments, monitor_log_duty_officer_comments', 'length', 'max'=>1000),
            array('Zip_Codes', 'length', 'max'=>1000),
            array('monitored_date, Smoke_Check, Smoke_Check_DO_Review, Perimeter_ID, Alert_Distance,  monitor_log_no_immediate_threat', 'safe'),

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array(
                'Monitor_ID, Dispatcher, Monitor_Date, Monitor_Time, Name, Size, Fuel, Location, State, Coord_Lat, Coord_Long, ' .
                'Temperature, Wind_Direction, Wind_Speed, Wind_Gust, Humidity, Containment, fire_id, fire_name, fire_alternate_name, ' .
                'fire_size, fire_containment, fire_city, fire_state, client_id, ' .
                'Media_Event, Zip_Codes', 'safe', 'on'=>'search'
            ),
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
            'resFireObs' => array(self::BELONGS_TO, 'ResFireObs', 'Obs_ID'),
            'resMonitorTriggered' => array(self::HAS_MANY, 'ResMonitorTriggered', 'monitor_id'),
            'resPerimeters' => array(self::BELONGS_TO, 'ResPerimeters', 'Perimeter_ID')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'Monitor_ID' => 'Monitor',
            'Dispatcher' => 'Dispatcher',
            'Monitor_Date' => 'Monitor Date',
            'Monitor_Time' => 'Monitor Time',
            'Name' => 'Name',
            'Size' => 'Size',
            'Fuel' => 'Fuel',
            'Location' => 'Location',
            'State' => 'State',
            'Coord_Lat' => 'Coord Lat',
            'Coord_Long' => 'Coord Long',
            'Temperature' => 'Temperature',
            'Wind_Direction' => 'Wind Direction',
            'Wind_Speed' => 'Wind Speed',
            'Wind_Gust' => 'Wind Gust',
            'Humidity' => 'Humidity',
            'Containment' => 'Containment',
            'Comments' => 'Client Facing Comments',
            'Update_Fire' => 'Update Fire',
            'Obs_ID' => 'Obs',
            'monitored_date' => 'Date',
            'Smoke_Check' => 'Smoke Check',
            'Smoke_Check_DO_Review' => 'Duty Officer Review',
            'Smoke_Check_Comments' => 'Internal Additional Smoke Check Comments',
            'Smoke_Check_Date'=>'Smoke Checked Date',
            'Smoke_Check_Email' => 'Smoke Check Email Sent',
            'Perimeter_ID' => 'Perimeter ID',
            'Alert_Distance' => 'Alert Distance',
            'Media_Event' => 'Media Event',
            'Zip_Codes' => 'Zip Codes',
			'monitor_log_duty_officer_comments' => 'Internal Duty Officer Comments',
			'monitor_log_no_immediate_threat' => 'No Immediate Threat for All Clients'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($columnsToShow = null)
    {
        $criteria = new CDbCriteria;

        $criteria->with = array(
            'resFireObs' => array(
                'select' => array('Fire_ID', 'Size', 'Containment')
            ),
            'resFireObs.resFireName' => array(
                'select' => array('Fire_ID', 'Name', 'City', 'Alternate_Name', 'State', 'Coord_Lat', 'Coord_Long', 'Contained', 'Smoke_Check')
            ),
            'resMonitorTriggered' => array(
                'select' => array('id', 'client_id', 'enrolled', 'eligible', 'closest', 'noteworthy')
            ),
            'resMonitorTriggered.client' => array(
                'select' => array('id', 'name')
            )
        );

        $criteria->select = array(
            't.Monitor_ID',
            't.Dispatcher',
            't.monitored_date',
            't.Comments',
            't.Update_Fire',
            't.Smoke_Check',
            't.Smoke_Check_DO_Review',
            't.Smoke_Check_Comments',
            't.Smoke_Check_Date',
            't.Smoke_Check_Email'
        );

        $criteria->compare('t.Dispatcher', $this->Dispatcher, true);
        $criteria->compare('t.Smoke_Check', $this->Smoke_Check);

        if ($this->monitored_date)
        {
            $monitored_date = strtotime($this->monitored_date);

            if ($monitored_date !== false && $monitored_date > strtotime('1753-01-01') && $monitored_date < strtotime('9999-12-31'))
            {
                $criteria->addCondition("monitored_date >= '" . date('Y-m-d', $monitored_date) . "' AND monitored_date < '" . date('Y-m-d', strtotime($this->monitored_date . ' + 1 day')) . "'");
            }
        }
        if ($this->smoke_check_only)
        {
            $criteria->addCondition('t.Smoke_Check = 1');
        }

        // Related table to search
        $criteria->compare('resFireName.Name', $this->fire_name, true);
        $criteria->compare('resFireName.City', $this->fire_city, true);
        $criteria->compare('resFireName.Alternate_Name', $this->fire_alternate_name, true);
        $criteria->compare('resFireName.State', $this->fire_state, true);
        $criteria->compare('resFireObs.Fire_ID', $this->fire_id, true);
        if(strtolower($this->fire_size) === 'unknown')
        {
            $criteria->compare('resFireObs.Size', -1);
        }
        elseif(($this->fire_size) == -1)
        {
           $criteria->compare('resFireObs.Size', ' ',true);
        }
        else
        {
           $criteria->compare('resFireObs.Size', $this->fire_size, true);
        }
        $criteria->compare('resFireObs.Containment', $this->fire_containment, true);

        if ($this->client_id)
        {
            $criteria->join = 'INNER JOIN [res_monitor_triggered] [m] ON [t].[monitor_id] = [m].[monitor_id]';
            $criteria->addCondition('m.client_id = ' . $this->client_id);
            $criteria->addCondition("(m.enrolled != '0' OR m.eligible != '0')");
        }

        return new WDSCActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('Monitor_ID' => CSort::SORT_DESC),
                'attributes' => array(
                    'fire_name' => array(
                        'asc' => 'resFireName.Name',
                        'desc' => 'resFireName.Name DESC',
                    ),
                    'fire_city' => array(
                        'asc' => 'resFireName.City',
                        'desc' => 'resFireName.City DESC',
                    ),
                    'fire_state' => array(
                        'asc' => 'resFireName.State',
                        'desc' => 'resFireName.State DESC',
                    ),
                    'fire_size' => array(
                        'asc' => 'resFireObs.Size',
                        'desc' => 'resFireObs.Size DESC',
                    ),
                    'fire_containment' => array(
                        'asc' => 'resFireObs.Containment',
                        'desc' => 'resFireObs.Containment DESC',
                    ),
                    '*'
                ),
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResMonitorLog the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function afterFind()
    {
        $this->monitored_time_stamp = date('H:i', strtotime($this->monitored_date));
        $this->monitored_date_stamp = date('Y-m-d', strtotime($this->monitored_date));
        $this->monitored_date = date('Y-m-d H:i', strtotime($this->monitored_date));

        if ($this->resFireObs)
        {
            $this->fire_size = $this->resFireObs->Size;
            $this->fire_containment = $this->resFireObs->Containment;

            if ($this->resFireObs->resFireName)
            {
                $this->fire_name = $this->resFireObs->resFireName->Name;
                $this->fire_alternate_name = $this->resFireObs->resFireName->Alternate_Name;
                $this->fire_city = $this->resFireObs->resFireName->City;
                $this->fire_state = $this->resFireObs->resFireName->State;
                $this->fire_lat = $this->resFireObs->resFireName->Coord_Lat;
                $this->fire_lon = $this->resFireObs->resFireName->Coord_Long;
                $this->fire_contained = $this->resFireObs->resFireName->Contained;
                $this->fire_id = $this->resFireObs->resFireName->Fire_ID;
            }
        }

        $this->client_triggered = array();

        if ($this->resMonitorTriggered)
        {
            foreach ($this->resMonitorTriggered as $entry)
            {
                if ($entry->enrolled || $entry->eligible)
                {
                    $this->client_triggered[] = $entry->client->name . ' (' . $entry->enrolled .  ' / ' . $entry->eligible . ')';
                }
            }
        }

        $this->client_noteworthy = array();

        if ($this->resMonitorTriggered)
        {
            foreach ($this->resMonitorTriggered as $entry)
            {
                if ($entry->noteworthy)
                {
                    $this->client_noteworthy[] = $entry->client->name;
                }
            }
        }

        if (!empty($this->Smoke_Check_Date))
        {
            $this->Smoke_Check_Date = date('Y-m-d H:i', strtotime($this->Smoke_Check_Date));
        }

        return parent::afterFind();
    }

    protected function beforeSave()
    {

        if ($this->isNewRecord)
        {
            $this->monitored_date = date('Y-m-d H:i');

            $updateBool = ResFireObs::model()->exists(array(
                'select' => 't.Fire_ID',
                'condition' => 't.Obs_ID in (select distinct res_monitor_log.Obs_ID from res_monitor_log) and t.Fire_ID = ' . $this->resFireObs->Fire_ID
            ));

            $this->Update_Fire = $updateBool ? 1 : 0;

            // Setting unmatched and zipcodes
            $zipcodes = GIS::getPerimeterZipcodes(null, $this->Perimeter_ID);
            $zipcodeNumbers = array_map(function($data) { return $data['zipcode']; }, $zipcodes);

            $this->Zip_Codes = implode(', ', $zipcodeNumbers);

            //Default is 5 miles
            $this->Alert_Distance = 5;
        }

        if ($this->Smoke_Check && empty($this->Smoke_Check_Date))
        {
            $this->Smoke_Check_Date = date('Y-m-d H:i');
        }

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        // Find out whta fire this is for
        $fireID = $this->resFireObs->resFireName->Fire_ID;

        // Run Query to fill out ResMonitorLogTriggered models, then store the perimeter ID that was used
        if ($this->isNewRecord)
        {
            $this->runAnalysis(null, $this->Perimeter_ID);
            if (!$this->Update_Fire)
            {
                $this->sendClientEmailNotification();
            }
        }
        else
        {

            $perimeterID = $this->Perimeter_ID;
            $buffer = ResPerimeterBuffers::model()->findBySql("select b.* from res_perimeter_buffers b
                inner join location l on l.id = b.location_id
                where b.perimeter_id = :perimeter_id
                and l.type = 'outer ring buffer'", array(':perimeter_id' => $perimeterID)
            );
            //If alert distance has increased, the outer ring needs to be updated
            if($buffer && $buffer->buffer_distance != $this->Alert_Distance)
            {
                ResPerimeterBuffers::updateOuterRingBuffer($this->Perimeter_ID, $this->Alert_Distance);
            }
        }

        // Set Fire to 'smokechecked'
        // Send Email notification (needs to be after the run analysis)
        if ($this->Smoke_Check)
        {
            $resFireName = ResFireName::model()->findByPk($fireID);
            $previouslySmokeChecked = $resFireName->Smoke_Check;
            $resFireName->saveAttributes(array('Smoke_Check' => 1));

            if (!$this->Smoke_Check_Email)
            {
                //Notify all WDS staff (duty officer, managers etc)
                $this->sendEmailNotification($previouslySmokeChecked);

                //Create auto notices which sends email to client
                $this->createAutoNotice();
            }
        }

        return parent::afterSave();
    }

    /**
     * Prefil Monitor Log comments if applicable
     * @param integer $obs_id
     */
    public function prefillMonitoringLogForm($obs_id)
    {
        $result = self::model()->find(array(
            'select' => 'Comments',
            'condition' => "Obs_ID IN (SELECT Obs_ID FROM res_fire_obs WHERE Fire_ID = (SELECT Fire_ID FROM res_fire_obs WHERE Obs_ID = :obs_id))",
            'order' => 'Monitor_ID desc',
            'limit' => 1,
            'params' => array(':obs_id' => $obs_id)
        ));

        if ($result)
        {
            $this->Comments = $result->Comments;
        }

    }

    /**
     * Runs analysis that populates the ResMonitorTriggered models for each response
     * client corresponding to the ResMonitorLog entry
     * @param integer $fireID
     * @return integer
     */
    public function runAnalysis($fireID = null, $perimeterID = null)
    {
        // Wasn't sure if we were using this anywhere else, so left it backwards compatable to work with the fire id. If we're
        // Not using anywhere else, than we can just use the relation
        $perimeter = ($fireID)
            ? ResPerimeters::model()->find(array('condition' => 'fire_id = ' . $fireID, 'order' => 'id DESC', 'limit' => 1))
            : ResPerimeters::model()->findByPk($perimeterID);

        if (empty($perimeter))
        {
            throw new CException(Yii::t('yii', "Perimeter wasn't found with value: {perimeter}.", array('{perimeter}' => var_export($perimeter, true))));
        }

        $clients = Client::model()->findAll('(wds_fire = 1 and active = 1) OR id = 999');

        $formatter = new CFormatter;
        $formatter->numberFormat = array('decimals' => 2, 'decimalSeparator' => '.', 'thousandSeparator' => '');

        $sql = "
            SET NOCOUNT ON

            DECLARE @noteworthyBufferMeters float(24) = :bufferDistance
            DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = :perimeter_id)
            DECLARE @noteworthyBuffer geography = @perimeter.STBuffer(@noteworthyBufferMeters)

            DECLARE @data TABLE(
                client_id int,
                response_status varchar(100),
                distance FLOAT(24)
            )

            INSERT INTO @data SELECT client_id, response_status, geog.STDistance(@perimeter) distance
            FROM properties
            WHERE policy_status = 'active' AND geog.STIntersects(@noteworthyBuffer) = 1

            SELECT
                client_id,
                SUM(CASE response_status WHEN 'enrolled' THEN 1 ELSE 0 END) enrolled,
                SUM(CASE response_status WHEN 'enrolled' THEN 0 ELSE 1 END) not_enrolled,
                ROUND(MIN(distance) / 1609.344, 2) distance,
                (
                    SELECT TOP 1 response_status
                    FROM @data inner_properties
                    WHERE inner_properties.client_id = data.client_id ORDER BY distance ASC
                ) response_status
            FROM @data data
            GROUP BY client_id";

        $results = Yii::app()->db->createCommand($sql)
            ->bindValue(':perimeter_id', $perimeter->id)
            ->bindValue(':bufferDistance', Helper::milesToMeters(3))
            ->queryAll();

        $clientTriggered = array_map(function($data) { return $data['client_id']; }, $results);
        $clientTriggered[] = 999;

        // Now find out if anybody is within 25 miles
        $sql = "DECLARE @meters float(24) = :bufferDistance
                DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = :perimeter_id)
                DECLARE @buffer geography = @perimeter.STBuffer(@meters)
                DECLARE @boundingboxgeom geometry = geometry::STGeomFromWKB(@buffer.STAsBinary(), @buffer.STSrid).STEnvelope()

                SELECT * FROM (
                    SELECT
                        ROUND(MIN(geog.STDistance(@perimeter)) / 1609.344, 2) distance,
                        response_status,
                        client_id
                    FROM properties
                    WHERE
                        client_id NOT IN (" . implode(",", $clientTriggered) . ")
                        AND policy_status = 'active'
                        AND wds_lat >= @boundingboxgeom.STPointN(1).STY
                        AND wds_lat <= @boundingboxgeom.STPointN(3).STY
                        AND wds_long <= @boundingboxgeom.STPointN(2).STX
                        AND wds_long >= @boundingboxgeom.STPointN(4).STX
                    GROUP BY client_id, response_status
                ) AS t
                WHERE t.distance <= @meters";

        $closestAll = Yii::app()->db->createCommand($sql)
                    ->bindValue(':perimeter_id', $perimeter->id)
                    ->bindValue(':bufferDistance', Helper::milesToMeters(25))
                    ->queryAll();

        // Reorganize to use for compairson later...
        $clientDistance = array();
        foreach ($closestAll as $closest)
        {
            if (!isset($clientDistance[$closest['client_id']]))
            {
                $clientDistance[$closest['client_id']] = array(
                    'distance' => $closest['distance'],
                    'response_status' => $closest['response_status']
                );
            }
            // If distance exists for this client, but this distance is closer, then use this distance instead
            else if (isset($clientDistance[$closest['client_id']]) && $closest['distance'] < $clientDistance[$closest['client_id']]['distance'])
            {
                // Overwriting previous value with closer one
                $clientDistance[$closest['client_id']] = array(
                    'distance' => $closest['distance'],
                    'response_status' => $closest['response_status']
                );
            }
        }

        // Will organize the results from sql into an array divided by clients
        $clientValues = array();

        // Break the array into client sections
        foreach ($results as $row)
        {
            $clientID = $row['client_id'];
            $clientValues[$clientID] = array(
                'enrolledCount' => $row['enrolled'],
                'notEnrolledCount' => $row['not_enrolled'],
                'distance' => $row['distance'],
                'response_status' => $row['response_status']
            );
        }

        foreach ($clients as $client)
        {
            // Get the smallest distance from the client/distance query - only need this if client wasn't triggered
            $clientClosestDistance = (isset($clientDistance[$client->id])) ? $formatter->formatNumber($clientDistance[$client->id]['distance']) : '25+';
            $clientClosestResponseStatus = (isset($clientDistance[$client->id])) ? $clientDistance[$client->id]['response_status'] : '';

            // Reset counting variables
            $noteworthy =  (isset($clientValues[$client->id])) ? 1 : 0;
            $enrolledCount = (isset($clientValues[$client->id])) ? $clientValues[$client->id]['enrolledCount'] : 0;
            $notEnrolledCount = (isset($clientValues[$client->id])) ? $clientValues[$client->id]['notEnrolledCount'] : 0;
            $distance = (isset($clientValues[$client->id])) ? $formatter->formatNumber($clientValues[$client->id]['distance']) : $clientClosestDistance;
            $responseStatus = (isset($clientValues[$client->id])) ? $clientValues[$client->id]['response_status'] : $clientClosestResponseStatus;

            // See if there's unmatched or not
            $unmatched = Helper::getUnmatchedForZipCodes(explode(', ', $this->Zip_Codes), $client->id);

            $monitorTriggered = new ResMonitorTriggered;
            $monitorTriggered->unmatched_enrolled = 0;
            $monitorTriggered->unmatched_not_enrolled = 0;

            foreach ($unmatched as $policy)
            {
                if ($policy['response_status'] === 'enrolled')
                    $monitorTriggered->unmatched_enrolled += $policy['count'];
                if ($policy['response_status'] !== 'enrolled')
                    $monitorTriggered->unmatched_not_enrolled += $policy['count'];
            }

            $unmatched = $monitorTriggered->unmatched_enrolled + $monitorTriggered->unmatched_not_enrolled;

            $monitorTriggered->monitor_id = $this->Monitor_ID;
            $monitorTriggered->client_id = $client->id;
            $monitorTriggered->enrolled = $enrolledCount;
            $monitorTriggered->eligible = $notEnrolledCount;
            if ($client->id != 999)
            {
                $monitorTriggered->closest = $distance;
                $monitorTriggered->closest_response_status = $responseStatus;
            }
            $monitorTriggered->noteworthy = 0;
            $monitorTriggered->unmatched = $unmatched;

            // Noteworthy Logic
            if ($this->Media_Event)
            {
                $monitorTriggered->noteworthy = 1;
            }
            else
            {
                $acres = isset($this->resFireObs->Size) ? (int)$this->resFireObs->Size : null;
                $noticeContained = ResNotice::model()
                    ->with(array('fireObs','fireObs.resFireName'))
                    ->exists('t.fire_id = :fireID and client_id = :clientID and fireObs.Containment = 100 and resFireName.Contained != 0', array(
                        ':fireID' => $fireID,
                        ':clientID' => $client->id
                    )
                );

                // Noteworthy if policy within client's noteworthy distance
                if ($noteworthy)
                    $monitorTriggered->noteworthy = 1;
                // Noteworthy if notice exists that fire obs has 100% Containment, but fire is not checked as Contained
                else if ($noticeContained)
                    $monitorTriggered->noteworthy = 1;
                // Noteworthy if acres over 1000 acres (checking both geography and Size ResFireName field)
                else if ($acres > 1000)
                    $monitorTriggered->noteworthy = 1;
            }

            $monitorTriggered->save();
        }

        return $perimeter->id;
   }

    /**
     * Sends the an email alert to recipients who have the "Dash Noteworthy Email" user type
     * @return bool
     */
    public function sendClientEmailNotification()
    {
        pclose(popen('start php ' . Yii::app()->basePath . DIRECTORY_SEPARATOR  . 'yiic ResSendClientEmail noteworthy ' . $this->Monitor_ID . ' >nul', 'r'));
    }

    /**
     * Returns clients models that are response
     * @return mixed
     */
    public function getAvailibleFireClients()
    {
        return Client::model()->findAll(array('order' => 'name ASC', 'condition' => 'wds_fire = 1 AND active = 1'));
    }

    /**
     * Sends the smokecheck email to everybody on the smokecheck@wildfire-defense.com email group (alerts them of a new fire/perimeter)
     * @return bool
     */
    public function sendEmailNotification($previouslySmokeChecked)
    {
        $fire = $this->resFireObs->resFireName;
        $fireDetails = $this->resFireObs;
        $triggers = $this->resMonitorTriggered;
        $cc = array('test@wildfire-defense.com');
        //Set email for production/training environment
        if((Yii::app()->params['env'] == 'pro'))
        {
            $cc = array('smokecheck@wildfire-defense.com');
        }
		else if((Yii::app()->params['env'] == 'trn'))
        {
            $cc = array('floor@wildfire-defense.com');
        }
        
        $link = Yii::app()->createAbsoluteUrl('resMonitorLog/viewMonitoredFire', array('page' => 'smokecheck', 'id' => $this->Monitor_ID), 'https');

        //Build the email content
        $subject = ($previouslySmokeChecked) ?  "(Update) " : "(New) ";
        $subject .= (Yii::app()->params['env'] == 'pro') ? "Smoke Check for " . $fire->Name . " - " . $fire->City . ", " . $fire->State : "!!!!! THIS IS A TEST FIRE !!!!! Smoke Check for " . $fire->Name . " - " . $fire->City . ", " . $fire->State;
        //Fire Name & Size
        $body = '<span style="font-size:22px; text-decoration: underline;">'. $fire->Name . ' - ' . $fire->City . ', ' . $fire->State . '</span>';
        $body .= '<ul>';
        $body .= '<li>Size: ' . $fireDetails->Size . '</li>';
        $body .= '<li>Containment: ' . $fireDetails->Containment . '</li>';
        $body .= '<li>Suppression: ' . $fireDetails->Supression . '</li>';
        $body .= '</ul>';

        //Weather
        $body .= '<span style="font-size:22px; text-decoration: underline;">Weather</span>';
        $body .= '<table cellpadding="15">';
        $body .= '<tr>';
        $body .= '<td valign="top">';
        $body .= '<p><strong>Current</strong></p>';
        $body .= '<ul style="padding:0;">';
        $body .= (empty($fireDetails->Gust)) ? '<li>Wind: ' . $fireDetails->Wind_Speed . ' mph ' . $fireDetails->Wind_Dir . '</li>' : '<li>Wind: ' . $fireDetails->Wind_Speed . ' mph, gusting ' . $fireDetails->Gust . ' mph ' . $fireDetails->Wind_Dir . '</li>';
        $body .= '<li>Temperature: ' . $fireDetails->Temp . '</li>';
        $body .= '<li>Humidity: ' . $fireDetails->Humidity . ' &#37;</li>';
        $body .= ($fireDetails->Red_Flags) ? '<li>Red Flags: YES</li>' : '<li>Red Flags: No</li>';
        $body .= '</ul>';
        $body .= '</td>';

        //Forecast Weather
        $body .= '<td valign="top">';
        $body .= '<p><strong>Forecast (' . $fireDetails->Fx_Time . ')</strong></p>';
        $body .= '<ul style="padding:0;">';
        $body .= (empty($fireDetails->Fx_Gust)) ? '<li>Wind: ' . $fireDetails->Fx_Wind_Speed . ' mph ' . $fireDetails->Fx_Wind_Dir . '</li>' : '<li>Wind: ' . $fireDetails->Fx_Wind_Speed . ' mph, gusting ' . $fireDetails->Fx_Gust . ' mph ' . $fireDetails->Fx_Wind_Dir . '</li>';
        $body .= '<li>Temperature: ' . $fireDetails->Fx_Temp . '</li>';
        $body .= '</ul>';
        $body .= '</td>';
        $body .= '</tr>';
        $body .= '</table>';

        // Weather Links
        $body .= '<span style="font-size:18px; text-decoration: underline;">Weather Links</span>';
        $body .= '<div style="margin: 20px 0 20px 0;">';
        $body .= '<a style="display: block;" href="http://forecast.weather.gov/MapClick.php?lon=' . $fire->Coord_Long . '&lat=' . $fire->Coord_Lat . '">NOAA weather link</a>';
        $body .= '<a style="display: block;" href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=' . $fire->Coord_Lat . ',' . $fire->Coord_Long . '">Weather Undergrouned weather link</a>';
        $body .= '</div>';
		//Internal Duty Officer Comments
		$body .= '<span style="font-size:22px; text-decoration: underline;">Internal Duty Officer Comments</span>';
        $body .= '<p>' . nl2br($this->monitor_log_duty_officer_comments) . '</p>';
		//staff Comments
		$body .= '<span style="font-size:22px; text-decoration: underline;">Staff Comments</span>';
        $body .= '<p>' . $this->Comments . '</p>';
        $body .= '<p>' . nl2br($this->Smoke_Check_Comments) . '</p>';
        if ($triggers)
        {
            //Policyholder info
            $body .= "<span style = 'font-size:22px; text-decoration: underline;'>Policyholders:</span>";

            foreach ($triggers as $trigger)
            {
                if ($trigger->client_id != 999)
                {
                    $body .= ($trigger->enrolled) ? '<p><strong>' . $trigger->client->name . '</strong> - Auto Notice Created</p>' : '<p><strong>' . $trigger->client->name . '</strong></p>';
                    $body .= '<ul>';
                    $body .= '<li>Enrolled: ' . $trigger->enrolled . '</li>';
                    $body .= '<li>Not Enrolled: ' . $trigger->eligible . '</li>';
                    if (!empty($trigger->closest_response_status))
                        $body .= '<li>Closest: ' . $trigger->closest . ' miles (' . ucwords($trigger->closest_response_status) . ')</li>';
                    else
                        $body .= '<li>Closest: ' . $trigger->closest . ' miles</li>';
                    $body .= '<li>Unmatched: ' . $trigger->unmatched_enrolled . ' Enrolled / ' . $trigger->unmatched_not_enrolled . ' Not Enrolled</li>';
                    $body .= '</ul>';
                }
            }
        }

        

        $body .= '<p>Maps, kmz and more detailed information can be viewed here: ' . $link . '</p>';

        $body .= '<p style="color:#888888;">CONFIDENTIALITY NOTE : The information in this e-mail is confidential and privileged; it is intended for use solely by the individual or entity named as the recipient hereof.
            Disclosure, copying, distribution, or use of the contents of this e-mail by persons other than the intended recipient is strictly prohibited and may violate applicable laws.
            If you have received this e-mail in error, please delete the original message and notify us by return email or phone call immediately.</p>';

        //Initialize email
        Yii::import('application.extensions.phpmailer.PHPMailer');
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->Host = Yii::app()->params['emailHost'];
        $mail->SMTPAutoTLS = false;
        $mail->SMTPOptions = Yii::app()->params['emailSMTPOptions'];
        $mail->Username = Yii::app()->params['emailUser'];
        $mail->Password = Yii::app()->params['emailPass'];
        $mail->SetFrom(Yii::app()->params['emailUser'], 'Wildfire Defense Systems');
        $mail->Subject = $subject;
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
        $mail->MsgHTML($body);
        $perimeterModel = ResPerimeters::model()->findByPk($this->Perimeter_ID);

        $kmz = new KMZSmokecheck(null, $fire->Name, $perimeterModel->id, $this->Alert_Distance);
        $filename = $kmz->createKMZ();
        $mail->AddAttachment($filename, basename($filename));

        //Client emails - don't have to hide them
        foreach ($cc as $address)
        {
            $mail->AddCC($address);
        }

        //Check for problems with sending email
        if ($mail->Send())
        {
            $kmz->removeKMZ();
            $this->isNewRecord = false;
            $this->saveAttributes(array("Smoke_Check_Email"=>1));
            return 1;
        }
        else
        {
           $kmz->removeKMZ();
            return 0;
        }
    }

    /*
     * Creates notices for all clients who have enrolled policyholders triggered
    */
    public function createAutoNotice()
    {

        if (isset($this->resMonitorTriggered))
        {
            //Fire that we're dealing with
            $fireID = $this->resFireObs->Fire_ID;
            //Now do one query to get all previous notices for all clients
            $sql = 'select client_id from res_notice where fire_id = :fireID group by client_id';
            $result = Yii::app()->db->createCommand($sql)
                ->bindParam(':fireID', $fireID, PDO::PARAM_INT)
                ->queryAll();
            $allPreviousNotices = array_map(function($data){return $data['client_id']; }, $result);
            foreach ($this->resMonitorTriggered as $resMonitorTriggered)
            {

                //Enrolled have to be triggered
                if ($resMonitorTriggered->enrolled > 0)
                {
                    //No previous notices - only for the first
                    $noPreviousNotices = (in_array($resMonitorTriggered->client_id, $allPreviousNotices)) ? 1 : 0;
                    if (!$noPreviousNotices)
                    {
                        $notice = new ResNotice;
                        $notice->recommended_action = 'New Fire - Status Pending';
                        $notice->wds_status = 2;
                        $notice->client_id = $resMonitorTriggered->client_id;
                        $notice->comments = $this->Comments . " Note: This information is subject to change as WDS continues to investigate this newly discovered fire. Updates to follow.";
                        $notice->obs_id = $this->Obs_ID;
                        $notice->fire_id = $fireID;
                        $notice->perimeter_id = $this->Perimeter_ID;
                        $notice->publish = 1;

                        if (!$notice->save())
                        {
                            //something...
                        }
                    }
                }
            }
        }

    }

    /**
     * Returns all noteworthy fires from the date provided to current
     * @param string $dateStart
     * @param string $dateEnd
     * @param integer $clientID
     * @param integer $noteworthy
     * @param integer $allFires
     * @return array
     */
    public static function getMonitoredFires($dateStart, $dateEnd = null, $clientID, $noteworthy, $allFires)
    {
        //Returned list of fires for the API
        $returnArray = array();
        $returnArray['data'] = array();
        //Default to current if no date is supplied
        $dateEnd = (!$dateEnd) ? date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d')))) : $dateEnd;

        if ($noteworthy)
        {
            // Noteworth Fires SQL
            $sql = "
                declare @clientID int = :clientID;
                declare @dateStart varchar(10) = :dateStart;
                declare @dateEnd varchar(10) = :dateEnd;
                select
                    f.name,
                    f.city,
                    f.state,
                    f.coord_lat,
                    f.coord_long,
                    f.contained,
                    f.contained_date,
                    o.size,
                    o.containment,
                    o.wind_speed,
                    o.wind_dir,
                    o.temp,
                    o.humidity,
                    o.red_flags,
                    m.monitor_id,
                    m.comments,
                    m.media_event,
                    m.monitored_date,
                    t.noteworthy,
                    t.enrolled,
                    t.eligible,
                    t.closest,
                    d.initial_date
                from
                    res_monitor_log m
                inner join
                    res_monitor_triggered t on t.monitor_id = m.monitor_id
                inner join
                    res_fire_obs o on o.obs_id = m.obs_id
                inner join
                    res_fire_name f on f.fire_id = o.fire_id
                inner join
                    (
                        -- get selection of monitored fires grouped by fire_id to join on
                        -- not joining on triggered table here to only get most recent monitored fire
                        select max(l.Monitor_ID) monitor_id, min(l.monitored_date) initial_date
                        from res_monitor_log l
                        inner join res_fire_obs o on o.obs_id = l.obs_id
                        where l.monitored_date >= @dateStart and l.monitored_date < @dateEnd
                        group by o.fire_id
                    ) d on d.monitor_id = m.Monitor_ID
                where
                    t.client_id = @clientID
                    and t.noteworthy = 1
                    and f.fire_id not in ( select fire_id from res_notice where client_id = @clientID )
                order by
                    monitor_id desc";
        }
        else if ($allFires)
        {
            // All Fires SQL - includes some notice data
            $sql = "
                declare @clientID int = :clientID;
                declare @dateStart varchar(10) = :dateStart;
                declare @dateEnd varchar(10) = :dateEnd;
                select
                    f.fire_id,
                    f.name,
                    f.city,
                    f.state,
                    f.coord_lat,
                    f.coord_long,
                    f.contained,
                    f.contained_date,
                    o.size,
                    o.containment,
                    o.wind_speed,
                    o.wind_dir,
                    o.temp,
                    o.humidity,
                    o.red_flags,
                    m.monitor_id,
                    m.comments,
                    m.media_event,
                    m.monitored_date,
                    t.noteworthy,
                    t.enrolled,
                    t.eligible,
                    t.closest,
                    n.wds_status,
                    n.notice_id,
                    d.initial_date
                from
                    res_monitor_triggered t
                inner join
                    res_monitor_log m  on t.monitor_id = m.monitor_id
                inner join
                    res_fire_obs o on o.obs_id = m.obs_id
                left outer join
                    (
                        select fire_id,  notice_id, wds_status
                        from res_notice
                        where notice_id in (select max(notice_id) from res_notice where client_id = @clientID group by fire_id)
                    ) n on n.fire_id = o.fire_id
                inner join
                    res_fire_name f on f.fire_id = o.fire_id
                inner join
                    (
                        select min(l.monitored_date) as initial_date, o.fire_id from res_monitor_log l
                        inner join res_fire_obs o on o.obs_id = l.obs_id
                        group by o.fire_id
                    ) d on d.fire_id = f.fire_id
                where
                    t.client_id = @clientID
                    and m.monitored_date >= @dateStart
                    and m.monitored_date < @dateEnd
                    and m.monitor_id in (
                        select max(monitor_id) as monitor_id
                        from res_monitor_log l
                        inner join res_fire_obs o on o.obs_id = l.obs_id
                        where
                            l.monitored_date >= @dateStart
                            and l.monitored_date < @dateEnd
                        group by o.fire_id
                    )
                order by
                    m.monitor_id desc;";
        }
        else
        {
            // Monitored Fires SQL - should exclude noteworthy fires
            $sql = "
                declare @clientID int = :clientID;
                declare @dateStart varchar(10) = :dateStart;
                declare @dateEnd varchar(10) = :dateEnd;
                select
                    f.name,
                    f.city,
                    f.state,
                    f.coord_lat,
                    f.coord_long,
                    f.contained,
                    f.contained_date,
                    o.size,
                    o.containment,
                    o.wind_speed,
                    o.wind_dir,
                    o.temp,
                    o.humidity,
                    o.red_flags,
                    m.monitor_id,
                    m.comments,
                    m.media_event,
                    m.monitored_date,
                    t.noteworthy,
                    t.enrolled,
                    t.eligible,
                    t.closest,
                    d.initial_date
                from
                    res_monitor_log m
                inner join
                    res_monitor_triggered t on t.monitor_id = m.monitor_id
                inner join
                    res_fire_obs o on o.obs_id = m.obs_id
                inner join
                    res_fire_name f on f.fire_id = o.fire_id
                inner join
                    (
                        -- get selection of monitored fires grouped by fire_id to join on
                        -- not joining on triggered table here to only get most recent monitored fire
                        select max(l.Monitor_ID) monitor_id, min(l.monitored_date) initial_date
                        from res_monitor_log l
                        inner join res_fire_obs o on o.obs_id = l.obs_id
                        where l.monitored_date >= @dateStart and l.monitored_date < @dateEnd
                        group by o.fire_id
                    ) d on d.monitor_id = m.Monitor_ID
                where
                    t.client_id = @clientID
                    and (t.noteworthy is null or t.noteworthy = 0)
                    and f.fire_id not in ( select fire_id from res_notice where client_id = @clientID )
                order by
                    monitor_id desc";
        }

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':dateStart', $dateStart, PDO::PARAM_STR)
            ->bindParam(':dateEnd', $dateEnd, PDO::PARAM_STR)
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->queryAll();

        foreach ($results as $row)
        {
            $returnData = array();
            $returnData = $row;
            // If fire has been contained, this will set the 'Updated' WDSFire column to the Contained Date
            if ($row['contained'] && !empty($row['contained_date']))
                $returnData['monitored_date'] = $row['contained_date'];

            $returnArray['data'][] = $returnData;
        }

        $returnArray['error'] = 0; // success

        return $returnArray;
    }

    /**
     * Returns some stats (acres, total fires, fires triggering, by state) on all fires monitored for the given client and for the timeframe
     * @param string $dateStart
     * @param string $dateEnd
     * @param integer $clientID
     * @return array
     */
    public static function getMonitoredFireSummary($dateStart, $dateEnd, $clientID)
    {
        $sql = "
            declare @dateStart varchar(10) = :dateStart;
            declare @dateEnd varchar(10) = :dateEnd;
            declare @clientID int = :clientID;

            select
                t1.acres,
                t1.number_fires,
                t2.fires_triggering,
                t3.policyholders_triggered,
                t1.state
            from (
                select
                    sum(case when o.size = '-1' then 0 else cast(o.size as float) end) as acres,
                    count(o.fire_id) as number_fires,
                    n.state
                from
                    res_fire_obs o
                inner join
                    res_fire_name n on n.fire_id = o.fire_id
                where
                    o.obs_id in (
                        select
                            max(m.obs_id)
                        from
                            res_monitor_log m
                        inner join
                            res_fire_obs o on o.obs_id = m.obs_id
                        inner join 
                            res_monitor_triggered t on t.monitor_id = m.Monitor_ID
                        where
                            m.monitored_date >= @dateStart
                            and m.monitored_date < @dateEnd
                            and t.client_id = @clientID
                            and m.monitor_id in (
                                select max(l.monitor_id) as monitor_id
                                from res_monitor_log l
                                inner join res_fire_obs o on o.obs_id = l.obs_id
                                where
                                    l.monitored_date >= @dateStart
                                    and l.monitored_date < @dateEnd
                                group by o.fire_id
                                )
                        group by
                            o.fire_id
                    )

                group by n.state
            ) t1
            left outer join (
                select
                    count(fire_id) as fires_triggering,
                    state
                from
                    res_fire_name
                where
                    fire_id in (
                        select
                            distinct fire_id
                        from
                            res_fire_obs
                        where
                            obs_id in (
                                select
                                    l.obs_id
                                from
                                    res_monitor_log l
                                inner join
                                    res_monitor_triggered t on t.monitor_id = l.Monitor_ID
                                where
                                    l.monitored_date >= @dateStart
                                    and l.monitored_date < @dateEnd
                                    and t.client_id = @clientID
                                    and (t.enrolled > 0 or t.eligible > 0)
                            )
                    )
                group by state
            ) t2 on t1.state = t2.state
            left outer join (
                select
                    SUM(subt.policyholders_triggered) as policyholders_triggered,
                    subt.state
                from (
                    select
                        max(cast(t.enrolled as int) + cast(t.eligible as int)) as policyholders_triggered,
                        n.state as state
                    from
                        res_monitor_triggered t
                    inner join
                        res_monitor_log l on l.monitor_id = t.monitor_id
                    inner join
                        res_fire_obs o on o.obs_id = l.obs_id
                    inner join
                        res_fire_name n on n.fire_id = o.fire_id
                    where
                        t.monitor_id in (
                            select max(monitor_id) as monitor_id
                            from res_monitor_log l
                            inner join res_fire_obs o on o.obs_id = l.obs_id
                            where
                                l.monitored_date >= @dateStart
                                and l.monitored_date < @dateEnd
                            group by o.fire_id
                        )
                        and t.client_id = @clientID
                    group by n.fire_id, n.state
                ) as subt
                group by subt.state
            ) t3 on t1.state = t3.state
        ";

        $results = Yii::app()->db->createCommand($sql)
               ->bindParam(':dateStart', $dateStart, PDO::PARAM_STR)
               ->bindParam(':dateEnd', $dateEnd, PDO::PARAM_STR)
               ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
               ->queryAll();

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $results;

        return $returnArray;
    }
}
