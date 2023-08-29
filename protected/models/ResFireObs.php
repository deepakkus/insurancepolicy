<?php

/**
 * This is the model class for table "res_fire_obs".
 *
 * The followings are the available columns in table 'res_fire_obs':
 * @property integer $Obs_ID
 * @property integer $Fire_ID
 * @property string $Wind_Dir
 * @property string $Wind_Speed
 * @property string $Gust
 * @property string $Precip
 * @property integer $Temp
 * @property integer $Humidity
 * @property string $Rating
 * @property string $Fx_Wind_Dir
 * @property string $Fx_Wind_Speed
 * @property string $Fx_Gust
 * @property string $Fx_Precip
 * @property integer $Fx_Temp
 * @property integer $Fx_Humidity
 * @property string $Fx_Rating
 * @property string $Fx_Time
 * @property string $Size
 * @property integer $Containment
 * @property string $Behavior
 * @property string $Growth_Potential
 * @property string $Supression
 * @property integer $Red_Flags
 * @property string $Date
 * @property string $Time
 * @property string $date_created
 * @property string $date_updated
 */
class ResFireObs extends CActiveRecord
{
    public $fire_name;
    public $fire_state;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_fire_obs';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('Fire_ID', 'required'),
            array('Temp, Rating, Fx_Temp, Fx_Rating, Fx_Time, Behavior, Growth_Potential', 'safe'),
            array('Fire_ID, Temp, Humidity, Fx_Temp, Fx_Humidity, Red_Flags', 'numerical', 'integerOnly' => true),
            array('Size','numerical','integerOnly'=>false),
            array('Wind_Dir, Fx_Wind_Dir', 'length', 'max'=>3),
            array('Containment', 'length', 'max'=>7),
            array('Wind_Speed, Fx_Wind_Speed', 'length', 'max'=>6),
            array('Gust, Fx_Gust', 'length', 'max'=>25),
            array('Precip, Fx_Precip', 'length', 'max'=>35),
            array('Rating, Fx_Rating, Date', 'length', 'max'=>10),
            array('Fx_Time, Behavior, Growth_Potential', 'length', 'max'=>15),
            array('Supression', 'length', 'max'=>500),
            array('Time', 'length', 'max'=>12),
            array('date_created, date_updated', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('Obs_ID, Fire_ID, Wind_Dir, Wind_Speed, Gust, Precip, Temp, Humidity, Rating, Fx_Wind_Dir, Fx_Wind_Speed, Fx_Gust, Fx_Precip, Fx_Temp, Fx_Humidity, Fx_Rating, Fx_Time, Size, Containment, Behavior, Growth_Potential, Supression, Red_Flags, Date, Time, date_created, date_updated, fire_name, fire_state', 'safe', 'on'=>'search'),
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
            'resFireName' => array(self::BELONGS_TO, 'ResFireName', 'Fire_ID'),
            'resFireStatus' => array(self::BELONGS_TO, 'ResFireStatus', 'Fire_ID'),
            'resMonitorLog' => array(self::HAS_MANY, 'ResMonitorLog', 'Obs_ID'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'Obs_ID' => 'Obs',
            'Fire_ID' => 'Fire',
            'Wind_Dir' => 'Wind Dir',
            'Wind_Speed' => 'Wind Speed',
            'Gust' => 'Gust',
            'Precip' => 'Precip',
            'Temp' => 'Temperature',
            'Humidity' => 'Humidity',
            'Rating' => 'Weather Rating',
            'Fx_Wind_Dir' => 'Fx Wind Dir',
            'Fx_Wind_Speed' => 'Fx Wind Speed',
            'Fx_Gust' => 'Fx Gust',
            'Fx_Precip' => 'Fx Precip',
            'Fx_Temp' => 'Fx Temperature',
            'Fx_Humidity' => 'Fx Humidity',
            'Fx_Rating' => 'Rating',
            'Fx_Time' => 'Time',
            'Size' => 'Size (acres)',
            'Containment' => 'Containment',
            'Behavior' => 'Behavior',
            'Growth_Potential' => 'Growth Potential',
            'Supression' => 'Suppression',
            'Red_Flags' => 'Red Flags',
            'Date' => 'Date',
            'Time' => 'Time',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
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

        $criteria->with = array(
            'resFireName' => array(
                'select' => array(
                    'Fire_ID',
                    'Name',
                    'City',
                    'State',
                    'Coord_Lat',
                    'Coord_Long'
                )
            )
        );

        $criteria->select = array(
            'Obs_ID',
            'Fire_ID',
            'Temp',
            'Humidity',
            'Rating',
            'Size',
            'Containment',
            'Supression',
            'Red_Flags',
            'date_created',
            'date_updated'
        );
       
        $criteria->compare('Temp', $this->Temp);
        
        if(strtolower($this->Size) === 'unknown')
        {
            $criteria->compare('Size', -1);
        }
        elseif(($this->Size) === '-1')
        {
            $criteria->compare('Size', ' ');
        }
        else
        {
            $criteria->compare('Size', $this->Size,true);
        }
        $criteria->compare('Humidity', $this->Humidity);
        $criteria->compare('Rating', $this->Rating, true);
       if(strtolower($this->Containment) === 'unknown')
       {
           $criteria->compare('Containment', -1);
       }
       elseif(($this->Containment) == -1)
       {
           $criteria->compare('Containment', ' ',true);
       }
       else
       {
           $criteria->compare('Containment', $this->Containment,true);
       }
        
        $criteria->compare('Supression', $this->Supression, true);
        $criteria->compare('Red_Flags', $this->Red_Flags);
        $criteria->compare('date_created', $this->date_created, true);
        $criteria->compare('date_updated', $this->date_updated, true);
        $criteria->compare('resFireName.Name', $this->fire_name, true);
        $criteria->compare('t.Fire_ID', $this->Fire_ID, true);
        $criteria->compare('resFireName.State', $this->fire_state, true);

        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('Obs_ID' => CSort::SORT_DESC),
                'attributes' => array(
                    'fire_name' => array(
                        'asc' => 'resFireName.Name',
                        'desc' => 'resFireName.Name DESC',
                    ),
                    'fire_state'=>array(
                        'asc' => 'resFireName.State',
                        'desc' => 'resFireName.State DESC',
                    ),
                    '*'
                ),
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    //----------------------------------------------------Standard Yii--------------------------------------------------
    #region Standard Yii

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResFireObs the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function afterFind() {
        //Convert the
        if($this->Size == -1)
            $this->Size = 'Unknown';
        if($this->Containment == -1)
            $this->Containment = 'Unknown';

        parent::afterFind();
    }


    protected function beforeSave()
    {
        //If certain fields are empty than format them

        //-1 = unknown
        if(empty($this->Size) || $this->Size == 'Unknown')
            $this->Size = -1;

        //-1 = unknown
        if(!strlen(trim($this->Containment)) || $this->Containment == 'Unknown')
            $this->Containment = -1;

        //New record so set the created field
        if($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');

        //For new or update record set the updated field
        $this->date_updated = date('Y-m-d H:i');

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        if ($this->Containment == '100' && $this->resFireName && !$this->resFireName->Contained)
        {
            Yii::app()->user->setFlash('error', 'Be sure to check this fire as contained <b><a style="color: blue;" href="' .
                                                 CController::createUrl('/resFireName/update', array('id'=>$this->Fire_ID)) . '">EDIT FIRE HERE</a></b>');
        }

        return parent::afterSave();
    }

    #endregion

    //---------------------------------------------------------------Virtual Attributes ------------------------------------------
    #region Virtual Attributes

    /**
     * Virtual attribute for Fire Name (from the resFireName model)
     */
    public function getFire_Name()
    {
        if($this->resFireName)
            return $this->resFireName->Name;
    }

    /**
     * Virtual attribute for Fire Name (from the resFireName model)
     */
    public function getFire_State()
    {
        if($this->resFireName)
            return $this->resFireName->State;
    }

    #endregion

    //------------------------------------------------------------General Functions ---------------------------------------------------
   #region General Functions

    /**
     * Returns an array of the wind directions used in the forms when creating/updating a set of fire obs
     * for current and forecast wind
     * @return associative array
     */

    public function getWindDirections()
    {
        return array(
            ""=>"",
            "N"=>"N",
            "NNE"=>"NNE",
            "NE"=>"NE",
            "ENE"=>"ENE",
            "E"=>"E",
            "ESE"=>"ESE",
            "SE"=>"SE",
            "SSE"=>"SSE",
            "S"=>"S",
            "SSW"=>"SSW",
            "SW"=>"SW",
            "WSW"=>"WSW",
            "W"=>"W",
            "WNW"=>"WNW",
            "NW"=>"NW",
            "NNW"=>"NNW"
        );
    }

    /**
     * Returns an array of the fire behavior ratings used in the forms when creating/updating a set of fire obs
     * for current conditions
     * @return associative array
     */
    public function getBehaviorRatings()
    {
        return array(
            "Low"=>"Low",
            "Moderate"=>"Moderate",
            "Active"=>"Active",
            "Very Active"=>"Very Active",
            "Extreme"=>"Extreme"
            );
    }

    /**
     * Returns an array of the growth potential ratings used in the forms when creating/updating a set of fire obs
     * for current conditions
     * @return associative array
     */
    public function getGrowthPotentialRatings()
    {
        return array(
            "Low"=>"Low",
            "Moderate"=>"Moderate",
            "High"=>"High"
            );
    }

    /**
     * Returns an array of the weather ratings used in the forms when creating/updating a set of fire obs
     * for current or forecast conditions
     * @return associative array
     */
    public function getWeatherRatings()
    {
        return array(
            "low"=>"low",
            "moderate"=>"moderate",
            "high"=>"high",
            "extreme"=>"extreme"
        );
    }

    /**
     * Returns an array of the weather timeframes used in the forms when creating/updating a set of fire obs
     * for current or forecast conditions
     * @return associative array
     */
    public function getForecastPeriod()
    {
        return array(
            "Today"=>"Today",
            "Tomorrow"=>"Tomorrow"
        );
    }

    //Returns the desired fields from the current observations data from the NOAA weather station
    public function getCurrentObs($obs)
    {
        $returnArray =array();
        $keys = array('name', 'elev', 'latitude', 'longitude', 'Temp', 'Dewp', 'Relh', 'Winds', 'Windd', 'Gust', 'Weather', 'Visibility', 'WindChill' );

        foreach($obs as $key=>$value)
        {
            if(in_array($key, $keys))
                $returnArray[$key]=$value;
        }

        return $returnArray;
    }

    #endregion

}
