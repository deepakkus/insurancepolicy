<?php

/**
 * Helper functions.
 *
 * @author adam
 */
class Helper
{
    const MAPBOX_ACCESS_TOKEN = 'pk.eyJ1Ijoid2RzcmVzcG9uc2UiLCJhIjoiWnM1QzgzcyJ9.mJDR64crtxwZKFr9Kck0BQ';

    static function camel_to_underscore($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    static function camel_to_words($str, $capitalize)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return " " . strtolower($c[1]);');
        return $capitalize ? ucwords(preg_replace_callback('/([A-Z])/', $func, $str)) : preg_replace_callback('/([A-Z])/', $func, $str);
    }

    static function pretty_var_dump($str)
    {
        echo "<pre>";
        var_dump($str);
        echo "</pre>";
    }

    static function copy_dir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    Helper::copy_dir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Helper to set a checkbox to checked if the field exists in the given columns.
     */
    static function checkIfInArray($columns, $field)
    {
        if (in_array($field, $columns))
        {
            echo 'checked="checked"';
        }
    }

    /**
     * Helper to format phone numbers. Returns 'N/A' if empty.
     * @param string $phoneNumber
     * @return string formatted phone number
     */
    static function formatPhone($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);

        if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', $phoneNumber, $matches))
        {
            $result = '(' . $matches[1] . ') ' .$matches[2] . '-' . $matches[3];
        }
        else if ($phoneNumber == '0' || empty($phoneNumber))
        {
            $result = 'N/A';
        }
        else
        {
            $result = $phoneNumber;
        }

