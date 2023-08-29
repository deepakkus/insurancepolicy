<?php

/**
 * This is the model class for table "properties_location_history".
 *
 * The followings are the available columns in table 'properties_location_history':
 * @property integer $id
 * @property integer $property_pid
 * @property string $wds_geocode_level
 * @property string $wds_lat
 * @property string $wds_long
 * @property string $wds_geocoder
 * @property string $wds_match_address
 * @property string $wds_match_score
 * @property string $wds_geocode_date
 *
 * The followings are the available model relations:
 * @property Property $property
 */
class PropertiesLocationHistory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'properties_location_history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('property_pid', 'required'),
			array('property_pid', 'numerical', 'integerOnly' => true),
			array('wds_geocode_level, wds_lat, wds_long, wds_geocoder', 'length', 'max' => 25),
			array('wds_match_address', 'length', 'max' => 200),
			array('wds_match_score', 'length', 'max' => 15),
			array('wds_geocode_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, property_pid, wds_geocode_level, wds_lat, wds_long, wds_geocoder, wds_match_address, wds_match_score, wds_geocode_date', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'id',
			'property_pid' => 'pid',
			'wds_geocode_level' => 'WDS Geocode Level',
			'wds_lat' => 'WDS Lat',
			'wds_long' => 'WDS Long',
			'wds_geocoder' => 'WDS Geocoder',
			'wds_match_address' => 'WDS Match Address',
			'wds_match_score' => 'WDS Match Score',
			'wds_geocode_date' => 'WDS Geocode Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
     *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('property_pid', $this->property_pid);
		$criteria->compare('wds_geocode_level', $this->wds_geocode_level, true);
		$criteria->compare('wds_lat', $this->wds_lat, true);
		$criteria->compare('wds_long', $this->wds_long, true);
		$criteria->compare('wds_geocoder', $this->wds_geocoder, true);
		$criteria->compare('wds_match_address', $this->wds_match_address, true);
		$criteria->compare('wds_match_score', $this->wds_match_score, true);
		$criteria->compare('wds_geocode_date', $this->wds_geocode_date, true);

        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder '=> array('id' => CSort::SORT_DESC),
                'attributes' => array('*'),
            ),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 10)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PropertiesLocationHistory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
