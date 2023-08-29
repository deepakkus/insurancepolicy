<?php

class UpdateRiskCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $command = Yii::app()->riskdb->createCommand();
        
        $topID = $command->setText('SELECT TOP 1 id FROM risk_model_update ORDER BY id DESC')->queryScalar();
        $topID = intval($topID);

        // Iterate through chunks of the risk_model_update table, by default, 10,000 at a time
        // and insert into the risk_model table
        foreach ($this->chunks($topID, 10000) as list($low, $high))
        {
            $sql = 'INSERT INTO risk_model (lat, long, point_id, risk, vcc, flame_length, crown, slope, vdep, fuel_id, point)
                SELECT lat, long, point_id, risk, vcc, flame_length, crown, slope, vdep, fuel_id, point
                FROM risk_model_update
                WHERE id BETWEEN :low AND :high;';

            printf("Executing from id %d to %d\n", $low, $high);

            $command->setText($sql)->execute(array(
                ':low' => $low,
                ':high' => $high
            ));
        }
        
        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Returns a generator given a maxnumber and rangesize that produces and 
     * high and low value until the maxnumber is met.
     * 
     * The purpose of this function is to provide SQL "between" values when running SQL inserts
     * from a larger database.  This way, no table locks are in affect.
     * 
     * @param integer $maxnumber 
     * @param integer $rangesize 
     */
    private function chunks($maxnumber, $rangesize=10000) // 20394668
    {
        $count = 0;
        while ($count < $maxnumber)
        {
            $low = $count;
            if ($count + $rangesize > $maxnumber)
                $count = $maxnumber;
            else
                $count += $rangesize;

            yield array($low + 1, $count);
        }
    }
}