<?php
class ResEnrollEmailCommand extends CConsoleCommand
{
	public function run($args)
    {   
        print '-----STARTING COMMAND--------' . PHP_EOL;

        $time_start = microtime(true);

        $pid = isset($args[0]) ? $args[0] : null;
        $fireID = isset($args[1]) ? $args[1] : null;

        /*commented to disable the mail being sent to engine Bosses.
        if ($pid && $fireID)
        {

            //$this->responseEnrollmentEmail($pid, $fireID);
        }
        else */

        // in all cases Enroment mails would be sent
        if ($pid)
        {
            $this->enrollmentEmail($pid);
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Send an enrollment email, used for USAA enrollment website
     * @param integer $pid
     */
    private function enrollmentEmail($pid)
    {
        $property = Property::model()->find(array(
            'select' => 'pid, address_line_1, city, state, zip',
            'with' => array(
                'client' => array(
                    'select' => 'id, name'
                )
            ),
            'condition' => 'pid = :pid',
            'params' => array(':pid' => $pid)
        ));
        // Find client than make selection from triggered table and make sure fire isn't 100% contained
        
        $clientName = $property->client->name;
        $address = sprintf('%s %s, %s %s', $property->address_line_1, $property->city, $property->state ,$property->zip);
        
        // Email Subject
        $subject = (Yii::app()->params['env'] == 'pro') ? "New Enrollment for $clientName" : "!!! TEST !!!! New Enrollment for $clientName";

        // Build body of notice...information about the address and the fire
        $body = "<img src = 'http://www.wildfire-defense.com/images/wds-header.jpg' alt = 'Wildfire Defense Systems' /><br>";
        $body .= "<p>$clientName has received a new enrollment:</p>";
        $body .= "<ul>";
        $body .= "<li><strong>Address:</strong> $address</li>";
        $body .= "</ul>";

        // Initialize emailer
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
        if (Yii::app()->params['env'] == 'pro')
        {
            $mail->AddAddress('ops@wildfire-defense.com');
        }
        else
        {
            $mail->AddAddress('test@wildfire-defense.com');
        }
        $mail->Subject = $subject;
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
        $mail->MsgHTML($body);

        // Check for problems with sending email
        try
        {
            $mail->Send();
        }
        catch (Exception $ex)
        {
            // Don't want to display any errors for client, just prevent it from crashing
        }
    }

    /**
     * Summary of enrollmentEmail
     * @param integer $pid
     * @param integer $fireID
     */
    private function responseEnrollmentEmail($pid, $fireID)
    {
        $property = Property::model()->find(array(
            'select' => 'pid, address_line_1, city, state, zip',
            'with' => array(
                'client' => array(
                    'select' => 'id, name'
                )
            ),
            'condition' => 'pid = :pid',
            'params' => array(':pid' => $pid)
        ));

        $fire = ResFireName::model()->find(array(
            'select' => 'Fire_ID, Name, City, State',
            'condition' => 'Fire_ID = :FireID',
            'params' => array(':FireID' => $fireID)
        ));

        $clientName = $property->client->name;
        $address = sprintf('%s %s, %s %s', $property->address_line_1, $property->city, $property->state ,$property->zip);
        $fireName = $fire->Name;
        $fireLocation = sprintf('%s, %s', $fire->City, $fire->State);
        //Call Engine Boss Email
        $this->engineBossEmail($pid, $clientName, $address, $fireID);
        // Email Subject
        $subject = (Yii::app()->params['env'] == 'pro') ? "New Enrollment for $clientName on the $fireName" : "!!! TEST !!!! New Enrollment for $clientName on the $fireName";

        // Build body of notice...information about the address and the fire
        $body = "<img src = 'http://www.wildfire-defense.com/images/wds-header.jpg' alt = 'Wildfire Defense Systems' /><br>";
        $body .= "<p>$clientName has received a new enrollment:</p>";
        $body .= "<ul>";
        $body .= "<li><strong>Address:</strong> $address</li>";
        $body.= "<li><strong>Fire Name:</strong> " . $fireName . "</li>";
        $body .= "<li><strong>Fire Location:</strong> " . $fireLocation . "</li>";
        $body .= "</ul>";

        //Initialize emailer
        // Initialize emailer
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
        if (Yii::app()->params['env'] === 'dev' || Yii::app()->params['env'] === 'local')
        {
            $mail->AddAddress('ops@wildfire-defense.com');
        }
        else
        {
            $mail->AddAddress('ops@wildfire-defense.com');
        }
        $mail->Subject = $subject;
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
        $mail->MsgHTML($body);

        // Check for problems with sending email
        try
        {
            $mail->Send();
        }
        catch (Exception $ex)
        {
            // Don't want to display any errors for client, just prevent it from crashing
        }
    }
    /*Engine Boss email
     * @param integer $pid
     * @param integer $fireID
     * @param string $clientName
     * @param string $address
     */
    public function engineBossEmail($pid, $clientName, $address, $fireID)
    {
         $engineSql = "SELECT c.first_name, c.last_name,c.email,f.City,f.State,f.Name, e.* FROM eng_scheduling e
                        INNER JOIN eng_scheduling_employee p ON p.engine_scheduling_id = e.id
                        INNER JOIN eng_crew_management c ON c.id = p.crew_id
                        INNER JOIN res_fire_name f ON f.Fire_ID = e.fire_id
                        WHERE p.scheduled_type = 'ENGB' AND c.crew_type = 'ENGB' 
                        AND (GETDATE() BETWEEN p.start_date AND p.end_date) 
                        AND e.fire_id = :fid";
         $engineData = Yii::app()->db->createCommand($engineSql)
                        ->bindParam(':fid', $fireID, PDO::PARAM_INT)
                        ->queryAll(); 
       
        foreach($engineData as $engine)
        {
            if($engine['email'] != '')
            {
                // Email Subject
                $subject = "Updated Response Status for property ($pid)";

                // Build body of notice...information about the address and the fire
                $body = "<img src = 'http://www.wildfire-defense.com/images/wds-header.jpg' alt = 'Wildfire Defense Systems' /><br>";
                $body .= "<p>Hello ".$engine['first_name']." ".$engine['last_name'].",</p></br>";
                $body .= "<p>Below property status has been changed to enrolled</p></br>";
                $body .= "<p><b>Property Id : $pid</b></p>";
                $body .= "<p><b>Address: ".$address."</b></p>";
                $body .= "<p><b>Fire Name: ".$engine['Name']."</b></p>";
                $body .= "<p><b>Fire Location: ".$engine['City'].", ".$engine['State']."</b></p>";
      
                // Initialize emailer
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
                $mail->AddAddress($engine['email']);
                $mail->Subject = $subject;
                $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
                $mail->MsgHTML($body);

                // Check for problems with sending email
                try
                {
                    $mail->Send();
                }
                catch (Exception $ex)
                {
                    // Don't want to display any errors for client, just prevent it from crashing
                }
            }
        }
    }
}