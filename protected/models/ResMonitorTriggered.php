<?php

/**
 * This is the model class for table "res_monitor_triggered".
 *
 * The followings are the available columns in table 'res_monitor_triggered':
 * @property integer $id
 * @property integer $monitor_id
 * @property string $enrolled
 * @property string $eligible
 * @property string $closest
 * @property string $direction
 * @property integer $client_id
 * @property integer $noteworthy
 * @property integer $unmatched
 * @property integer $unmatched_enrolled
 * @property integer $unmatched_not_enrolled
 * @property string $closest_response_status
 */
class ResMonitorTriggered extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_monitor_triggered';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('monitor_id, client_id, unmatched, unmatched_enrolled, unmatched_not_enrolled', 'numerical', 'integerOnly'=>true),
			array('closest', 'length', 'max'=>5),
            array('enrolled, eligible,', 'length', 'max'=>6),
			array('direction', 'length', 'max'=>3),
            array('closest_response_status', 'length', 'max'=>25),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, monitor_id, enrolled, eligible, closest, direction, client_id, noteworthy, unmatched, unmatched_enrolled, unmatched_not_enrolled, closest_response_status', 'safe', 'on'=>'search'),
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
            'resMonitorLog' => array(self::BELONGS_TO, 'ResMonitorLog', 'monitor_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id')
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'monitor_id' => 'Monitor',
			'enrolled' => 'Enrolled',
			'eligible' => 'Eligible',
			'closest' => 'Closest',
			'direction' => 'Direction',
			'client_id' => 'Client',
            'unmatched' => 'Unmatched',
            'unmatched_enrolled' => 'Unmatched Enrolled',
            'unmatched_not_enrolled' => 'Unmatched Not Enrolled',
            'closest_response_status' => 'Closest Status'
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
		$criteria->compare('monitor_id',$this->monitor_id);
		$criteria->compare('enrolled',$this->enrolled);
		$criteria->compare('eligible',$this->eligible);
		$criteria->compare('closest',$this->closest,true);
		$criteria->compare('direction',$this->direction,true);
		$criteria->compare('client_id',$this->client_id);
        $criteria->compare('unmatched',$this->unmatched);
        $criteria->compare('unmatched_enrolled',$this->unmatched_enrolled);
        $criteria->compare('unmatched_not_enrolled',$this->unmatched_not_enrolled);
        $criteria->compact('closest_response_status',$this->closest_response_status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResMonitorTriggered the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
