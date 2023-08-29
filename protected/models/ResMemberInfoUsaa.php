<?php

/**
 * This is the model class for table "res_member_info_usaa".
 *
 * The followings are the available columns in table 'res_member_info_usaa':
 * @property integer $Action_ID
 * @property integer $Notice_ID
 * @property integer $Member_Num
 * @property string $Policy_Num
 * @property string $Member_Name
 * @property string $Member_Status
 * @property integer $Threat
 * @property string $Comments
 * @property string $Picture_Path
 * @property string $Picture_Path2
 * @property integer $Safe
 * @property integer $Damaged
 * @property string $Damaged_Date
 * @property integer $Lost
 * @property string $Lost_Date
 * @property integer $Fuel_Exterior
 * @property string $Fuel_Exterior_Date
 * @property integer $Fuel_Landscape
 * @property string $Fuel_Landscape_Date
 * @property integer $Fuel_Other
 * @property string $Fuel_Other_Date
 * @property integer $Doors
 * @property string $Doors_Date
 * @property integer $Windows
 * @property string $Windows_Date
 * @property integer $Attachments
 * @property string $Attachments_Date
 * @property integer $Assessing
 * @property string $Assessing_Date
 * @property integer $Photos
 * @property string $Photos_Date
 * @property integer $Examination
 * @property string $Examination_Date
 * @property integer $Interview
 * @property string $Interview_Date
 * @property string $Date_Added
 * @property string $Date_Updated
 * @property integer $property_pid
 */
