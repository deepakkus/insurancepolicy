<?php

/**
 * This is the model class for table "client_states".
 *
 * The followings are the available columns in table 'client_states':
 * @property integer $id
 * @property integer $client_id
 * @property integer $state_id
 */
class ClientStates extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'client_states';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('client_id, state_id', 'numerical', 'integerOnly'=>true),
			array('id, client_id, state_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'state' => array(self::BELONGS_TO, 'GeogStates', 'state_id')
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
			'state_id' => 'State',
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
		$criteria=new CDbCriteria;

		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('state_id',$this->state_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ClientStates the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getStateName()
    {
        return $this->state->abbr;
    }
    
    public static function updateClientStates($clientStates, $client)
    {
        if (!isset($_POST['ClientStates']['state_id']))
        {
            if (ClientStates::model()->exists('client_id = :client_id', array(':client_id' => $client->id)))
                ClientStates::model()->deleteAll('client_id = :client_id', array(':client_id' => $client->id));
        }
        else
        {
            $currentStateIDs = array_map(function($model) {  return $model->state_id; }, $clientStates);
            $newstateIDs = $_POST['ClientStates']['state_id'];
            
            // Add new state if appropriate
            foreach ($newstateIDs as $stateID)
            {
                if (!in_array($stateID, $currentStateIDs))
                {
                    $clientState = new ClientStates;
                    $clientState->client_id = $client->id;
                    $clientState->state_id = $stateID;
                    $clientState->save();
                }
            }
            
            // Delete old state if appropriate
            foreach ($clientStates as $clientState)
            {
                if (!in_array($clientState->state_id, $newstateIDs))
                    $clientState->delete();
            }
        }
    }
}
