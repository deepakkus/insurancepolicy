<?php

/**
 * This is the model class for table "geog_states".
 *
 * The followings are the available columns in table 'geog_states':
 * @property integer $id
 * @property string $name
 * @property string $abbr
 * @property string $geog
 */
class GeogStates extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'geog_states';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, abbr, geog', 'required'),
			array('name', 'length', 'max'=>50),
			array('abbr', 'length', 'max'=>2),
			array('id, name, abbr, geog', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'counties' => array(self::HAS_MANY, 'GeogCounties', 'state_id'),
            'zipcodes' => array(self::HAS_MANY, 'GeogZipcodes', 'state_id')
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
			'abbr' => 'Abbr',
			'geog' => 'Geog',
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
		$criteria->compare('abbr',$this->abbr,true);
		$criteria->compare('geog',$this->geog,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return GeogStates the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
