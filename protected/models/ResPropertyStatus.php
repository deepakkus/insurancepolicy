<?php

/**
 * This is the model class for table "res_property_status".
 *
 * The followings are the available columns in table 'res_property_status':
 * @property integer $id
 * @property integer $res_triggered_id
 * @property integer $engine_id
 * @property string $division
 * @property string $status
 * @property string $actions
 * @property integer $has_photo
 * @property string $other_issues
 * @property string $date_visited
 */
class ResPropertyStatus extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_property_status';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that will receive user inputs.
		return array(
			array('res_triggered_id, engine_id, has_photo', 'numerical', 'integerOnly'=>true),
			array('division, status', 'length', 'max'=>25),
			array('actions', 'length', 'max'=>512),
			array('other_issues', 'length', 'max'=>1024),
			array('date_visited', 'safe'),
			// The following rule is used by search(). Please remove those attributes that should not be searched.
			array('id, res_triggered_id, engine_id, division, status, actions, has_photo, other_issues, date_visited', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'triggered' => array(self::BELONGS_TO, 'ResTriggered', 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'res_triggered_id' => 'Res Triggered',
			'engine_id' => 'Engine ID',
			'division' => 'Division',
			'status' => 'Status',
			'actions' => 'Actions',
			'has_photo' => 'Has Photo',
			'other_issues' => 'Other Issues',
			'date_visited' => 'Date Visited',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('res_triggered_id',$this->res_triggered_id);
		$criteria->compare('engine_id',$this->engine_id);
		$criteria->compare('division',$this->division,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('actions',$this->actions,true);
		$criteria->compare('has_photo',$this->has_photo);
		$criteria->compare('other_issues',$this->other_issues,true);
		$criteria->compare('date_visited',$this->date_visited,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResPropertyStatus the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
    {
        if (empty($this->actions))
			$this->actions = null;
        
        if (empty($this->other_issues))
            $this->other_issues = null;
        
        if (empty($this->division))
            $this->division = null;
        
        if (empty($this->status))
            $this->status = null;
        
        if (empty($this->date_visited))
            $this->date_visited = null;
        
        return parent::beforeSave();
    }
}
