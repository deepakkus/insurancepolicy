<?php

/**
 * This is the model class for table "geog_counties".
 *
 * The followings are the available columns in table 'geog_counties':
 * @property integer $id
 * @property string $name
 * @property integer $state_id
 * @property string $geog
 */
class GeogCounties extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'geog_counties';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, state_id, geog', 'required'),
			array('state_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>50),
			array('id, name, state_id, geog', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'state' => array(self::BELONGS_TO, 'GeogStates', 'state_id')
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
			'state_id' => 'State',
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
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('state_id',$this->state_id);
		$criteria->compare('geog',$this->geog,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return GeogCounties the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
