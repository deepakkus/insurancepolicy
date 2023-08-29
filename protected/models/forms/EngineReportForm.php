<?php

class EngineReportForm extends CFormModel
{
    public $clientids;
    public $sources;
    public $startdate;
    public $enddate;
    public $onhold;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            array('onhold', 'numerical', 'integerOnly' => true),
            array('sources, clientids', 'required', 'message' => '{attribute} must be selected.'),
            array('startdate, enddate', 'required'),
            array('startdate', 'validateDtaes') 
		);
	}
 
	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
            'clientids' => 'Clients',
            'sources' => 'Engine Sources',
            'startdate' => 'Start Date',
            'enddate' => 'End Date',
            'onhold' => 'Include On Hold'
		);
	}

    /**
     * Return array of wdsfire clients for options menu
     * @return string[]
     */
    public function getActiveWdsfireClients()
    {
        return CHtml::listData(Client::model()->findAll(array(
            'select' => array('id', 'name'),
            'condition' => 'wds_fire = 1 AND active = 1',
            'order' => 'name ASC'
        )), 'id', 'name');
    }
    /**
     * Validation method that checks Start date range
     */
     public function validateDtaes()
     {
         if($this->startdate < 1900-01-01)
         { 
            $this->addError('startdate',  'Invalid Date Range');
         }
     }

    /**
     * Create array of engine counts by assignment for day for current model attributes.
     * Note: Response assignment always takes priority over dedicated or on hold assignments.
     * @return array
     */
    public function getTallies()
    {
        $tallyEngineResponse = array();
        $tallyEngineDedicated = array();
        $tallyEngineOnHold = array();
        $tallyEngineTotal = array();
        $tallyPoliciesTriggered = array();
        $tallyDispatchedFires = array();

        $startdate = new DateTime($this->startdate);
        $enddate = new DateTime($this->enddate);

        $sqlEngines = "
            DECLARE @startdate datetime = :startdate;
            DECLARE @enddate datetime = :enddate;

            SELECT
                s.id,
                s.engine_id,
                s.assignment,
                CONVERT(DATE, s.start_date) start_date,
                CONVERT(DATE, s.end_date) end_date
            FROM eng_scheduling s
	            INNER JOIN eng_engines e ON e.id = s.engine_id
	            INNER JOIN eng_scheduling_client c ON s.id = c.engine_scheduling_id
            WHERE e.engine_source IN (" . implode(',', $this->sources)  .")
	            AND ((s.start_date BETWEEN @startdate AND @enddate) OR (s.end_date BETWEEN @startdate AND @enddate) OR
	                 (@startdate BETWEEN s.start_date AND s.end_date) OR (@enddate BETWEEN  s.start_date AND s.end_date))
	            AND s.assignment IN ('Response', 'Dedicated Service'" . ($this->onhold ? ",'On Hold'" : '')  . ")
	            AND c.client_id IN (" . implode(',', $this->clientids)  .")
            ORDER BY s.assignment DESC
        ";

        $sqlDispatchedFiresPolicies = "
            DECLARE @startdate datetime = :startdate;
            DECLARE @enddate datetime = :enddate;

            SELECT
                COUNT(t.property_pid) triggered_count,
                n.notice_id,
                CONVERT(DATE, n.date_created) date_created,
                f.Fire_ID fire_id,
                f.Name fire_name
            FROM res_notice n
                INNER JOIN res_status s ON n.wds_status = s.id
                INNER JOIN res_triggered t ON n.notice_id = t.notice_id
                INNER JOIN res_fire_name f ON n.fire_id = f.Fire_ID
            WHERE n.date_created > @startdate
                AND n.date_created < @enddate
                AND s.status_type = 'Dispatched'
                AND n.client_id IN (" . implode(',', $this->clientids)  .")
            GROUP BY n.notice_id, n.date_created, f.Fire_ID, f.Name
            ORDER BY n.date_created ASC
        ";

        $startdateFormatted = $startdate->format('Y-m-d');
        $enddateFormatted = $enddate->format('Y-m-d');

        $resultEngines = Yii::app()->db->createCommand($sqlEngines)
            ->bindParam(':startdate', $startdateFormatted, PDO::PARAM_STR)
            ->bindParam(':enddate', $enddateFormatted, PDO::PARAM_STR)
            ->queryAll();

        $resultDispatchedFiresPolicies = Yii::app()->db->createCommand($sqlDispatchedFiresPolicies)
            ->bindParam(':startdate', $startdateFormatted, PDO::PARAM_STR)
            ->bindParam(':enddate', $enddateFormatted, PDO::PARAM_STR)
            ->queryAll();

        // Make Datetime objects in results for comparison ... set to midnight for datetime comparisons
        array_walk($resultEngines, function(&$value, $key) {
            $value['start_date'] = new DateTime($value['start_date']);
            $value['end_date'] = new DateTime($value['end_date']);
        });
        array_walk($resultDispatchedFiresPolicies, function(&$value, $key) {
            $value['date_created'] = new DateTime($value['date_created']);
        });

        // Split results by assignment type
        $resultsResponse =  array_filter($resultEngines, function($result) { return $result['assignment'] === 'Response'; });
        $resultsDedicated = array_filter($resultEngines, function($result) { return $result['assignment'] === 'Dedicated Service'; });
        if ($this->onhold)
            $resultsOnHold = array_filter($resultEngines, function($result) { return $result['assignment'] === 'On Hold'; });

        $period = new DatePeriod($startdate, DateInterval::createFromDateString('1 day'), $enddate);

        // Iterate through each day in range
        foreach($period as $day)
        {
            $timestamp = $day->getTimestamp();

            $tallyEngineResponse[$timestamp] = array('count' => 0, 'engineids' => array());
            $tallyEngineDedicated[$timestamp] = array('count' => 0, 'engineids' => array());
            $tallyEngineOnHold[$timestamp] = array('count' => 0, 'engineids' => array());
            $tallyEngineTotal[$timestamp] = array('count' => 0, 'engineids' => array());
            $tallyPoliciesTriggered[$timestamp] = array('count' => 0, 'noticeIDs' => array());
            $tallyDispatchedFires[$timestamp] = array('count' => 0, 'fireIDs' => array());

            // Iterate through response results
            foreach ($resultsResponse as $result)
            {
                if ($day >= $result['start_date'] && $day <= $result['end_date'])
                {
                    // Check if engine already been assigned this day? (multiple clients assigned to one engine)
                    if (!in_array($result['engine_id'], $tallyEngineResponse[$timestamp]['engineids']))
                    {
                        $tallyEngineResponse[$timestamp]['count']++;
                        $tallyEngineResponse[$timestamp]['engineids'][] = $result['engine_id'];
                        $tallyEngineTotal[$timestamp]['count']++;
                        $tallyEngineTotal[$timestamp]['engineids'][] = $result['engine_id'];
                    }
                }
            }

            // Iterate through dedicated results
            foreach ($resultsDedicated as $result)
            {
                if ($day >= $result['start_date'] && $day <= $result['end_date'])
                {
                    // Make sure engine is not in the corresponding response tallies (response takes priority)
                    // Then check if engine already been assigned this day? (multiple clients assigned to one engine)
                    if (!in_array($result['engine_id'], $tallyEngineResponse[$timestamp]['engineids']) &&
                        !in_array($result['engine_id'], $tallyEngineDedicated[$timestamp]['engineids']))
                    {
                        $tallyEngineDedicated[$timestamp]['count']++;
                        $tallyEngineDedicated[$timestamp]['engineids'][] = $result['engine_id'];
                        $tallyEngineTotal[$timestamp]['count']++;
                        $tallyEngineTotal[$timestamp]['engineids'][] = $result['engine_id'];
                    }
                }
            }

            if ($this->onhold)
            {
                foreach ($resultsOnHold as $result)
                {
                    if ($day >= $result['start_date'] && $day <= $result['end_date'])
                    {
                        // Make sure engine is not in the corresponding response tallies (response takes priority)
                        // Then check if engine already been assigned this day? (multiple clients assigned to one engine)
                        if (!in_array($result['engine_id'], $tallyEngineResponse[$timestamp]['engineids']) &&
                            !in_array($result['engine_id'], $tallyEngineOnHold[$timestamp]['engineids']))
                        {
                            $tallyEngineOnHold[$timestamp]['count']++;
                            $tallyEngineOnHold[$timestamp]['engineids'][] = $result['engine_id'];
                            $tallyEngineTotal[$timestamp]['count']++;
                            $tallyEngineTotal[$timestamp]['engineids'][] = $result['engine_id'];
                        }
                    }
                }
            }

            // Check if notices for triggered policies falls between dates
            foreach ($resultDispatchedFiresPolicies as $result)
            {
                if ($result['date_created'] == $day)
                {
                    $tallyPoliciesTriggered[$timestamp]['count'] += $result['triggered_count'];
                    $tallyPoliciesTriggered[$timestamp]['noticeIDs'][] = $result['notice_id'];

                    if (!in_array($result['fire_id'], $tallyDispatchedFires[$timestamp]['fireIDs']))
                    {
                        $tallyDispatchedFires[$timestamp]['fireIDs'][] = $result['fire_id'];
                        $tallyDispatchedFires[$timestamp]['count']++;
                    }
                }
            }
        }

        return array(
            $tallyEngineResponse,
            $tallyEngineDedicated,
            $tallyEngineOnHold,
            $tallyEngineTotal,
            $tallyPoliciesTriggered,
            $tallyDispatchedFires
        );
    }
}
