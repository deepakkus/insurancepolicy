<?php
/**
 * This is the model class for table "properties".
 *
 * The followings are the available columns in table 'properties':
 * @property integer $pid
 * @property integer $member_mid
 * @property string $address_line_1
 * @property string $address_line_2
 * @property string $city
 * @property string $county
 * @property string $state
 * @property string $zip
 * @property string $zip_supp
 * @property string $long
 * @property string $lat
 * @property string $dwelling_type
 * @property string $roof_type
 * @property string $lob
 * @property string $policy
 * @property string $policy_status
 * @property string $policy_status_date
 * @property integer $coverage_a_amt
 * @property string $policy_effective
 * @property string $policy_expiration
 * @property string $transaction_effective
 * @property integer $geo_risk
 * @property string $response_status
 * @property string $res_status_date
 * @property string $response_enrolled_date
 * @property string $fireshield_status
 * @property string $fs_status_date
 * @property string $pre_risk_status
 * @property string $pr_status_date
 * @property integer $fs_assessments_allowed
 * @property string $rated_company
 * @property string $geocode_level
 * @property string $brushfire_inspect
 * @property string $brushfire_inspect_date
 * @property string $last_update
 * @property string $comments
 * @property integer $agent_id
 * @property integer $flag //internal use
 * @property string $transaction_type
 * @property string $additional_contacts
 * @property string $producer
 * @property integer $response_auto_enrolled    // bool to indicate this was auto enrolled in the response program (for usaa use only currently)
 * @property integer $type_id                   // type of property (pif, risk, agent, etc ...)
 * @property string $wds_geocode_level
 * @property string $geog
 * @property integer $client_id
 * @property string $client_policy_id
 * @property string $wds_lat
 * @property string $wds_long
 * @property integer $question_set_id
 * @property string $wds_geocoder
 * @property string $wds_match_address
 * @property string $wds_match_score
 * @property string $wds_geocode_date
 * @property integer $multi_family
 * @property string $app_status
 * @property string $agency_code
 * @property string $agency_name
 * @property string $location
 * @property string $seq_num
 * @property string $wds_lob
 * @property string $risk_score_whp
 * @property string $risk_score_v
 * @property string $risk_score
 * @property string $risk_date_created
 * @property string $policyholder_name
 * @property string $agency_phone
 *
 * The followings are the available model relations:
 * @property Member $member
 * @property FSReport[] $fs_reports
 * @property StatusHistory $status_history
 * @property Agent $agent_id
 * @property Contact[] $contacts
 * @property ResPhVisit[] $ph_visits
 * @property PropertiesType[] $properties_type
 * @property PropertiesLocationHistory[] $properties_location_history
 * @property Client $client
 * @property ResPropertyAccess $res_property_access
 * @property ClientAppQuestionSet $question_set
 */

class Property extends CActiveRecord
{
    public $member_first_name;
    public $member_last_name;
    public $member_client;
    public $member_member_num;
    public $member_email_1;
    public $member_fs_carrier_key;
    public $member_home_phone;
    public $member_work_phone;
    public $member_cell_phone;
    public $property_type;
    public $distance;
    public $last_action;

    public $property_access_gate_code;

    public $geog_lat;
    public $geog_lon;
    public $is_search = false;

    public $geocoded = false;
    const GEOCODE_WDS = 'WDS';
    const GEOCODE_CLIENT = 'client';
    const GEOCODE_ADDRESS = 'address';
    const GEOCODE_UNMATCHED = 'unmatched';

    const GEOCODER_ESRI = 'esri';
    const GEOCODER_MAPBOX = 'mapbox';

    static public $wdsGeocodeTypes = array(
        self::GEOCODE_WDS,
        self::GEOCODE_CLIENT,
        self::GEOCODE_ADDRESS,
        self::GEOCODE_UNMATCHED
    );

    // These values are what we consider "good" geocode levels from our current client imported lists
    static public $goodGeocodeLevels = array(
        'ADDRESS',
        'PARCEL',
        'PP',
        'PR',
        'PS',
        'Point'
    );

