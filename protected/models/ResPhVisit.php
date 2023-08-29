<?php

/**
 * This is the model class for table "res_ph_visit".
 *
 * The followings are the available columns in table 'res_ph_visit':
 * @property integer $id
 * @property integer $property_pid
 * @property integer $client_id
 * @property integer $fire_id
 * @property string $date_created
 * @property string $date_updated
 * @property string $date_action
 * @property string $status
 * @property string $comments
 * @property integer $approval_user_id
 * @property integer $user_id
 * @property string $review_status
 * @property string $publish_comments
 * @property string $phvisit_lat
 * @property string $phvisit_long
 *
 * The followings are the available model relations:
 * @property Property $property
 * @property ResFireName $fire
 * @property Client $client
 * @property User $approvalUser
 * @property User $user
 * @property string $lastUpdateUserName
 * @property ResPhPhotos[] $phPhotos
 * @property ResPhAction[] $phActions
 */
class ResPhVisit extends CActiveRecord
{
    public $propertyAddress;
    public $propertyPolicy;
    public $memberFirstName;
    public $memberLastName;
    public $userName;
    public $approvalUserName;
    public $id;
    public $lastUpdateUserName;
    public $response_status;

    static public $statusTypes = array(
        'undamaged',
        'damaged',
        'lost',
        'saved',
        'unknown',
    );

    static public $reviewstatusType = array(
        'not reviewed',
        'published',
        're-review',
        'reviewed'
    );

