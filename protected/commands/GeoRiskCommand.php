<?php
class GeoRiskCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print "\n-----STARTING COMMAND--------\n\n";

        //Run all flag is set, so update all
        if(isset($args[0]) && $args[0]){
            $this->runUpdate(true);
        }
        //No flag is set, so just update the 99s
        else{
            $this->runUpdate();
        }


        print "\n-----DONE WITH COMMAND-------\n";
    }

    /**
     * Updates geo risk on select properties
     *
     * @param $updateAll boolean - If set to true, will update all properties where fireshield is "not enrolled". If set to false, will only update
     * those with a geo_risk of 99
     */
    public function runUpdate($updateAll = false)
    {
        //declare sql
        $sql = '';

        //Build SQL
        if($updateAll){
            $sql = "select top 100 p.pid, p.geo_risk, p.wds_lat, p.wds_long, p.state, r.score_wds, r.property_pid, m.mean, m.std_dev from properties p
                    left outer join risk_score r on r.property_pid = p.pid
                    inner join geog_states g on g.abbr = r.state
                    inner join risk_state_means m on m.state_id = g.id
                    where p.fireshield_status = 'not enrolled' and p.client_id = 1 and r.score_wds != '-1'";
        }
        else{
            $sql = "select top 100 p.pid, p.geo_risk, p.wds_lat, p.wds_long, p.state, r.score_wds, r.property_pid, m.mean, m.std_dev from properties p
                    left outer join risk_score r on r.property_pid = p.pid
                    inner join geog_states g on g.abbr = r.state
                    inner join risk_state_means m on m.state_id = g.id
                    where p.fireshield_status = 'not enrolled' and p.client_id = 1 and p.geo_risk = '99' and r.score_wds != '-1'";
        }

        //Get initial data
        $result = Yii::app()->db->createCommand($sql)->queryAll();
        //Counter
        $i = 0;

        while($result){
            foreach($result as $row){

                //declare georisk
                $geoRisk = null;
                $stdDev = null;
                $mean = null;
                $wdsScore = null;

                //Risk score is already set
                if(!empty($row['property_pid'])){
                    $stdDev = $row['std_dev'];
                    $mean = $row['mean'];
                    $wdsScore = $row['score_wds'];
                }
                //No risk score is set, so run risk model and get state mean
                else{
                    $riskModel = new RiskModel;
                    $riskScore = $riskModel->executeRiskModel($row['wds_lat'], $row['wds_long'], RiskModel::RISK_QUERY_TABULAR);
                    $riskStateMean = RiskStateMeans::model()->findBySql("select * from risk_state_means m inner join geog_states g on m.state_id = g.id where g.abbr = :state", array(":state", $row['state']));
                    $stdDev = $riskStateMean->std_dev;
                    $mean = $riskStateMean->mean;
                    $wdsScore = $riskScore['score_wds'];
                }

                // If the standard dev is zero, or risk score had an error than just flag as no georisk
                if ($stdDev == 0 || empty($stdDev) || $wdsScore == -1){
                    $geoRisk = 99;
                }
                else{
                    //Calculate standard deviation
                    $numStdDev = ($wdsScore - $mean) / $stdDev;

                    //Calculate the georisk based on a very rough breakout of standard deviations
                    if ($numStdDev >= 3){
                        $geoRisk = 3;
                    }
                    else if ($numStdDev == 2){
                        $geoRisk = 2;
                    }
                    else{
                        $geoRisk = 1;
                    }

                }

                //Only update if there is a valid georisk, otherwise leave it
                if($geoRisk != 99){
                    //Create update statement
                    $updateSql = "update properties set geo_risk = :geo_risk where pid = :pid";
                    //Update the property
                    Yii::app()->db->createCommand($updateSql)
                        ->bindParam(':geo_risk', $geoRisk,  PDO::PARAM_INT)
                        ->bindParam(':pid', $row['pid'],  PDO::PARAM_INT)
                        ->execute();
                }
            }

            $i+=100;

            if($i % 1000 == 0){
                print "\n-----Finished with $i-------\n";
            }

            //Get initial data
            $result = Yii::app()->db->createCommand($sql)->queryAll();

        }

    }


}