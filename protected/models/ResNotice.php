<?php

/**
 * This is the model class for table "res_notice".
 *
 * The followings are the available columns in table 'res_notice':
 * @property integer $notice_id
 * @property integer $obs_id
 * @property integer $fire_id
 * @property integer $triggered_enrolled
 * @property integer $triggered_eligible
 * @property integer $threatened_enrolled
 * @property integer $threatened_eligible
 * @property integer $threatened_enrolled_exp
 * @property integer $threatened_eligible_exp
 * @property integer $triggered_enrolled_exp
 * @property integer $triggered_eligible_exp
 * @property integer $unmatched
 * @property string $zip_codes
 * @property string $recommended_action
 * @property string $evacuations
 * @property string $evac_effecting_policy
 * @property string $homes_lost
 * @property string $comments
 * @property string $notes
 * @property integer $publish
 * @property integer $client_id
 * @property string $date_created
 * @property string $date_updated
 * @property integer $email_notify_sent
 * @property integer $wds_status
 * @property int $perimeter_id
 * @property string $date_published
 * @property string $date_emailed
 * @property string $date_do_reviewed
 */
class ResNotice extends CActiveRecord
{
    // Related public variables
    public $fire_name;
    public $client_name;
    public $res_status;
    public $contained;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_notice';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('obs_id, fire_id, perimeter_id, wds_status', 'required'),
            array('obs_id, fire_id, triggered_enrolled, triggered_eligible, threatened_enrolled, threatened_eligible, threatened_enrolled_exp, threatened_eligible_exp, triggered_enrolled_exp, triggered_eligible_exp, unmatched, publish, client_id, email_notify_sent, wds_status, perimeter_id', 'numerical', 'integerOnly'=>true),
            array('zip_codes', 'length', 'max'=>1000),
            array('recommended_action', 'length', 'max'=>40),
            array('evacuations', 'length', 'max'=>9),
            array('evac_effecting_policy', 'length', 'max'=>8),
            array('homes_lost', 'length', 'max'=>125),
            array('comments, notes', 'length', 'max'=>2000),
            array('date_created, date_updated, date_published, date_emailed, date_do_reviewed', 'safe'),
            // Custom validation rules
            array('perimeter_id', 'isAllowedPerimeter'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('notice_id, obs_id, fire_id, triggered_enrolled, triggered_eligible, threatened_enrolled, threatened_eligible, threatened_enrolled_exp, threatened_eligible_exp, triggered_enrolled_exp, triggered_eligible_exp, unmatched, zip_codes, recommended_action, evacuations, evac_effecting_policy, homes_lost, comments, notes, publish, client_id, date_created, date_updated, email_notify_sent, wds_status, fire_name, client_name, res_status, contained, date_published, date_emailed, date_do_reviewed', 'safe', 'on'=>'search'),
        );
    }

    /**
     * Validation rule to determine if perimeter is allowed based on notice status type
     * @param integer $attribute
     */
    public function isAllowedPerimeter($attribute)
    {
        $shouldValidate = false;
        $validateReason = '';

        if (($this->wds_status == '2' && $this->recommended_action === 'Enrollment/Response Recommended') ||
                ($this->wds_status == '2' && $this->recommended_action === 'Potential Threat'))
        {
            $shouldValidate = true;
            $validateReason = '"Enrollment/Response Recommended" and "Potential Threat" notice perimeters must have a threat associated!';
        }

        if ($shouldValidate === true)
        {
            $sql = 'SELECT CASE WHEN threat_location_id IS NULL THEN 0 ELSE 1 END isAllowed FROM res_perimeters WHERE id = :perimeter_id';

            $result = Yii::app()->db->createCommand($sql)->queryScalar(array(
                ':perimeter_id' => $this->perimeter_id
            ));

            $isAllowed = filter_var($result, FILTER_VALIDATE_BOOLEAN);

            if ($isAllowed === false)
            {
                $this->addError($attribute, $validateReason);
            }
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'fire' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'fireObs'=>array(self::BELONGS_TO, 'ResFireObs', 'obs_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'resTriggered' => array(self::HAS_MANY, 'ResTriggered', 'notice_id'),
            'resStatus' => array(self::BELONGS_TO, 'ResStatus', 'wds_status'),
            'resPerimeters' => array(self::BELONGS_TO, 'ResPerimeters','perimeter_id'),
            'resTriageZone' => array(self::HAS_ONE, 'ResTriageZone', 'notice_id'),
            'resEvacZones' => array(self::HAS_MANY, 'ResEvacZone', 'notice_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'notice_id' => 'Notice',
            'obs_id' => 'Obs',
            'fire_id' => 'Fire',
            'triggered_enrolled' => 'Triggered Enrolled',
            'triggered_eligible' => 'Triggered Eligible',
            'threatened_enrolled' => 'Threatened Enrolled',
            'threatened_eligible' => 'Threatened Eligible',
            'threatened_enrolled_exp' => 'Threatened Enrolled Exp',
            'threatened_eligible_exp' => 'Threatened Eligible Exp',
            'triggered_enrolled_exp' => 'Triggered Enrolled Exp',
            'triggered_eligible_exp' => 'Triggered Eligible Exp',
            'unmatched' => 'Unmatched',
            'zip_codes' => 'Zip Codes',
            'recommended_action' => 'Recommended Action',
            'evacuations' => 'Evacuations',
            'evac_effecting_policy' => 'Evac Effecting Policy',
            'homes_lost' => 'Homes Lost',
            'comments' => 'Fire Summary',
            'notes' => 'WDS Actions',
            'publish' => 'Publish',
            'client_id' => 'Client',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
            'email_notify_sent' => 'Email Sent',
            'wds_status' => 'WDS Status',
            'perimeter_id' => 'Perimeter',
            'res_status' => 'Response Status',
            'date_published' => 'Date Published',
            'date_emailed' => 'Date Emailed',
            'date_do_reviewed' => 'Date Duty Officer Reviewed'
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
    public function search($startDate = null, $endDate = null, $evacZones = false)
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('client','fire','resStatus',);

        $criteria->compare('notice_id',$this->notice_id);
        $criteria->compare('obs_id',$this->obs_id);
        $criteria->compare('fire_id',$this->fire_id);
        $criteria->compare('triggered_enrolled',$this->triggered_enrolled);
        $criteria->compare('triggered_eligible',$this->triggered_eligible);
        $criteria->compare('threatened_enrolled',$this->threatened_enrolled);
        $criteria->compare('threatened_eligible',$this->threatened_eligible);
        $criteria->compare('threatened_enrolled_exp',$this->threatened_enrolled_exp);
        $criteria->compare('threatened_eligible_exp',$this->threatened_eligible_exp);
        $criteria->compare('triggered_enrolled_exp',$this->triggered_enrolled_exp);
        $criteria->compare('triggered_eligible_exp',$this->triggered_eligible_exp);
        $criteria->compare('unmatched',$this->unmatched);
        $criteria->compare('zip_codes',$this->zip_codes,true);
        $criteria->compare('recommended_action',$this->recommended_action,true);
        $criteria->compare('evacuations',$this->evacuations,true);
        $criteria->compare('evac_effecting_policy',$this->evac_effecting_policy,true);
        $criteria->compare('homes_lost',$this->homes_lost,true);
        $criteria->compare('comments',$this->comments,true);
        $criteria->compare('notes',$this->notes,true);
        $criteria->compare('publish',$this->publish);
        $criteria->compare('client_id',$this->client_id);
        $criteria->compare('date_created',$this->date_created,true);
        $criteria->compare('email_notify_sent',$this->email_notify_sent,true);
        $criteria->compare('wds_status',$this->wds_status,true);
        $criteria->compare('client.name',$this->client_name,true);
        $criteria->compare('fire.Name',$this->fire_name,true);
        $criteria->compare('resStatus.status_type',$this->res_status);
        $criteria->compare('fire.Contained',$this->contained,true);
        $criteria->compare('perimeter_id',$this->perimeter_id,true);
        $criteria->compare('date_emailed',$this->date_emailed,true);
        $criteria->compare('date_do_reviewed',$this->date_do_reviewed,true);

        //Narrow down notices by date ranges
        if($startDate)
        {
            $criteria->addCondition("t.date_created >= '$startDate'");
        }

        if($endDate)
        {
            $criteria->addCondition("t.date_created < '$endDate'");
        }
        if($this->date_published)
        {
            $date_time = strtotime($this->date_published);
            if ($date_time !== false && $date_time > strtotime('1753-01-01 00:00') && $date_time < strtotime('9999-12-31 23:59'))
	        {
		        $criteria->addCondition("t.date_published >= '" . date('Y-m-d', $date_time) . "' AND t.date_published < '" . date('Y-m-d', strtotime($this->date_published . ' + 1 day')) . "'");
	        }
        }
        if($this->date_updated)
        {
            $date_time = strtotime($this->date_updated);
            if ($date_time !== false && $date_time > strtotime('1753-01-01 00:00') && $date_time < strtotime('9999-12-31 23:59'))
	        {
		        $criteria->addCondition("t.date_updated >= '" . date('Y-m-d', $date_time) . "' AND t.date_updated < '" . date('Y-m-d', strtotime($this->date_updated . ' + 1 day')) . "'");
	        }
        }
        //if evacZones bool is set then only return notices with evac zones
        if($evacZones)
        {
            $criteria->with[] = 'resEvacZones';
            $criteria->addCondition("t.notice_id IN (SELECT notice_id FROM res_evac_zone)");
        }
       
        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder'=>array('notice_id'=>CSort::SORT_DESC),
                'attributes'=>array(
                    'fire_name'=>array(
                        'asc'=>'fire.Name ASC',
                        'desc'=>'fire.Name DESC',
                    ),
                    'client_name'=>array(
                        'asc'=>'client.name ASC',
                        'desc'=>'client.name DESC',
                    ),
                    'res_status'=>array(
                        'asc'=>'resStatus.status_type ASC',
                        'desc'=>'resStatus.status_type DESC',
                    ),
                    '*'
                ),
            ),
            'criteria'=>$criteria,
            'pagination' => array('PageSize'=>20)
        ));

    }

    //----------------------------------------------------Standard Yii--------------------------------------------------

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResNotice the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        if ($this->client_id == 999)
            $this->wds_status = 2;

        $this->date_updated =  date('Y-m-d H:i');

        // Setting Date Created
        if ($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');

        //Save the published time - this will only capture the original published time
        if($this->publish && empty($this->date_published))
            $this->date_published = date('Y-m-d H:i');
        elseif(empty($this->date_published))
            $this->date_published = null;

        //Save the duty officer reviewed time - this will only capture the original event
        if(empty($this->date_do_reviewed))
            $this->date_do_reviewed = date('Y-m-d H:i');
        elseif(empty($this->date_do_reviewed))
            $this->date_do_reviewed = null;

        //Send out email notification if it's published
        if($this->publish && !$this->email_notify_sent){
            $emailSent = $this->sendEmailNotification();
            if($emailSent){
                //Email sent, now record this in the notice
                $this->email_notify_sent = 1;
                $this->date_emailed = date('Y-m-d H:i');
            }

        }

        return parent::beforeSave();
    }

    protected function beforeDelete()
    {
        //Remove all the triggered entries that are related to the notice (orphaned data)
        ResTriggered::model()->deleteAll("notice_id = :notice_id",array(":notice_id"=>$this->notice_id));

        return parent::beforeDelete();
    }

    protected function afterSave()
    {
        //GIS Analysis
        if ($this->isNewRecord || Yii::app()->request->getPost('runAnalysis'))
        {
            $this->saveTriggered($this->perimeter_id, $this->client_id);
        }

        return parent::afterSave();
    }

    protected function afterFind()
    {
        if ($this->fire)
        {
            $this->fire_name = $this->fire->Name;
            $this->contained = $this->fire->Contained;
        }

        if ($this->client)
            $this->client_name = $this->client->name;

        if ($this->resStatus)
            $this->res_status = $this->resStatus->status_type;
        else
            $this->res_status = 'No Status Type';

        return parent::afterFind();
    }

    //-----------------------------------------------------Virtual Attributes -------------------------------------------------------------

    /**
     * Virtual attribute for Client Name - retreives the client name for the notice
     */
    public function getclientName()
    {
        if($this->client_id && $this->client_id > 0)
        {
            $client = Client::model()->findByPk($this->client_id);
            return $client->name;
        }

        return 'Pilot';
    }

    /**
     * Gets the fire name.
     */
    public function getFireName()
    {
        return isset($this->notice_id) ? $this->fire->Name : '';
    }

    //----------------------------------------------------- General Functions -------------------------------------------------------------

    public static function getDispatchedType($type)
    {
        if ($type === '1') { return 'Dispatched'; }
        if ($type === '2') { return 'Non Dispatched'; }
        if ($type === '3') { return 'Demobed'; }
        return '';
    }

    /**
     * Retrieves fire names by client.
     * @param string $clientName (optional) name of the client
     */
    public function getFireNames($clientName = null)
    {
        $criteria = new CDbCriteria;
        $criteria->select = 'fire_id';
        $criteria->group = 'fire_id';
        if ($clientName)
        {
            $criteria->condition = "client_id IN (select id from client where name = :clientName)";
            $criteria->params[':clientName'] = $clientName;
        }

        $unique_fires = ResNotice::model()->findAll($criteria);

        $fire_names = array_map(function($data) { if ($data->fire) { return array('name'=>$data->fire->Name); }  return array(); }, $unique_fires);

        asort($fire_names);

        return $fire_names;
    }

    /**
     * Retrieves a list of people and their details who were triggered by a fire.
     */
    public function getPolicyholdersByFire($fireID, $clientID)
    {
        $sql = '
        SELECT t.property_pid pid,
            max(t.priority) priority,
            max(t.threat) threat,
            max(m.first_name) fname,
            max(m.last_name) lname,
            max(p.address_line_1) address,
            max(p.city) city,
            max(p.state) state,
            max(p.zip) zip,
            max(p.coverage_a_amt) coverage,
            max(p.response_status) response_status,
            max(t.response_status) snapshot_status,
            max(t.distance) distance
        FROM res_notice n
            join res_triggered t on t.notice_id = n.notice_id
            join properties p on p.pid = t.property_pid
            join members m on m.mid = p.member_mid
        WHERE n.fire_id = :fire_id AND n.client_id = :client_id
        GROUP BY t.property_pid
        ORDER BY lname';

        $results = Yii::app()->db->createCommand($sql)
            ->bindValue(':fire_id', $fireID)
            ->bindValue(':client_id', $clientID)
            ->queryAll();

        return $results;
    }

    /**
     * Retrieves notices.
     * @param int $clientID - filters the data by client
     * @param string $startDate - all notices after or equal to this date
     * @param string $endDate - all notices prior to this date
     */
    public function getRecentNoticesByClient($clientID, $startDate, $endDate)
    {
        $sql = "
        DECLARE @clientID int = :clientID
        DECLARE @startDate datetime = :startDate
        DECLARE @endDate datetime = :endDate

        SELECT
            n.fire_id, n.notice_id, n.obs_id, n.recommended_action, n.comments, n.triggered_eligible_exp, n.triggered_enrolled_exp, n.triggered_eligible, n.triggered_enrolled, n.wds_status,
            f.Name, f.City, f.State, f.Start, f.Contained, f.Coord_Lat, f.Coord_Long,
            o.Containment, o.Size, n.date_created,
            CASE
                WHEN o.date_updated > n.date_updated AND (a.date_updated IS NULL OR o.date_updated > a.date_updated) THEN o.date_updated
                WHEN n.date_updated > o.date_updated AND (a.date_updated IS NULL OR n.date_updated > a.date_updated) THEN n.date_updated
                WHEN a.date_updated > o.date_updated AND a.date_updated > n.date_updated THEN a.date_updated
                ELSE n.date_updated
            END AS date_updated
        FROM res_notice n
        INNER JOIN res_fire_name f ON f.fire_id = n.fire_id
        CROSS APPLY ( SELECT TOP 1 o.containment, o.size, o.date_updated FROM res_fire_obs o WHERE o.fire_id = n.fire_id ORDER BY obs_id DESC ) o
        OUTER APPLY ( SELECT TOP 1 a.date_updated FROM res_policy_action a WHERE a.fire_id = n.fire_id and n.client_id = a.client_id ORDER BY action_id DESC ) a
        WHERE notice_id IN (
            SELECT MAX(n.notice_id) AS notice_id FROM res_notice n
            INNER JOIN res_fire_obs o ON o.fire_id = n.fire_id
            WHERE
            (
                (n.date_updated >= @startDate and n.date_updated < @endDate ) OR
                (o.date_updated >= @startDate and o.date_updated < @endDate )
            )
            AND n.date_created >= @startDate and n.date_created <= @endDate
            AND n.client_id = @clientID
            AND n.publish = 1
            GROUP BY n.fire_id
        )
        ORDER BY date_updated DESC";

        $command = Yii::app()->db->createCommand($sql);

        $command->bindValues(array(
            ':clientID' => $clientID,
            ':startDate' => $startDate,
            ':endDate' => $endDate
        ));

        $results = $command->queryAll();

        return $results;
    }

    /**
     * Retrieves notices.
     * @param int[] $clientIDs - filters the data by client
     * @param string $startDate - all notices after or equal to this date
     * @param string $endDate - all notices prior to this date
     */
    public function getRecentNoticesByClients($clientIDs, $startDate, $endDate)
    {
        $sql = "
        DECLARE @startDate datetime = :startDate
        DECLARE @endDate datetime = :endDate

        SELECT
            n.fire_id, n.notice_id, n.obs_id, n.recommended_action, n.comments, n.triggered_eligible_exp, n.triggered_enrolled_exp, n.triggered_eligible, n.triggered_enrolled, n.wds_status,
            f.Name, f.City, f.State, f.Start, f.Contained, f.Coord_Lat, f.Coord_Long,
            o.Containment, o.Size, n.date_created,
            CASE
                WHEN o.date_updated > n.date_updated AND (a.date_updated IS NULL OR o.date_updated > a.date_updated) THEN o.date_updated
                WHEN n.date_updated > o.date_updated AND (a.date_updated IS NULL OR n.date_updated > a.date_updated) THEN n.date_updated
                WHEN a.date_updated > o.date_updated AND a.date_updated > n.date_updated THEN a.date_updated
                ELSE n.date_updated
            END AS date_updated
        FROM res_notice n
        INNER JOIN res_fire_name f ON f.fire_id = n.fire_id
        CROSS APPLY ( SELECT TOP 1 o.containment, o.size, o.date_updated FROM res_fire_obs o WHERE o.fire_id = n.fire_id ORDER BY obs_id DESC ) o
        OUTER APPLY ( SELECT TOP 1 a.date_updated FROM res_policy_action a WHERE a.fire_id = n.fire_id and n.client_id = a.client_id ORDER BY action_id DESC ) a
        WHERE notice_id IN (
            SELECT MAX(n.notice_id) AS notice_id FROM res_notice n
            INNER JOIN res_fire_obs o ON o.fire_id = n.fire_id
            WHERE
            (
                (n.date_updated >= @startDate and n.date_updated < @endDate ) OR
                (o.date_updated >= @startDate and o.date_updated < @endDate )
            )
            AND n.client_id IN (" . join(',', $clientIDs) . ")
            AND n.publish = 1
            GROUP BY n.fire_id
        )
        ORDER BY date_updated DESC";

        $command = Yii::app()->db->createCommand($sql);

        $command->bindValues(array(
            ':startDate' => $startDate,
            ':endDate' => $endDate
        ));

        $results = $command->queryAll();

        return $results;
    }

    /**
     * Retrieves notices for the given timeframe ---- VERY similar to above...might consolidate these...
     * @param string $dateStart - all notices after or equal to this date
     * @param string $dateEnd - all notices prior to this date
     * @param int $clientID - filters the data by client
     */
    public static function getAllNoticesByClient($dateStart, $dateEnd, $clientID)
    {
        //Reformat dates
        $dateStart = date('Y-m-d', strtotime($dateStart));
        $dateEnd = date('Y-m-d', strtotime('+1 days', strtotime($dateEnd)));
        $returnArray = array();

        $sql = "
        DECLARE @clientID int = :clientID;
        DECLARE @dateStart datetime = :dateStart;
        DECLARE @dateEnd datetime = :dateEnd;

        SELECT
            f.fire_id,
            f.name,
            f.city,
            f.state,
            n.date_created,
            n.date_updated,
            n.triggered_enrolled,
            n.triggered_enrolled_exp,
            n.triggered_eligible,
            n.triggered_eligible_exp,
            n.threatened_enrolled,
            n.threatened_enrolled_exp,
            n.threatened_eligible,
            n.threatened_eligible_exp,
            n.recommended_action,
            s.status_type
        FROM res_notice n
            INNER JOIN res_fire_obs o ON o.obs_id = n.obs_id
            INNER JOIN res_fire_name f ON n.fire_id = f.fire_id
            INNER JOIN res_status s ON s.id = n.wds_status
        WHERE n.date_created >= @dateStart
            AND n.date_created < @dateEnd
            AND n.client_id = @clientID
        ORDER BY n.fire_id, n.notice_id DESC;";

        $result = Yii::app()->db->createCommand($sql)
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->bindParam(':dateStart', $dateStart, PDO::PARAM_STR)
            ->bindParam(':dateEnd', $dateEnd, PDO::PARAM_STR)
            ->queryAll();

        $returnArray['data'] = $result;
        $returnArray['error'] = 0;

        return $returnArray;
    }

    /**
     * Used for the dropdown in the create/update notice forms
     * @return string[]
     */
    public function getWdsStatus()
    {
        $returnArray = array('2'=>'Non-Dispatched', '1'=>'Dispatched', '3'=>'Demobilized');

        $hasDispatchedNotice = ResNotice::model()->exists("fire_id = :fire_id AND client_id = :client_id AND wds_status = (select id from res_status where status_type = 'Dispatched')", array(
            ':fire_id' => $this->fire_id,
            ':client_id' => $this->client_id
        ));

        return $returnArray;
    }

    /**
     * Description: Returns if a demobilized notice has been created for a given fire+client
     * @param int client_id
     * @param int fire_id
     * @return bool
     */
    public static function isClientFireDemob($fire_id, $client_id)
    {
        return ResNotice::model()->exists("fire_id = :fire_id AND client_id = :client_id AND wds_status = (SELECT id FROM res_status WHERE status_type = 'Demobilized')", array(
            ':fire_id' => $fire_id,
            ':client_id' => $client_id
        ));
    }

    /**
     * Used for the dropdown in the create/update notice forms
     * @return string[]
     */
    public function getRecommendedActions()
    {
        return array(
            'New Fire - Status Pending'=>'New Fire - Status Pending',
            'No Immediate Threat'=>'No Immediate Threat',
            'Enrollment/Response Recommended'=>'Enrollment/Response Recommended',
            'Potential Threat'=>'Potential Threat',
            'Program Responding'=>'Program Responding',
            'Program Resources on Incident'=>'Program Resources on Incident',
            'Program Resources Demobilized'=>'Program Resources Demobilized',
        );
    }

    /**
     * Used for the dropdown in the create/update notice forms
     * @return string[]
     */
    public function getEvacuations()
    {
        return array(
            'Yes' => 'Yes',
            'No' => 'No',
            'Voluntary' => 'Voluntary',
            'Unknown' => 'Unknown'
        );
    }

    public function getEvacAffectingPolicy()
    {
        return array(
            'Yes' => 'Yes',
            'No' => 'No',
            'Unknown' => 'Unknown'
        );
    }

    public function translateStatus($s)
    {
        $statuses = array('2'=>'Non-Dispatched', '1'=>'Dispatched', '3'=>'Demobilized');
        return $statuses[$s];
    }

    /**
     * Run the analysis on the policyholders and save the triggered while tallying the enrolled/eligible
     * @param integer $perimeterID
     * @param integer $clientID
     */
    private function saveTriggered($perimeterID, $clientID)
    {
        //Keep track of triggered/threatened enrolled and eligibles
        $noticeModelAttrs = array(
            'triggered_enrolled' => 0, 'threatened_enrolled' => 0, 'triggered_enrolled_exp' => 0, 'threatened_enrolled_exp' => 0,
            'triggered_eligible' => 0, 'threatened_eligible' => 0, 'triggered_eligible_exp' => 0, 'threatened_eligible_exp' => 0
        );

        //Get triggered people from current query & clear out the previously triggered people (for update notices)
        $result = GIS::runFireAnalysis($perimeterID, $clientID);

        ResTriggered::model()->deleteAll('notice_id = :notice_id', array(
            ':notice_id' => $this->notice_id
        ));

        //Now need to loop through all the triggered people and save them in the triggered table, as well as count the talleys and save into the notice table
        foreach ($result as $row)
        {
            //Create new triggered entry
            $triggered = new ResTriggered;
            $triggered->notice_id = $this->notice_id;
            $triggered->property_pid = $row['pid'];
            $triggered->distance = round($row['distance'] * 0.000621371, 2);
            $triggered->response_status =$row['response_status'];
            $triggered->client = $this->client_id;
            $triggered->threat = (isset($row['threat'])) ? $row['threat'] : 0;
            $triggered->coverage = $row['coverage_a_amt'];
            $triggered->triggered = 1;
            $triggered->geog = $row['geog'];

            $triggered->save();

            //Enrolled Count
            $noticeModelAttrs['triggered_enrolled'] += ($triggered->response_status == 'enrolled') ? 1 : 0;
            $noticeModelAttrs['threatened_enrolled'] += ($triggered->response_status == 'enrolled') ? $triggered->threat : 0;
            $noticeModelAttrs['triggered_enrolled_exp'] += ($triggered->response_status == 'enrolled') ? (int)$triggered->coverage : 0;
            $noticeModelAttrs['threatened_enrolled_exp'] += ($triggered->response_status == 'enrolled' && $triggered->threat) ? (int)$triggered->coverage : 0;
            //Not Enrolled Count
            $noticeModelAttrs['triggered_eligible'] += ($triggered->response_status != 'enrolled') ? 1 : 0;
            $noticeModelAttrs['threatened_eligible'] += ($triggered->response_status != 'enrolled') ? $triggered->threat : 0;
            $noticeModelAttrs['triggered_eligible_exp'] += ($triggered->response_status != 'enrolled') ? (int)$triggered->coverage : 0;
            $noticeModelAttrs['threatened_eligible_exp'] += ($triggered->response_status != 'enrolled' && $triggered->threat) ? (int)$triggered->coverage : 0;
        }

        // Setting unmatched and zipcodes
        $zipcodes = GIS::getPerimeterZipcodes($this->notice_id);
        $zipcodeNumbers = array_map(function($data) { return $data['zipcode']; }, $zipcodes);

        $criteria = new CDbCriteria;
        $criteria->with = array('member');
        $criteria->condition = "member.client = :clientName AND t.wds_geocode_level = 'UNMATCHED' AND t.policy_status = 'active'";
        $criteria->params = array(':clientName' => $this->client->name);
        $criteria->addInCondition('t.zip', $zipcodeNumbers);

        $noticeModelAttrs['unmatched'] = Property::model()->count($criteria);
        $noticeModelAttrs['zip_codes'] = implode(', ', $zipcodeNumbers);

        // Update notice, but don't call aftersave!
        ResNotice::model()->updateByPk($this->notice_id, $noticeModelAttrs);

        //Figure out if client needs call list
        $client = Client::model()->findByPk($clientID);

        if ($client->call_list || $client->client_call_list || $client->enrollment)
        {
            //Get all triggered properties for the given fire
            $triggeredProperties = ResTriggered::model()->findAllByAttributes(array('client' => $this->client_id, 'notice_id'=>$this->notice_id));

            //Go through each triggered property and add them to call list
            foreach($triggeredProperties as $property)
            {
                // Saving triggered to call list if they don't already exist
                if (!ResCallList::model()->exists('res_fire_id = :fireID and property_id = :pid', array(':fireID' => $this->fire_id, ':pid' => $property->property_pid)))
                {
                    $callListRecord = new ResCallList();
                    $callListRecord->res_fire_id = $this->fire_id;
                    $callListRecord->property_id = $property->property_pid;
                    $callListRecord->triggered = 1;
                    $callListRecord->client_id = $this->client_id;
                    $callListRecord->save();
                }
            }
        }
    }

    /**
     * Send email notificications
     * @return boolean|null
     */
    public function sendEmailNotification()
    {
        $returnValue = null;

        // Set local variables & objects
        $url = Yii::app()->params['wdsfireBaseUrl'];

        $client = Client::model()->findByPk($this->client_id);

        //---------------Get all users from parameters (clients to send email to)---------------------------

        $criteria = new CDbCriteria();
        $criteria->with = array('user_geo');
        $criteria->addCondition('t.active = 1');
        $criteria->addCondition('last_login > :last_login');
        $criteria->params = array(
            ':last_login' => date('Y-m-d H:i', strtotime('-10 months')),
            ':client_id' => $this->client_id,
            ':state' => $this->fire->State
        );

        // Get all users for the given client - hardcoded logic for liberty or safeco because of their 'unique' situation
        if ($this->client_id == 7 || $this->client_id == 3)
        {
            $criteria->addCondition("(t.client_id = :client_id OR t.type LIKE '%Dash LM All%')");
        }
        else
        {
            $criteria->addCondition("t.client_id = :client_id");
        }

        // Filter by state if necssary...only send emails out to people who are responsible for the state that the fire is in
        $criteria->addCondition('user_geo.geo_location = :state OR user_geo.geo_location is null');

        // Filter by wds status - dispatched or non-dispatched groups
        if ($this->wds_status == 1 || $this->wds_status == 3)
        {
            $criteria->addCondition("t.type LIKE '%Dash Email Group Dispatch%'");
        }
        else
        {
            $criteria->addCondition("t.type LIKE '%Dash Email Group Non-Dispatch%'");
        }

        $users = User::model()->findAll($criteria);

        // Create Email List based on the filters provided by API caller - these are for the client
        $bcc = array();
        foreach ($users as $user)
        {
            if ($user->email)
            {
                $bcc[] = $user->email;
            }
        }

        //-------Get WDS Users to send email to, need to keep separate because they'll be a BCC-----------

        $criteria = new CDbCriteria();
        $criteria->addCondition('wds_staff = 1');
        $criteria->addCondition('active = 1');
        $criteria->addCondition('last_login > :last_login');
        $criteria->params = array(
            ':last_login' => date('Y-m-d H:i', strtotime('-10 months'))
        );

        if ($this->wds_status == 1 || $this->wds_status == 3)
        {
            $criteria->addCondition("type LIKE '%Dash Email Group Dispatch%'");
        }
        else
        {
            $criteria->addCondition("type LIKE '%Dash Email Group Non-Dispatch%'");
        }

        $users = User::model()->findAll($criteria);

        // Get all WDS Staff that have the 'email notify' permission set
        foreach ($users as $user)
        {
            if ($user->email)
            {
                $bcc[] = $user->email;
            }
        }

        // Make sure that an email has not already been sent for the fire (user has clicked back button and accessed the url which hits the api again
        if (!$this->email_notify_sent)
        {
            // Default to true - it will get set to false if the email doesn't work
            $returnValue = true;

            $subject = (empty($this->recommended_action))
                ? $client->name . " " . $client->response_program_name . " - " . $this->fire->Name . " - " . $this->fire->City . ", " . $this->fire->State
                : $client->name . " " . $client->response_program_name . " - " . $this->recommended_action . " - " . $this->fire->Name . " - " . $this->fire->City . ", " . $this->fire->State;

            // Create Text for email
            $body = "<img src = 'http://www.wildfire-defense.com/images/wds-header.jpg' alt = 'Wildfire Defense Systems' /><br>";
            $body .= "<strong>Fire Name</strong><br>";
            $body .= $this->fire->Name . "<br><br>";
            $body .= "<strong>Location</strong><br> ";
            $body .=  $this->fire->City . ", " . $this->fire->State . "<br><br>";
            $body .= "<strong>WDS Status/Recommended Action</strong><br>";
            $body .= ($this->recommended_action == 'Enrollment/Response Recommended' || $this->wds_status == 1) ? "<font color = 'red'>" : '';
            $body .= $this->translateStatus($this->wds_status);
            $body .= (!empty($this->recommended_action)) ? " - " . $this->recommended_action . "<br><br>" : "<br><br>";
            $body .= ($this->recommended_action == 'Enrollment/Response Recommended' || $this->wds_status == 1) ? "</font>" : '';

            // Program Response from notice
            if (!empty($this->comments))
            {
                $body .= "<strong>Fire Summary</strong><br>";
                $body .= $this->comments . "<br><br>";
            }

            //Manager Notes from the notice
            if (!empty($this->notes))
            {
                $body .= "<strong>WDS Actions</strong><br>";
                $body .= $this->notes . "<br><br>";
            }

            $body .= "<strong>$client->name Staff</strong><br>";
            $body .= "For more information on this fire, please visit <a href = '$url'>$url</a> <br><br>";

            $body .= "<font color = '#777777'>CONFIDENTIALITY NOTE : The information in this e-mail is confidential and privileged; it is intended for use solely by the individual or entity named as the recipient hereof. Disclosure, copying, distribution, or use of the contents of this e-mail by persons other than the intended recipient is strictly prohibited and may violate applicable laws. If you have received this e-mail in error, please delete the original message and notify us by return email or phone call immediately. </font>";

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

            // Chunk emails into batches of 50 so we don't max out the google send limits
            foreach (array_chunk($bcc, 50) as $chunk)
            {
                //Build list
                foreach ($chunk as $address)
                {
                    $mail->AddBCC($address);
                }

                // Check for problems with sending email
                if (!$mail->Send())
                {
                    $returnValue = false;
                }

                //Clear the BCCs
                $mail->ClearBCCs();
            }

        }

        return $returnValue;
    }

    // Commented out 3/15/2017
    // Remove this method by 5/15/2017 if no issues have occured
    //public static function getRecentNotices($limit)
    //{
    //    $notices = ResNotice::model()->findAll(array("limit = $limit"));
    //}

    /**
     * Description: Count the total fires for all clients
     * @param $dateStart (optional) beginning range - can be simplye '2014'
     * @param $dateEnd (optional) ending range
     * @param $wdsStatus (optional) 1= dispatched, 2=not-dispatched, 3= dembalized
     * @return int number of entires that meets the criteria
    */
    public static function countProgramFires($dateStart = null, $dateEnd = null, $wdsStatus, $clientID = null)
    {
        $criteria = new CDbCriteria;
        $criteria->select = "fire_id";
        $criteria->group = "fire_id";

        if ($wdsStatus)
            $criteria->addCondition("wds_status = $wdsStatus");

        if ($dateStart)
            $criteria->addCondition("date_created >= '$dateStart'");

        if ($dateEnd)
            $criteria->addCondition("date_created <= '$dateEnd'");

        if ($clientID)
            $criteria->addCondition("client_id = $clientID");

        return ResNotice::model()->count($criteria);
    }

    /**
     * Description: Select the most recent notice for each fire that isn't contained
     * @param $clientID (int) id of the client
     * @return array an array of the dispatched and non dispatched fires w/ client
     */
    public static function getAllCurrentFires($clientID)
    {
        $returnArray = array(
            'dispatched'=> array(),
            'not_dispatched'=>array(),
            'demobalized'=>array()
        );

        $twoWeeksAgo = date('Y-m-d', strtotime('-2 weeks', strtotime(date('Y-m-d'))));

        $sql = "select * from res_notice where notice_id in
        (
            select max(notice_id) from res_notice
            where
                date_created >= :twoWeeksAgo
                and client_id = :clientID
            group by fire_id
        )";

        $models = ResNotice::model()->findAllBySql($sql, array(':clientID' => $clientID, ':twoWeeksAgo' => $twoWeeksAgo));

        foreach($models as $model)
        {
            if($model->wds_status == 1)
                $returnArray['dispatched'][] = $model;
            elseif($model->wds_status == 2)
                $returnArray['not_dispatched'][] = $model;
            elseif($model->wds_status == 3)
                $returnArray['demobalized'][] = $model;
        }

        return $returnArray;

    }

    /**
     * Get fire data for each fire that has been dispatched is not demobilized yet
     * @return array
     */
    public static function getDispatchedFireList($clientIds = array(), $startDate ='', $endDate = '', $details = '')
    {
        $appendSql = "";
        if($details)
        {
            $appendSql = " WHERE ";
        }
        $appendSql2 = "";
        $appendSql3 = "";
        if(!(empty($clientIds)))
        {
            $clientIdstr = "";
            foreach($clientIds as $clientId)
            {
                $clientIdstr .= $clientId.",";
            }
            $appendSql2 = " AND j.client_id IN ( ".substr($clientIdstr,0,-1).")";
            $appendSql3 = " WHERE client_id IN ( ".substr($clientIdstr,0,-1).")";
        }
        if($details)
        {
            $appendSql .= "o.Obs_ID in
               (
                SELECT MAX(Obs_ID) FROM res_fire_obs WHERE Fire_ID = n.fire_id
               )";
        }
        if($startDate)
        {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if($endDate)
        {
            $endDate = date('Y-m-d', strtotime($endDate. ' + 1 day'));
        }
        if($startDate && $endDate)
        {
           $appendSql .= " AND
            n.date_created >='".$startDate."' AND n.date_created <='".$endDate."'";
        }
        
        $sql = "
            SET NOCOUNT ON;

            DECLARE @DispatchedNoticeIDs TABLE(
                notice_id int,
                client_id int,
                client_name varchar(40)
            )

            -- Most recent currently dispatched for each client

            INSERT INTO @DispatchedNoticeIDs SELECT n.notice_id, n.client_id, c.name
            FROM res_notice n
            INNER JOIN client c ON n.client_id = c.id
            WHERE notice_id IN (
                SELECT MAX(notice_id) notice_id
                FROM res_notice
                GROUP BY fire_id, client_id
            ) AND wds_status = 1

            SELECT
                n.fire_id,
                f.name fire_name,
                f.state,
                f.city,
                SUM(cast(n.triggered_enrolled as bigint)) triggered_enrolled,
                SUM(cast(n.threatened_enrolled as bigint)) threatened_enrolled,
                SUM(cast(n.triggered_eligible as bigint)) triggered_eligible,
                SUM(cast(n.threatened_eligible as bigint)) threatened_eligible,
                SUM(cast(n.threatened_enrolled_exp as bigint)) threatened_enrolled_exp,
                SUM(cast(n.triggered_eligible_exp as bigint)) triggered_eligible_exp,
                SUM(cast(n.threatened_eligible_exp as bigint)) threatened_eligible_exp,
                p.id perimeter_id,
                g.Containment,
                g.Size,
                e.engines,
                STUFF((SELECT ',' + CONVERT(VARCHAR(100), j.client_name)
                    FROM res_notice t1
                    LEFT JOIN (
                        SELECT * FROM @DispatchedNoticeIDs
                    ) j ON t1.notice_id = j.notice_id
                    WHERE t1.fire_id = n.fire_id".$appendSql2."
                    GROUP BY j.client_id, j.client_name
                    ORDER BY j.client_name ASC
                    FOR XML PATH('')),1,1,'') client_names,
                STUFF((SELECT ',' + CONVERT(VARCHAR(100), j.client_id)
                    FROM res_notice t1
                    LEFT JOIN (
                        SELECT * FROM @DispatchedNoticeIDs
                    ) j ON t1.notice_id = j.notice_id
                    WHERE t1.fire_id = n.fire_id".$appendSql2."
                    GROUP BY j.client_id, j.client_name
                    ORDER BY j.client_name ASC
                    FOR XML PATH('')),1,1,'') client_ids
            FROM res_notice n
            INNER JOIN res_fire_name f on f.fire_id = n.fire_id
            INNER JOIN res_fire_obs o on o.fire_id = n.fire_id
            OUTER APPLY (
                -- Most recent perimeter/threat combo for each fire
                SELECT TOP 1 * FROM res_perimeters p WHERE p.fire_id = f.fire_id AND threat_location_id IS NOT NULL ORDER BY p.id DESC
            ) p
            INNER JOIN (
                SELECT * FROM @DispatchedNoticeIDs".$appendSql3."
            ) j ON n.notice_id = j.notice_id
            OUTER APPLY (
                SELECT COUNT(engine_id) engines FROM eng_scheduling sc WHERE n.fire_id = sc.fire_id
                GROUP BY sc.fire_id
            )e".$appendSql."
            OUTER APPLY(
			   SELECT 
				o.Obs_ID,
				o.Fire_ID,
				o.Containment Containment,
				o.Size Size
				from res_fire_obs o
				where
				o.Obs_ID IN (
				select max(o.Obs_ID) obsid from res_fire_obs o)
			   )g
            GROUP BY n.Fire_ID, f.Name, f.State, f.city, p.id, e.engines, g.Containment, g.Size
            ORDER BY f.name ASC
        ";
        $results = Yii::app()->db->createCommand($sql)->queryAll();

        return $results;
    }

    //Returns the total count of notices based on the
    public static function countNotices($dateStart = null, $dateEnd = null, $clientID = null, $wdsStatus = null)
    {
        $total = 0;

        if($dateStart || $dateEnd || $clientID){
            $criteria = new CDbCriteria;
            if($dateStart)
                $criteria->addCondition("date_created >= '$dateStart'");
            if($dateEnd)
                $criteria->addCondition("date_created <= '$dateEnd'");
            if($clientID)
                $criteria->addCondition("client_id = $clientID");
            if($wdsStatus)
                $criteria->addCondition("wds_status = $wdsStatus");

            $total = ResNotice::model()->count($criteria);
        }
        else{
            $total = ResNotice::model()->count();
        }

        return $total;
    }

    public function createEngineList()
    {
        Yii::import('application.vendors.PHPExcel.*');

        $sql = '
        SELECT
            m.last_name,
            m.first_name,
            p.address_line_1,
            p.city,
            p.zip,
            CASE t.threat WHEN 1 THEN \'Yes\' ELSE \'No\' END threat,
            t.distance,
            t.response_status
        FROM res_triggered t
            INNER JOIN properties p ON t.property_pid = p.pid
            INNER JOIN members m ON p.member_mid = m.mid
        WHERE t.notice_id = :notice_id and t.client = :client_id
        ORDER BY t.response_status ASC, t.threat DESC, t.distance ASC;';

        $policies = Yii::app()->db->createCommand($sql)
            ->bindValue(':notice_id', $this->notice_id)
            ->bindValue(':client_id', $this->client_id)
            ->queryAll();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('Engine List')
            ->setSubject('Engine List')
            ->setDescription('Engine List download from WDSAdmin.')
            ->setKeywords('office PHPExcel php')
            ->setCategory('engine list file');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Engine List');

        // Setting header
        $header = array('Last','First','Address','City','Zip','Threatened','Distance','Response Status');

        $style = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 11,
                'bold' => true,
                'color' => array('rgb' => '1F497D')
            ),
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('rbg' => '4F81BD')
                )
            )
        );

        $headerRange = PHPExcel_Cell::stringFromColumnIndex(0) . '1:' . PHPExcel_Cell::stringFromColumnIndex(count($header) - 1) . '1';
        $activeSheet->getStyle($headerRange)->applyFromArray($style);
        $activeSheet->getRowDimension(1)->setRowHeight(20);
        $activeSheet->fromArray($header, null, 'A1');

        $row = 2;
        foreach ($policies as $result)
        {
            $activeSheet->fromArray($result, null, 'A' . $row);
            $row++;
        }

        // Setting auto column widths
        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell)
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);

        return $objPHPExcel;
    }
}
