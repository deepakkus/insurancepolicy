<?php

/**
 * This is the model class for table "wdsfire_enrollments".
 *
 * The followings are the available columns in table 'wdsfire_enrollments':
 * @property integer $id
 * @property integer $user_id
 * @property integer $client_id
 * @property integer $fire_id
 * @property integer $pid
 * @property integer $status_id
 * @property string $date
 */
class WdsfireEnrollments extends CActiveRecord
{
    //virtual attributes
    public $user_name;
    public $client_name;
    public $fire_name;
    public $status_type;
    public $member_first_name;
    public $member_last_name;
    public $property_address_1;
    public $property_city;
    public $property_state;
    public $property_zip;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'wdsfire_enrollments';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('user_id, client_id, pid, status_id, date', 'required'),
			array('user_id, client_id, fire_id, pid, status_id', 'numerical', 'integerOnly'=>true),
     		// The following rule is used by search().
			array('user_id, client_id, fire_id, pid, status_id, date, user_name, client_name, fire_name, status_type, member_first_name, member_last_name, property_address_1, property_city, property_state, property_zip', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'resFireName' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'property' => array(self::BELONGS_TO, 'Property', 'pid'),
            'status' => array(self::BELONGS_TO, 'EnrollmentStatus', 'status_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
            'client_id' => 'Client',
			'fire_id' => 'Fire',
			'pid' => 'PID',
			'status_id' => 'Status',
			'date' => 'Date',
            
            // Virtual Attributes
            'user_name' => 'User',
            'client_name' => 'Client',
            'fire_name' => 'Fire',
            'status_type' => 'Status',
            'member_first_name' => 'First Name',
            'member_last_name' => 'Last Name',
            'property_address_1' => 'Address',
            'property_city' => 'City',
            'property_state' => 'State',
            'property_zip' => 'Zip',
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
		$criteria=new CDbCriteria;
        
        $criteria->with = array('user','client','resFireName','status','property','property.member');

		$criteria->compare('user_id',$this->user_id);
        $criteria->compare('client_id',$this->client_id);
		$criteria->compare('fire_id',$this->fire_id);
		$criteria->compare('t.pid',$this->pid,true);
		$criteria->compare('status_id',$this->status_id);
        $criteria->compare('[user].name',$this->user_name,true);
        $criteria->compare('client.name',$this->client_name,true);
        $criteria->compare('resFireName.Name',$this->fire_name, true);
        $criteria->compare('status.status',$this->status_type);
        $criteria->compare('member.first_name',$this->member_first_name,true);
        $criteria->compare('member.last_name',$this->member_last_name,true);
        $criteria->compare('property.address_line_1',$this->property_address_1,true);
        $criteria->compare('property.city',$this->property_city,true);
        $criteria->compare('property.state',$this->property_state);
        $criteria->compare('property.zip',$this->property_zip,true);
        if($this->date)
        {
            $criteria->addBetweenCondition('date', date('Y-m-d',strtotime($this->date)) , date('Y-m-d',strtotime($this->date . ' + 1 day')) );
        }
        
        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array(
                    'id' => CSort::SORT_DESC,
                    'date' => CSort::SORT_DESC
                ),
                'attributes' => array(
                    'user_name' => array(
                        'asc' => '[user].name',
                        'desc' => '[user].name DESC'
                    ),
                    'client_name' => array(
                        'asc' => 'client.name',
                        'desc' => 'client.name DESC'
                    ),
                    'fire_name' => array(
                        'asc' => 'resFireName.Name',
                        'desc' => 'resFireName.Name DESC'
                    ),
                    'status_type' => array(
                        'asc' => 'status.status',
                        'desc' => 'status.status DESC'
                    ),
                    'member_first_name' => array(
                        'asc' => 'member.first_name',
                        'desc' => 'member.first_name DESC'
                    ),
                    'member_last_name' => array(
                        'asc' => 'member.last_name',
                        'desc' => 'member.last_name DESC'
                    ),
                    'property_address_1' => array(
                        'asc' => 'property.address_line_1',
                        'desc' => 'property.address_line_1 DESC'
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
                    '*'
                ),
            ),
            'criteria' => $criteria,
            'pagination' => array('PageSize' => 20)
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return WdsfireEnrollments the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function afterFind()
    {
        if ($this->user)
            $this->user_name = $this->user->name;
        
        if ($this->client)
            $this->client_name = $this->client->name;
        
        if ($this->resFireName)
            $this->fire_name = $this->resFireName->Name;
        
        if ($this->status)
            $this->status_type = $this->status->status;
        
        if ($this->property)
        {
            $this->property_address_1 = $this->property->address_line_1;
            $this->property_city = $this->property->city;
            $this->property_state = $this->property->state;
            $this->property_zip = $this->property->zip;
            if ($this->property->member)
            {
                $this->member_first_name = $this->property->member->first_name;
                $this->member_last_name = $this->property->member->last_name;
            }
        }
        
        return parent::afterFind();
    }
}
