<?php

/**
 * This is the model class for table 'risk_whp'.
 *
 * @property float $whp
 * @property float $distance
 * @property float $inverse_distance
 */

class RiskWhp
{
    private static $__instance = null;

    private static function getStaticInstance()
    {
        if (self::$__instance == null)
        {
            self::$__instance = new static();
        }
        return self::$__instance;
    }

    private function loadModel($results)
    {
        $models = array();
        foreach ($results as $params)
        {
            $model = new static();
            foreach ($params as $key => $value)
            {
                $model->$key = $value;
            }
            $models[] = $model;
        }
        return $models;
    }

    //----------------------------------------------------General Functions-------------------------------------------------

    /**
     * Description: Using the given lat/lon, this function get's all raw WHP values within 500 meters, than turns
     * it into a whp score
     * @param string $lat decimal degree coordinte in the WGS84 Coordinate System
     * @param string $lon decimal degree coordinte in the WGS84 Coordinate System
     * @return float The whp score
     */
    static public function executeWhpModel($albersPoint)
    {
        $northing = $albersPoint->y;
        $easting = $albersPoint->x;

        $stored_procudure = 'sp_risk_whp_query';

        $db = new PDO(Yii::app()->riskdb->connectionString, Yii::app()->riskdb->username, Yii::app()->riskdb->password);
        $db->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
        $stmt  = $db->prepare("EXECUTE $stored_procudure @northing=:northing_value, @easting=:easting_value");
        $stmt->bindParam(':northing_value', $northing, PDO::PARAM_STR);
        $stmt->bindParam(':easting_value', $easting, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $results = $stmt->fetchAll();

        $whp = self::getStaticInstance();

        $tabular_data = $whp->loadModel($results);
        $score = $whp->getWhpScore($tabular_data);

        return $score;
    }

    /*
    static public function executeWhpModel($lat, $lon)
    {
        $results = Yii::app()->riskdb->createCommand('SELECT * FROM riskwhp(:lat, :lon)')
            ->bindParam(':lat', $lat, PDO::PARAM_STR)
            ->bindParam(':lon', $lon, PDO::PARAM_STR)
            ->queryAll();

        $whp = self::getStaticInstance();

        $tabular_data = $whp->loadModel($results);
        $score = $whp->getWhpScore($tabular_data);

        return $score;
    }
    */

	/**
     * Returns calculated whp score from raw sql data.
     * @param array $whp_models An array of static tabular whp models
     * @return float value representing the whp score.
     */
    private function getWhpScore($whp_models)
    {
        // Summation of distances
        $total_distance = array_sum(array_map(function($data) { return $data->distance; }, $whp_models));

        // This yields the points's whp weighted by spatial orientation
        $weighted_whps = array_map(function($data) use ($total_distance) { return ($data->inverse_distance / $total_distance) * $data->whp; }, $whp_models);

        // weighted whps sum
        $weighted_whps_sum = array_sum($weighted_whps);

        // This calculates the maximum xbar using 100,000 scale
        $weighted_whps_100000 = array_map(function($data) use ($total_distance) { return ($data->inverse_distance / $total_distance) * 100000; }, $whp_models);

        // weighted whps 100,000 scale sum
        $weighted_whps_100000_sum = array_sum($weighted_whps_100000);

        // Final spatially weighted average for the buffer converted to a 100000 point scale
		$final = ($weighted_whps_100000_sum <= 0) ? 0 : $weighted_whps_sum / $weighted_whps_100000_sum;

        return $final;
    }
}
