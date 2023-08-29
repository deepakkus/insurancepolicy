<?php

/**
 * This is the model class for table "properties_file".
 *
 * The followings are the available columns in table 'properties_file':
 * @property integer $id
 * @property integer $property_pid
 * @property integer $file_id
 * @property string $notes
 *
 * The followings are the available model relations:
 * @property File $file
 * @property Property $property
 */
class PropertiesFile extends CActiveRecord
{
    public $upload;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'properties_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(

            array('upload', 'required', 'on' => 'insert'),
            array('upload', 'required', 'except' => 'update'),
			array('property_pid, file_id', 'required'),
			array('property_pid, file_id', 'numerical', 'integerOnly' => true),
            array('notes', 'length', 'max' => 255),
			// The following rule is used by search().
			array('id, property_pid, file_id', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'file' => array(self::BELONGS_TO, 'File', 'file_id'),
			'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'property_pid' => 'PID',
			'file_id' => 'File ID',
            'notes' => 'Notes'
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
		$criteria->compare('property_pid', $this->property_pid);
		$criteria->compare('file_id', $this->file_id);
        $criteria->compare('notes', $this->notes, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PropertiesFile the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
