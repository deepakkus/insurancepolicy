<?php

/**
 * This is the model class for table "country_code".
 *
 * The followings are the available columns in table 'country_code':
 * @property integer $id
 * @property string $country_name
 * @property string $country_code
 * @property string $iso
 
 */
class CountryCode extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'country_code';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            array('country_name, sms_enabled, country_code, iso', 'safe'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'country_name' => 'Country Name',
            'sms_enabled' => 'Sms Enabled',
            'country_code' => 'Country Code',
            'iso' => 'ISO'
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SystemSettings the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}
	/*Concatenate Country code with name
	* @return string
	*/
	public function getCountrywithcode()
	{
		return $this->country_code.' '.$this->country_name;
	}
}
