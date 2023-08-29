<?php

/**
 * This is the model class for table "user_client". This is the model that connects the User to one or more clients they are allowed to see
 * It has a many to one relationship with Users table and a one to one relationship with the clients table via foriegn keys
 *
 * The followings are the available columns in table 'contact':
 * @property integer $id
 * @property string $user_id
 * @property string $client_id
 * //RELATIONS
 * @property Client $client
 * @property User $user
 */

class UserClient extends CActiveRecord
{
	/**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return userClient the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
     * @return string the associated database table name
     */
	public function tableName()
	{
		return 'user_client';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
			array('user_id, client_id', 'required'),
            array('user_id, client_id', 'numerical', 'integerOnly'=>true),
			array('id, user_id, client_id','safe', 'on'=>'search'),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id')
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'user_id' => 'User ID',
			'client_id' => 'Client ID',
		);
	}

	/**
     * Retrieves a list of user clients based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('client_id', $this->client_id, true);
		
        $dataProvider = new CActiveDataProvider($this, array('criteria'=>$criteria));

		return $dataProvider;
	}
}