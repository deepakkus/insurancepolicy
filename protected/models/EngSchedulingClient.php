<?php

/**
 * This is the model class for table "eng_scheduling_client".
 *
 * The followings are the available columns in table 'eng_scheduling_client':
 * @property integer $id
 * @property integer $client_id
 * @property integer $engine_scheduling_id
 * @property string $start_date
 * @property string $end_date
 */
class EngSchedulingClient extends CActiveRecord
{
    public $client_name;
    public $start_time;
    public $end_time;
    // Boolean to track if client is still schedule on this engine
    public $client_scheduled;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'eng_scheduling_client';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('client_id, engine_scheduling_id, start_date, end_date', 'required'),
			array('client_id, engine_scheduling_id', 'numerical', 'integerOnly' => true),
            array('start_time, end_time', 'safe'),

            //array('client_id','each'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, client_id, engine_scheduling_id, start_date, end_date, client_name', 'safe', 'on'=>'search'),
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
            'engineScheduling' => array(self::BELONGS_TO, 'EngScheduling', 'engine_scheduling_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'client_id' => 'Client',
			'engine_scheduling_id' => 'Engine Scheduling',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
            'client_name' => 'Client Name',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'client_scheduled' => 'Client Currently Scheduled'
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

        $criteria->with = array('client');

		$criteria->compare('id',$this->id);
		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
        $criteria->compare('client.name',$this->client_name,true);

        $criteria->addCondition("engine_scheduling_id = $engine_scheduling_id");

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_ASC),
				'attributes' => array(
                    'client_name' => array(
                        'asc' => 'client.name',
                        'desc' => 'client.name DESC'
                    ),
                    '*'
				)
			),
			'criteria'=>$criteria,
            'pagination' => array('PageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EngSchedulingClient the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
    {
        if (isset($_POST['EngSchedulingClient']['start_time']))
        {
            $start_date_str = $this->start_date . ' ' . $_POST['EngSchedulingClient']['start_time'];
            $this->start_date = Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm', $start_date_str);
        }

        if (isset($_POST['EngSchedulingClient']['end_time']))
        {
            $end_date_str = $this->end_date . ' ' . $_POST['EngSchedulingClient']['end_time'];
            $this->end_date = Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm', $end_date_str);
        }

        return parent::beforeSave();
    }

    protected function afterFind()
    {
        $this->client_scheduled = (date_create('now') < date_create($this->end_date)) ? true : false;

        $this->start_time = Yii::app()->dateFormatter->format('HH:mm', $this->start_date);
        $this->start_date = Yii::app()->dateFormatter->format('MM/dd/yyyy', $this->start_date);

        $this->end_time = Yii::app()->dateFormatter->format('HH:mm', $this->end_date);
        $this->end_date = Yii::app()->dateFormatter->format('MM/dd/yyyy', $this->end_date);

        if ($this->client)
            $this->client_name = $this->client->name;

        return parent::afterFind();
    }

    //-----------------------------------------------------General Functions -------------------------------------------------------------
    #region General Functions

    /**
     * Get available clients for engine scheduling client dropdown
     * @param integer $fireID ID of the assigned fire
     * @param string $assignment Scheduling model assignment type
     * @return  Client[]
     */
    public function getAvailibleClients($fireID ,$assignment)
    {
        $retval = null;
        if ($assignment === EngScheduling::ENGINE_ASSIGNMENT_RESPONSE && !empty($fireID))
        {
            // Getting an array of distinct client_ids that have had notice on the assigned fire_id
            $sql = 'SELECT DISTINCT client_id FROM res_notice WHERE fire_id = :fire_id';
            $uniqueFireClientIDs = Yii::app()->db->createCommand($sql)->queryColumn(array(':fire_id' => $fireID));

            $criteria = new CDbCriteria;
            $criteria->addInCondition('id', $uniqueFireClientIDs);
            $criteria->addCondition('wds_fire = 1');
            $criteria->order = 'name ASC';

            $retval = Client::model()->findAll($criteria);
        }
        else
        {
            $retval = Client::model()->findAll(array(
                'condition' => 'wds_fire = 1',
                'order' => 'name ASC'
            ));
        }

        return $retval;
    }

    #endregion
}
