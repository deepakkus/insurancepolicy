<?php

class RiskStataDataOutputCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true);

        print '-----STARTING COMMAND--------' . PHP_EOL;

        if (isset($args[0]))
        {
            $fileName = $args[0];

            $fileHandleInput = fopen('C:\IMPORT_FILES\RISK\\' . $fileName, 'r');
            $fileHandleOutput = fopen('C:\IMPORT_FILES\RISK\\' . str_replace('.csv', '_output.csv', $fileName), 'w');

            $headerFields = fgetcsv($fileHandleInput);

            fputcsv($fileHandleOutput, $headerFields);

            $headerFields = array_map('trim', $headerFields);
            $headerFields = array_map('strtolower', $headerFields);

            $columnPid = array_search('pid', $headerFields);
            $columnLat = array_search('lat', $headerFields);
            $columnLong = array_search('long', $headerFields);
            $columnFireName = array_search('fire name', $headerFields);
            $columnState = array_search('state', $headerFields);
            $columnLoss = array_search('loss', $headerFields);

            $data = null;
			$count = 0;

            while (($data = fgetcsv($fileHandleInput)) !== false)
            {
                $lat = $data[$columnLat];
                $long = $data[$columnLong];

                $riskModel = new RiskModel();

                list(
                    $score_wds,
                    $score_v,
                    $score_whp,
                    $risk_variable_1,
                    $risk_variable_2,
                    $risk_variable_3,
                    $slope_xbar,
                    $slope_xbar13,
                    $sum_cluster_results
                ) = $riskModel->executeRiskModel($lat, $long, RiskModel::RISK_QUERY_TABULAR, false, true);

                $stateMeanModel = RiskStateMeans::loadModelByLatLong($lat, $long);

				$stateMean = $stateMeanModel ? $stateMeanModel->mean : null;

                $outputData = array(
                    $data[$columnPid],
                    $data[$columnLat],
                    $data[$columnLong],
                    $data[$columnFireName],
                    $data[$columnState],
                    $data[$columnLoss],
                    $stateMean,
                    $score_wds,
                    $score_v,
                    $score_whp,
                    $risk_variable_1 * 0.5,
                    $risk_variable_2 * 0.5,
                    $risk_variable_3 * 0.5,
                    $slope_xbar,
                    $slope_xbar13,
                    $sum_cluster_results * 0.5
                );

                fputcsv($fileHandleOutput, $outputData);

				$count++;

				if ($count % 50 === 0)
				{
					printf("%d records processed\n", $count);
				}
            }

            fclose($fileHandleInput);
            fclose($fileHandleOutput);
        }
        else
        {
            print "\n";
            print "This script requires an argument of the filename for the input csv file.\n";
            print "\n";
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }
}