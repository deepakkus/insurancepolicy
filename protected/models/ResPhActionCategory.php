<?php

/**
 * This is the model class for table "res_ph_action_category".
 *
 * The followings are the available columns in table 'res_ph_action_category':
 * @property integer $id
 * @property string $category
 *
 * The followings are the available model relations:
 * @property ResPhActionType[] $phActionTypes
 */
class ResPhActionCategory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_ph_action_category';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('category', 'required'),
			array('category', 'length', 'max' => 100),
			array('category', 'safe', 'on' => 'search')
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'phActionTypes' => array(self::HAS_MANY, 'ResPhActionType', 'category_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category' => 'Category',
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
        $sort = new CSort;
        $sort->defaultOrder = array('category' => CSort::SORT_ASC);
        $sort->attributes = array('*');
        
        return new CActiveDataProvider($this, array(
             'sort' => $sort
        ));
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResPhActionCategory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
