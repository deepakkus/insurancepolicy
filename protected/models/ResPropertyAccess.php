<?php

/**
 * This is the model class for table "res_property_access".
 *
 * The followings are the available columns in table 'res_property_access':
 * @property integer $id
 * @property integer $property_id
 * @property boolean $address_verified
 * @property string $best_contact_number
 * @property string $access_issues
 * @property string $gate_code
 * @property string $suppression_resources
 * @property string $other_info
 * @property string $date_created
 * @property string $date_updated
 *
 * The followings are the available model relations:
 * @property Properties $property
 */
class ResPropertyAccess extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_property_access';
	}

    /**
     * @return string the name of this model
     */
    public static function modelName()
    {
        return __CLASS__;
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('property_id', 'required'),
			array('property_id', 'numerical', 'integerOnly'=>true),
			array('best_contact_number', 'length', 'max'=>25),
			array('access_issues, suppression_resources', 'length', 'max'=>512),
			array('gate_code', 'length', 'max'=>128),
			array('other_info', 'length', 'max'=>1024),
			array('address_verified,date_created,date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, property_id, address_verified, best_contact_number, access_issues, gate_code, suppression_resources, other_info', 'safe', 'on'=>'search'),
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
			'property' => array(self::BELONGS_TO, 'Properties', 'property_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'property_id' => 'Property',
			'address_verified' => 'Address Verified',
			'best_contact_number' => 'Best Contact Number',
			'access_issues' => 'Access Issues',
			'gate_code' => 'Gate Code',
			'suppression_resources' => 'Suppression Resources',
			'other_info' => 'Other Info',
			'date_created' => 'Date Created',
			'date_updated' => 'Last Updated'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('property_id',$this->property_id);
		$criteria->compare('address_verified',$this->address_verified);
		$criteria->compare('best_contact_number',$this->best_contact_number,true);
		$criteria->compare('access_issues',$this->access_issues,true);
		$criteria->compare('gate_code',$this->gate_code,true);
		$criteria->compare('suppression_resources',$this->suppression_resources,true);
		$criteria->compare('other_info',$this->other_info,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResPropertyAccess the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	} 

	public function beforeSave()
    {
        if($this->isNewRecord)
        {           
            $this->date_created = date("Y/m/d m:i:s");
        }else{
            $this->date_updated = date("Y/m/d m:i:s");
        }
        return parent::beforeSave();
    } 
}
