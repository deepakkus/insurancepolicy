<?php

/**
 * This is the model class for table "model_history".
 *
 * The followings are the available columns in table 'model_history':
 * @property integer $id
 * @property integer $user_id
 * @property string $date
 * @property string $table
 * @property integer $table_pk
 * @property string $data
 */
class ModelHistory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'model_history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('user_id, date, table, table_pk, data', 'required'),
			array('user_id, table_pk', 'numerical', 'integerOnly' => true),
			array('table', 'length', 'max' => 50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, date, table, table_pk, data', 'safe', 'on' => 'search'),
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
			'user_id' => 'User',
			'date' => 'Date',
			'table' => 'Table',
			'table_pk' => 'Table PK',
			'data' => 'Data',
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
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('date', $this->date, true);
		$criteria->compare('table', $this->table, true);
		$criteria->compare('table_pk', $this->table_pk);
		$criteria->compare('data', $this->data, true);

		return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*')
            ),
			'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ModelHistory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
