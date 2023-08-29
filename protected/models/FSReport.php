<?php

/**
 * This is the model class for table "fs_report". This is the main model for incoming assessments
 * from the FireShield app. It has a one-to-many relationship with FSCondition.
 *
 * The followings are the available columns in table 'fs_report':
 * @property integer $id
 * @property string $report_guid
 * @property string $property_pid
 * @property integer $member_mid
 * @property datetime $start_date
 * @property datetime $end_date
 * @property datetime $submit_date
 * @property string $version
 * @property string $address_line_1
 * @property string $address_line_2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $longitude
 * @property string $latitude
 * @property integer $geo_risk
 * @property integer $condition_risk
 * @property string $summary
 * @property string $status
 * @property integer $risk_level
 * @property datetime $scheduled_call
 * @property string $scheduled_call_notes
 * @property string $scheduled_call_tz
 * @property string $notes
 * @property datetime $due_date
 * @property integer $assigned_user_id
 * @property datetime $status_date
 * @property integer $orig_risk_level
 * @property integer $agent_property_id
 * @property string $type
 * @property bool $no_scoring
 * @property bool $show_site_risk
 * @property bool $show_geo_risk
 * @property bool $show_los_risk
 * @property integer $pre_risk_id
 * @property integer $fs_user_id
 * @property string $risk_summary
 * @property string $download_types
 * @property string $email_download_types
 * @property string $risk_detail
 * @property string $pdf_pass
 * @property integer $level
 */
class FSReport extends CActiveRecord
{
    public $assigned_user_name;
	public $property_address_line_1;
	public $property_city;
	public $property_state;
	public $property_policy;
	public $property_geo_risk;
	public $property_fsOfferedDate;
	public $member_first_name;
	public $member_last_name;
	public $member_client;
	public $member_member_num;
    public $member_is_tester;
	public $agent_id;
	public $agent_agent_num;
	public $agent_first_name;
	public $agent_last_name;
	public $client_id;
	public $client_name;
	public $agent_property_address_line_1;
	public $agent_property_city;
	public $agent_property_state;
	public $agent_property_property_value;
	public $agent_property_geo_risk;
	public $agent_property_work_order_num;
    public $pre_risk_ha_date;
    public $completeDate;
    public $fs_user_email;
    public $fs_user_type; //agent or member
    public $fs_user_name; //first + last
    public $fs_user_client_name;

	/**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return fs the static model class
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
		return 'fs_report';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('report_guid', 'required'),
			array('status', 'in', 'range'=>$this->getStatuses()),
            array('pdf_pass', 'length', 'max'=>20),
			array('property_pid, member_mid, start_date, end_date, submit_date, version, address_line_1, address_line_2, city, state, zip, longitude, latitude, geo_risk, condition_risk, summary, risk_summary, risk_level, scheduled_call, scheduled_call_notes, scheduled_call_tz, notes, due_date, assigned_user_id, status_date, orig_risk_level, agent_property_id, type, no_scoring, show_site_risk, show_geo_risk, show_los_risk, pre_risk_id, fs_user_id, download_types, email_download_types, risk_detail, level', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, report_guid, property_pid, member_mid, start_date, end_date, submit_date, version, address_line_1, address_line_2, city, state, zip, longitude, latitude, geo_risk, condition_risk, summary, risk_summary, status, risk_level, scheduled_call, scheduled_call_tz, scheduled_call_notes, notes, due_date, pdf_pass, level,
                assigned_user_id, status_date, orig_risk_level, agent_property_id, type, no_scoring, show_site_risk, show_geo_risk, show_los_risk, pre_risk_id, fs_user_id, download_types, email_download_types, risk_detail, assigned_user_name, property_address_line_1, property_city, property_state, property_policy,
                property_geo_risk, property_fsOfferedDate, member_first_name, member_last_name, member_client, member_member_num, member_is_tester, agent_id, agent_first_name, agent_last_name, agent_agent_num, client_id, client_name, agent_property_address_line_1, agent_property_city, agent_property_state,
                agent_property_property_value, agent_property_geo_risk, agent_property_work_order_num, pre_risk_ha_date, fs_user_email, fs_user_type, fs_user_name, fs_user_client_name', //more relational attributes
				'safe', 'on'=>'search'),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		return array(
			'conditions' => array(self::HAS_MANY, 'FSCondition', 'fs_report_id'),
			'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
			'assigned_user' => array(self::BELONGS_TO, 'User', 'assigned_user_id'),
			'member' => array(self::BELONGS_TO, 'Member', 'member_mid'),
            'agent_property' => array(self::BELONGS_TO, 'AgentProperty', 'agent_property_id'),
			'agent' => array(self::BELONGS_TO, 'Agent', '', 'on' => 'agent_property.agent_id = agent.id'),
			'client' => array(self::BELONGS_TO, 'Client', '', 'on' => 'agent.client_id = client.id'),
            'pre_risk' => array(self::BELONGS_TO, 'PreRisk', 'pre_risk_id'),
            'fs_user' => array(self::BELONGS_TO, 'FSUser', 'fs_user_id'),
            'user' => array(self::BELONGS_TO, 'User','fs_user_id'),
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'report_guid' => 'Report GUID',
			'property_pid' => 'Property ID',
			'member_mid' => 'Member ID',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'submit_date' => 'Submit Date',
			'version' => 'Version',
			'address_line_1' => 'Address Line 1',
			'address_line_2' => 'Address Line 2',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'longitude' => 'Longitude',
			'latitude' => 'Latitude',
			'geo_risk' => 'Geo Risk Override',
			'condition_risk' => 'Home Char. Risk',
			'summary' => 'Summary',
			'status' => 'Status',
			'risk_level' => 'LOS',
			'scheduled_call' => 'Scheduled Call',
			'scheduled_call_notes' => 'Scheduled Call Notes',
			'scheduled_call_tz' => 'Scheduled Call TZ',
			'notes' => 'Notes',
			'due_date' => 'Due Date',
			'assigned_user_id' => 'Assigned User ID',
			'status_date' => 'Status Date',
			'orig_risk_level' => 'Orig. LOS',
			'member_member_num' => 'Member #',
            'member_is_tester' => 'Test Member',
            'agent_property_id' => 'Agent Property ID',
			'agent_property_address_line_1' => 'Agent Property Address',
			'agent_property_city' => 'Agent Property City',
			'agent_property_state' => 'Agent Property State',
			'agent_property_property_value' => 'Agent Property Value',
			'agent_property_geo_risk' => 'Agent Property Geo Risk',
			'agent_property_work_order_num' => 'Agent Property Work Order #',
			'type' => 'Type',
			'agent_id' => 'Agent ID',
			'agent_agent_num' => 'Agent #',
			'agent_first_name' => 'Agent First Name',
			'agent_last_name' => 'Agent Last Name',
			'client_id' => 'Client ID',
			'client_name' => 'Client Name',
			'no_scoring' => 'Do Not Show Scoring',
            'show_site_risk' => 'Show Site Risk Scoring',
            'show_geo_risk' => 'Show Geo Risk Scoring',
            'show_los_risk' => 'Show LOS(total) Risk Scoring',
            'pre_risk_id' => 'PreRisk ID',
            'pre_risk_ha_date' => 'PreRisk HA Date',
            'completeDate' => 'Completed Date',
            'fs_user_id' => 'App User ID',
            'fs_user_email' => 'App User Email',
            'fs_user_type' => 'App User Type',
            'fs_user_name' => 'App User Name',
            'fs_user_client_name'=> 'Client',
            'risk_summary'=>'Risk Summary',
            'download_types'=>'App Download Type(s)',
            'email_download_types' => 'Email Download Type(s)',
            'risk_detail'=>'Risk Detail',
            'pdf_pass'=>'PDF Password',
            'level'=>'Level',
		);
	}

	public function getCompleteDate()
	{
		if($this->status == 'Completed')
			return date_format(new DateTime($this->status_date), 'm/d/Y');
		else
		{
			$sh = StatusHistory::model()->findByAttributes(array('table_name'=>'fs_report', 'table_id'=>$this->id, 'table_field'=>'status', 'status'=>'Completed'));
			if(isset($sh))
				return date_format(new DateTime($sh->date_changed), 'm/d/Y');
			else
				return '';
		}
	}

	/**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
	public function search($advSearch = NULL, $pageSize = 25, $sort = NULL, $pageNumber=NULL)
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		$criteria->with = array('property', 'assigned_user', 'member', 'agent_property', 'agent', 'client', 'pre_risk', 'user', 'fs_user');

		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.property_pid',$this->property_pid);
		$criteria->compare('t.member_mid', $this->member_mid);
		$criteria->compare('report_guid',$this->report_guid);
		$criteria->compare('start_date', $this->start_date, true);
		$criteria->compare('end_date', $this->end_date, true);

        if($this->submit_date)
        {
	        $submit_date = strtotime($this->submit_date);

            if ($submit_date !== false)
	        {
		        $criteria->addCondition("submit_date >= '" . date('Y-m-d', strtotime($this->submit_date)) . "' AND submit_date < '" . date('Y-m-d', strtotime($this->submit_date . ' + 1 day')) . "'",true);
	        }
        }
        $criteria->compare('version', $this->version);
		$criteria->compare('t.address_line_1', $this->address_line_1, true);
		$criteria->compare('t.address_line_2', $this->address_line_2, true);
		$criteria->compare('t.city', $this->city, true);
		$criteria->compare('t.state', $this->state, true);
		$criteria->compare('t.zip', $this->zip, true);
		$criteria->compare('t.longitude', $this->longitude,true);
		$criteria->compare('t.latitude', $this->latitude,true);
		$criteria->compare('t.geo_risk', $this->geo_risk);
		$criteria->compare('t.condition_risk', $this->condition_risk);
		$criteria->compare('t.summary', $this->summary,true);
		$criteria->compare('t.status', $this->status,true);
		$criteria->compare('risk_level', $this->risk_level, true);

        if ($this->scheduled_call)
        {
	        $scheduled_call = strtotime($this->scheduled_call);

	        if ($scheduled_call !== false)
	        {
		        $criteria->addCondition("scheduled_call >= '" . date('Y-m-d', strtotime($this->scheduled_call)) . "' AND scheduled_call < '" . date('Y-m-d', strtotime($this->scheduled_call . ' + 1 day')) . "'");

	        }
        }
		$criteria->compare('scheduled_call_notes', $this->scheduled_call_notes, true);
		$criteria->compare('scheduled_call_tz', $this->scheduled_call_tz, true);
		$criteria->compare('notes', $this->notes, true);

        if ($this->due_date)
        {
	        $due_date = strtotime($this->due_date);

	        if ($due_date !== false)
	        {
		        $criteria->addCondition("due_date >= '" . date('Y-m-d', strtotime($this->due_date)) . "' AND due_date < '" . date('Y-m-d', strtotime($this->due_date . ' + 1 day')) . "'");
	        }
        }
        $criteria->compare('assigned_user_id', $this->assigned_user_id);

        if(!empty($this->status_date))
            $criteria->addBetweenCondition('status_date', $this->status_date.' 00:00:00', $this->status_date.' 23:59:59');
        if(!empty($advSearch['statusDateBegin']) && !empty($advSearch['statusDateEnd']))
            $criteria->addBetweenCondition('status_date', $advSearch['statusDateBegin'].' 00:00:00', $advSearch['statusDateEnd'].' 23:59:59');

        if(!empty($this->pre_risk_ha_date))
            $criteria->addBetweenCondition('pre_risk.ha_date', $this->pre_risk_ha_date.' 00:00:00', $this->pre_risk_ha_date.' 23:59:59');
        if(!empty($advSearch['haDateBegin']) && !empty($advSearch['haDateEnd']))
            $criteria->addBetweenCondition('pre_risk.ha_date', $advSearch['haDateBegin'].' 00:00:00', $advSearch['haDateEnd'].' 23:59:59');

		$criteria->compare('orig_risk_level', $this->orig_risk_level, true);
        $criteria->compare('level', $this->level);
		$criteria->compare('assigned_user.name', $this->assigned_user_name, true);
		$criteria->compare('property.address_line_1', $this->property_address_line_1, true);
		$criteria->compare('property.city', $this->property_city, true);
		$criteria->compare('property.state', $this->property_state, true);
		$criteria->compare('property.policy', $this->property_policy, true);
		$criteria->compare('property.geo_risk', $this->property_geo_risk);
		$criteria->compare('member.first_name', $this->member_first_name, true);
		$criteria->compare('member.last_name', $this->member_last_name, true);
		$criteria->compare('member.client', $this->member_client, true);
		$criteria->compare('member.member_num', $this->member_member_num, true);
        $criteria->compare('member.is_tester', $this->member_is_tester, true);
        $criteria->compare('agent_property_id', $this->agent_property_id);
		$criteria->compare('agent_property.address_line_1', $this->agent_property_address_line_1, true);
		$criteria->compare('agent_property.city', $this->agent_property_city, true);
		$criteria->compare('agent_property.state', $this->agent_property_state, true);
		$criteria->compare('agent_property.property_value', $this->agent_property_property_value, true);
		$criteria->compare('agent_property.geo_risk', $this->agent_property_geo_risk, true);
		$criteria->compare('agent_property.work_order_num', $this->agent_property_work_order_num, true);
		$criteria->compare('t.type', $this->type);
		$criteria->compare('agent.id', $this->agent_id);
		$criteria->compare('agent.agent_num', $this->agent_agent_num, true);
		$criteria->compare('agent.first_name', $this->agent_first_name, true);
		$criteria->compare('agent.last_name', $this->agent_last_name, true);
		$criteria->compare('client.id', $this->client_id);
		$criteria->compare('client.name', $this->client_name, true);
		$criteria->compare('t.no_scoring', $this->no_scoring);
        $criteria->compare('pre_risk_id', $this->pre_risk_id);
        $criteria->compare('fs_user_id', $this->fs_user_id);
        $criteria->compare('fs_user.email', $this->fs_user_email);
        $criteria->compare('risk_summary', $this->risk_summary, true);
        $criteria->compare('download_types', $this->download_types);
        $criteria->compare('email_download_types', $this->email_download_types);
        $criteria->compare('risk_detail', $this->risk_detail, true);
        $criteria->compare('pdf_pass', $this->pdf_pass);

		$condition = '1=1 ';
		if(isset($advSearch['statuses']))
		{
			$in = '(';
			foreach($advSearch['statuses'] as $status)
				$in .= "'".$status."',";
			$in = trim($in, ',').')';
			$condition .= 'AND t.status IN'.$in.' ';
		}

		if(isset($advSearch['types']) && $advSearch['types'] == 'agent')
			$condition .= "AND t.type IN ('uw', 'edu', 'edu-b') ";
		else if(isset($advSearch['types']) && $advSearch['types'] == 'fs')
			$condition .= "AND t.type = 'fs' ";

        $criteria->addCondition($condition);

		//$sort = 'due_date.desc';

		$sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

		$dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
				'attributes'=>array(
                    'pre_risk_ha_date'=>array(
                        'asc'=>'pre_risk.ha_date ASC',
                        'desc'=>'pre_risk.ha_date DESC',
                    ),
					'assigned_user_name'=>array(
						'asc'=>'assigned_user.name ASC',
						'desc'=>'assigned_user.name DESC',
					),
					'property_address_line_1'=>array(
						'asc'=>'property.address_line_1 ASC',
						'desc'=>'property.address_line_1 DESC',
					),
					'property_city'=>array(
						'asc'=>'property.city ASC',
						'desc'=>'property.city DESC',
					),
					'property_state'=>array(
						'asc'=>'property.state ASC',
						'desc'=>'property.state DESC',
					),
					'property_policy'=>array(
						'asc'=>'property.policy ASC',
						'desc'=>'property.policy DESC',
					),
					'property_geo_risk'=>array(
						'asc'=>'property.geo_risk ASC',
						'desc'=>'property.geo_risk DESC',
					),
					'member_first_name'=>array(
						'asc'=>'member.first_name ASC',
						'desc'=>'member.first_name DESC',
					),
					'member_last_name'=>array(
						'asc'=>'member.last_name ASC',
						'desc'=>'member.last_name DESC',
					),
					'member_client'=>array(
						'asc'=>'member.client ASC',
						'desc'=>'member.client DESC',
					),
					'member_member_num'=>array(
						'asc'=>'member.member_num ASC',
						'desc'=>'member.member_num DESC',
					),
                    'member_is_tester'=>array(
                        'asc'=>'member.is_tester ASC',
                        'desc'=>'member.is_tester DESC',
                    ),
					'agent_id'=>array(
						'asc'=>'agent.id ASC',
						'desc'=>'agent.id DESC',
					),
					'agent_agent_num'=>array(
						'asc'=>'agent.agent_num ASC',
						'desc'=>'agent.agent_num DESC',
					),
					'agent_first_name'=>array(
						'asc'=>'agent.first_name ASC',
						'desc'=>'agent.first_name DESC',
					),
					'agent_last_name'=>array(
						'asc'=>'agent.last_name ASC',
						'desc'=>'agent.last_name DESC',
					),
					'client_id'=>array(
						'asc'=>'client.id ASC',
						'desc'=>'client.id DESC',
					),
					'client_name'=>array(
						'asc'=>'client.name ASC',
						'desc'=>'client.name DESC',
					),
					'agent_property_address_line_1'=>array(
						'asc'=>'agent_property.address_line_1 ASC',
						'desc'=>'agent_property.address_line_1 DESC',
					),
					'agent_property_city'=>array(
						'asc'=>'agent_property.city ASC',
						'desc'=>'agent_property.city DESC',
					),
					'agent_property_state'=>array(
						'asc'=>'agent_property.state ASC',
						'desc'=>'agent_property.state DESC',
					),
					'agent_property_property_value'=>array(
						'asc'=>'agent_property.property_value ASC',
						'desc'=>'agent_property.property_value DESC',
					),
					'agent_property_geo_risk'=>array(
						'asc'=>'agent_property.geo_risk ASC',
						'desc'=>'agent_property.geo_risk DESC',
					),
					'agent_property_work_order_num'=>array(
						'asc'=>'agent_property.work_order_num ASC',
						'desc'=>'agent_property.work_order_num DESC',
					),
                    'fs_user_email'=>array(
						'asc'=>'fs_user.email ASC',
						'desc'=>'fs_user.email DESC',
					),
					'*',
				),
			),
			'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>$pageSize)
		));

		if($pageSize == -1)
		{
			$dataProvider->pagination = false;
		}
		else
		{
			$dataProvider->pagination->pageSize = $pageSize;
		}

		if($pageNumber != NULL)
			$dataProvider->pagination->currentPage = $pageNumber;

		return $dataProvider;
	}

	//creates a report of the current gridview (all pages)
    public function makeDownloadableReport($columnsToShow, $advSearch, $sort)
    {
        $myFile = Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_FSReports.csv';
		$fh = fopen($myFile, 'w') or die("can't open file");

        //headerrow
        $tempLine = '';
        foreach($columnsToShow as $column)
        {
            $tempLine .= $column.',';
        }
		fwrite($fh, rtrim($tempLine, ',')."\n");

		//loop through all pages in dataprovider so report contains all data rows
		$dataProvider = $this->search($advSearch, 100, $sort);
		$itemCount = 0;
        while($itemCount < $dataProvider->totalItemCount)
		{
			$pageData = '';
			foreach($dataProvider->data as $data)
			{
				//datarows
				$tempLine = '';
				foreach($columnsToShow as $columnToShow)
				{
					if($columnToShow == 'assigned_user_name')
						$tempLine .= '"'.str_replace('"', '""', (isset($data->assigned_user_name) ? $data->assigned_user_name : "")).'",';
					elseif(strpos($columnToShow, 'property_') !== FALSE)
					{
						$data_attr = str_replace('property_', 'property->', $columnToShow);
						$tempLine .= '"'.str_replace('"', '""', (isset($data->$data_attr) ? $data->$data_attr : "")).'",';
					}
					elseif(strpos($columnToShow, 'member_') !== FALSE)
					{
						if($columnToShow == 'member_member_num')
							$data_attr = 'member->member_num';
						else
							$data_attr = str_replace('member_', 'member->', $columnToShow);
						$tempLine .= '"'.str_replace('"', '""', (isset($data->$data_attr) ? $data->$data_attr : "")).'",';
					}
					else
					{
						$tempLine .= '"'.str_replace('"', '""', (isset($data->$columnToShow) ? $data->$columnToShow : "")).'",';
					}
				}
				$pageData .= rtrim($tempLine, ',')."\n";
				$itemCount++;
			}
			fwrite($fh, $pageData);
			$dataProvider = $this->search($advSearch, 100, $sort, $dataProvider->pagination->currentPage+1);
        }
		fclose($fh);
    }

	public function calcConditionRisk()
	{
		$risk = 0;
		foreach($this->conditions as $condition)
		{
			$risk += $condition->getRiskLevel();
		}
        return $risk;
	}

    /**
     * Calculates the risk level.
     * @param boolean $isNew indicates that this call is being made from the import method that is only run on the very first creation of the report and the orig_Los needs to be set
     */
	public function calcRiskLevel($isNew = NULL)
	{
		$this->risk_level = 0; //default

		if(!empty($this->geo_risk) && $this->geo_risk > 0)
			$geo_risk = $this->geo_risk;
		else
        {
            if($this->type == 'fs')
                $geo_risk = $this->property->geo_risk;
            elseif($this->type == '2.0')
                $geo_risk = $this->getPropertyRiskScore();
             elseif($this->type == 'sl')
                $geo_risk = $this->getPropertyRiskScore();
             elseif($this->type == 'fso')
             {
                $geo_risk = $this->getPropertyRiskScore();
             }
			else //agent report
				$geo_risk = $this->agent_property->geo_risk;
        }

		if($this->type == 'fs')
		{
            //bug fix for unmatched/non-geocoded properties that get reports submitted to them (there is no FSText's that work for geo_risk = 99). tc 7/20/2015
            if($geo_risk == 99 || empty($geo_risk))
                $geo_risk = 1;

			if($geo_risk <= 1 || ($geo_risk == 2 && $this->condition_risk < 9))
				$this->risk_level = 1;
			else if($geo_risk == 2 || ($geo_risk == 3 && $this->condition_risk <= 27))
				$this->risk_level = 2;
			else if($geo_risk == 3 && $this->condition_risk > 27)
				$this->risk_level = 3;
		}
        elseif($this->type == '2.0')
        {
            $this->risk_level = round((0.4 * $geo_risk) + (0.6 * $this->condition_risk), 0);
        }
        elseif($this->type == 'sl')
        {
            $this->risk_level = round((0.4 * $geo_risk) + (0.6 * $this->condition_risk), 0);
        }
        elseif($this->type == 'fso')
        {
            $this->risk_level = round((0.4 * $geo_risk) + (0.6 * $this->condition_risk), 0);
        }
		else //agent report
		{
			//$this->risk_level = round($geo_risk * $this->condition_risk * $this->client->risk_multiplier, 2);
            $geo_score = ($geo_risk * 100) / $this->client->getMaxPts('site');
            $site_score = ($this->condition_risk * 100) / $this->client->getMaxPts('site');
            $this->risk_level = round( (0.4 * $geo_score) + (0.6 * $site_score), 2);
		}

		if($isNew || empty($this->orig_risk_level))
			$this->orig_risk_level = $this->risk_level;

		$this->save();
	}

