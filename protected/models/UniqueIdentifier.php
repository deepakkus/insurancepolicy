<?php

/**
 * This is the model class for table "unique_identifier". 
 * 
 *
 * The followings are the available columns in table 'unique_identifier':
 * @property integer $id
 * @property integer $unique_guid
 * @property integer $user_type_id
 * @property integer $is_active
 * 
 */

class UniqueIdentifier extends CActiveRecord
{
	/**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return uniqueIdentifier the static model class
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
		return 'unique_identifier';
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
		return array(
			array('id, unique_guid, user_type_id, is_active', 'required'),
            //array('unique_guid', 'unique'),
            array('id, user_type_id', 'numerical', 'integerOnly'=>true),
			array('id, unique_guid, is_active, user_type_id','safe', 'on'=>'search'),
		);
	}

	
	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'unique_guid' => 'Unique Guid',
            'user_type_id' => 'User Type ID',
            'is_active' => 'Active'
		);
	}

	/**
     * Retrieves a list of user clients based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('is_active',$this->active);
		$criteria->compare('unique_guid', $this->unique_guid, true);
        $criteria->compare('user_type_id', $this->user_type_id, true);
		$dataProvider = new CActiveDataProvider($this, array('criteria'=>$criteria));

		return $dataProvider;
	}
}