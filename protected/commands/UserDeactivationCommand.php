<?php

/**
 * This class contains two methods that deactivates users based on how many months their passwords
 * have been expired
 * 
 * WDS Staff - deactivated 3 months after their password expires
 * Dashboard Users - deactivated 8 months after their password expires
 */
class UserDeactivationCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
            
        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $dbCommand = Yii::app()->db->createCommand();

        // Deactivate all Admin users who's passwords have expired 3 months

        $dbCommand->setText('UPDATE [user] SET active = 0 WHERE active = 1 AND wds_staff = 1 AND pw_exp < DATEADD(MONTH, -3, GETDATE())');
        $dbCommand->execute();

        // Deactivate all Dashboard users who's passwords have expired 3 months

        $dbCommand->setText('UPDATE [user] SET active = 0 WHERE active = 1 AND ISNUMERIC(client_id) = 1 AND wds_staff IS NULL AND pw_exp < DATEADD(MONTH, -3, GETDATE())');
        $dbCommand->execute();

        // Warn users who's passwords expired for over 2 months, 3 weeks of implending deactivation

        $results = $dbCommand->setText('SELECT name, username, email
            FROM [user]
            WHERE active = 1
	            AND ISNUMERIC(client_id) = 1
	            AND wds_staff IS NULL
	            AND pw_exp > DATEADD(MONTH, -3, GETDATE())
	            AND pw_exp < DATEADD(MONTH, -3, DATEADD(WEEK, 1, GETDATE()))')->queryAll();
        
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
                $body .= '<p>Your user, <strong>' . $result['name'] . ' (' . $result['username'] . ')</strong>, has not logged into the WDS<i>fire</i> dashboard for more than <strong>80 days</strong>. In <strong>10 days</strong>, your account will be deactivated.</p>';
                $body .= '<p>Please log into <a href="' . Yii::app()->params['wdsfireBaseUrl'] . '">WDSfire</a> to prevent this from happening.  When logging in, you will be prompted for a password change.  ';
                $body .= 'If you can\'t remember your old password, please click on "<a href="' . $resetPassUrl . '">Forgot Password</a>" and complete the reset password procedures.</p>';

                $mail = new JPhpMailer;
                $mail->IsSMTP();
                $mail->SMTPDebug = 0;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'ssl';
                $mail->Host = Yii::app()->params['emailHost'];
                $mail->Port = Yii::app()->params['emailPort'];
                $mail->Username = Yii::app()->params['emailUser'];
                $mail->Password = Yii::app()->params['emailPass'];
                $mail->SetFrom(Yii::app()->params['emailUser'], 'Wildfire Defense Systems');
                $mail->AddAddress($result['email']);
                $mail->Subject = $isDev ? 'TESTING - WDS User Deactivation Alert' : 'WDS User Deactivation Alert';
                $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
                $mail->MsgHTML($body);

                try
                {
                    $sent = $mail->Send();

                    if ($sent)
                        print 'Email sent to: ' . $result['name'] . PHP_EOL;
                    else
                        print 'Failed to send email to: ' . $result['name'] . PHP_EOL;
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