<?php

/**
 * This is the model class for table "res_evac_zone".
 *
 * The followings are the available columns in table 'res_evac_zone':
 * @property integer $id
 * @property integer $notice_id
 * @property string $geog
 * @property integer $notes
 *
 * The followings are the available model relations:
 * @property ResNotice $notice
 */
class ResEvacZone extends CActiveRecord
{
	/**
     * @return string the associated database table name
     */
	public function tableName()
	{
		return 'res_evac_zone';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
			array('notice_id, geog, notes', 'required'),
			array('notice_id', 'numerical', 'integerOnly' => true),
            array('notes', 'length', 'max' => 200),
			array('id, notice_id, notes', 'safe', 'on' => 'search'),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		return array(
			'notice' => array(self::BELONGS_TO, 'ResNotice', 'notice_id'),
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'notice_id' => 'Notice',
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

		$criteria->compare('notice_id',$this->notice_id);
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
     * @return ResEvacZone the static model class
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

    public static function getResponseClients()
    {
        $responseClients = Client::model()->findAllByAttributes(array('wds_fire' => 1), array('order' => 'name ASC'));
        $responseClientsList = CHtml::listData($responseClients, 'id', 'name');
        return $responseClientsList;
    }

    public static function getClientFires($clientID)
    {
        $clientNotices = ResNotice::model()->findAllBySql('
            SELECT fire_id, client_id FROM res_notice WHERE notice_id IN (
	            SELECT MAX(notice_id) FROM res_notice WHERE client_id = :client_id GROUP BY client_id, fire_id
            )', array(
                ':client_id' => $clientID
            )
        );
        $fires = CHtml::listData($clientNotices, 'fire_id', 'fire_name');
        return $fires;
    }

    public static function getClientFireNotices($clientID, $fireID, $exclude = array())
    {
        $notices = ResNotice::model()->findAll('client_id = :client_id AND fire_id = :fire_id AND notice_id NOT IN ( :exclude )', array(
                ':client_id' => $clientID, 
                ':fire_id' => $fireID, 
                ':exclude' => implode(',',$exclude)
            ),
            array('order' => 'notice_id DESC')
        );

        return CHtml::listData($notices, 'notice_id', function($model) {
            return date('Y-m-d H:i', strtotime($model->date_created)) . ' - ' . $model->recommended_action;
        });
    }
}
