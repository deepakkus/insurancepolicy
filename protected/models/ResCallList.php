<?php

/**
 * This is the model class for table "res_call_list".
 *
 * The followings are the available columns in table 'res_call_list':
 * @property integer $id
 * @property integer $res_fire_id
 * @property integer $property_id
 * @property integer $assigned_caller_user_id
 * @property integer $triggered
 * @property integer $client_id
 * @property integer $do_not_call
 */
class ResCallList extends CActiveRecord
{
    public $res_triggered_response_status;
    public $res_triggered_coverage;
    public $res_triggered_threat;
    public $res_triggered_distance;
    public $res_triggered_priority;
    public $member_first_name;
	public $member_last_name;
	public $member_num;
    public $client_name;
    public $property_address_line_1;
    public $property_address_line_2;
    public $property_city;
    public $property_state;
    public $property_zip;
    public $fire_name;
    public $notice_type;
    public $assigned_caller_user_name;
    public $evacuated;
    public $published;
    public $dashboard_comments;
    public $general_comments;
    public $prop_res_status;

    const ATTRIBUTES = 'wds_response_call_list_searchAttr';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_call_list';
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
            array('res_fire_id, property_id, client_id', 'required'),
			array('res_fire_id, property_id, assigned_caller_user_id, client_id', 'numerical', 'integerOnly'=>true),
            array('do_not_call', 'safe'),
			// The following rule is used by search(). Remove those attributes that should not be searched.
            array('id, res_fire_id, property_id, assigned_caller_user_id, triggered, '
                . 'res_triggered_response_status, res_triggered_coverage, res_triggered_threat, res_triggered_distance, res_triggered_priority, '
                . 'member_first_name, member_last_name, member_num, client_name, property_address_line_1, property_address_line_2, '
                . 'property_city, property_state, property_zip, fire_name, notice_type, assigned_caller_user_name, '
                . 'published, evacuated, dashboard_comments, general_comments, prop_res_status, do_not_call', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'assigned_caller_user' => array(self::BELONGS_TO, 'User', 'assigned_caller_user_id'),
            'property' => array(self::BELONGS_TO, 'Property', 'property_id'),
            'fire' => array(self::BELONGS_TO, 'ResFireName', 'res_fire_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'notice' => array(self::BELONGS_TO, 'ResNotice', '',
                'on' => 'notice.notice_id = (SELECT MAX(notice_id) FROM res_notice rsn where rsn.fire_id = t.res_fire_id AND rsn.client_id = t.client_id)'),
            'res_triggered' => array(self::BELONGS_TO, 'ResTriggered', '', 'joinType' => 'LEFT JOIN',
                'on' => 'notice.notice_id = res_triggered.notice_id AND t.property_id = res_triggered.property_pid'),
            'call_attempt' => array(self::HAS_MANY, 'ResCallAttempt', 'call_list_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'res_fire_id' => 'Fire',
			'property_id' => 'Property ID',
            'client_id' => 'Client',
			'assigned_caller_user_name' => 'Assigned Caller',
			'triggered' => 'Triggered',
            'res_triggered_response_status' => 'Response Status',
            'res_triggered_coverage' => 'Coverage',
            'res_triggered_threat' => 'Threat',
            'res_triggered_distance' => 'Distance',
            'res_triggered_priority' => 'Priority',
            'member_first_name' => 'First Name',
            'member_last_name' => 'Last Name',
            'property_address_line_1' => 'Address Line 1',
            'property_address_line_2' => 'Address Line 2',
            'property_city' => 'City',
            'property_state' => 'State',
            'property_zip' => 'Zip',
            'do_not_call'=>'Do Not Call'
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
	public function search($pageSize = 25, $sort = NULL, $method = NULL)
	{
		$criteria = new CDbCriteria;
        //$criteria->select = array("*", "notice.recommended_action AS noticename");
        $criteria->with = array(
            'assigned_caller_user',
            'client',
            'fire',
            'notice',
            'res_triggered',
            'property',
            'property.member',
            'call_attempt'=>
            array(
            'on' => 'call_attempt.id = (SELECT MAX(id) FROM res_call_attempt a WHERE a.call_list_id = t.id)'
            )
        );
		$criteria->compare('t.id', $this->id);
		$criteria->compare('t.res_fire_id', $this->res_fire_id);
        $criteria->compare('do_not_call', $this->do_not_call);
		$criteria->compare('t.property_id', $this->property_id);
        $criteria->compare('t.triggered', $this->triggered);
        $criteria->compare('res_triggered.response_status', $this->res_triggered_response_status);
        $criteria->compare('res_triggered.coverage',$this->res_triggered_coverage);
        $criteria->compare('res_triggered.threat', Helper::getBooleanIntFromString($this->res_triggered_threat));
        $criteria->compare('res_triggered.distance',$this->res_triggered_distance);
        $criteria->compare('res_triggered.priority',$this->res_triggered_priority);
        $criteria->compare('client.name', $this->client_name);
        $criteria->compare('member.first_name', $this->member_first_name, true);
        $criteria->compare('member.last_name', $this->member_last_name, true);
        $criteria->compare('member.member_num', $this->member_num, true);
        $criteria->compare('property.address_line_1', $this->property_address_line_1, true);
        $criteria->compare('property.address_line_2', $this->property_address_line_2, true);
        $criteria->compare('property.city', $this->property_city, true);
        $criteria->compare('property.state', $this->property_state, true);
        $criteria->compare('property.zip', $this->property_zip, true);
        $criteria->compare('fire.Name', $this->fire_name);
        $criteria->compare('notice.wds_status', $this->notice_type, true);
        $criteria->compare('assigned_caller_user.name', $this->assigned_caller_user_name, true);
        if(isset($this->prop_res_status)&&($this->prop_res_status!=''))
        {
           $criteria->compare('call_attempt.prop_res_status',$this->prop_res_status);
           $criteria->together = true;
        }
        if(isset($this->published)&&($this->published!=''))
        {
           $criteria->compare('call_attempt.publish',$this->published);
           $criteria->together = true;
        }
        
        
		$sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

        $dataProvider = new WDSCActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array($sort=>$sortWay),
				'attributes' => array(
                    'assigned_caller_user_name'=>array(
						'asc' => 'assigned_caller_user.name ASC',
						'desc' => 'assigned_caller_user.name DESC',
					),
                    'fire_name' => array(
                        'asc' => 'fire.Name ASC',
                        'desc' => 'fire.Name DESC',
                    ),
                    'client_name' => array(
                        'asc' => 'client.name ASC',
                        'desc' => 'client.name DESC',
                    ),
                    'notice_type' => array(
                        'asc' => 'notice.wds_status ASC',
                        'desc' => 'notice.wds_status DESC'
                    ),
                    'notice_id' => array(
                        'asc' => 'notice.notice_id ASC',
                        'desc' => 'notice.notice_id DESC'
                    ),
                    'member_first_name' => array(
                        'asc' => 'member.first_name ASC',
                        'desc' => 'member.first_name DESC',
                    ),
                    'member_last_name' => array(
                        'asc' => 'member.last_name ASC',
                        'desc' => 'member.last_name DESC',
                    ),
                    'member_num' => array(
                        'asc' => 'member.member_num ASC',
                        'desc' => 'member.member_num DESC',
                    ),
                    'property_address_line_1' => array(
                        'asc' => 'property.address_line_1 ASC',
                        'desc' => 'property.address_line_1 DESC'
                    ),
                    'property_address_line_2' => array(
                        'asc' => 'property.address_line_2 ASC',
                        'desc' => 'property.address_line_2 DESC'
                    ),
                    'property_city' => array(
                        'asc' => 'property.city ASC',
                        'desc' => 'property.city DESC'
                    ),
                    'property_state' => array(
                        'asc' => 'property.state ASC',
                        'desc' => 'property.state DESC'
                    ),
                    'property_zip' => array(
                        'asc' => 'property.zip ASC',
                        'desc' => 'property.zip DESC'
                    ),
                    'res_triggered_priority' => array(
                        'asc' => 'res_triggered.priority ASC',
                        'desc' => 'res_triggered.priority DESC',
                    ),
                    'res_triggered_threat' => array(
                        'asc' => 'res_triggered.threat ASC',
                        'desc' => 'res_triggered.threat DESC',
                    ),
                    'res_triggered_distance' => array(
                        'asc' => 'res_triggered.distance ASC',
                        'desc' => 'res_triggered.distance DESC',
                    ),
                    'res_triggered_response_status' => array(
                        'asc' => 'res_triggered.response_status ASC',
                        'desc' => 'res_triggered.response_status DESC',
                    ),
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
        if($method)
        {
            $dataProvider->pagination->currentPage = 0;
        }
		return $dataProvider;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResCallList the static model class
	 */
	public static function model($className=__CLASS__)
	{
        // These two tables must be joined on or a "multi-part identifier could not be bound"
        // sql error with raise
		return parent::model($className)->with(
            'notice',
            'res_triggered'
        );
	}

    /**
     * This method is invoked after each record is instantiated by a find method.
     */
    protected function afterFind()
    {
        if ($this->client)
            $this->client_name = $this->client->name;

        if ($this->fire)
            $this->fire_name = $this->fire->Name;

        if ($this->notice)
            $this->notice_type = $this->notice->wds_status;

        if ($this->res_triggered)
        {
            $this->res_triggered_coverage = $this->res_triggered->coverage;
            $this->res_triggered_distance = $this->res_triggered->distance;
            $this->res_triggered_threat = $this->res_triggered->threat;
            $this->res_triggered_priority = $this->res_triggered->priority;
            $this->res_triggered_response_status = $this->res_triggered->response_status;
        }

        if ($this->property)
        {
            $this->property_address_line_1 = $this->property->address_line_1;
            $this->property_address_line_2 = $this->property->address_line_2;
            $this->property_city = $this->property->city;
            $this->property_state = $this->property->state;
            $this->property_zip = $this->property->zip;

            if ($this->property->member)
            {
                $this->member_first_name = $this->property->member->first_name;
                $this->member_last_name = $this->property->member->last_name;
                $this->member_num = $this->property->member->member_num;
            }
        }

        if ($this->call_attempt)
        {
            $this->evacuated = array();
            $this->published = array();
            $this->dashboard_comments = array();
            $this->general_comments = array();
            $this->prop_res_status = '';
            foreach($this->call_attempt as $call_attempt)
            {
                $this->evacuated[] = $call_attempt->evacuated;
                $this->dashboard_comments[] = $call_attempt->dashboard_comments;
                $this->general_comments[] = $call_attempt->general_comments;
                if(!empty($call_attempt->publish))
                {
                    $this->published[] = $call_attempt->publish;
                }
                $this->prop_res_status = $call_attempt->prop_res_status;
            }
        }
    }

    /**
     * Gets the member's full name.
     */
    public function getMemberFullName()
    {
        $fullName = NULL;

        if (isset($this->property))
        {
            if (isset($this->property->member))
            {
                $fullName = $this->property->member->first_name . ' ' . $this->property->member->last_name;
            }
        }

        return $fullName;
    }

    // Creates a report of the current gridview. (all pages)
    public function makeDownloadableReport($columnsToShow, $sort)
    {
        if (isset($_SESSION[self::ATTRIBUTES]) && !empty($_SESSION[self::ATTRIBUTES]['fire_name']))
            $name = Yii::app()->user->name . '_ResCallListReport_' . $_SESSION[self::ATTRIBUTES]['fire_name'];
        else
            $name = Yii::app()->user->name . '_ResCallListReport';

        $columnsHeader = array();
        foreach ($columnsToShow as $columns)
        {
            $columnsHeader[] = $this -> getAttributeLabel($columns);
        }
                
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$name.csv");

        $csvfile = fopen('php://output', 'w');
        
        fputcsv($csvfile, $columnsHeader);

		$pageSize = 100;

		$dataProvider = $this->search($pageSize, $sort);
		$dataRows = $dataProvider->getData(true);
		$pagination = $dataProvider->pagination;

        while ($pagination->currentPage < $pagination->pageCount)
        {
            $dataRows = $dataProvider->getData(true);
            foreach ($dataRows as $data)
            {
                $csv_array = array();
                foreach($columnsToShow as $columnToShow)
                {
                    // If a call attempt, aggregate comments for the csv
                    if (is_array($data->$columnToShow)) {
                        $csv_array[] = join("\n", $data->$columnToShow);
                    }
                    else {
                        $csv_array[] = $data->$columnToShow;
                    }
                }
                fputcsv($csvfile, $csv_array);
            }
            $pagination->currentPage++;
        }
        fclose($csvfile);
    }

    // Creates a report of the new current gridview. (all pages)
    public function makeDownloadableReport2($columnsToShow, $sort, $dataItems)
    {
        if (isset($_SESSION[self::ATTRIBUTES]) && !empty($_SESSION[self::ATTRIBUTES]['fire_name']))
            $name = Yii::app()->user->name . '_ResCallListReport_' . $_SESSION[self::ATTRIBUTES]['fire_name'];
        else
            $name = Yii::app()->user->name . '_ResCallListReport';

        $columnsHeader = array();
        foreach ($columnsToShow as $columns)
        {
            $columnsHeader[] = $this -> getAttributeLabel($columns);
        }
                
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$name.csv");

        $csvfile = fopen('php://output', 'w');
        
        fputcsv($csvfile, $columnsHeader);

		$pageSize = 100;

		//$dataProvider = $this->search($pageSize, $sort);
        $dataProvider = $dataItems;
		//$dataRows = $dataProvider->getData(true);
		//$pagination = $dataProvider->pagination;
        $csv_data = '';
       // while ($pagination->currentPage < $pagination->pageCount)
       // {
            //$dataRows = $dataProvider->getData(true);
            //foreach ($dataRows as $data)
            foreach ($dataProvider as $data)
            {
                $csv_array = array();

                foreach($columnsToShow as $columnToShow)
                {
                    // If a call attempt, aggregate comments for the csv
                    /*if (is_array($data->$columnToShow)) {
                        $csv_array[] = join("\n", $data->$columnToShow);
                    }
                    else {
                        $csv_array[] = $data->$columnToShow;
                    }*/
                    $csv_data = '';
                    if($columnToShow=='do_not_call')
                    {
                        $csv_data = ($data['do_not_call'] == 0)? '' : 'DNC';
                    }
                    if($columnToShow=='assigned_caller_user_name')
                    {
                        $csv_data = $data['username'];
                    }
                    elseif($columnToShow=='client_name')
                    {
                        $csv_data = $data['clientname'];
                    }
                    elseif($columnToShow=='fire_name')
                    {
                        $csv_data = $data['firename'];
                    }
                    elseif($columnToShow=='res_triggered_priority')
                    {
                        $csv_data = $data['priority'];
                    }
                    elseif($columnToShow=='res_triggered_priority')
                    {
                        $csv_data = $data['priority'];
                    }
                    elseif($columnToShow=='res_triggered_threat')
                    {
                        $csv_data = ($data['threat'] == 1)? 'Yes' : 'No';
                    }
                    elseif($columnToShow=='res_triggered_distance')
                    {
                        $csv_data = number_format($data['distance'],2);
                    }
                    elseif($columnToShow=='res_triggered_response_status')
                    {
                        $csv_data = $data['rtresponsestatus'];
                    }
                    elseif($columnToShow=='triggered')
                    {
                        $csv_data = ($data['triggered'] == 1)? 'Yes' : 'No';
                    }
                    elseif($columnToShow=='evacuated')
                    {
                        $csv_data = $data['evacuated'];
                    }
                    elseif($columnToShow=='published')
                    {
                        $csv_data = ($data['publish'] == 1)? 'Yes' : 'No';
                    }
                    elseif($columnToShow=='dashboard_comments')
                    {
                        $csv_data = $data['dashboard_comments'];
                    }
                    elseif($columnToShow=='general_comments')
                    {
                        $csv_data = $data['general_comments'];
                    }
                    elseif($columnToShow=='prop_res_status')
                    {
                        $csv_data = $data['prop_res_status'];
                    }
                    elseif($columnToShow=='property_id')
                    {
                        $csv_data = $data['property_id'];
                    }
                    elseif($columnToShow=='member_first_name')
                    {
                        $csv_data = $data['first_name'];
                    }
                    elseif($columnToShow=='member_last_name')
                    {
                        $csv_data = $data['last_name'];
                    }
                    elseif($columnToShow=='property_address_line_1')
                    {
                        $csv_data = $data['address_line_1'];
                    }
                    elseif($columnToShow=='property_address_line_2')
                    {
                        $csv_data = $data['address_line_2'];
                    }
                    elseif($columnToShow=='property_city')
                    {
                        $csv_data = $data['city'];
                    }
                    elseif($columnToShow=='property_state')
                    {
                        $csv_data = $data['state'];
                    }
                    elseif($columnToShow=='property_zip')
                    {
                        $csv_data = $data['zip'];
                    }
                    elseif($columnToShow=='member_num')
                    {
                        $csv_data = $data['member_num'];
                    }
                    $csv_array[] = $csv_data;
                }
                fputcsv($csvfile, $csv_array);
            }
            //$pagination->currentPage++;
        //}
        fclose($csvfile);
    }

}
