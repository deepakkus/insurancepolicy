<?php

/**
 * This is the model class for table "res_dedicated_hours".
 *
 * The followings are the available columns in table 'res_dedicated_hours':
 * @property integer $id
 * @property integer $client_id
 * @property string $dedicated_hours
 * @property string $dedicated_start_date
 * @property string $notes
 */
class ResDedicatedHours extends CActiveRecord
{
    public $client_name;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_dedicated_hours';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('client_id', 'required'),
            array('client_id', 'numerical', 'integerOnly'=>true),
            array('dedicated_hours', 'numerical', 'integerOnly'=>false),
			array('dedicated_hours', 'length', 'max'=>8),
            array('notes', 'length', 'max'=>300),
			array('dedicated_start_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, client_id, notes, dedicated_hours, dedicated_start_date, client_name', 'safe', 'on'=>'search'),
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
            'dedicated' => array(self::HAS_MANY, 'ResDedicated', 'hours_id')
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
            'notes' => 'Notes',
			'dedicated_hours' => 'Dedicated Hours',
			'dedicated_start_date' => 'Dedicated Start Date',
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
        
        $criteria->with = array('client');

		$criteria->compare('dedicated_hours',$this->dedicated_hours,true);
        $criteria->compare('client.name',$this->client_name, true);
        
        if ($this->dedicated_start_date)
        {
            $criteria->addCondition("dedicated_start_date >= '$this->dedicated_start_date'");
            $criteria->addCondition("dedicated_start_date < '" . date('Y-m-d', strtotime($this->dedicated_start_date . ' + 1 day')) . "'");
        }

		return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder'=>array('id'=>CSort::SORT_DESC),
                'attributes'=>array(
                    'client_name'=>array(
                        'asc'=>'client.name',
                        'desc'=>'client.name DESC',
                    ),
                    '*'
                ),
            ),
			'criteria'=>$criteria,
            'pagination' => array('PageSize'=>10)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResDedicatedHours the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function afterFind()
    {
        if ($this->client)
            $this->client_name = $this->client->name;
        
        return parent::afterFind();
    }
}
