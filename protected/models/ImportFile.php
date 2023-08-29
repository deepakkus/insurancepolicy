<?php

/**
 * This is the model class for table "import_files".
 *
 * The followings are the available columns in table 'import_files':
 * @property integer $id
 * @property string $file_path
 * @property string $date_time
 * @property string $status
 * @property string $details
 * @property string $client
 * @property string $errors
 * @property string $type
 * @property integer $client_id
 *
 * The followings are the available model relations:
 * @property Properties[] $properties
 */
class ImportFile extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'import_files';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('file_path, status, type', 'required'),
			array('id, client_id', 'numerical', 'integerOnly'=>true),
			array('file_path', 'length', 'max'=>300),
            array('status, client, type, date_time', 'length', 'max'=>50),
            array('details, errors', 'length', 'max'=>500),
			// The following rule is used by search().
			array('id, file_path, date_time, status, details, client, errors, type, client_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
        return array(
            'clientName' => array(self::BELONGS_TO, 'Client', 'client_id')
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'file_path' => 'File Name',
			'date_time' => 'Timestamp',
			'status' => 'Status',
			'details' => 'Details',
            'client' => 'Client',
            'errors'=> 'Errors',
            'type'=> 'Type',
            'client_id'=> 'Client'
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
	public function search($pageSize = 25, $sort = 'id')
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('file_path',$this->file_path,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('details',$this->details,true);
        $criteria->compare('client', $this->client, true);
        $criteria->compare('errors', $this->errors, true);
        $criteria->compare('type', $this->type, true);
        $criteria->compare('client_id', $this->client_id, true);
        if($this->date_time){
            $date_time = strtotime($this->date_time);
            if ($date_time !== false && $date_time > strtotime('1753-01-01') && $date_time < strtotime('9999-12-31'))
	        {
		        $criteria->addCondition("date_time >= '" . date('Y-m-d', $date_time) . "' AND t.date_time < '" . date('Y-m-d', strtotime($this->date_time . ' + 1 day')) . "'");
	        }
        }
        $sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

        $dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
				'attributes'=>array('*',),
			),
			'criteria'=>$criteria,
		));

		if($pageSize == NULL)
		{
			$dataProvider->pagination = false;
		}
		else
		{
			$dataProvider->pagination->pageSize = $pageSize;
			$dataProvider->pagination->validateCurrentPage = false;
		}

        return $dataProvider;
	}

    protected function afterFind()
    {
        // Convert the date/time fields to display format.
        $format = 'm/d/Y h:i A';
		if(isset($this->date_time))
	        $this->date_time = date_format(new DateTime($this->date_time), $format);
    }

    public function beforeSave()
    {
        if(!empty($this->client_id))
        {
            $this->client = Client::model()->findByPk($this->client_id)->name;
        }

        return parent::beforeSave();
    }

	/**
     * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Agent the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array possible statuses (name=>label)
     */
	public static function getStatuses()
	{
		return array(
			'Uploaded' => 'Uploaded',
            'Processing' => 'Processing',
            'Error' => 'Error',
            'Finished' => 'Finished',
		);
	}

    /**
     * @return array possible types (name=>label)
     */
	public static function getTypes()
	{
		return array(
			'USAA - Add Drop PIF'=>'USAA - Add Drop PIF',
			'USAA - Change PIF' => 'USAA - Change PIF',
			'USAA - Audit' => 'USAA - Audit',
			'WDS - Update Lat Long' => 'WDS - Update Lat Long',
			'WDS - FS Offered' => 'WDS - FS Offered',
			'WDS - Update Geo Risk' => 'WDS - Update Geo Risk',
            'WDS - Property Statuses Update' => 'WDS - Property Statuses Update',
            'Chubb - Eligible PIF Import' => 'Chubb - Eligible PIF Import',
            'Chubb - Enrolled PIF Import' => 'Chubb - Enrolled PIF Import',
            'LM/SAF - Full PIF Import' => 'LM/SAF - Full PIF Import',
            'LM/SAF - Incremental PIF Import' => 'LM/SAF - Incremental PIF Import',
            'WDS - PR Call List' => 'WDS - PR Call List',
            'Nationwide - Enrolled PIF Import' => 'Nationwide - Enrolled PIF Import',
            'Nationwide - Eligible PIF Import' => 'Nationwide - Eligible PIF Import',
			'Pharm - PIF Import' => 'Pharm - PIF Import',
            'MOE - PIF Import' => 'MOE - PIF Import',
            'Ace - PIF Import' => 'Ace - PIF Import',
            'Firemans Fund - PIF Import' => 'Firemans Fund - PIF Import',
            'Pemco - PIF Import' => 'Pemco - PIF Import',
            'Cincinnati - PIF Import' => 'Cincinnati - PIF Import',
            'Full PIF' => 'Full PIF'
		);
	}

}

