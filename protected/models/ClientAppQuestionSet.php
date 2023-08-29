<?php

/**
 * This is the model class for table "client_app_question_set".
 *
 * The followings are the available columns in table 'client_app_question_set':
 * @property integer $id
 * @property string $name
 * @property string $client_id
 * @property integer $active
 * @property integer $is_default
 * @property integer $default_level
 */
class ClientAppQuestionSet extends CActiveRecord
{
	/**
     * @return string the associated database table name
     */
	public function tableName()
	{
		return 'client_app_question_set';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
			array('name, client_id, active, is_default, default_level', 'required'),
			array('client_id, active, is_default, default_level', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>25),
			array('id, name, client_id, active, is_default, default_level', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'name' => 'Name',
			'client_id' => 'Client',
			'active' => 'Active',
            'is_default' => 'Default',
            'default_level' => 'Default Level',
		);
	}

	/**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
	public function search()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('active',$this->active);
        $criteria->compare('is_default', $this->is_default);
        $criteria->compare('default_level', $this->default_level, true);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    protected function beforeSave()
    {
        if(!isset($this->is_default))
            $this->is_default = 0;
        if(!isset($this->active))
            $this->active = 0;

        return parent::beforeSave();
    }

	/**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return AppSetting the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
