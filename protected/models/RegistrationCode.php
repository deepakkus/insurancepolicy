<?php

/**
 * This is the model class for table "registration_code". This is the model that connects the User to one registration code
 * It has a one to one relationship with Registration table via foriegn key
 *
 * The followings are the available columns in table 'contact':
 * @property integer $id
 * @property string $code
 * @property integer $active
 * //RELATIONS
 * @property User $user
 */

class RegistrationCode extends CActiveRecord
{
	/**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return registrationCode the static model class
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
		return 'registration_code';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
			array('id, code, is_active', 'required'),
            array('id', 'numerical', 'integerOnly'=>true),
			array('id, code, is_active','safe', 'on'=>'search'),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		return array(
			'user' => array(self::HAS_ONE, 'User', 'registration_code_id'),
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'code' => 'Code'
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
		$criteria->compare('code', $this->client_id, true);
		$dataProvider = new CActiveDataProvider($this, array('criteria'=>$criteria));

		return $dataProvider;
	}
}