    /**
     * Returns the static model of the specified AR class.
     * @return Property the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'properties';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('address_line_1, city, state, zip, policy', 'required'),
            array('member_mid, flag, fs_assessments_allowed, member_mid, geo_risk, coverage_a_amt, agent_id, response_auto_enrolled, type_id, question_set_id, multi_family, client_id', 'numerical', 'integerOnly'=>true),
            array('policy, lob, geocode_level, brushfire_inspect, wds_match_score, client_policy_id', 'length', 'max'=>15),
            array('rated_company', 'length', 'max'=>40),
            array('seq_num', 'length', 'max'=>10),
            array('policy_effective, policy_expiration, transaction_effective, response_enrolled_date, res_status_date, pr_status_date, fs_status_date, policy_status_date,
                brushfire_inspect_date, last_update,  wds_geocode_date', 'length', 'max'=>30),
            array('response_status, fireshield_status, pre_risk_status', 'in', 'range'=>$this->getProgramStatuses()),
            array('policy_status', 'in', 'range'=>$this->getPolicyStatuses()),
            array('transaction_type', 'in', 'range'=>$this->getTransactionTypes()),
            array('app_status', 'in', 'range'=>$this->getAppStatuses()),
            array('wds_lob', 'in', 'range'=>$this->getLOBTypes()),
            array('lat, long', 'numerical'),
            array('state, zip, zip_supp, long, lat, wds_geocode_level, wds_lat, wds_long, wds_geocoder, agency_code', 'length', 'max'=>25),
            array('address_line_2, city, county, dwelling_type, roof_type, location', 'length', 'max'=>50),
            array('address_line_1, producer, agency_name', 'length', 'max'=>100),
            array('wds_match_address', 'length', 'max'=>200),
            array('comments, additional_contacts', 'length', 'max'=>1000),
            array('risk_score_whp, risk_score_v, risk_score, risk_date_created, policyholder_name', 'safe'),
            array('pid, member_mid, address_line_1, address_line_2, city, county, state, zip, zip_supp, long, lat, dwelling_type, roof_type,
                geo_risk, response_status, res_status_date, response_enrolled_date, fireshield_status, fs_status_date, pre_risk_status, pr_status_date,
                coverage_a_amt, policy, policy_status, policy_status_date, policy_effective, policy_expiration, transaction_effective, lob, fs_assessments_allowed,
                rated_company, geocode_level, brushfire_inspect, brushfire_inspect_date, fullName, member_first_name, member_last_name, member_fs_carrier_key,
                member_home_phone, member_work_phone, member_cell_phone, member_email_1, client_id, member_member_num, agent_id, flag, additional_contacts,
                type_id, property_type, question_set_id, wds_geocode_level, wds_geocoder, wds_match_address, wds_match_score, wds_geocode_date, multi_family,
                agency_name, agency_code, property_access_gate_code, wds_lob, risk_score_whp, risk_score_v, risk_score, risk_date_created, agency_phone', 'safe', 'on'=>'search'),
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
            'member' => array(self::BELONGS_TO, 'member', 'member_mid'),
            'fs_reports' => array(self::HAS_MANY, 'FSReport', 'property_pid'),
            'status_history' => array(self::HAS_MANY, 'StatusHistory', 'table_id'),
            'agent_id' => array(self::BELONGS_TO, 'Agent', 'id'),
            'contacts' => array(self::HAS_MANY, 'Contact', 'property_pid'),
            'ph_visits' => array(self::HAS_MANY, 'ResPhVisit', 'property_pid'),
            'properties_type' => array(self::BELONGS_TO, 'PropertiesType', 'type_id'),
            'properties_location_history' => array(self::HAS_MANY, 'PropertiesLocationHistory', 'property_pid'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'res_property_access' => array(self::HAS_ONE, 'ResPropertyAccess', 'property_id'),
            'question_set' => array(self::BELONGS_TO, 'ClientAppQuestionSet', 'question_set_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'pid' => 'PID',
            'member_mid' => 'MID',
            'address_line_1' => 'Address Line 1',
            'address_line_2' => 'Address Line 2',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip Code',
            'zip_supp' => 'Zip Supplement',
            'long' => 'Client Longitude',
            'lat' => 'Client Latitude',
            'dwelling_type' => 'Dwelling Type',
            'roof_type' => 'Roof Type',
            'policy' => 'Policy',
            'policy_status' => 'Policy Status',
            'policy_status_date' => 'Policy Status Date',
            'coverage_a_amt' => 'Coverage A Amount',
            'policy_effective' => 'Policy Effective Date',
            'policy_expiration' => 'Policy Expiration Date',
            'transaction_effective' => 'Transaction Effective Date',
            'transaction_type' => 'Transaction Type',
            'lob' => 'LOB',
            'geo_risk' => 'Geo Risk',
            'response_status' => 'Response Status',
            'res_status_date' => 'Response Status Date',
            'response_enrolled_date' => 'Response Enrolled Date',
            'fireshield_status' => 'Fireshield Status',
            'fs_status_date' => 'Fireshield Status Date',
            'pre_risk_status' => 'Pre-Risk Status',
            'pr_status_date' => 'Pre-Risk Status Date',
            'fs_assessments_allowed' => "FS Assessments Allowed",
            'rated_company' => "Rated Company",
            'geocode_level' => "Client Geocode Level",
            'brushfire_inspect' => 'Brushfire Inspection',
            'brushfire_inspect_date' => 'Brushfire Inspection Date',
            'last_update' => 'Last Update',
            'comments' => 'Comments',
            'agent_id' => 'Agent ID',
            'additional_contacts' => 'Additional Contacts',
            'producer' => 'Producer Info',
            'response_auto_enrolled' => 'Response Auto Enrolled',
            'type_id' => 'Property Type',
            'property_type' => 'Property Type',
            'wds_geocode_level' => 'WDS Geocode Level',
            'client_id' => 'Client',
            'wds_lat' => 'WDS Latitude',
            'wds_long' => 'WDS Longitude',
            'question_set_id' => 'Question Set ID',
            'wds_geocoder' => 'WDS Geocoder',
            'wds_match_address' => 'WDS Match Address',
            'wds_match_score' => 'WDS Match Score',
            'wds_geocode_date' => 'WDS Geocode Date',
            'multi_family' => 'Multi-Family Indicator',
            'app_status' => 'App Status',
            'agency_name' => 'Agency Name',
            'agency_code' => 'Agency Code',
            'location' => 'Location',
            'client_id' => 'Client',
            'wds_lob' => 'WDS LOB',
			'risk_score_whp' => 'WHP Score',
			'risk_score_v' => 'Vulnerability Score',
			'risk_score' => 'WDS Risk Score',
			'risk_date_created' => 'Risk Score Date',
            'agency_phone' => 'Agency Phone'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search($advSearch = NULL, $pageSize = 25, $sort = NULL)
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->with = array(
            'member' => array(
                'select' => array('mid','first_name','last_name','member_num','home_phone','work_phone','cell_phone','email_1','fs_carrier_key','client')
            ),
            'properties_type' => array(
                'select' => array('id','type')
            ),
            'res_property_access' => array(
                'select' => array('id','gate_code')
            ),
            'ph_visits' => array(
            'select' => array('status', 'id', 'property_pid', 'review_status','date_updated')
            )
        );
        $criteria->together = true;
        $criteria->compare('pid', $this->pid, true);
        $criteria->compare('member_mid', $this->member_mid);
        $criteria->compare('address_line_1', $this->address_line_1, true);
        $criteria->compare('address_line_2', $this->address_line_2, true);
        $criteria->compare('city', $this->city);
        $criteria->compare('state', $this->state);
        $criteria->compare('zip', $this->zip);
        $criteria->compare('zip_supp', $this->zip_supp);
        $criteria->compare('long', $this->long);
        $criteria->compare('lat', $this->lat);
        $criteria->compare('dwelling_type', $this->dwelling_type);
        $criteria->compare('roof_type', $this->roof_type);
        $criteria->compare('policy', $this->policy);
        $criteria->compare('policy_status', $this->policy_status);
        $criteria->compare('policy_status_date', $this->policy_status_date);
        $criteria->compare('coverage_a_amt', $this->coverage_a_amt);
        $criteria->compare('policy_effective', $this->policy_effective);
        $criteria->compare('policy_expiration', $this->policy_expiration);
        $criteria->compare('transaction_effective', $this->transaction_effective);
        $criteria->compare('transaction_type', $this->transaction_type);
        $criteria->compare('lob', $this->lob);
        $criteria->compare('geo_risk', $this->geo_risk);
        $criteria->compare('response_status', $this->response_status);
        $criteria->compare('res_status_date', $this->res_status_date);
        $criteria->compare('response_enrolled_date', $this->response_enrolled_date);
        $criteria->compare('fireshield_status', $this->fireshield_status);
        $criteria->compare('fs_status_date', $this->fs_status_date);
        $criteria->compare('pre_risk_status', $this->pre_risk_status);
        $criteria->compare('pr_status_date', $this->pr_status_date);
        $criteria->compare('fs_assessments_allowed', $this->fs_assessments_allowed);
        $criteria->compare('rated_company', $this->rated_company);
        $criteria->compare('geocode_level', $this->geocode_level);
        $criteria->compare('brushfire_inspect', $this->brushfire_inspect);
        $criteria->compare('brushfire_inspect_date', $this->brushfire_inspect_date);
        $criteria->compare('t.last_update', $this->last_update);
        $criteria->compare('comments', $this->comments);
        $criteria->compare('agent_id', $this->agent_id);
        $criteria->compare('member.first_name', $this->member_first_name, true);
        $criteria->compare('member.last_name', $this->member_last_name, true);
        $criteria->compare('member.member_num', $this->member_member_num, true);
        $criteria->compare('member.home_phone', $this->member_home_phone, true);
        $criteria->compare('member.work_phone', $this->member_work_phone, true);
        $criteria->compare('member.cell_phone', $this->member_cell_phone, true);
        $criteria->compare('member.email_1', $this->member_email_1, true);
        $criteria->compare('member.fs_carrier_key', $this->member_fs_carrier_key, true);
        $criteria->compare('additional_contacts', $this->additional_contacts, true);
        $criteria->compare('producer', $this->producer, true);
        $criteria->compare('response_auto_enrolled', $this->response_auto_enrolled);
        $criteria->compare('t.type_id', $this->type_id);
        $criteria->compare('t.client_id', $this->client_id);
        $criteria->compare('wds_geocode_level', $this->wds_geocode_level);
        $criteria->compare('question_set_id', $this->question_set_id);
        $criteria->compare('wds_geocoder', $this->wds_geocoder);
        $criteria->compare('wds_match_address', $this->wds_match_address);
        $criteria->compare('wds_match_score', $this->wds_match_score);
        $criteria->compare('multi_family', $this->multi_family);
        $criteria->compare('app_status', $this->app_status);
        $criteria->compare('agency_name', $this->agency_name, true);
        $criteria->compare('agency_code', $this->agency_code, true);
        $criteria->compare('location', $this->location);
        $criteria->compare('wds_lob', $this->wds_lob);
        $criteria->compare('risk_score_whp', $this->risk_score_whp,true);
        $criteria->compare('risk_score_v', $this->risk_score_v,true);
        $criteria->compare('risk_score', $this->risk_score,true);
        $criteria->compare('risk_date_created', $this->risk_date_created,true);
        $criteria->compare('agency_phone', $this->agency_phone, true);
        if ($this->wds_geocode_date)
        {
            $criteria->addCondition('wds_geocode_date >= :today AND wds_geocode_date < :tomorrow');
            $criteria->params[':today'] = date('Y-m-d', strtotime($this->wds_geocode_date));
            $criteria->params[':tomorrow'] = date('Y-m-d', strtotime($this->wds_geocode_date . ' + 1 day'));
        }

        //From the advanced search dropdown
        if(!empty($advSearch['fs_statuses']))
        {
            $criteria->addInCondition('t.fireshield_status',$advSearch['fs_statuses'], 'AND');
        }
        if(!empty($advSearch['response_statuses']))
        {
            $criteria->addInCondition('t.response_status',$advSearch['response_statuses'], 'AND');
        }
        if(!empty($advSearch['policy_statuses']))
        {
            $criteria->addInCondition('t.policy_status',$advSearch['policy_statuses'], 'AND');
        }
        if(!empty($advSearch['clients']))
        {
            $criteria->addInCondition('t.client_id',$advSearch['clients'], 'AND');
        }
        if(!empty($advSearch['states']))
        {
            $criteria->addInCondition('t.state',$advSearch['states'], 'AND');
        }
        if(!empty($advSearch['resEnrolledDateBegin']) && !empty($advSearch['resEnrolledDateEnd']))
        {
            $criteria->addBetweenCondition('response_enrolled_date', $advSearch['resEnrolledDateBegin'], $advSearch['resEnrolledDateEnd'].' 11:59 PM', 'AND');
        }
        if(!empty($advSearch['policyEffDateBegin']) && !empty($advSearch['policyEffDateEnd']))
        {
            $criteria->addBetweenCondition('policy_effective', $advSearch['policyEffDateBegin'], $advSearch['policyEffDateEnd'].' 11:59 PM', 'AND');
        }
        if(!empty($advSearch['lastUpdateDateBegin']) && !empty($advSearch['lastUpdateDateEnd']))
        {
            $criteria->addBetweenCondition('t.last_update', $advSearch['lastUpdateDateBegin'], $advSearch['lastUpdateDateEnd'].' 11:59 PM', 'AND');
        }
        if(!empty($advSearch['member_first_name']))
        {
            $criteria->compare('member.first_name', $advSearch['member_first_name'], true);
        }
        if(!empty($advSearch['member_last_name']))
        {
            $criteria->compare('member.last_name', $advSearch['member_last_name'], true);
        }
        if(!empty($advSearch['address_line_1']))
        {
            $criteria->compare('address_line_1', $advSearch['address_line_1'], true);
        }

        $sortWay = false; //false = DESC, true = ASC
        if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
            $sortWay = true;
        $sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc
        $this->is_search = true;
        $dataProvider = new CActiveDataProvider($this, array(
            'sort'=>array(
                'defaultOrder'=>array($sort=>$sortWay),
                'attributes'=>array(
                    'member_first_name'=>array(
                        'asc'=>'member.first_name',
                        'desc'=>'member.first_name DESC',
                    ),
                    'member_last_name'=>array(
                        'asc'=>'member.last_name',
                        'desc'=>'member.last_name DESC',
                    ),
                    'member_member_num'=>array(
                        'asc'=>'member.member_num',
                        'desc'=>'member.member_num DESC',
                    ),
                    '*',
                ),
            ),
            'criteria'=>$criteria,
            //'pagination'=>array('pageSize'=>$pageSize)
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

    //creates a report of the current gridview (all pages)
    public function makeDownloadableReport($columnsToShow, $advSearch, $sort)
    {
        $myFile = Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_PropReport.csv';
        $fh = fopen($myFile, 'w') or die("can't open file");

        //headerrow
        $tempLine = '';
        foreach($columnsToShow as $column)
        {
            $tempLine .= $column.',';
        }
        fwrite($fh, rtrim($tempLine, ',')."\n");

        //loop through all pages in dataprovider so report contains all data rows
        $pageSize = 100;
        $dataProvider = $this->search($advSearch, $pageSize, NULL);
        $dataRows = $dataProvider->getData(true);
        $pagination = $dataProvider->pagination;
        while ($pagination->currentPage < $pagination->pageCount)
        {
            if($pagination->currentPage+1 == $pagination->pageCount)
            {
                $numberOnLastPage = $pagination->itemCount - $pagination->offset;
                $lastPageIndex = $pageSize;
            }

            $dataRows = $dataProvider->getData(true);
            foreach($dataRows as $data)
            {
                //this top if is part of the dirty hack needed to not repeat data with pagination that only occurs on the second to last and last pages of a MSSQL search. if there is only one page the hack isnt needed.
                //if($pagination->currentPage+1 == $pagination->pageCount && $pagination->pageCount != 1)
                //{
                //    if($lastPageIndex <= $numberOnLastPage)
                //    {
                //        $tempLine = '';
                //        foreach($columnsToShow as $columnToShow)
                //        {
                //            $tempLine .= '"'.str_replace('"', '""', (isset($data[$columnToShow]) ? $data[$columnToShow] : "")).'",';
                //        }
                //        fwrite($fh, $tempLine."\n");
                //    }
                //    $lastPageIndex--;
                //}
                //else
                //{
                    $tempLine = '';
                    foreach($columnsToShow as $columnToShow)
                    {
                        $tempLine .= '"'.str_replace('"', '""', (isset($data[$columnToShow]) ? $data[$columnToShow] : "")).'",';
                    }
                    fwrite($fh, $tempLine."\n");
            }

            $pagination->currentPage++;
        }

        fclose($fh);
    }

    //for FS, Response, and Pre Risk statues
    public function getProgramStatuses()
    {
        return array('not enrolled', 'ineligible', 'offered', 'enrolled', 'declined');
    }

    public function getPolicyStatuses()
    {
        return array('active', 'pending', 'canceled', 'expired');
    }

    public function getTransactionTypes()
    {
        return array('', 'issue', 'non-renew', 'cancel', 're-write', 'reinstate', 'renew');
    }

    public function getAppStatuses()
    {
        return array('', 'active', 'canceled', 'removed');
    }

    public function getLOBTypes()
    {
        return array('HOM','BUS');
    }

    public function getFSOfferedDate()
    {
        if($this->fireshield_status == 'offered')
            return date_format(new DateTime($this->fs_status_date), 'm/d/Y h:i A');
        else
        {
            $sh = StatusHistory::model()->findByAttributes(array('table_name'=>'properties', 'table_id'=>$this->pid, 'table_field'=>'fireshield_status', 'status'=>'offered'));
            if(isset($sh))
                return date_format(new DateTime($sh->date_changed), 'm/d/Y h:i A');
            else
                return '';
        }
    }

    public function getFSEnrolledDate()
    {
        if($this->fireshield_status == 'enrolled')
            return date_format(new DateTime($this->fs_status_date), 'm/d/Y h:i A');
        else
        {
            $sh = StatusHistory::model()->findByAttributes(array('table_name'=>'properties', 'table_id'=>$this->pid, 'table_field'=>'fireshield_status', 'status'=>'enrolled'));
            if(isset($sh))
                return date_format(new DateTime($sh->date_changed), 'm/d/Y h:i A');
            else
                return '';
        }
    }


    protected function beforeFind()
    {
        //Need to convert geometry type to wkt so it doesn't come through as binary (problems with reading and writing)
        if(!$this->is_search && isset($this->dbCriteria) && isset($this->dbCriteria->select) && $this->dbCriteria->select == '*')
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
        // Convert the date/time fields to display format.
        $format = 'm/d/Y h:i A';
        if(isset($this->res_status_date))
            $this->res_status_date = date_format(new DateTime($this->res_status_date), $format);
        if(isset($this->fs_status_date))
            $this->fs_status_date = date_format(new DateTime($this->fs_status_date), $format);
        if(isset($this->pr_status_date))
            $this->pr_status_date = date_format(new DateTime($this->pr_status_date), $format);
        if(isset($this->policy_status_date))
            $this->policy_status_date = date_format(new DateTime($this->policy_status_date), $format);
        if(isset($this->response_enrolled_date))
            $this->response_enrolled_date = date_format(new DateTime($this->response_enrolled_date), $format);
        if(isset($this->transaction_effective))
            $this->transaction_effective = date_format(new DateTime($this->transaction_effective), $format);
        if(isset($this->policy_effective))
            $this->policy_effective = date_format(new DateTime($this->policy_effective), $format);
        if(isset($this->policy_expiration))
            $this->policy_expiration = date_format(new DateTime($this->policy_expiration), $format);
        if(isset($this->transaction_effective))
            $this->transaction_effective = date_format(new DateTime($this->transaction_effective), $format);
        if (isset($this->properties_type))
            $this->property_type = $this->properties_type->type;

        return parent::afterFind();
    }

   protected function beforeValidate()
   {
        //Fixes values that come through as "NULL" strings
        if(!is_numeric($this->lat))
        {
            $this->lat = null;
        }
        if(!is_numeric($this->long))
        {
            $this->long = null;
        }
        return parent::beforeValidate();
   }

    protected function beforeSave()
    {
        // The status override causes all automatic status logic to be bypassed.
        if (isset($this->member) && !$this->member->status_override)
        {
            if ($this->pre_risk_status == 'enrolled' && $this->fireshield_status != 'enrolled')
            {
                $this->fireshield_status = 'ineligible';
                $this->fs_status_date = date('Y-m-d H:i:s');
                $this->fs_assessments_allowed = 0;
            }

            if ($this->fireshield_status == 'enrolled' && $this->pre_risk_status != 'enrolled')
            {
                $this->pre_risk_status = 'ineligible';
                $this->pr_status_date = date('Y-m-d H:i:s');
            }

            if ($this->fireshield_status == 'enrolled' && (isset($this->member->mem_fireshield_status) && $this->member->mem_fireshield_status != 'enrolled'))
            {
                $this->member->mem_fireshield_status = 'enrolled';
                $this->member->mem_fs_status_date = $this->fs_status_date;
                $this->member->save();
            }

            if ($this->fireshield_status == 'offered' && (isset($this->member->mem_fireshield_status) && $this->member->mem_fireshield_status != 'enrolled' && $this->member->mem_fireshield_status != 'offered'))
            {
                $this->member->mem_fireshield_status = 'offered';
                $this->member->mem_fs_status_date = $this->fs_status_date;
                $this->member->save();
            }
        }

        if (!$this->isNewRecord)
        {
            $currentProp = Property::model()->findByPk($this->pid);

            // If any of the status fields have changed, push their values onto the status_history table.
            if ($currentProp->response_status != $this->response_status)
            {
                if($currentProp->res_status_date!=null)
                {
                    StatusHistory::model()->insertStatus($currentProp, 'response_status', $currentProp->res_status_date);
                }
            }

            if ($currentProp->fireshield_status != $this->fireshield_status)
            {
                if($currentProp->fs_status_date!=null)
                {
                    StatusHistory::model()->insertStatus($currentProp, 'fireshield_status', $currentProp->fs_status_date);
                }
            }

            if ($currentProp->pre_risk_status != $this->pre_risk_status)
            {
                if($currentProp->pr_status_date!=null)
                {
                    StatusHistory::model()->insertStatus($currentProp, 'pre_risk_status', $currentProp->pr_status_date);
                }
            }

            if ($currentProp->policy_status != $this->policy_status)
            {
                if($currentProp->policy_status_date!=null)
                {
                    StatusHistory::model()->insertStatus($currentProp, 'policy_status', $currentProp->policy_status_date);
                }
            }

            // If any of the address fields changed, need to set geog to null as it is no longer valid
            if ($currentProp->address_line_1 != $this->address_line_1 ||
                $currentProp->city != $this->city ||
                $currentProp->state != $this->state ||
                $currentProp->zip != $this->zip)
            {
                $this->geog = null;
                $this->wds_geocode_level = null;
                $this->wds_geocoder = null;
                $this->wds_match_address = null;
                $this->wds_match_score = null;
                $this->wds_geocode_date = null;
            }
        }

        if ($this->response_status == 'enrolled' && (empty($this->response_enrolled_date) || $this->response_enrolled_date == '' ))
        {
            $this->response_enrolled_date = date('Y-m-d H:i:s');
        }
        else if ($this->response_status != 'enrolled' && (empty($this->response_enrolled_date) || $this->response_enrolled_date == '' ))
        {
            $this->response_enrolled_date = null;
        }

        if (!isset($this->client_id) && isset($this->member_mid))
        {
            $member = Member::model()->findByPk($this->member_mid);
            $client = Client::model()->findByPk($member->client_id);
            $this->client_id = $client->id;
        }

        $this->last_update = date('Y-m-d H:i:s');

        //if type_id isn't set, assume it's a PIF property
        if (empty($this->type_id))
            $this->type_id = 1; //PIF

        $this->geocodeProperty();

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        if ($this->geocoded === true)
        {
            // Creating a location history transaction
            Yii::app()->db->createCommand()->insert('properties_location_history', array(
                'property_pid' => $this->pid,
                'wds_geocode_level' => $this->wds_geocode_level,
                'wds_lat' => $this->wds_lat,
                'wds_long' => $this->wds_long,
                'wds_geocoder' => $this->wds_geocoder,
                'wds_match_address' => $this->wds_match_address,
                'wds_match_score' => $this->wds_match_score,
                'wds_geocode_date' => $this->wds_geocode_date
            ));
        }

        return parent::afterSave();
    }

    /**
     * This method geocodes a property.
     * @return void
     */
    private function geocodeProperty()
    {
        // Only geocode properties with the correct parameters
        if ($this->policy_status === 'active' && $this->type_id == 1 && empty($this->geog) && empty($this->wds_geocode_level))
        {
            $this->wds_geocoder = null;
            $this->wds_match_address = null;
            $this->wds_match_score = null;

            // Client geocode level is not good enough, we geocode
            if (!in_array($this->geocode_level, self::$goodGeocodeLevels))
            {
                if (empty($this->address_line_1) || empty($this->city) || empty($this->state) || empty($this->zip))
                {
                    $this->geog = null;
                    $this->wds_lat = null;
                    $this->wds_long = null;
                    $this->wds_geocode_level = self::GEOCODE_UNMATCHED;
                    return;
                }
                // Build address as one string
                $address = sprintf('%s, %s, %s %s', $this->address_line_1, $this->city, $this->state, Helper::splitZipCode($this->zip));
                // Get and store the results
                $this->geocodeAddress($address);
            }
            // Client geocode level is good enough, use their coordinates
            else
            {
                if ($this->validateCoordinates())
                {
                    // Chopping of lat/long float fields to 7 chars after decimal
                    $this->geog = 'POINT (' . $this->long . ' ' . $this->lat . ')';
                    $this->wds_lat = substr($this->lat, 0, strpos((string)$this->lat, '.') + 8);
                    $this->wds_long = substr($this->long, 0, strpos((string)$this->long, '.') + 8);
                    $this->wds_geocode_level = self::GEOCODE_CLIENT;
                }
                else
                {
                    $this->geog = null;
                    $this->wds_lat = null;
                    $this->wds_long = null;
                    $this->wds_geocode_level = self::GEOCODE_UNMATCHED;
                }
            }
        }
    }

