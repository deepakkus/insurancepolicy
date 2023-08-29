<?php

/**
 * This is the model class for table "res_fire_status".
 *
 * The followings are the available columns in table 'res_fire_status':
 * @property integer $id
 * @property integer $fire_id
 * @property integer $client_id
 * @property integer $status
 */
class ResFireStatus extends CActiveRecord
{    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_fire_status';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fire_id, client_id, status', 'required'),
			array('fire_id, client_id, status', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, fire_id, client_id, status', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'ResFireName' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'resFireObs' => array(self::HAS_MANY, 'ResFireObs', 'fire_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'fire_id' => 'Fire',
			'client_id' => 'Client',
			'status' => 'Status',
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
		$criteria->compare('fire_id',$this->fire_id);
		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResFireStatus the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
