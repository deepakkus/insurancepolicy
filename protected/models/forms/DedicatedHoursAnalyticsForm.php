<?php

class DedicatedHoursAnalyticsForm extends CFormModel
{
    public $startDate;
    public $endDate;
    public $clientID;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('startDate, endDate, clientID', 'required')
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array(
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'clientID' => 'Client'
        );
    }

    /**
     * Get data for hours spent on engine assignments by shift ticket activites.
     * @param boolean $includeClients should results be joined on clients by assignment
     * @return array
     * [{
     *     "hours": 330.25
     *     "assignment": "Dedicated Service",
     *     "state": "CA"
     * },
     * {
     *     "hours": 250.0
     *     "assignment": "Response",
     *     "state": "CO"
     * }]
     */
    public function getHoursByAssignment($includeClients = false)
    {
        $sqlNoClients = "
            SELECT
                st.id,
                CONVERT(FLOAT, DATEDIFF(MINUTE, [start_time], [end_time])) / 60.0 [hours],
                es.assignment,
                es.state,
                completed.completed
            FROM eng_shift_ticket st
            INNER JOIN eng_scheduling es ON st.eng_scheduling_id = es.id
            INNER JOIN eng_shift_ticket_activity sta ON st.id = sta.eng_shift_ticket_id
            LEFT OUTER JOIN (
                SELECT id, CASE
                    WHEN 0 IN (
                        SELECT s.completed FROM eng_shift_ticket_status s
                        INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                        WHERE s.shift_ticket_id = st.id AND (t.disabled != 1 OR t.disabled IS NULL)
                    ) THEN 0 ELSE 1 END completed
                FROM eng_shift_ticket st
            ) completed ON st.id = completed.id
            WHERE st.date >= :start_date
                AND st.date <= :end_date
            ORDER BY es.state ASC, st.date ASC
        ";

        $sqlWithClients = "
            SELECT
                st.id,
                CONVERT(FLOAT, DATEDIFF(MINUTE, [start_time], [end_time])) / 60.0 [hours],
                es.assignment,
                es.state,
                completed.completed,
                esc.client_id
            FROM eng_shift_ticket st
            INNER JOIN eng_scheduling es ON st.eng_scheduling_id = es.id
            INNER JOIN eng_scheduling_client esc ON es.id = esc.engine_scheduling_id
            INNER JOIN eng_shift_ticket_activity sta ON st.id = sta.eng_shift_ticket_id
            LEFT OUTER JOIN (
                SELECT id, CASE
                    WHEN 0 IN (
                        SELECT s.completed FROM eng_shift_ticket_status s
                        INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                        WHERE s.shift_ticket_id = st.id AND (t.disabled != 1 OR t.disabled IS NULL)
                    ) THEN 0 ELSE 1 END completed
                FROM eng_shift_ticket st
            ) completed ON st.id = completed.id
            WHERE st.date >= :start_date
                AND st.date <= :end_date
            ORDER BY esc.client_id ASC, st.date ASC
        ";

        $sql = $includeClients === true ? $sqlWithClients : $sqlNoClients;

        $command = Yii::app()->db->createCommand($sql);

        $command->bindValue(':start_date', $this->startDate, PDO::PARAM_STR);
        $command->bindValue(':end_date', $this->endDate, PDO::PARAM_STR);

        $results = $command->queryAll();

        foreach ($results as $index => $result)
        {
            $results[$index]['hours'] = floatval($result['hours']);
        }

        return $results;
    }

    /**
     * Get data for hours spent on engine assignments for a specific client
     * @param string $assignment
     * @return array
     * [{
     *     "date": PHP DateTime Object
     *     "hours": 1.5
     *     "state": "CA"
     * },
     * {
     *     "date": PHP DateTime Object
     *     "hours": 2.5
     *     "state": "CO"
     * }]
     */
    public function getHoursByAssignmentForClient($assignment)
    {
        $sql = "

            SELECT
                FORMAT([date],'yyyy-MM') [date],
                SUM(CONVERT(FLOAT, DATEDIFF(MINUTE, [start_time], [end_time]))) / 60.0 [hours],
                es.[state]
            FROM eng_shift_ticket st
                INNER JOIN eng_scheduling es ON st.eng_scheduling_id = es.id
                INNER JOIN eng_scheduling_client esc ON es.id = esc.engine_scheduling_id
                INNER JOIN eng_shift_ticket_activity sta ON st.id = sta.eng_shift_ticket_id
                LEFT OUTER JOIN (
                    SELECT id, CASE
                        WHEN 0 IN (
                            SELECT s.completed FROM eng_shift_ticket_status s
                            INNER JOIN eng_shift_ticket_status_type t ON t.id = s.status_type_id
                            WHERE s.shift_ticket_id = st.id AND (t.disabled != 1 OR t.disabled IS NULL)
                        ) THEN 0 ELSE 1 END completed
                    FROM eng_shift_ticket st
                ) completed ON st.id = completed.id
            WHERE st.[date] >= :start_date
                AND st.[date] <= :end_date
                AND es.assignment = :assignment
                AND esc.client_id = :client_id
                --AND completed.completed = 1
            GROUP BY FORMAT([date],'yyyy-MM'), [state]
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindValue(':start_date', $this->startDate, PDO::PARAM_STR)
            ->bindValue(':end_date', $this->endDate, PDO::PARAM_STR)
            ->bindValue(':assignment', $assignment, PDO::PARAM_STR)
            ->bindValue(':client_id', $this->clientID, PDO::PARAM_INT)
            ->queryAll();

        foreach ($results as $index => $result)
        {
            $results[$index]['date'] = new DateTime($result['date']);
            $results[$index]['hours'] = floatval($result['hours']);
        }

        return $results;
    }

    /**
     * Get dedicated service hour pools for a client for appropriate start and end dates
     * @param integer $clientID
     * @return array
     * [{
     *     "dedicated_hours": 3600
     *     "dedicated_hours_remaining": 3600
     *     "dedicated_start_date": PHP DateTime Object
     *     "dedicated_end_date": PHP DateTime Object
     * },
     * {
     *     "dedicated_hours": 4800
     *     "dedicated_hours_remaining": 4800
     *     "dedicated_start_date": PHP DateTime Object
     *     "dedicated_end_date": PHP DateTime Object
     * }]
     */
    public function getDedicatedHourPoolsByClientAndDate()
    {
        $sql = "
            DECLARE @startDate DATETIME = :start_date
            DECLARE @endDate DATETIME = :end_date

            SELECT
                [dedicated_hours],
                [dedicated_start_date],
                DATEADD(YEAR, 1, dedicated_start_date) [dedicated_end_date]
            FROM client_dedicated_hours cdh
            INNER JOIN client_dedicated cd ON cdh.id = cd.client_dedicated_hours_id
            WHERE client_id = :client_id
                AND
                (
                    (dedicated_start_date <= @startDate AND DATEADD(YEAR, 1, dedicated_start_date) > @startDate)
                    OR
                    (dedicated_start_date <= @endDate AND DATEADD(YEAR, 1, dedicated_start_date) > @endDate)
                )
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindValue(':start_date', $this->startDate, PDO::PARAM_STR)
            ->bindValue(':end_date', $this->endDate, PDO::PARAM_STR)
            ->bindValue(':client_id', $this->clientID, PDO::PARAM_INT)
            ->queryAll();

        foreach ($results as $index => $result)
        {
            $results[$index]['dedicated_hours'] = intval($result['dedicated_hours']);
            $results[$index]['dedicated_hours_remaining'] = intval($result['dedicated_hours']);
            $results[$index]['dedicated_start_date'] = new DateTime($result['dedicated_start_date']);
            $results[$index]['dedicated_end_date'] = new DateTime($result['dedicated_end_date']);
        }

        return $results;
    }

    /**
     * Getting the most recent dedicated pool of hours per unique client within
     * the last year
     * @return ClientDedicatedHours[]
     */
    public function GetMostRecentDedicatedHourPools()
    {
        return ClientDedicatedHours::model()->findAll(array(
            'condition' => 'id IN (
                -- Get Only most recent of each client dedicated service
                -- Needed in case there is more than one entry for a client within a year (unlikely)
                SELECT MAX(client_dedicated_hours_id)
                FROM client_dedicated cd
                INNER JOIN client_dedicated_hours cdh ON cdh.id = cd.client_dedicated_hours_id
                WHERE dedicated_start_date > DATEADD(YEAR, -1, GETDATE())
                GROUP BY client_id
            )',
            'order' => 'name ASC'
        ));
    }
}