    /**
     * Validate model client coordinates.  Return false if not valid.
     * @return bool
     */
    private function validateCoordinates()
    {
        if (!empty($this->lat) &&
            !empty($this->long) &&
            floatval($this->lat) > 0 &&
            floatval($this->lat) < 90 &&
            floatval($this->long < 0) &&
            floatval($this->long > -180))
        {
            return true;
        }

        return false;
    }

    /**
     * Summary of geocodeAddress
     * @param string $address
     * @return void
     */
    private function geocodeAddress($address)
    {
        $geocode = Geocode::getLocation($address, 'address');

        // Mapbox Geocode
        if (isset($geocode['location_type'], $geocode['state']) && $geocode['location_type'] !== 'unmatched')
        {
            $stateAbbrGeocode = Helper::convertStateToAbbr(strtoupper($geocode['state']));
            $stateAbbr = Helper::convertStateToAbbr(strtoupper($this->state));

            $stateAbbrGeocode = $stateAbbrGeocode ? $stateAbbrGeocode : $geocode['state'];
            $stateAbbr = $stateAbbr ? $stateAbbr : $this->state;

            // Ensure match is good enough
            if ($geocode['location_score'] >= 0.70 && $geocode['location_type'] === 'address' && $stateAbbrGeocode === $stateAbbr)
            {
                $this->geog = 'POINT (' . $geocode['geometry']['lon'] . ' ' . $geocode['geometry']['lat'] . ')';
                $this->wds_lat = $geocode['geometry']['lat'];
                $this->wds_long = $geocode['geometry']['lon'];
                $this->wds_geocode_level = self::GEOCODE_ADDRESS;
                $this->wds_geocoder = self::GEOCODER_MAPBOX;
                $this->wds_match_address = $geocode['address_formatted'];
                $this->wds_match_score = $geocode['location_score'];
                $this->wds_geocode_date = date('Y-m-d H:i:s');
                $this->geocoded = true;
                return;
            }
        }

        // Mapbox didn't work or didn't match, try ESRI
        if (empty($this->wds_geocode_level))
        {
            $geocode = GeocodeESRI::getLocation($address);

            if (isset($geocode['error']) && $geocode['error'] !== 1)
            {
                // Ensure match is good enough
                if ($geocode['location_score'] >= 0.85 && in_array($geocode['location_type'], array('PointAddress','BuildingName','StreetAddress')))
                {
                    $this->geog = 'POINT (' . $geocode['geometry']['lon'] . ' ' . $geocode['geometry']['lat'] . ')';
                    $this->wds_lat = $geocode['geometry']['lat'];
                    $this->wds_long = $geocode['geometry']['lon'];
                    $this->wds_geocode_level = self::GEOCODE_ADDRESS;
                    $this->wds_geocoder = self::GEOCODER_ESRI;
                    $this->wds_match_address = $geocode['address_formatted'];
                    $this->wds_match_score = $geocode['location_score'];
                    $this->wds_geocode_date = date('Y-m-d H:i:s');
                    $this->geocoded = true;
                    return;
                }
            }
        }

        // Neither Mapbox or ESRI worked, set property to unmatched
        $this->geog = null;
        $this->wds_lat = null;
        $this->wds_long = null;
        $this->wds_geocode_level = self::GEOCODE_UNMATCHED;
        $this->wds_geocoder = null;
        $this->wds_match_address = null;
        $this->wds_match_score = null;
        $this->wds_geocode_date = date('Y-m-d H:i:s');
        $this->geocoded = true;
    }

