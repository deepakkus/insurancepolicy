<?php

/**
 * This is the model class for table 'risk_model'.
 *
 * The followings are the available columns in table 'risk_model':
 * @property integer $id
 * @property string $lat
 * @property string $long
 * @property integer $point_id
 * @property integer $risk
 * @property integer $vcc
 * @property integer $flame_length
 * @property integer $crown
 * @property integer $slope
 * @property integer $vdep
 * @property integer $fuel_id
 * @property float $distance
 * @property float $inverse_distance
 * @property integer $cluster
 * @property string $polygon
 * @property integer $pointcount
 */

class RiskModel
{
    const SMALL_NUM = 0.000000000000000001;

    const RISK_QUERY_TABULAR = 1;
    const RISK_QUERY_MAP = 2;
    const RISK_QUERY_BOTH = 3;

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'lat' => 'Lat',
			'long' => 'Long',
			'point_id' => 'Point',
			'risk' => 'Risk',
			'vcc' => 'VCC',
			'flame_length' => 'Flame Length',
			'crown' => 'Crown',
			'slope' => 'Slope',
			'vdep' => 'Vdep',
			'fuel_id' => 'Fuel',
            'distance' => 'Distance',
            'inverse_distance' => 'Inverse Distance',
            'cluster' => 'Cluster'
		);
	}

    public function loadModel($results)
    {
        $models = array();
        foreach ($results as $params)
        {
            $model = new static();
            foreach ($params as $key => $value)
            {
                $model->$key = $value;
            }
            //Temp bug fix for bad risk data - remove when new risk model is hooked up
            if(isset($model->vcc)){
                $model->risk = $model->vcc + $model->flame_length + $model->crown;
            }

            $models[] = $model;
        }
        return $models;
    }

    //----------------------------------------------------Risk Model Stored Procedures--------------------------------------

	/**
     * Returns an array of Risk Model classes
     * @param float $lat coordinte in the WGS84 Coordinate System
     * @param float $lon coordinte in the WGS84 Coordinate System
     * @param int $type query return type
     *      1 -> Score Only     (RiskModel::RISK_QUERY_TABULAR)
     *      2 -> Map Only       (RiskModel::RISK_QUERY_MAP)
     *      3 -> Score and Map  (RiskModel::RISK_QUERY_BOTH)
     * @param bool $data should data be output for tabular display
     * @param bool $stata should data be output for stata output command
     * @return array with the following structure
     * {
     *      score_v: x.xxxxx,
     *      score_whp: x.xxxxx,
     *      score_wds: x.xxxxx,
     *      map: geojson_feature_collection, // Optional
     *      error: 0
     * }
     *
     * Note: Depending on the $type param revieved, the return array with only be
     * returned with the corresponding values.
     */
    public function executeRiskModel($lat, $lon, $type, $data = false, $stata = false)
    {
        $albersPoint = self::projectWGStoAlbers($lat, $lon);

        $northing = $albersPoint->y;
        $easting = $albersPoint->x;

        if ($type == self::RISK_QUERY_TABULAR) { $stored_procudure = 'sp_risk_model_query';       }
        if ($type == self::RISK_QUERY_MAP)     { $stored_procudure = 'sp_risk_model_query_map';   }
        if ($type == self::RISK_QUERY_BOTH)    { $stored_procudure = 'sp_risk_model_query_both';  }

        $db = new PDO(Yii::app()->riskdb->connectionString, Yii::app()->riskdb->username, Yii::app()->riskdb->password);
        $db->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
        $stmt  = $db->prepare("EXECUTE $stored_procudure @northing=:northing_value, @easting=:easting_value");
        $stmt->bindParam(":northing_value", $northing, PDO::PARAM_STR);
        $stmt->bindParam(":easting_value", $easting, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $results = $stmt->fetchAll();

        if ($data === true)
        {
            $tabular_data = $this->loadModel($results);
            $tabular_data_clusters = $this->getClusters($tabular_data);
            return array($tabular_data, $tabular_data_clusters);
        }

        if ($stata === true)
        {
            $tabular_data = $this->loadModel($results);

            list(
                $score_v,
                $risk_variable_1,
                $risk_variable_2,
                $risk_variable_3,
                $sum_cluster_results
            ) = $this->getRiskScore($tabular_data, true);

            list(
                $slope_xbar,
                $slope_xbar13
            ) = self::riskStataSlopeAnalysis($tabular_data);

            $score_whp = RiskWhp::executeWhpModel($albersPoint);
            $score_wds = $score_v * $score_whp;

            return array(
                $score_wds,
                $score_v,
                $score_whp,
                $risk_variable_1,
                $risk_variable_2,
                $risk_variable_3,
                $slope_xbar,
                $slope_xbar13,
                $sum_cluster_results
            );
        }

        $retval = array();

        // Point queried outside of the risk layer
        if (empty($results))
        {
            $retval['score_v'] = -1;
            $retval['score_whp'] = -1;
            $retval['score_wds'] = -1;
            $retval['error'] = 1;
            $retval['error_message'] = "No risk data available for the given point. It was likely outside of the risk layer boundary.";
            if ($type != self::RISK_QUERY_TABULAR)
                $retval['map'] = -1;
        }
        // Populate Score Only
        else if ($type == self::RISK_QUERY_TABULAR)
        {
            $tabular_data = $this->loadModel($results);
            $score_v = $this->getRiskScore($tabular_data);
            $score_whp = RiskWhp::executeWhpModel($albersPoint);
            $retval['score_v'] = $score_v;
            $retval['score_whp'] = $score_whp;
            $retval['score_wds'] = $score_v * $score_whp; //Used to check to see if WHP was 0 and replace w/ small number, but we're ok with 0 now (should continue to use "0" if that is what the WHP layer has. SMALL_NUM would create an arbitrarily small number and would dilute the accuracy of the overall dataset. NL)
            $retval['error'] = 0;
        }
        //Populate geojson data only
        else if ($type == self::RISK_QUERY_MAP)
        {
            // Populate Feature Collection
            $map_data = $this->loadModel($results);
            $results_geojson = array_map(function($data) { return $data->polygon; }, $map_data);
            $geojson_array = $this->convertWKTtoGeojson($results_geojson);
            $geojson_feature_class = $this->buildGeojsonFeatureCollection($geojson_array, $map_data);
            $retval['map'] = $geojson_feature_class;
            $retval['error'] = 0;
        }
        // Populate Score and geojson data
        else if ($type == self::RISK_QUERY_BOTH)
        {
            // Populate Score
            $tabular_data = $this->loadModel($results);
            $score_v = $this->getRiskScore($tabular_data);
            $score_whp = RiskWhp::executeWhpModel($albersPoint);
            $retval['score_v'] = $score_v;
            $retval['score_whp'] = $score_whp;
            $retval['score_wds'] = $score_v * $score_whp;
            $stmt->nextRowset();
            // Populate Feature Collection
            $results = $stmt->fetchAll();
            $map_data = $this->loadModel($results);
            $results_geojson = array_map(function($data) { return $data->polygon; }, $map_data);
            $geojson_array = $this->convertWKTtoGeojson($results_geojson);
            $geojson_feature_class = $this->buildGeojsonFeatureCollection($geojson_array, $map_data);
            $retval['map'] = $geojson_feature_class;
            $retval['error'] = 0;
        }

        return $retval;
    }

    //----------------------------------------------------Risk Model Algorithm----------------------------------------------

	/**
     * Returns calculated risk score from raw sql data.
     * @param array $risk_models An array of static tabular risk models
     * @param bool $stata should this method output an array of data for stata command output
     * @return float|array value representing the risk score.
     */
    private function getRiskScore($risk_models, $stata = false)
    {
        $results = self::riskDataAnalysis($risk_models);
        $risk_variable_1 = $results['risk_variable_1'];
        $risk_variable_2 = $results['risk_variable_2'];
        $risk_variable_3 = $results['risk_variable_3'];
        $total_distance = $results['total_distance'];
        $xbar_max_sum = $results['xbar_max_sum'];

        // Getting unique cluster values
        $unique_clusters = array_unique(array_filter(array_map(function($data) { return $data->cluster; }, $risk_models)));
        $cluster_results = array();

        // Getting all tabular data for each cluster, running through analysis
        foreach($unique_clusters as $cluster_id)
        {
            $cluster = array_filter($risk_models, function($data) use ($cluster_id) { return $data->cluster === $cluster_id; });

            $cluster_results[] = self::clusterDataAnalysis($risk_models, $cluster, $total_distance);
        }

        $FINAL_RESULT = ($risk_variable_1 + $risk_variable_2 + $risk_variable_3 + array_sum($cluster_results)) * 0.5;

        if ($stata === true)
        {
            return array(
                $FINAL_RESULT,
                $risk_variable_1,
                $risk_variable_2,
                $risk_variable_3,
                array_sum($cluster_results)
            );
        }

        return $FINAL_RESULT;
    }

    /**
     * Returns 2 dimensional array of cluster risk data
     * @param array $risk_models An array of static tabular risk models
     * @return array
     */
    private function getClusters($risk_models)
    {
        $unique_clusters = array_unique(array_filter(array_map(function($data) { return $data->cluster; }, $risk_models)));

        $cluster_results = array();
        foreach($unique_clusters as $cluster_id)
            $cluster_results[] = array_filter($risk_models, function($data) use ($cluster_id) { return $data->cluster === $cluster_id; });

        return $cluster_results;
    }

    /**
     * Perform special slope data analysis for stata data output
     * @param array $risk_data
     */
    static protected function riskStataSlopeAnalysis($risk_data)
    {
        $slope_xbar = array_sum(array_map(function($data) { return $data->inverse_distance * $data->slope; }, $risk_data));

        $slope_xbar_13 = array_sum(array_map(function($data) { return $data->inverse_distance * 13.5; }, $risk_data));

        return array(
            $slope_xbar,
            $slope_xbar_13
        );
    }

	/**
     * Returns an array of risk variables and distance from the WDS Risk Algorithm.
     * @param array $risk_data
     */
    static protected function riskDataAnalysis($risk_data)
    {
        // -------------------------------------------------------------------------------------------------
        // Variable 1: Estimator for spatial relationship between site and cells; replacing old models main calculation and the need to weight sides of a map

        // Summation of distances
        $total_distance = array_sum(array_map(function($data) { return $data->distance; }, $risk_data));

        // This yields the points's risk weighted by spatial orientation to house/property site
        $weighted_risks = array_map(function($data) use ($total_distance) {
            return ($data->risk - $data->vcc <= 0) ? 0 : ($data->inverse_distance / $total_distance) * ($data->flame_length + $data->crown);
        }, $risk_data);

        // weighted_risks
        $weighted_risks_sum = array_sum($weighted_risks);

        // Assuming max score of 6, redo cell weight calculation
        $weighted_risks_max_6 = array_map(function($data) use ($total_distance) {
            return ($data->risk - $data->vcc <= 0) ? 0 : 6 * ($data->inverse_distance / $total_distance);
        }, $risk_data);

        $weighted_risks_max_sum = array_sum($weighted_risks_max_6);

        // Final spatially weighted average for the buffer
        $weighted_risks_ratio = ($weighted_risks_max_sum <= 0) ? 0 : $weighted_risks_sum / $weighted_risks_max_sum;

        // -------------------------------------------------------------------------------------------------
        // Variable 2:  Uses the departure from normal (Vdep) as a measure of the frequency of burn, which can be captured in the density of vegetation away from the norm.
        // This adds to risk and should therefore be an additional portion of the risk calculation.

        // "Scrubbed Vdep Values" (RAW Vdep scores over 100 removed since these are non-burnable fuels)
        // Vdep after non-burnables have been removed
        $vdep_scrubbed = array_map(function($data) { return ($data->vdep > 100 || empty($data->vdep)) ? self::SMALL_NUM : $data->vdep; }, $risk_data);

        // Distance from site for all APPLICABLE POINTS
        $vdep_distances = array_map(function($data, $key) use ($vdep_scrubbed) { return ($vdep_scrubbed[$key] == 0) ? self::SMALL_NUM : $data->distance; }, $risk_data, array_keys($risk_data));
        $vdep_distances_sum = array_sum($vdep_distances);

        // (inverse_distance/vdep_distances_sum) * scrubbed vdeps
        $vdep_weighted = array_map(function($data, $key) use ($vdep_distances_sum, $vdep_scrubbed) {
            return ($data->inverse_distance / $vdep_distances_sum) * $vdep_scrubbed[$key];
        }, $risk_data, array_keys($risk_data));

        // Sum of vdep_weighted - unconverted data for xbar
        $vdep_weighted_sum = array_sum($vdep_weighted);

        // Assume max score of 100 for each point E/D*100 (Vdep score ranges 0-100)
        $vdep_weighted_max_100 = array_map(function($data, $key) use ($vdep_weighted, $vdep_distances_sum) {
            return ($vdep_weighted[$key] == 0) ? self::SMALL_NUM : $data->inverse_distance / $vdep_distances_sum * 100;
        }, $risk_data, array_keys($risk_data));

        // Sum H Max score could achieve if all risks were marked 100
        $vdep_weighted_max_100_sum = array_sum($vdep_weighted_max_100);

        // Calculate VDEP weighted ratio
        $weighted_vdep_ratio = ($vdep_weighted_max_100_sum <= 0) ? 0 : $vdep_weighted_sum / $vdep_weighted_max_100_sum;

        // -------------------------------------------------------------------------------------------------
        // Variable 3: accounts for the added risk of being built on a slope.

        // Product of SLOPE and Vdep (INTERACTION TERM)
        $vdep_slope_product = array_map(function($data, $key) use ($vdep_scrubbed) { return $data->slope * $vdep_scrubbed[$key]; }, $risk_data, array_keys($risk_data));

        // Distance to Cell
        $distance_to_cell = array_map(function($data, $key) use ($vdep_slope_product) {
            return ($vdep_slope_product[$key] == 0) ? self::SMALL_NUM : $data->distance;
        }, $risk_data, array_keys($risk_data));

        $distance_to_cell_sum = array_sum($distance_to_cell);

        // Proportional score for slope-Vdep product.
        $vdep_slope_product_scores = array_map(function($data, $key) use ($distance_to_cell_sum, $vdep_slope_product) {
            return ($data->inverse_distance / $distance_to_cell_sum) * $vdep_slope_product[$key];
        }, $risk_data, array_keys($risk_data));

        $vdep_slope_product_scores_sum = array_sum($vdep_slope_product_scores);

        // Max score for slope times Vdep (INTERACTION TERM) for each point.
        $vdep_slope_product_scores_max = array_map(function($data) use ($distance_to_cell_sum) { return ($data->inverse_distance / $distance_to_cell_sum) * 8400; }, $risk_data);
        $vdep_slope_product_scores_max_sum = array_sum($vdep_slope_product_scores_max);

        // No longer convert Value to a 9-point scale for INTERACTION TERM!  Straight division yields a probability.
        $weighted_vdep_slope_ratio = ($vdep_slope_product_scores_max_sum <= 0) ? 0 : $vdep_slope_product_scores_sum / $vdep_slope_product_scores_max_sum;

        // -------------------------------------------------------------------------------------------------
        // Finding the total xbar for the entire area, used later to divide cluster xbars by

        // Assuming max risk score of 9, redo cell weight calculation
        $xbar_max_sum = array_sum(array_map(function($data) use ($total_distance) { return ($data->inverse_distance / $total_distance) * 9; }, $risk_data));

        return array(
            'risk_variable_1' => $weighted_risks_ratio,
            'risk_variable_2' => $weighted_vdep_ratio,
            'risk_variable_3' => $weighted_vdep_slope_ratio,
            'total_distance' => $total_distance,
            'xbar_max_sum' => $xbar_max_sum
        );
    }

	/**
     * Returns an array of cluster value modifiers.
     */
    static protected function clusterDataAnalysis($risk_models, $cluster, $total_distance)
    {
        // This yields the points's risk weighted by spatial orientation to house/property site
        $cluster_weighted_risk = array_map(function($data) use ($total_distance) { return ($data->inverse_distance / $total_distance) * $data->risk; }, $cluster);

        // Sum column AJ for Xbar; This yields the cluster's riskiness spatially weighted to the house/property site; In this case, we get one single number
        $cluster_weighted_risk_sum = array_sum($cluster_weighted_risk);

        // Getting proportional cluster size to total area
        $cluster_proportion_sub_i = count($cluster) / count($risk_models);

        // Assuming max risk score of 9, redo cell weight calculation
        $xbar_max_sum_cluster = array_sum(array_map(function($data) use ($total_distance) { return ($data->inverse_distance / $total_distance) * 9; }, $cluster));

        $result = ($cluster_weighted_risk_sum / $xbar_max_sum_cluster) * $cluster_proportion_sub_i;

        return $result;
    }

    //----------------------------------------------------General Functions-------------------------------------------------

    static protected function projectWGStoAlbers($lat, $lon)
    {
        Assets::registerProj4Php();

        $proj4 = new Proj4php();
        $projWGS84 = new Proj4phpProj('EPSG:4326', $proj4);
        $projAlbers = new Proj4phpProj('EPSG:6952', $proj4);

        $pointSrc = new proj4phpPoint($lon, $lat);

        $pointDest = $proj4->transform($projWGS84, $projAlbers, $pointSrc);

        return $pointDest;
    }

    static protected function projectAlbersToWGS($northing, $easting)
    {
        Assets::registerProj4Php();

        $proj4 = new Proj4php();
        $projWGS84 = new Proj4phpProj('EPSG:4326', $proj4);
        $projAlbers = new Proj4phpProj('EPSG:6952', $proj4);

        $pointSrc = new proj4phpPoint($easting, $northing);

        $pointDest = $proj4->transform($projAlbers, $projWGS84, $pointSrc);

        return $pointDest;
    }

	/**
     * Returns an array of valid geojson string from given Well Known Text array of inputs.
     * @param array $wtk_array An array WKT strings
     * @return array of geojson strings.
     */
    private function convertWKTtoGeojson($wtk_array)
    {
        if (is_array($wtk_array))
        {
            Assets::registerGeoPHP();

            $retval = array();

            foreach ($wtk_array as $wkt)
            {
                $geom = geoPHP::load($wkt, 'wkt');
                $geojson_string = $geom->out('json');

                // Convert northings/eastings to lat/lons

                $geojson_string_wgs = preg_replace_callback('/-?\d+\.\d+,-?\d+\.\d+/', function($matches) {
                    // Regex callback function that converts coordinates in geojson array from Albers to WGS84 coordinate systems.
                    $coords_array = explode(',', $matches[0]);
                    $easting = $coords_array[0];
                    $northing = $coords_array[1];
                    $wgsPoint = self::projectAlbersToWGS($northing, $easting);
                    return "{$wgsPoint->x},{$wgsPoint->y}";
                }, $geojson_string);

                $retval[] = $geojson_string_wgs;
            }

            return $retval;
        }
    }

	/**
     * Builds a geojson feature collections for return to the client and a web mapping platform.
     */
    private function buildGeojsonFeatureCollection($geojson_array, $map_data)
    {
        $geojson_feature_class = array();
        $geojson_feature_class['type'] = 'FeatureCollection';
        $geojson_feature_class['features'] = array();

        $count = count($geojson_array);

        for ($i = 0; $i < $count; $i++)
        {
            $geojson_feature_class['features'][] = array(
                'type' => 'Feature',
                'geometry' => json_decode($geojson_array[$i], true),
                'properties' => array(
                    'risk' => (int)$map_data[$i]->risk,
                    'pointcount' => (int)$map_data[$i]->pointcount
                )
            );
        }

        return $geojson_feature_class;
    }
}
