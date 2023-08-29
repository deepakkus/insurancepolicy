<?php

/**
 * This is the model class for table "res_fuel".
 *
 * The followings are the available columns in table 'res_fuel':
 * @property integer $ID
 * @property integer $Fire_ID
 * @property integer $fuel_type_id
 */
class ResFuel extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_fuel';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Fire_ID, fuel_type_id', 'required'),
			array('Fire_ID, fuel_type_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('ID, Fire_ID, fuel_type_id', 'safe', 'on'=>'search'),
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
            'ResFireName' => array(self::BELONGS_TO, 'ResFireName', 'Fire_ID'),
			'resFuelType' => array(self::BELONGS_TO, 'ResFuelType', 'fuel_type_id'),
            'resFireObs' => array(self::BELONGS_TO, 'ResFireObs', 'Fire_ID')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'ID' => 'ID',
			'Fire_ID' => 'Fire',
			'fuel_type_id' => 'Fuel Type',
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

		$criteria->compare('ID',$this->ID);
		$criteria->compare('Fire_ID',$this->Fire_ID);
		$criteria->compare('fuel_type_id',$this->fuel_type_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResFuel the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
