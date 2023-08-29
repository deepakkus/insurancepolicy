<?php

/**
 * This is the model class for table "res_daily".
 *
 * The followings are the available columns in table 'res_daily':
 * @property integer $id
 * @property string $map_image
 * @property string $monitor_path
 * @property integer $monitored
 * @property integer $fires_triggered
 * @property integer $fires_responding
 * @property string $exposure
 * @property integer $policy_triggered
 * @property string $dedicated_path
 * @property integer $published
 * @property integer $response_enrolled
 * @property integer $client_id
 * @property string $date_created
 * @property string $date_updated
 * @property int $weather_file_id
 * @property int $danger_file_id
 * @property int $smoke_file_id
 * */
class ResDaily extends CActiveRecord
{
    public $clientName;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_daily';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('published', 'required'),
			array('monitored, fires_triggered, fires_responding, policy_triggered, published, exposure, response_enrolled, client_id ', 'numerical', 'integerOnly'=>true),
			array('map_image, monitor_path, dedicated_path', 'length', 'max'=>100),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, map_image, monitor_path, monitored, fires_triggered, fires_responding, exposure, policy_triggered, dedicated_path, published, response_enrolled, client_id, date_created, date_updated, clientName, weatherJsonName, dangerJsonName, smokeJsonName', 'safe', 'on'=>'search'),
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
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'weather_file' => array(self::BELONGS_TO, 'File', 'weather_file_id'),
            'danger_file' => array(self::BELONGS_TO, 'File', 'danger_file_id'),
            'smoke_file' => array(self::BELONGS_TO, 'File', 'smoke_file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'map_image' => 'Map Image',
			'monitor_path' => 'Monitor Path',
			'monitored' => 'Fires Monitored',
			'fires_triggered' => 'Fires Triggering',
			'fires_responding' => 'Fires Responding',
			'exposure' => 'Total Exposure',
			'policy_triggered' => 'Policyholders Triggered',
			'dedicated_path' => 'Dedicated Path',
			'published' => 'Published',
			'response_enrolled' => 'Response Enrolled (YTD)',
			'client_id' => 'Client',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
            'weather_file_id' => 'Weather Hazards Layer',
            'danger_file_id' => 'Fire Danger Hazards Layer',
            'smoke_file_id' => 'Smoke Layer',
            'clientName'=>'Client Name'
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
		$criteria->compare('map_image',$this->map_image,true);
		$criteria->compare('monitor_path',$this->monitor_path,true);
		$criteria->compare('monitored',$this->monitored);
		$criteria->compare('fires_triggered',$this->fires_triggered);
		$criteria->compare('fires_responding',$this->fires_responding);
		$criteria->compare('exposure',$this->exposure,true);
		$criteria->compare('policy_triggered',$this->policy_triggered);
		$criteria->compare('dedicated_path',$this->dedicated_path,true);
		$criteria->compare('published',$this->published);
		$criteria->compare('response_enrolled',$this->response_enrolled);
		$criteria->compare('client_id',$this->client_id); 
        $criteria->compare('weather_file_id',$this->weather_file_id,true);
        $criteria->compare('danger_file_id',$this->danger_file_id,true);
        $criteria->compare('smoke_file_id',$this->smoke_file_id,true);
        
        if ($this->date_created)
            $criteria->addCondition("date_created >= '$this->date_created' and date_created < '" . date('Y-m-d', strtotime($this->date_created . ' + 1 day')) . "'");
        if ($this->date_updated)
            $criteria->addCondition("date_updated >= '$this->date_updated' and date_updated < '" . date('Y-m-d', strtotime($this->date_updated . ' + 1 day')) . "'");
        
        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
             'Pagination' => array (
                  'PageSize' => 25
              ),
             'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'client_name' => array(
                        'asc' => 'clientName',
                        'desc' => 'clientName DESC',
                    ),
                    '*'
                )
            )
        ));
        
	}
    
    //----------------------------------------------------Standard Yii--------------------------------------------------
    #region Standard Yii

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResDaily the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeSave()
	{
        // Setting Date Updated
        $this->date_updated = date('Y-m-d H:i');
        
        // Setting Date Created
        if ($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');

        return parent::beforeSave();
    } 
    
    protected function afterFind()
	{
        if ($this->client)
            $this->clientName = $this->client->name;
        
        return parent::afterFind();
    } 
    
    #endregion
    
    //-----------------------------------------------------Virtual Attributes -------------------------------------------------------------
    #region Virtual Attributes
    
    /**
     * Virtual attribute for Weather Json File - retreives the client name for the notice
     */
    
    public function getweatherJsonName()
    {
        if($this->weather_file_id)
            return $this->weather_file->name;
        else
            return 'No File'; //not sure this is necessary
    }
    
    /**
     * Virtual attribute for Weather Json File - retreives the client name for the notice
     */
    
    public function getdangerJsonName()
    {
        if($this->danger_file_id)
            return $this->danger_file->name;
        else
            return 'No File'; //not sure this is necessary
    }
    
    /**
     * Virtual attribute for Weather Json File - retreives the client name for the notice
     */
    
    public function getsmokeJsonName()
    {
        if($this->smoke_file_id)
            return $this->smoke_file->name;
        else
            return 'No File'; //not sure this is necessary
    }
    
    #endregion
    
    /**
     * Determine if the daily threat has been created for today
     * @return boolean
     */
    public static function isDailyPublished()
    {
        $today = date('Y-m-d');
        $daily = ResDaily::model()->countBySql("select * from res_daily where date_created >= :date", array(':date' => $today));
        $dailyPublished = ResDaily::model()->countBySql("select * from res_daily where published = 1 and date_created >= :date", array(':date' => $today));

        //No dailies created at all
        if($daily < 1){
            return false;
        }
        //Dailies created, but some or all are not published
        elseif($daily != $dailyPublished){
            return false;
        }
        //Created and published
        else{
            return true;
        }
    }
}
