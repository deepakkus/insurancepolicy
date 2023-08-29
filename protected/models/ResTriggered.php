<?php

/**
 * This is the model class for table "res_triggered".
 *
 * The followings are the available columns in table 'res_triggered':
 * @property integer $id
 * @property integer $notice_id
 * @property integer $property_pid
 * @property integer $response_status
 * @property integer $coverage
 * @property integer $threat
 * @property double $distance
 * @property integer $priority
 * @property integer $client
 * @property integer $triggered
 * @property float $lat
 * @property float $lon
 * @property string $geog
 */
class ResTriggered extends CActiveRecord
{
    public $geog_lat;
    public $geog_lon;
    public $date_visited;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_triggered';
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
        // NOTE: you should only define rules for those attributes that will receive user inputs.
        return array(
            array('notice_id, property_pid, response_status, threat, distance', 'required'),
            array('notice_id, property_pid, coverage, threat, priority, client, triggered', 'numerical', 'integerOnly' => true),
            array('response_status', 'length', 'max' => 25),
            array('distance', 'numerical'),
            // The following rule is used by search(). Remove those attributes that should not be searched.
            array('id, notice_id, property_pid, response_status, coverage, threat, distance, priority, client, triggered', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
            'resPropertyStatus' => array(self::HAS_ONE, 'ResPropertyStatus', 'res_triggered_id'),
            'resNotice' => array(self::HAS_ONE, 'ResNotice', 'notice_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'notice_id' => 'Notice',
            'property_pid' => 'Property Pid',
            'response_status' => 'Response Status',
            'coverage' => 'Coverage',
            'threat' => 'Threat',
            'distance' => 'Distance',
            'priority' => 'Priority',
            'client' => 'Client',
            'triggered' => 'Triggered'
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
    public function search($pageSize = NULL, $sort = NULL)
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('notice_id',$this->notice_id);
        $criteria->compare('property_pid',$this->property_pid);
        $criteria->compare('response_status',$this->response_status,true);
        $criteria->compare('coverage',$this->coverage);
        $criteria->compare('threat',$this->threat);
        $criteria->compare('coverage',$this->coverage);
        $criteria->compare('distance',$this->distance);
        $criteria->compare('priority',$this->priority);
        $criteria->compare('client',$this->client);
        $criteria->compare('triggered',$this->triggered);

        $sortWay = false; //false = DESC, true = ASC
        if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
            $sortWay = true;
        $sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

        $dataProvider = new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array($sort=>$sortWay),
                'attributes' => array(
                    '*',
                ),
            ),
            'criteria' => $criteria,
        ));

        if($pageSize == NULL)
        {
            $dataProvider->pagination = false;
        }
        else
        {
            $dataProvider->pagination->pageSize = $pageSize;
            $dataProvider->pagination->validateCurrentPage = false;
        }

