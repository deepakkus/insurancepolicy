 <?php

/**
 * This is the model class for table "eng_shift_ticket_status_type".
 *
 * The followings are the available columns in table 'eng_shift_ticket_status_type':
 * @property integer $id
 * @property string $type
 * @property integer $order
 * @property integer $disabled
 */
class EngShiftTicketStatusType extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_shift_ticket_status_type';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('type', 'required'),
            array('type', 'length', 'max' => 50),
            array('order, disabled', 'numerical', 'integerOnly' => true),
            array('id, type, order, disabled', 'safe', 'on' => 'search')
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'type' => 'Type',
            'order' => 'Order',
            'disabled' => 'Disabled',
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
        $criteria->compare('type', $this->type, true);
        $criteria->compare('[order]', $this->order, true);
        $criteria->compare('disabled', $this->disabled, true);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
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
     * @return EngShiftTicketStatusType the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
    * Returns an array of Status Type models that are not disabled.
    *
    * @return EngShiftTicketStatusType[]
    */
    public static function getAllActiveStatuses()
    {
        return self::model()->findAll(array('condition'=>'disabled = 0 OR disabled IS NULL', 'order' => '[order] ASC'));
    }

    protected function afterSave()
    {
        //If its a new Status Type we need to add an entry for it into the shift ticket status table for every past existing shift ticket 
        if($this->isNewRecord)
        {
            $allSTCommand = Yii::app()->db->createCommand()
			    ->select('id')
			    ->from('eng_shift_ticket');

            $stDataReader = $allSTCommand->query();
            while(($shiftTicket = $stDataReader->read()) !== false)
		    {
                $newStatus = new EngShiftTicketStatus;
                $newStatus->shift_ticket_id = $shiftTicket['id'];
                $newStatus->status_type_id = $this->id;
                $newStatus->save();
            }
        }

        return parent::afterSave();
    }
}
