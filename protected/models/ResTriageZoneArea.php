<?php

/**
 * This is the model class for table "res_triage_zone_area".
 *
 * The followings are the available columns in table 'res_triage_zone_area':
 * @property integer $id
 * @property integer $triage_zone_id
 * @property string $geog
 * @property integer $notes
 *
 * The followings are the available model relations:
 * @property ResTriageZone $triageZone
 */
class ResTriageZoneArea extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_triage_zone_area';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('triage_zone_id, geog, notes', 'required'),
			array('triage_zone_id', 'numerical', 'integerOnly' => true),
            array('notes', 'length', 'max' => 200),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('triage_zone_id', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'triageZone' => array(self::BELONGS_TO, 'ResTriageZone', 'triage_zone_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'triage_zone_id' => 'Triage Zone',
			'geog' => 'Geog',
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
		$criteria=new CDbCriteria;

		$criteria->compare('triage_zone_id',$this->triage_zone_id);
        $criteria->compare('notes',$this->notes);

		return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    '*'
                ),
            ),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResTriageZoneArea the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeFind()
    {
        //Need to convert geometry type to wkt so it doesn't come through as binary (problems with reading and writing)
        if(isset($this->dbCriteria) && isset($this->dbCriteria->select) && $this->dbCriteria->select == '*')
        {
            $alias = $this->getTableAlias();
            $columnReplace = ($alias != 't') ? $alias . ".geog.ToString() as $alias.geog" : 't.geog.ToString() as geog';

            //Dynamically get all columns, than replace geog with the sql server function ToString() - wkt
            $columns = implode(',', array_keys($this->attributes));
            $select = str_replace("geog", $columnReplace,$columns);

            $this->dbCriteria->select = $select;
        }

        return parent::beforeFind();
    }

    protected function afterSave()
    {
        // Intersect with the corresponding triggered table entries and update the priority number

        $sql = "update 
                    res_triggered 
                set 
                    priority = :notes 
                where 
                    notice_id = (select notice_id from res_triage_zone where id = :triageZoneId) 
                   and geog.STIntersects((select geog from res_triage_zone_area where id = :id)) = 1";
            
        Yii::app()->db->createCommand($sql)
            ->bindValue(':id', $this->id, PDO::PARAM_INT)
            ->bindValue(':triageZoneId', $this->triage_zone_id, PDO::PARAM_INT)
            ->bindValue(':notes', $this->notes, PDO::PARAM_STR)
            ->execute();

        return parent::afterSave();
    }
}
