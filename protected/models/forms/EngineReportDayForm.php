<?php

class EngineReportDayForm extends CFormModel
{
    // Prefilled attributes
    public $date;
    public $clientids;
    public $sources;
    public $onhold;

    // Entered Attributes
    public $alliance;
    public $fires;
    public $assignment;

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
            array('date, clientids, sources', 'required'),
            array('alliance, assignment, fires, onhold', 'safe')
		);
	}

	/**
     * @return array
     */
	public function attributeLabels()
	{
		return array(
            'date' => 'Date',
            'clientids' => 'Clients',
            'sources' => 'Sources',
            'onhold' => 'On Hold',
            'alliance' => 'Alliance Partner',
            'fires' => 'Fires',
            'assignment' => 'Assignment'
		);
	}

    /**
     * Return array of availible dates
     * @param EngineReportForm $engineReportForm
     * @return string[]
     */
    public function getDate($engineReportForm)
    {
        $period = new DatePeriod(date_create($engineReportForm->startdate), DateInterval::createFromDateString('1 day'), date_create($engineReportForm->enddate));
        $returnArray = array();
        foreach($period as $day)
            $returnArray[$day->format('Y-m-d')] = $day->format('M d, Y');
        return $returnArray;
    }

    /**
     * Return array of availible alliance partners
     * @return string[]
     */
    public function getAlliance()
    {
        if ($this->sources)
        {
            if (in_array((string)EngEngines::ENGINE_SOURCE_ALLIANCE,  json_decode($this->sources)))
            {
                return CHtml::listData(Alliance::model()->findAll(array(
                    'select' => array('id', 'name')
                )), 'id', 'name');
            }
            return array();
        }
        return array();
    }

    /**
     * Return availible engine assignments
     * @return string[]
     */
    public function getAssignments()
    {
        $returnArray = array(
            EngScheduling::ENGINE_ASSIGNMENT_RESPONSE => EngScheduling::ENGINE_ASSIGNMENT_RESPONSE,
            EngScheduling::ENGINE_ASSIGNMENT_DEDICATED => EngScheduling::ENGINE_ASSIGNMENT_DEDICATED
        );

        if ($this->onhold)
            $returnArray[EngScheduling::ENGINE_ASSIGNMENT_ONHOLD] = EngScheduling::ENGINE_ASSIGNMENT_ONHOLD;

        return $returnArray;
    }

    /**
     * Return an array of availible fires on a given date and array of clientIDs
     * @param string $date
     * @param array $clientids
     * @return string[]
     */
    static public function getFires($date, $clientids)
    {
        $sql = "
            DECLARE @day datetime = CONVERT(DATE, :date);

            SELECT f.Fire_ID, f.Name
            FROM res_fire_name f
	            INNER JOIN res_notice n ON n.fire_id = f.Fire_ID
	            INNER JOIN res_status s ON n.wds_status = s.id
            WHERE CONVERT(DATE, n.date_created) = @day
                AND s.status_type = 'Dispatched'
                AND n.client_id IN (" . implode(',', $clientids) . ")
            GROUP BY f.Fire_ID, f.Name
            ORDER BY f.Name
        ";

        return CHtml::listData(ResFireName::model()->findAllBySql($sql, array(
            ':date' => $date
        )), 'Fire_ID', 'Name');
    }

    /**
     * Getting models for details day stats given assigned model attributes
     * @return EngScheduling[]
     */
    public function getEngineDayResults()
    {
        $criteria = new CDbCriteria;

        $criteria->with = array('engine', 'engineClient');

        // The SQL convert statements set the dates to midnight of that day, so that hours don't affect the results of the query
        $criteria->addCondition("'$this->date' >= CONVERT(DATE, t.start_date) AND '$this->date' <= CONVERT(DATE, t.end_date)");
        $criteria->addInCondition('engineClient.client_id', json_decode($this->clientids));
        $criteria->addInCondition('engine.engine_source', json_decode($this->sources));
       
        if ($this->alliance)
            $criteria->addInCondition('engine.alliance_id', $this->alliance);
        if ($this->fires)
            $criteria->addInCondition('t.fire_id', $this->fires);

        if ($this->assignment)
            $criteria->addInCondition('t.assignment', $this->assignment);
        else
            $criteria->addInCondition('t.assignment', array_values($this->getAssignments()));

        //var_dump($this->attributes);
        //var_dump(array_values($this->getAssignments())); die();

        $criteria->order = 't.start_date ASC';

        return EngScheduling::model()->findAll($criteria);;
    }
}
