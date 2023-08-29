<?php

/**
 * This is the model class for table "res_triage_zone".
 *
 * The followings are the available columns in table 'res_triage_zone':
 * @property integer $id
 * @property integer $notice_id
 * @property string $date_created
 * @property string $date_updated
 *
 * The followings are the available model relations:
 * @property ResNotice $notice
 * @property ResTriageZoneArea[] $resTriageZoneAreas
 */
class ResTriageZone extends CActiveRecord
{
    public $noticeID;
    public $clientID;
    public $fireID;
    public $clientName;
    public $fireName;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_triage_zone';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('notice_id', 'required'),
            array('notice_id','unique', 'className' => __CLASS__, 'message' => 'Notice {value} for the ' . $this->fireName . ' has already been taken.'),
			array('notice_id', 'numerical', 'integerOnly' => true),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('notice_id, date_created, date_updated, clientName, fireName', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'notice' => array(self::BELONGS_TO, 'ResNotice', 'notice_id'),
			'resTriageZoneAreas' => array(self::HAS_MANY, 'ResTriageZoneArea', 'triage_zone_id'),
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
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',

            // Virtual Attributes
            'clientID' => 'Client',
            'fireID' => 'Fire',
            'clientName' => 'Client Name',
            'fireName' => 'Fire Name'
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

        $criteria->with = array('resTriageZoneAreas', 'notice.client', 'notice.fire');

		$criteria->compare('notice_id',$this->notice_id);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);

        $criteria->compare('client.name',$this->clientName,true);
        $criteria->compare('fire.Name',$this->fireName,true);

		return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'clientName' => array(
                        'asc' => 'client.name',
                        'desc' => 'client.name DESC'
                    ),
                    'fireName' => array(
                        'asc' => 'fire.Name',
                        'desc' => 'fire.Name DESC'
                    ),
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
	 * @return ResTriageZone the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
    {
        if ($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');

        $this->date_updated = date('Y-m-d H:i');

        return parent::beforeSave();
    }

    protected function afterFind()
    {
        if ($this->notice)
        {
            $this->noticeID = $this->notice->notice_id;

            if ($this->notice->fire)
            {
                $this->fireID = $this->notice->fire->Fire_ID;
                $this->fireName = $this->notice->fire->Name;
            }

            if ($this->notice->client)
            {
                $this->clientID = $this->notice->client->id;
                $this->clientName = $this->notice->client->name;
            }
        }
        
        return parent::afterFind();
    }

    public static function getResponseClients()
    {
        $response_clients = Client::model()->findAllByAttributes(array('wds_fire' => 1), array('order' => 'name ASC'));

        return CHtml::listData($response_clients, 'id', 'name');
    }

    public static function getDispatchedFires($clientID)
    {
        $response_notices = ResNotice::model()->findAllBySql('
            SELECT fire_id, client_id FROM res_notice WHERE notice_id IN (
	            SELECT MAX(notice_id) FROM res_notice WHERE client_id = :client_id GROUP BY client_id, fire_id
            ) AND wds_status = 1', array(
                ':client_id' => $clientID
            )
        );

        return CHtml::listData($response_notices, 'fire_id', 'fire_name');
    }

    public static function getDispatchedNotices($clientID, $fireID)
    {
        $dispatched_notices = ResNotice::model()->findAllByAttributes(array('client_id' => $clientID, 'fire_id' => $fireID), array('order' => 'notice_id DESC'));
        
        return CHtml::listData($dispatched_notices, 'notice_id', function($model) {
            return date('Y-m-d H:i', strtotime($model->date_created)) . ' - ' . $model->recommended_action;
        });
    }
}
