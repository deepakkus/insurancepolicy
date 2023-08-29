<?php
/**
 * This is the model class for table "fs_user"
 *
 * The followings are the available columns in table 'fs_condition':
 * @property integer $id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $password
 * @property string $salt
 * @property integer $member_mid
 * @property string $login_token
 * @property string $vendor_id
 * @property integer $agent_id
 * @property datetime $user_created_date
 * @property string $platform
 * @property string $reset_token
 */
class FSUser extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return fs the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'fs_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, first_name, last_name, password', 'required'),
			array('email', 'length', 'max'=>100),
            array('email', 'email'),
            array('first_name, last_name, password, salt, login_token, vendor_id, reset_token', 'length', 'max'=>50),
            array('member_mid, agent_id', 'numerical', 'integerOnly' => true),
            array('platform', 'length', 'max'=>10),
            array('user_created_date', 'length', 'max'=>30),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, email, first_name, last_name, member_mid, agent_id, user_created_date, platform', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'member' => array(self::BELONGS_TO, 'member', 'member_mid'),
			'reports' => array(self::HAS_MANY, 'FSReport', 'fs_user_id'),
            'agent' => array(self::BELONGS_TO, 'agent', 'agent_id'),
		);
	}
    
    //virtual attributes from related tables
	//public $agent_client_name;
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'email' => 'Email', 
			'first_name' => 'First Name', 
			'last_name' => 'Last Name', 
			'password' => 'Password', 
			'member_mid' => 'Member ID',
            'vendor_id' => 'Vendor ID',
            'agent_id' => 'Agent ID',
            'user_created_date' => 'User Created Date',
            'platform' => 'Platform',
            'reset_token' => 'Reset Token',
            //'agent_client_name' => 'Client',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($type = 'fs')
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
        
        //if($type == 'agent')
        //    $criteria->with = array('agent');

		$criteria->compare('id',$this->id);
		$criteria->compare('email',$this->email);
		$criteria->compare('first_name',$this->first_name);
		$criteria->compare('last_name',$this->last_name);
		$criteria->compare('member_mid',$this->member_mid);
        $criteria->compare('login_token',$this->login_token);
        $criteria->compare('vendor_id',$this->vendor_id);
        $criteria->compare('agent_id',$this->agent_id);
		$criteria->compare('user_created_date',$this->user_created_date);
        $criteria->compare('platform', $this->platform);
        $criteria->compare('reset_token', $this->reset_token);
        //if($type == 'agent')
        //    $criteria->compare('agent.client_name', $this->agent_client_name, false);
            
		if($type == 'fs')
			$criteria->addCondition ('member_mid IS NOT NULL');
		elseif($type == 'agent')
			$criteria->addCondition('agent_id IS NOT NULL');

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>50),
            //'sort'=>array(
            //    'defaultOrder'=>array('id'=>true),
            //    'attributes'=>array(
            //        'agent_client_name'=>array(
            //            'asc'=>'agent.client_name',
            //            'desc'=>'agent.client_name DESC',
            //        ),
            //    ),
            //),
		));
	}

    protected function beforeSave()
	{
		if ($this->isNewRecord)
		{	
    		$this->user_created_date = date('Y-m-d H:i:s');
        }
        
		return parent::beforeSave();
	}

    /**
     * Creates a Registration code that is unique across both 
     * Member.fs_carrier_key and Agent.fs_carrier_key
     * This is to ensure that when apiCheckCarrierKey is used
     * that it finds the right Member or Agent w/o the possibility of 
     * duplicate keys (which it would return the first one found)
     * 
     * @return string unique 8 character alpha numeric code
     */
    public static function createUniqueRegCode()
    {
        $dupe_check = true;
        while($dupe_check)
        {
            $unique_code = substr(str_shuffle("23456789ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 8);
            $member = Member::model()->findByAttributes(array('fs_carrier_key' => $unique_code));
            $agent = Agent::model()->findByAttributes(array('fs_carrier_key' => $unique_code));
            if(isset($member) || isset($agent))
                $dupe_check = true;
            else
                $dupe_check = false;
        }
        return $unique_code;
    }

    public function sendAzureNotification($message)
    {
        $connectionString = "Endpoint=sb://wdspro.servicebus.windows.net/;SharedAccessKeyName=DefaultFullSharedAccessSignature;SharedAccessKey=xbMv/mf9nE0JI853iDqW6cXm9i3FjUQo3I2CgaDeiAY=";
        $hubName = "WDSpro";
        $hub = new AzureNotificationHub($connectionString, $hubName);
        if($this->platform === 'Android')
        {
            $payload = '{"data":{"message":"'.$message.'"}}';
            $notification = new AzureNotification("gcm", $payload);
        }
        elseif($this->platform === 'iOS')
        {
            $payload = '{"aps":{"alert":"'.$message.'"}}';
            $notification = new AzureNotification("apple", $payload);
        }
        $hub->sendNotification($notification, $this->vendor_id);
    }
    
	public function sendPushNotification($alert)
	{	
		if(Yii::app()->params['env'] == 'pro')
		{
			$APPLICATION_ID = "QIoXYQ22m9eLR9UWbxi4lUO6cuBDJmZM9Je12Irw";
			$REST_API_KEY = "Y1KuOQhVkFHRfgOaeVVGbYPP1WebMa6ChCiH3zed";
		}
		else //use dev keys
		{
			$APPLICATION_ID = "37jCwtiA33xkqCjGdFCbbXLFlifvR2679LAbch7D";
			$REST_API_KEY = "eo0xSa2PjOYUXKkpeqexArmZPN5qo8dym5txoFAh";
		}

		$url = 'https://api.parse.com/1/push';
		$data = array(
			'where' => array(
				'fireShieldUser' => array(
					'$inQuery' => array(
						'where' => array(
							'vendorId'=>$this->vendor_id
						),
						'className' => '_User'
					),
				),
			),
			'data' => array(
				'alert' => $alert,
				'sound' => 'default',
				'badge' => '1',
				'title' => 'Alert From WDSpro',
			),
		);
		$_data = json_encode($data);
		//echo "data=".$_data."<br /><br />";
		$headers = array(
			'X-Parse-Application-Id: ' . $APPLICATION_ID,
			'X-Parse-REST-API-Key: ' . $REST_API_KEY,
			'Content-Type: application/json',
			'Content-Length: ' . strlen($_data),
		);

		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $_data);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($curl);
		if($result)
		{
			//echo 'Done. Result: '.$result;
			return true;
		}
		else
		{
			//echo 'Error: '.curl_error($curl);
			return false;
		}
	}
	
	public function getProperties()
	{
		$properties = array();
        $name = '';
        // Get the member properties
        if (isset($this->member))
        {
            foreach($this->member->properties as $member_prop)
            {
                //Fireshield properties are only eligible if not in pre-risk program and policy is active
                if($member_prop->policy_status == 'active' && ($member_prop->fireshield_status == 'offered' || $member_prop->fireshield_status == 'enrolled'))
                {
                    $agentProperty = AgentProperty::model()->find("property_pid ='".$member_prop->pid."'");
                    if(isset($agentProperty))
                    {
                        $name = $agentProperty->policyholder_name;
                    }
                    $property = array(
                        'id'=>$member_prop->pid,
                        'name'=>trim($name),
                        //'name'=>trim($member_prop->member->first_name.' '.$member_prop->member->last_name),
                        'address'=>trim($member_prop->address_line_1.' '.$member_prop->address_line_2),
                        'city'=>$member_prop->city,
                        'state'=>$member_prop->state,
                        'zip'=>$member_prop->zip,
                        'assessmentsAllowed'=>$member_prop->fs_assessments_allowed,
                        'questionSetID'=>$member_prop->question_set_id,
                    );
                    $properties[] = $property;
                }
            }
        }
        
        // Get the agent properties
        if (isset($this->agent)) 
        {
            foreach ($this->agent->agent_properties as $agent_prop) 
            {
                if($agent_prop->status == 'active')
                {
                    $property = array(
                        'id'=>$agent_prop->id,
                        'name'=>$agent_prop->policyholder_name,
                        'address'=>trim($agent_prop->address_line_1.' '.$agent_prop->address_line_2),
                        'city'=>$agent_prop->city,
                        'state'=>$agent_prop->state,
                        'zip'=>$agent_prop->zip,
                        'assessmentsAllowed'=>1,
                        'questionSetID'=>$agent_prop->question_set_id,
                    );
                    $properties[] = $property;
                }
            }
        }
        
		return $properties;
	}
	
	public function getProperty($address_line, $zip)
	{
		$address_line = trim($address_line);
		$zip = trim($zip);

		if(isset($this->member) && isset($this->member->properties))
		{
			foreach($this->member->properties as $member_prop)
			{
				// Address line could be just line 1 or line 1 and line 2 concatenated.
                if((trim($member_prop->address_line_1) == $address_line ||
					trim($member_prop->address_line_1 . ' ' . trim($member_prop->address_line_2) == $address_line))
					&& trim($member_prop->zip) == $zip)
				{
					return $member_prop;
				}
			}
		}
        else if (isset($this->agent)) 
        {
            foreach ($this->agent->agent_properties as $agent_prop)
            {
				// Address line could be just line 1 or line 1 and line 2 concatenated.
                if((trim($agent_prop->address_line_1) == $address_line ||
					trim($agent_prop->address_line_1 . ' ' . trim($agent_prop->address_line_2) == $address_line))
					&& trim($agent_prop->zip) == $zip)
				{
					return $agent_prop;
				}                
            }
        }
        
		return null;
	}
	
    /*
     * Get assessments. Note: assessments are actually fs_reports.
     */
	public function getAssessments()
	{
		$assessments = array();
        
        if (isset($this->member))
        {
            $properties = $this->member->properties;
            $property_id_attr_name = 'pid';
        }
        elseif(isset($this->agent))
        {
            $properties = $this->agent->agent_properties;
            $property_id_attr_name = 'id';
        }
        else
        {
            $properties = array();
        }
        
        foreach($properties as $property)
        {
            foreach($property->fs_reports as $fs_report)
            {
                $status = 0; //all statuses except Complete should return 0 (In Progress)
                if(isset($fs_report->status) && $fs_report->status == 'Completed')
                {
                    $status = 1;	
                }
                elseif($fs_report->status == 'Error')
                    $status = -1;

                $assessment = array(
                    'guid'=>$fs_report->report_guid,
                    'status'=>$status,
                    'submitDate'=>strtotime($fs_report->submit_date),
                    'propertyID'=>$property->$property_id_attr_name,
                    'address'=>array(
                        'addressLine1'=>$property->address_line_1,
                        'city'=>$property->city,
                        'state'=>$property->state,
                        'zip'=>$property->zip,
                    ),
                );
                $assessments[] = $assessment;
            }
        }

        return $assessments;
	}

    /**
	 * Generates the password hash.
	 * @param string password
	 * @param string salt
	 * @return string hash
	 */
	public function hashPassword($password,$salt)
	{
		return md5($password.$salt);
	}

	/**
	 * Generates a salt that can be used to generate a password hash.
	 * @return string the salt
	 */
	public function generateSalt()
	{
		return uniqid('',true);
	}
    
    /**
     * Checks to see if the FSUser email address is unique.
     * @return true if unique, false otherwise.
     */
    public function checkIsEmailUnique()
    {
        $isUnique = true;
        
        if (FSUser::model()->findByAttributes(array('email' => $this->email)))
            $isUnique = false;
        
        return $isUnique;
    }

    public function isAgentUser()
    {
        return !empty($this->agent_id);
    }

    /**
     * Gets the type of user (agent or member)
     * @return string
     */
    public function getType()
    {
        if(!empty($this->agent_id))
            return 'Agent';
        if(!empty($this->member_mid))
            return 'PolicyHolder';    
    }

    public function getName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getClientName()
    {
        if($this->isAgentUser())
        {
            if(isset($this->agent->client->name))
                return $this->agent->client->name;
        }
        else
        {
            if(isset($this->member->client->name))
                return $this->member->client->name;
        }
        return '';
    }

    public function getClientID()
    {
        if($this->isAgentUser())
            return $this->agent->client->id;
        else
            return $this->member->client->id;
    }
}
