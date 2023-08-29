<?php

/**
 * This is the model class for table "eng_shift_ticket_activity_type".
 *
 * The followings are the available columns in table 'eng_shift_ticket_activity_type':
 * @property integer $id
 * @property string $type
 * @property string $description
 * @property integer $active
 */
class EngShiftTicketActivityType extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'eng_shift_ticket_activity_type';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('type', 'required'),
            array('type','unique', 'message'=>'This Type Name already exists.'),
            array('type', 'length', 'max' => 25),
            array('description', 'length', 'max' => 500),
            array('active', 'numerical', 'integerOnly' => true),
            array('id, type, active, description', 'safe', 'on' => 'search')
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'engShiftTicketActivities' => array(self::HAS_MANY, 'EngShiftTicketActivity', 'eng_shift_ticket_activity_type_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'type' => 'Type',
            'active' => 'Active',
            'description' => 'Description',
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
        $criteria->compare('description', $this->description, true);
        $criteria->compare('active', $this->active);

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
     * @return EngShiftTicketActivityType the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Gets all active types
     * @return array
     */
    public static function getAllTypes()
    {
        return self::model()->findAll(array('condition'=>'active = 1', 'order' => 'id ASC'));
    }

    /***
     * Gets a List of Active Types with id as the key and type as the label (with tooltip Definitions)
     * @return array
     */
    public static function getTypeDefTTList()
    {
        $return = array();
        $types = self::model()->findAll(array('condition'=>"active = 1", 'order' => 'id ASC'));
        foreach($types as $type)
        {
            if(empty($type->description))
            {
                $return[$type->id] = $type->type;
            }
            else
            {
                $return[$type->id] = $type->type.'  <a data-toggle="tooltip" title="'.$type->description.'"><i class="icon icon-info-sign" id="defination-ico"></i></a>';
            }
        }
        return $return;
    }

    /***
     * Gets a List of Active Types with id as the key and type as the label (with tooltip Definitions)
     * @return array
     */
    public static function getTypeDefList()
    {
        $return = '<ul>';
        $types = self::model()->findAll(array('condition'=>"active = 1 AND description IS NOT NULL and description != ''", 'order' => 'id ASC'));
        foreach($types as $type)
        {
            $return .= '<li><strong style="text-decoration:underline;">'.$type->type.':</strong> '.$type->description.'</li>';
        }
        $return .= '</ul>';
        return $return;
    }

    /**
    *   Returns all activity types in an HTML List format
    *   @param array $shiftTicketGridSubColumnsToShow
    *   @return $activitiesList
    */
    public function geActivityTimeHTMLList($shiftTicketGridSubColumnsToShow)
    {
        $activitiesList = "<ul>";
        $activityTypes = $this->getAllTypes();
        foreach($activityTypes as $activity)
        {
            $activitiesList .= '<li style="list-style-type: none;"><input type="checkbox" style="margin-right:5px;margin-top:0px;" name="activitytypes[]" value="'.$activity->id.'" '.((in_array($activity->id,$shiftTicketGridSubColumnsToShow))?"checked=checked":"").' />'.$activity->type.' Time</li>';
        }
        $activitiesList .= '</ul>';
        return $activitiesList;
    }
}
