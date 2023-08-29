<?php

/**
 * This is the model class for table "eng_shift_ticket_status".
 *
 * The followings are the available columns in table 'eng_shift_ticket':
 * @property integer $id
 * @property integer $shift_ticket_id
 * @property integer $status_type_id
 * @property integer $completed_by_user_id
 * @property integer $completed
 *
 * The followings are the available model relations:
 * @property EngShiftTicket $shiftTicket
 * @property EngShiftTicketStatusType $statusType
 * @property User $completedByUser
 */
class EngShiftTicketStatus extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_shift_ticket_status';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('shift_ticket_id, status_type_id', 'required'),
            array('shift_ticket_id, status_type_id, completed_by_user_id, completed', 'numerical', 'integerOnly' => true),
            array('id, shift_ticket_id, status_type_id, completed_by_user_id, completed', 'safe', 'on' => 'search')
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'shiftTicket' => array(self::BELONGS_TO, 'EngShiftTicket', 'shift_ticket_id'),
            'statusType' => array(self::BELONGS_TO, 'EngShiftTicketStatusType', 'status_type_id'),
            'completedByUser' => array(self::BELONGS_TO, 'User', 'completed_by_user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'shift_ticket_id' => 'Shift Ticket ID',
            'status_type_id' => 'Status Type ID',
            'completed_by_user_id' => 'Completed By User ID',
            'completed' => 'Completed',
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EngShiftTicketStatus the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
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
        $criteria->compare('t.id', $this->id);
        $criteria->compare('shift_ticket_id', $this->shift_ticket_id);
        $criteria->compare('status_type_id', $this->status_type_id);
        $criteria->compare('completed_by_user_id',$this->completed_by_user_id);
        $criteria->compare('completed', $this->completed);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array('*')
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 10)
        ));
    }

    /**
     * Get records of statuses associated with a shift ticket
     * @param integer $shiftTicketID
     * @return array
     */
    public static function getStatusesForShiftTicket($shiftTicketID)
    {
        $sql = '
            SELECT *
            FROM eng_shift_ticket_status [status]
            INNER JOIN eng_shift_ticket_status_type [status_type] ON [status].[status_type_id] = [status_type].[id]
            WHERE
	            [status].[shift_ticket_id] = :shift_ticket_id AND
	            ([status_type].[disabled] != 1 OR [status_type].[disabled] IS NULL)
            ORDER BY [status_type].[order] ASC
        ';

        return Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':shift_ticket_id' => $shiftTicketID
        ));
    }
}