<?php

/**
 * This is the model class for table "AuthAssignment".
 *
 * The followings are the available columns in table 'AuthAssignment':
 * @property string $itemname
 * @property integer $userid
 * @property string $bizrule
 * @property string $data
 *
 * The followings are the available model relations:
 * @property AuthItem $authItem
 */
class AuthAssignment extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'auth_assignment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('itemname, userid', 'required'),
            array('userid', 'numerical', 'integerOnly'=>true),
			array('itemname', 'length', 'max'=>64),
			array('bizrule, data, date_created', 'safe'),
			// The following rule is used by search().
			array('itemname, userid, bizrule, data', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'authItem' => array(self::BELONGS_TO, 'AuthItem', 'itemname'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'itemname' => 'Name',
			'userid' => 'User ID',
			'bizrule' => 'Business Rule',
			'data' => 'Data'
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

		$criteria->compare('itemname', $this->itemname, true);
		$criteria->compare('userid', $this->userid, true);
		$criteria->compare('bizrule', $this->bizrule, true);
		$criteria->compare('data', $this->data, true);

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder'=>array(
                    'itemname' => CSort::SORT_ASC
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
     * Retrieves a list of user models based on auth item searched for
     * @param string $name 
     * @return CActiveDataProvider
     */
    public static function searchAuthItemUsers($name)
    {
        $assignmentTable = Yii::app()->getModule('wdsauth')->authManager->assignmentTable;

        return new CActiveDataProvider('User', array(
            'sort' => array(
                'defaultOrder' =>array(
                    'username' => CSort::SORT_ASC
                )
            ),
            'criteria' => array(
                'select' => array(
                    '[t].[name]',
                    '[t].[username]'
                ),
                'condition' => '[assignment].[itemname] = :name',
                'join' => 'INNER JOIN [' . $assignmentTable . '] [assignment] ON [t].[id] = [assignment].[userid]',
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
	 * @return AuthAssignment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
