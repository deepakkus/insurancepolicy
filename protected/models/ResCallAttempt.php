<?php

/**
 * This is the model class for table "res_call_attempt".
 *
 * The followings are the available columns in table 'res_call_attempt':
 * @property integer $id
 * @property integer $res_fire_id
 * @property integer $property_id
 * @property integer $caller_user_id
 * @property integer $attempt_number
 * @property string $date_called
 * @property string $point_of_contact
 * @property string $point_of_contact_description
 * @property boolean $in_residence
 * @property boolean $evacuated
 * @property string $general_comments
 * @property string $dashboard_comments
 * @property integer $publish
 * @property integer $call_list_id
 * @property integer $platform
 * @property string $contact_type
 * @property string $prop_res_status
 */
class ResCallAttempt extends CActiveRecord
{
    public $caller_user_name;
    public $distance;
    public $threat;

    const PLATFORM_ADMIN = 1;
    const PLATFORM_WDS_FIRE = 2;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_call_attempt';
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
			array('res_fire_id, property_id, caller_user_id, attempt_number, date_called, call_list_id, contact_type', 'required'),
			array('res_fire_id, property_id, caller_user_id, attempt_number, publish, call_list_id, platform', 'numerical', 'integerOnly'=>true),
            array('contact_type', 'in', 'range'=>$this->getContactTypes()),
			array('point_of_contact', 'length', 'max'=>50),
			array('point_of_contact_description', 'length', 'max'=>128),
			array('general_comments, dashboard_comments', 'length', 'max'=>1024),
			array('in_residence, evacuated, contact_type, prop_res_status', 'safe'),

            // The following rule is used by search(). Remove those attributes that should not be searched.
			array('id, res_fire_id, property_id, caller_user_id, attempt_number, date_called, point_of_contact, '
            . 'point_of_contact_description, in_residence, evacuated, general_comments, dashboard_comments, caller_user_name, publish, call_list_id, contact_type, prop_res_status', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'caller_user' => array(self::BELONGS_TO, 'User', 'caller_user_id'),
            'property' => array(self::BELONGS_TO, 'Property', 'property_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'res_fire_id' => 'Res Fire',
			'property_id' => 'Property',
			'caller_user_id' => 'Caller',
			'attempt_number' => 'No.',
			'date_called' => 'Date Called',
			'point_of_contact' => 'Point Of Contact',
			'point_of_contact_description' => 'Description',
			'in_residence' => 'In Residence',
			'evacuated' => 'Evacuated',
			'general_comments' => 'General Comments (internal only)',
			'dashboard_comments' => 'Dashboard Comments (client facing)',
            'caller_user_name' => 'Caller',
            'publish' => 'Publish',
            'call_list_id' => 'Call List ID',
            'contact_type' => 'Contact Type',
            'prop_res_status' => 'Status Outcome'
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
	public function search($sort = NULL)
	{

		$criteria = new CDbCriteria;
        $criteria->with = array('caller_user');

		$criteria->compare('id',$this->id);
		$criteria->compare('res_fire_id',$this->res_fire_id);
		$criteria->compare('property_id',$this->property_id);
		$criteria->compare('caller_user_id',$this->caller_user_id);
		$criteria->compare('attempt_number',$this->attempt_number);
		$criteria->compare('date_called',$this->date_called,true);
		$criteria->compare('point_of_contact',$this->point_of_contact,true);
		$criteria->compare('point_of_contact_description',$this->point_of_contact_description,true);
		$criteria->compare('in_residence',$this->in_residence);
		$criteria->compare('evacuated',$this->evacuated);
		$criteria->compare('general_comments',$this->general_comments,true);
		$criteria->compare('dashboard_comments',$this->dashboard_comments,true);
        $criteria->compare('caller_user.name', $this->caller_user_name, true);
        $criteria->compare('call_list_id', $this->call_list_id);
        $criteria->compare('publish', $this->publish);
        $criteria->compare('contact_type',$this->contact_type,true);
        $criteria->compare('prop_res_status', $this->prop_res_status, true);

        $criteria->addCondition('platform = ' . self::PLATFORM_ADMIN);

        $sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array($sort => $sortWay),
            ),
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResCallAttempt the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function afterFind()
    {
        // Convert the date/time fields to display format.
        $format = 'm/d/Y g:i A';
        $this->date_called = date_format(new DateTime($this->date_called), $format);

        if (isset($this->caller_user))
        {
            $this->caller_user_name = $this->caller_user->name;
        }

        parent::afterFind();
    }

    protected function beforeSave()
	{
        if (is_null($this->publish) || $this->publish === '')
            $this->publish = 0;

		return parent::beforeSave();
	}

    /*
    *   return array of contact types
    */
    public static function getContactTypes()
    {
        return array(
            'Successful Contact (Enroll/Decline)',
            'Successful Contact (Undecided)',
            'Contact not Policyholder',
            'VM',
            'No answer/No VM',
            'Inbound',
            'Policyholder'
        );
    }
    /*
    * return array of contact types
    */
    public static function getAdminContactTypes()
    {
        return  array(
            'Policyholder', 
            'Contact not Policyholder',
            'VM','No answer/No VM',
            'Inbound'
        );
    }
}
