<?php

/**
 * This is the model class for table "app_setting".
 *
 * The followings are the available columns in table 'app_setting':
 * @property integer $id
 * @property string $type
 * @property string $client_ids
 * @property string $context
 * @property string $name
 * @property string $data_type
 * @property string $value
 * @property string $effective_date
 * @property string $expiration_date
 */
class AppSetting extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'app_setting';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, minimum_resolution', 'length', 'max'=>25),
			//array('client_ids', 'length', 'max'=>100),
			array('platform_context, application_context, data_type', 'length', 'max'=>50),
			array('name', 'length', 'max'=>200),
			array('value, effective_date, expiration_date, client_ids', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, type, client_ids, platform_context, application_context, minimum_resolution, name, data_type, value, effective_date, expiration_date', 'safe', 'on'=>'search'),
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
			'client_ids' => 'Clients',
			'application_context' => 'Application Context',
            'platform_context' => 'Platform Context',
			'name' => 'Name',
			'data_type' => 'Data Type',
			'value' => 'Value',
			'effective_date' => 'Effective Date',
			'expiration_date' => 'Expiration Date',
            'minimum_resolution' => 'Minimum Resolution'
		);
	}

    public function getTypes()
    {
        return array(
                'Basic'=>'Basic',
                'Custom'=>'Custom',
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
		$criteria->compare('type',$this->type,true);
		$criteria->compare('client_ids',$this->client_ids,true);
		$criteria->compare('application_context',$this->application_context,true);
        $criteria->compare('platform_context',$this->platform_context,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('data_type',$this->data_type,true);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('effective_date',$this->effective_date,true);
		$criteria->compare('expiration_date',$this->expiration_date,true);
        $criteria->compare('minimum_resolution',$this->minimum_resolution,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AppSetting the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    //returns an array of client_ids selected for this setting
	public function getSelectedClients()
	{
        if(is_array($this->client_ids))
            return $this->client_ids;
        else
    		return explode(',', $this->client_ids);
	}

    protected function beforeSave()
    {
        if(is_array($this->client_ids))
        {
            if(in_array('0',$this->client_ids))
                $this->client_ids = '0';
            else
    			$this->client_ids = implode(",", $this->client_ids);
        }

        if(empty($this->effective_date) || $this->effective_date == '1900-01-01 00:00:00.0000000')
            $this->effective_date = null;
        if(empty($this->expiration_date) || $this->expiration_date == '1900-01-01 00:00:00.0000000')
            $this->expiration_date = null;

        return parent::beforeSave();
    }
}
