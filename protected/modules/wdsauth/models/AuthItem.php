<?php

/**
 * This is the model class for table "AuthItem".
 *
 * The followings are the available columns in table 'AuthItem':
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $bizrule
 * @property string $data
 *
 * The followings are the available model relations:
 * @property AuthItemChild[] $authItemChildrenParent
 * @property AuthItemChild[] $authItemChildrenChild
 * @property AuthAssignment[] $authAssignments
 */
class AuthItem extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'auth_item';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, type', 'required'),
			array('type', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 64),
			array('description, bizrule, data', 'safe'),
			// The following rule is used by search().
			array('name, type, description, bizrule, data', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'authItemChildrenParent' => array(self::HAS_MANY, 'AuthItemChild', 'parent'),
			'authItemChildrenChild' => array(self::HAS_MANY, 'AuthItemChild', 'child'),
			'authAssignments' => array(self::HAS_MANY, 'AuthAssignment', 'itemname'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'name' => 'Name',
			'type' => 'Type',
			'description' => 'Description',
			'bizrule' => 'Business Rule',
			'data' => 'Data'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
     * @param $name (optional) auth item and type to exclude from search
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search($name = null)
	{
		$criteria = new CDbCriteria;

		$criteria->compare('name', $this->name, true);
		$criteria->compare('type', $this->type);
		$criteria->compare('description', $this->description, true);
		$criteria->compare('bizrule', $this->bizrule, true);
		$criteria->compare('data', $this->data, true);

        // For index method, don't display operations
        if (Yii::app()->controller->action->id === 'index')
        {
            $criteria->addCondition('type != :type');
            $criteria->params[':type'] = CAuthItem::TYPE_OPERATION;
        }

        if ($name !== null)
        {
            $authItem = Yii::app()->authManager->getAuthItem($name);

            $criteria->addCondition('name != :name');
            // Only allow next level of item
            $criteria->addCondition('type = :type - 1');
            $criteria->params[':name'] = $authItem->getName();
            $criteria->params[':type'] = $authItem->getType();
        }

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder'=>array(
                    'type' => CSort::SORT_DESC,
                    'name' => CSort::SORT_ASC
                ),
				'attributes' => array('*')
			),
			'criteria' => $criteria,
            'pagination' => array(
                'PageSize' => 20
            )
		));
	}

    /**
     * Retrieves a list of children models for the name and type of the passed in auth item
     * @param string $name name of the item to find children of
     * @param integer $type auth type of children
     * @return CActiveDataProvider
     */
    public static function searchChildrenAuthItemsByType($name, $type)
    {
        $itemChildTable = Yii::app()->getModule('wdsauth')->authManager->itemChildTable;

        return new CActiveDataProvider('AuthItem', array(
            'sort' => array(
                'defaultOrder' =>array(
                    'name' => CSort::SORT_ASC
                )
            ),
            'criteria' => array(
                'select' => array(
                    '[t].[name]',
                    '[t].[type]',
                    '[t].[description]',
                    '[t].[bizrule]',
                    '[t].[data]'
                ),
                'condition' => '[t].[type] = :type AND [child].[parent] = :name',
                'join' => 'INNER JOIN [' . $itemChildTable . '] [child] ON [t].[name] = [child].[child]',
                'params' => array(
                    ':type' => $type, 
                    ':name' => $name
                )
            ),
            'pagination' => false,
        ));
    }

    /**
     * Retrieves a list of auth item child models based on auth item searched for
     * @param string $name
     * @return CActiveDataProvider
     */
    public static function searchChildrenAuthItems($name)
    {
        $itemChildTable = Yii::app()->getModule('wdsauth')->authManager->itemChildTable;

        return new CActiveDataProvider('AuthItem', array(
            'sort' => array(
                'defaultOrder' =>array(
                    'name' => CSort::SORT_ASC
                )
            ),
            'criteria' => array(
                'select' => array(
                    '[t].[name]',
                    '[t].[type]',
                    '[t].[description]',
                    '[t].[bizrule]',
                    '[t].[data]'
                ),
                'condition' => '[child].[parent] = :name',
                'join' => 'INNER JOIN [' . $itemChildTable . '] [child] ON [t].[name] = [child].[child]',
                'params' => array(
                    ':name' => $name
                )
            ),
            'pagination' => false,
        ));
    }

    /**
     * Retrieves a list of auth item parent models based on auth item searched for
     * @param string $name
     * @return CActiveDataProvider
     */
    public static function searchParentAuthItems($name)
    {
        $itemChildTable = Yii::app()->getModule('wdsauth')->authManager->itemChildTable;

        return new CActiveDataProvider('AuthItem', array(
            'sort' => array(
                'defaultOrder' =>array(
                    'name' => CSort::SORT_ASC
                )
            ),
            'criteria' => array(
                'select' => array(
                    '[t].[name]',
                    '[t].[type]',
                    '[t].[description]',
                    '[t].[bizrule]',
                    '[t].[data]'
                ),
                'condition' => '[child].[child] = :name',
                'join' => 'INNER JOIN [' . $itemChildTable . '] [child] ON [t].[name] = [child].[parent]',
                'params' => array(
                    ':name' => $name
                )
            ),
            'pagination' => false,
        ));
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AuthItem the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