	public function zipReport()
	{
		$zip = new FSReportZipper();
        $outgoing_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR;
		$result = $zip->open($outgoing_path.'report.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        if($result === TRUE)
        {
            if($this->type != '2.0')
            {
                $zip->addFile($outgoing_path.'report.pdf', 'report.pdf');
                $zip->addFile($outgoing_path.'payload.json', 'payload.json');
                $zip->addDir($outgoing_path.'html/');
            }
            else //app2 report
            {
                $zip->addFile($outgoing_path.'payload.json', 'payload.json');
                if(in_array('UW', explode(',', $this->download_types)))
                    $zip->addFile($outgoing_path.'report_uw.pdf', 'report_uw.pdf');
                if(in_array('EDU', explode(',', $this->download_types)))
                    $zip->addFile($outgoing_path.'report_edu.pdf', 'report_edu.pdf');
            }
            $zip->close();
            return true;
        }
		else
        {
            //echo 'ZIP FILE FAILED. ERROR CODE: '.$result;
            return false;
        }
	}

    public function createJSONFile2()
    {
        $json = array();
        $json['reports'] = array();
        if(in_array('UW', explode(',', $this->download_types)))
            $json['reports'][] = array('name'=>'report_uw.pdf', 'type'=>'UW');
        if(in_array('EDU', explode(',', $this->download_types)))
            $json['reports'][] = array('name'=>'report_edu.pdf', 'type'=>'EDU');
        //create outgoing folder
        
        $outgoing_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR;
        if(!file_exists($outgoing_path))
           mkdir(Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid);
		return file_put_contents($outgoing_path.'payload.json', json_encode($json));
    }


	public function createJSONFile()
	{
		$json = array();

		$binaryReportUri = 'report.pdf';
		$json['binaryReportUri'] = $binaryReportUri;

		$json['address'] = array('addressLine1' => $this->address_line_1, 'city' => $this->city, 'state' => $this->state, 'zip' => $this->zip);
		$json['headingTitle'] = 'Report';

		$reportData = array();

		$summarySection = array('sectionTitle' => 'Summary', 'items'=>array());
		if($this->risk_level > 1) //only 2/3 risk level get custom summary
		{
			$items = array('conditionType' => 0, 'title' => 'Summary', 'contentUri' => 'html/summary.html');
			$summarySection['items'][] = $items;
		}
		$reportData[] = $summarySection;

		//conditions
		$recommendedActionsSection = array('sectionTitle' => 'Recommended Actions', 'items'=>array());
		$goodConditionsSection = array('sectionTitle' => 'Good Conditions', 'items'=>array());
		$recommendedActionItems = array();
		$goodConditionItems = array();
		foreach($this->conditions as $condition)
		{
			if($condition->response == 0 || $condition->response == 2) //yes or not sure response
				$recommendedActionItems[] = array('conditionType' => 1, 'title' => $condition->getType(), 'contentUri' => "html/".$condition->condition_num.'.html');
			else //no response
				$goodConditionItems[] = array('conditionType' => 2, 'title' => $condition->getType(), 'contentUri' => "html/".$condition->condition_num.'.html');

			$recommendedActionsSection['items'] = $recommendedActionItems;
			$goodConditionsSection['items'] = $goodConditionItems;
		}
		$reportData[] = $recommendedActionsSection;
		$reportData[] = $goodConditionsSection;

		$json['reportData'] = $reportData;

		if($this->risk_level == 3) //only risk level 3 should get ability to schedule a follow up call
			$json['scheduler'] = array('showScheduler'=>true);
		else
			$json['scheduler'] = array('showScheduler'=>false);

        $outgoing_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR;

        $filePath = '';

        if (file_exists(pathinfo($outgoing_path, PATHINFO_DIRNAME)))
        {
            $filePath = file_put_contents($outgoing_path.'payload.json', json_encode($json));
        }

        return $filePath;
	}

	public function createSummaryHTMLTemplate()
	{
		$html = '
			<!DOCTYPE html>
			<html>
				<head>
					<meta name="viewport" content=" initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
					<title></title>
					<link href="css/style.css" rel="stylesheet" media="screen" type="text/css">
					<script src="fontAdjust.js" type="text/javascript"></script>
				</head>
				<body>
					<div class="background">&nbsp;</div>
					<div class="container">
						<div class="header" style="font-size:16px;">
							Your WDSpro Wildfire Assessment
						</div>
						<div class="content">
							'.$this->summary.'
						</div>
					</div>
				</body>
			</html>';

        $file_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'summary.html';
        $file_headers = @get_headers($file_path);
        if (isset($file_headers[0]) && $file_headers[0] === 'HTTP/1.1 200 OK')
        {
		    if(file_put_contents($file_path, $html) === FALSE)
            {
			    return false;
		    }
		    else
            {
			    return true;
		    }
        }
        else
        {
           return false;
        }
	}

	public function createKMLFile()
	{
        $file_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'ge.kml';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
		<kml xmlns="http://www.opengis.net/kml/2.2">
		  <Placemark>
			<name>'.$this->address_line_1.', '.$this->city.', '.$this->state.' '.$this->zip.'</name>
			<description>Attached to the ground. Intelligently places itself
			   at the height of the underlying terrain.</description>
			<Point>
			  <coordinates>'.$this->longitude.','.$this->latitude.'</coordinates>
			</Point>
		  </Placemark>
		</kml>
		';

        if (file_exists(pathinfo($file_path, PATHINFO_DIRNAME)))
        {
            file_put_contents($file_path, $xml) or die("Could Not write kml file");
        }
        else
        {
            return false;
        }
	}

	public function createPDFReport($usaa_ver=false, $show_usaa_text=true)
	{
		//move images to temp dir
		$pdf = new FSPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);

		//set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 18, PDF_MARGIN_RIGHT);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);

		//if risk level 3 show wds contact info footer
		if($this->risk_level == 3)
			$pdf->setPrintFooter(true);
		else
			$pdf->setPrintFooter(false);
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		//HTML

		//Front page
		$pdf->SetTitle("Hazard Assessment");
        $pdf->SetFont('helvetica', '', 12);
        $pdf->AddPage();
        $reportName = 'report.pdf';
		if($usaa_ver)
			$reportName = 'usaaReport.pdf';

        $filePath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.$reportName;
        $html = '';

        if (file_exists(pathinfo($filePath, PATHINFO_DIRNAME)))
        {
            $html = '<img height="78" width="71" src="https://pro.wildfire-defense.com/images/logo.jpg" />';
            @$pdf->writeHTML($html, true, false, true, false, 'C');
            $html = '<span style="font-color: #3399ff; font-size: 16pt"><b>WILDFIRE DEFENSE SYSTEMS, INC.</b></span>';
            @$pdf->writeHTML($html, true, false, true, false, 'C');
            $html = '<span style="font-color: #3399ff; font-size: 14pt"><b>Hazard Assessment Report</b></span>';
            @$pdf->writeHTML($html, true, false, true, false, 'C');
            $html = '<br><span>'.$this->address_line_1.', '.$this->city.', '.$this->state.' '.$this->zip.'</span><br>';
            @$pdf->writeHTML($html, true, false, true, false, 'C');
            $pdf->lastPage();
		    if($usaa_ver)
		    {
			    $homeowner = Homeowner::model()->find("address = '".$this->address_line_1."' AND zip = '".$this->zip."'");
			    $ho_name = 'N/A';
			    $ho_member_num = 'N/A';
			    if(!is_null($homeowner))
			    {
				    if(!empty($homeowner->name))
					    $ho_name = $homeowner->name;
				    if(!empty($homeowner->member_num))
					    $ho_member_num = $homeowner->member_num;
			    }
			    $html = '<br><br>This report was prepared for<br><b>'.$ho_name.'</b><br>Member #'.$ho_member_num.'<br>Inspection Date: '.date('F d, Y');
			    @$pdf->writeHTML($html, true, false, true, false, 'C');
		    }

            if ($show_usaa_text) {
                @$pdf->writeHTML('<br><br><br><b>This service was provided at the request of USAA to complement the safety of your home and family. Understanding your wildlife hazards and how to address them can greatly reduce the risk of losing your home to a wildfire.<b><br><br>', true, false, true, false, 'C');
            }
            @$pdf->writeHTML('<br>The information in this report identifies conditions and offers recommendations for reducing wildfire risk. <br><br>', true, false, true, false, 'C');
            @$pdf->writeHTML('<br><br><table><tr><td width="25%" style="background-color:#f77119"><img src="https://pro.wildfire-defense.com/images/hazard.jpg"></td><td width="75%" style="background-color:#f77119;color:white;font-size:14pt">By modifying hazards noted in this report, your wildfire risk can be reduced, and your home will have an improved chance of surviving a wildfire.</td></tr></table><br><br>', true, true, true, false, 'C');

		    if($usaa_ver)
		    {
			    //Hazard Table
			    $hazardRatingTable = '<table>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow" colspan="2"><u>Hazard Rating Scale</u></td></tr>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow">Low Structure ignition Risk</td><td style="background-color:yellow">0-8</td></tr>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow">Moderate Structure ignition Risk</td><td style="background-color:yellow">9-26</td></tr>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow">High/Very High Structure ignition Risk</td><td style="background-color:yellow">27+</td></tr>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow" colspan="2"><br><br><u>Risk/Service Levels</u></td></tr>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow">Home Characteristic Risk Level:</td><td style="background-color:yellow">'.$this->condition_risk.'</td></tr>';
			    $hazardRatingTable .= '<tr><td style="background-color:yellow">Service Level:</td><td style="background-color:yellow">'.$this->risk_level.'</td></tr>';
			    $hazardRatingTable .= '</table>';
			    @$pdf->writeHTML($hazardRatingTable);
		    }

		    //Summary page
		    if($this->risk_level > 1)
		    {
			    $pdf->AddPage();
                @$pdf->writeHTML("<h2>SUMMARY</h2><br>");
			    @$pdf->writeHTML($this->summary);
		    }

		    //conditions
		    //yes conditions
		    foreach($this->conditions as $condition)
		    {
			    if($condition->response == 0) //yes, only one per page
			    {
				    $pdf->AddPage();
				    @$pdf->writeHTML($condition->getPDFTemplateHTML($usaa_ver));
			    }
		    }

		    //no conditions
		    $noConditionCounter = 0;
		    foreach($this->conditions as $condition)
		    {

			    if($condition->response == 1) //No, only one per page
			    {
				    $noConditionCounter++;
				    if($noConditionCounter == 1) //create new page for first one
					    $pdf->AddPage();
				    else if($noConditionCounter == 2) //if there have been 2, then reset counter so a new page is added
					    $noConditionCounter = 0;
				    @$pdf->writeHTML($condition->getPDFTemplateHTML($usaa_ver));
			    }
		    }

		    //Last page
		    if($noConditionCounter != 1) //already 2 conditions, so add new page. otherwise it gets tacked on to the 1 no condition page at the end
			    $pdf->AddPage();
		    @$pdf->writeHTML('<p>The homeowner is encouraged to abide by all State fire codes pertaining to the Wildland Urban Interface. For additional information regarding fire codes in your specific community, contact your local fire department.</p>', true, false, true, false, 'C');
		    @$pdf->writeHTML("<br /><p>The components of this assessment are derived from the National Fire Protection Association's&copy; National Firewise&trade; Program and the NFPA Standard 1144.<br>www.nfpa.org www.firewise.org</p>", true, false, true, false, 'C');

		    if ($show_usaa_text) {
                @$pdf->writeHTML('<br /><p>There are no guarantees that mitigation steps taken as a result of this report will prevent damage. Neither USAA, Wildfire Defense Systems, Inc. nor their representatives take responsibility for personal injury or property damage arising out of reliance on the wildfire mitigation recommendations or this assessment report.</p>', true, false, true, false, 'C');
            } else {
                @$pdf->writeHTML('<br /><p>There are no guarantees that mitigation steps taken as a result of this report will prevent damage. Wildfire Defense Systems, Inc. nor their representatives take responsibility for personal injury or property damage arising out of reliance on the wildfire mitigation recommendations or this assessment report.</p>', true, false, true, false, 'C');
            }
		    @$pdf->writeHTML('<br /><p>The evaluations, reports and recommendations regarding changes that should be considered to help protect your property are designed to provide additional protection in the event of wildfire. Even if every recommended step is taken, your property could still be destroyed because wildfire is unpredictable and can be impossible to stop or control, no matier what mitigation efforts have been undertaken. WDS does not represent or warrant that taking the steps suggested can or will protect your property from destruction by fire. No warranties or representations of any kind are provided to the recipient of this evaluation.</p>', true, false, true, false, 'C');

            if (file_exists($filePath))
            {
                @unlink ($filePath);
            }

			//Close and output PDF document
			$pdf->Output($filePath, 'F');
		}
		else
		{
            return false;
        }
	}


