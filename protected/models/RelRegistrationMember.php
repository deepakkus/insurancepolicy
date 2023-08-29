<?php

/**
 * This is the model class for table "rel_registration_member". 
 * 
 *
 * The followings are the available columns in table 'contact':
 * @property integer $id
 * @property integer $registration_code_id
 * @property integer $member_id
 * @property string $created_on
 * @property string $updated_on
 * @property integer $active
 * @property integer $deleted
 * //RELATIONS
 * @property RegistrationCode $registrationcode
 */

class RelRegistrationMember extends CActiveRecord
{
	/**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
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
		return 'rel_registration_member';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
			array('id, is_active', 'required'),
            array('id', 'numerical', 'integerOnly'=>true),
			array('id, registration_code_id, member_id, created_on, updated_on, is_active, is_deleted','safe', 'on'=>'search'),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		return array(
			'user' => array(self::HAS_ONE, 'RegistrationCode', 'registration_code_id'),
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'registration_code_id' => 'Registration Code ID'
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
        $criteria->compare('is_deleted',$this->deleted);
        $criteria->compare('registration_code_id',$this->registration_code_id);
		$criteria->compare('code', $this->client_id, true);
		$dataProvider = new CActiveDataProvider($this, array('criteria'=>$criteria));

		return $dataProvider;
	}
}