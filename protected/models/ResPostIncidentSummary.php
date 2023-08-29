<?php

/**
 * This is the model class for table "res_post_incident_summary".
 *
 * The followings are the available columns in table 'res_post_incident_summary':
 * @property integer $id
 * @property integer $fire_id
 * @property integer $client_id
 * @property string $wds_actions
 * @property string $personel
 * @property integer $published
 * @property string $date_created
 * @property string $date_updated
 * @property datetime $fire_summary
 * @property string $date_access_gained
 * @property string $access_gained_comment
 * @property string $date_access_denied
 * @property string $access_denied_comment
 */
class ResPostIncidentSummary extends CActiveRecord
{
    public $fire_city;
    public $fire_state;
    public $fire_name;
    public $client_name;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_post_incident_summary';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('fire_id, client_id, published', 'numerical', 'integerOnly'=>true),
            array('wds_actions, fire_summary', 'length', 'max'=>3000),
            array('access_gained_comment, access_denied_comment', 'length', 'max'=>500),
            array('personel', 'length', 'max'=>200),
            array('date_created, date_updated, date_access_gained, date_access_denied,client_name,fire_city,fire_state,fire_name', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, fire_id, client_id, wds_actions, personel, published, date_created, date_updated, fire_summary, date_access_gained, date_access_denied,fire_name,fire_state,fire_city', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'fire' => array(self::BELONGS_TO, 'ResFireName', 'fire_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'fire_id' => 'Fire',
            'client_id' => 'Client',
            'wds_actions' => 'Wds Actions',
            'personel' => 'Personnel',
            'published' => 'Published',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
            'fire_summary' => 'Fire Summary',
            'date_access_gained' => 'Date Access Gained',
            'access_gained_comment' => 'Access Gained Comment',
            'date_access_denied' => 'Date Access Denied',
            'access_denied_comment' => 'Access Denied Comment',
            'fire_name' => 'Fire Name',
            'fire_city' => 'Fire City',
            'fire_state' => 'Fire State',
            'client_name' => 'Client',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($sort = null)
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;
        $criteria->with = array('client','fire');

        $criteria->compare('t.id',$this->id);
        $criteria->compare('t.fire_id',$this->fire_id);
        $criteria->compare('t.client_id',$this->client_id);
        $criteria->compare('t.wds_actions',$this->wds_actions,true);
        $criteria->compare('t.personel',$this->personel,true);
        $criteria->compare('t.published',$this->published);
        if($this->date_created)
        {
            $date_created = strtotime($this->date_created);
            if ($date_created !== false)
            {
                $criteria->addCondition("t.date_created >= '" . date('Y-m-d', $date_created) . "' AND t.date_created < '" . date('Y-m-d', strtotime($this->date_created . ' + 1 day')) . "'");
            }
        }
        if($this->date_updated)
        {
            $date_updated = strtotime($this->date_updated);
            if ($date_updated !== false)
            {
                $criteria->addCondition("t.date_updated >= '" . date('Y-m-d', $date_updated) . "' AND t.date_updated < '" . date('Y-m-d', strtotime($this->date_updated . ' + 1 day')) . "'");
            }
        }
        $criteria->compare('t.fire_summary',$this->fire_summary,true);
        if($this->date_access_gained)
        {
            $date_access_gained = strtotime($this->date_access_gained);
            if ($date_access_gained !== false)
            {
                $criteria->addCondition("t.date_access_gained >= '" . date('Y-m-d', $date_access_gained) . "' AND t.date_access_gained < '" . date('Y-m-d', strtotime($this->date_access_gained . ' + 1 day')) . "'");
            }
        }
        $criteria->compare('t.access_gained_comment',$this->access_gained_comment,true);
        if($this->date_access_denied)
        {
            $date_access_denied = strtotime($this->date_access_denied);
            if ($date_access_denied !== false)
            {
                $criteria->addCondition("t.date_access_denied >= '" . date('Y-m-d', $date_access_denied) . "' AND t.date_access_denied < '" . date('Y-m-d', strtotime($this->date_access_denied . ' + 1 day')) . "'");
            }
        }
        $criteria->compare('t.access_denied_comment',$this->access_denied_comment,true);

        $criteria->compare('fire.Name', $this->fire_name,true);
        $criteria->compare('fire.State', $this->fire_state,true);
        $criteria->compare('fire.City', $this->fire_city,true);
        $criteria->compare('client.name',$this->client_name,true);

        $sortWay = false; //false = DESC, true = ASC
        if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
            $sortWay = true;
        $sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

        $dataProvider = new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id'=>CSort::SORT_DESC),
                'attributes' => array(
                    'fire_name' => array(
                        'asc' => 'fire.Name ASC',
                        'desc' => 'fire.Name DESC',
                    ),
                    'client_name' => array(
                        'asc' => 'client.name ASC',
                        'desc' => 'client.name DESC',
                    ),
                    'fire_state'=>array(
                        'asc'=>'fire.State ASC',
                        'desc'=>'fire.State DESC',
                    ),
                    'fire_city'=>array(
                        'asc'=>'fire.City ASC',
                        'desc'=>'fire.City DESC',
                    ),
                    '*',
                ),
            ),
            'criteria' => $criteria
        ));

        return $dataProvider;
    }

    //----------------------------------------------------Standard Yii--------------------------------------------------

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResPostIncidentSummary the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {

        $this->date_updated =  date('Y-m-d H:i');

        // Setting Date Created
        if ($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');

        //Make sure they don't get saved as 1900-01-01
        if(empty($this->date_access_denied)){
            $this->date_access_denied = null;
        }

        if(empty($this->date_access_gained)){
            $this->date_access_gained = null;
        }

        return parent::beforeSave();
    }
    /**
     * Fetch minimum value for date_created, date_updated, date_access_gained, date_access_denied
     * @return array $postIncidentSummary
     */
    public function minDate()
    {
         $postIncidentSummary = Yii::app()->db->createCommand()
         ->select('min(date_created) cminDate, min(date_updated) uminDate, min(date_access_gained) gminDate,  min(date_access_denied) dminDate')
         ->from('res_post_incident_summary')
         ->queryRow();

        return $postIncidentSummary;
    }
    protected function afterFind()
    {
        if (isset($this->client))
            $this->client_name = $this->client->name;

        $this->date_updated =  date('Y-m-d H:i', strtotime($this->date_updated));
        $this->date_created =  date('Y-m-d H:i', strtotime($this->date_created));

        return parent::afterFind();
    }

    //----------------------------------------------------General Functions--------------------------------------------------

    /**
     * Get all fires that have had a dispatched notice created
     * @param integer $clientID
     * @return array Associative array of Fire_ID => Name
     */
    public static function getDispatchedFires($clientID)
    {
        $criteria = new CDbCriteria;
        $criteria->select = array('Fire_ID','Name');
        $criteria->with = array(
            'ResNotice' => array(
                'select' => array('notice_id','client_id','wds_status')
            )
        );
        $criteria->addCondition('ResNotice.client_id = :client_id');
        $criteria->addCondition("ResNotice.wds_status = (SELECT id FROM res_status WHERE status_type = 'Dispatched')");
        $criteria->params[':client_id'] = $clientID;
        $criteria->order = "t.Name DESC";

        $models = ResFireName::model()->findAll($criteria);

        return CHtml::ListData($models, 'Fire_ID', 'Name');
    }

    /**
     * Virtual attribute that gets a client's name
     * @return string
     */
    public function getClient_name()
    {
        return $this->client->name;
    }
}
