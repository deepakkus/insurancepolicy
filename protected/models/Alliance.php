<?php

/**
 * This is the model class for table "alliance".
 *
 * The followings are the available columns in table 'alliance':
 * @property integer $id
 * @property string $name
 * @property string $contact_first
 * @property string $contact_last
 * @property string $phone
 * @property string $phone_alt
 * @property string $email
 * @property string $preseason_agreement
 * @property integer $email_reminder
 * @property integer $active
 */
class Alliance extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'alliance';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
            array('email_reminder, active', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>40),
			array('contact_first, contact_last, phone, phone_alt', 'length', 'max'=>20),
            array('email', 'length', 'max'=>50),
			array('preseason_agreement', 'length', 'max'=>10),
			array('id, name, contact_first, contact_last, phone, phone_alt, email, preseason_agreement, email_reminder, active', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'engines' => array(self::HAS_MANY, 'EngEngines', 'alliance_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'contact_first' => 'Contact First',
			'contact_last' => 'Contact Last',
			'phone' => 'Phone',
            'phone_alt' => 'Alternate Phone',
            'email' => 'Email',
			'preseason_agreement' => 'Preseason Agreement',
            'active' => 'Active'
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
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('contact_first',$this->contact_first,true);
		$criteria->compare('contact_last',$this->contact_last,true);
		$criteria->compare('phone',$this->phone,true);
        $criteria->compare('phone_alt',$this->phone_alt,true);
        $criteria->compare('email',$this->email,true);
		$criteria->compare('preseason_agreement',$this->preseason_agreement,true);
        $criteria->compare('active',$this->active,true);
        $criteria->compare('email_reminder',$this->email_reminder,true);


		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*'),
			),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Alliance the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
