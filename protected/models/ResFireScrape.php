<?php

/**
 * This is the model class for table "res_fire_scrape".
 *
 * The followings are the available columns in table 'res_fire_scrape':
 * @property integer $id
 * @property string $acres
 * @property string $date
 * @property string $fuels
 * @property string $ic
 * @property string $inc_num
 * @property string $location
 * @property string $name
 * @property string $resources
 * @property string $type
 * @property string $web_comment
 * @property string $dispatch
 * @property string $state
 * @property string $date_created
 * @property string $point
 */
class ResFireScrape extends CActiveRecord
{
    public $lat;
    public $lon;
    public $viewed;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_fire_scrape';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*array('date, dispatch, state', 'required'),
			array('acres', 'length', 'max'=>5),
			array('fuels, ic', 'length', 'max'=>30),
            array('inc_num, name', 'length', 'max'=>60),
			array('resources', 'length', 'max'=>200),
			array('location, web_comment', 'length', 'max'=>120),
			array('type, state', 'length', 'max'=>20),
			array('dispatch', 'length', 'max'=>10),
			array('date_created, point', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, acres, date, fuels, ic, inc_num, location, name, resources, type, web_comment, dispatch, state, date_created, point', 'safe', 'on'=>'search'),*/
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'scrapeViewed' => array(self::BELONGS_TO, 'ResFireScrapeViewed', '', 'joinType' => 'LEFT JOIN', 'foreignKey' => array('inc_num'=>'inc_num'))
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'acres' => 'Acres',
			'date' => 'Date',
			'fuels' => 'Fuels',
			'ic' => 'Ic',
			'inc_num' => 'Inc #',
			'location' => 'Location',
			'name' => 'Name',
			'resources' => 'Resources',
			'type' => 'Type',
			'web_comment' => 'Web Comment',
			'dispatch' => 'Dispatch',
			'state' => 'State',
			'date_created' => 'Date Created',
			'point' => 'Point',
            
            // Virtual attributes
            'lat' => 'Lat',
            'lon' => 'Lon',
            'viewed' => 'Viewed'
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResFireScrape the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeFind()
    {
        // Need to convert geometry type to wkt so it doesn't come through as binary (problems with reading and writing)
        
        if(isset($this->dbCriteria) && isset($this->dbCriteria->select) && $this->dbCriteria->select == '*')
        {
            $columns = implode(',', array_keys($this->attributes));
            $select = str_replace('point', 'point.ToString() as point, point.Lat as lat, point.Long as lon',$columns);
            $this->dbCriteria->select = $select;
        }

        return parent::beforeFind();
    }
    
    protected function afterFind()
    {
        if ($this->acres == 0)
        {
            $this->acres = null;
        }
        
        if ($this->scrapeViewed)
        {
            $this->viewed = $this->scrapeViewed->viewed;
        }
    
        return parent::afterFind();
    }
}