        return $result;
    }

    /**
     * Helper for formatting a text field. Returns 'N/A' if empty.
     * @param string $text
     * @return string formatted text
     */
    static function formatText($text)
    {
        $text = trim($text);

        if (empty($text))
        {
            $result = 'N/A';
        }
        else
        {
            $result = $text;
        }

        return $result;
    }

    /**
     * Gets a boolean integer value from a string yes/no value.
     */
    static function getBooleanIntFromString($text)
    {
        if (strcasecmp($text, 'yes') == 0)
            return 1;
        else if (strcasecmp($text, 'no') == 0)
            return 0;
        return NULL;
    }

    /**
     * Gets a boolean text value from an integer value.
     */
    static function getBooleanStringFromInt($text)
    {
        if (!is_null($text))
        {
            if ($text == 1)
                return 'Yes';
            else if ($text == 0)
                return 'No';
            return '';
        }
        return '';
    }

    /**
     * returns an array of states with the name as the key
     */
    public static $statesToAbbrDict = array(
        'ALABAMA'=>'AL',
        'ALASKA'=>'AK',
        'ARIZONA'=>'AZ',
        'ARKANSAS'=>'AR',
        'CALIFORNIA'=>'CA',
        'COLORADO'=>'CO',
        'CONNECTICUT'=>'CT',
        'DELAWARE'=>'DE',
        'FLORIDA'=>'FL',
        'GEORGIA'=>'GA',
        'HAWAII'=>'HI',
        'IDAHO'=>'ID',
        'ILLINOIS'=>'IL',
        'INDIANA'=>'IN',
        'IOWA'=>'IA',
        'KANSAS'=>'KS',
        'KENTUCKY'=>'KY',
        'LOUISIANA'=>'LA',
        'MAINE'=>'ME',
        'MARYLAND'=>'MD',
        'MASSACHUSETTS'=>'MA',
        'MICHIGAN'=>'MI',
        'MINNESOTA'=>'MN',
        'MISSISSIPPI'=>'MS',
        'MISSOURI'=>'MO',
        'MONTANA'=>'MT',
        'NEBRASKA'=>'NE',
        'NEVADA'=>'NV',
        'NEW HAMPSHIRE'=>'NH',
        'NEW JERSEY'=>'NJ',
        'NEW MEXICO'=>'NM',
        'NEW YORK'=>'NY',
        'NORTH CAROLINA'=>'NC',
        'NORTH DAKOTA'=>'ND',
        'OHIO'=>'OH',
        'OKLAHOMA'=>'OK',
        'OREGON'=>'OR',
        'PENNSYLVANIA'=>'PA',
        'RHODE ISLAND'=>'RI',
        'SOUTH CAROLINA'=>'SC',
        'SOUTH DAKOTA'=>'SD',
        'TENNESSEE'=>'TN',
        'TEXAS'=>'TX',
        'UTAH'=>'UT',
        'VERMONT'=>'VT',
        'VIRGINIA'=>'VA',
        'WASHINGTON'=>'WA',
        'WEST VIRGINIA'=>'WV',
        'WISCONSIN'=>'WI',
        'WYOMING'=>'WY',
        //stupid chubb abbreviations
        'ARIZ'=>'AZ',
        'CAL'=>'CA',
        'COLO'=>'CO',
        'IDA'=>'ID',
        'MONT'=>'MT',
        'N D'=>'ND',
        'NEV'=>'NV',
        'N M'=>'NM',
        'ORE'=>'OR',
        'S D'=>'SD',
        'UTAH'=>'UT',
        'TEX'=>'TX',
        'WASH'=>'WA',
        'WYO'=>'WY',
        'FLA'=>'FL',
        'S C'=>'SC',
        'TENN'=>'TN',
        'GA'=>'GA',
        'OKLA' => 'OK'
    );

    /**
     * Converts full states (or crazy chubb abbreviations) to standard 2 letter ones
     */
    public static function convertStateToAbbr($state)
    {
        if(array_key_exists($state, self::$statesToAbbrDict))
            return self::$statesToAbbrDict[$state];
        else
            return false;
    }

    public static function addWeekDays($date_str, $numDays)
    {
        $date = new DateTime($date_str);
        $numDays = (int)($numDays); // ensure integer!
        $interval = new DateInterval('P1D'); // 1 day
        while ($numDays != 0)
        {
            if ($numDays > 0)
                $date->add($interval);
            else
                $date->sub($interval);
            $dayOfWeek = $date->format('N'); // 1 (Monday) .. 7 (Sunday)
            if (($dayOfWeek >= 1) && ($dayOfWeek <= 5))
            {
                if ($numDays > 0)
                    $numDays--;
                else
                    $numDays++;
            }
        }
        return $date->format('Y-m-d H:i:s');
    }

    public static function getStates()
    {
        $states = array('AK','AL','AR','AZ','CA','CO','CT','DC','DE','FL','GA','HI','IA','ID','IL','IN','KS','KY','LA','MA','MD','ME','MI','MN',
                    'MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VA','VT','WA',
                    'WI','WV','WY');
        return array_combine($states, $states);
    }

    /**
     * Takes a given number of meters and returns the number of miles
     * @param double $meters
     * @param boolean $format
     * @return double|int
     */
    public static function metersToMiles($meters, $format = false)
    {
        if ($format)
            return round(0.000621371 * $meters, 2);

        return 0.000621371 * $meters;
    }

    //Takes a given number of miles and returns the number of meters - commonly used for buffering
    public static function milesToMeters($miles)
    {
        return 1609.34 * $miles;
    }

    /**
     * Returns the number of unmatched for a client and the given zip codes
     * @param string[] $zipcodeNumbers
     * @param integer $clientID
     * @return array
     */
    public static function getUnmatchedForZipCodes($zipcodeNumbers, $clientID)
    {
        $sql = "
        SELECT
            COUNT(response_status) count,
            response_status
        FROM properties
        WHERE wds_geocode_level = 'unmatched'
            AND client_id = :client_id
            AND policy_status = 'active'
            AND type_id = 1
            AND zip IN ('" . implode("','", $zipcodeNumbers) . "')
        GROUP BY response_status
        ORDER BY response_status ASC";

        return Yii::app()->db->createCommand($sql)
            ->bindValue(':client_id', $clientID, PDO::PARAM_INT)
            ->queryAll();
    }

    /**
     * Sends an email to specified email address
     *
     * Using the outgoing SMTP credentials from the main config this function
     * sends an email to the specified email address with the specified
     * subject, body and optional attachments.
     *
     * @param string $subject email subject line
     * @param string $body email body content
     * @param string $email email address to send to
     * @param array $attachments optional array of 'name'=>'path' key values to attach to the email
     *
     * @return bool result of email send
     */
    public static function sendEmail($subject, $body, $to, $attachments=null)
    {
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
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AddAddress($to);
        if(isset($attachments) && is_array($attachments))
        {
            foreach($attachments as $name => $path)
            {
                $attachmentString = file_get_contents($path);
                $mail->AddStringAttachment($attachmentString, $name);
            }
        }

        $sendResult = $mail->Send();
        if($sendResult)
            return true;
        else
        {
            return false;
            //for debugging
            //return 'ERROR SENDING EMAIL! details: '.$mail->ErrorInfo;
        }
    }

    public static function getFireClients()
    {
        return CHtml::listData(Client::model()->findAll(array(
            'condition' => 'id != 999 AND wds_fire = 1 AND active = 1',
            'order' => 'name ASC'
        )), 'id', 'name');
    }

    public static function getDispatchedFires()
    {
        $date = date('Y');
        $sql = '
            select f.name, f.fire_id from res_notice n
            inner join res_fire_name f on f.fire_id = n.fire_id
            inner join (
	            select max(notice_id) as notice_id, client_id, fire_id from res_notice where wds_status = 1 and date_created >= :date
	            group by client_id, fire_id
            ) d on d.notice_id = n.notice_id
            and n.wds_status = 1
            group by f.fire_id, f.name
            order by f.name asc;
        ';

        $fires = Yii::app()->db->createCommand($sql)->bindValue(':date', $date, PDO::PARAM_STR)->queryAll();
        return CHtml::listData($fires, 'fire_id', 'name');
    }

    /**
     * Returns the check box or x entitiy symbol depending on true/false value
     * @param integer $item
     * @param boolean $style
     * @return string
     */
    public static function getCheckMark($item, $style = false)
    {
        if ($style)
            return ($item == 1) ? "<span class = 'green span-medium'>&#10004;</span>" : "<span class = 'grey span-medium'>&#215;</span>";

        return ($item == 1) ? "&#10004;" : "&#215;";
    }

    /**
     * Returns a connection string to the network datastore drive.  If the necessary app parameters
     * are not set or the folder is not writable, the local app protected folder will be returned.
     * @return string
     */
    public static function getDataStorePath()
    {
        $dataStorePath = Yii::app()->basePath . DIRECTORY_SEPARATOR;

        if (Yii::app()->params['env'] !== 'local')
        {
            if (isset(Yii::app()->params['dataStorePath'], Yii::app()->params['dataStorePW'], Yii::app()->params['dataStoreUser'], Yii::app()->params['dataStoreMapDriveLetter']))
            {
                $mapDriveLetter = Yii::app()->params['dataStoreMapDriveLetter'];

                $dataStore = Yii::app()->params['dataStorePath'];
                $dataStorePassword = Yii::app()->params['dataStorePW'];
                $dataStoreUser = Yii::app()->params['dataStoreUser'];

                //system("net use {$mapDriveLetter}: /delete");
                system("net use {$mapDriveLetter}: \"{$dataStore}\" /user:{$dataStoreUser} {$dataStorePassword} /persistent:no>nul 2>&1");

                $dataStorePath = $mapDriveLetter . ':' . DIRECTORY_SEPARATOR;
            }
        }

        return $dataStorePath;
    }

    /**
     * Converts hex string to array of rgb integer values.
     * Ex: list($red, $green, $blue) = Helper::hexToRgb($hexcolor);
     * @param string $hex
     * @return array
     */
    public static function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3)
        {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        }
        else
        {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }

    /*
     *   Description: parses apart a zip code that may have the 4 digit extension
     *   $zipcode string - could be a 5 digit or might have the -xxxx extension
     *   $pieces boolean - return all the pieces or just the first 5
     */
    public static function splitZipCode($zipcode, $pieces = false)
    {
        $parts = explode("-", $zipcode);

        return ($pieces) ? $parts : $parts[0];

    }

    /**
     * Save property contact
     * @param int $property_pid
     * @param string $type
     * @param string $priority
     * @param string $name
     * @param string $relationship
     * @param string $detail
     * @param string $notes
     * @return string $priority
     */
    public static function savePropContact($property_pid, $type, $priority, $name, $relationship, $detail, $notes)
    {
        // Find contact
        $contact = Contact::model()->findByAttributes(array(
            'property_pid' => $property_pid,
            'priority' => $priority
        ));

        if (!$contact)
        {
            // If contact doesn't already exists, create new one
            $contact = new Contact;
        }
        else
        {
            if ($contact->property_pid == $property_pid &&
                $contact->type == $type &&
                $contact->priority == $priority &&
                $contact->name == $name &&
                $contact->relationship == $relationship &&
                $contact->detail == $detail &&
                $contact->notes == $notes)
            {
                // If contact remains the same, exit now
                return true;
            }
        }

        $contact->property_pid = $property_pid;
        $contact->type = $type;
        $contact->priority = $priority;
        $contact->name = $name;
        $contact->relationship = $relationship;
        $contact->detail = $detail;
        $contact->notes = $notes;

        if (!$contact->save())
        {
            return false;
        }

        return true;
    }

    /**
     * Replaces problem causing character encodings from MS Word pasted text
     *
     * @param string $wordString
     * @return string $sanatizedString
     */
    public static function sanatizeWordString($wordString)
    {
        $search = array(chr(145),
                     chr(146),
                     chr(147),
                     chr(148),
                     chr(151));

        $replace = array("'",
                         "'",
                         '"',
                         '"',
                         '-');


        return str_replace($search, $replace, $wordString);
    }
}