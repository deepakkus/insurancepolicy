<?php

/**
 * This is the model class for table "system_settings".
 *
 * The followings are the available columns in table 'system_settings':
 * @property integer $id
 * @property integer $max_login_attempts
 * @property string $announcements
 * @property string $support
 * @property string $api_md
 
 */
class SystemSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'system_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            array('max_login_attempts', 'required'),
			array('max_login_attempts', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 5),
            array('announcements, support, api_md', 'safe'),
			// The following rule is used by search().
			array('id, max_login_attempts', 'safe', 'on' => 'search'),
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
			'max_login_attempts' => 'Max Login Attempts',
            'announcements' => 'Announcements',
            'support' => 'Support',
            'api_md' => 'API Markdown'
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

		$criteria->compare('id', $this->id);
		$criteria->compare('max_login_attempts', $this->max_email_attempts);

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*')
			),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 10)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SystemSettings the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}
}