        return $dataProvider;
    }

    //----------------------------------------------------Standard Yii--------------------------------------------------

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResTriggered the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeFind()
    {

        //Need to convert geometry type to wkt so it doesn't come through as binary (problems with reading and writing)
        if(isset($this->dbCriteria) && isset($this->dbCriteria->select) && $this->dbCriteria->select == '*')
        {
            $alias = $this->getTableAlias();
            $columnReplace = ($alias != 't') ? $alias . ".geog.ToString() as $alias.geog" : 't.geog.ToString() as geog';

            //Dynamically get all columns, than replace coords with the sql server function ToString() - wkt
            $columns = implode(',', array_keys($this->attributes));
            $select = str_replace("geog", $columnReplace,$columns);
            $this->dbCriteria->select = $select;
        }

        parent::beforeFind();
    }


    protected function afterFind()
    {
        // distance is a double and must be formatted to not display as "8.000000000000000E-2""
        Yii::app()->format->numberFormat = array('decimals'=>2, 'decimalSeparator'=>'.', 'thousandSeparator'=>'');
        $this->distance = Yii::app()->format->number($this->distance);

        return parent::afterFind();
    }

    //-----------------------------------------------------Virtual Attributes -------------------------------------------------------------

    /**
     * Returns the response status name for a given ID.
     * @param integer $responseStatusID
     * @return string response status name
     */
    public static function getResponseStatusName($responseStatusID)
    {
        if ($responseStatusID == 1)
            return 'Enrolled';
        else if ($responseStatusID == 0)
            return 'Not Enrolled';
        else
            return '';
    }

    //----------------------------------------------------- General Functions -------------------------------------------------------------

    /**
     * Returns the count of triggered or threatened
     * @param integer clientID
     * @param string dateStart
     * @param string dateEnd
     * @param integer threat (boolean)
     * @return integer count of threatened or triggered
     */
    public static function countTriggeredPolicyholders($clientID, $dateStart, $dateEnd, $threat = null)
    {
        if ($threat)
        {
            $sql = "select count(distinct property_pid) from res_triggered
            where notice_id in
            (
                select notice_id from res_notice where date_created >= '$dateStart' and date_created < '$dateEnd' and client_id = $clientID
            )
            and threat = 1";
        }
        else
        {
            $sql = "select count(distinct property_pid) from res_triggered
            where notice_id in
            (
                select notice_id from res_notice where date_created >= '$dateStart' and date_created < '$dateEnd' and client_id = $clientID
            )";
        }

        $count = ResTriggered::model()->countBySql($sql);

        return $count;
    }

    /**
     * Selects the triggered entries for a specific notice and client and creates a geojson object for a map layer
     * @param integer $noticeID
     * @param integer $clientID
     * @param integer $realTime
     * @return array object (geojson)
     */
    public static function getGeoJsonTriggered($noticeID, $clientID, $realTime = null)
    {
        if ($realTime)
        {
            $notice = ResNotice::model()->findByPk($noticeID);
            $fireID = $notice->fire_id;
			$alertDistance = 5;
            $sql = "
            DECLARE @clientID int = :clientID;
            DECLARE @fireID int = :fireID;
            SELECT
                t.property_pid,
                p.response_status,
                CASE p.response_status
                    WHEN 'enrolled' THEN c.enrolled_label
                    WHEN 'not enrolled' THEN c.not_enrolled_label
                    ELSE p.response_status
                END response_status_label,
                t.coverage,
                t.threat,
                t.distance,
                p.wds_lat lat,
                p.wds_long long,
                p.address_line_1,
                m.last_name,
                cl.id as call_list_id,
                a.attempts,
                p.policy,
                m.member_num,
                c.policyholder_label
            FROM
                res_triggered t
            INNER JOIN
                properties p ON p.pid = t.property_pid
            INNER JOIN
                members m ON m.mid = p.member_mid
            INNER JOIN
                client c ON c.id = t.client
            LEFT OUTER JOIN
            (
                SELECT id, property_id FROM res_call_list WHERE res_fire_id = @fireID and client_id = @clientID
            ) cl on cl.property_id = t.property_pid
            LEFT OUTER JOIN
            (
                SELECT COUNT(a.id) AS attempts, a.property_id
                FROM res_call_attempt a INNER JOIN res_call_list l on l.id = a.call_list_id
                WHERE a.res_fire_id = @fireID AND l.client_id = @clientID AND a.platform = 2 GROUP BY a.property_id
            ) a ON a.property_id = t.property_pid
            WHERE
                t.notice_id = :noticeID AND t.client = @clientID AND t.distance < :alertDistance";
            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':noticeID', $noticeID, PDO::PARAM_INT)
				->bindParam(':alertDistance', $alertDistance, PDO::PARAM_INT)
                ->bindParam(':fireID', $fireID, PDO::PARAM_INT)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT);
        }
        else
        {
            $sql = "
            SELECT
                t.property_pid,
                t.response_status,
                CASE t.response_status
                    WHEN 'enrolled' THEN c.enrolled_label
                    WHEN 'not enrolled' THEN c.not_enrolled_label
                    ELSE t.response_status
                END response_status_label,
                t.coverage,
                t.threat,
                t.distance,
                t.geog.Lat lat,
                t.geog.Long long,
                p.address_line_1,
                m.last_name,
                p.policy,
                m.member_num,
                c.policyholder_label
            FROM
                res_triggered t
            INNER JOIN
                properties p ON p.pid = t.property_pid
            INNER JOIN
                members m ON m.mid = p.member_mid
            INNER JOIN
                client c ON c.id = t.client
            WHERE
                t.notice_id = :noticeID AND t.client = :clientID";

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':noticeID', $noticeID, PDO::PARAM_INT)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT);
        }

        $models = $command->queryAll();

        $features = array();

        if ($models)
        {
            foreach ($models as $model)
            {
                $features[] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array((float)$model['long'], (float)$model['lat'])
                    ),
                    'properties' => array(
                        'threat' => $model['threat'],
                        'response_status' => $model['response_status'],
                        'response_status_label' => $model['response_status_label'],
                        'last_name' => $model['last_name'],
                        'address' => $model['address_line_1'],
                        'pid' => $model['property_pid'],
                        'call_list_id' => (isset($model['call_list_id'])) ? $model['call_list_id'] : null,
                        'attempts' => (isset($model['attempts'])) ? $model['attempts'] : 0,
                        'policy_num' => $model['policy'],
                        'policyholder_num' => $model['member_num'],
                        'policyholder_label' => $model['policyholder_label']
                    )
                );
            }
        }

        return array(
            'type' => 'FeatureCollection',
            'features' => $features
        );
    }

    /**
     * Selects the top 500 policies that are within 50 miles of the fire and creates a geojson object for a map layer
     * @param int $perimeterID
     * @param int $clientID
     * @return array geojson object
     */
    public static function getGeoJsonAll($perimeterID, $clientID, $realTime = null)
    {
        //Select policyholders within 50 miles
        $meters = Helper::milesToMeters(50);
        //Get the data (policyholders
        $models = GIS::getPolicyFromCentroidBuffer($perimeterID, $meters, array($clientID));

        $features = array();

        //Build the Geojson
        if ($models)
        {
            //Build the Geojson
            foreach ($models as $model)
            {
                $features[] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array((float)$model['long'], (float)$model['lat'])
                    ),
                    'properties' => array(
                        'threat' => 0,
                        'response_status' => $model['response_status'],
                        'response_status_label' => $model['response_status_label'],
                        'last_name' => $model['last_name'],
                        'address' => $model['address_line_1'],
                        'pid' => $model['pid'],
                        'policy_num' => $model['policy'],
                        'policyholder_num' => $model['member_num'],
                        'policyholder_label' => $model['policyholder_label']
                    )
                );
            }
        }

        return array(
            'type' => 'FeatureCollection',
            'features' => $features
        );
    }

    /**
     * Selects the triggered entires for multiple clients and notices and returns a geojson object used for a map layer. The response statuses are real time.
     * @param int $perimeterID
     * @param array $clientIDs
     * @return geojson array
     */
    public static function getGeoJsonMultipleClients($clientIDs, $perimeterID)
    {
        //Select policyholders within 15 miles
        $meters = Helper::milesToMeters(15);
        //Get the data (policyholders)
        $models = GIS::getPolicyFromCentroidBuffer($perimeterID, $meters, $clientIDs);

        //Build the Geojson
        if ($models)
        {
            $features = array();
            //Build the Geojson
            foreach ($models as $model)
            {
                $features[] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array((float)$model['long'], (float)$model['lat'])
                    ),
                    'properties' => array(
                        'threat' => 0,
                        'response_status' => $model['response_status'],
                        'response_status_label' => $model['response_status_label'],
                        'last_name' => $model['last_name'],
                        'address' => $model['address_line_1'],
                        'pid' => $model['pid'],
                        'client' => $model['client_name'],
                        'client_id' => $model['client_id'],
                        'enrolled_color' =>  $model['map_enrolled_color'],
                        'not_enrolled_color' =>  $model['map_not_enrolled_color'],
                        'distance' => round(Helper::metersToMiles($model['distance']), 2)
                    )
                );
            }

            return array(
                'type' => 'FeatureCollection',
                'features' => $features
            );
        }

        return null;
    }
}
