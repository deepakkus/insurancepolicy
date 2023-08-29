<?php
/**
 * Re-geocodes all active policies which a low confidence mapbox score (between .6 and .7).
 * There are roughly 32,940 that fall into this category
 */
class ReGeocodeCommand extends CConsoleCommand
{

	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print '-----STARTING COMMAND--------' . PHP_EOL;

        //Get first batch
        $sql = "select top 500 * from properties where policy_status = 'active' and type_id = 1 and wds_geocoder = 'mapbox' and convert(float, wds_match_score) < .7";
        $results = Property::model()->findAllBySql($sql);
        $count = 0;
        $failed = 0;

        while(!empty($results))
        {
            foreach($results as $result)
            {
                //Set geog and geocoder to null, so that they get re-geocoded on save
                $result->geog = null;
                $result->wds_geocode_level = null;
                //Before save should take care of geocoding
                if(!$result->save())
                {
                    print "Error saving PID " . $result->pid . "\n";
                    $failed +=1;
                }
                else
                {
                    $count +=1;
                }
            }

            //Counter and status
            print "Finished with $count \n";

            //Get next 500 results
            $results = Property::model()->findAllBySql($sql);
        }

        print "-----DONE WITH COMMAND----- \n";
        print "Successfully processed $count policies \n";
        print "Failed to save $failed policies \n";

    }

}