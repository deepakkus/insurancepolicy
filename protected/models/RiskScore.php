<?php

/**
 * This is the model class for table "risk_score".
 *
 * The followings are the available columns in table 'risk_score':
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $lat
 * @property string $long
 * @property string $client_property_id
 * @property string $score_v
 * @property string $score_whp
 * @property string $score_wds
 * @property integer $score_type
 * @property integer $processed
 * @property integer $client_id
 * @property integer $batch_file_id
 * @property integer $property_pid
 * @property string $geojson
 * @property integer $geocoded
 * @property string $match_type
 * @property string $match_score
 * @property string $match_address
 * @property string $date_created
 * @property string $wds_geocode_level
 * @property string $client_member_id
 * @property integer $user_id
 * @property integer $version_id
 *
 * @property RiskScoreType $riskScoreType
 * @property Client $client
 * @property Property $property
 * @property User $user
 * @property RiskVersion $riskVersion
 */
class RiskScore extends CActiveRecord
{
    public $scoreType;
    public $clientName;
    public $userName;
    public $queryType;
    public $version;

    public $searchStartDate;
    public $searchEndDate;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'risk_score';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('score_type, processed, client_id, batch_file_id, geocoded, property_pid, user_id, version_id', 'numerical', 'integerOnly'=>true),
			array('first_name, last_name, address, city', 'length', 'max'=>50),
			array('state', 'length', 'max'=>2),
			array('zip', 'length', 'max'=>10),
            array('lat', 'length', 'max'=>20),
			array('client_property_id, match_type, match_score, long, wds_geocode_level', 'length', 'max'=>25),
            array('client_member_id', 'length', 'max'=>50),
			array('score_v, score_whp, score_wds', 'length', 'max'=>12),
            array('match_address', 'length', 'max'=>100),
			//array('geojson', 'length', 'max'=>8000),
			array('date_created, searchStartDate, searchEndDate', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, first_name, last_name, address, city, state, zip, lat, long, client_property_id, score_v, score_whp, score_wds, score_type, '
            . 'geocoded, client_id, batch_file_id, property_pid, geojson, processed, match_type, match_score, match_address, date_created, searchStartDate, '
            . 'searchEndDate, wds_geocode_level, client_member_id, clientName, userName, version', 'safe', 'on'=>'search'),
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
            'riskScoreType' => array(self::BELONGS_TO, 'RiskScoreType', 'score_type'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
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
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'address' => 'Address',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'lat' => 'Lat',
			'long' => 'Long',
			'client_property_id' => 'Client Property ID',
			'score_v' => 'Score V',
			'score_whp' => 'Score WHP',
			'score_wds' => 'Score WDS',
			'score_type' => 'Score Type',
			'processed' => 'Processed',
			'client_id' => 'Client',
			'batch_file_id' => 'Batch File',
            'property_pid' => 'Property PID',
			'geojson' => 'Geojson',
            'geocoded' => 'Geocoded',
            'match_type' => 'Match Type',
            'match_score' => 'Match Score',
            'match_address' => 'Match Address',
			'date_created' => 'Date Created',
            'wds_geocode_level' => 'WDS Geocode Level',
            'client_member_id' => 'Client Member ID',
            'user_id' => 'User ID',
            'version_id' => 'Version',

            // Virtual Attributes
            'scoreType' => 'Score Type',
            'clientName'=> 'Client Name',
            'userName' => 'User Name',
            'queryType' => 'Query Type',
            'version' => 'Version'
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

		$criteria->compare('id',$this->id);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('lat',$this->lat,true);
		$criteria->compare('long',$this->long,true);
		$criteria->compare('client_property_id',$this->client_property_id,true);
		$criteria->compare('score_v',$this->score_v,true);
		$criteria->compare('score_whp',$this->score_whp,true);
		$criteria->compare('score_wds',$this->score_wds,true);
		$criteria->compare('score_type',$this->score_type);
		$criteria->compare('processed',$this->processed);
        $criteria->compare('client_id',$this->client_id);
		$criteria->compare('batch_file_id',$this->batch_file_id);
        $criteria->compare('property_pid',$this->property_pid);
		$criteria->compare('geojson',$this->geojson,true);
        $criteria->compare('geocoded',$this->geocoded);
        $criteria->compare('match_type',$this->match_type);
        $criteria->compare('match_score',$this->match_score);
        $criteria->compare('match_address',$this->match_address);
		$criteria->compare('date_created',$this->date_created,true);
        $criteria->compare('wds_geocode_level',$this->wds_geocode_level,true);
        $criteria->compare('client_member_id',$this->client_member_id);
        $criteria->compare('user_id',$this->user_id);
        $criteria->compare('version_id',$this->version_id);

        if ($this->searchStartDate)
        {
            $criteria->addCondition("date_created >= '$this->searchStartDate'");
        }

        if ($this->searchEndDate)
        {
            $criteria->addCondition("date_created < '" . date('Y-m-d', strtotime($this->searchEndDate . ' + 1 day')) . "'");
        }

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array(
                    '*'
                )
			),
			'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RiskScore the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function afterFind()
    {
        if ($this->riskScoreType)
        {
            $this->scoreType = $this->riskScoreType->type;
        }

        if ($this->client)
        {
            $this->clientName = $this->client->name;
        }

        if ($this->user)
        {
            $this->userName = $this->user->username;
        }

        if ($this->riskVersion)
        {
            $this->version = $this->riskVersion->version;
        }

        if (is_null($this->client_id))
        {
            $this->clientName = 'WDS';
        }

        return parent::afterFind();
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord)
        {
            if (empty($this->version_id))
            {
                $this->version_id = RiskVersion::getLiveVersionID();
            }
        }

