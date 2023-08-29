<?php

/**
 * This is the model class for table "client_dedicated_hours".
 *
 * The followings are the available columns in table 'client_dedicated_hours':
 * @property integer $id
 * @property string $name
 * @property string $dedicated_hours
 * @property string $dedicated_start_date
 * @property string $notes
 *
 * The followings are the available model relations:
 * @property ClientDedicated[] $clientDedicated
 */
class ClientDedicatedHours extends CActiveRecord
{
    // To be used in grid
    public $clientNames;

    // Used in form
    public $clientIDs;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'client_dedicated_hours';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, dedicated_hours, dedicated_start_date, clientIDs', 'required'),
            array('name', 'length', 'max' => 100),
            array('dedicated_hours', 'numerical', 'integerOnly' => true),
            array('notes', 'length', 'max' => 300),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('clientIDs', 'safe'),
            array('id, name, dedicated_hours, dedicated_start_date, notes', 'safe', 'on' => 'search'),
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
            'clientDedicated' => array(self::HAS_MANY, 'ClientDedicated', 'client_dedicated_hours_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Name',
            'dedicated_hours' => 'Dedicated Hours',
            'dedicated_start_date' => 'Dedicated Start Date',
            'notes' => 'Notes',
            'clientNames' => 'Client Names',
            'clientIDs' => 'Clients'
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
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('dedicated_hours',$this->dedicated_hours,true);
        $criteria->compare('dedicated_start_date',$this->dedicated_start_date,true);
        $criteria->compare('notes',$this->notes,true);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('dedicated_start_date' => CSort::SORT_DESC),
                'attributes' => array('*')
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ClientDedicatedHours the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function afterFind()
    {
        if ($this->clientDedicated)
        {
            $this->clientNames = array();
            foreach ($this->clientDedicated as $clientDedicated)
                $this->clientNames[] = $clientDedicated->client->name;
        }

        return parent::afterFind();
    }

    /**
     * Returns an array of id => name for clients who have dedicated service entries.
     * @return array
     * {
     *     "1006": "Ace",
     *     "2": "Chubb",
     *     "1": "USAA"
     * }
     */
    public static function GetDedicatedServiceClients()
    {
        $models = ClientDedicated::model()->findAll(array(
            'select' => 'client_id',
            'distinct' => true,
            'with' => array(
                'client' => array(
                    'select' => 'name',
                    //'condition' => 'dedicated = 1'
                )
            ),
            'order' => 'client.name ASC'
        ));

        $returnArray = array();

        foreach ($models as $model)
        {
            $returnArray[$model->client_id] = $model->client->name;
        }

        return $returnArray;
    }
}
