<?php

/**
 * This is the model class for table "eng_shift_ticket_activity".
 *
 * The followings are the available columns in table 'eng_shift_ticket_activity':
 * @property integer $id
 * @property integer $eng_shift_ticket_id
 * @property integer $eng_shift_ticket_activity_type_id
 * @property string $start_time
 * @property string $end_time
 * @property string $comment
 * @property integer $res_ph_visit_id
 * @property integer $billable
 * @property string $tracking_location
 * @property string $tracking_location_end
 *
 * The followings are the available model relations:
 * @property EngScheduling $engScheduling
 * @property EngShiftTicket $engShiftTicket
 * @property EngShiftTicketActivityType $engShiftTicketActivityType
 */
class EngShiftTicketActivity extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_shift_ticket_activity';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('eng_shift_ticket_id, eng_shift_ticket_activity_type_id, start_time, end_time', 'required'),
            array('eng_shift_ticket_id, eng_shift_ticket_activity_type_id, res_ph_visit_id', 'numerical', 'integerOnly' => true),
            array('comment', 'length', 'max' => 200),
            array('tracking_location, tracking_location_end', 'length', 'max' => 50),
            array('billable', 'safe'),
            // Custom start/end validation logic
            // Will not allow time overlap with any other activity, but WILL allow 8AM exactly to overlap with 8AM exactly
            array('start_time', 'validationStartTime'),
            array('end_time', 'validateEndTime'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, eng_shift_ticket_id, eng_shift_ticket_activity_type_id, start_time, end_time, comment, res_ph_visit_id, billable, tracking_location, tracking_location_end', 'safe', 'on' => 'search')
        );
    }

    public function validationStartTime($attribute)
    {
        $sql = '
            DECLARE @startTime time(7) = :start_time
            SELECT COUNT(*)
            FROM eng_shift_ticket_activity
            WHERE eng_shift_ticket_id = :shift_ticket_id
             AND (start_time <= @startTime AND end_time >= @startTime)
        ';

        $params = array(
            ':shift_ticket_id' => $this->eng_shift_ticket_id,
            ':start_time' => $this->start_time
        );

        if ($this->scenario !== 'insert')
        {
            $sql .= ' AND id != :id';
            $params[':id'] = $this->id;
        }
        if($this->start_time==$this->end_time)
        {
            $this->addError($attribute, 'Start time and end time can not be same!');
        }
        $count = Yii::app()->db->createCommand($sql)->queryScalar($params);

        if ($count)
        {
            $this->addError($attribute, 'Start time overlaps with another activity!');
        }
    }

    public function validateEndTime($attribute)
    {
        $sql = '
            DECLARE @endTime time(7) = :end_time
            SELECT COUNT(*)
            FROM eng_shift_ticket_activity
            WHERE eng_shift_ticket_id = :shift_ticket_id
	            AND (start_time < @endTime AND end_time > @endTime)
        ';

        $params = array(
            ':shift_ticket_id' => $this->eng_shift_ticket_id,
            ':end_time' => $this->end_time
        );

        if ($this->scenario !== 'insert')
        {
            $sql .= ' AND id != :id';
            $params[':id'] = $this->id;
        }
        if($this->start_time==$this->end_time)
        {
            $this->addError($attribute, 'Start time and end time can not be same!');
        }
        $count = Yii::app()->db->createCommand($sql)->queryScalar($params);

        if ($count)
        {
            $this->addError($attribute, 'End time overlaps with another activity!');
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'engShiftTicket' => array(self::BELONGS_TO, 'EngShiftTicket', 'eng_shift_ticket_id'),
            'engShiftTicketActivityType' => array(self::BELONGS_TO, 'EngShiftTicketActivityType', 'eng_shift_ticket_activity_type_id'),
            'resPhVisit' => array(self::BELONGS_TO, 'ResPhVisit', 'res_ph_visit_id'),
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
            'eng_shift_ticket_activity_type_id' => 'Activity Type',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'comment' => 'Comment',
            'res_ph_visit_id' => 'Policyholder Visit',
            'billable' => 'Billable',
            'tracking_location' => 'Begin',
            'tracking_location_end' => 'End',
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

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('eng_shift_ticket_id', $this->eng_shift_ticket_id);
        $criteria->compare('eng_shift_ticket_activity_type_id', $this->eng_shift_ticket_activity_type_id);
        $criteria->compare('start_time', $this->start_time, true);
        $criteria->compare('end_time', $this->end_time, true);
        $criteria->compare('comment', $this->comment, true);
        $criteria->compare('res_ph_visit_id', $this->res_ph_visit_id);
        $criteria->compare('billable', $this->billable);
        $criteria->compare('tracking_location', $this->tracking_location);
        $criteria->compare('tracking_location_end', $this->tracking_location_end);

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
     * @return EngShiftTicketActivity the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns all activities that are connected to a given shift ticket
     * @return array
     */
    public function getAllActivities()
    {
        //Pull all of activites and return them
        $sql = '
                SELECT
                    a.id,
                    CONVERT(varchar(5), a.start_time) start_time,
                    CONVERT(varchar(5), a.end_time) end_time,
                    t.type as activity,
                    a.comment,
                    phv.id visit_id,
                    m.last_name,
                    a.billable,
                    a.tracking_location,
                    a.tracking_location_end
                FROM eng_shift_ticket_activity a
                INNER JOIN eng_shift_ticket_activity_type t ON t.id = a.eng_shift_ticket_activity_type_id
                LEFT JOIN res_ph_visit phv ON phv.id = a.res_ph_visit_id
                LEFT JOIN properties p ON p.pid = phv.property_pid
                LEFT JOIN members m ON m.mid = p.member_mid
                WHERE a.eng_shift_ticket_id = :shift_ticket_id
                ORDER BY a.start_time ASC
            ';
        $id = $this->eng_shift_ticket_id;

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':shift_ticket_id', $id, PDO::PARAM_INT)
            ->queryAll();
    }
}
