<?php

/**
 * This is the model class for table "res_dedicated".
 *
 * The followings are the available columns in table 'res_dedicated':
 * @property integer $dedicated_id
 * @property integer $client_id
 * @property integer $hours_id
 * @property string $date
 * @property string $AZ
 * @property string $CA
 * @property string $CO
 * @property string $FL
 * @property string $GA
 * @property string $ID
 * @property string $MT
 * @property string $NC
 * @property string $ND
 * @property string $NM
 * @property string $NV
 * @property string $OK
 * @property string $OR
 * @property string $SC
 * @property string $SD
 * @property string $TN
 * @property string $TX
 * @property string $UT
 * @property string $WA
 * @property string $WY
 * @property string $hours_used
 * @property string $date_updated
 */
class ResDedicated extends CActiveRecord
{
    public $client_name;
    public $dedicated_hours;
    public $dedicated_hours_date;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_dedicated';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('client_id, hours_id, date', 'required'),
            array('client_id, hours_id', 'numerical', 'integerOnly'=>true),
			array('AZ, CA, CO, FL, GA, ID, MT, NC, ND, NM, NV, OK, OR, SC, SD, TN, TX, UT, WA, WY, hours_used', 'length', 'max'=>12),
			array('date, date_updated', 'safe'),
			array('AZ, CA, CO, FL, GA, ID, MT, NC, ND, NM, NV, OK, OR, SC, SD, TN, TX, UT, WA, WY', 'match', 'pattern' => '/^\d{0,3}(\.\d{0,5})?$/',  'message' => '{attribute} must entered in the Correct Decimal format.<br />Ex: 107.12421'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('dedicated_id, client_id, hours_id, AZ, CA, CO, FL, GA, ID, MT, NC, ND, NM, NV, OK, OR, SC, SD, TN, TX, UT, WA, WY, client_name, dedicated_hours_date', 'safe', 'on'=>'search'),
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
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'hours' => array(self::BELONGS_TO, 'ResDedicatedHours', 'hours_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'dedicated_id' => 'Dedicated',
			'client_id' => 'Client',
			'date' => 'Date',
			'AZ' => 'AZ',
			'CA' => 'CA',
			'CO' => 'CO',
            'FL' => 'FL',
            'GA' => 'GA',
			'ID' => 'ID',
			'MT' => 'MT',
            'NC' => 'NC',
			'ND' => 'ND',
			'NM' => 'NM',
			'NV' => 'NV',
            'OK' => 'OK',
			'OR' => 'OR',
            'SC' => 'SC',
			'SD' => 'SD',
            'TN' => 'TN',
			'TX' => 'TX',
			'UT' => 'UT',
			'WA' => 'WA',
			'WY' => 'WY',
            'hours_used' => 'Hours Used',
            'date_updated' => 'Date Updated',
            'client_name' => 'Client Name',
            'dedicated_hours_date' => 'Dedicated Year'
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
		$criteria = new CDbCriteria;

        $criteria->with = array(
            'client' => array('select' => array('id','name')),
            'hours' => array('select' => array('id','dedicated_start_date'))
        );

		$criteria->compare('AZ',$this->AZ,true);
		$criteria->compare('CA',$this->CA,true);
		$criteria->compare('CO',$this->CO,true);
        $criteria->compare('FL',$this->FL,true);
        $criteria->compare('GA',$this->GA,true);
		$criteria->compare('ID',$this->ID,true);
		$criteria->compare('MT',$this->MT,true);
		$criteria->compare('ND',$this->ND,true);
        $criteria->compare('NC',$this->NC,true);
		$criteria->compare('NM',$this->NM,true);
		$criteria->compare('NV',$this->NV,true);
        $criteria->compare('OK',$this->OK,true);
		$criteria->compare('OR',$this->OR,true);
        $criteria->compare('SC',$this->SC,true);
		$criteria->compare('SD',$this->SD,true);
        $criteria->compare('TN',$this->TN,true);
		$criteria->compare('TX',$this->TX,true);
		$criteria->compare('UT',$this->UT,true);
		$criteria->compare('WA',$this->WA,true);
		$criteria->compare('WY',$this->WY,true);
        $criteria->compare('client.name',$this->client_name, true);

        if ($this->dedicated_hours_date)
        {
            $criteria->addCondition('hours.dedicated_start_date >= :dedicated_start_date');
            $criteria->addCondition('hours.dedicated_start_date < :dedicated_start_date_plus_day');
            $criteria->params[':dedicated_start_date'] = date('Y-m-d',strtotime(trim($this->dedicated_hours_date)));
            $criteria->params[':dedicated_start_date_plus_day'] = date('Y-m-d', strtotime(trim($this->dedicated_hours_date) . ' + 1 day'));
        }

		return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('dedicated_id' => CSort::SORT_DESC),
                'attributes' => array(
                    'client_name' => array(
                        'asc' => 'client.name',
                        'desc' => 'client.name DESC'
                    ),
                    'dedicated_hours_date' => array(
                        'asc' => 'hours.dedicated_start_date',
                        'desc' => 'hours.dedicated_start_date DESC'
                    ),
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
	 * @return ResDedicated the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function afterFind()
    {
        if ($this->client)
            $this->client_name = $this->client->name;

        if ($this->hours)
        {
            $this->dedicated_hours = $this->hours->dedicated_hours;
            $this->dedicated_hours_date = $this->hours->dedicated_start_date;
        }

        return parent::afterFind();
    }

    protected function beforeSave()
    {
        $this->date_updated = date('Y-m-d');

        // Tallying up hours for each state to store in the 'hours_used' column

        $hours_used = 0;

        foreach (self::getDedicatedStates() as $state)
        {
            if (!empty($this->$state))
                $hours_used += (float)$this->$state;
        }

        $this->hours_used = $hours_used;

        return parent::beforeSave();
    }

	/**
     * Returns an associate array of months left to fill in the designated dedicated service year.
     * Ex:
     * {
     *     '2015-10-01': 'October',
     *     '2016-01-01': 'January',
     *     '2015-12-01': 'December',
     *     '2015-08-01': 'August',
     *     '2016-04-01': 'April'
     * }
     * @param string $dedicated_start_date
     * @param int $client_id
     * @return array $date_array
     */
    public function dedicatedMonthsToFill($dedicated_start_date, $client_id)
    {
        $start_date = new DateTime($dedicated_start_date);
        $end_date = clone $start_date;
        $end_date = $end_date->modify('+1 year');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date);

        // Creating an array of all months in dedicated year.

        $date_array = array();

        foreach ($period as $dt)
            $date_array[$dt->format('Y-m-d')] = $dt->format('F - Y');

        $criteria = new CDbCriteria();
        $criteria->addCondition("client_id = $client_id");
        $criteria->addCondition("date between '$dedicated_start_date' AND DATEADD(MONTH, 11, '$dedicated_start_date')");

        // Removing those months in array that have already been filled out

        foreach (ResDedicated::model()->findAll($criteria) as $dedicated)
        {
            $existing_date = date('Y-m-d', strtotime($dedicated->date));
            if (array_key_exists($existing_date, $date_array))
                unset($date_array[$existing_date]);
        }

        return $date_array;
    }

    public static function getDedicatedStates()
    {
        return array('AZ','CA','CO','FL','GA','ID','MT','NC','ND','NM','NV','OK','OR','SC','SD','TN','TX','UT','WA','WY');
    }

    public static function reportingSiteCountDaysMTD($clientID)
    {
        return Yii::app()->db->createCommand("
        SELECT CAST(hours_used AS float(24)) / 8.0 AS days_used
        FROM res_dedicated
        WHERE client_id = :clientID AND [date] >= FORMAT(GETDATE(), 'yyyyMM01')")
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->queryScalar();
    }

    public static function reportingSiteCountDaysYTD($clientID, $contractToDate = true)
    {
        $retval = 0;
        $dedicatedDateSearch = null;

        if ($contractToDate === true)
        {
            $clientDedicatedHoursModel = ResDedicatedHours::model()->find(array(
                'select' => 'dedicated_start_date',
                'condition' => 'client_id = :client_id',
                'order' => 'id DESC',
                'limit' => 1,
                'params' => array(':client_id' => $clientID)
            ));

            if (isset($clientDedicatedHoursModel->dedicated_start_date))
            {
                $dedicatedDateSearch = $clientDedicatedHoursModel->dedicated_start_date;
            }
            else
            {
                return $retval;
            }
        }
        else
        {
            $dedicatedDateSearch = date('Y-01-01');
        }

        $sql = '
            SELECT SUM(CAST(hours_used AS float(24))) / 8.0
            AS days_used FROM res_dedicated
            WHERE client_id = :clientID AND [date] >= :dedicatedStartDate
        ';

        $retval = Yii::app()->db->createCommand($sql)
            ->bindValue(':clientID', $clientID)
            ->bindValue(':dedicatedStartDate', $dedicatedDateSearch)
            ->queryScalar();

        return $retval;
    }

    public static function getDedicatedHoursAnalytics($clientID, $startDate, $enddate)
    {
        $startdate = date('Y-m-01', strtotime($startDate));
        $enddate = date('Y-m-d', strtotime($enddate));

        $criteria = new CDbCriteria;
        $criteria->addCondition('date >= :startdate');
        $criteria->addCondition('date < :enddate');
        $criteria->addCondition('client_id = :clientid');
        $criteria->params = array(':startdate' => $startdate, ':enddate' => $enddate, ':clientid' => $clientID);
        $criteria->order = 'date ASC';

        $dedicated_models = ResDedicated::model()->findAll($criteria);

        $returnData = array();
        $current_dedicated_year = null;
        $hours_total_used = null;

        foreach ($dedicated_models as $dedicated_model)
        {
            if ($dedicated_model->dedicated_hours_date !== $current_dedicated_year)
            {
                // Checking if this is the start of hours analytics and if not the first of the dedicated year.
                // This is needed so the 'hours used' starts in the correct place. (not 0 hours used in the middle of the dedicated year)
                if (is_null($hours_total_used) && $dedicated_model->date !== $dedicated_model->dedicated_hours_date)
                {
                    $hours_total_used = (float)Yii::app()->db->createCommand()
                        ->select('SUM(CAST(hours_used AS NUMERIC(8,2)))')
                        ->from('res_dedicated')
                        ->where('date >= :dedicated_hours_date AND date < :dedicated_model_date AND client_id = :client_id')
                        ->queryScalar(array(
                            ':dedicated_hours_date' => $dedicated_model->dedicated_hours_date,
                            ':dedicated_model_date' => $dedicated_model->date,
                            ':client_id' => $clientID
                        ));
                }
                else
                {
                    $hours_total_used = 0;
                }

                $current_dedicated_year = $dedicated_model->dedicated_hours_date;
            }

            $hours_total_used += $dedicated_model->hours_used;

            $returnData[] = array(
                'hours_used' => (float)$dedicated_model->hours_used,
                'hours_remaining' => (float)$dedicated_model->dedicated_hours - $hours_total_used,
                'dedicated_year_hours' => (float)$dedicated_model->dedicated_hours,
                'dedicated_year' => date('Y-m-d', strtotime($dedicated_model->dedicated_hours_date)),
                'dedicated_month' => date('Y-m-d', strtotime($dedicated_model->date))
            );
        }

        return $returnData;
    }

    public static function getDedicatedHoursMonthBreakdownAnalytics($clientID, $startDate, $endDate)
    {
        $startdate = date('Y-m-01', strtotime($startDate));
        $enddate = date('Y-m-d', strtotime($endDate));

        $criteria = new CDbCriteria;
        $criteria->addCondition('date >= :startdate');
        $criteria->addCondition('date <= :enddate');
        $criteria->addCondition('client_id = :clientid');
        $criteria->params = array(':startdate' => $startdate, ':enddate' => $enddate, ':clientid' => $clientID);
        $criteria->order = 'date ASC';

        $dedicated_models = ResDedicated::model()->findAll($criteria);

        $returnData = array();

        foreach ($dedicated_models as $dedicated_model)
        {
            $returnData[] = array(
                'dedicated_date' => date('Y-m-d', strtotime($dedicated_model->date)),
                'dedicated_states' => array(
                    'AZ' => (float)$dedicated_model->AZ,
                    'CA' => (float)$dedicated_model->CA,
                    'CO' => (float)$dedicated_model->CO,
                    'FL' => (float)$dedicated_model->FL,
                    'GA' => (float)$dedicated_model->GA,
                    'ID' => (float)$dedicated_model->ID,
                    'MT' => (float)$dedicated_model->MT,
                    'NC' => (float)$dedicated_model->NC,
                    'ND' => (float)$dedicated_model->ND,
                    'NM' => (float)$dedicated_model->NM,
                    'NV' => (float)$dedicated_model->NV,
                    'OK' => (float)$dedicated_model->OK,
                    'OR' => (float)$dedicated_model->OR,
                    'SC' => (float)$dedicated_model->SC,
                    'SD' => (float)$dedicated_model->SD,
                    'TN' => (float)$dedicated_model->TN,
                    'TX' => (float)$dedicated_model->TX,
                    'UT' => (float)$dedicated_model->UT,
                    'WA' => (float)$dedicated_model->WA,
                    'WY' => (float)$dedicated_model->WY
                )
            );
        }

        return $returnData;
    }

    public static function getDedicatedHoursMonthBreakdownAllAnalytics($startDate, $endDate)
    {
        $startdate = date('Y-m-01', strtotime($startDate));
        $enddate = date('Y-m-d', strtotime($endDate));

        $criteria = new CDbCriteria;
        $criteria->select = array(
            'date',
            'SUM(CAST([AZ] AS float(24))) AS [AZ]',
            'SUM(CAST([CA] AS float(24))) AS [CA]',
            'SUM(CAST([CO] AS float(24))) AS [CO]',
            'SUM(CAST([FL] AS float(24))) AS [FL]',
            'SUM(CAST([GA] AS float(24))) AS [GA]',
            'SUM(CAST([ID] AS float(24))) AS [ID]',
            'SUM(CAST([MT] AS float(24))) AS [MT]',
            'SUM(CAST([NC] AS float(24))) AS [NC]',
            'SUM(CAST([ND] AS float(24))) AS [ND]',
            'SUM(CAST([NM] AS float(24))) AS [NM]',
            'SUM(CAST([NV] AS float(24))) AS [NV]',
            'SUM(CAST([OK] AS float(24))) AS [OK]',
            'SUM(CAST([OR] AS float(24))) AS [OR]',
            'SUM(CAST([SC] AS float(24))) AS [SC]',
            'SUM(CAST([SD] AS float(24))) AS [SD]',
            'SUM(CAST([TN] AS float(24))) AS [TN]',
            'SUM(CAST([TX] AS float(24))) AS [TX]',
            'SUM(CAST([UT] AS float(24))) AS [UT]',
            'SUM(CAST([WA] AS float(24))) AS [WA]',
            'SUM(CAST([WY] AS float(24))) AS [WY]'
        );
        $criteria->addCondition('date >= :startdate');
        $criteria->addCondition('date <= :enddate');
        $criteria->params = array(':startdate' => $startdate, ':enddate' => $enddate);
        $criteria->group = 'date';
        $criteria->order = 'date ASC';

        $dedicated_models = ResDedicated::model()->findAll($criteria);

        $returnData = array();

        foreach ($dedicated_models as $dedicated_model)
        {
            $returnData[] = array(
                'dedicated_date' => date('Y-m-d', strtotime($dedicated_model->date)),
                'dedicated_states' => array(
                    'AZ' => (float)$dedicated_model->AZ,
                    'CA' => (float)$dedicated_model->CA,
                    'CO' => (float)$dedicated_model->CO,
                    'FL' => (float)$dedicated_model->FL,
                    'GA' => (float)$dedicated_model->GA,
                    'ID' => (float)$dedicated_model->ID,
                    'MT' => (float)$dedicated_model->MT,
                    'NC' => (float)$dedicated_model->NC,
                    'ND' => (float)$dedicated_model->ND,
                    'NM' => (float)$dedicated_model->NM,
                    'NV' => (float)$dedicated_model->NV,
                    'OK' => (float)$dedicated_model->OK,
                    'OR' => (float)$dedicated_model->OR,
                    'SC' => (float)$dedicated_model->SC,
                    'SD' => (float)$dedicated_model->SD,
                    'TN' => (float)$dedicated_model->TN,
                    'TX' => (float)$dedicated_model->TX,
                    'UT' => (float)$dedicated_model->UT,
                    'WA' => (float)$dedicated_model->WA,
                    'WY' => (float)$dedicated_model->WY
                )
            );
        }

        return $returnData;
    }
}