    /**
     * Retrieves the status history for the current property for a specific table_field.
     * @param string $tableField table field name
     * @return CActiveDataProvider status history data
     */
    public function getStatusHistory($tableField)
    {
        return new CActiveDataProvider('StatusHistory', array(
            'sort' => array('defaultOrder' => array('date_changed' => true)),
            'criteria' => array(
                'condition' => 'table_name=\'properties\' AND table_id='.$this->pid.' AND table_field=\''. $tableField .'\'',
            ),
        ));
    }

    /**
     * Uses transaction type and date and policy effective dates to determine the current policy status of a USAA property
     * @return string $status that it should be
     */
    public function getUSAAPolicyStatus()
    {
        //setting this as the default because when it doubt we are assuming active
        $policy_status = 'active'; //'active', 'pending', 'canceled', or expired
        //setting this as the default as we want to treat anything thats not set as an 'renew'ing policy that needs to stay active
        $transaction_type = 'renew';
        if(isset($this->transaction_type)) //only apply this logic if transaction_type is set
            $transaction_type = $this->transaction_type;

        $current_date = new DateTime();
        $transaction_date = new DateTime($this->transaction_effective);
        $effective_date = new DateTime($this->policy_effective);
        $expiration_date = new DateTime($this->policy_expiration);

        //IF transation_type = cancel and the transaction_effective date is current date or earlier, then policy is canceled
        //cancels are based off transaction date
        if($transaction_type == 'cancel' && $transaction_date <= $current_date)
        {
             $policy_status = 'canceled';
        }
        //IF transaction_type = non-renew, leave it active until the current date passes the expire date
        else if($transaction_type == 'non-renew' && $current_date > $expiration_date)
        {
            $policy_status = 'expired';
        }
        //IF transaction_type = issue or re-write or reinstate or non-renew
        else if(in_array($transaction_type, array('issue', 're-write', 'reinstate')))
        {
            //IF current date is between policy_effective and policy_expiration dates, then policy is active
            if($effective_date <= $current_date && $expiration_date >= $current_date)
                $policy_status = 'active';
            //IF the effective date is in the future then policy is pending
            else if($effective_date > $current_date)
                $policy_status = 'pending';
            //IF the expiration date has passed and then policy is expired
            else if($current_date > $expiration_date)
                $policy_status = 'expired';
        }
        //else transaction_type is renew (or unset) so return the defalut 'active'

        return $policy_status;
    }