class ResMemberInfoUsaa extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_member_info_usaa';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Notice_ID, Member_Num, Member_Name, Date_Added, Date_Updated, property_pid', 'required'),
			array('Notice_ID, Member_Num, Threat, Safe, Damaged, Lost, Fuel_Exterior, Fuel_Landscape, Fuel_Other, Doors, Windows, Attachments, Assessing, Photos, Examination, Interview, Property_PID', 'numerical', 'integerOnly'=>true),
			array('Policy_Num', 'length', 'max'=>5),
			array('Member_Name', 'length', 'max'=>25),
			array('Member_Status', 'length', 'max'=>13),
			array('Comments', 'length', 'max'=>3000),
			array('Picture_Path', 'length', 'max'=>75),
			array('Picture_Path2', 'length', 'max'=>100),
			array('Damaged_Date, Lost_Date, Fuel_Exterior_Date, Fuel_Landscape_Date, Fuel_Other_Date, Doors_Date, Windows_Date, Attachments_Date, Assessing_Date, Photos_Date, Examination_Date, Interview_Date', 'length', 'max'=>15),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('Action_ID, Notice_ID, Member_Num, Policy_Num, Member_Name, Member_Status, Threat, Comments, Picture_Path, Picture_Path2, Safe, Damaged, Damaged_Date, Lost, Lost_Date, Fuel_Exterior, Fuel_Exterior_Date, Fuel_Landscape, Fuel_Landscape_Date, Fuel_Other, Fuel_Other_Date, Doors, Doors_Date, Windows, Windows_Date, Attachments, Attachments_Date, Assessing, Assessing_Date, Photos, Photos_Date, Examination, Examination_Date, Interview, Interview_Date, Date_Added, Date_Updated, property_pid', 'safe', 'on'=>'search'),
		);
	}

    /**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'property' => array(self::BELONGS_TO, 'property', 'property_pid'),
            'resTriggered' => array(self::BELONGS_TO, 'ResTriggered', array('Notice_ID' => 'notice_id', 'property_pid' => 'property_pid')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'Action_ID' => 'Action',
			'Notice_ID' => 'Notice',
			'Member_Num' => 'Member Num',
			'Policy_Num' => 'Policy Num',
			'Member_Name' => 'Member Name',
			'Member_Status' => 'Member Status',
			'Threat' => 'Threat',
			'Comments' => 'Comments',
			'Picture_Path' => 'Picture Path',
			'Picture_Path2' => 'Picture Path2',
			'Safe' => 'Safe',
			'Damaged' => 'Damaged',
			'Damaged_Date' => 'Damaged Date',
			'Lost' => 'Lost',
			'Lost_Date' => 'Lost Date',
			'Fuel_Exterior' => 'Fuel Exterior',
			'Fuel_Exterior_Date' => 'Fuel Exterior Date',
			'Fuel_Landscape' => 'Fuel Landscape',
			'Fuel_Landscape_Date' => 'Fuel Landscape Date',
			'Fuel_Other' => 'Fuel Other',
			'Fuel_Other_Date' => 'Fuel Other Date',
			'Doors' => 'Doors',
			'Doors_Date' => 'Doors Date',
			'Windows' => 'Windows',
			'Windows_Date' => 'Windows Date',
			'Attachments' => 'Attachments',
			'Attachments_Date' => 'Attachments Date',
			'Assessing' => 'Assessing',
			'Assessing_Date' => 'Assessing Date',
			'Photos' => 'Photos',
			'Photos_Date' => 'Photos Date',
			'Examination' => 'Examination',
			'Examination_Date' => 'Examination Date',
			'Interview' => 'Interview',
			'Interview_Date' => 'Interview Date',
			'Date_Added' => 'Date Added',
			'Date_Updated' => 'Date Updated',
            'property_pid'=> 'Property PID'
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
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('Action_ID',$this->Action_ID);
		$criteria->compare('Notice_ID',$this->Notice_ID);
		$criteria->compare('Member_Num',$this->Member_Num);
		$criteria->compare('Policy_Num',$this->Policy_Num,true);
		$criteria->compare('Member_Name',$this->Member_Name,true);
		$criteria->compare('Member_Status',$this->Member_Status,true);
		$criteria->compare('Threat',$this->Threat);
		$criteria->compare('Comments',$this->Comments,true);
		$criteria->compare('Picture_Path',$this->Picture_Path,true);
		$criteria->compare('Picture_Path2',$this->Picture_Path2,true);
		$criteria->compare('Safe',$this->Safe);
		$criteria->compare('Damaged',$this->Damaged);
		$criteria->compare('Damaged_Date',$this->Damaged_Date,true);
		$criteria->compare('Lost',$this->Lost);
		$criteria->compare('Lost_Date',$this->Lost_Date,true);
		$criteria->compare('Fuel_Exterior',$this->Fuel_Exterior);
		$criteria->compare('Fuel_Exterior_Date',$this->Fuel_Exterior_Date,true);
		$criteria->compare('Fuel_Landscape',$this->Fuel_Landscape);
		$criteria->compare('Fuel_Landscape_Date',$this->Fuel_Landscape_Date,true);
		$criteria->compare('Fuel_Other',$this->Fuel_Other);
		$criteria->compare('Fuel_Other_Date',$this->Fuel_Other_Date,true);
		$criteria->compare('Doors',$this->Doors);
		$criteria->compare('Doors_Date',$this->Doors_Date,true);
		$criteria->compare('Windows',$this->Windows);
		$criteria->compare('Windows_Date',$this->Windows_Date,true);
		$criteria->compare('Attachments',$this->Attachments);
		$criteria->compare('Attachments_Date',$this->Attachments_Date,true);
		$criteria->compare('Assessing',$this->Assessing);
		$criteria->compare('Assessing_Date',$this->Assessing_Date,true);
		$criteria->compare('Photos',$this->Photos);
		$criteria->compare('Photos_Date',$this->Photos_Date,true);
		$criteria->compare('Examination',$this->Examination);
		$criteria->compare('Examination_Date',$this->Examination_Date,true);
		$criteria->compare('Interview',$this->Interview);
		$criteria->compare('Interview_Date',$this->Interview_Date,true);
		$criteria->compare('Date_Added',$this->Date_Added,true);
		$criteria->compare('Date_Updated',$this->Date_Updated,true);
        $criteria->compare('property_pid',$this->property_pid,true);
        
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResMemberInfoUsaa the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
