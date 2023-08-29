<?php

/**
 * This is the model class for table "service_offering". 
 *
 * The followings are the available columns in table 'contact':
 * @property integer $service_offering_id
 * @property string $service_offering_name
 * @property string $service_offering_code
 * //RELATIONS
 * @property  */

class ServiceOffering extends CActiveRecord
{
	/**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return serviceOffering the static model class
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
		return 'service_offering';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
			array('service_offering_id, service_offering_name, service_offering_code', 'required'),
            array('service_offering_id', 'numerical', 'integerOnly'=>true),
			array('service_offering_id, service_offering_name, service_offering_code','safe', 'on'=>'search'),
		);

	}

	

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'service_offering_id' => 'Service Offering Id',
            'service_offering_code' => 'Service Offering Code',
            'service_offering_name' => 'Service Offering Name'
		);
	}

	/**
     * Retrieves a list of user clients based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('service_offering_id',$this->service_offering_id);
		$criteria->compare('service_offering_code',$this->service_offering_code);
		$criteria->compare('service_offering_name', $this->service_offering_name, true);
		
        $dataProvider = new CActiveDataProvider($this, array('criteria'=>$criteria));

		return $dataProvider;
	}
}