    /**
     * Looks up and returns all the pids that were set to a given policy status in a given date range
     * @param date $start_date
     * @param date $end_date
     * @param string array $policy_statuses
     * @return array of pids
     */
    function getStatusChanges($start_date, $end_date, $policy_statuses)
    {
        $pids = array();
        $criteria = new CDbCriteria();
        $criteria->addBetweenCondition('policy_status_date', $start_date, $end_date);
        $criteria->addInCondition('policy_status', $policy_statuses);
        $props = Property::model()->findAll($criteria);
        foreach($props as $prop)
            $pids[] = $prop->pid;
        return $pids;
    }

    //Count the number of enrolled policyholders YTD from the previous day
    public static function getResponseEnrollmentCount($clientName)
    {
        $criteria = new CDbCriteria;
        $criteria->with = array('member');
        $criteria->addCondition("member.client = '$clientName'");
        $criteria->addCondition("member.is_tester = 0");
        $criteria->addCondition("t.response_status = 'enrolled'");
        $criteria->addCondition("t.policy_status = 'active'");
        $criteria->addCondition("t.type_id = 1");

        return Property::model()->count($criteria);
    }

    /**
     * Used for smoke checks - get's the alert triggered policyholders as geojson data
     * @param integer $perimeterID
     * @param integer $bufferDistance
     * @return array
     */
    public static function getGeoJson($perimeterID, $bufferDistance, $clientID)
    {
        $properties = GIS::getPolicyAlert($perimeterID, $bufferDistance, $clientID);

        $features = array();

        foreach ($properties as $property)
        {
            $features[] = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array($property['long'], $property['lat'])
                ),
                'properties' => array(
                    'client' => $property['client'],
                    'pid' => $property['pid'],
                    'last_name' => $property['last_name'],
                    'address' => $property['address_line_1'] . ' ' . $property['city'] . ', ' . $property['state'],
                    'response_status' => $property['response_status'],
                    'distance' => round($property['distance'] * 0.000621371, 2),
                    'enrolled_color' => $property['map_enrolled_color'],
                    'not_enrolled_color' => $property['map_not_enrolled_color'],
					'wds_geocode_confidence' => self::getMatchConfidence($property)
                )
            );
        }

        return array(
            'type' => 'FeatureCollection',
            'features' => $features
        );
    }

	/**
	 * Translates the geocoder and match score into our confidence rating being high, moderate, low
	 * @param mixed $property
	 * @return string $matchConfidence
	 */
	public static function getMatchConfidence($property)
	{
		$matchConfidence = '';
		if($property['wds_geocode_level'] == 'client')
		{
			$matchConfidence = 'Client Coordinates';
		}
		elseif($property['wds_geocode_level'] == 'wds')
		{
			$matchConfidence = "WDS Found";
		}
		elseif($property['wds_geocoder'] == 'mapbox')
		{
			if(floatval($property['wds_match_score']) >= .74)
			{
				$matchConfidence = 'High';
			}
			elseif (floatval($property['wds_match_score']) >= .7)
			{
				$matchConfidence = 'Moderate';
			}
			else
			{
				$matchConfidence = 'Low';
			}

			$matchConfidence .= " (Mapbox)";
		}
		elseif($property['wds_geocoder'] == 'esri')
		{
			if(floatval($property['wds_match_score']) >= .9)
			{
				$matchConfidence = 'High';
			}
			else
			{
				$matchConfidence = 'Moderate';
			}

			$matchConfidence .= " (ESRI)";
		}

		return $matchConfidence;
	}

    public function getCurrentWdsFireEnrollmentStatus()
    {
        $wdsFireEnrollments = WdsfireEnrollments::model()->findByAttributes(array('pid'=>$this->pid),array('order'=>'date DESC'));
        if(isset($wdsFireEnrollments))
            return $wdsFireEnrollments->status_type;
        else
            return null;
    }

    public function getWdsRisk()
    {
        return RiskScore::model()->getRiskScore($this->pid);
    }

    public function getWdsRiskStateMeans()
    {
        $geogState = GeogStates::model()->findByAttributes(array('abbr'=>$this->state));
        if(isset($geogState))
            return RiskStateMeans::model()->findByAttributes(array('state_id'=>$geogState->id,'version_id'=>RiskVersion::getLiveVersionID()));
        else
            return null;
    }

    public function getWDSRiskDev()
    {
        if(isset($this->wdsRisk->score_wds, $this->wdsRiskStateMeans->std_dev, $this->wdsRiskStateMeans->mean))
            return round(($this->wdsRisk->score_wds - $this->wdsRiskStateMeans->mean) / $this->wdsRiskStateMeans->std_dev, 2);
        else
            return null;
    }

    public function getLastPhVisit($fireID = null)
    {
        $last_ph_visit = null;
        if(isset($this->ph_visits) && count($this->ph_visits) > 0)
        {
            foreach($this->ph_visits as $ph_visit)
            {
                if(!isset($last_ph_visit) || $ph_visit->date_action > $last_ph_visit->date_action)
                {
                    if(isset($fireID))
                    {
                        if($ph_visit->fire_id == $fireID)
                        {
                            $last_ph_visit = $ph_visit;
                        }
                    }
                    else
                    {
                        $last_ph_visit = $ph_visit;
                    }
                }
            }
        }
        return $last_ph_visit;
    }
}
?>
