<?php

/**
 * This is the model class for table "res_ph_action".
 *
 * The followings are the available columns in table 'res_ph_action':
 * @property integer $id
 * @property integer $visit_id
 * @property integer $action_type_id
 * @property float $qty
 * @property float $allianceqty
 *
 * The followings are the available model relations:
 * @property ResPhActionType $phActionType
 * @property ResPhVisit $phVisit
 */
class ResPhAction extends CActiveRecord
{
    public $actionTypeName;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_ph_action';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('visit_id, action_type_id', 'required'),
            array('visit_id, action_type_id', 'numerical', 'integerOnly' => true),
            array('qty, alliance_qty', 'numerical', 'allowEmpty' => true, 'integerOnly' => false, 'min' => 0, 'max' => 1000),
            // The following rule is used by search().
            array('id, visit_id, action_type_id, qty, alliance_qty', 'safe', 'on' => 'search'),
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
            'phActionType' => array(self::BELONGS_TO, 'ResPhActionType', 'action_type_id'),
            'phVisit' => array(self::BELONGS_TO, 'ResPhVisit', 'visit_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'visit_id' => 'Visit ID',
            'action_type_id' => 'Action Type ID',
            'actionTypeName' => 'Action Type',
            'qty' => 'Quantity',
            'alliance_qty' => 'Alliance Qty'
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

        $criteria->with = array('phActionType');

        $criteria->compare('id', $this->id);
        $criteria->compare('visit_id', $this->visit_id);
        $criteria->compare('action_type_id', $this->action_type_id);
        $criteria->compare('qty', $this->qty, true);
        $criteria->compare('alliance_qty', $this->qty, true);
        $criteria->compare('phActionType.name', $this->actionTypeName, true);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'actionTypeName' => array(
                        'asc' => 'phActionType.name',
                        'desc' => 'phActionType.name DESC'
                    ),
                    '*'
                )
            ),
            'criteria' => $criteria,
            'pagination' => array('PageSize' => 20)
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResPhAction the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function afterFind()
    {
        if ($this->phActionType)
            $this->actionTypeName = $this->phActionType->name;

        return parent::afterFind();
    }

    /**
     * Get the could of policyholder actions for a specific fire and client
     * @param integer $clientID
     * @param integer $fireID
     * @param boolean $isEnrolled Is the policy enrolled?
     * @param string $actionType "Recon" or "Physical"
     * @return int
     */
    public static function getCountActionsByActionType($clientID, $fireID, $isEnrolled, $actionType)
    {
        $criteria = new CDBCriteria;
        $criteria->select = 't.id';
        $criteria->with = array(
            'phVisit',
            'phActionType'
        );
        $criteria->addCondition('phActionType.action_type = :action_type');
        $criteria->addCondition('phVisit.client_id = :client_id');
        $criteria->addCondition('phVisit.fire_id = :fire_id');
        $criteria->params = array(
            ':action_type' => $actionType,
            ':client_id' => $clientID,
            ':fire_id' => $fireID
        );

        if ($isEnrolled === true)
        {
            $criteria->with[] = 'phVisit.property';
            $criteria->addCondition('property.response_status = :response_status');
            $criteria->params[':response_status'] = 'enrolled';
        }

        return ResPhAction::model()->count($criteria);
    }

    /**
     * Get the could of policyholder actions for a specific fire and client
     * @param integer $clientID
     * @param integer $fireID
     * @param string[] $actionNames array of action names
     * @return int
     */
    public static function getCountActionsByName($clientID, $fireID, $actionNames)
    {
        /*
        $criteria = new CDBCriteria;
        $criteria->select = 't.id';
        $criteria->with = array(
            'phVisit',
            'phActionType'
        );
        $criteria->addCondition('phVisit.client_id = :client_id');
        $criteria->addCondition('phVisit.fire_id = :fire_id');
        $criteria->addInCondition('phActionType.name', $actionNames);
        $criteria->params = array(
            ':client_id' => $clientID,
            ':fire_id' => $fireID
        );
        return ResPhAction::model()->count($criteria);
        */

        // The above criteria produces a SQL error
        // The below SQL is exactly what the criteria results in (taken from code), and doesn't result in error ...werid

        $sql = "SELECT COUNT(DISTINCT [t].[id])
            FROM [dbo].[res_ph_action] [t]
            LEFT OUTER JOIN [dbo].[res_ph_visit] [phVisit] ON ([t].[visit_id]=[phVisit].[id])
            LEFT OUTER JOIN [dbo].[res_ph_action_type] [phActionType] ON ([t].[action_type_id]=[phActionType].[id])
            WHERE phVisit.client_id = :client_id
                AND phVisit.fire_id = :fire_id
                AND phActionType.name IN ('" . implode("','", $actionNames) . "')";

        $count = (int)Yii::app()->db->createCommand($sql)->queryScalar(array(
            ':client_id' => $clientID,
            ':fire_id' => $fireID
        ));

        return $count;
    }
    /*
    *   param ind visitId
    *   return visit actions name, qty
    */
    public static function  getVisitActions($visitId)
    {
        $sql = "SELECT r.id,
                            visit_id,
                            action_type_id,
                            qty,
                            name  
                            FROM res_ph_action r 
                            INNER JOIN res_ph_action_type a ON a.id = r.action_type_id
                            WHERE visit_id = ".$visitId;
        return ResPhAction::model()->findallbysql($sql);
    }
}