        return parent::beforeSave();
    }

    protected function afterSave()
	{
        //Save this in the client transation table
        $modelTransaction = new ClientTransaction;

        //Assign values from the current transaction
        $modelTransaction->client_id = $this->client_id;
        $modelTransaction->address = $this->address;
        $modelTransaction->city = $this->city;
        $modelTransaction->state = $this->state;
        $modelTransaction->score = $this->score_wds;
        $modelTransaction->service = "wdsrisk";
        $modelTransaction->type = strtolower(RiskScoreType::model()->findByPk($this->score_type)->type);
        $modelTransaction->status = ($this->wds_geocode_level == 'unmatched') ? 0 : 1;
        $modelTransaction->date_created = date("Y-m-d H:i:s");

        //Indicate if it didn't work - not sure if there's a better way?
        if (!$modelTransaction->save())
        {
            print "Couldn't save the transaction record! \n";
            print_r($modelTransaction->getErrors());
        }

        //If there's a property, than update the entry in the property table
        if($this->property_pid)
        {
            $sql = "
            UPDATE properties
            SET
                risk_date_created = :date_created,
                risk_score_v = :score_v,
                risk_score_whp = :score_whp,
                risk_score = :risk_score
            WHERE
                pid = :pid";

            //Assign to variables because yii bind doesn't like objects??!
            $pid = $this->property_pid;
            $dateCreated = $this->date_created;
            $scoreV = $this->score_v;
            $scoreWHP = $this->score_whp;
            $riskScore = $this->score_wds;

            Yii::app()->db->createCommand($sql)
                ->bindParam(':pid', $pid, PDO::PARAM_INT)
                ->bindParam(':date_created', $dateCreated, PDO::PARAM_STR)
                ->bindParam(':score_v', $scoreV, PDO::PARAM_STR)
                ->bindParam(':score_whp', $scoreWHP, PDO::PARAM_STR)
                ->bindParam(':risk_score', $riskScore, PDO::PARAM_STR)
                ->execute();
        }

        return parent::afterSave();
    }

    //----------------------------------General Functions --------------------------------//

    /**
     * Look up the most recent risk score for the given pid
     * @param integer $pid
     * @return RiskScore|null RiskScore instance.
     */
    public function getRiskScore($pid)
    {
        //Get live riskVersionId
        $liveVersion = RiskVersion::getLiveVersionID();
        return RiskScore::model()->findByAttributes(array('property_pid' => $pid, 'version_id' => $liveVersion), array('order' => 'id DESC'));
    }

    /**
     * Create a risk score for a given property instance
     * Defaults to $result['error'] = true, user is notified of error reason through $result['message']
     *
     * @param object $property - Property model instance
     * @param integer $type - Risk score type from "risk_score_type" table
     * @return array
     */
    public function setRiskScore($property, $type)
    {
        $result = array(
            'error' => true,
            'message' => 'There was an error'
        );

        if (!$property instanceof Property)
        {
            Yii::log('ERRORS SAVING RiskScore ENTRY: the passed in property is not an instance of the Property model', CLogger::LEVEL_ERROR, __METHOD__);
            $result['error'] = true;
            $result['message'] = 'property is not an instance of the Property model';
            return $result;
        }

        // Get coordinates for address
        $address = $property->address_line_1 . ', ' . $property->city . ', ' . $property->state . ' ' . $property->zip;
        $geocode = Geocode::getLocation($address, 'address', 1, $property->client_id);

        if (isset($geocode['location_type'])) // Geocode worked
        {
            $stateAbbr = Helper::convertStateToAbbr(strtoupper($property->state));
            $state = $stateAbbr ? $stateAbbr : trim($property->state);

            if ($property->member)
            {
                $this->first_name = $property->member->first_name;
                $this->last_name = $property->member->last_name;
            }
            $this->address = $property->address_line_1;
            $this->city = $property->city;
            $this->state = $state;
            $this->zip = $property->zip;
            $this->lat = round($geocode['geometry']['lat'], 5);
            $this->long = round($geocode['geometry']['lon'], 5);
            $this->client_id = $property->client_id;
            $this->property_pid = $property->pid;
            $this->geocoded = 1;
            $this->match_type = $geocode['location_type'];
            $this->match_score = $geocode['location_score'];
            $this->match_address = $geocode['address_formatted'];
            $this->wds_geocode_level = ($geocode['location_type'] === 'address' && $geocode['location_score'] > .75) ? 'address' : 'unmatched';
            $this->date_created = date('Y-m-d H:i:s');
            $this->score_type = $type;
            $this->processed = null;

            // Run the risk score logic if a good match (address & better than .75)
            if ($this->wds_geocode_level === 'address')
            {
                $riskModel = new RiskModel;
                $model = $riskModel->executeRiskModel($this->lat, $this->long, RiskModel::RISK_QUERY_TABULAR);
                $this->score_v = number_format(round($model['score_v'], 8), 8);
                $this->score_whp = number_format(round($model['score_whp'], 8), 8);
                $this->score_wds = number_format(round($model['score_wds'], 8), 8);
                $result['error'] = false;
                $result['message'] = 'Geocode matched to address.';
            }
            // Unmatched, but wds_lat/wds_long are populated
            else if (!empty($property->wds_lat) && !empty($property->wds_long))
            {
                $riskModel = new RiskModel;
                $model = $riskModel->executeRiskModel($property->wds_lat, $property->wds_long, RiskModel::RISK_QUERY_TABULAR);
                $this->score_v = number_format(round($model['score_v'], 8), 8);
                $this->score_whp = number_format(round($model['score_whp'], 8), 8);
                $this->score_wds = number_format(round($model['score_wds'], 8), 8);
                $result['error'] = false;
                $result['message'] = 'Geocode was unmatched with score: ' . $geocode['location_score'] . '.  Using wds_lat/wds_long.';
            }
            // Result is unmatched
            else
            {
                $result['error'] = true;
                $result['message'] = 'Geocode was unmatched with score: ' . $geocode['location_score'] . '.  No wds_lat/wds_long found.';
            }

            if (!$this->save())
            {
                Yii::log('ERRORS SAVING RiskScore ENTRY: ' . var_export($this->getErrors(), true), CLogger::LEVEL_ERROR, __METHOD__);
                $result['error'] = true;
                $result['message'] = 'Error (see log for more details): ' . var_export($this->getErrors(), true);
            }
        }
        else // Geocode didn't work
        {
            $stateAbbr = Helper::convertStateToAbbr(strtoupper($property->state));
            $state = $stateAbbr ? $stateAbbr : $property->state;

            $this->address = $property->address_line_1;
            $this->city = $property->city;
            $this->state = $state;
            $this->zip = $property->zip;
            $this->client_id = $property->client_id;
            $this->property_pid = $property->pid;
            $this->geocoded = 0;
            $this->match_type = 'unmatched';
            $this->match_score = '0';
            $this->match_address = 'could not match';
            $this->wds_geocode_level = 'unmatched';
            $this->date_created = date('Y-m-d H:i:s');
            $this->score_type = $type;
            $this->processed = null;
            $this->lat = null;
            $this->long = null;

            if (!empty($property->wds_lat) && !empty($property->wds_long))
            {
                $riskModel = new RiskModel;
                $model = $riskModel->executeRiskModel($property->wds_lat, $property->wds_long, RiskModel::RISK_QUERY_TABULAR);
                $this->score_v = number_format(round($model['score_v'], 8), 8);
                $this->score_whp = number_format(round($model['score_whp'], 8), 8);
                $this->score_wds = number_format(round($model['score_wds'], 8), 8);
                $result['error'] = false;
                $result['message'] =  'Geocode did not work.  Using wds_lat/wds_long.';
            }
            else
            {
                $result['error'] = true;
                $result['message'] = 'Geocode did not work.  No wds_lat/wds_long found.';
            }

            if (!$this->save())
            {
                Yii::log('ERRORS SAVING RiskScore ENTRY: ' . var_export($this->getErrors(), true), CLogger::LEVEL_ERROR, __METHOD__);
                $result['error'] = true;
                $result['message'] = 'Error (see log for more details): ' . var_export($this->getErrors(), true);
            }
        }

        return $result;
    }

    /**
     * List data of clients with wds_risk type
     * @return array
     */
    public function getRiskClients()
    {
        return CHtml::listData(Client::model()->findAllByAttributes(array('wds_risk' => 1)), 'id', 'name');
    }

    public function getRiskScoreTypes()
    {
        return CHtml::listData(RiskScoreType::model()->findAll(), 'id', 'type');
    }

    /**
     * Return an array of states risk scores exist in
     * @return array
     */
    public function getRiskScoreStates()
    {
        return CHtml::listData(RiskScore::model()->findAll(array(
            'select' => 'state',
            'distinct' => true,
            'order' => 'state ASC'
        )), 'state', 'state');
    }

    /**
     * Return an array of risk versions
     * @return array
     */
    public function getRiskScoreVersions()
    {
        return CHtml::listData(RiskVersion::model()->findAll(array(
            'select' => 'id, version',
            'order' => 'id ASC',
        )), 'id', 'version');
    }

    public function exportCSV($columnsToShow)
    {
        $dataProvider = $this->search();
        $dataProvider->setPagination(false);
        $countRecords = $dataProvider->getTotalItemCount();

        if ($countRecords > 1000)
        {
            echo CHtml::script('alert("You must export fewer than 1000 records.  You have ' . $countRecords . ' selected");');
            echo CHtml::script('window.location = "' . Yii::app()->createAbsoluteUrl('/riskScore/riskScores') . '"');
            exit(0);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=risk_scores.csv');

        $csvfile = fopen('php://output', 'w');

        fputcsv($csvfile, array_map(function($header) { return $this->getAttributeLabel($header); }, $columnsToShow));

        foreach ($dataProvider->getData() as $model)
        {
            $csvrow = array();

            foreach ($columnsToShow as $header)
            {
                switch ($header)
                {
                    case 'processed':
                    case 'geocoded':
                        $csvrow[] = Yii::app()->format->formatBoolean($model->$header); break;
                    case 'date_created':
                        $csvrow[] = date('Y-m-d', strtotime($model->$header)); break;
                    default:
                        $csvrow[] = $model->$header; break;
                }
            }

            fputcsv($csvfile, $csvrow);
        }

        fclose($csvfile);
        exit(0);
    }

    /**
     * Gets the number of entires in each standard dev category (lumping everything at 0 and below into 'no concern')
     * @param int $batchFileID
     * @param int $clientID
     * @return array
     */
    public static function getRiskBatchScoreAnalytics($batchFileID, $clientID)
    {
        $return = array();

        $sql = "select
	                count(r.id) as num,
	                r.std_dev_score
                from (
	                select
		                s.id,
		                case
			                when s.score_wds >= (m.mean + (m.std_dev * 3)) then 4 -- extreme concern (red)
			                when s.score_wds >= (m.mean + (m.std_dev * 2)) then 3 -- high concern (orange)
			                when s.score_wds >= (m.mean + (m.std_dev * 1)) then 2 -- moderate concern (yellow)
			                when s.score_wds > (m.mean) then 1 -- low concern (green)
			                else 0 -- no concern (green)
		                end as std_dev_score
	                from
		                risk_score s
	                inner join
		                geog_states g on g.abbr = s.state
	                inner join
		                risk_state_means m on m.state_id = g.id
	                where
		                s.batch_file_id = :batch_file_id
		                and s.client_id = :client_id
                        and ( (s.wds_geocode_level != 'unmatched' and s.geocoded = 1) or (s.geocoded is null and s.processed = 1) )
                ) r
                group by
	                r.std_dev_score
                order by
	                num desc;
                ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':batch_file_id', $batchFileID, PDO::PARAM_INT)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->queryAll();

        foreach($results as $row){
            switch ($row['std_dev_score']){
                case 4:
                    $row['std_dev_text'] = "Extreme Concern";
                    break;
                case 3:
                    $row['std_dev_text'] = "High Concern";
                    break;
                case 2:
                    $row['std_dev_text'] = "Moderate Concern";
                    break;
                case 1:
                    $row['std_dev_text'] = "Low Concern";
                    break;
                default:
                    $row['std_dev_text'] = "No Concern";
                    break;
            }

            $return[] = $row;

        }

        return $return;

    }
    /**
     * Gets the state distribution for the given batch
     * @param int $batchFileID
     * @param int $clientID
     * @return mixed
     */
    public static function getRiskBatchStateAnalytics($batchFileID, $clientID)
    {
        $sql = "select
	                count(id) as num,
	                state
                from
	                risk_score
                where
	                batch_file_id = :batch_file_id
                    and client_id = :client_id
                    and ( (wds_geocode_level != 'unmatched' and geocoded = 1) or (geocoded is null and processed = 1) )
                group by
	                state
                order by
	                num desc";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':batch_file_id', $batchFileID, PDO::PARAM_INT)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->queryAll();
    }

    /**
     * Gets text to describe what standard deviation range the score falls into.
     * @param string $state_mean
     * @param string $std_dev
     * @param string $score_wds
     * @param boolean $html ( optional )
     * @return string
     */
    public static function getRiskConcern($state_mean, $std_dev, $score_wds, $html = true)
    {
        if ($state_mean === 'n/a' || $std_dev === 'n/a' || $score_wds === 'n/a')
        {
            return ($html) ? '<span class="red">n/a</span>' : 'n/a';
        }

        if (is_numeric($state_mean) === false || is_numeric($std_dev) === false || is_numeric($state_mean) === false)
        {
            return ($html) ? '<span class="red">n/a</span>' : 'n/a';
        }

        $score_wds = round($score_wds, 6);

        $x_bar = round($state_mean, 6);
        $plus_1_std_dev = round($state_mean + $std_dev, 6);
        $plus_2_std_dev = round($state_mean + (2 * $std_dev), 6);
        $plus_3_std_dev = round($state_mean + (3 * $std_dev), 6);

        if ($score_wds > $plus_3_std_dev)
            $score_wds = $plus_3_std_dev;

        $riskText = '';

        if ($score_wds <= $x_bar)
            $riskText = ($html) ? '<span class="green">No concern</span>' : 'No concern';
        else if ($score_wds <= $plus_1_std_dev)
            $riskText = ($html) ? '<span class="green">Low concern</span>' : 'Low concern';
        else if ($score_wds <= $plus_2_std_dev)
            $riskText = ($html) ? '<span class="yellow">Moderate concern</span>' : 'Moderate concern';
        else if ($score_wds < $plus_3_std_dev)
            $riskText = ($html) ? '<span class="orange">High concern</span>' : 'High concern';
        else if ($score_wds == $plus_3_std_dev)
            $riskText = ($html) ? '<span class="red">Extreme concern</span>' : 'Extreme concern';

	    return $riskText;
    }

    /**
     * Displays more human readable text as to whe wds_score means in relation to the the standard dev and state mean
     * @param string $state_mean
     * @param string $std_dev
     * @param string $score_wds
     * @return string
     */
    public static function getStandardDevText($state_mean, $std_dev, $score_wds)
    {
        if ($state_mean === 'n/a' || $std_dev === 'n/a' || $score_wds === 'n/a')
        {
            return 'No mean or standard deviation data for this state';
        }

        if (is_numeric($state_mean) === false || is_numeric($std_dev) === false || is_numeric($score_wds) === false)
        {
            return 'No mean or standard deviation data for this state';
        }

        $score_wds = round($score_wds, 6);

        $minus_3_std_dev = round($state_mean - (3 * $std_dev), 6);
        $minus_2_std_dev = round($state_mean - (2 * $std_dev), 6);
        $minus_1_std_dev = round($state_mean - $std_dev, 6);
        $x_bar = round($state_mean, 6);
        $plus_1_std_dev = round($state_mean + $std_dev, 6);
        $plus_2_std_dev = round($state_mean + (2 * $std_dev), 6);
        $plus_3_std_dev = round($state_mean + (3 * $std_dev), 6);

        if ($score_wds < $minus_3_std_dev)
            $score_wds = $minus_3_std_dev;
        if ($score_wds > $plus_3_std_dev)
            $score_wds = $plus_3_std_dev;

        $std_dev_text = '';

        if ($score_wds == $minus_3_std_dev)
            $std_dev_text = "The property is below the THIRD STANDARD DEVIATION BELOW THE MEAN";
        else if ($score_wds <= $minus_2_std_dev)
            $std_dev_text = "The property is within the THIRD STANDARD DEVIATION BELOW THE MEAN";
        else if ($score_wds <= $minus_1_std_dev)
            $std_dev_text = "The property is within the SECOND STANDARD DEVIATION BELOW THE MEAN";
        else if ($score_wds <= $x_bar)
            $std_dev_text = "The property is within the FIRST STANDARD DEVIATION BELOW THE MEAN";
        else if ($score_wds <= $plus_1_std_dev)
            $std_dev_text = "The property is within the FIRST STANDARD DEVIATION ABOVE THE MEAN";
        else if ($score_wds <= $plus_2_std_dev)
            $std_dev_text = "The property is within the SECOND STANDARD DEVIATION ABOVE THE MEAN";
        else if ($score_wds < $plus_3_std_dev)
            $std_dev_text = "The property is within the THIRD STANDARD DEVIATION ABOVE THE MEAN";
        else if ($score_wds == $plus_3_std_dev)
            $std_dev_text = "The property is above the THIRD STANDARD DEVIATION ABOVE THE MEAN";

        return $std_dev_text;
    }
}
