<?php

/**
 * This is the model class for table "oa2_tokens".
 *
 * The followings are the available columns in table 'oa2_tokens':
 * @property string $oauth_token
 * @property string $client_id
 * @property integer $expires
 * @property string $scope
 * @property string $type
 */
class Oa2Tokens extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'oa2_tokens';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('oauth_token, client_id', 'required'),
			array('expires', 'length', 'max'=>80),
			array('oauth_token', 'length', 'max'=>40),
			array('client_id', 'length', 'max'=>20),
			array('scope', 'length', 'max'=>200),
			array('type', 'length', 'max'=>50),
            array('expires, type, scope', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('oauth_token, client_id, expires, scope, type', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'oauth_token' => 'Oauth Token',
			'client_id' => 'Client',
			'expires' => 'Expires',
			'scope' => 'Scope',
			'type' => 'Type',
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
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('oauth_token',$this->oauth_token,true);
		$criteria->compare('client_id',$this->client_id);		
		$criteria->compare('scope',$this->scope,true);
		$criteria->compare('type',$this->type,true);

        if ($this->expires)
        {
            $expirestart = (int) strtotime($this->expires.' 00:00:00');
            $expireend = (int) strtotime($this->expires.' 23:59:59');           
            $criteria->addBetweenCondition('expires', $expirestart, $expireend);
        }

		$dataProvider = new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
        $dataProvider->pagination->pageSize = 25;
        return $dataProvider;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Oa2Tokens the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function afterFind() {

        // Convert the date/time fields to display format.
        if(!empty($this->expires)){
            $this->expires = date("Y-m-d H:i", $this->expires);
        }
        return parent::afterFind();
    }

    protected function beforeSave()
	{
        // Convert the date/time fields to display format.
        if(!empty($this->expires) && ! is_numeric($this->expires)){
            $this->expires = (int) strtotime($this->expires);
        }
        return true;
	}
}
