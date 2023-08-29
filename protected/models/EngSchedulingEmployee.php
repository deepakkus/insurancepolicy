<?php

/**
 * This is the model class for table "eng_scheduling_employee".
 *
 * The followings are the available columns in table 'eng_scheduling_employee':
 * @property integer $id
 * @property integer $crew_id
 * @property string $scheduled_type
 * @property string $start_date
 * @property string $end_date
 * @property integer $engine_scheduling_id
 */
class EngSchedulingEmployee extends CActiveRecord
{
    public $crew_last_name;
    public $crew_first_name;
    public $engine_boss;
    public $start_time;
    public $end_time;
    
    // Boolean to track if crew member is still actively scheduled
    public $on_engine;
    
    // Related Scheduled Engine
    public $engine_name;
    public $engine_city;
    public $engine_state;
    public $engine_assignment;
    
    // Related Fire Name
    public $fire_name;
    
    // Reporting Variables
    public $daycount;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'eng_scheduling_employee';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('start_date, end_date, engine_scheduling_id, start_time, end_time, crew_id, scheduled_type', 'required'),
			array('crew_id, engine_scheduling_id', 'numerical', 'integerOnly'=>true),
            array('scheduled_type', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, crew_id, scheduled_type, start_date, end_date, engine_scheduling_id, engine_name, engine_city, engine_state, engine_assignment, crew_last_name, crew_first_name, fire_name', 'safe', 'on'=>'search'),
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
			'engineScheduling' => array(self::BELONGS_TO, 'EngScheduling', 'engine_scheduling_id'),
            'crew' => array(self::BELONGS_TO, 'EngCrewManagement', 'crew_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'crew_id' => 'Crew Member',
            'scheduled_type' => 'Scheduled Type',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'engine_scheduling_id' => 'Engine Scheduling',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'engine_name' => 'Engine Name',
            'crew_last_name' => 'Last Name',
            'crew_first_name' => 'First Name',
            'engine_boss' => 'Scheduled as Engine Boss',
            'on_engine' => 'On Engine',
            'daycount' => 'Day Count',
            'fire_name' => 'Fire'
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
	public function search($engine_scheduling_id)
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
        
        $criteria->with = array('engineScheduling','engineScheduling.engine','crew');

		$criteria->compare('id',$this->id);
		$criteria->compare('crew_id',$this->crew_id);
        $criteria->compare('scheduled_type',$this->scheduled_type,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('engine_scheduling_id',$this->engine_scheduling_id);
        $criteria->compare('engine.engine_name',$this->engine_name,true);
        $criteria->compare('crew.last_name',$this->crew_last_name,true);
        $criteria->compare('crew.first_name',$this->crew_first_name,true);
        
        $criteria->addCondition("engine_scheduling_id = $engine_scheduling_id");
        
		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder'=>array('id'=>CSort::SORT_ASC),
				'attributes' => array(
                    'engine_name' => array(
                        'asc' => 'engine.engine_name',
                        'desc' => 'engine.engine_name DESC'
                    ),
                    'crew_last_name' => array(
                        'asc' => 'crew.last_name',
                        'desc' => 'crew.last_name DESC'
                    ),
                    'crew_first_name' => array(
                        'asc' => 'crew.first_name',
                        'desc' => 'crew.first_name DESC'
                    ),
                    '*',
				),
			),
			'criteria'=>$criteria,
            'pagination' => array('PageSize'=>20)
		));
	}
    
    public function searchCrewAnalytics($crewID, $startdate, $enddate)
    {
        $enddate1 = date_create($enddate)->modify('+1 day')->format('Y-m-d');
        
        $criteria = new CDbCriteria;
        
        $criteria->with = array('engineScheduling','engineScheduling.engine','engineScheduling.fire');
        
        $criteria->compare('id',$this->id);
		$criteria->compare('crew_id',$this->crew_id);
        $criteria->compare('scheduled_type',$this->scheduled_type,true);
		$criteria->compare('t.start_date',$this->start_date,true);
		$criteria->compare('t.end_date',$this->end_date,true);
		$criteria->compare('engine_scheduling_id',$this->engine_scheduling_id);
        $criteria->compare('engineScheduling.city',$this->engine_city,true);
        $criteria->compare('engineScheduling.state',$this->engine_state,true);
        $criteria->compare('engineScheduling.assignment',$this->engine_assignment,true);
        $criteria->compare('engine.engine_name',$this->engine_name,true);
        $criteria->compare('fire.Name',$this->fire_name,true);
        
        $criteria->select = array(
            't.id',
            'crew_id',
            'scheduled_type',
            't.start_date',
            't.end_date',
            'engine_scheduling_id',
            'engineScheduling.city as engine_city',
            'engineScheduling.state as engine_state',
            'engineScheduling.assignment as engine_assignment',
            'engine.engine_name as engine_name',
            'fire.Name as fire_name',
            "DATEDIFF(day,
                CASE WHEN t.start_date < '$startdate' THEN '$startdate' ELSE t.start_date END,
                CASE WHEN t.end_date > '$enddate1' THEN '$enddate1' ELSE t.end_date END
            ) AS daycount"
        );
        
        $criteria->addCondition("(t.start_date BETWEEN '$startdate' AND '$enddate') 
            OR (t.end_date BETWEEN '$startdate' AND '$enddate') 
            OR ('$startdate' BETWEEN FORMAT(t.start_date,'yyyy-MM-dd') AND t.end_date)
            OR ('$enddate' BETWEEN FORMAT(t.start_date,'yyyy-MM-dd') AND t.end_date)");
        
        //$criteria->addBetweenCondition('t.start_date' ,$startdate, $enddate);
        //$criteria->addBetweenCondition('t.end_date', $startdate, $enddate, 'OR');
        
        if ($crewID)
            $criteria->addCondition("crew_id = $crewID");
        else
            $criteria->addCondition('crew_id = 0');
        
		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_ASC),
				'attributes' => array(
                    'engine_name' => array(
                        'asc' => 'engine.engine_name',
                        'desc' => 'engine.engine_name DESC'
                    ),
                    'engine_city' => array(
                        'asc' => 'engineScheduling.city',
                        'desc' => 'engineScheduling.city DESC'
                    ),
                    'engine_state' => array(
                        'asc' => 'engineScheduling.state',
                        'desc' => 'engineScheduling.state DESC'
                    ),
                    'engine_assignment' => array(
                        'asc' => 'engineScheduling.assignment',
                        'desc' => 'engineScheduling.assignment DESC'
                    ),
                    'fire_name' => array(
                        'asc' => 'fire.Name',
                        'desc' => 'fire.Name DESC'
                    ),
                    '*',
				),
			),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 20)
		));
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EngSchedulingEmployee the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeSave()
    {
        if (isset($_POST['EngSchedulingEmployee']['start_time']))
        {
            $start_date_str = $this->start_date . ' ' . $_POST['EngSchedulingEmployee']['start_time'];
            $this->start_date = Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm', $start_date_str);
        }
        
        if (isset($_POST['EngSchedulingEmployee']['end_time']))
        {
            $end_date_str = $this->end_date . ' ' . $_POST['EngSchedulingEmployee']['end_time'];
            $this->end_date = Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm', $end_date_str);
        }
        
        return parent::beforeSave();
    }
    
    protected function afterFind()
    { 
        $this->on_engine = (date_create('now') < date_create($this->end_date)) ? true : false;
        
        $this->start_time = Yii::app()->dateFormatter->format('HH:mm', $this->start_date);
        $this->start_date = Yii::app()->dateFormatter->format('MM/dd/yyyy', $this->start_date);
        
        $this->end_time = Yii::app()->dateFormatter->format('HH:mm', $this->end_date);
        $this->end_date = Yii::app()->dateFormatter->format('MM/dd/yyyy', $this->end_date);
        
        $this->engine_boss = $this->scheduled_type === 'ENGB' ? true : false;
        
        if ($this->engineScheduling && $this->engineScheduling->engine)
            $this->engine_name = $this->engineScheduling->engine->engine_name;
        
        if ($this->engineScheduling)
        {
            $this->engine_city = $this->engineScheduling->city;
            $this->engine_state = $this->engineScheduling->state;
            $this->engine_assignment = $this->engineScheduling->assignment;
        }
        
        if ($this->crew)
        {
            $this->crew_last_name = $this->crew->last_name;
            $this->crew_first_name = $this->crew->first_name;
        }
        
        return parent::afterFind();
    }
    
    //-----------------------------------------------------General Functions -------------------------------------------------------------
    #region General Functions
    
    public function getEngineCrewMembers()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'id,first_name,last_name,crew_type';
        $criteria->order = 'last_name, crew_type asc';
        return CHtml::listData(EngCrewManagement::model()->findAll($criteria),'id', function($data) {
            return $data->crew_type . ' - ' . $data->last_name . ', ' . $data->first_name;
        });
    }
    
    public function getScheduledCrewTypes()
    {
        return array(
            'FFT' => 'FFT',
            'ENGB' => 'ENGB',
            'STLD' => 'STLD',
            'Advanced' => 'Advanced'
        );
    }
    
    #endregion
}
