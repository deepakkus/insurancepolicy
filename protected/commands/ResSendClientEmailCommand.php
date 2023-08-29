<?php
class ResSendClientEmailCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $time_start = microtime(true);

        $type = isset($args[0]) ? $args[0] : null;
        $id = isset($args[1]) ? $args[1] : null;

        if ($type == "noteworthy")
        {
            $this->noteworthyEmail($id);
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Get the contents for the noteworthy email
     * @param monitor_id
     */

    private function noteworthyEmail($monitor_id)
    {
        //Get the fires/clients
        $sql = "
        select
	        f.name,
	        f.city,
	        f.state,
	        o.size,
	        o.containment,
	        t.client_id
        from
	        res_monitor_triggered t
        inner join
	        res_monitor_log l on l.monitor_id = t.monitor_id
        inner join
	        res_fire_obs o on o.obs_id = l.obs_id
        inner join
	        res_fire_name f on f.fire_id = o.fire_id
        where
	        (enrolled > 0 or eligible > 0) and t.monitor_id = :monitor_id ";

        //Get clients impacted by fire
        $clients = Yii::app()->db->createCommand($sql)
            ->bindParam(':monitor_id', $monitor_id, PDO::PARAM_INT)
            ->queryAll();

        if($clients){

            //Get some of the variables used in the body and subject
            $fireName = $clients[0]['name'];
            $location = $clients[0]['city'] . ", " . $clients[0]['state'];
            $fireSize = $clients[0]['size'];
            $dashboardURL = Yii::app()->params['wdsfireBaseUrl'];

            //Subject
            $subject = "New Wildfire near $location";
            //Email body
            $body = "<p>A new wildfire has been found which is within 3 miles of one or more of your properties. Depending on the circumstances, this could be followed by another alert for a program fire (dispatched or not-dispatched)</p>";
            $body .= "<ul>";
            $body .= "<li><strong>Fire Name:</strong> $fireName</li>";
            $body .= "<li><strong>Location:</strong> $location</li>";
            $body .= "<li><strong>Fire Size:</strong> $fireSize</li>";
            $body .= "</ul>";
            $body .= "<p>For more information on this fire, please visit <a href = '$dashboardURL'>$dashboardURL</a> </p>";

            $body .= "<p><font color = '#777777'>CONFIDENTIALITY NOTE : The information in this e-mail is confidential and privileged; it is intended for use solely by the individual or entity named as the recipient hereof. Disclosure, copying, distribution, or use of the contents of this e-mail by persons other than the intended recipient is strictly prohibited and may violate applicable laws. If you have received this e-mail in error, please delete the original message and notify us by return email or phone call immediately. </font></p>";

            foreach($clients as $client){

                $clientID = $client['client_id'];

                //Get the users
                $sql = "
            select
	            email
            from
	            [user]
            where
	            client_id = :client_id
                and type like '%Dash Email Group Noteworthy%'
                and active = 1";

                $users = Yii::app()->db->createCommand($sql)
                    ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
                    ->queryAll();

                $emails = array_map(function($data){ return $data['email']; }, $users);

                if($users){
                    $this->sendEmail($emails, $subject, $body);
                }

            }

        }


    }

    private function sendEmail($bcc, $subject, $body){

        //Initialize emailer
        Yii::import('application.extensions.phpmailer.PHPMailer');
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->Host = Yii::app()->params['emailHost'];
        $mail->SMTPAutoTLS = false;
        $mail->SMTPOptions = Yii::app()->params['emailSMTPOptions'];
        $mail->Username = Yii::app()->params['emailUser'];
        $mail->Password = Yii::app()->params['emailPass'];
        $mail->SetFrom(Yii::app()->params['emailUser'], 'Wildfire Defense Systems');
        foreach($bcc as $address){
            $mail->AddBCC($address);
        }
        $mail->Subject = $subject;
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
        $mail->MsgHTML($body);

        //Check for problems with sending email
        try
        {
            $success = $mail->Send();
        }
        catch (Exception $ex)
        {
            //Don't want to display any errors for client, just prevent it from crashing
            $success = false;
        }

        return $success;
    }


}