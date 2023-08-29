<?php

/**
 * This is the model class for table "client_dedicated".
 *
 * The followings are the available columns in table 'client_dedicated':
 * @property integer $id
 * @property integer $client_id
 * @property integer $client_dedicated_hours_id
 *
 * The followings are the available model relations:
 * @property Client $client
 * @property ClientDedicatedHours $clientDedicatedHours
 */
class ClientDedicated extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'client_dedicated';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('client_id, client_dedicated_hours_id', 'required'),
            array('client_id, client_dedicated_hours_id', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, client_id, client_dedicated_hours_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'clientDedicatedHours' => array(self::BELONGS_TO, 'ClientDedicatedHours', 'client_dedicated_hours_id'),
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
            'client_dedicated_hours_id' => 'Client Dedicated Hours',
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

        $criteria->compare('id',$this->id);
        $criteria->compare('client_id',$this->client_id);
        $criteria->compare('client_dedicated_hours_id',$this->client_dedicated_hours_id);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ClientDedicated the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
