<?php

/**
 * This is the model class for table "agent_property".
 *
 * The followings are the available columns in table 'agent_property':
 * @property integer $id
 * @property string $address_line_1
 * @property string $address_line_2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $zip_supp
 * @property double $long
 * @property double $lat
 * @property integer $agent_id
 * @property integer $geo_risk
 * @property integer $property_value
 * @property string $work_order_num
 * @property integer $property_pid
 * @property integer $question_set_id
 * @property string $policyholder_name
 * @property integer $member_mid
 * @property string $status //coorosponds to property->app_status, can be 'canceled' or 'active'
 *
 * The followings are the available model relations:
 * @property Agent $agent
 */
class AgentProperty extends CActiveRecord
{
   	public $agent_first_name;
	public $agent_last_name;
	public $agent_num;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'agent_property';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('address_line_1, city, state, agent_id', 'required'),
			array('agent_id, geo_risk, property_value, property_pid, question_set_id, member_mid', 'numerical', 'integerOnly'=>true),
			array('long, lat', 'numerical'),
			array('address_line_1, city, policyholder_name', 'length', 'max'=>100),
			array('address_line_2', 'length', 'max'=>50),
			array('state, zip, zip_supp, work_order_num', 'length', 'max'=>25),
            array('status', 'in', 'range'=>$this->getStatuses()),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, address_line_1, address_line_2, city, state, zip, zip_supp, long, lat, agent_id, geo_risk, property_value, work_order_num, '
                . 'agent_first_name, agent_last_name, agent_num, property_pid, question_set_id, status, policyholder_name, member_mid', 'safe', 'on'=>'search'),
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
			'agent' => array(self::BELONGS_TO, 'Agent', 'agent_id'),
            'fs_reports' => array(self::HAS_MANY, 'FSReport', 'agent_property_id'),
            'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
            'question_set' => array(self::BELONGS_TO, 'ClientAppQuestionSet', 'question_set_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'address_line_1' => 'Address Line 1',
			'address_line_2' => 'Address Line 2',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'zip_supp' => 'Zip Supp',
			'long' => 'Long',
			'lat' => 'Lat',
			'agent_id' => 'Agent',
			'geo_risk' => 'Geo Risk',
			'property_value' => 'Property Value',
			'work_order_num' => 'Work Order Number',
            'property_pid' => 'PID',
            'question_set_id' => 'Question Set',
            'status' => 'Status',
            'policyholder_name' => 'Policyholder Name',
            'member_mid' => 'MID',
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
	public function search($pageSize = 25, $sort = 'id')
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
		$criteria->with = array('agent');

		$criteria->compare('t.id',$this->id);
		$criteria->compare('address_line_1',$this->address_line_1,true);
		$criteria->compare('address_line_2',$this->address_line_2,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('zip_supp',$this->zip_supp,true);
		$criteria->compare('long',$this->long);
		$criteria->compare('lat',$this->lat);
		$criteria->compare('agent_id',$this->agent_id);
		$criteria->compare('geo_risk',$this->geo_risk);
		$criteria->compare('property_value',$this->property_value);
		$criteria->compare('work_order_num',$this->work_order_num,true);
        $criteria->compare('property_pid', $this->property_pid,false);
        $criteria->compare('question_set_id', $this->question_set_id);
        $criteria->compare('status', $this->status);
        $criteria->compare('policyholder_name', $this->policyholder_name);
        $criteria->compare('member_mid', $this->member_mid);
		$criteria->compare('agent.first_name', $this->agent_first_name, true);
		$criteria->compare('agent.last_name', $this->agent_last_name, true);
		$criteria->compare('agent.agent_num', $this->agent_num, true);

		$sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc
		
        $dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
				'attributes'=>array(
					'agent_first_name'=>array(
						'asc'=>'agent.first_name',
						'desc'=>'agent.first_name DESC',
					),
					'agent_last_name'=>array(
						'asc'=>'agent.last_name',
						'desc'=>'agent.last_name DESC',
					),
					'agent_num'=>array(
						'asc'=>'agent.agent_num',
						'desc'=>'agent.agent_num DESC',
					),
					'*',
				),
			),
			'criteria'=>$criteria,
            'pagination'=>array(
                        'pageSize'=>50,
                ),
		));
		
        //if($pageSize == NULL)
        //{
        //    $dataProvider->pagination = false;
        //}
        //else
        //{
        //    $dataProvider->pagination->pageSize = $pageSize;
        //    $dataProvider->pagination->validateCurrentPage = false;
        //}
		
		return $dataProvider;
	}

    /**
     * This method is invoked after each record is instantiated by a find method.
     */
    protected function afterFind() 
    {
        if (isset($this->agent)) 
        {
            $this->agent_first_name = $this->agent->first_name;
            $this->agent_last_name = $this->agent->last_name;
            $this->agent_num = $this->agent->agent_num;
        }

        //if pid isn't set then create related property which will set it
        if(empty($this->property_pid))
        {
            $this->property_pid = $this->createRelatedProperty();
            $this->save();
        }

        parent::afterFind();
    }

    protected function beforeSave()
    {
        //if there is a policyholder name we need to create a related member to store it
        if(!empty($this->policyholder_name))
        {
            //if mid isn't set then create related mem which will set it
            if(empty($this->member_mid))
                $this->member_mid = $this->createRelatedMember();
            else //related mem already existed, need to update it with any new info
                $this->updateRelatedMember();
        }

        //if pid isn't set then create related property which will set it
        if(empty($this->property_pid)) 
            $this->property_pid = $this->createRelatedProperty();
        else //related prop already existed, need to update it with any new info
            $this->updateRelatedProperty();

        return parent::beforeSave();
    }
    
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AgentProperty the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    //this is a "bridge" function to update a corrosponding main properties table entry
    private function updateRelatedProperty()
    {
        $property = Property::model()->findByPk($this->property_pid);
        if(isset($property) && $property->type_id == 3) //if prop exists and is agent type (we don't update pif types)
        {
            $property->address_line_1 = $this->address_line_1;
            $property->city = $this->city;
            $property->state = $this->state;
            $property->zip = $this->zip;
            $property->long = $this->long;
            $property->lat = $this->lat;
            $property->app_status = $this->status;
            $property->comments = "Work Order Num: ".$this->work_order_num;
            $property->question_set_id = $this->question_set_id;
            $property->member_mid = $this->member_mid;
            $property->save();
        }
    }

    //this is a "bridge" function to create corrosponding main properties table entries for all agent_property entries
    private function createRelatedProperty()
    {
        if(empty($this->property_pid)) //check if it's empty, otherwise it already has a related property
        {
            //if there is a workorder number with a PID already, then use that to connect up to a property
            if(isset($this->work_order_num) && strpos($this->work_order_num, 'PID:') !== false)
            {
                $prop_id = trim(str_replace('PID:', '', $this->work_order_num));
                $this->property_pid = intval($prop_id);
                $save_results = true; //prop already exists, no need to save
            }
            else //no pid in the work order num, so create a new one
            {
                $property = new Property();
                $property->member_mid = $this->member_mid;
                $property->client_id = $this->agent->client_id;
                $property->address_line_1 = $this->address_line_1;
                $property->address_line_2 = $this->address_line_2;
                $property->city = $this->city;
                $property->state = $this->state;
                $property->zip = $this->zip;
                $property->zip_supp = $this->zip_supp;
                $property->long = $this->long;
                $property->lat = $this->lat;
                $property->agent_id = $this->agent_id;
                $property->comments = "Work Order Num: ".$this->work_order_num;
                $property->fireshield_status = 'enrolled';
                $property->response_status = 'not enrolled';
                $property->pre_risk_status = 'not enrolled';
                $property->policy_status = 'active';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->res_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status_date = date('Y-m-d H:i:s');
                if(!empty($this->work_order_num))
                    $property->policy = $this->work_order_num;
                else
                    $property->policy = com_create_guid();

                $property->policy = substr($property->policy,0,15);
                $property->type_id = 3; //agent type
                $property->question_set_id = $this->question_set_id;
                if($property->save())
                    return $property->pid;
                else
                    return false;
            }
        }
        return true;
    }

    private function updateRelatedMember()
    {
        $member = Member::model()->findByPk($this->member_mid);
        if(isset($member) && $member->type_id == 3) //if mem exists and is agent type (we don't update pif types)
        {
            $name_split = explode(' ', $this->policyholder_name);
            $last_word_index = count($name_split) - 1;
            $member->last_name = $name_split[$last_word_index];
            if(count($name_split) > 1)
            {
                array_pop($name_split);
                $member->first_name = implode(' ', $name_split);
            }
            return $member->save();
        }
    }

    private function createRelatedMember()
    {
        $member = new Member();
        $member->client_id = $this->agent->client_id;
        $client = Client::model()->findByPk($this->agent->client_id);
        $member->client = $client->name;
        $member->member_num = com_create_guid();
        $name_split = explode(' ', $this->policyholder_name);
        $last_word_index = count($name_split) - 1;
        $member->last_name = $name_split[$last_word_index];
        if(count($name_split) > 1)
        {
            array_pop($name_split);
            $member->first_name = implode(' ', $name_split);
        }
        if($member->save())
            return $member->mid;
        else
            return false;
    }

    public function getStatuses()
	{
		return array('active','canceled');
	}
}
