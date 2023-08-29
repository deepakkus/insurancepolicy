<?php

/**
 * This is the model class for table "fs_report_text", which stores canned/default entries
 * for sections of each condition of a returned assessment.
 *
 * The followings are the available columns in table 'fs_report_text':
 * @property integer $id
 * @property integer $condition_num
 * @property string $response
 * @property string $risk_level
 * @property string $type
 * @property string $text

 */
class FSReportText extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return fs the static model class
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
		return 'fs_report_text';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('condition_num, response, risk_level, type, text', 'safe'),
			array('condition_num, response, risk_level, type', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, condition_num, response, risk_level, type, text', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
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
			'condition_num' => 'Condition #',
			'response' => 'Response',
			'risk_level' => 'Risk Level',
			'type' => 'Type',
			'text' => 'Text',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('condition_num',$this->condition_num);
		$criteria->compare('response',$this->response, true);
		$criteria->compare('risk_level', $this->risk_level, true);
		$criteria->compare('type', $this->type, true);
		$criteria->compare('text', $this->text, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function getTypes()
	{
		return array('After Review' => 'After Review', 'Good News' => 'Good News', 'Remember' => 'Remember', 'Did You Know?' => 'Did You Know?', 'Recommendation' => 'Recommendation', 'Example' => 'Example', 'Header'=>'Header');
	}
}