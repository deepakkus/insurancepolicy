<?php

/**
 * This is the model class for table "user_tracking".
 *
 * The followings are the available columns in table 'user_tracking':
 * @property integer $id
 * @property integer $user_id
 * @property integer $client_id
 * @property integer $platform_id
 * @property integer $fire_id
 * @property string $ip
 * @property string $date
 * @property string $route
 * @property string $data
 */
class UserTracking extends CActiveRecord
{
    public $user_name;
    public $client_name;
    public $fire_name;
    public $platform_name;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user_tracking';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('date, platform_id, route', 'required'),
			array('user_id, client_id, fire_id, platform_id', 'numerical', 'integerOnly' => true),
			array('ip', 'length', 'max' => 30),
			array('route', 'length', 'max' => 200),
			// The following rule is used by search().
			array('user_id, client_id, platform_id, fire_id, ip, date, route, data, user_name, client_name, fire_name, platform_name', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'resFireName' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'platform' => array(self::BELONGS_TO, 'UserTrackingPlatform', 'platform_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'client_id' => 'Client',
            'platform_id' => 'Platform',
            'fire_id' => 'Fire',
			'ip' => 'IP',
			'date' => 'Date',
			'route' => 'Route',
            'data' => 'Data',
            
            // Virtual Attributes
            'user_name' => 'User',
            'client_name' => 'Client',
            'fire_name' => 'Fire',
            'platform_name' => 'Platform'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search($userTrackForm = null)
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
        
        $criteria->with = array('user','platform','resFireName');
        
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('t.client_id',$this->client_id);
        $criteria->compare('platform_id',$this->platform_id);
        $criteria->compare('fire_id',$this->fire_id);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('route',$this->route,true);
        $criteria->compare('data',$this->data,true);
        $criteria->compare('[user].name',$this->user_name,true);
        $criteria->compare('resFireName.Name',$this->fire_name, true);
        
        if ($this->date)
        {
            $criteria->addCondition('date >= :today AND date < :tomorrow');
            $criteria->params[':today'] = date('Y-m-d', strtotime($this->date));
            $criteria->params[':tomorrow'] = date('Y-m-d', strtotime($this->date . ' + 1 day'));
        }

        if ($userTrackForm !== null)
        {
            $criteria->addCondition('user_id = :user_id AND platform_id = :platform_id AND route = :route');
            $criteria->params[':user_id'] = $userTrackForm->userID;
            $criteria->params[':platform_id'] = $userTrackForm->platformID;
            $criteria->params[':route'] = $userTrackForm->route;

            if (!empty($userTrackForm->startDate))
            {
                $criteria->addCondition('date >= :start_date');
                $criteria->params[':start_date'] = $userTrackForm->startDate;
            }
            if (!empty($userTrackForm->endDate))
            {
                $criteria->addCondition('date < :end_date');
                $criteria->params[':end_date'] = $userTrackForm->endDate;
            }
        }
        
        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'user_name' => array(
                        'asc' => '[user].name ASC',
                        'desc' => '[user].name DESC'
                    ),
                    'fire_name' => array(
                        'asc' => 'resFireName.Name ASC',
                        'desc' => 'resFireName.Name DESC'
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
	 * @return UserTracking the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function afterFind()
    {
        if ($this->user)
            $this->user_name = $this->user->name;
        
        if ($this->client)
            $this->client_name = $this->client->name;
        
        if ($this->resFireName)
            $this->fire_name = $this->resFireName->Name;
        
        if ($this->platform)
            $this->platform_name = $this->platform->platform;
        
        return parent::afterFind();
    }
}
