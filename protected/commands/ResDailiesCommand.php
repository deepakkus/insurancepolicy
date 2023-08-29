<?php
class ResDailiesCommand extends CConsoleCommand
{
    private $dbCommand;

	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $time_start = microtime(true);

        $this->dbCommand = Yii::app()->db->createCommand();

        $clients = Client::model()->findAll(array('order'=>'name ASC','condition'=>'wds_fire = 1'));

        foreach ($clients as $client)
        {
            print '-------Starting ' . $client->name . '---------' . PHP_EOL;
            $dailiesResults = $this->getDailiesData($client);

            $daily = new ResDaily;
            $daily->monitored = $dailiesResults->monitored;
            $daily->fires_triggered = $dailiesResults->fires_triggered;
            $daily->fires_responding = $dailiesResults->fires_responding;
            $daily->exposure = $dailiesResults->exposure;
            $daily->policy_triggered = $dailiesResults->policy_triggered;
            $daily->response_enrolled = $dailiesResults->response_enrolled;
            $daily->client_id = $client->id;
            $daily->published = 1;

            if (!$daily->save())
            {
                exit('Was unable to save!');
            }

            print 'Completed ' . $client->name . 'with data:' . PHP_EOL;
            var_dump($dailiesResults);
            print PHP_EOL;
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Getting data neccessary to autofill the dailies model
     * @param model $client
     */
    private function getDailiesData($client)
    {
        $dailiesData = new StdClass;

        $dailiesData->response_enrolled = $this->dbCommand->setText("SELECT COUNT(pid)
        FROM properties
            INNER JOIN members on members.mid = properties.member_mid
        WHERE members.client = :clientName
	        AND response_status = 'enrolled'
	        AND response_enrolled_date < :today
	        AND policy_status = 'active'")
            ->bindValue(':clientName', $client->name)
            ->bindValue(':today', date('Y-m-d'))
            ->queryScalar();

        $dailiesData->monitored = $this->dbCommand->setText('SELECT COUNT(DISTINCT res_fire_obs.Fire_ID)
        FROM res_monitor_log
            INNER JOIN res_monitor_triggered ON res_monitor_log.Monitor_ID = res_monitor_triggered.monitor_id
            INNER JOIN res_fire_obs ON res_monitor_log.Obs_ID = res_fire_obs.Obs_ID
        WHERE monitored_date >= :yesterday
            AND monitored_date < :today
            AND res_monitor_triggered.client_id = :clientID')
            ->bindValue(':yesterday', date('Y-m-d', strtotime('yesterday')))
            ->bindValue(':today', date('Y-m-d'))
            ->bindValue(':clientID', $client->id)
            ->queryScalar();

        $dailiesData->fires_triggered = $this->dbCommand->setText('SELECT COUNT(DISTINCT res_fire_obs.Fire_ID)
        FROM res_monitor_log
	        INNER JOIN res_monitor_triggered ON res_monitor_log.Monitor_ID = res_monitor_triggered.monitor_id
	        INNER JOIN res_fire_obs ON res_monitor_log.Obs_ID = res_fire_obs.Obs_ID
        WHERE monitored_date >= :yesterday
            AND monitored_date < :today
            AND res_monitor_triggered.client_id = :clientID
            AND (res_monitor_triggered.enrolled > 0 OR res_monitor_triggered.eligible > 0)')
            ->bindValue(':yesterday', date('Y-m-d', strtotime('yesterday')))
            ->bindValue(':today', date('Y-m-d'))
            ->bindValue(':clientID', $client->id)
            ->queryScalar();

        $dailiesData->fires_responding = $this->dbCommand->setText('SELECT COUNT(fire_id) FROM res_notice WHERE notice_id IN (
	        SELECT MAX(notice_id) FROM res_notice WHERE client_id = :clientID GROUP BY client_id, fire_id
        ) AND wds_status = 1')
            ->bindValue(':clientID', $client->id)
            ->queryScalar();

        $triggeredPolicies = $this->dbCommand->setText("
		SET NOCOUNT ON;

        DECLARE @buffer FLOAT(24) = 4828.03;
        DECLARE @clientID INT = :clientID

        DECLARE @perimeters TABLE(
            id INT,
            geog GEOGRAPHY
        );

        INSERT INTO @perimeters (p.id, l.geog)
        SELECT p.id, geography::STGeomFromWKB(geog.STBuffer(@buffer).STAsBinary(), 4326)
        FROM res_perimeters p
		INNER JOIN location l ON p.perimeter_location_id = l.id
        WHERE p.id IN (
	        SELECT MAX(id) FROM res_perimeters WHERE res_perimeters.fire_id IN (
		        SELECT DISTINCT res_fire_obs.Fire_ID
		        FROM res_monitor_log
			        INNER JOIN res_monitor_triggered ON res_monitor_log.Monitor_ID = res_monitor_triggered.monitor_id
			        INNER JOIN res_fire_obs ON res_monitor_log.Obs_ID = res_fire_obs.Obs_ID
	            WHERE monitored_date >= :yesterday
		            AND monitored_date < :today
			        AND res_monitor_triggered.client_id = @clientID
			        AND (res_monitor_triggered.enrolled > 0 OR res_monitor_triggered.eligible > 0)
	        ) GROUP BY fire_id
        )

        SELECT coverage_a_amt
        FROM properties
        INNER JOIN @perimeters ON properties.geog.STIntersects([@perimeters].geog) = 1
        WHERE properties.client_id = @clientID
            AND type_id = 1
            AND policy_status = 'active'")
            ->bindValue(':clientID', $client->id)
            ->bindValue(':yesterday', date('Y-m-d', strtotime('yesterday')))
            ->bindValue(':today', date('Y-m-d'))
            ->queryAll();


        $dailiesData->exposure = array_sum(array_map(function($policy) { return $policy['coverage_a_amt']; }, $triggeredPolicies));
        $dailiesData->policy_triggered = count($triggeredPolicies);

        return $dailiesData;
    }
}