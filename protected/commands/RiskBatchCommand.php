<?php
class RiskBatchCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print "\n-----STARTING COMMAND--------\n\n";

        if(isset($args[0]))
            $this->runBatch($args[0]);
        else
            print "A batch ID needs to be supplied \n";

        print "\n-----DONE WITH COMMAND-------\n";
    }

    //Select all entries for the batch, than run each against the risk algorithm
    public function runBatch($id)
    {
        //Get batch and set status
        $batch = RiskBatchFile::model()->findByPk($id);
        $batch->status = 'processing';
        $batch->date_run = date('Y-m-d H:i');
        $batch->save();

        //Batch it out to 1000 entries at a time
        $sql = "SELECT TOP 1000 * FROM risk_score WHERE batch_file_id = :batch_file_id AND (processed IS NULL OR processed = 0)";
        $models = RiskScore::model()->findAllBySql($sql, array(':batch_file_id' => $id));
        $riskModel = new RiskModel();

        while (!empty($models))
        {
            foreach ($models as $model)
            {
                //record query type - in this case batch
                $model->queryType = 'bulk';

                //Needs to be geocoded
                if (!$model->lat || !$model->long)
                {
                    $address = $model->address . ' ' . $model->city . ' ' . $model->state . ' ' . Helper::splitZipCode($model->zip);
                    $addressParts = array('address'=>$model->address, 'city'=>$model->city, 'state'=>$model->state, 'zip'=> Helper::splitZipCode($model->zip));
                    $geocode = Geocode::getLocation($address, 'address', 1, $model->client_id, $addressParts);

                    //Good match, use the coordinates
                    if (isset($geocode['location_type']))
                    {
                        $model->lat = $geocode['geometry']['lat'];
                        $model->long = $geocode['geometry']['lon'];
                        $model->match_type = $geocode['location_type'];
                        $model->match_score = $geocode['location_score'];
                        $model->match_address = $geocode['address_formatted'];
                        $model->wds_geocode_level = ($geocode['location_type'] == 'address' && $geocode['location_score'] > .75) ? 'address' : 'unmatched';
                        $model->geocoded = 1;
                    }
                    //Something went wrong with the geocoding
                    else
                    {
                        $model->lat = null;
                        $model->long = null;
                        $model->match_type = 'unmatched';
                        $model->match_score = 0;
                        $model->match_address = 'could not match';
                        $model->geocoded = 1;
                        $model->wds_geocode_level = 'unmatched';
                    }
                }

                //Now we've got coordinates, either from the geocoder or from the csv
                if ($model->lat && $model->long && $model->wds_geocode_level != 'unmatched')
                {
					$result = $riskModel->executeRiskModel($model->lat, $model->long, 1);
                    $model->score_v = number_format(round($result['score_v'], 8), 8);
                    $model->score_whp = number_format(round($result['score_whp'], 8), 8);
                    $model->score_wds = number_format(round($result['score_wds'], 8), 8);
                    $model->date_created = date('Y-m-d H:i:s');
                    $model->score_type = 2;
                    $model->processed = 1;
                }
                //Still doesn't have coordinates - unmatched by geocoder and client didn't provide coordinates
                else
                {
                    $model->score_v = 0;
                    $model->score_whp = 0;
                    $model->score_wds = 0;
                    $model->date_created = date('Y-m-d H:i:s');
                    $model->score_type = 2;
                    $model->processed = 1;
                }

                if (!$model->save())
                {
                    print "Couldn't save the risk score! \n";
                    print_r ($model->getErrors());
                }

                print 'Risk Score ' . $model->id . ' processed with WDS score of ' . $model->score_wds . PHP_EOL;
                print 'Geocoded: ' . $model->geocoded . ' Match score: ' . $model->match_score . ' Match Type: ' . $model->match_type . ' WDS Geocode Level: ' . $model->wds_geocode_level . PHP_EOL;
            }

            $models = RiskScore::model()->findAllBySql($sql, array(':batch_file_id' => $id));

    }

        $batch->status = 'complete';
        $batch->save();
    }
}