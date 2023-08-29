<?php

/**
 * This is the model class for table "res_daily_threat".
 *
 * The followings are the available columns in table 'res_daily_threat':
 * @property integer $threat_id
 * @property string $southwest
 * @property string $california_south
 * @property string $california_north
 * @property string $wgb
 * @property string $egb
 * @property string $rocky_mountains
 * @property string $northern_rockies
 * @property string $northwest
 * @property string $fx_southwest
 * @property string $fx_california_south
 * @property string $fx_california_north
 * @property string $fx_wgb
 * @property string $fx_egb
 * @property string $fx_rocky_mountains
 * @property string $fx_northern_rockies
 * @property string $fx_northwest
 * @property string $details
 * @property string $date_created
 * @property string $date_updated
 * @property string $panhandle
 * @property string $matanuska
 * @property string $fx_panhandle
 * @property string $fx_matanuska
 * @property string $southern
 * @property string $fx_southern
 *
 * @property string $great_basin
 * @property string $fx_great_basin
 * @property string $alaska
 * @property string $fx_alaska
 * @property string $eastern
 * @property string $fx_eastern
 */
class ResDailyThreat extends CActiveRecord
{
	/**
     * @return string the associated database table name
     */
	public function tableName()
	{
		return 'res_daily_threat';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('southwest, california_south, california_north, rocky_mountains, northern_rockies, northwest, fx_southwest, fx_california_south, fx_california_north, fx_rocky_mountains, fx_northern_rockies, fx_northwest, details, southern, fx_southern, great_basin, fx_great_basin, alaska, fx_alaska, eastern, fx_eastern', 'required'),
			array('southwest, california_south, california_north, wgb, egb, rocky_mountains, northern_rockies, northwest, fx_southwest, fx_california_south, fx_california_north, fx_wgb, fx_egb, fx_rocky_mountains, fx_northern_rockies, fx_northwest, panhandle, matanuska, fx_panhandle, fx_matanuska, great_basin, fx_great_basin, alaska, fx_alaska, eastern, fx_eastern', 'length', 'max'=>10),
            array('wgb, egb, fx_wgb, fx_egb, panhandle, matanuska, fx_panhandle, fx_matanuska', 'default', 'value'=>''),
			array('details', 'length', 'max'=>1000),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('threat_id, southwest, california_south, california_north, wgb, egb, rocky_mountains, northern_rockies, northwest, fx_southwest, fx_california_south, fx_california_north, fx_wgb, fx_egb, fx_rocky_mountains, fx_northern_rockies, fx_northwest, details, date_created, date_updated, panhandle, matanuska, fx_panhandle, fx_matanuska, southern, fx_southern', 'safe', 'on'=>'search'),
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
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'threat_id' => 'Threat',
			'southwest' => 'Southwest',
			'california_south' => 'S. California',
			'california_north' => 'N. California',
			'wgb' => 'W. Great Basin',
			'egb' => 'E. Great Basin',
			'rocky_mountains' => 'Rocky Mountains',
			'northern_rockies' => 'N. Rockies',
			'northwest' => 'Northwest',
			'fx_southwest' => 'Fx Southwest',
			'fx_california_south' => 'Fx S. California',
			'fx_california_north' => 'Fx N. California',
			'fx_wgb' => 'Fx W. Great Basin',
			'fx_egb' => 'Fx E. Great Basin',
			'fx_rocky_mountains' => 'Fx Rocky Mountains',
			'fx_northern_rockies' => 'Fx N. Rockies',
			'fx_northwest' => 'Fx Northwest',
			'details' => 'Details',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
            'panhandle' => 'Panhandle',
            'matanuska' => 'Matanuska Valley',
            'fx_panhandle' => 'Fx Panhandle',
            'fx_matanuska' => 'Fx Matanuska Valley',
            'southern' => 'Southern',
            'fx_southern' => 'Fx Southern',
            'great_basin' => 'Great Basin',
            'fx_great_basin' => 'Fx Great Basin',
            'alaska' => 'Alaska',
            'fx_alaska' => 'Fx Alaska',
            'eastern' => 'Eastern',
            'fx_easterm' => 'Fx Eastern'
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

		$criteria->compare('threat_id',$this->threat_id);
		$criteria->compare('southwest',$this->southwest,true);
		$criteria->compare('california_south',$this->california_south,true);
		$criteria->compare('california_north',$this->california_north,true);
		$criteria->compare('wgb',$this->wgb,true);
		$criteria->compare('egb',$this->egb,true);
		$criteria->compare('rocky_mountains',$this->rocky_mountains,true);
		$criteria->compare('northern_rockies',$this->northern_rockies,true);
		$criteria->compare('northwest',$this->northwest,true);
		$criteria->compare('fx_southwest',$this->fx_southwest,true);
		$criteria->compare('fx_california_south',$this->fx_california_south,true);
		$criteria->compare('fx_california_north',$this->fx_california_north,true);
		$criteria->compare('fx_wgb',$this->fx_wgb,true);
		$criteria->compare('fx_egb',$this->fx_egb,true);
		$criteria->compare('fx_rocky_mountains',$this->fx_rocky_mountains,true);
		$criteria->compare('fx_northern_rockies',$this->fx_northern_rockies,true);
		$criteria->compare('fx_northwest',$this->fx_northwest,true);
		$criteria->compare('details',$this->details,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
        $criteria->compare('panhandle',$this->panhandle,true);
        $criteria->compare('matanuska',$this->matanuska,true);
        $criteria->compare('fx_panhandle',$this->fx_panhandle,true);
        $criteria->compare('fx_matanuska',$this->fx_matanuska,true);
        $criteria->compare('southern',$this->southern,true);
        $criteria->compare('fx_southern',$this->fx_southern,true);
        
        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
             'Pagination' => array (
                  'PageSize' => 25
              ),
             'sort' => array(
                'defaultOrder' => array(
                    'threat_id'=>CSort::SORT_DESC
                ),
                'attributes' => array(
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
     * @return ResDailyThreat the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeSave()
	{
        if($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');
        
        $this->date_updated = date('Y-m-d H:i');
        
        return parent::beforeSave();
    } 
    
    #endregion
    
    //----------------------------------------------------- General Functions -------------------------------------------------------------
    #region General Functions
    
    /**
     * Provides options for the dropdown used for fire danger rating by region
     */    
    public function getFireDangerRatings() 
    {
        return array(
            'low'=>'Low',
            'moderate'=>'Moderate',
            'high'=>'High',
            'extreme'=>'Extreme',
        );
    }
    
    /**
     * Get the color for the danger rating
     * @param string $rating 
     * @return mixed
     */
    public function getColorRating($rating)
    {
        if($rating == 'low')
            return "<span style='color:green;'>Low</span>";
        elseif($rating == 'moderate')
            return "<span style='color:#E8A317;'>Moderate</span>";
        elseif($rating == 'high')
            return "<span style='color: #F87217; font-weight:bold;'>HIGH</span>";
        elseif($rating == 'extreme')
            return "<span style='color:red; font-weight:bold;'>EXTREME</span>";
        else
            return '';
    }

    /**
     * Determine if the daily threat has been created for today
     * @return boolean
     */
    public static function isDailyThreat()
    {
        $today = date('Y-m-d');
        return (ResDailyThreat::model()->countBySql("select * from res_daily_threat where date_created >= :date", array(':date' => $today)) > 0) ? true : false;
    }
    
    #endregion
}
