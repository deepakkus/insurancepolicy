<?php

/**
 * Send a reminder email to an alliance partner to remind them to log into the engine
 * website and update their engines.
 */
class AllianceReminderEmailCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;
        
        // Get all alliance parters who are flagged to get emails and associated engines who are outdated.

        $allianceModels = Alliance::model()->findAll(array(
            'select' => array('id','name','email'),
            'condition' => 'email_reminder = 1',
            'with' => array(
                'engines' => array(
                    'condition' => 'date_updated < DATEADD(week,-1,GETDATE())'
                )
            )
        ));

        foreach ($allianceModels as $alliance)
        {
            // If there are related engines that are outdated, send a reminder email
            if ($alliance->engines)
            {
                $success = $this->sendAllianceReminderEmailWithModel($alliance);
                if ($success)
                    print 'Email ' . $alliance->email . ' was successfully sent!' . PHP_EOL;
            }
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    private function sendAllianceReminderEmailWithModel($alliance)
    {
        //Create Text for email
        $body = '<img src ="http://www.wildfire-defense.com/images/wds-header.jpg" alt="Wildfire Defense Systems" /><br />';
        $body .= "$alliance->name,<br /><br />";
        $body .= 'Please remember to log into WDS Engines at <a href="https://engines.wildfire-defense.com/" target="_blank">https://engines.wildfire-defense.com/</a> to update your engines!<br /><br />';
        $body .= 'Engines that need to be updated:<br />';
        $body .= '<ul>';
        foreach ($alliance->engines as $engine)
            $body .= '<li>' . $engine->engine_name . '</li>';
        $body .= '</ul>';
        $body .= '<font color="#777777">CONFIDENTIALITY NOTE : The information in this e-mail is confidential and privileged; it is intended for use solely by the individual or entity named as the recipient hereof. Disclosure, copying, distribution, or use of the contents of this e-mail by persons other than the intended recipient is strictly prohibited and may violate applicable laws. If you have received this e-mail in error, please delete the original message and notify us by return email or phone call immediately. </font>';

        Yii::import('application.extensions.phpmailer.JPhpMailer');
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
        $mail->Subject = 'WDS Engines Checkin Reminder';
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
        $mail->MsgHTML($body);
        $mail->AddAddress($alliance->email);

        if ($mail->Send())
        {
            // Update eng_engines 'date_email' date

            Yii::app()->db->createCommand('UPDATE eng_engines SET date_email = :date WHERE alliance_id = :alliance_id AND date_updated < DATEADD(week,-1,GETDATE());')->execute(array(
                ':date' => date('Y-m-d H:i'),
                'alliance_id' => $alliance->id
            ));

            return true;
        }
            
        return false;
    }
}