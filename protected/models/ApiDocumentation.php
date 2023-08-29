<?php

/**
 * This is the model class for table "api_documentation".
 *
 * The followings are the available columns in table 'api_documentation':
 * @property integer $id
 * @property string $name
 * @property string $docs
 * @property integer $active
 */
class ApiDocumentation extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'api_documentation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
			array('active', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
			array('docs', 'safe'),
			// The following rule is used by search().
			array('name, active', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'docs' => 'Docs',
			'active' => 'Active'
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
		$criteria = new CDbCriteria;

		$criteria->compare('name',$this->name,true);
		$criteria->compare('active',$this->active);

		return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes'=>array('*')
            ),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 10)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ApiDocumentation the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
