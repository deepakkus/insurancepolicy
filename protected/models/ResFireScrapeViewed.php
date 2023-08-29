<?php

/**
 * This is the model class for table "res_fire_scrape_viewed".
 *
 * The followings are the available columns in table 'res_fire_scrape_viewed':
 * @property integer $id
 * @property string $inc_num
 * @property integer $viewed
 */
class ResFireScrapeViewed extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_fire_scrape_viewed';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('viewed', 'required'),
            array('viewed', 'numerical', 'integerOnly'=>true),
			array('viewed', 'safe'),
			array('inc_num, viewed', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'inc_num' => 'Inc Num',
            'viewed' => 'Viewed'
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResFireScrape the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
