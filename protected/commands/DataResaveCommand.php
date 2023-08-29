<?php

class DataResaveCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $time_start = microtime(true);

        $tables = array(
            'res_notice' => array(
                'comments',
                'notes'
            ),
            'res_call_attempt' => array(
                'general_comments',
                'dashboard_comments'
            ),
            'res_ph_visit' => array(
                'publish_comments',
                'comments'
            ),
            'res_fire_obs' => array(
                'Supression'
            ),
            'res_fire_name' => array(
                'fire_summary'
            ),
            'client' => array(
                'response_disclaimer'
            ),
            'res_post_incident_summary' => array(
                'fire_summary',
                'wds_actions'
            ),
            'res_property_access' => array(
                'suppression_resources',
                'other_info',
                'access_issues'
            ),
            'res_monitor_log' => array(
                'Comments'
            )
        );

        $yiiDB = Yii::app()->db;

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
        );

        $pdo = new PDO($yiiDB->connectionString, $yiiDB->username, $yiiDB->password, $options);

        foreach ($tables as $table => $columns)
        {
            // Getting table primary key

            $sql = "SELECT Col.Column_Name
            FROM
                INFORMATION_SCHEMA.TABLE_CONSTRAINTS Tab,
                INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE Col
            WHERE
                Col.Constraint_Name = Tab.Constraint_Name
                AND Col.Table_Name = Tab.Table_Name
                AND Constraint_Type = 'PRIMARY KEY'
                AND Col.Table_Name = '$table'
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pk = $stmt->fetchColumn();

            // getting total count of entries in this table

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $totalcount = (int)$stmt->fetchColumn();

            // select pk + update column for this table

            $selectColumns = join(', ', array_merge(array($pk), $columns));

            $stmt = $pdo->prepare("SELECT $selectColumns FROM $table");
            $stmt->execute();

            $count = 0;
            $countupdates = 0;

            // Iterate select statement results

            while(($row = $stmt->fetch()) !== false)
            {
                if ($count === 0)
                {
                    printf("Starting updates for %s\n\n", $table);
                }

                // Save data back to database in system encoding

                $updatecolumns = join(', ', array_map(function($column) { return "$column = :$column"; }, $columns));

                // Setting ansi warnings off allows truncation of data, if needed

                $updatestmt = $pdo->prepare("
                SET ANSI_WARNINGS OFF
                UPDATE $table SET $updatecolumns WHERE $pk = {$row[$pk]}
                ");

                $updatestmt->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);

                foreach ($columns as $column)
                {
                    $updatestmt->bindParam(":$column", $row[$column]);
                }

                try
                {
                    $updatestmt->execute();
                }
                catch (PDOException $exception)
                {
                    print "There was an error on $table for $pk = {$row[$pk]}\n";
                    print $exception->getMessage() . "\n";
                    print "\n\n";
                }

                if ($updatestmt->rowCount() > 0)
                {
                    $countupdates++;
                }

                $count++;

                if ($count % 50 === 0)
				{
					printf("%0.2f%% records processed\n", (((float)$count/(float)$totalcount)* 100));
				}

                if ($count === $totalcount)
                {
                    printf("%d records updated in %s\n\n", $countupdates, $table);
                }
            }
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }
}