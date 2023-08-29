<?php

/**
 * This is the model class for table "eng_shift_ticket_notes".
 *
 * The followings are the available columns in table 'eng_shift_ticket_notes':
 * @property integer $id
 * @property integer $eng_shift_ticket_id
 * @property integer $user_id
 * @property string $notes
 * @property string $date_created
 * @property string $date_updated
 *
 * The followings are the available model relations:
 * @property EngShiftTicket $shiftTicket
 * @property User $user
 */
class EngShiftTicketNotes extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_shift_ticket_notes';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('eng_shift_ticket_id, user_id, notes', 'required'),
            array('eng_shift_ticket_id, user_id', 'numerical', 'integerOnly' => true),
            array('notes', 'length', 'max' => 300),
            array('date_created, date_updated', 'safe'),
            // The following rule is used by search().
            array('eng_shift_ticket_id, user_id, notes, date_created, date_updated', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'shiftTicket' => array(self::BELONGS_TO, 'EngShiftTicket', 'eng_shift_ticket_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'eng_shift_ticket_id' => 'Shift Ticket',
            'user_id' => 'User',
            'notes' => 'Notes',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
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

        $criteria->compare('eng_shift_ticket_id', $this->eng_shift_ticket_id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('notes', $this->notes, true);
        $criteria->compare('date_created', $this->date_created, true);
        $criteria->compare('date_updated', $this->date_updated, true);

        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    '*'
                )
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EngShiftTicketNotes the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * This method is invoked before saving a record (after validation, if any).
     * @return boolean
     */
    protected function beforeSave()
    {
        $this->date_updated =  date('Y-m-d H:i');

        if ($this->isNewRecord)
        {
            $this->date_created = date('Y-m-d H:i');
        }

        return parent::beforeSave();
    }
}