	public function getAgentReportRiskLevelLabel($type = 'los', $risk_level = null)
	{
		if(!isset($risk_level))
			$risk_level = $this->risk_level;

		$los_struct = json_decode($this->client->report_los_structure);
		if(!isset($los_struct))
			return 'ERROR Parsing the los structure json for the client';

		$risk_level_label = 'n/a';
		foreach($los_struct as $entry)
		{
			if($entry->type == $type && $risk_level >= $entry->start_value && $risk_level <= $entry->end_value)
			{
				if(isset($entry->label))
					$risk_level_label = $entry->label;
			}
		}
		return $risk_level_label;
	}

    /*
     *  2.0 PDF Report Creation
     *  SL PDF Report Creation
     */
    public function createPDFReport2()
    {
        if($this -> type == 'sl' || $this -> type == 'fso')
        {
            $this-> createSLUWPDFReport();
            $this-> createSLEDUPDFReport();
        }
        else
        {
            $this->createApp2UWPDFReport();
            $this->createApp2EDUPDFReport();
        }
    }

    private function getReportStamp($property)
    {
        $report_stamp = $property->client->report_stamp_1;
        //if(!empty($property->member_mid))
        //{
        //    $address = "<br><b>Policyholder:</b> ".$property->member->first_name." ".$property->member->last_name;
        //}

        //$address .= "<br><br><b>Address:</b> ".$property->address_line_1."<br><br>".$property->city.", ".$property->state." ".$property->zip."<br><br><b>Policy:</b> ".$property->policy."<br><br><b>Agent:</b> ".$this->fs_user->name."<br><br><b>Date:</b> ".$this->submit_date;
    }

    private function createApp2UWPDFReport()
    {
        //grap the report options
        $report_options = $this->getClientReportOptions();
        //pull property info into local var or set it to a new blank one if for some reason it's empty from an error on import
        $property = $this->property;
        if(!isset($property))
            $property = new Property;

        //setup new pdf
        $pdf = new FSPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
        if(!empty($this->pdf_pass))
            $pdf->SetProtection(array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), $this->pdf_pass, null, 0, null);
        $pdf->SetLeftMargin(5);
        $pdf->SetRightMargin(5);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);
        if(!empty($report_options['app2-uw-footer-text']))
            $pdf->custom_footer_text = $report_options['app2-uw-footer-text'];
        $pdf->setPrintFooter(true);

        //images for FOH and Logos setup
        $pdf->SetTitle("WDSpro Assessment");
        $pdf->SetFont('helvetica', '', 12);
        $wds_pro_logo = Yii::app()->getBaseUrl(true) .'/images/WDSpro-with-R-for-web.jpg';
        $missing_image = Yii::app()->getBaseUrl(true). '/images/missing-image.jpg';
        $foh_image_src = $client_logo = $missing_image;
        $foh_question = FSAssessmentQuestion::model()->findByAttributes(array('set_id'=>$this->getQuestionSetID(), 'client_id' => ($this->type=='sl' || $this->type=='fso')? $this->user->client_id : $this->fs_user->getClientID(), 'type'=>'foh'));

        $incoming_images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        if(isset($foh_question))
        {
            $foh_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$foh_question->question_num, 'set_id'=>$this->getQuestionSetID()));
            if(isset($foh_condition))
            {
                $foh_photos_array = $foh_condition->getSubmittedPhotosArray();
                if(count($foh_photos_array) > 0)
                    $foh_image_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$incoming_images_path.$foh_photos_array[0]));
            }
        }
        if(!empty($report_options['app2-uw-logo']))
            $client_logo = Yii::app()->getBaseUrl(true) . $report_options['app2-uw-logo'];

        //risk score setup
        $site_score = $this->calcConditionRisk();
        $integrated_score = $this->risk_level;
        $wds_risk_score = $wds_risk_whp = $wds_risk_v = $state_mean = $state_std_dev = $dev = 'n/a';
        if(isset($property->wdsRisk->score_wds))
            $wds_risk_score = $property->wdsRisk->score_wds;
        if(isset($property->wdsRisk->score_whp))
            $wds_risk_whp = number_format($property->wdsRisk->score_whp, 6);
        if(isset($property->wdsRisk->score_v))
            $wds_risk_v = number_format($property->wdsRisk->score_v, 6);
        if(isset($property->wdsRiskStateMeans->std_dev))
            $state_std_dev = $property->wdsRiskStateMeans->std_dev;
        if(isset($property->wdsRiskStateMeans->mean))
            $state_mean = $property->wdsRiskStateMeans->mean;
        if(isset($property->wdsRiskDev))
        {
            if($property->wdsRiskDev > 3)
                $dev = '+3';
            else if ($property->wdsRiskDev < -3)
                $dev = '-3';
            else if($property->wdsRiskDev < 0)
                $dev = floor($property->wdsRiskDev); //round down to the next integer
            else //$property->wdsRiskDev >= 0)
                $dev = '+'.ceil($property->wdsRiskDev); //round up to the next integer
        }

        //risk conditions setup
        $i=0;
        $total_site_risk = 0;
        $risks_present_html = '';
        $condition_titles = array();
        $condition_images = array();
        $condition_texts = array();
        $incoming_images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        foreach($this->getOrderedConditionsAndQuestions('condition') as $conditionQuestion)
	    {
            if($conditionQuestion['condition']->response == 0 && $conditionQuestion['question']->type == 'condition') //if yes
            {
                $i++;
                $risks_present_html .= $i.'. '.htmlspecialchars($conditionQuestion['question']->title).' ('.$conditionQuestion['condition']->score.' points)<br>';
                $total_site_risk += $conditionQuestion['condition']->score;

                $condition_titles[$i] = $i.'. '.htmlspecialchars($conditionQuestion['question']->title).' ('.$conditionQuestion['condition']->score.' points)';
                if(!empty($conditionQuestion['condition']->submitted_photo_path))
                {
                    $submittedPhotos = $conditionQuestion['condition']->getSubmittedPhotosArray();
                    $photo_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$incoming_images_path.$submittedPhotos[0]));
                }
                else
                    $photo_src = $missing_image;
                $condition_images[$i] = '<img style="width:120px;height:120px;border-top:1px solid black;" src="'.$photo_src.'" />';
                $condition_texts[$i] = htmlspecialchars($conditionQuestion['condition']->risk_text);
            }
        }
        $total_risks = $i;

        $risks_present_html .= '<br><b>TOTAL SITE RISK: </b>'.$total_site_risk;

        //front page
        $report_stamp = '';
        if(isset($report_options['app2-uw-stamp']))
        {
        $report_stamp = $report_options['app2-uw-stamp'];
        }

        $pdf->AddPage();
        $html = <<<HTML
            <style>
                .logo {
                    text-align: center;
                }

                .logo img {
                    width:200px;
                }
                .uw-table {
                    padding:5px;
                    font-size: 25px;
                }
                .uw-table td {
                    border: 1px solid black;
                }
                .top-score {
                    width: 80px;
                    text-align: center;
                    background-color: #FFC000;
                }
                .top-score-definitions {
                    font-style: italic;
                    text-align: right;
                }
                .risk-summary-header {
                    background-color: #FFC000;
                    text-align: center;
                    font-weight: bold;
                }
                .top-address {
                    text-align:center;
                    width:145px;
                }
                .top-client-logo {
                    width:200px;
                    text-align:center;

                }
                .top-score-breakdown {
                    text-align: center;
                    background-color: black;
                    color: white;
                    font-weight: bold;
                }
                .foh-img {
                    border: 1px solid black;
                }
                .foh {
                    background-color: #D9D9D9;
                    text-align: center;
                    font-weight: bold;
                    border: 1px solid black;
                    width:140px;
                    padding: -10px;
                }
            </style>
            <div class="logo">
                <img src="$wds_pro_logo" />
            </div>
            <table class="uw-table">
                <tr>
                    <td rowspan="2" class="foh">
                        <img class="foh-img" src="$foh_image_src" width="120px" height="120px">
                        <br><b>Front of House</b>
                    </td>
                    <td rowspan="2" class="top-address">$report_stamp</td>
                    <td class="top-client-logo">In cooperation with: <br><img width="175px" src="$client_logo" /></td>
                    <td class="top-score">INTEGRATED RISK <br><span style="font-size:70px">$integrated_score</span></td>
                </tr>
                <tr>
                    <td class="top-score-definitions">
                        Site = risks immediate to home <br>
                        WHP = wildfire hazard potential <br>
                        V = location vulnerability to fire <br>
                        Dev = Deviation from location standard
                    </td>
                    <td class="top-score-breakdown">
                        Site = $site_score <br>
                        WHP = $wds_risk_whp <br>
                        V = $wds_risk_v <br>
                        Dev = $dev
                    </td>
                </tr>
