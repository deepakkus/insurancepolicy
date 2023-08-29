<?php

/**
 * This is the model class for table "eng_engines".
 *
 * The followings are the available columns in table 'eng_engines':
 * @property integer $id
 * @property string $engine_name
 * @property string $make
 * @property string $model
 * @property string $vin
 * @property string $plates
 * @property string $type
 * @property string $comment
 * @property integer $availible
 * @property string $reason
 * @property integer $engine_source
 * @property integer $alliance_id
 * @property string $date_created
 * @property string $date_updated
 * @property string $date_email
 * @property integer $active
 */
class EngEngines extends CActiveRecord
{
    public $alliance_partner;
    
    const ENGINE_SOURCE_WDS = 1;
    const ENGINE_SOURCE_ALLIANCE = 2;
    const ENGINE_SOURCE_RENTAL = 3;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'eng_engines';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('engine_name', 'required'),
			array('availible, engine_source, alliance_id, active', 'numerical', 'integerOnly'=>true),
			array('engine_name, plates, type', 'length', 'max'=>20),
			array('date_created, date_updated, date_email, active', 'safe'),
			array('make, model', 'length', 'max'=>50),
			array('vin', 'length', 'max'=>30),
            array('comment, reason', 'length', 'max'=>200),
			array('id, engine_name, make, model, vin, plates, type, comment, availible, reason, engine_source, alliance_id, alliance_partner, date_created, date_updated, date_email, active', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'alliancepartner' => array(self::BELONGS_TO, 'Alliance', 'alliance_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'engine_name' => 'Engine Name',
			'make' => 'Make',
			'model' => 'Model',
			'vin' => 'Vin',
			'plates' => 'Plates',
            'type' => 'Engine Type',
            'comment' => 'Comment',
            'availible' => 'Available',
            'reason' => 'Availability Reason',
			'engine_source' => 'Engine Source',
            'alliance_id' => 'Alliance Partners',
            'alliance_partner' => 'Alliance Partner',
            'date_created'=>'Date Created',
            'date_updated'=>'Date Updated',
            'date_email' => 'Email Date',
            'active' => 'Active'
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
		$criteria = new CDbCriteria;

        $criteria->with = array('alliancepartner');
            
		$criteria->compare('id',$this->id);
		$criteria->compare('engine_name',$this->engine_name,true);
		$criteria->compare('make',$this->make,true);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('vin',$this->vin,true);
		$criteria->compare('plates',$this->plates,true);
        $criteria->compare('type',$this->type,true);
        $criteria->compare('comment',$this->comment);
        $criteria->compare('availible',$this->availible);
        $criteria->compare('reason',$this->reason);
		$criteria->compare('engine_source',$this->engine_source);
        $criteria->compare('alliance_id',$this->alliance_id);
        $criteria->compare('t.active',$this->active);
        $criteria->compare('alliancepartner.name',$this->alliance_partner,true);

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array(
                    'alliance_partner' => array(
                        'asc' => 'alliancepartner.name ASC',
                        'desc' => 'alliancepartner.name DESC'
                    ),
                    '*'
				)
			),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EngEngines the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeSave()
    {
        if ($this->engine_source != self::ENGINE_SOURCE_ALLIANCE)
            $this->alliance_id = null;
        
        if($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');
        
        $this->date_updated = date('Y-m-d H:i');

        return parent::beforeSave();
    }
    
    protected function afterFind()
    {
        if ($this->engine_source == self::ENGINE_SOURCE_ALLIANCE)
        {
            if ($this->alliancepartner)
                $this->alliance_partner = $this->alliancepartner->name;
        }
        
        $this->date_created = date('Y-m-d H:i', strtotime($this->date_created));
        $this->date_updated = date('Y-m-d H:i', strtotime($this->date_updated));
    
        return parent::afterFind();
    }
    
    //-----------------------------------------------------General Functions -------------------------------------------------------------
    #region General Functions
    
    public function getAlliancePartners()
    {
        return CHtml::listData(Alliance::model()->findAll(),'id','name');
    }
    
    public function getEngineTypes()
    {
        return array(
            'Water Tender' => 'Water Tender',
            'Type 3 Engine' => 'Type 3 Engine',
            'Type 4 Engine' => 'Type 4 Engine',
            'Type 5 Engine' => 'Type 5 Engine',
            'Type 6 Engine' => 'Type 6 Engine',
            'Type 7 Engine' => 'Type 7 Engine',
            'Command Vehicle' => 'Command Vehicle'
        );
    }
    
    public function getEngineSources()
    {
        return array(
            self::ENGINE_SOURCE_WDS => 'WDS',
            self::ENGINE_SOURCE_ALLIANCE => 'Alliance',
            self::ENGINE_SOURCE_RENTAL => 'Rental'
        );
    }
    
    #endregion
    
    //-----------------------------------------------------Virtual Attributes -------------------------------------------------------------
    #region Virtual Attributes
    
    /**
     * Virtual attribute for Client Name - retreives the client name for the notice
     */
    
    public function getEngineSource()
    {
        if ($this->engine_source == self::ENGINE_SOURCE_WDS) { return 'WDS'; }
        if ($this->engine_source == self::ENGINE_SOURCE_ALLIANCE) { return 'Alliance'; }
        if ($this->engine_source == self::ENGINE_SOURCE_RENTAL) { return 'Rental'; }
    }
    
    #endregion
}
