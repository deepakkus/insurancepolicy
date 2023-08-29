<?php

/**
 * This is the model class for table "contact". This is the model that holds a dynamic amount of contacts for a related table
 * It has a many-to-one relationship with Properties table. Could be used with other tables if needed by adding a relational id field for that table
 *
 * The followings are the available columns in table 'contact':
 * @property integer $id
 * @property string $property_pid
 * @property string $type
 * @property string $priority
 * @property string $name
 * @property string $relationship
 * @property string $detail
 * @property string $notes 
 */

class Contact extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return contact the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'contact';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, name, detail', 'required'),
            array('property_pid', 'numerical', 'integerOnly'=>true),
			array('type, relationship, priority', 'length', 'max'=>50),
            array('name, detail', 'length', 'max'=>100),
            array('notes', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, property_pid, type, priority, name, relationship, detail, notes',
				'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'property' => array(self::BELONGS_TO, 'Property', 'pid'),
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
			'type' => 'Type',
			'name' => 'Name',
			'relationship' => 'Relationship',
            'priority' => 'Priority',
			'detail' => 'Detail',
			'notes' => 'Notes',
		);
	}

	/**
	 * Retrieves a list of contacts based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('property_pid',$this->property_pid);
		$criteria->compare('type', $this->type, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('relationship', $this->relationship, true);
		$criteria->compare('priority', $this->priority, true);
		$criteria->compare('detail', $this->detial, true);
		$criteria->compare('notes', $this->notes, true);
        
        $dataProvider = new CActiveDataProvider($this, array('criteria'=>$criteria));
		
		return $dataProvider;
	}
}