HTML;
        $html .= ' <tr>';

        if($this->level > 1)
        {
            $html .= '  <td colspan="2" class="risk-summary-header">Risks Present</td>';
            $html .= '  <td colspan="2" class="risk-summary-header">Risk Detail</td>';
        }
        else
            $html .= '  <td colspan="4" class="risk-summary-header">Risks Present</td>';
        $html .= ' </tr>';
        $html .= ' <tr>';
        if($this->level > 1)
        {
            $html .= '  <td colspan="2">'.$risks_present_html.'</td>';
            $html .= '  <td colspan="2">'.$this->risk_detail.'</td>';
        }
        else
            $html .= '  <td colspan="4">'.$risks_present_html.'</td>';
        $html .= ' </tr>';
        $html .= '</table>';

        $pdf->writeHTML($html);
        $pdf->lastPage();

        //conditions table output
        $html = '<table style="border:1px solid black;">';
        for($i=1; $i <= $total_risks; $i++)
        {
            if($i % 2 !== 0) //only process odd numbers (cause we add 2 at a time)
            {
                //title cell(s)
                $html .= '<tr>';
                $html .= '<td colspan="2" style="border:1px solid black;background-color:#D9D9D9;">'.$condition_titles[$i].'</td>';
                if($i === $total_risks) //if it's an odd and the end then need to add blanks
                {
                    $html .= '<td colspan="2"></td>';
                }
                else
                {
                    $html .= '<td colspan="2" style="border:1px solid black;background-color:#D9D9D9;">'.$condition_titles[$i+1].'</td>';
                }
                $html .= '</tr>';

                //image and text cells
                $html .= '<tr>';
                $html .= '<td style="border:1px solid black;padding-top:5px;width:120px;">'.$condition_images[$i].'</td>';
                $html .= '<td style="margin-top:auto;margin-bottom:auto;border:1px solid black;width:163px;font-size: 25px;"><br><br>'.$condition_texts[$i].'</td>';
                if($i === $total_risks) //if it's an odd and the end then need to add blanks
                {
                    $html .= '<td></td><td></td>';
                }
                else
                {
                    $html .= '<td style="border:1px solid black;padding-top:5px;width:120px;">'.$condition_images[$i+1].'</td>';
                    $html .= '<td style="border:1px solid black;width:163px;font-size: 25px;"><br><br>'.$condition_texts[$i+1].'</td>';
                }
                $html .= '</tr>';
            }
            if($i === 4 || $i === 14 || $i === 24 || $i === 34 || $i === 44) //on 4th (only 4 on first page, 10 on each page after), 14th, 24th ones write the table and then start a new page and new table
            {
                $html .= '</table>';
                $pdf->writeHTML($html);
                if($i < $total_risks) //only add new page if there are more conditions
                    $pdf->AddPage();

                $pdf->lastPage();
                $html = '<table style="border:1px solid black;">';
            }
        }
        if($html !== '<table style="border:1px solid black;">') //no need to write it if its just an empty table
        {
            $html .= '</table>';
            $pdf->writeHTML($html);
            $pdf->lastPage();
        }

        //finalize and save report
        $outgoing_report_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'report_uw.pdf';
        //remove existing report if there is one
        if(file_exists($outgoing_report_path))
			unlink ($outgoing_report_path);
		//Close and output PDF document
		$pdf->Output($outgoing_report_path, 'F');
    }

    private function createSLUWPDFReport()
    {
        //grap the report options
        $report_options = $this->getClientReportOptions();
        //pull property info into local var or set it to a new blank one if for some reason it's empty from an error on import
        $property = $this->property;
        if(!isset($property))
            $property = new Property;

        //setup new pdf
        $pdf = new FSPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
        if(!empty($this->pdf_pass))
            $pdf->SetProtection(array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), $this->pdf_pass, null, 0, null);
        $pdf->SetLeftMargin(5);
        $pdf->SetRightMargin(5);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);
        if(!empty($report_options['sl-uw-footer-text']))
            $pdf->custom_footer_text = $report_options['sl-uw-footer-text'];
        $pdf->setPrintFooter(true);

        //images for FOH and Logos setup
        $pdf->SetTitle("WDSpro Assessment");
        $pdf->SetFont('helvetica', '', 12);
        $wds_pro_logo = Yii::app()->getBaseUrl(true) .'/images/WDSpro-with-R-for-web.jpg';
        $missing_image = Yii::app()->getBaseUrl(true). '/images/missing-image.jpg';
        $foh_image_src = $client_logo = $missing_image;
        $foh_question = FSAssessmentQuestion::model()->findByAttributes(array('set_id'=>$this->getQuestionSetID(), 'client_id' => ($this->type=='sl' || $this->type=='fso')? $this->user->client_id : $this->fs_user->getClientID(), 'type'=>'foh'));

        $incoming_images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        if(isset($foh_question))
        {
            $foh_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$foh_question->question_num, 'set_id'=>$this->getQuestionSetID()));
            if(isset($foh_condition))
            {
                $foh_photos_array = $foh_condition->getSubmittedPhotosArray();
                if(count($foh_photos_array) > 0)
                    $foh_image_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$incoming_images_path.$foh_photos_array[0]));
            }
        }
        if(!empty($report_options['sl-uw-logo']))
            $client_logo = Yii::app()->getBaseUrl(true) . $report_options['sl-uw-logo'];

        //risk score setup
        $site_score = $this->calcConditionRisk();
        $integrated_score = $this->risk_level;
        $wds_risk_score = $wds_risk_whp = $wds_risk_v = $state_mean = $state_std_dev = $dev = 'n/a';
        if(isset($property->wdsRisk->score_wds))
            $wds_risk_score = $property->wdsRisk->score_wds;
        if(isset($property->wdsRisk->score_whp))
            $wds_risk_whp = number_format($property->wdsRisk->score_whp, 6);
        if(isset($property->wdsRisk->score_v))
            $wds_risk_v = number_format($property->wdsRisk->score_v, 6);
        if(isset($property->wdsRiskStateMeans->std_dev))
            $state_std_dev = $property->wdsRiskStateMeans->std_dev;
        if(isset($property->wdsRiskStateMeans->mean))
            $state_mean = $property->wdsRiskStateMeans->mean;
        if(isset($property->wdsRiskDev))
        {
            if($property->wdsRiskDev > 3)
                $dev = '+3';
            else if ($property->wdsRiskDev < -3)
                $dev = '-3';
            else if($property->wdsRiskDev < 0)
                $dev = floor($property->wdsRiskDev); //round down to the next integer
            else //$property->wdsRiskDev >= 0)
                $dev = '+'.ceil($property->wdsRiskDev); //round up to the next integer
        }

        //risk conditions setup
        $i=0;
        $total_site_risk = 0;
        $risks_present_html = '';
        $condition_titles = array();
        $condition_images = array();
        $condition_texts = array();
        $incoming_images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        foreach($this->getOrderedConditionsAndQuestions('condition') as $conditionQuestion)
	    {
            if($conditionQuestion['condition']->response == 0 && $conditionQuestion['question']->type == 'condition') //if yes
            {
                $i++;
                $risks_present_html .= $i.'. '.htmlspecialchars($conditionQuestion['question']->title).' ('.$conditionQuestion['condition']->score.' points)<br>';
                $total_site_risk += $conditionQuestion['condition']->score;

                $condition_titles[$i] = $i.'. '.htmlspecialchars($conditionQuestion['question']->title).' ('.$conditionQuestion['condition']->score.' points)';
                if(!empty($conditionQuestion['condition']->submitted_photo_path))
                {
                    $submittedPhotos = $conditionQuestion['condition']->getSubmittedPhotosArray();
                    $photo_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$incoming_images_path.$submittedPhotos[0]));
                }
                else
                    $photo_src = $missing_image;
                $condition_images[$i] = '<img style="width:120px;height:120px;border-top:1px solid black;" src="'.$photo_src.'" />';
                $condition_texts[$i] = htmlspecialchars($conditionQuestion['condition']->risk_text);
            }
        }
        $total_risks = $i;

        $risks_present_html .= '<br><b>TOTAL SITE RISK: </b>'.$total_site_risk;

        //front page
        $report_stamp = '';
        if(isset($report_options['sl-uw-stamp']))
        {
        $report_stamp = $report_options['sl-uw-stamp'];
        }

        $pdf->AddPage();
        $html = <<<HTML
            <style>
                .logo {
                    text-align: center;
                }

                .logo img {
                    width:200px;
                }
                .uw-table {
                    padding:5px;
                    font-size: 25px;
                }
                .uw-table td {
                    border: 1px solid black;
                }
                .top-score {
                    width: 80px;
                    text-align: center;
                    background-color: #FFC000;
                }
                .top-score-definitions {
                    font-style: italic;
                    text-align: right;
                }
                .risk-summary-header {
                    background-color: #FFC000;
                    text-align: center;
                    font-weight: bold;
                }
                .top-address {
                    text-align:center;
                    width:145px;
                }
                .top-client-logo {
                    width:200px;
                    text-align:center;

                }
                .top-score-breakdown {
                    text-align: center;
                    background-color: black;
                    color: white;
                    font-weight: bold;
                }
                .foh-img {
                    border: 1px solid black;
                }
                .foh {
                    background-color: #D9D9D9;
                    text-align: center;
                    font-weight: bold;
                    border: 1px solid black;
                    width:140px;
                    padding: -10px;
                }
            </style>
            <div class="logo">
                <img src="$wds_pro_logo" />
            </div>
            <table class="uw-table">
                <tr>
                    <td rowspan="2" class="foh">
                        <img class="foh-img" src="$foh_image_src" width="120px" height="120px">
                        <br><b>Front of House</b>
                    </td>
                    <td rowspan="2" class="top-address">$report_stamp</td>
                    <td class="top-client-logo">In cooperation with: <br><img width="175px" src="$client_logo" /></td>
                    <td class="top-score">INTEGRATED RISK <br><span style="font-size:70px">$integrated_score</span></td>
                </tr>
                <tr>
                    <td class="top-score-definitions">
                        Site = risks immediate to home <br>
                        WHP = wildfire hazard potential <br>
                        V = location vulnerability to fire <br>
                        Dev = Deviation from location standard
                    </td>
                    <td class="top-score-breakdown">
                        Site = $site_score <br>
                        WHP = $wds_risk_whp <br>
                        V = $wds_risk_v <br>
                        Dev = $dev
                    </td>
                </tr>
