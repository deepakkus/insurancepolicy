<?php

/**
 * ResTriggeredWithPropertyStatus
 *
 * Inherts from ResTriggered and includes its associated ResPropertyStatus.
 * This model is used by the Response Property Status grid.
 */
class ResTriggeredWithPropertyStatus extends ResTriggered
{
    public $division;
    public $status;
    public $actions;
    public $has_photo;
    public $date_visited;
    public $engine_id;
    public $engine_name;
    public $other_issues;
    public $notice_name;    

    public $client_name;
    public $member_first_name;
	public $member_last_name;
	public $member_num;
    public $property_address_line_1;
    public $property_address_line_2;
    public $property_city;
    public $property_state;
    public $property_zip;
    public $property_response_status;
    public $fire_name;
    public $notice_date_created;

    /**
     * @return string the name of this model
     */
    public static function modelName()
    {
        return __CLASS__;
    }

    /**
     * @return array relational rules.
     */
	public function relations()
	{
		return array(
            'property' => array(self::BELONGS_TO, 'Property', 'property_pid', 'joinType' => 'INNER JOIN'),
            'propertyStatus' => array(self::HAS_ONE, 'ResPropertyStatus', 'res_triggered_id'),
            'notice' => array(self::BELONGS_TO, 'ResNotice', 'notice_id'),
            'fire' => array(self::BELONGS_TO, 'ResFireName', '', 'joinType' => 'INNER JOIN', 'on' => 'notice.fire_id = fire.Fire_ID'),
            'theClient' => array(self::BELONGS_TO, 'Client', 'client'),
            'engine' => array(self::BELONGS_TO, 'Engine', '', 'joinType' => 'LEFT JOIN', 'on' => 'engine.id = propertyStatus.engine_id'),
		);
	}

