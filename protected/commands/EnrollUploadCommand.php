<?php

/**
 * Used to select enrolled members and upload the list to USAA so they can keep their system up to date on response enrollments
 */
class EnrollUploadCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
        //Let run 15 minutes
        set_time_limit(60 * 15);
		print "Starting Enrollment Upload!\n";

		//create upload file
		$file_name = date('Y_m_d_H_i_s').".txt";
		$file_path = (Yii::app()->params['env'] == 'pro') ? Helper::getDataStorePath() . "usaa_enrollment_exports".DIRECTORY_SEPARATOR.$file_name : 'C:\TempPath'.DIRECTORY_SEPARATOR.$file_name;
		$fh = fopen($file_path, 'w');

        //----- Get all enrolled properties ------
        // - Original enrolled properties lookup...Eats up too much memory with over 100K records --
		//$properties = Property::model()->with('member')->findAll("response_status='enrolled' AND policy_status='active' AND member.client = 'USAA'");
		$propsCommand = Yii::app()->db->createCommand()
			->select('m.member_num, p.policy, p.response_enrolled_date, p.state')
			->from('properties p')
			->join('members m', 'p.member_mid = m.mid')
			->where("p.response_status='enrolled' AND p.policy_status='active' AND m.client = 'USAA'");

        $propsDataReader = $propsCommand->query();
        while(($property = $propsDataReader->read()) !== false)
		{
			//add leading 0s to member number until it is 9 chars
			$mem_num = $property['member_num'];
			while(strlen($mem_num) < 9)
				$mem_num = '0'.$mem_num;

			//date setup
			$enrollment_date = strtotime($property['response_enrolled_date']);
			//effective date is 4 days after enrollment, and if before june 1 2013, then is june 1 2013
			$effective_date = strtotime($property['response_enrolled_date'].' + 4 days');
			if($effective_date < strtotime('2013-06-01'))
				$effective_date = strtotime('2013-06-01');

			//write line to file for this member, fields are space seperated
			$line = $mem_num.' '.$property['policy'].' '.date('m/d/Y', $enrollment_date).' '.date('m/d/Y', $effective_date).' '.$property['state'].PHP_EOL;
			fwrite($fh, $line);
		}
		//close enrollment upload file
		fclose($fh);

        //Email WDS
        if($this->uploadFile($file_name, $file_path) == false)
        {
            Helper::sendEmail(
                (Yii::app()->params['env'] == 'pro') ? "USAA Enroll Upload Failed" : "!!TEST - USAA Enroll Upload Failed - TEST!!" ,
                "USAA Enroll Upload Failed",
                "software@wildfire-defense.com"
            );
        }

        print "\nDone With Enrollment Upload!";

	}

    /** A function that recursively tries to upload the usaa file. It will try 5 times before failing
    *   @param $fileName - the name of the file
    *   @param $filePath - the path of the file
    *   @param $attempts - keeps track of the number of tries
    *   @return true/false
    */
    private static function uploadFile($fileName, $filePath, $attempts = 0)
    {
        //upload to b2b
        $result = '';

        $ch = curl_init();
        if(!$ch)
        {
            $error = curl_error($ch);
            die("cURL session could not be initiated.  ERROR: $error.");
        }

        $fh = fopen($filePath, 'r');
        if(!$fh)
        {
            $error = curl_error($ch);
            die("$filePath could not be read.");
        }

        if (Yii::app()->params['env'] == 'dev' || Yii::app()->params['env'] == 'local')
        {
            // Some sort of FTP server will need to be used for further testing
            $username = '';
            $password  = '';
            curl_setopt($ch, CURLOPT_URL, "sftp://$username:$password@wildfire-defense.com:22/$fileName");
        }
        elseif (Yii::app()->params['env'] == 'pro' && isset(Yii::app()->params['usaaB2BUsername'], Yii::app()->params['usaaB2BPassword']))
        {
            $username = Yii::app()->params['usaaB2BUsername'];
            $password = Yii::app()->params['usaaB2BPassword'];

            curl_setopt($ch, CURLOPT_URL, "sftp://$username:$password@b2bfg.usaa.com:8022/ReceiveEnrolledMembers/$fileName");
        }
        else
        {
            die('Yii config params "usaaB2BUsername" and "usaaB2BPassword" do not exist!"');
        }

        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));

        //Error with upload, so try again
        if(!curl_exec($ch) && $attempts < 5)
        {
            //Show error for people running from console
            var_dump(curl_error ($ch)); 
            //Pause 10 seconds before trying again
            sleep(10);
            //Try again recursively
            $result = self::uploadFile($fileName, $filePath, $attempts + 1);
        }
        //Reached 5 attempts, so exit
        elseif ($attempts >= 5)
        {
            $result = false;
        }
        //Worked!
        else
        {
            $result = true;
        }

        fclose($fh);
        curl_close($ch);

        return $result;
    }
}
?>
