<?php

/**
 * This is the model class for table "agent".
 *
 * The followings are the available columns in table 'agent':
 * @property integer $id
 * @property string $agent_num
 * @property string $first_name
 * @property string $last_name
 * @property string $fs_carrier_key
 * @property int $client_id
 * @property string $agent_type
 *
 * The followings are the available model relations:
 * @property Property[] $properties
 */
class Agent extends CActiveRecord
{
    const AGENT_TYPE_WDSED = 'wdsed';
    const AGENT_TYPE_OTHER = 'other';

    public $agent_client_name;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'agent';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, last_name', 'required'),
			array('id, client_id', 'numerical', 'integerOnly'=>true),
			array('agent_num, first_name, last_name', 'length', 'max'=>50),
			array('fs_carrier_key, agent_type', 'length', 'max'=>25),
            array('fs_carrier_key', 'checkUniqueRegCode'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, agent_num, first_name, last_name, fs_carrier_key, agent_client_name, client_id, agent_type', 'safe', 'on'=>'search'),
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
			'properties' => array(self::HAS_MANY, 'Properties', 'agent_id'),
            'agent_properties' => array(self::HAS_MANY, 'AgentProperty', 'agent_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'agent_num' => 'Agent Number',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'fs_carrier_key' => 'Registration Code',
            'agent_client_name' => 'Client',
            'client_id' => 'Client ID',
            'agent_type' => 'Agent Type'
		);
	}

    /**
     * validation rule for making sure reg code is unique across both mem and agent
     * @param string $attribute the reg code (fs_carrier_key)
     */
    public function checkUniqueRegCode($attribute)
    {
        $member = Member::model()->findByAttributes(array('fs_carrier_key' => $this->$attribute));
        $agent = Agent::model()->findByAttributes(array('fs_carrier_key' => $this->$attribute));
        if(isset($member) || (isset($agent) && $agent->id != $this->id))
            $this->addError($attribute, 'Your reg code must be unique.');
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
	public function search($pageSize = 25, $sort = NULL)
	{
		$criteria = new CDbCriteria;
        $criteria->with = array('client');
        $criteria->together = true;

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.agent_num',$this->agent_num,true);
		$criteria->compare('t.first_name',$this->first_name,true);
		$criteria->compare('t.last_name',$this->last_name,true);
		$criteria->compare('t.fs_carrier_key',$this->fs_carrier_key,true);
        $criteria->compare('t.agent_type',$this->agent_type,true);
        $criteria->compare('client_id', $this->client_id);
        $criteria->compare('client.name', $this->agent_client_name, true);

        $sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

        $dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
				'attributes'=>array(
					'client_name'=>array(
						'asc'=>'client.name',
						'desc'=>'client.name DESC',
					),
					'*',
				),
			),
			'criteria'=>$criteria,
		));

		if($pageSize == NULL)
		{
			$dataProvider->pagination = false;
		}
		else
		{
			$dataProvider->pagination->pageSize = $pageSize;
			$dataProvider->pagination->validateCurrentPage = false;
		}
        
        return $dataProvider;
	}

    /**
     * This method is invoked after each record is instantiated by a find method.
     */
    protected function afterFind() 
    {
        if (isset($this->client)) 
        {
            $this->agent_client_name = $this->client->name;
        }
        
        parent::afterFind();
    }

	protected function beforeSave()
	{
        if (empty($this->fs_carrier_key))
			$this->fs_carrier_key = FSUser::model()->createUniqueRegCode();

        if (isset($this->client)) 
        {
            $this->client_id = $this->client->id;
        }
        
		return parent::beforeSave();
	}    
    
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Agent the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Return an array of availible agent types
     * @return string[]
     */
    public static function agentTypes()
    {
        return array(
            self::AGENT_TYPE_WDSED => 'WDSed',
            self::AGENT_TYPE_OTHER => 'Other'
        );
    }
}
