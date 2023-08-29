<?php

/**
 * This is the model class for table "risk_batch_file".
 *
 * The followings are the available columns in table 'risk_batch_file':
 * @property integer $id
 * @property string $file_name
 * @property integer $client_id
 * @property string $date_created
 * @property integer $batch_id
 * @property string $status
 * @property string $date_run
 * @property string $type
 * @property integer $version_id
 */
class RiskBatchFile extends CActiveRecord
{
    public $clientName;
    public $percentageComplete;
    public $version;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'risk_batch_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('file_name', 'required'),
			array('client_id, batch_id, version_id', 'numerical', 'integerOnly'=>true),
			array('file_name', 'length', 'max'=>50),
            array('status', 'length', 'max'=>25),
            array('type', 'length', 'max'=>10),
			array('date_created, date_run', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, file_name, client_id, date_created, date_run, batch_id, status, type, version', 'safe', 'on'=>'search'),
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
            'riskScore' => array(self::HAS_MANY, 'RiskScore', 'batch_file_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'property' => array(self::BELONGS_TO, 'Property', 'client_property_id'),
            'riskVersion' => array(self::BELONGS_TO, 'RiskVersion', 'version_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'file_name' => 'File/Bulk Run Name',
			'client_id' => 'Client',
			'date_created' => 'Date Created',
			'batch_id' => 'Batch',
            'status' => 'Status',
            'date_run' => 'Date Run',
            'type' => 'Type',
            'version_id' => 'Version',

            // Virtual Attirubte
            'version' => 'Version'
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
		$criteria=new CDbCriteria;

        $criteria->with = array(
            'riskVersion' => array(
                'select' => array('id','version')
            )
        );

		$criteria->compare('id',$this->id);
		$criteria->compare('file_name',$this->file_name,true);
		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('batch_id',$this->batch_id);
        $criteria->compare('status',$this->status);
        $criteria->compare('date_run',$this->date_created,true);
        $criteria->compare('type',$this->type,true);
        $criteria->compare('riskVersion.version', $this->version, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array(
                    'version' => array(
                        'asc' => 'riskVersion.version ASC',
                        'desc' => 'riskVersion.version DESC'
                    ),
                    '*'
                )
            ),
            'pagination' => array('pageSize' => 25)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RiskBatchFile the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
	{
        if ($this->isNewRecord)
            $this->date_created = date('Y-m-d H:i');

        if ($this->client_id)
        {
            // If is for a client
            $this->batch_id = RiskBatchFile::model()->count('client_id = :client_id', array(':client_id' => $this->client_id)) + 1;
        }

        return parent::beforeSave();
    }

    protected function afterFind()
	{
        if ($this->client)
        {
            $this->clientName = $this->client->name;
        }

        if ($this->riskVersion)
        {
            $this->version = $this->riskVersion->version;
        }

        if ($this->status == 'processing')
        {
            $this->percentageComplete = $this->getPercentage($this->id);
        }
        elseif ($this->status == 'complete')
        {
            $this->percentageComplete = 100;
        }
        else
        {
            $this->percentageComplete = 0;
        }

        return parent::afterFind();
    }


    //----------------------------------General Functions --------------------------------//

    /**
     * List data of clients with wds_risk type
     * @return array
     */
    public function getRiskClients()
    {
        return CHtml::listData(Client::model()->findAllByAttributes(array('wds_risk' => 1)), 'id', 'name');
    }

    /**
     * Import the csv into the risk_batch table
     * @param string $filePath
     * @return double|integer
     */
    public function importCSV($filePath)
    {
        //In case it's a big file, make sure the time limit won't kill the imoprt - not sure if these are entirely necessary
        set_time_limit(0);
        ini_set('max_execution_time', 900); //15 minutes

        $versionID = RiskVersion::getLiveVersionID();

        $command = Yii::app()->db->createCommand();
        $attributeArray = array();

        $row = 0;
        if (($handle = fopen($filePath, 'r')) !== FALSE)
        {
            while (($data = fgetcsv($handle, 2500, ',')) !== FALSE)
            {
                if($row > 0)
                {
                    //Create new entry
                    //$entry = new RiskScore;

                    //Fill in generic info from csv
                    foreach($_POST['FieldMap'] as $key => $value)
                    {
                        if (isset($data[$value]))
                        {
                            $attributeArray[$key] = $data[$value];
                        }
                    }

                    //Make note of batch
                    $attributeArray['batch_file_id'] = $this->id;
                    $attributeArray['client_id'] = $this->client_id;
                    $attributeArray['version_id'] = $versionID;

                    $rowsAffected = $command->insert('risk_score', $attributeArray);

                    if ($rowsAffected < 1)
                    {
                        $errorfile = fopen(dirname($filePath) . DIRECTORY_SEPARATOR . "errors-batch" . $this->id . ".txt", "a");
                        $error = "\r\nCould not import: " . $attributeArray['address'] . " " . $attributeArray['city'] . " " . $attributeArray['state'] . "\r\n";
                        fwrite($errorfile, $error);
                    }
                }
                $row += 1;
            }
            fclose($handle);
        }

        $this->save();

        return $row;
    }

    /**
     * Import the pif into the risk_score table
     * @return integer
     */
    public function importPif()
    {
        //In case it's a big file, make sure the time limit won't kill the imoprt - not sure if these are entirely necessary
        set_time_limit(0);
        ini_set('max_execution_time', 900); //15 minutes

        //Get IDs for query
        $clientID = $this->client_id;
        $batchID = $this->id;

        //SQL to copy PIF over to risk score table
        $sql = "insert into risk_score
                    (batch_file_id, property_pid, first_name, last_name, address, city, state, zip, lat, long, match_score, wds_geocode_level, version_id)
                select
	                :batch_id,
	                p.pid,
	                substring(m.first_name, 0, 50) as first_name,
	                substring(m.last_name, 0, 50) as last_name,
	                substring(p.address_line_1, 0, 50) as address,
	                substring(p.city, 0, 50) as city,
                    substring(p.[state], 0, 3) as [state],
	                substring(p.zip, 0, 6) as zip,
	                wds_lat,
	                wds_long,
	                wds_match_score,
	                wds_geocode_level,
                    (select id from risk_version where is_live = 1)
                from
	                properties p
                inner join
	                members m on m.mid = p.member_mid
                where
	                m.client_id = :client_id
	                and is_tester = 0
	                and p.policy_status = 'active'
	                and p.[type_id] = 1
	                and ( (wds_geocoder = 'mapbox' and wds_geocode_level != 'unmatched' and wds_match_score >= '.73') or (wds_geocode_level = 'client') )
                order by
	                pid asc;";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':batch_id', $batchID, PDO::PARAM_INT)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->query();

    }

    /**
     * Fire off the batch for a given id - calls the yiic command so it runs in the background
     */
    public function runRisk()
    {
        //pclose(popen('start php ' . Yii::app()->basePath . DIRECTORY_SEPARATOR  . 'yiic riskbatch ' . $this->id . ' >nul', 'r'));
    }

    /**
     * Figures out the percentage of completion for a given risk batch
     * id = id of the risk batch (foreign key to risk score table)
    */
    public function getPercentage($id){
        $sql = "
            declare @batchFileID int = :batch_file_id;
            declare @total int = (select count(id) from risk_score where batch_file_id = @batchFileID);
            declare @complete int = (select count(id) from risk_score where batch_file_id = @batchFileID and processed = 1);

            select @complete as complete, @total as total;
        ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':batch_file_id', $id, PDO::PARAM_INT);
        $result = $command->queryRow();

        return (isset($result['total']) && $result['total']) ? round($result['complete'] / $result['total'], 4) * 100 : 0;
    }
}