    /**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that will receive user inputs.
		return array(
            array('notice_id, property_pid, response_status, threat, distance', 'required'),
            array('engine_id, notice_id, property_pid, response_status, coverage, threat, priority, client', 'numerical', 'integerOnly'=>true),
            array('distance', 'numerical'),
			// The following rule is used by search(). Remove those attributes that should not be searched.
			array('id, notice_id, property_pid, response_status, coverage, threat, distance, priority, client, client_name, '
                . 'engine_id, division, status, actions, has_photo, other_issues, date_visited, '
                . 'member_first_name, member_last_name, member_num, property_address_line_1, property_address_line_2, '
                . 'property_city, property_state, property_zip, property_response_status, fire_name, engine_name, notice_date_created, notice_name'
                , 'safe', 'on'=>'search'),
		);
	}

    /**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
            'id' => 'ID',
            'member_first_name' => 'First Name',
			'member_last_name' => 'Last Name',
            'property_pid' => 'Property ID',
            'property_address_line_1' => 'Address',
            'property_address_line_2' => 'Address Line 2',
            'property_city' => 'City',
            'property_state' => 'State',
            'property_zip' => 'Zip',
            'property_response_status' => 'Response Status',
            'engine_id' => 'Engine',
            'engine_name' => 'Engine',
            'notice_name' => 'Notice',
		);
	}

	/**
     * Retrieves a list of models based on the current search/filter conditions.
     */
	public function search($pageSize = 25, $sort = NULL)
	{
        $criteria = new CDbCriteria;
        $criteria->select = array("*", "notice.type AS shortnoticename");

        $criteria->with = array(
            'property',
            'property.member',
            'propertyStatus',
            'notice',
            'fire',
            'theClient',
            'engine',
        );

		$criteria->compare('id', $this->id);
		$criteria->compare('notice_id', $this->notice_id);
		$criteria->compare('property_pid', $this->property_pid);
        $criteria->compare('response_status', $this->response_status);
        $criteria->compare('coverage', $this->coverage);
        $criteria->compare('threat', Helper::getBooleanIntFromString($this->threat));
        $criteria->compare('coverage', $this->coverage);
        $criteria->compare('distance', $this->distance);
        $criteria->compare('priority', $this->priority);
		$criteria->compare('client', $this->client);
        $criteria->compare('theClient.name', $this->client_name);
        $criteria->compare('member.first_name', $this->member_first_name, true);
        $criteria->compare('member.last_name', $this->member_last_name, true);
        $criteria->compare('member.member_num', $this->member_num, true);
        $criteria->compare('property.address_line_1', $this->property_address_line_1, true);
        $criteria->compare('property.address_line_2', $this->property_address_line_2, true);
        $criteria->compare('property.city', $this->property_city, true);
        $criteria->compare('property.state', $this->property_state, true);
        $criteria->compare('property.zip', $this->property_zip, true);
        $criteria->compare('property.response_status', $this->property_response_status);
        $criteria->compare('fire.Name', $this->fire_name, true);
        $criteria->compare('engine.name', $this->engine_name);
        $criteria->compare('propertyStatus.division', $this->division);
        $criteria->compare('propertyStatus.status', $this->status, true);
        $criteria->compare('propertyStatus.actions', $this->actions, true);
        $criteria->compare('propertyStatus.has_photo', Helper::getBooleanIntFromString($this->has_photo));
        $criteria->compare('propertyStatus.other_issues', $this->other_issues, true);
        $criteria->compare('propertyStatus.date_visited', $this->date_visited, true);
        $criteria->compare('notice.date_created', $this->notice_date_created, true);
        
        // Handle the special case of searching for a notice name, which is a combination of fields in the database.
        // Note: this logic needs to match the logic in the ResNotice.getNoticeName property.
        $noticeNameCriteria = "CASE WHEN notice.subsequent_num IS NULL OR notice.subsequent_num = '' THEN notice.type ELSE notice.subsequent_num END + ' - ' + notice.recommended_action";
        $criteria->compare($noticeNameCriteria, $this->notice_name, true);
        
        // Sort way: false for DESC and true for ASC
        $sortWay = false;
		// If the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
        if (stripos($sort,'.'))
			$sortWay = true;
        // If the sort order is descending specified it will be like status.desc so need to drop the .desc
		$sort = str_replace('.desc', '', $sort);

        $dataProvider = new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array($sort => $sortWay),
				'attributes' => array(
                    'notice_name' => array(
                        'asc' => "$noticeNameCriteria",
                        'desc' => "$noticeNameCriteria DESC",
                    ),
                    'engine_name' => array(
                        'asc' => 'engine.name',
                        'desc' => 'engine.name DESC',
                    ),
                    'client_name' => array(
                        'asc' => 'theClient.Name',
                        'desc' => 'theClient.Name DESC',
                    ),
                    'fire_name' => array(
                        'asc' => 'fire.Name',
                        'desc' => 'fire.Name DESC',
                    ),
                    'member_first_name' => array(
                        'asc' => 'member.first_name',
                        'desc' => 'member.first_name DESC',
                    ),
                    'member_last_name' => array(
                        'asc' => 'member.last_name',
                        'desc' => 'member.last_name DESC',
                    ),
                    'property_address_line_1' => array(
                        'asc' => 'property.address_line_1',
                        'desc' => 'property.address_line_1 DESC'
                    ),
                    'property_address_line_2' => array(
                        'asc' => 'property.address_line_2',
                        'desc' => 'property.address_line_2 DESC'
                    ),
                    'property_city' => array(
                        'asc' => 'property.city',
                        'desc' => 'property.city DESC'
                    ),
                    'property_state' => array(
                        'asc' => 'property.state',
                        'desc' => 'property.state DESC'
                    ),
                    'property_zip' => array(
                        'asc' => 'property.zip',
                        'desc' => 'property.zip DESC'
                    ),
                    'property_response_status' => array(
                        'asc' => 'property.response_status',
                        'desc' => 'property.response_status DESC'
                    ),
                    // 999 lets blank priorities float to the end of the list so that 1 can be at the top.
                    'priority' => array(
                        'asc' => 'ISNULL(priority, 999)',
                        'desc' => 'priority DESC',
                    ),
                    'division' => array(
                        'asc' => 'propertyStatus.division',
                        'desc' => 'propertyStatus.division DESC'
                    ),
                    'status' => array(
                        'asc' => 'propertyStatus.status',
                        'desc' => 'propertyStatus.status DESC'
                    ),
                    'actions' => array(
                        'asc' => 'propertyStatus.actions',
                        'desc' => 'propertyStatus.actions DESC'
                    ),
                    'has_photo' => array(
                        'asc' => 'propertyStatus.has_photo',
                        'desc' => 'propertyStatus.has_photo DESC'
                    ),
                    'other_issues' => array(
                        'asc' => 'propertyStatus.other_issues',
                        'desc' => 'propertyStatus.other_issues DESC'
                    ),
                    'date_visited' => array(
                        'asc' => 'propertyStatus.date_visited',
                        'desc' => 'propertyStatus.date_visited DESC'
                    ),
                    '*',
				),
			),
			'criteria' => $criteria,
		));

		if ($pageSize == NULL)
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

    /**
     * This method is invoked after each record is instantiated by a find method.
     */
    protected function afterFind()
    {
        if (isset($this->property))
        {
            $property = $this->property;

            $this->property_address_line_1 = $property->address_line_1;
            $this->property_address_line_2 = $property->address_line_2;
            $this->property_city = $property->city;
            $this->property_state = $property->state;
            $this->property_zip = $property->zip;
            $this->property_response_status = $property->response_status;

            if (isset($this->property->member))
            {
                $member = $property->member;

                $this->member_first_name = $member->first_name;
                $this->member_last_name = $member->last_name;
                $this->member_num = $member->member_num;
            }
        }

        if (isset($this->fire))
        {
            $this->fire_name = $this->fire->Name;
        }

        if (isset($this->theClient))
        {
            $this->client_name = $this->theClient->name;
        }

        if (isset($this->propertyStatus))
        {
            $this->division = $this->propertyStatus->division;
            $this->status = $this->propertyStatus->status;
            $this->actions = $this->propertyStatus->actions;
            $this->has_photo = $this->propertyStatus->has_photo;

            // Convert the date/time fields to display format.
            if (!empty($this->propertyStatus->date_visited))
                $this->date_visited = date_format(new DateTime($this->propertyStatus->date_visited), 'Y-m-d H:i');

            $this->other_issues = $this->propertyStatus->other_issues;
        }

        if (isset($this->engine))
        {
            $this->engine_id = $this->engine->id;
            $this->engine_name = $this->engine->name;
        }

        if (isset($this->notice))
        {
            $this->notice_name = $this->notice->shortnoticename;
            $this->notice_date_created = $this->notice->date_created;
        }
        
        parent::afterFind();
    }

    /**
     * Returns available property statuses
     */
    public function getPropertyStatuses()
    {
        return array (
            '' => '',
            'damaged' => 'damaged',
            'safe' => 'safe',
            'threatened' => 'threatened',
        );
    }
}
