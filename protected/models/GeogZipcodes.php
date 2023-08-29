<?php

/**
 * This is the model class for table "geog_zipcodes".
 *
 * The followings are the available columns in table 'geog_zipcodes':
 * @property integer $id
 * @property string $name
 * @property string $zipcode
 * @property integer $state_id
 * @property string $geog
 */
class GeogZipcodes extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'geog_zipcodes';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, zipcode, state_id, geog', 'required'),
			array('state_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>50),
			array('zipcode', 'length', 'max'=>30),
			array('id, name, zipcode, state_id, geog', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'state' => array(self::BELONGS_TO, 'GeogStates', 'state_id'),
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
			'zipcode' => 'Zipcode',
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
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('zipcode',$this->zipcode,true);
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
	 * @return GeogZipcodes the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    /**
     * Returns geojson feature collection of zipcodes given a perimeterID
     * @param integer $perimeterID 
     * @return array
     */
    public function getNoticeGeoJson($perimeterID)
    {
        $zipcodes = GIS::getPerimeterZipcodes(null, $perimeterID);
        
        $feature_collection = array(
            'type' => 'FeatureCollection',
            'features' => array()  
        );

        foreach ($zipcodes as $zipcode)
        {
            $feature_collection['features'][] = array(
                'type' => 'Feature',
                'geometry' => json_decode(GIS::convertWkbToGeoJson($zipcode['geog'])),
                'properties' => array(
                    'zipcode' => $zipcode['zipcode']
                )
            );
        }
        
        return $feature_collection;
    }
}
