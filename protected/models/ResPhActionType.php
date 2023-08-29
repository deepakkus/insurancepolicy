<?php

/**
 * This is the model class for table "res_ph_action_type".
 *
 * The followings are the available columns in table 'res_ph_action_type':
 * @property integer $id
 * @property string $name
 * @property integer $active
 * @property integer $category_id
 * @property string $definition
 * @property string $units
 * @property string $app_sub_category
 * @property integer $action_item_order
 *
 * The followings are the available model relations:
 * @property ResPhActionCategory $phActionCategory
 * @property ResPhAction[] $phActions
 */
class ResPhActionType extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_ph_action_type';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('name, category_id, app_sub_category', 'required'),
            array('active, category_id, action_item_order', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 100),
            array('definition', 'length', 'max' => 255),
            array('action_type', 'length', 'max' => 25),
            array('units', 'length', 'max' => 150),
            array('action_item_order', 'safe'),
            array('name, active, category_id, definition, action_type, units, app_sub_category, action_item_order', 'safe', 'on' => 'search')
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'phActionCategory' => array(self::BELONGS_TO, 'ResPhActionCategory', 'category_id'),
            'phActions' => array(self::HAS_MANY, 'ResPhAction', 'action_type_id')
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
            'active' => 'Active',
            'category_id' => 'Category',
            'definition' => 'Definition',
            'action_type' => 'Action Type',
            'units' => 'Unit',
            'app_sub_category' => 'App Sub Category',
            'action_item_order' => 'Action Item Order'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($categoryID)
    {
        $criteria = new CDbCriteria;
        $criteria->compare('name', $this->name, true);
        $criteria->compare('active', $this->active);
        $criteria->compare('definition', $this->definition, true);
        $criteria->compare('action_type', $this->action_type, true);
        $criteria->compare('units', $this->units, true);
        $criteria->addCondition('category_id = :category_id');       
        $criteria->params[':category_id'] = $categoryID;
        $criteria->compare('app_sub_category', $this->app_sub_category, true);
        $criteria->compare('action_item_order',$this->action_item_order);
      
        $sort = new CSort;
        $sort->defaultOrder = array('name' => CSort::SORT_ASC);
        $sort->attributes = array('*');
        $sort->route = 'resPhActionType/manageSearch';
        $sort->params = array('id' => $categoryID);

        $pagination = new CPagination;
        $pagination->pageSize = 5;
        $pagination->route = 'resPhActionType/manageSearch';
        $pagination->params = array('id' => $categoryID);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => $pagination
        ));
    }

    /**
     * Return all policyholder action categories
     * @return ResPhActionCategory[]
     */
    public function getCategories()
    {
        return ResPhActionCategory::model()->findAll(array(
            'order' => 'category ASC'
        ));
    }
    /*
    * Return sub categories
    */
    public function getSubCategories()
    {
       return (array('Firefighter Actions'=>'Firefighter Actions',
            'Customer Service' => 'Customer Service',
            'Consumables' => 'Consumables'
        ));
    }
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResPhActionType the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
