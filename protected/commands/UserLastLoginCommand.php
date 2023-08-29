<?php

class UserLastLoginCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true);

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $dbCommand = Yii::app()->db->createCommand();

        // Warn users who's last login was between 6 months and 6 months and 1 week ago that they will no longer recieve notice emails

        $results = $dbCommand->setText('SELECT name, username, email
            FROM [user]
            WHERE active = 1
	            AND ISNUMERIC(client_id) = 1
	            AND wds_staff IS NULL
	            AND CONVERT(DATE, last_login) >= CONVERT(DATE, DATEADD(WEEK, -1, DATEADD(MONTH, -6, GETDATE())))
	            AND CONVERT(DATE, last_login) < CONVERT(DATE, DATEADD(MONTH, -6, GETDATE()))')->queryAll();

        if ($results)
        {
            Yii::import('application.extensions.phpmailer.JPhpMailer');

            Yii::app()->urlManager->showScriptName = true;
            Yii::app()->urlManager->urlFormat = 'path';

            Yii::app()->request->hostInfo = Yii::app()->params['wdsfireBaseUrl'];
            Yii::app()->request->baseUrl = Yii::app()->params['wdsfireBaseUrl'];
            Yii::app()->request->scriptUrl = 'index.php';

            $resetPassUrl = Yii::app()->createAbsoluteUrl('site/forgot-password');

            $isDev = Yii::app()->params['env'] !== 'pro';

            foreach ($results as $result)
            {
                $body = '<img src="http://www.wildfire-defense.com/images/wds-header.jpg" alt="Wildfire Defense Systems" /><br>';
                $body .= '<p>Dear ' . $result['name'] . ',</p>';
                $body .= '<p>You are receiving this email because you have not accessed the WDSfire dashboard in excess of 10 months.  ';
                $body .= 'If you want to receive fire notices, please go to <a href="' . $resetPassUrl . '">WDSfire</a>, reset your password, and log into the WDSfire website.</p>';
                $body .= '<p>For support with this request, please contact ops@wildfire-defense.com.</p>';
                $body .= '<p>Thank you</p>';
                $body .= '<p>WDS</p>';

                $mail = new JPhpMailer;
                $mail->IsSMTP();
                $mail->SMTPDebug = false;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'ssl';
                $mail->Host = Yii::app()->params['emailHost'];
                $mail->Port = Yii::app()->params['emailPort'];
                $mail->Username = Yii::app()->params['emailUser'];
                $mail->Password = Yii::app()->params['emailPass'];
                $mail->SetFrom(Yii::app()->params['emailUser'], 'Wildfire Defense Systems');
                $mail->AddAddress($result['email']);
                $mail->Subject = $isDev ? 'TESTING - WDS User Deactivation Alert' : 'WDS User Last Login Alert';
                $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
                $mail->MsgHTML($body);

                try
                {
                    $sent = $mail->Send();

                    if ($sent)
                    {
                        print 'Email sent to: ' . $result['name'] . PHP_EOL;
                    }
                    else
                    {
                        print 'Failed to send email to: ' . $result['name'] . PHP_EOL;
                    }
                }
                catch (Exception $ex)
                {
                    print 'There was a error sending this email: ' . $ex->getMessage() . PHP_EOL;
                }
            }
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }
}