     static public $responseStatus = array(
        'enrolled',
        'not enrolled',
        'declined',
        'ineligible'
    );

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_ph_visit';
    }

    public function behaviors()
    {
        return array(
            'history' => array(
                'class' => 'HistoryBehavior',
                'historyBehaviorCallback' => array($this, 'historyBehaviorCallback'),
                'historyBehaviorCallbackUserID' => array($this, 'historyBehaviorCallbackUserID')
            )
        );
    }

    public function historyBehaviorCallback()
    {
        $history_entry = $this->attributes;
        $history_entry['phActions'] = array();
        foreach($this->phActions as $phAction)
        {
            $history_entry['phActions'][] = $phAction->attributes;
        }
        return json_encode($history_entry);
    }

    public function historyBehaviorCallbackUserID()
    {
        if(isset(Yii::app()->user->id) && Yii::app()->user->id > 0)
            return Yii::app()->user->id;
        else
            return $this->user_id;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('property_pid, client_id, fire_id, date_action, status, user_id', 'required'),
            array('property_pid, client_id, fire_id, approval_user_id, user_id', 'numerical', 'integerOnly' => true),
            array('phvisit_lat', 'match', 'pattern' => '/\d{2,3}\.{1}\d+/', 'message' => '{attribute} must entered in the Decimal format.<br />Ex: 40.12421'),
            array('phvisit_long', 'match', 'pattern' => '/-\d{2,3}\.{1}\d+/', 'message' => '{attribute} must entered in the Decimal format.<br />Ex: -122.4512'),
            array('review_status', 'length', 'max' => 50),
            array('status', 'length', 'max' => 255),
            array('comments', 'length', 'max' => 3000),
            array('publish_comments', 'length', 'max' => 3000),
            array('id, property_pid, client_id, fire_id, date_created, date_updated, date_action, status, comments, approval_user_id, user_id, review_status, publish_comments', 'safe', 'on' => 'search'),
            array('memberFirstName, memberLastName, status, date_created, date_updated, date_action, userName, approvalUserName, review_status, comments, publish_comments, response_status, propertyAddress, propertyPolicy', 'safe', 'on' => 'search_actions')

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
            'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
            'fire' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'approvalUser' => array(self::BELONGS_TO, 'User', 'approval_user_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'phPhotos' => array(self::HAS_MANY, 'ResPhPhotos', 'visit_id'),
            'phActions' => array(self::HAS_MANY, 'ResPhAction', 'visit_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'client_id' => 'Client',
            'fire_id' => 'Fire',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
            'date_action' => 'Action Date',
            'status' => 'Status',
            'comments' => 'Engine Comments',
            'approval_user_id' => 'Approval User',
            'user_id' => 'Submitted By',
            'review_status' => 'Review Status',
            // Virtual Attributes
            'propertyAddress' => 'Address',
            'propertyPolicy' => 'Policy Number',
            'memberFirstName' => 'First Name',
            'memberLastName' => 'Last Name',
            'userName' => 'Submitted By',
            'approvalUserName' => 'Approval User',
            'publish_comments' => 'Dashboard Comments',
            'phvisit_lat' => 'Latitude',
            'phvisit_long' => 'Longitude',
            'lastUpdateUserName' => 'Last Updated By',
            'response_status' => 'Response Enrollment Status',
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

        $criteria->compare('id', $this->id);
        $criteria->compare('client_id', $this->client_id);
        $criteria->compare('fire_id', $this->fire_id);
        $criteria->compare('status', $this->status, true);
        $criteria->compare('comments', $this->comments, true);
        $criteria->compare('approval_user_id', $this->approval_user_id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('review_status', $this->review_status, false);
        $criteria->compare('publish_comments', $this->publish_comments, true);
        $criteria->compare('phvisit_lat', $this->phvisit_lat, false);
        $criteria->compare('phvisit_long', $this->phvisit_long, true);

        if ($this->date_created)
        {
            $criteria->addCondition('date_created >= :date_created_today AND date_created < :date_created_tomorrow');
            $criteria->params[':date_created_today'] = date('Y-m-d', strtotime($this->date_created));
            $criteria->params[':date_created_tomorrow'] = date('Y-m-d', strtotime($this->date_created . ' + 1 day'));
        }

        if ($this->date_updated)
        {
            $criteria->addCondition('date_updated >= :date_updated_today AND date_updated < :date_updated_tomorrow');
            $criteria->params[':date_updated_today'] = date('Y-m-d', strtotime($this->date_updated));
            $criteria->params[':date_updated_tomorrow'] = date('Y-m-d', strtotime($this->date_updated . ' + 1 day'));
        }

        if ($this->date_action)
        {
            $criteria->addCondition('date_action >= :date_action_today AND date_action < :date_action_tomorrow');
            $criteria->params[':date_action_today'] = date('Y-m-d', strtotime($this->date));
            $criteria->params[':date_action_tomorrow'] = date('Y-m-d', strtotime($this->date . ' + 1 day'));
        }

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array('*')
            ),
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 20
            )
        ));
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @param string $sort
     * @param integer $fireId
     * @param integer $clientID
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search_actions($sort, $fireId, $clientID)
    {
        // This searches for all unique pids actions have been done for on a fire for the give client

        $criteria = new CDbCriteria;

        $criteria->with = array('property','property.member','user','approvalUser');

        if ($this->propertyAddress)
            $criteria->compare('property.address_line_1', $this->propertyAddress, true);
        if ($this->propertyPolicy)
            $criteria->compare('property.policy', $this->propertyPolicy, true);
        if ($this->response_status)
            $criteria->compare('property.response_status', $this->response_status);
        if ($this->memberFirstName)
            $criteria->compare('member.first_name', $this->memberFirstName, true);
        if ($this->memberLastName)
            $criteria->compare('member.last_name', $this->memberLastName, true);
        if ($this->status)
            $criteria->compare('[t].status', $this->status);
        if ($this->userName)
            $criteria->compare('[user].username', $this->userName, true);
        if ($this->approvalUserName)
            $criteria->compare('approvalUser.username', $this->approvalUserName, true);
        if ($this->review_status)
            $criteria->compare('review_status', $this->review_status, false);
        if ($this->comments)
            $criteria->compare('[t].comments', $this->comments, true);
        if ($this->publish_comments)
            $criteria->compare('[t].publish_comments', $this->publish_comments, true);
            if ($this->comments)
            $criteria->compare('[t].phvisit_lat', $this->phvisit_lat, true);
        if ($this->publish_comments)
            $criteria->compare('[t].phvisit_long', $this->phvisit_long, true);

        if ($this->date_created)
        {
            $date_created = strtotime($this->date_created);
            if ($date_created !== false)
	        {
		        $criteria->addCondition('[t].date_created >= :today_created AND [t].date_created < :tomorrow_created');
                $criteria->params[':today_created'] = date('Y-m-d', strtotime($this->date_created));
                $criteria->params[':tomorrow_created'] = date('Y-m-d', strtotime($this->date_created . ' + 1 day'));
	        }
        }

        if ($this->date_updated)
        {
            $date_updated = strtotime($this->date_updated);
            if ($date_updated !== false)
            {
                $criteria->addCondition('[t].date_updated >= :today_updated AND [t].date_updated < :tomorrow_updated');
                $criteria->params[':today_updated'] = date('Y-m-d', strtotime($this->date_updated));
                $criteria->params[':tomorrow_updated'] = date('Y-m-d', strtotime($this->date_updated . ' + 1 day'));
            }
        }
        if ($this->date_action)
        {
            $date_action = strtotime($this->date_action);
            if ($date_action !== false)
            {
                $criteria->addCondition('[t].date_action >= :today_action AND [t].date_action < :tomorrow_action');
                $criteria->params[':today_action'] = date('Y-m-d', strtotime($this->date_action));
                $criteria->params[':tomorrow_action'] = date('Y-m-d', strtotime($this->date_action . ' + 1 day'));
            }
        }
        $criteria->params[':fire_id'] = $fireId;
        $criteria->params[':client_id'] = $clientID;

        $criteria->addCondition('[t].client_id = :client_id');
        $criteria->addCondition('[t].fire_id = :fire_id');

        $sortWay = CSort::SORT_ASC;

        // Is this a desc sort?
        if (strpos($sort, '.') !== false)
        {
            $sortWay = CSort::SORT_DESC;
            $sort = str_replace('.desc', '', $sort);
        }

         return new WDSCActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array($sort => $sortWay),
                'attributes' => array(
                    'memberFirstName' => array(
                        'asc' => ' member.first_name',
                        'desc' =>'member.first_name DESC',
                    ),
                    'memberLastName' => array(
                        'asc' => 'member.last_name',
                        'desc' => 'member.last_name DESC',
                    ),
                    'userName' => array(
                        'asc' => '[user].username',
                        'desc' => '[user].username DESC',
                    ),
                    'approvalUserName' => array(
                        'asc' => 'approvalUser.username',
                        'desc' => 'approvalUser.username DESC',
                    ),
                    '*'
                ),
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @param integer $pid
     * @param integer $fireId
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search_edit($pid, $fireId)
    {
        // This searches for all actions for a pid on a given fire

        $criteria=new CDbCriteria;

        $criteria->with = array('property','property.member');

        $criteria->compare('id', $this->id);
        $criteria->compare('t.property_pid', $this->property_pid);
        $criteria->compare('member.first_name', $this->memberFirstName, true);
        $criteria->compare('member.last_name', $this->memberLastName, true);

        $criteria->addCondition('t.property_pid = :property_pid');
        $criteria->addCondition('t.fire_id = :fire_id');
        $criteria->params[':property_pid'] = $pid;
        $criteria->params[':fire_id'] = $fireId;

        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('action_id' => CSort::SORT_DESC),
                'attributes' => array(
                    'memberFirstName'=>array(
                        'asc' => 'member.first_name',
                        'desc' => 'member.first_name DESC',
                    ),
                    'memberLastName'=>array(
                        'asc' => 'member.last_name',
                        'desc' => 'member.last_name DESC',
                    ),
                    '*'
                ),
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }



    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResPhVisit the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        $this->date_updated = date('Y-m-d H:i:s');

        if ($this->isNewRecord)
        {
            $this->date_created = date('Y-m-d H:i');

            if (empty($this->review_status))
            {
                $this->review_status = 'not reviewed';
            }
        }

        if (empty($this->phvisit_lat))
        {
            $this->phvisit_lat = null;
        }

        if (empty($this->phvisit_long))
        {
            $this->phvisit_long = null;
        }

        return parent::beforeSave();
    }

    protected function afterFind()
    {
        if ($this->property)
        {
            $this->propertyAddress = $this->property->address_line_1;
            $this->propertyPolicy = $this->property->policy;
            $this->response_status = $this->property->response_status;

            if ($this->property->member)
            {
                $this->memberFirstName = $this->property->member->first_name;
                $this->memberLastName = $this->property->member->last_name;
            }
        }

        if ($this->user)
            $this->userName = $this->user->username;

        if ($this->approvalUser)
            $this->approvalUserName = $this->approvalUser->username;

        if(isset($this->date_action))
            $this->date_action = date_format(new DateTime($this->date_action), 'Y-m-d H:i');

        return parent::afterFind();
    }

    /**
     * Trucate the passed in attribute to 100 chars
     * @param string $attr - Should be "comments" or "publish_comments"
     * @return string
     */
    public function truncateComments($attr)
    {
        $retVal = '';

        if (isset($this->$attr))
        {
            $retVal = $this->$attr;

            if (strlen($retVal) > 100)
            {
                $retVal = substr($retVal, 0, 100) . ' ...' . CHtml::link('Full Comment', '#', array(
                    'class' => 'comments-popup',
                    'data-comment' => $this->$attr,
                    'data-comment-type' => $this->getAttributeLabel($attr)
                ));
            }
        }

        return $retVal;
    }

    /**
     * Return array of visit status types for drop down field
     * @return array
     */
    public function getStatusTypes()
    {
        return array_combine(self::$statusTypes, array_map('ucfirst', self::$statusTypes));
    }

    /**
     * Return array of visit status types for drop down field
     * @return array
     */
    public function getResponseStatus()
    {
        return array_combine(self::$responseStatus, array_map('ucfirst', self::$responseStatus));
    }

    /**
     * Return array of visit review status type for drop down field
     * @return array
     */
    public function getReviewStatusType($showStatus=false)
    {
        if($showStatus)
        {
            array_push(self::$reviewstatusType,"Removed");
        }
        return array_combine(self::$reviewstatusType, array_map('ucwords', self::$reviewstatusType));
    }

    /**
     * Return array of user models that have Admin or Manager permissions
     * @return array
     */
    public function getManagerApprovalDropdown()
    {
        return User::model()->findAll(array(
            'select' => array('id', 'name'),
            'condition' => "(type LIKE '%Admin%' OR type LIKE '%Manager%') AND active = 1",
            'order' => 'name ASC'
        ));
    }

    /**
     * Get unique pids for all notices for a given fire_id
     * @param integer $fireID
     * @param integer $clientID
     * @return array returnArray of pids
     */
    public function getDistinctPidsforFireID($fireID, $clientID)
    {
        $criteria = new CDbCriteria;
        $criteria->select = 'property_pid';
        $criteria->distinct = true;
        $criteria->addCondition('fire_id = :fire_id');
        $criteria->addCondition('client_id = :client_id');
        $criteria->addCondition('date_action >= :date_action');

        $criteria->params[':fire_id'] = $fireID;
        $criteria->params[':client_id'] = $clientID;
        $criteria->params[':date_action'] = date('Y-m-d');

        $pids = ResPhVisit::model()->findAll($criteria);

        return array_map(function($data) { return $data->property_pid; }, $pids);
    }

    /**
     * Get's the policyholder actions for a given fire/client
     * @param int $noticeID
     * @param int $fireID
     * @param int $clientID
     * @param boolean $realTime
     * @return array
     *
     * example return:
     * "408186": {
            "date_updated": "2016-07-26 10:59:00.000",
            "property_pid": "408186",
            "member_name": "Clark, Harry",
            "address": "45 CHAMBERS DR",
            "city": "Eagle",
            "state": "CO",
            "threat": "0",
            "response_status": "enrolled",
            "snapshot_status": "enrolled",
            "member_num": "MEM14387",
            "producer_name": "",
            "signature": null,
            "daily_entry": [
                {
                    "date_action": "2016-07-26 00:00:00.000",
                    "comments": "The WDS engine deployed a sprinkler kit on the home and insured that all windows and vents were shut and secured. Home owner is currently evacuated.",
                    "safe": 1,
                    "lost": 0,
                    "damaged": 0,
                    "wds_saved": 0,
                    "actions": {
                        "Sprinklers Set Up/Maintained": 1
                    },
                    "images": [
                        "9836",
                        "9837",
                        "9838"
                    ]
                },
                {
                    "date_action": "2016-07-25 00:00:00.000",
                    "comments": "Home owner is currently evacuated. A WDS engine drove by the home to inspect threat",
                    "safe": 1,
                    "lost": 0,
                    "damaged": 0,
                    "wds_saved": 0,
                    "actions": {
                        "Property Triage": 1
                    },
                    "images": [
                        "9835"
                    ]
                }
            ]
        }
     */
    public static function getPolicyActionsByFire($noticeID, $fireID, $clientID, $realTime)
    {
        $date = null;
        $returnData = array();
        $visitPhotos = array();

        if (!$realTime)
        {
            $date = Yii::app()->db->createCommand('SELECT date_updated FROM res_notice WHERE notice_id = :notice_id')->queryScalar(array(
                ':notice_id' => $noticeID
            ));
        }

        //Get the actions and the photos
        $models = self::getVisitsByFire($fireID, $noticeID, $clientID, $date);
        $photos = self::getPhotosByFire($fireID, $noticeID, $clientID, $date);

        //Parse the photos so we can easily get all photos per visit later - kind of ugly, but it accomplishes the task in one loop
        foreach ($photos as $photo)
        {
            if (isset($visitPhotos[$photo['visit_id']]))
            {
                $visitPhotos[$photo['visit_id']][] = $photo['file_id'];
            }
            else
            {
                $visitPhotos[$photo['visit_id']] = array($photo['file_id']);
            }
        }

        //Now go through and parse the visits/actions into a different structure
        foreach($models as $model)
        {
            //get all actions
            $actionNames = self::getActionsNameArray($model['id']);
            $actionsArray = array();
            foreach($actionNames as $action)
            {
                $actionsArray[$action['name']] = 1;
            }

            //if visit is not currently in review_status = 'published' or 're-review', then need to see if it has a history entry that has been published and use that data instead
            if($model['review_status'] == 'published' || $model['review_status'] == 're-review')
            {
                $historyModels = self::getVisitHistory($model['id']);
                foreach($historyModels as $historyModel)
                {
                    $model['status'] = '';
                    if($historyModel['review_status'] == 'published')
                    {
                        //update current model var with historymodel values for publishing
                        $model['date_action'] = $historyModel['date_action'];
                        $model['comments'] = $historyModel['comments'];
                        $model['publish_comments'] = $historyModel['publish_comments'];
                        $model['safe'] = ($historyModel['status'] == 'undamaged') ? 1 : 0;
                        $model['lost'] = ($historyModel['status'] == 'lost') ? 1 : 0;
                        $model['damaged'] = ($historyModel['status'] == 'damaged') ? 1 : 0;
                        $model['wds_saved'] = ($historyModel['status'] == 'saved') ? 1 : 0;
                        $model['unknown'] = ($historyModel['status'] == 'unknown') ? 1 : 0;

                        if($historyModel['status'] == 'undamaged')
                        {
                            $model['status'] = 'undamaged';
                        }
                        elseif($historyModel['status'] == 'lost')
                        {
                            $model['status'] = 'lost';
                        }
                        elseif($historyModel['status'] == 'damaged')
                        {
                            $model['status'] = 'damaged';
                        }
                        elseif($historyModel['status'] == 'saved')
                        {
                            $model['status'] = 'saved';
                        }
                        elseif($historyModel['status'] == 'unknown')
                        {
                            $model['status'] = 'unknown';
                        }
                       

                        $actionsArray = array();
                        foreach($historyModel['phActions'] as $action)
                        {
                            $actionType = ResPhActionType::model()->findByPk($action['action_type_id']);
                            $actionsArray[$actionType->name] = 1;
                        }

                        break; //found a published history model so can drop out of loop now
                    }
                }

                //setup top level for property
                if (!isset($returnData[$model['property_pid']]))
                {
                    $returnData[$model['property_pid']] = array();
                }

                //setup property daily entry array
                if (!isset($returnData[$model['property_pid']]) && !isset($returnData[$model['property_pid']]['daily_entry']))
                {
                    $returnData[$model['property_pid']]['daily_entry'] = array();
                }

                $returnData[$model['property_pid']]['date_updated'] = $model['date_updated'];
                $returnData[$model['property_pid']]['property_pid'] = $model['property_pid'];
                $returnData[$model['property_pid']]['member_name'] = $model['last_name'] . ', ' . $model['first_name'];
                $returnData[$model['property_pid']]['address'] = $model['address_line_1'];
                $returnData[$model['property_pid']]['city'] = $model['city'];
                $returnData[$model['property_pid']]['state'] = $model['state'];
                $returnData[$model['property_pid']]['threat'] =  $model['threat'];
                $returnData[$model['property_pid']]['response_status'] = $model['response_status'];
                $returnData[$model['property_pid']]['snapshot_status'] =  $model['snapshot_status'];
                $returnData[$model['property_pid']]['member_num'] = $model['member_num'];
                $returnData[$model['property_pid']]['policy'] = $model['policy'];
                $returnData[$model['property_pid']]['producer_name'] = $model['producer'];
                $returnData[$model['property_pid']]['agency_code'] = $model['agency_code'];
                $returnData[$model['property_pid']]['signature'] = $model['signed_ola'];
                $returnData[$model['property_pid']]['review_status'] = $model['review_status'];

                $images = isset($visitPhotos[$model['id']]) ? $visitPhotos[$model['id']] : null;

                $returnData[$model['property_pid']]['daily_entry'][] = array(
                    'review_status' => $model['review_status'],
                    'date_action' => $model['date_action'],
                    'comments' => $model['comments'],
                    'publish_comments' => $model['publish_comments'],
                    'safe' => ($model['status'] == 'undamaged') ? 1 : 0,
                    'lost' => ($model['status'] == 'lost') ? 1 : 0,
                    'damaged' => ($model['status'] == 'damaged') ? 1 : 0,
                    'unknown' => ($model['status'] == 'unknown') ? 1 : 0,
                    'wds_saved' => ($model['status'] == 'saved') ? 1 : 0,
                    'actions' => $actionsArray,
                    'images'=>$images
                );
            }
        }

        $returnArray['error'] = 0;
        return $returnData;
    }

    /**
     * Returns all visits for the given fire/client/notice and before date
     * @param int $fireID
     * @param int $noticeID
     * @param int $clientID
     * @param string $date
     */
    private static function getVisitsByFire($fireID, $noticeID, $clientID, $date)
    {
        // If no date provided, just set date into the future so it doesn't matter
        if ($date == null)
        {
            $date = date('Y-m-d', strtotime('+1 month'));
        }

        $sql = "
            SELECT
                v.*,
                m.first_name,
                m.last_name,
                p.address_line_1,
                p.city,
                p.[state],
                p.response_status,
                p.producer,
                p.agency_code,
                p.policy,
                m.member_num,
                p.client_policy_id,
                m.signed_ola,
                CASE t.threat WHEN 1 THEN 1 ELSE 0 END AS threat,
                t.response_status as snapshot_status
            FROM
                res_ph_visit v
            INNER JOIN
                properties p on p.pid = v.property_pid
            INNER JOIN
                members m on m.mid = p.member_mid
            LEFT OUTER JOIN
                (SELECT * FROM res_triggered WHERE notice_id = :notice_id) t ON t.property_pid = v.property_pid
            WHERE
                v.date_action <= :date
                and v.client_id = :client_id
                and v.fire_id = :fire_id
                and v.review_status IN ('published','re-review','reviewed')
            ORDER BY
                v.date_action
        ";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->bindParam(':fire_id', $fireID, PDO::PARAM_INT)
            ->bindParam(':notice_id', $noticeID, PDO::PARAM_INT)
            ->bindParam(':date', $date, PDO::PARAM_STR)
            ->queryAll();
    }

    /**
     * Returns all actions for the given visit id
     * @param int $visitID
     */
    private static function getActionsNameArray($visitID)
    {
        $sql = "SELECT res_ph_action_type.[name]
                FROM res_ph_action
                JOIN res_ph_action_type ON res_ph_action_type.id = res_ph_action.action_type_id
                WHERE res_ph_action.visit_id = :visit_id";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':visit_id', $visitID, PDO::PARAM_INT)
            ->queryAll();
    }

    /**
     * Returns all photos for the given fire/client
     * @param int $fireID
     * @param int $noticeID
     * @param int $clientID
     * @param string $date
     */
    private static function getPhotosByFire($fireID, $noticeID, $clientID, $date)
    {
        // If no date provided, just set date into the future so it doesn't matter
        if ($date == null)
        {
            $date = date('Y-m-d', strtotime('+1 month'));
        }

        $sql = "
            select
                p.file_id, p.visit_id
            from
                res_ph_photos p
            inner join
                res_ph_visit v on v.id = p.visit_id
            where
                v.date_action <= :date
                and v.client_id = :client_id
                and v.fire_id = :fire_id
                and v.review_status IN ('published','re-review','reviewed')
				and p.publish = 1
			order by p.visit_id ASC, p.[order] ASC";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->bindParam(':fire_id', $fireID, PDO::PARAM_INT)
            ->bindParam(':date', $date, PDO::PARAM_STR)
            ->queryAll();
    }

    public static function getCountPolicyHomes($clientID, $fireID, $wdsEduDate, $status = null, $wdsEdu = null)
    {

        $criteria = new CDbCriteria;

        $criteria->addCondition('t.client_id = :client_id');
        $criteria->addCondition('t.fire_id = :fire_id');
        $criteria->params[':client_id'] = $clientID;
        $criteria->params[':fire_id'] = $fireID;

        // If we want a specific status type
        if ($status !== null)
        {
            $criteria->addCondition('t.status = :status');
            $criteria->params[':status'] = $status;
        }

        // If we're looking for wds edu only
        if ($wdsEdu !== null)
        {
            $criteria->with = array('property');
            $criteria->addCondition('property.pre_risk_status = \'enrolled\'');
            $criteria->addCondition('property.pr_status_date <= :pr_status_date');
            $criteria->params[':pr_status_date'] = $wdsEduDate;
        }

        return (int)ResPhVisit::model()->count($criteria);
    }

    /**
     * Return a history array of visits models from a visit id
     * @param integer $visitID
     * @return EngShiftTicket[] array stack of what the model used to look like with the date and time it was changed to that as the key
     */
    public static function getVisitHistory($visitID)
    {
        $historyModels = ModelHistory::model()->findAll(array(
            'select' => 'data, date, user_id',
            'condition' => '[table] = :table AND [table_pk] = :table_pk',
            'params' => array(':table' => 'res_ph_visit', ':table_pk' => $visitID),
            'order' => 'date DESC',
        ));

        $visitHistoryArray = array();

        foreach ($historyModels as $history)
        {
            $dataArray = json_decode($history->data, true);

            if ($history->user_id > 0)
            {
                $dataArray['user_id'] = $history->user_id;
            }

            $historyDate = strtotime($history->date);
            $visitHistoryArray[$historyDate] = $dataArray;
        }

        return $visitHistoryArray;
    }

    /**
     * Get the most recent UserName that updated this PHV
     * @return string
     */
    public function getLastUpdateUserName()
    {
        $return = '';

        $latestHistoryEntry = ModelHistory::model()->find(array(
            'select' => 'user_id',
            'condition' => '[table] = :table AND [table_pk] = :table_pk',
            'params' => array(':table' => 'res_ph_visit', ':table_pk' => $this->id),
            'order' => 'date DESC',
        ));

        if(isset($latestHistoryEntry))
        {
            $user = User::model()->findByPk($latestHistoryEntry->user_id);
            if(isset($user))
            {
                $return = $user->name;
            }
        }

        $this->lastUpdateUserName = $return;
        return $return;
    }

    /**
     * Get the most recent Published property status
     * @param integer $pid
     * @param string $action_date
     * @param integer $id
     * @return string $phvStatus
     */
    public function getPhVisitStatus($pid, $action_date, $id)
    {
        $phvStatus = "No previous Visit";       
        $counter = 1;      
        $ph_visit_details = ResPhVisit::model()->findAll(array("condition"=>"property_pid=".$pid,"order"=>"id desc"));
        foreach($ph_visit_details as $visit)
        {
                if($visit->review_status=='published')
                {
                    $phvStatus = $visit->status;
                    if(date("Y-m-d H:i:s",strtotime($visit -> date_action)) == date("Y-m-d H:i:s",strtotime($action_date)))
                    {   
                        if($counter == 1 || $id)
                        {
                            $phvStatus = "No previous Visit";
                        }
                        continue;
                    }
                    else
                    {
                        break;
                    }
                }
            $counter ++;
        }
        return $phvStatus;
    }
}