HTML;
        $html .= ' <tr>';

        if($this->level > 1)
        {
            $html .= '  <td colspan="2" class="risk-summary-header">Risks Present</td>';
            $html .= '  <td colspan="2" class="risk-summary-header">Risk Detail</td>';
        }
        else
            $html .= '  <td colspan="4" class="risk-summary-header">Risks Present</td>';
        $html .= ' </tr>';
        $html .= ' <tr>';
        if($this->level > 1)
        {
            $html .= '  <td colspan="2">'.$risks_present_html.'</td>';
            $html .= '  <td colspan="2">'.$this->risk_detail.'</td>';
        }
        else
            $html .= '  <td colspan="4">'.$risks_present_html.'</td>';
        $html .= ' </tr>';
        $html .= '</table>';

        $pdf->writeHTML($html);
        $pdf->lastPage();
        $pdf->addPage();

        //conditions table output
        $html = '<table style="border:1px solid black;">';
        for($i=1; $i <= $total_risks; $i++)
        {
            if($i % 2 !== 0) //only process odd numbers (cause we add 2 at a time)
            {
                //title cell(s)
                $html .= '<tr>';
                $html .= '<td colspan="2" style="border:1px solid black;background-color:#D9D9D9;">'.$condition_titles[$i].'</td>';
                if($i === $total_risks) //if it's an odd and the end then need to add blanks
                {
                    $html .= '<td colspan="2"></td>';
                }
                else
                {
                    $html .= '<td colspan="2" style="border:1px solid black;background-color:#D9D9D9;">'.$condition_titles[$i+1].'</td>';
                }
                $html .= '</tr>';

                //image and text cells
                $html .= '<tr>';
                $html .= '<td style="border:1px solid black;padding-top:5px;width:120px;">'.$condition_images[$i].'</td>';
                $html .= '<td style="margin-top:auto;margin-bottom:auto;border:1px solid black;width:163px;font-size: 25px;"><br><br>'.$condition_texts[$i].'</td>';
                if($i === $total_risks) //if it's an odd and the end then need to add blanks
                {
                    $html .= '<td></td><td></td>';
                }
                else
                {
                    $html .= '<td style="border:1px solid black;padding-top:5px;width:120px;">'.$condition_images[$i+1].'</td>';
                    $html .= '<td style="border:1px solid black;width:163px;font-size: 25px;"><br><br>'.$condition_texts[$i+1].'</td>';
                }
                $html .= '</tr>';
            }
            if($i === 4 || $i === 14 || $i === 24 || $i === 34 || $i === 44) //on 4th (only 4 on first page, 10 on each page after), 14th, 24th ones write the table and then start a new page and new table
            {
                $html .= '</table>';
                $pdf->writeHTML($html);
                if($i < $total_risks) //only add new page if there are more conditions
                    $pdf->AddPage();

                $pdf->lastPage();
                $html = '<table style="border:1px solid black;">';
            }
        }
        if($html !== '<table style="border:1px solid black;">') //no need to write it if its just an empty table
        {
            $html .= '</table>';
            $pdf->writeHTML($html);
            $pdf->lastPage();
        }

        //finalize and save report
        $outgoing_report_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'report_uw.pdf';
        //remove existing report if there is one
        if(file_exists($outgoing_report_path))
			unlink ($outgoing_report_path);
		//Close and output PDF document
		$pdf->Output($outgoing_report_path, 'F');
    }
    private function createApp2EDUPDFReport()
    {
        $property = $this->property;
        if(!isset($property))
            $property = new Property;

        $report_options = $this->getClientReportOptions();

        $pdf = new FSPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
        if(!empty($this->pdf_pass))
            $pdf->SetProtection(array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), $this->pdf_pass, null, 0, null);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 18, PDF_MARGIN_RIGHT);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);
        if(!empty($report_options['app2-edu-footer-text']))
            $pdf->custom_footer_text = $report_options['app2-edu-footer-text'];
        $pdf->setPrintFooter(true);


        $pdf->SetTitle("WDSpro Assessment");
        $pdf->SetFont('helvetica', '', 12);

        //front page
        $pdf->AddPage();
        $wds_pro_logo = '/images/WDSpro-with-R-for-web.jpg';
        $missing_image = '/images/missing-image.jpg';
        $foh_image_src = $missing_image;
        $foh_question = FSAssessmentQuestion::model()->findByAttributes(array('set_id'=>$this->getQuestionSetID(), 'client_id' => ($this->type=='sl' || $this->type=='fso') ? $this->user->client_id: $this->fs_user->getClientID(), 'type'=>'foh'));
        $images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        if(isset($foh_question))
        {
            $foh_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$foh_question->question_num, 'set_id'=>$this->getQuestionSetID()));
            if(isset($foh_condition))
            {
                $foh_photos_array = $foh_condition->getSubmittedPhotosArray();
                if(count($foh_photos_array) > 0)
                    $foh_image_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$images_path.$foh_photos_array[0]));
            }
        }
        if(!empty($report_options['app2-edu-logo']))
            $client_logo = $report_options['app2-edu-logo'];
        else
            $client_logo = $missing_image;
        $html = '<img width="150px" src="'.$wds_pro_logo.'" />';
        $html .= '<br><img src="/images/blue-hr.png" /><br><br>';
        $html .= '<table><tr>';
        $html .= '<td style="width:155px;"><img width="135px" height="135px" src="'.$foh_image_src.'" /></td>';
        $html .= '<td style="font-size:27px;width:200px;border-top:1px solid grey;border-bottom:1px solid grey;">';
        $html .= (isset($report_options['app2-edu-stamp'])) ? $report_options['app2-edu-stamp'] : '';
        $html .= '</td>';
        $html .= '<td style="text-align:right;font-size:27px;width:150px;border-top:1px solid grey;border-bottom:1px solid grey;"><br><br><img width="100px" src="'.$client_logo.'" /></td>';
        $html .= '</tr></table>';
        $html .= '<span style="color:grey;font-size:30px;"><b>&nbsp;&nbsp;Front of House</b>';
        $html .= '<br>';
        $pdf->writeHTML($html);
        if($this->level == 2)
        {
            $html = '<div style="background-color:#002B45;color:white;font-size:33px;width:600px;line-height:4px;letter-spacing:1.5px;border:1px solid grey;">&nbsp;Wildfire Concerns in the Surrounding Area</div>';
            $html .= '<p style="line-height:110%;color:black;">'.$this->summary.'</p>';
            $html .= '<div style="background-color:#002B45;color:white;font-size:35px;width:600px;line-height:4px;letter-spacing:1.5px;border:1px solid grey;">&nbsp;Property Risk</div>';
            $html .= '<p style="line-height:110%;color:black;">'.$this->risk_summary.'</p>';
            $pdf->writeHTML($html);
            $pdf->lastPage();
            $pdf->addPage();

            //Start new page for Risk Conditions with logo at the top
            $html = '<img width="150px" src="'.$wds_pro_logo.'" />';
            $html .= '<br><img src="/images/blue-hr.png" /><br><br>';
            $pdf->writeHTML($html);
        }

        //Risk Conditions
        $html = '<table><tr>';
        $html .= '<td style="border-left:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;line-height:4px;width:400px;"><b>&nbsp;Risk Condition</b></td>';
        $html .= '<td style="border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;width:110px;"><b>Photo Evidence</b></td>';
        $html .= '</tr>';
        $i=0;
        foreach($this->getOrderedConditionsAndQuestions('condition') as $conditionQuestion)
	    {
            if($conditionQuestion['condition']->response == 0 && $conditionQuestion['question']->type == 'condition') //if yes and is of type='condition'
            {
                $i++;
                if($i !== 1 && (($i%5 == 1 && $this->level > 1) || ($this->level == 1 && ($i == 5 || $i%5 == 0)))) //start of a new set (first one after each 5 or 4th,9th,14th,19th if level 1)
                {
                    $pdf->addPage();
                    //start over new table
                    $html = '<img width="150px" src="'.$wds_pro_logo.'" />';
                    $html .= '<br><img src="/images/blue-hr.png" /><br><br>';
                    $html .= '<table><tr>';
                    $html .= '<td style="border-left:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;line-height:4px;width:400px;"><b>&nbsp;Risk Condition</b></td>';
                    $html .= '<td style="border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;width:110px;"><b>Photo Evidence</b></td>';
                    $html .= '</tr>';
                }
                $html .= '<tr>';
                $html .= '<td style="border-left:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;font-size:27px;">';
                $html .= '<br><br><b>&nbsp;'.$i.'. '.htmlspecialchars($conditionQuestion['question']->title).'</b><br><br>';
                $html .= '<b>&nbsp;RISK: </b>'.htmlspecialchars($conditionQuestion['condition']->risk_text).'<br><br>';
                $html .= '&nbsp;<span style="background-color:#C00000;color:white;font-weight:bold;">ACTION:</span> '.htmlspecialchars($conditionQuestion['condition']->recommendation_text);
                $html .= '</td>';

                if(!empty($conditionQuestion['condition']->submitted_photo_path))
                {
                    $submittedPhotos = $conditionQuestion['condition']->getSubmittedPhotosArray();
                    $photo_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$images_path.$submittedPhotos[0]));
                }
                else
                    $photo_src = $missing_image;
                $html .= '<td style="border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;height:110px;"><span style="line-height:1px;font-size:1px;">&nbsp;</span><img width="100px" height="100px" src="'.$photo_src.'" /></td>';
                $html .= '</tr>';
                //end table every 5 conditions and write it out to prep for new page add if there are more conditions
                //or every condition %5 with 4 remainder when we have level 1 (4, 9, 14, 19, etc.)
                if(($i%5 == 0 && $this->level > 1) || ($this->level == 1 && $i%5 == 4))
                {
                    $html .= "</table>";
                    $pdf->writeHTML($html);
                    $pdf->lastPage();
                }
            }
        }
        if(($i%5 != 0 && $this->level > 1) || ($this->level == 1 && $i%5 != 4)) //only do this if we ended not on a every 5th (because if we did we already wrote the end of the table above)
        {
            $html .= "</table>";
            $pdf->writeHTML($html);
            $pdf->lastPage();
        }

        //finalize and save report
        $outgoing_report_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'report_edu.pdf';
        //remove existing report if there is one
        if(file_exists($outgoing_report_path))
			unlink ($outgoing_report_path);
		//Close and output PDF document
		$pdf->Output($outgoing_report_path, 'F');
    }

    private function createSLEDUPDFReport()
    {
        $property = $this->property;
        if(!isset($property))
            $property = new Property;
        $report_options = $this->getClientReportOptions();
        
        $pdf = new FSPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
        if(!empty($this->pdf_pass))
            $pdf->SetProtection(array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), $this->pdf_pass, null, 0, null);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 18, PDF_MARGIN_RIGHT);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);
        if(!empty($report_options['sl-edu-footer-text']))
            $pdf->custom_footer_text = $report_options['sl-edu-footer-text'];
        $pdf->setPrintFooter(true);


        $pdf->SetTitle("WDSpro Assessment");
        $pdf->SetFont('helvetica', '', 12);

        //front page
        $pdf->AddPage();
        $wds_pro_logo = '/images/WDSpro-with-R-for-web.jpg';
        $missing_image = '/images/missing-image.jpg';
        $foh_image_src = $missing_image;
        $foh_question = FSAssessmentQuestion::model()->findByAttributes(array('set_id'=>$this->getQuestionSetID(), 'client_id' => ($this->type=='sl' || $this->type=='fso') ? $this->user->client_id: $this->fs_user->getClientID(), 'type'=>'foh'));
        $images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        if(isset($foh_question))
        {
            $foh_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$foh_question->question_num, 'set_id'=>$this->getQuestionSetID()));
            if(isset($foh_condition))
            {
                $foh_photos_array = $foh_condition->getSubmittedPhotosArray();
                if(count($foh_photos_array) > 0)
                    $foh_image_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$images_path.$foh_photos_array[0]));
            }
        }
        if(!empty($report_options['sl-edu-logo']))
            $client_logo = $report_options['sl-edu-logo'];
        elseif(!empty($report_options['app2-edu-logo']))
            $client_logo = $report_options['app2-edu-logo'];
        else
            $client_logo = $missing_image;
        $html = '<img width="150px" src="'.$wds_pro_logo.'" />';
        $html .= '<br><img src="/images/blue-hr.png" /><br><br>';
        $html .= '<table><tr>';
        $html .= '<td style="width:155px;"><img width="135px" height="135px" src="'.$foh_image_src.'" /></td>';
        $html .= '<td style="font-size:27px;width:200px;border-top:1px solid grey;border-bottom:1px solid grey;">';
        $html .= (isset($report_options['sl-edu-stamp'])) ? $report_options['sl-edu-stamp'] :'';
        $html .= '</td>';
        $html .= '<td style="text-align:right;font-size:27px;width:150px;border-top:1px solid grey;border-bottom:1px solid grey;"><br><br><img width="100px" src="'.$client_logo.'" /></td>';
        $html .= '</tr></table>';
        $html .= '<span style="color:grey;font-size:30px;"><b>&nbsp;&nbsp;Front of House</b>';
        $html .= '<br>';
        $pdf->writeHTML($html);
        if($this->level == 2)
        {
            $html = '<div style="background-color:#002B45;color:white;font-size:33px;width:600px;line-height:4px;letter-spacing:1.5px;border:1px solid grey;">&nbsp;Wildfire Concerns in the Surrounding Area</div>';
            $html .= '<p style="line-height:110%;color:black;">'.$this->summary.'</p>';
            $html .= '<div style="background-color:#002B45;color:white;font-size:35px;width:600px;line-height:4px;letter-spacing:1.5px;border:1px solid grey;">&nbsp;Property Risk</div>';
            $html .= '<p style="line-height:110%;color:black;">'.$this->risk_summary.'</p>';
            $pdf->writeHTML($html);
            $pdf->lastPage();
            $pdf->addPage();

            //Start new page for Risk Conditions with logo at the top
            $html = '<img width="150px" src="'.$wds_pro_logo.'" />';
            $html .= '<br><img src="/images/blue-hr.png" /><br><br>';
            $pdf->writeHTML($html);
        }

        //Risk Conditions
        $html = '<table><tr>';
        $html .= '<td style="border-left:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;line-height:4px;width:400px;"><b>&nbsp;Risk Condition</b></td>';
        $html .= '<td style="border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;width:110px;"><b>Photo Evidences</b></td>';
        $html .= '</tr>';
        $i=0;
        foreach($this->getOrderedConditionsAndQuestions('condition') as $conditionQuestion)
	    {
            if($conditionQuestion['condition']->response == 0 && $conditionQuestion['question']->type == 'condition') //if yes and is of type='condition'
            {
                $i++;
                if($i !== 1 && (($i%5 == 1 && $this->level > 1) || ($this->level == 1 && ($i == 5 || $i%5 == 0)))) //start of a new set (first one after each 5 or 4th,9th,14th,19th if level 1)
                {
                    $pdf->addPage();
                    //start over new table
                    $html = '<img width="150px" src="'.$wds_pro_logo.'" />';
                    $html .= '<br><img src="/images/blue-hr.png" /><br><br>';
                    $html .= '<table><tr>';
                    $html .= '<td style="border-left:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;line-height:4px;width:400px;"><b>&nbsp;Risk Condition</b></td>';
                    $html .= '<td style="border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;background-color:#C00000;color:white;width:110px;"><b>Photo Evidence</b></td>';
                    $html .= '</tr>';
                }
                $html .= '<tr>';
                $html .= '<td style="border-left:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;font-size:27px;">';
                $html .= '<br><br><b>&nbsp;'.$i.'. '.htmlspecialchars($conditionQuestion['question']->title).'</b><br><br>';
                $html .= '<b>&nbsp;RISK: </b>'.htmlspecialchars($conditionQuestion['condition']->risk_text).'<br><br>';
                $html .= '&nbsp;<span style="background-color:#C00000;color:white;font-weight:bold;">ACTION:</span> '.htmlspecialchars($conditionQuestion['condition']->recommendation_text);
                $html .= '</td>';

                if(!empty($conditionQuestion['condition']->submitted_photo_path))
                {
                    $submittedPhotos = $conditionQuestion['condition']->getSubmittedPhotosArray();
                    $photo_src = Yii::app()->createAbsoluteUrl('site/getJpegImageRotate',array('token'=>'A9er5726rTqncRNC', 'filepath'=>$images_path.$submittedPhotos[0]));
                }
                else
                    $photo_src = $missing_image;
                $html .= '<td style="border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;height:110px;"><span style="line-height:1px;font-size:1px;">&nbsp;</span><img width="100px" height="100px" src="'.$photo_src.'" /></td>';
                $html .= '</tr>';
                //end table every 5 conditions and write it out to prep for new page add if there are more conditions
                //or every condition %5 with 4 remainder when we have level 1 (4, 9, 14, 19, etc.)
                if(($i%5 == 0 && $this->level > 1) || ($this->level == 1 && $i%5 == 4))
                {
                    $html .= "</table>";
                    $pdf->writeHTML($html);
                    $pdf->lastPage();
                }
            }
        }
        if(($i%5 != 0 && $this->level > 1) || ($this->level == 1 && $i%5 != 4)) //only do this if we ended not on a every 5th (because if we did we already wrote the end of the table above)
        {
            $html .= "</table>";
            $pdf->writeHTML($html);
            $pdf->lastPage();
        }

        //finalize and save report
        $outgoing_report_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'report_edu.pdf';
        //remove existing report if there is one
        if(file_exists($outgoing_report_path))
			unlink ($outgoing_report_path);
		//Close and output PDF document
		$pdf->Output($outgoing_report_path, 'F');
    }

	public function createAgentPDFReport()
	{
		//move images to temp dir
		$pdf = new FSPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);

		//set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 18, PDF_MARGIN_RIGHT);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

        $incoming_images_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;

        //HTML
		//Front page
        if($this->type == 'edu-b')
        {
            $report_options = $this->getClientReportOptions();

            $front_of_house_image_url = 'https://pro.wildfire-defense.com/images/logo.jpg';
            $front_of_house_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$this->client->photos_question_num));
            if(isset($front_of_house_condition))
            {
                $photos = $front_of_house_condition->getSubmittedPhotosArray();
                if(count($photos)>0)
                    $front_of_house_image_url = Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>$incoming_images_path.$photos[0]));
            }

            $pdf->SetTitle("Hazard Assessment");
            $pdf->SetFont('helvetica', '', 12);
            $pdf->AddPage();

            //front page table
            $html = '<style>
				table.front-page {
					width: 525px;
				}
                td.front-page-header {
                    border-bottom:1px solid orange;
                    border-top:1px solid #F17E21;
                    padding-top:15px;
                    color: #F17E21;
                    width:500px;
                    font-size:75px;
                    text-align:center;
                }
				</style>';
            $html .= '<table class="front-page">';
            $html .= '  <tr>';
            $html .= '    <td><img style="height:100px;width:320px;" src="' . Yii::app()->getBaseUrl(true) .'/images/WDSpro-with-R-for-web.jpg' . '" /></td>';
            $html .= '    <td style="text-align:center;">&nbsp;<br>'.$report_options['edu-b-top-right'].'</td>';
            $html .= '  </tr>';
            $html .= '  <tr><td colspan="2">&nbsp;<br></td></tr>';
            $html .= '  <tr><td class="front-page-header" colspan="2">'.$report_options['edu-b-header'].'</td></tr>';
            $html .= '  <tr><td colspan="2">&nbsp;<br></td></tr>';
            $html .= '  <tr><td style="text-align:center;" colspan="2">'.$report_options['edu-b-mem-prop-info'].'</td></tr>';
            $html .= '  <tr><td colspan="2">&nbsp;<br></td></tr>';
            $html .= '  <tr><td style="text-align:center;" colspan="2">'.$report_options['edu-b-inspection-info'].'</td></tr>';
            $html .= '  <tr><td colspan="2">&nbsp;<br></td></tr>';
            $html .= '  <tr><td style="text-align:center;" colspan="2"><img width="300px" height="250px" src="'.$front_of_house_image_url.'"><br></td></tr>';
            $html .= '  <tr><td colspan="2">&nbsp;<br></td></tr>';
            $html .= '  <tr><td style="text-align:center;" colspan="2">'.$report_options['edu-b-bottom-blurb'].'</td></tr>';
            $html .= '</table>';
            $pdf->writeHTML($html);
            $pdf->lastPage();
            $pdf->addPage();

        }
        else //Front page for type == 'edu' or 'uw'
        {
            $pdf->SetTitle("Hazard Assessment");
            $pdf->SetFont('helvetica', '', 12);
            $pdf->AddPage();
            $html = '';
            $logo_url = Yii::app()->getBaseUrl(true) .'/images/WDSpro-with-R-for-web.jpg';
            $file_headers = @get_headers($logo_url);
            if (isset($file_headers[0]) && $file_headers[0] === 'HTTP/1.1 200 OK')
            {
                if(!empty($this->client->report_logo_url))
                    $logo_url = $this->client->report_logo_url;
                $html = '<img src="'.$logo_url.'" />';
                $pdf->writeHTML($html, true, false, true, false, 'C');

                $html = '<span style="font-color: #3399ff; font-size: 12pt;"><b>Wildfire Defense Systems - Wildfire Hazard Assessment</b></span><br>';
                $pdf->writeHTML($html, true, false, true, false, 'C');

                //top table of report
                $front_of_house_image_url = Yii::app()->getBaseUrl(true) . '/images/logo.jpg';
                $front_of_house_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$this->client->photos_question_num));
                if(isset($front_of_house_condition))
                {
                    $photos = $front_of_house_condition->getSubmittedPhotosArray();
                    if(count($photos)>0)
                        $front_of_house_image_url = Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>$incoming_images_path.$photos[0]));
                }
                $html = '<style>
				    table.front-page {
					    border-collapse:collapse;
					    border: 1px solid black;
					    width: 700px;
					    color: black;
					    font-size: 28px;
				    }
				    table.front-page td {
					    text-align:left;
				    }
				    table.front-page-inner {

				    }
				    </style>';
                $html .= '<table class="front-page"><tr>';
                $html .= '<td width="25%" style="text-align:center;border-right: 1px solid black;"><br><br><img width="125px" height="125px" src="'.$front_of_house_image_url.'"><br><strong>Front of House</strong></td>';
                $html .= '<td>';
                $agent_name = $this->agent_property->agent->first_name . ' ' . $this->agent_property->agent->last_name;
                $html .= '<table width="100%;" style="border-spacing: 0px;border-collapse: separate;"><tr><td height="75px" style="border-right: 1px solid black;border-bottom: 1px solid black;"><br><strong>Agent Name:</strong> '.$agent_name.'<br><strong>Policy Number:</strong> '.$this->agent_property->work_order_num.'<br><strong>Inspection Date:</strong> '.date('F d, Y', strtotime($this->start_date)).'</td>';
                $html .= '<td style="border-bottom: 1px solid black;"><br><strong>Property Address:</strong><br> '.$this->agent_property->address_line_1.'<br>'.$this->agent_property->city.', '.$this->agent_property->state.' '.$this->agent_property->zip.'<br><strong>Lat:</strong> '.round($this->agent_property->lat,4).' <br><strong>Long:</strong> '.round($this->agent_property->long,4).'</td></tr>';

                if($this->type == 'uw')
                    $report_stamp_2 = '<b>Report Type: UNDERWRITING</b><br>'.$this->client->report_stamp_2;
                elseif($this->type == 'edu')
                    $report_stamp_2 = '<b>Report Type: EDUCATIONAL</b><br>'.$this->client->report_stamp_2;
                else
                    $report_stamp_2 = $this->client->report_stamp_2;

                $html .= '<tr><td height="75px" style="border-right: 1px solid black">'.$this->client->report_stamp_1.'</td><td>'.$report_stamp_2.'</td></tr></table>';
                $html .= '</td></tr>';
                $html .= '</table>';
                $pdf->writeHTML($html, true, false, true, false, 'C');

                //outer table to hold both condition summary and score summary tables
                $html = '<table style="width:640px"><tbody>';
                $html .= '<tr><td>'; //start left cell with summary table

                //By Condition Summary table
                $html .= '<table style="border-collapse:collapse;font-size:28px;text-align:left;border:1px solid black;width:300px"><tbody>'; //525px total
                $html .= '<tr><td colspan="2" style="background-color:#1F497D;color:white;font-weight:bold;height:18px;font-size:31px;text-align:center;">By Condition Summary</td></tr>';
                $html .= '<tr><td colspan="2"><strong>Wildfire Hazard Conditions:</strong></td>';
                //$html .= '<td style="width:40%;"><strong>Threats Not Present:</strong></td>';
                $html .= '</tr>';

                foreach($this->getOrderedConditions() as $condition)
                {
                    $question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $condition->condition_num, 'client_id' => $this->client->id));
                    if(isset($question) && isset($question->type) && $question->type == 'normal') //skip if you can't find a question in the client, and the question needs to be of normal type
                    {
                        if($condition->response == 0 && $this->client->photos_question_num != $question->question_num) //yes response
                        {
                            if($this->type == 'edu' || (isset($this->no_scoring) && $this->no_scoring == 1))
                            {
                                $html .= '<tr><td>'.$question->title.'</td></tr>';
                            }
                            elseif($this->type == 'uw')
                            {
                                $html .= '<tr><td>'.$question->title.'</td><td>'.$question->yes_points.' pts</td></tr>';
                            }
                        }
                    }
                }
                $html .= '</tbody></table></td>';

                //Vulnerability Score Summary table
                $html .= '<td>'; //right cell of outer table
                if($this->type == 'uw') //only show this in UW reports
                    $html .= $this->client->losPDFHTMLTable($this);

                $html .= '</td></tr></tbody></table>';
                $pdf->writeHTML($html);
            }
            $pdf->lastPage();
            $pdf->addPage();
        } //end front page for 'edu' or 'uw'

		//Risk Analysis Summary table
		//only show if Total Risk Level >= Clients FRA Report Threshold
		if($this->risk_level >= $this->client->fra_report_threshold && !empty($this->summary))
		{
			$html = '<table style="width:525px"><tbody>';
			$html .= '<tr><td style="background-color:#1F497D;color:white;font-weight:bold;height:18px;font-size:31px;text-align:center;">Risk Analysis Summary</td></tr>';
			$html .= '<tr><td style="font-size:28px">'.$this->summary.'</td></tr></tbody></table>';
			$pdf->writeHTML($html);
			$pdf->lastPage();
			$pdf->addPage();
		}

		//Condition Detail section
		$html = '<table style="width:525px"><tbody>';
		$html .= '<tr><td style="background-color:#E5E5E5;font-weight:bold;height:18px;font-size:31px;text-align:center;">Condition Detail</td></tr></tbody></table>';
		$pdf->writeHTML($html);
		foreach($this->getOrderedConditions() as $condition)
		{
			$question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $condition->condition_num, 'client_id' => $this->client->id));
			if(isset($question) && $condition->response == 0 && $this->client->photos_question_num != $question->question_num && isset($question->type) && $question->type == 'normal') //yes response, or skip if question doesn't exist or if its not a normal type
			{
				$html = '<table id="'.$condition->condition_num.'" style="width:525px"><tbody>';
				if($this->type == 'edu' || $this->type == 'edu-b' || (isset($this->no_scoring) && $this->no_scoring == 1))
				{
					$html .= '<tr><td style="width:100%;background-color:#1F497D;color:white;font-weight:bold;height:18px;font-size:31px;text-align:center;">'.$question->title.'</td></tr>';
                    $colspan = 1;
				}
				elseif($this->type == 'uw')
				{
					$html .= '<tr><td style="width:90%;background-color:#1F497D;color:white;font-weight:bold;height:18px;font-size:31px;text-align:center;">'.$question->title.'</td>';
					$html .= '<td style="width:10%;background-color:yellow;text-align:center">'.$question->yes_points.' pts</td></tr>';
                    $colspan = 2;
				}

				$html .= '<tr><td colspan="'.$colspan.'" style="font-size:28px;">'.htmlspecialchars($question->description).'<br></td></tr>';

				$html .= '<tr><td colspan="'.$colspan.'">'; //photos
				$photos = $condition->getSubmittedPhotosArray();
				$photo_index = 0;
				foreach($photos as $photo)
				{
					if($photo_index%3 == 0) //3 pics per line
						$html .= '<br><br>';
					$photo_src = Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>$incoming_images_path.$photos[$photo_index]));
					$html .= (string)($photo_index+1).' <img style="width:135px;height:125px;" src="'.$photo_src.'"> &nbsp;&nbsp;&nbsp;';
					$photo_index++;
				}
				$html .= '</td></tr>'; //end photos

                //notes
				if(isset($condition->notes) && !empty($condition->notes) && $this->type == 'uw')
					$html .= '<tr><td colspan="'.$colspan.'" style="font-size:28px;"><b><br>Assessor Notes: </b>'.htmlspecialchars($condition->notes).'</td></tr>';

                //risk text
                $risk_text = $question->risk_text; //default (canned text from the client level)
                if(!empty($condition->risk_text)) //check if filled in on this specific report
                    $risk_text = $condition->risk_text;
                if(isset($risk_text))
                    $html .= '<tr><td colspan="'.$colspan.'" style="font-size:28px;"><b><br>Risk: </b>'.htmlspecialchars($risk_text).'</td></tr>';

                //recommendation (Action) text
                $rec_text = $question->rec_text; //default (canned text from the client level)
                if(!empty($condition->recommendation_text)) //check if filled in on this specific report
                    $rec_text = $condition->recommendation_text;
                if(isset($rec_text))
                    $html .= '<tr><td colspan="'.$colspan.'" style="font-size:28px;"><b><br>Action: </b>'.htmlspecialchars($rec_text).'</td></tr>';

                //example text/photo
                $ex_text = $question->example_text;
                if(!empty($condition->example_text))
                    $ex_text = $condition->example_text;
                $ex_image_file_id = $question->example_image_file_id;
                if(!empty($condition->example_image_file_id))
                    $ex_image_file_id = $condition->example_image_file_id;

                if(isset($ex_text) && !empty($ex_text) && !empty($ex_image_file_id))
                {
                    $ex_photo_src = Yii::app()->createAbsoluteUrl('file/loadFileToken',array('id'=>$ex_image_file_id, 'token'=>'WildFireDefenseSystems2014'));
                    $html .= '<tr><td><br><br><br><br>';
                    $html .= '<table><tr><td><img style="width:200px;height:200px;" src="'.$ex_photo_src.'"></td>';
                    $html .= '<td style="font-size:28px;"><b><br><br>Example: </b>'.htmlspecialchars($ex_text).'</td></tr></table>';
                    $html .= '</td></tr>';
                }

				$html .= '</tbody></table>';
				$pdf->writeHTML($html);
				$pdf->lastPage();
				$pdf->addPage();
			}
		}

		//Additional Photos table
		$html = '<table style="width:525px"><tbody>';
		$html .= '<tr><td style="background-color:#E5E5E5;font-weight:bold;height:18px;font-size:31px;text-align:center;">Additional Photos</td></tr>';
		$html .= '<tr><td>'; //photos
		$condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$this->client->photos_question_num));
		if(isset($condition))
		{
			$photos = $condition->getSubmittedPhotosArray();
			$photo_index = 0;
			foreach($photos as $photo)
			{
				if($photo_index % 3 == 0)
					$html .= '<br><br>';
				$photo_src = Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>$incoming_images_path.$photos[$photo_index]));
				$html .= (string)($photo_index+1).' <img style="width:135px;height:125px;" src="'.$photo_src.'"> &nbsp;&nbsp;&nbsp;';
				$photo_index++;
			}
		}
		$html .= '</td></tr>'; //end photos
		$html .= '</tbody></table>';
		$pdf->writeHTML($html);

		//Last page
		$pdf->lastPage();
		$pdf->addPage();
		$pdf->writeHTML('<br><br><br><br><br><br><br><br><br><br><br><br><p style="font-size:28px">The homeowner is encouraged to abide by all State fire codes pertaining to the Wildland Urban Interface. For additional information regarding fire codes in your specific community, contact your local fire department.</p>', true, false, true, false, 'C');
		$pdf->writeHTML('<br /><p style="font-size:28px">For inquiries regarding this report or the materials/suggestions herin, please feel free to contact:<br><b><span style="font-size:32px"> Wildfire Defense Systems, Inc.<br> (877) 323-4730  ha@wildfire-defense.com</b></span></p>', true, false, true, false, 'C');
		$pdf->writeHTML('<br /><p style="font-size:28px">The components of this assessment are derived from the research of the Insurance Institute for Business and Home Safety, the National Fire Protection Association\'s©<br><br>www.disastersafety.org<br><br>www.nfpa.org www.firewise.org<br><br>www.firewise.org</p>', true, false, true, false, 'C');
        $pdf->writeHTML('<br /><p style="font-size:28px">There are no guarantees that mitigation steps taken as a result of this report will prevent damage. Wildfire Defense Systems, Inc. nor their representatives take responsibility for personal injury or property damage arising out of reliance on the wildfire mitigation recommendations or this assessment report.</p>', true, false, true, false, 'C');
		$pdf->writeHTML('<br /><p style="font-size:28px">The evaluations, reports and recommendations regarding changes that should be considered to help protect your property are designed to provide additional protection in the event of wildfire. Even if every recommended step is taken, your property could still be destroyed because wildfire is unpredictable and can be impossible to stop or control, no matter what mitigation efforts have been undertaken. WDS does not represent or warrant that taking the steps suggested can or will protect your property from destruction by fire. No warranties or representations of any kind are provided to the recipient of this evaluation.</p>', true, false, true, false, 'C');

        $reportPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid.DIRECTORY_SEPARATOR.'report.pdf';
		if(file_exists($reportPath))
		{
			@unlink ($reportPath);
		}

        //Close and output PDF document
        $pdf->Output($reportPath, 'F');
	}

	//import an uploaded report. return false if successful (means no errors), else return a string with the error
	public function import($report_guid)
	{
		try
		{
            $reportsPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR;

            //unzip uploaded report
			$zip = new ZipArchive();

            if ($zip->open($reportsPath.'/'.$report_guid.'.zip') !== true)
			{
				return 'ERROR: Could not unzip uploaded assessment';
            }

            $unzip_result = $zip->extractTo($reportsPath.$report_guid."/");
            if(!$unzip_result)
                return 'ERROR: Could not unzip uploaded assessment';
            $zip->close();

            //parse report
            if(!is_file($reportsPath.$report_guid.'/payload.json'))
                return 'ERROR: no payload.json in uploaded assessment zip';
            $report = json_decode(file_get_contents($reportsPath.$report_guid.'/payload.json'), true);

            //make sure all required fields are in the json payload
            if(!isset($report['startDate'], $report['endDate'], $report['version'], $report['address']['addressLine1'], $report['address']['city'], $report['address']['state'], $report['address']['zip'], $report['address']['gps']['longitude'], $report['address']['gps']['latitude'], $report['responses'], $report['loginToken']))
                return "ERROR: Not all the required fields were in the payload.json file, please check documentation for proper json structure and fields";

            $fsUser = FSUser::model()->find("login_token = '".$report['loginToken']."'");
            if(!isset($fsUser))
                return "ERROR: Could not find property based on passed in loginToken and address-zip";

            $property = $fsUser->getProperty($report['address']['addressLine1'], $report['address']['zip']);
            if(!isset($property))
                return "ERROR: Could not find property based on passed in loginToken and address-zip";

            $isAgentProperty = isset($property->agent_id);

            if (!$isAgentProperty && $property->fs_assessments_allowed < 1)
                return "ERROR: No more FireShield Assessments are allowed for this property";

            $fsReport = new FSReport;
            $fsReport->report_guid = $report_guid;

            if (!$isAgentProperty)
            {
                $fsReport->property_pid = $property->pid;
                $fsReport->member_mid = $property->member_mid;
				$fsReport->type = 'fs';
            }
            else //agent report
            {
                $fsReport->agent_property_id = $property->id;
				$fsReport->type = 'uw';
				if(isset($property->agent->client->report_type))
					$fsReport->type = $property->agent->client->report_type;

				$fsReport->no_scoring = 0;
				if(isset($property->agent->client->no_scoring))
					$fsReport->no_scoring = $property->agent->client->no_scoring;

                if(isset($property->work_order_num))
                {
                    if(strpos($property->work_order_num, 'PRID:') !== false)
                    {
                        $pre_risk_id = trim(str_replace('PRID:', '', $property->work_order_num));
                        $fsReport->pre_risk_id = intval($pre_risk_id);
                    }
                    elseif(strpos($property->work_order_num, 'PID:') !== false)
                    {
                        $prop_id = trim(str_replace('PID:', '', $property->work_order_num));
                        $fsReport->property_pid = intval($prop_id);
                    }
                }
            }

            $fsReport->start_date = date('Y-m-d H:i:s', $report['startDate']); //comes in as unix timestamp, need to convert it for MSSQL datetime type
            $fsReport->end_date = date('Y-m-d H:i:s', $report['endDate']);
            $fsReport->version = $report['version'];
            $fsReport->address_line_1 = $report['address']['addressLine1'];
            $fsReport->city = $report['address']['city'];
            $fsReport->state = $report['address']['state'];
            $fsReport->zip = $report['address']['zip'];
            $fsReport->longitude = $report['address']['gps']['longitude'];
            $fsReport->latitude = $report['address']['gps']['latitude'];
            $fsReport->status = 'New';
            $fsReport->status_date = date('Y-m-d H:i:s');
            $fsReport->submit_date = date('Y-m-d H:i:s');
            $fsReport->show_geo_risk = 0;
            $fsReport->show_los_risk = 0;
            $fsReport->show_site_risk = 1;
            $fsReport->save(); //save here cause we will need the report id to tie to the conditions

			$fsReport = FSReport::model()->with('agent_property', 'agent', 'client')->findByPk($fsReport->id);

            //go through each condition and save into fs_condition db table that is related to the fs_report
            foreach($report['responses'] as $condition)
            {
                if(!isset($condition['questionID'], $condition['responseType'], $condition['media']))
                    return "ERROR: Not all the required fields were in the payload.json file (conditions section), please check documentation for proper json structure and fields";

                $fsCondition = new FSCondition;
                $fsCondition->fs_report_id = $fsReport->id;
                $fsCondition->condition_num = $condition['questionID'];
                $fsCondition->response = $condition['responseType'];

                // Attach notes if they were provided.
                if (!empty($condition['notes']))
                {
                    $fsCondition->notes = $condition['notes'];
                }

                //parse condition photos
                if(!empty($condition['media']))
                {
                    $fsCondition->pic_to_use = 1; //default to first picture
                    foreach ($condition['media'] as $media)
                    {
                        //check if this imageName is already in the photo path string; this is a fix to avoid the duplicates coming in by error from the app payload
                        if(!in_array($media['imageName'], explode('|',$fsCondition->submitted_photo_path)))
                        {
                            //check if the image actually exists in the image folder; this is a fix to avoid bad/missing images being sent in by error in the app payload
                            if(file_exists($reportsPath.$report_guid.'/Images/'.$media['imageName']))
                                $fsCondition->submitted_photo_path .= $media['imageName'] . "|";
                        }

                    }
                    $fsCondition->submitted_photo_path = rtrim($fsCondition->submitted_photo_path, '|');
                }
                $fsCondition->save();
            }

            //put default files (ex photos, css, js) into outgoing folder

            $src = Helper::getDataStorePath() . 'fs_reports' . DIRECTORY_SEPARATOR . 'outgoing' . DIRECTORY_SEPARATOR . 'default';
            $dst = Helper::getDataStorePath() . 'fs_reports' . DIRECTORY_SEPARATOR . 'outgoing' . DIRECTORY_SEPARATOR . $report_guid;

            CFileHelper::copyDirectory($src, $dst);

			$fsReport->condition_risk = $fsReport->calcConditionRisk();
			$fsReport->calcRiskLevel(true);
			$fsReport->createKMLFile();

			if ($fsReport->type == 'fs')
            {
				$fsReport->risk_level = round($fsReport->risk_level);

				foreach($fsReport->conditions as $condition)
				{
					$condition->createHTMLTemplate();
				}
				$fsReport->createSummaryHTMLTemplate();
                // Tell the report to display USAA references based on the member's client type.
                $showUSAATextInReport = isset($fsReport->member) && $fsReport->member->client == 'USAA';
                $fsReport->createPDFReport(false, $showUSAATextInReport);
                $fsReport->createJSONFile();
                $fsReport->zipReport();

                $property->fs_assessments_allowed = (int)$property->fs_assessments_allowed - 1;
                $property->fireshield_status = 'enrolled';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->save(false, array('fireshield_status', 'fs_status_date', 'fs_assessments_allowed'));
            }
            else //agent report type ('uw' or 'edu' or 'edu-b')
            {
                $fsReport->createAgentPDFReport();
            }

            return false; //return false on success meaning no errors
		}
		catch(Exception $e)
		{
			return 'Error: Error parsing uploaded report. Check documentation to make sure json and directory structure are correct. Details: '.$e->getMessage();
		}
	}

    //import an uploaded report. return null if successful (means no errors), else return a string with the error
	public function import2()
	{
        if(!isset($this->report_guid, $this->fs_user_id))
        {
            return 'ERROR: This function requires you set the report_guid and fs_user_id of the model first.';
        }

		try
		{
            $reportsPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR;

            //unzip uploaded report
			$zip = new ZipArchive();
            if ($zip->open($reportsPath.'/'.$this->report_guid.'.zip') !== true)
			{
				return 'ERROR: Could not open uploaded zipped assessment'.$reportsPath.$this->report_guid;
            }

            $unzipResult = $zip->extractTo($reportsPath.$this->report_guid."/");
            if(!$unzipResult)
                return 'ERROR: Could not unzip uploaded assessment';
            $zip->close();

            //parse report
            if(!is_file($reportsPath.$this->report_guid.'/payload.json'))
                return 'ERROR: no payload.json in uploaded assessment zip';
            $report = json_decode(file_get_contents($reportsPath.$this->report_guid.'/payload.json'), true);

            //make sure all required fields are in the json payload
            if(!isset($report['startDate'], $report['endDate'], $report['version'], $report['propertyID'], $report['address']['addressLine1'], $report['address']['city'], $report['address']['state'], $report['address']['zip'], $report['address']['gps']['longitude'], $report['address']['gps']['latitude'], $report['responses']))
                return "ERROR: Not all the required fields were in the payload.json file, please check documentation for proper json structure and fields";
            if($this->type == '2.0')
            {
                $fsUser = FSUser::model()->findByPk($this->fs_user_id);
                if(!isset($fsUser))
                {
                    $user = User::model()->findByPk($this->fs_user_id);
                    if(!isset($user))
                    {
                        return "ERROR: Could not find FS User/User based on fs_user_id";
                    }
                
                }
                if(isset($fsUser))
                {
                   $this->type = '2.0'; 
                }
                if(isset($fsUser))
                {
                    if($fsUser->isAgentUser())
                    {
                        $property = AgentProperty::model()->findByPk($report['propertyID']);
                        if(!isset($property))
                            return "ERROR: Could not find property for the passed in propertyID";
                        elseif($property->agent_id !== $fsUser->agent_id)
                            return "ERROR: PropertyID did not belong to User Agent.";
                    }
                    else
                    {
                        $property = Property::model()->findByPk($report['propertyID']);
                        if(!isset($property))
                            return "ERROR: Could not find property for the passed in propertyID";
                        elseif($property->member_mid !== $fsUser->member_mid)
                            return "ERROR: PropertyID did not belong to User Member.";
                    }

                    $isAgentProperty = isset($property->agent_id);

                    if (!$isAgentProperty)
                    {
                        $this->property_pid = $property->pid;
                        $this->member_mid = $property->member_mid;
                        $client = $property->member->client;
                    }
                    else //agent property
                    {
                        $this->agent_property_id = $property->id;
                        $client = $property->agent->client;

                        //agent properties now get created along with a related main property that we can link up
                        $this->property_pid = $property->property_pid;
                    }
                }
            }
            else
            {
                $user = User::model()->findByPk($this->fs_user_id);
                if(!isset($user))
                {
                    return "ERROR: Could not find FS User/User based on fs_user_id";
                }
            }
            if(isset($user))
            {
               if($user->type=='Second Look')
               {
                    $this->type = 'sl'; 
               }
               else
               {
                   $this->type = 'fso';
               }
            }
            
            if(isset($user))
            {
                $property = Property::model()->findByPk($report['propertyID']);
                    if(!isset($property))
                        return "ERROR: Could not find property for the passed in propertyID";
                $this->property_pid = $property->pid;
                $client = $property->client;
            }
            //$this->type = '2.0'; //all reports coming in to this import funciton should be type 2.0

            //set the client default app download types
            if(!empty($client->fs_default_download_types))
				$this->download_types = $client->fs_default_download_types;

            //set the client default email download types
            if(!empty($client->fs_default_email_download_types))
				$this->email_download_types = $client->fs_default_email_download_types;

            //set the no_scoring variable based on client default
			$this->no_scoring = 0;
			if(isset($property->agent->client->no_scoring))
				$this->no_scoring = $property->agent->client->no_scoring;

            $this->start_date = date('Y-m-d H:i:s', $report['startDate']); //comes in as unix timestamp, need to convert it for MSSQL datetime type
            $this->end_date = date('Y-m-d H:i:s', $report['endDate']);
            $this->version = $report['version'];
            $this->address_line_1 = $report['address']['addressLine1'];
            $this->city = $report['address']['city'];
            $this->state = $report['address']['state'];
            $this->zip = substr($report['address']['zip'], 0, 10);
            $this->longitude = substr($report['address']['gps']['longitude'],0,15);
            $this->latitude = substr($report['address']['gps']['latitude'],0,15);
            $this->status = 'New';
            $this->status_date = date('Y-m-d H:i:s');
            $this->submit_date = date('Y-m-d H:i:s');
            $this->show_geo_risk = 0;
            $this->show_los_risk = 0;
            $this->show_site_risk = 0;
            $this->notes = '';
            $debug = '';
            $autoTriggeredQuestions = array();
            //go through each field response and save into fs_condition db table that is related to the fs_report
            foreach($report['responses'] as $condition)
            {
                if(!isset($condition['questionNumber'], $condition['selectedChoices'], $condition['media'], $condition['questionDescription'], $condition['questionText']))
                    return "ERROR: Not all the required fields were in the payload.json file (conditions section), please check documentation for proper json structure and fields";

                $fsCondition = new FSCondition;
                $fsCondition->fs_report_id = $this->id;
                $fsCondition->condition_num = $condition['questionNumber'];
                if(!empty($condition['setID']))
                    $fsCondition->set_id = $condition['setID'];
                else
                {
                    $default_client_set = ClientAppQuestionSet::model()->findByAttributes(array('is_default'=>1, 'client_id'=>$client->id, 'active'=>1));
                    $fsCondition->set_id = $default_client_set->id; //default
                }

                $set_id = $fsCondition->set_id; //need this for down in condition creation

                $fsCondition->question_text = $condition['questionDescription'].' '.$condition['questionText'];
                $fsCondition->selected_choices = json_encode($condition['selectedChoices']);
                $fsCondition->notes = $condition['notes'];

                //parse condition photos
                if(!empty($condition['media']))
                {
                    $fsCondition->pic_to_use = 1; //default to first picture
                    foreach ($condition['media'] as $media)
                    {
                        //check if this imageName is already in the photo path string; this is a fix to avoid the duplicates coming in by error from the app payload
                        if(!in_array($media['imageName'], explode('|',$fsCondition->submitted_photo_path)))
                        {
                            //check if the image actually exists in the image folder; this is a fix to avoid bad/missing images being sent in by error in the app payload
                            if(file_exists($reportsPath.$this->report_guid.'/Images/'.$media['imageName']))
                                $fsCondition->submitted_photo_path .= $media['imageName'] . "|";
                        }

                    }
                    $fsCondition->submitted_photo_path = rtrim($fsCondition->submitted_photo_path, '|');
                }

                //check for auto triggers
                $fsQuestion = FSAssessmentQuestion::model()->findByAttributes(array('set_id'=>$fsCondition->set_id, 'question_num'=>$fsCondition->condition_num, 'client_id'=>$client->id));
                if(isset($fsQuestion))
                {
                    $choicesArray = json_decode($fsQuestion->choices, true);
                    $autoTriggerIDs = array();
                    if(isset($choicesArray['choices']))
                    {
                        foreach($choicesArray['choices'] as $choice)
                        {
                            if(isset($choice['autotrigger'], $choice['autofill_question_id']) && $choice['autotrigger'] == true)
                                $autoTriggerIDs[$choice['value']] = $choice['autofill_question_id'];
                        }
                    }

                    foreach($condition['selectedChoices'] as $selectedChoice)
                    {
                        if(in_array($selectedChoice['value'], array_keys($autoTriggerIDs)))
                        {
                            $fsAutoFillQuestion = FSAssessmentQuestion::model()->findByAttributes(array('id'=>$autoTriggerIDs[$selectedChoice['value']], 'client_id'=>$client->id));
                            if(isset($fsAutoFillQuestion))
                            {
                                $autoTriggeredQuestions[] = array('autofill_question_id'=>$fsAutoFillQuestion->id,
                                    'triggering_question_id'=>$fsQuestion->id,
                                    'triggering_condition_id'=>$fsCondition->id,
                                    'triggering_condition_num'=>$fsCondition->condition_num,
                                    'triggering_photo_path'=>$fsCondition->submitted_photo_path,
                                    'autofill_question_num'=>$fsAutoFillQuestion->question_num,
                                    'autofill_set_id'=>$fsAutoFillQuestion->set_id,
                                );
                            }
                        }
                    }
                    $debug .= "AutoTriggeredQuestions Dump: ".var_export($autoTriggeredQuestions, true);

                }

                if(!$fsCondition->save())
                    return "ERROR: Could not save responses for question_num: ".$condition['questionNumber'];
            }

            //set default level based on question set default setting
            $question_set = ClientAppQuestionSet::model()->findByPk($set_id);
            $this->level = $question_set->default_level;

            //add all 'condition' type questions as response conditions to this report so that they can be used in updating it (includes FOH condition if there is one for this client as well as any autotrigger fills)
            $this->createConditions($set_id, $client->id, $autoTriggeredQuestions);

            //create outgoing folder
            mkdir(Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report_guid);

            if($this->save())
                return null; //return null on success meaning no errors
            else
                return "ERROR: could not save report at end of import process. Details: ".var_export($this->getErrors(), true);
		}
		catch(Exception $e)
		{
			return 'Error: Error importing uploaded report. Check documentation to make sure json and directory structure are correct. Details: '.$e->getMessage();
		}
	}

	public function getStatuses()
	{
		return array(
            'Importing'=>'Importing',
            'New'=> 'New',
            'FRA Ready'=>'FRA Ready',
            'FRA'=> 'FRA',
            'Reviser Ready'=>'Reviser Ready',
            'Reviser'=>'Reviser',
            'Edit Ready'=>'Edit Ready',
            'Editor' => 'Editor',
            'Holding' => 'Holding',
            'Completed'=> 'Completed',
            'Mailed' => 'Mailed',
            'Call Scheduled' => 'Call Scheduled',
            'Call Completed' => 'Call Completed',
            'Other' => 'Other',
            'Error' => 'Error',
            'Canceled' => 'Canceled',
        );
	}

    public function getTypes()
    {
        return array(
            'fs' => 'Fireshield',
            'uw' => 'Underwriter',
            'edu' => 'Educational',
            'edu-b' => 'Educational-B',
            '2.0' => 'App 2.0',
            'sl' => 'Second Look',
            'fso' => 'FS Offered'
        );
    }

    public function getTypeLabel()
    {
        $types = $this->getTypes();
        return (isset($types[$this->type]) ? $types[$this->type] : '');
    }

    /**
     * Sets the assigned user name by looking up the name from the assigned user ID.
     */
    //    public function refreshAssignedUserName()
    //    {
    //        $this->assigned_user_name = 'Unassigned';
    //
    //        if (isset($this->assigned_user_id))
    //        {
    //            $assignedUsername = User::model()->findByAttributes(array('id' => $this->assigned_user_id))->name;
    //
    //            if (!empty($assignedUsername))
    //                $this->assigned_user_name = $assignedUsername;
    //        }
    //    }

    protected function afterFind()
    {
        // Convert the date/time fields to display format.
        $format = 'm/d/Y h:i A';

		if(isset($this->scheduled_call))
			$this->scheduled_call = date_format(new DateTime($this->scheduled_call), $format);
		if(isset($this->due_date))
			$this->due_date = date_format(new DateTime($this->due_date), $format);
		if(isset($this->status_date))
			$this->status_date = date_format(new DateTime($this->status_date), $format);
        if(isset($this->submit_date))
			$this->submit_date = date_format(new DateTime($this->submit_date), $format);

        // Set the assigned user name by looking up the name from the assigned user ID.
        //$this->refreshAssignedUserName();

        parent::afterFind();
    }

    protected function beforeSave()
	{
        // If the report is in a "working" status, then assign the logged in user.
        if ($this->status == "FRA" || $this->status == "Editor" || $this->status == "Reviser")
            $this->assigned_user_id = Yii::app()->user->id;


        // if the PreRisk ID is set then set the PID as well according to the prop attached to the pr entry
        if(isset($this->pre_risk_id))
        {
            $pre_risk = PreRisk::model()->findByPk($this->pre_risk_id);
            if(isset($pre_risk))
                $this->property_pid = $pre_risk->property_pid;
        }

        // Populate the due date for new records to five business days from the submit date.
        if ($this->isNewRecord)
        {
            if(isset($this->agent_property_id))
                $agent_prop = AgentProperty::model()->with('agent')->findByPk($this->agent_property_id);

            if(isset($agent_prop) && ($agent_prop->agent->client_id == 1000 || $agent_prop->agent->client_id == 13)) //if assessor client (13 on pro, 1000 on dev)
                $this->due_date = date('Y-m-d', strtotime('+30 days', strtotime($this->submit_date))).' '.date('H:i:s', strtotime($this->submit_date));
            elseif($this->type=='sl') //for second look, due date 2 business day prior to submitted dated
            {
                $this->due_date = Helper::addWeekDays($this->submit_date, 2);
            }
            else
            {
                //There is a bug in PHP that causes the following sunday to be returned sometimes when using +5 weekdays functionality (https://bugs.php.net/bug.php?id=63521), going to use the work around function instead
                //$this->due_date = date('Y-m-d', strtotime('+5 weekdays', strtotime($this->submit_date))) . ' ' . date('H:i:s', strtotime($this->submit_date));
                $this->due_date = Helper::addWeekDays($this->submit_date, 5);
            }
        }

        // If the report status has changed, push its value onto the status_history table.
        if (!$this->isNewRecord)
		{
			$currentReport = FSReport::model()->findByPk($this->id);

            if ($currentReport->status != $this->status)
            {
                StatusHistory::model()->insertStatus($currentReport, 'status', $currentReport->status_date);
                $this->status_date = date('Y-m-d H:i:s');
            }

            //if there is a property_pid change then need to update the agent property relation too (if there is one)
            if($currentReport->property_pid !== $this->property_pid && isset($this->agent_property_id))
            {
                $agent_prop = AgentProperty::model()->findByPk($this->agent_property_id);
                $new_prop = Property::model()->with('member')->findByPk($this->property_pid);
                if($agent_prop->property_pid !== $this->property_pid)
                {
                    if(isset($new_prop->member))
                        $agent_prop->member_mid = $new_prop->member_mid;
                    $agent_prop->property_pid = $this->property_pid;
                    $agent_prop->save();
                }
            }
        }

		if(empty($this->scheduled_call))
			$this->scheduled_call = NULL;

        //all agent_props should now have a related property, so if it's not set already then need to set the reporty property_pid based on that relation (so we can eventually phase out agent props all together)
        if(!isset($this->property_pid) && isset($this->agent_property_id))
        {
            $agent_prop = AgentProperty::model()->findByPk($this->agent_property_id);
            $this->property_pid = $agent_prop->property_pid;
        }

		return parent::beforeSave();
	}

    /**
     * Retrieves the status history for the current report.
     * @return CActiveDataProvider status history data
     */
    public function getStatusHistory()
    {
        $dataProvider = new CActiveDataProvider('StatusHistory', array(
			'sort' => array('defaultOrder' => array('date_changed' => true)),
			'criteria' => array(
                'condition' => 'table_name=\'fs_report\' AND table_id='.$this->id.' AND table_field=\'status\'',
            ),
		));

        return $dataProvider;
    }

    /**
     * Counts all FS reports to date, or based on the search dates provided
     * @return int, formatted
     */
    public static function countReports($dateStart = null, $dateEnd = null)
    {
        $criteria = new CDbCriteria;
        $criteria->addCondition("status = 'Completed'");
        $criteria->addCondition("type = 'fs'");

        if($dateStart || $dateEnd){

            $criteria->addCondition("status_date >='" . $dateStart . "'");

            if($dateEnd)
                $criteria->addCondition("status_date <'" . $dateEnd . "'");

            $total = FSReport::model()->count($criteria);

        }
        else{
            $total = FSReport::model()->count($criteria);
        }

        return Yii::app()->format->number($total);
    }

    /**
     * Counts all WDSPro reports to date, or based on the search dates provided
     * @return int, formatted
     */
    public static function countWDSProReports($dateStart = null, $dateEnd = null)
    {
        $criteria = new CDbCriteria;
        $criteria->addCondition("status = 'Completed'");
        $criteria->addCondition("type NOT IN ('fs')");
        $params = array();

        if($dateStart || $dateEnd)
        {
            $criteria->addCondition("status_date >= :startDate");
            $params[':startDate'] = $dateStart;

            if($dateEnd)
            {
                $criteria->addCondition("status_date < :endDate");
                $params[':endDate'] = $dateEnd;
            }
            $criteria->params = $params;
            $total = FSReport::model()->count($criteria);
        }
        else
        {
            $total = FSReport::model()->count($criteria);
        }
        return Yii::app()->format->number($total);
    }

    /**
     * Gets report options from related client.
     * Sends in template vars to swap out place holders with about specific mem/prop tied to this report
     * @return array of report options
     */
    public function getClientReportOptions()
    {
        if(isset($this->property))
        {
            if(isset($this->property->member_mid))
            {
                $member = Member::model()->findByPk($this->property->member_mid);
                $member_name = $member->first_name;
                $member_name .= (empty($member->last_name)) ? '' : ' '.$member->last_name;
                $member_num = $member->member_num;
            }
            else
            {
                $member_name = 'n/a';
                $member_num = 'n/a';
            }
            $address = $this->property->address_line_1;
            $address .= (empty($this->property->address_line_2)) ? '' : ' '.$this->property->address_line_2;
            $city = $this->property->city;
            $state = $this->property->state;
            $zip = $this->property->zip;
            $policy_num = $this->property->policy;
            $producer = substr($this->property->producer, 0, strpos($this->property->producer,'('));
        }
        else if(isset($this->agent_property))
        {
            $member_name = 'n/a';
            $member_num = 'n/a';
            $address = $this->agent_property->address_line_1;
            $address .= (empty($this->agent_property->address_line_2)) ? '' : ' '.$this->agent_property->address_line_2;
            $city = $this->agent_property->city;
            $state = $this->agent_property->state;
            $zip = $this->agent_property->zip;
            $policy_num = 'n/a';
            $producer = 'n/a';
        }
        else
        {
            $member_name = 'n/a';
            $member_num = 'n/a';
            $address = 'n/a';
            $city = 'n/a';
            $state = 'n/a';
            $zip = 'n/a';
            $policy_num = 'n/a';
            $producer = 'n/a';
        }

        if(isset($this->pre_risk))
        {
            $pre_risk_ha_date = $this->pre_risk->ha_date;
        }
        else
        {
            $pre_risk_ha_date = 'n/a';
        }

        $agent_name = (isset($this->agent) ? $this->agent->first_name.' '.$this->agent->last_name : 'n/a');
        $report_date = date('F j, Y', strtotime($this->submit_date));
        $template_vars = array('member_name'=>$member_name, 'member_num'=>$member_num, 'property_address'=>$address, 'property_city'=>$city, 'property_state'=>$state, 'property_zip'=>$zip, 'agent_name'=>$agent_name, 'report_date'=>$report_date, 'property_policy_num'=>$policy_num, 'property_producer'=>$producer, 'pre_risk_ha_date'=>$pre_risk_ha_date);

        //defalut options
        $options = array('edu-b-top-right'=>'no client set', 'edu-b-header'=>'no client set', 'edu-b-mem-prop-info'=>'no client set', 'edu-inspection-info'=>'no client set', 'edu-b-bottom-blurb'=>'no client set');
        if(isset($this->client))
            $options = $this->client->getReportOptions($template_vars);
        if(isset($this->user->client))
            $options = $this->user->client->getReportOptions($template_vars);

        return $options;

    }

    //Counts the total number of completed reports per month, and returns an array with each month's tally
    public static function countCompletedReportsPerMonth($startDate, $endDate)
    {

        //Final result
        $returnArray = array();

        //Reassign to preserve the originals
        $date1 = date('Y-m-d', strtotime($startDate));
        $date2 = date('Y-m-d', strtotime('+1 months', strtotime($date1)));

        //makes selections for each month
        while($date2 < $endDate){

            $criteria = new CDbCriteria;
            $criteria->addCondition("status_date >= '$date1'");
            $criteria->addCondition("status_date < '$date2'");
            $criteria->addCondition("status = 'Completed'");
            $criteria->addCondition("type = 'fs'");

            //Add totals per month into return array
            $monthEntry = array(
                'month'=> date('M', strtotime($date1)),
                'completed_reports' => FSReport::model()->count($criteria)
            );

            //Add totals per month into return array
            $returnArray[] = $monthEntry;

            //incriment dates to the next month
            $date1 = $date2;
            $date2 = date('Y-m-d', strtotime('+1 months', strtotime($date1)));

        }

        return $returnArray;

    }

    //Get a count of the number of completed reports per state
    public static function getReportsByDate($startDate, $endDate)
    {
        //Final result
        $returnArray = array();

        $criteria = new CDbCriteria;
        $criteria->with = array('member');
        $criteria->addCondition("status_date >= '$startDate'");
        $criteria->addCondition("status_date < '$endDate'");
        $criteria->addCondition("status = 'Completed'");
        $criteria->addCondition("type = 'fs'");
        $criteria->addCondition("member.is_tester = 0");

        //Get models
        $models = FSReport::model()->findAll($criteria);

        //Load model data into return array
        foreach($models as $model)
        {
            $returnArray[] = array(
                'state' => $model->state,
                'condition_risk' => $model->condition_risk,
                'risk_level' => $model->risk_level
            );
        }

        return $returnArray;

    }

    //Takes the results from the getReportsByDate and tally's up the reports by state
    public static function countReportsPerState($result)
    {
        $returnArray = array();

        foreach($result as $row)
        {
            if(isset($returnArray[$row['state']]))
                $returnArray[$row['state']] +=1;
            else
                $returnArray[$row['state']] =1;
        }

        return $returnArray;

    }

    //returns the condtions for this report that are ordered by their corrosponding fs_assessment_question's order_by attribute
    public function getOrderedConditions()
    {
        $default_client_set = ClientAppQuestionSet::model()->findByAttributes(array('is_default'=>1, 'client_id'=>$this->client->id));
        $orderedConditions = array();
        $conditions = $this->conditions;
        foreach($conditions as $condition)
        {
            $question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $condition->condition_num, 'set_id'=>$default_client_set->id, 'client_id' => $this->client->id));
            $orderedConditions[$question->order_by] = $condition;
        }
        ksort($orderedConditions);
        return $orderedConditions;
    }

    //returns an ordered array containing both the condition tied to the report, plus it's related client question.
    //takes in a type
    public function getOrderedConditionsAndQuestions($type)
    {
       $orderedConditions = array();
       $conditions = $this->conditions;
       if(isset($this->client->id))
       {
            $clientId = $this->client->id;
       }
       else
       {
            $clientId = $this->user->client->id;
       }
       foreach($conditions as $condition)
       {
            $question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $condition->condition_num, 'set_id'=>$condition->set_id, 'client_id' => $clientId));

            if(isset($question) && ($question->type == $type || ($question->type == 'foh' && $type == 'condition'))) //only add the ones that have a matching question and is the right type
            {
                //if response is NOT 'no'(1), which means its yes(0) or not sure(2), and there is not already a score set, then set it to the question default
                if($condition->response != 1 && empty($condition->score) && isset($question->yes_points))
                    $condition->score = $question->yes_points;
                $orderedConditions[$question->order_by] = array('condition'=>$condition,'question'=>$question);
            }
        }
        ksort($orderedConditions);
        return $orderedConditions;
    }

    //creates conditions that are not field responses so they can be used internally
    public function createConditions($set_id, $client_id, $autoTriggeredQuestions=null)
    {
        $conditions = FSAssessmentQuestion::model()->findAllByAttributes(array('set_id'=>$set_id, 'client_id' => $client_id, 'type'=>'condition'));
        foreach($conditions as $condition)
        {
            //check if condition already exists
            $existing_condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$condition->question_num, 'set_id'=>$set_id));
            if(!isset($existing_condition))//if it doesn't exist, create it
            {
                $fsCondition = new FSCondition;
                $fsCondition->fs_report_id = $this->id;
                $fsCondition->condition_num = $condition->question_num;
                $fsCondition->set_id = $set_id;
                $fsCondition->question_text = $condition->question_text;
                $fsCondition->response = 1;

                //autotrigger logic
                if(isset($autoTriggeredQuestions))
                {
                    foreach($autoTriggeredQuestions as $autoTriggeredQuestion)
                    {
                        if($fsCondition->condition_num == $autoTriggeredQuestion['autofill_question_num'] && $set_id == $autoTriggeredQuestion['autofill_set_id'])
                        {
                            $fsCondition->response = 0; //set response to yes
                            $fsCondition->submitted_photo_path = $autoTriggeredQuestion['triggering_photo_path'];
                            $fsCondition->notes = 'Autofilled (set to yes and images added) from Field Condition '.$autoTriggeredQuestion['triggering_condition_num'];
                        }
                    }
                }

                if(!$fsCondition->save())
                    return "ERROR: Could not save responses for question_num: ".$condition['questionNumber'];
            }
        }

        //create FOH condition if there is one and it doesn't alerady exist
        $foh = FSAssessmentQuestion::model()->findByAttributes(array('set_id'=>$set_id, 'client_id' => $client_id, 'type'=>'foh'));
        if(isset($foh))
        {
            $existing_foh = FSCondition::model()->findByAttributes(array('fs_report_id'=>$this->id, 'condition_num'=>$foh->question_num, 'set_id'=>$set_id));
            if(!isset($existing_foh))
            {
                $fohCondition = new FSCondition;
                $fohCondition->fs_report_id = $this->id;
                $fohCondition->condition_num = $foh->question_num;
                $fohCondition->set_id = $set_id;
                $fohCondition->question_text = $foh->question_text;
                $fohCondition->response = 1;

                if(!$fohCondition->save())
                    return "ERROR: Could not save responses for question_num: ".$condition['questionNumber'];
            }
        }
    }

    //returns list of availible download types
    public function getDownloadTypes()
    {
        return array('UW'=>'UW', 'EDU'=>'EDU', 'UW,EDU'=>'UW,EDU');
    }

    /**
     * Returns the wdsrisk score for a given property.  If risk is not already set, one
     * will be created.
     * @return string
     */
    public function getPropertyRiskScore()
    {
        $return = -1; //somethings not right value of -1 by default

        if ($this->property_pid)
        {
            //look up to see if one already exists
            $riskscore = RiskScore::model()->getRiskScore($this->property_pid);

            if ($riskscore)
            {
                if (isset($riskscore->score_v)) //check for null
                    $return = round($riskscore->score_v * 100, 0);
            }
            else //no existing one, make a new one
            {
                $newriskscore = new RiskScore();
                $result = $newriskscore->setRiskScore($this->property, RiskScoreType::TYPE_WDSPRO); //create new score for related property, type_id=4 (WDS Pro)
                if ($result['error'] === false) //successfully created new risk score
                {
                    if (isset($newriskscore->score_v)) //check for null
                        $return = round($newriskscore->score_v * 100, 0);
                }
            }
        }

        return $return;
    }

    public function getQuestionSetID()
    {
        if(count($this->conditions)>0)
        {
            return $this->conditions[0]->set_id;
        }
    }
}