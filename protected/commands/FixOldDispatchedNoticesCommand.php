<?php

class FixOldDispatchedNoticesCommand extends CConsoleCommand
{
    const DISTACHED = 1;
    const NONDISPATCHED = 2;
    const DEMOBILIZED = 3;

	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true);

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $n = 0;
        $noticeIDsToUpdate = array();
        $command  = Yii::app()->db->createCommand();
        $totalCount = (float)$command->setText('SELECT COUNT(notice_id) FROM res_notice')->queryScalar();
        $fireIDs = $command->setText('SELECT DISTINCT fire_id FROM res_notice ORDER BY fire_id ASC')->queryColumn();

        // Go through each fire, one at a time
        foreach ($fireIDs as $fireID)
        {
            $clientIDs = $command->setText('SELECT DISTINCT client_id FROM res_notice WHERE fire_id = :fire_id ORDER BY client_id ASC')->queryColumn(array(
                ':fire_id' => $fireID
            ));

            // Go through each client on that fire, one at a time
            foreach ($clientIDs as $clientID)
            {
                $notices = $command->setText('SELECT notice_id,fire_id,client_id,wds_status FROM res_notice WHERE fire_id = :fire_id AND client_id = :client_id ORDER BY notice_id ASC')->queryAll(true, array(
                    ':fire_id' => $fireID,
                    ':client_id' => $clientID
                ));

                // If there are any "Non-Dispatched" notices after a "Dispatched" notice, record the notice_id to change to demobilized later

                $lookForNonDispatched = false;

                foreach ($notices as $notice)
                {
                    if ($notice['wds_status'] == self::DISTACHED)
                    {
                        $lookForNonDispatched = true;
                    }

                    if ($notice['wds_status'] == self::NONDISPATCHED && $lookForNonDispatched === true)
                    {
                        $noticeIDsToUpdate[] = $notice['notice_id'];
                    }

                    if ($n % 50 === 0)
                    {
                        printf("%0.2f %%\n", ((float)$n/$totalCount)*100.0);
                    }

                    $n++;
                }
            }
        }

        if (count($noticeIDsToUpdate) > 0)
        {
            // Update all the needed notices
            $command->setText('UPDATE res_notice SET wds_status = ' . self::DEMOBILIZED . ' WHERE notice_id IN (' . join(',', $noticeIDsToUpdate) . ')')->execute();

            printf("%d notices updated!\n", count($noticeIDsToUpdate));
        }
        else
        {
            printf("There were no notices to update!\n");
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }
}