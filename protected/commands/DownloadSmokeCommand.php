<?php

/**
 * This class uses the ArcGIS Server REST API to retrieve smoke data.  The data is then
 * simplified and writted to a file on disk:
 * 
 * /protected/downloads/smoke.json
 *
 * ArcGIS Server REST API: http://resources.arcgis.com/en/help/rest/apiref/index.html?geometry.html
 * ESRI Open Data Smoke: http://openesridrp.disasterresponse.opendata.arcgis.com/datasets/f73d7a1495df4ec5b77a5ba6d3cb33f8_0
 */
class DownloadSmokeCommand extends CConsoleCommand
{
    private $_filepath;

	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $geom = array(
            'xmin' => -124.848974,
            'ymin' => 24.396308,
            'xmax' => -66.885444,
            'ymax' => 49.384358,
            'spatialReference' => array(
                'wkid' => 4326
            )
        );

        $url = 'http://openesridrp.disasterresponse.opendata.arcgis.com/datasets/f73d7a1495df4ec5b77a5ba6d3cb33f8_0.geojson?' . http_build_query(array(
            'where' => rawurlencode("smoke_classdesc IN ('25 - 63', '63 - 158', '158 - 1000')"),
            'geometry' => json_encode($geom),
            'outFields' => 'smoke_classdesc',
            'geometryPrecision' => 3
        ));

        $query = str_replace('"', '%22', urldecode($url));

        // Let script try for one minute.
        $limit = 0;
        do
        {
            $data = CurlRequest::getRequest($query);
            $datajson = json_decode($data, true);
            if (json_last_error() == JSON_ERROR_NONE)
            {
                if (in_array('status', array_keys($datajson)) === false)
                {
                    break;
                }
            }
            $limit++;
            print "Request processing ... trying again\n";
            sleep(10);
        }
        while ($limit < 6);

        $this->_filepath = Yii::getPathOfAlias('application.downloads') . DIRECTORY_SEPARATOR . 'smoke.json';

        // Requests failed, create empty feature collection
        if ($limit == 6)
        {
            return $this->createEmptyFeatureCollection();
        }

        $data = json_decode($data, true);

        // Json decoding didn't work, create empty feature collection
        if (json_last_error())
        {
            return $this->createEmptyFeatureCollection();
        }

        if (isset($data['features']))
        {
            // ------------------------ PHP array data reduction

            $featuresCount = count($data['features']);

            // Reducing complexity of feature collections before geospatial work
            for ($index = 0; $index < $featuresCount; $index++)
            {
                // Removing all property keys that aren't "smoke_classdesc"
                foreach (array_keys($data['features'][$index]['properties']) as $key)
                {
                    if ($key === 'smoke_classdesc')
                        $data['features'][$index]['properties']['smoke'] = $data['features'][$index]['properties']['smoke_classdesc'];
                    unset($data['features'][$index]['properties'][$key]);
                }
            }

            // ------------------------ Geospatial data reduction

            Assets::registerGeoPHP();

            // Build SQL insert statement

            $count = 0;
            $sqlinsert = "INSERT INTO @smoke (geog, classification) VALUES \n";

            for ($index = 0; $index < $featuresCount; $index++)
            {
                $geom = geoPHP::load(json_encode($data['features'][$index]['geometry']), 'geojson');

                // SQL Server must have fewer than 1,000 inserts in a batch
                if ($count < 900)
                {
                    $sqlinsert .= " (geography::STGeomFromText('{$geom->out('wkt')}', 4326), '{$data['features'][$index]['properties']['smoke']}'), \n";
                    $count++;
                }
                else
                {
                    $sqlinsert = substr($sqlinsert, 0, -3);
                    $sqlinsert.= " \nINSERT INTO @smoke (geog, classification) VALUES \n";
                    $sqlinsert .= " (geography::STGeomFromText('{$geom->out('wkt')}', 4326), '{$data['features'][$index]['properties']['smoke']}'), \n";
                    $count = 0;
                }
            }

            $sqlinsert = substr($sqlinsert, 0, -3);

            $sql = "
            SET NOCOUNT ON;

            DECLARE @smoke TABLE(
	            geog GEOGRAPHY,
	            classification VARCHAR(255)
            );

            -- Insert data into temp table
            $sqlinsert

            -- Ensure the the geogs are valid and oriented correctly
            UPDATE @smoke
            SET geog = CASE
		        WHEN geog.MakeValid().EnvelopeAngle() >= 90 THEN geog.MakeValid().ReorientObject()
		        WHEN geog.STIsValid() = 0 THEN geog.MakeValid()
		        ELSE geog
	        END

            -- Union the geogs together based on the classification
            SELECT classification, GEOGRAPHY::UnionAggregate(geog).STAsText() wkt FROM @smoke GROUP BY classification
            ";

            $results = Yii::app()->db->createCommand($sql)->queryAll();

            $featureCollection = array(
                'type' => 'FeatureCollection',
                'features' => array()
            );

            // Create feature collection to output to file
            foreach ($results as $row)
            {
                $geom = geoPHP::load($row['wkt'], 'wkt');

                $json = json_decode($geom->out('json'), true);

                // Rounding decimal values to lower precision/file size ... doesn't matter when displayed on such a small scale
                array_walk_recursive($json, function(&$value, $key) { if (is_float($value)) $value = round($value, 4); });

                $featureCollection['features'][] = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'smoke' => $row['classification']
                    ),
                    'geometry' => $json
                );
            }

            $data = $featureCollection;
        }
        
        file_put_contents($this->_filepath, json_encode($data));
        
        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Output a file with the contents of an empty feature collection
     */
    private function createEmptyFeatureCollection()
    {
        file_put_contents($this->_filepath, '{"type":"FeatureCollection","features":[]}');
    }
}