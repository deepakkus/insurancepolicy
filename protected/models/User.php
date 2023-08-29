<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $name
 * @property string $username
 * @property string $password
 * @property string $salt
 * @property string $type
 * @property integer $permission_id
 * @property string $pw_exp
 * @property integer $login_attempts
 * @property datetime $locked_until
 * @property string $ips_allowed
 * @property string $email
 * @property string $reset_token
 * @property integer $client_id
 * @property datetime $user_expire
 * @property string $auto_login_token
 * @property string $alliance_id
 * @property string $wds_staff
 * @property integer $active
 * @property string $timezone
 * @property string $client_secret
 * @property string $redirect_uri
 * @property string $scope
 * @property string $api_mode
 * @property integer $removed
 * @property string $last_login
 * @property integer $registration_code_id
 * @property string $vendor_id
 * @property string $login_token
 * @property integer $member_mid
 * @property string $MFAPhoneNumber
 * @property string $MFACountryCode
 * @property string $MFAEmail
 * @property string $MFAMethodDefault
 * @property integer $country_id
 */
class User extends CActiveRecord
{
    public $affiliation;

    /**
     * User active state before the form was submitted
     * @var boolean
     */
    public $wasActive;

	/**
     * Returns the static model of the specified AR class.
     * @return User the static model class
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
		return 'user';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
            array('registration_code_id, vendor_id, login_token, member_mid, id', 'safe'),
            // scenario specific validation rules
            array('username, name, email, type', 'required', 'on' => 'insert'),
            array('username, name, email, type', 'required', 'on' => 'update'),
            array('username, type, client_secret, scope', 'required', 'on' => 'oauth'),
            array('type', 'isOauthUserType', 'on' => 'oauth'),
            // unique validation rule
            array('username', 'unique', 'message' => '{attribute} "{value}" has already been taken!'),
            // length validation rules
            array('api_mode,MFACountryCode,MFAMethodDefault', 'length', 'max' => 10),
            array('reset_token, auto_login_token', 'length', 'max' => 15),
            array('salt', 'length', 'max' => 23),
            array('MFAPhoneNumber', 'length', 'max' => 20),
            array('MFAEmail', 'length', 'max' => 100),
            array('timezone', 'length', 'max' => 30),
            array('password', 'length', 'max' => 32),
            array('client_secret', 'length', 'max' => 40),
            array('username, name, email, pw_exp, locked_until, user_expire', 'length', 'max' => 50),
            array('redirect_uri, scope', 'length', 'max' => 200),
			array('ips_allowed', 'length', 'max' => 500), //this should allow for about 10 ip ranges in a json structure
            array('permission_id, login_attempts, client_id, alliance_id, wds_staff, active, removed, country_id', 'numerical', 'integerOnly' => true),
			// The following rule is used by search()
			array('id, username, name, email, type, client_id, alliance_id, active, api_mode, removed', 'safe', 'on' => 'search'),
            // The following rule is used by searchEngineUsers()
			array('id, name, type, alliance_id, active, removed', 'safe', 'on' => 'searchEngineUsers'),
            // The following rule is used by searchClientUsers()
            array('id, username, type, client_secret, redirect_uri, scope, active, removed', 'safe', 'on' => 'searchClientUsers'),
            // The following rule is used by searchOauth()
            array('id, username, type, client_secret, redirect_uri, scope, active, removed', 'safe', 'on' => 'searchOauth')
		);
	}

    /**
     * Validation rule to determine if correct OAuth type is choosen
     * @param string $attribute
     */
    public function isOauthUserType($attribute)
    {
        $oauth = false;
        $oauthLegacy = false;

        foreach ($this->getSelectedTypes() as $type)
        {
            if ($type !== 'OAuth2' && $type !== 'OAuth2 Legacy')
                $this->addError($attribute, 'Oauth2 Users can only be of types" OAuth2" or "OAuth2 Legacy"!');

            if ($type === 'OAuth2')
                $oauth = true;
            else if ($type === 'OAuth2 Legacy')
                $oauthLegacy = true;

            if ($oauth === true && $oauthLegacy === true)
                $this->addError($attribute, 'Oauth2 Users can only be one of types "OAuth2" or "OAuth2 Legacy", not both!');
        }
    }

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'status_history' => array(self::HAS_MANY, 'StatusHistory', 'user_id'),
			'fs_report' => array(self::HAS_MANY, 'FSReport', 'assigned_user_id'),
            'user_geo' => array(self::HAS_MANY, 'UserGeo', 'user_id'),
            'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'alliance' => array(self::BELONGS_TO, 'Alliance', 'alliance_id'),
            'crew' => array(self::HAS_ONE, 'EngCrewManagement', 'user_id'),
            'user_clients' => array(self::HAS_MANY, 'UserClient', 'user_id'),
            'member' => array(self::BELONGS_TO, 'member', 'member_mid'),
			'country_code' => array(self::BELONGS_TO, 'CountryCode', 'country_id'), 
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => 'Username',
			'name' => 'Name',
			'password' => 'Password',
			'salt' => 'Salt',
			'type' => 'User Type',
			'permission_id' => 'Permission',
			'pw_exp' => 'Password Expiration',
			'locked_until' => 'Account Locked Until',
			'login_attempts' => 'Failed Login Attempts',
			'ips_allowed' => 'IP Ranges Allowed',
			'email' => 'Email',
            'reset_token' => 'PW Reset Token',
            'auto_login_token' => 'Auto Login Token',
            'client_id' => 'Parent Client',
            'user_expire' => 'User Expire Date (For Response Dashboard Trial Users)',
            'alliance_id'=>'Alliance',
            'wds_staff'=>'WDS Staff',
            'active'=>'Active',
            'timezone' => 'Timezone',
            'client_secret' => 'Client Secret',
            'redirect_uri' => 'Redirect URI',
            'scope' => 'Scope',
            'removed' => 'Removed',
            'api_mode' => 'API Mode',
            'last_login' => 'Last Login',
            'MFAPhoneNumber' => 'MFA Phone Number',
            'MFACountryCode' => 'MFA Country Code',
            'MFAEmail' => 'MFA Email',
            'MFAMethodDefault' => 'MFA Method Default'
		);
	}

	/**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('email', $this->email, true);
        $criteria->compare('client_id', $this->client_id);
        $criteria->compare('alliance_id', $this->alliance_id);
        $criteria->compare('active', $this->active);

        // Omit oauth users
        $criteria->addCondition('client_secret IS NULL');

		return new WDSCActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*'),
			),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 10)
		));
	}

	/**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchOauth()
    {
		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id);
        $criteria->compare('client_id',$this->client_id);
		$criteria->compare('username',$this->username,true);
        $criteria->compare('type',$this->type,true);
        $criteria->compare('client_secret', $this->client_secret, true);
		$criteria->compare('redirect_uri',$this->redirect_uri,true);
		$criteria->compare('scope', $this->scope, true);
        $criteria->compare('active', $this->active);
        $criteria->compare('api_mode', $this->api_mode, true);

        // Only oauth users
        $criteria->addCondition('client_secret IS NOT NULL');

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*'),
			),
			'criteria' => $criteria,
            'pagination' => array('PageSize' => 10)
		));
    }

	public static function getTypes()
	{
		return array(
			'Admin' => 'Admin',
			'Manager' => 'Manager',
            'Analytics' => 'Analytics',
            'Second Look' => 'Second Look',
            // Department
            'Risk' => 'Risk',
            'Risk Manager' => 'Risk Manager',
            'Engine View' => 'Engine View',
            'Engine' => 'Engine',
            'Engine Manager' => 'Engine Manager',
            'Response' => 'Response',
            'Response Manager' => 'Response Manager',
            // Fireshield
			'FS FRA' => 'FS FRA',
			'FS Editor' => 'FS Editor',
			'FS Fire Reviewer' => 'FS Fire Reviewer',
            'FS Offered' => 'FS Offered',
            // Pre Risk
			'PR Caller' => 'PR Caller',
			'PR Assessor' => 'PR Assessor',
			'PR Fire Reviewer' => 'PR Fire Reviewer',
			'PR Writer' => 'PR Writer',
			'PR Reviser' => 'PR Reviser',
			'PR Final' => 'PR Final',
            // Engines
            'Engine User' => 'Engine User',
            'Engine Alliance Manager' => 'Engine Alliance Manager',
            // Dashboards
            'Dash WDS Staff' => 'Dash WDS Staff',   // Assign to all internal staff
            'Dash Client' => 'Dash Client',
            'Dash Email Group Non-Dispatch' => 'Dash Email Group Non-Dispatch',
            'Dash Email Group Dispatch' => 'Dash Email Group Dispatch',
            'Dash Email Group Noteworthy' => 'Dash Email Group Noteworthy',
            'Dash Enrollment' => 'Dash Enrollment',
            'Dash Caller' => 'Dash Caller',
            'Dash Analytics' => 'Dash Analytics',
            'Dash Post Incident Summary' => 'Dash Post Incident Summary',
            'Dash User Admin' => 'Dash User Admin',
            'Dash User Manager' => 'Dash User Manager',
            'Dash API' => 'Dash API',
            // Liberty specific dashboard
            'Dash LM All' => 'Dash LM All',
            // WDS Risk
            'Dash Risk'=>'Dash Risk',
            // Oauth2
            'OAuth2' => 'OAuth2',
            'OAuth2 Legacy' => 'OAuth2 Legacy',

            // Other
            'Disabled' => 'Disabled'
		);
	}

    public static function getClientUserTypes()
	{
		return array(
            // Dashboards
            'Dash WDS Staff' => 'Dash WDS Staff',   // Assign to all internal staff
            'Dash Client' => 'Dash Client',
            'Dash Email Group Non-Dispatch' => 'Dash Email Group Non-Dispatch',
            'Dash Email Group Dispatch' => 'Dash Email Group Dispatch',
            'Dash Email Group Noteworthy' => 'Dash Email Group Noteworthy',
            'Dash Enrollment' => 'Dash Enrollment',
            'Dash Caller' => 'Dash Caller',
            'Dash Analytics' => 'Dash Analytics',
            'Dash Post Incident Summary' => 'Dash Post Incident Summary',
            'Dash User Admin' => 'Dash User Admin',
            'Dash User Manager' => 'Dash User Manager',
            'Dash API' => 'Dash API'
		);
	}

    /**
     * Returns only usertypes which ally to engine users
     * @return array
     */
    public static function getEngineUserTypes()
	{
		return array(
            'Engine User' => 'Engine User',
            'Engine Alliance Manager' => 'Engine Alliance Manager'
		);
	}

    public function getGeoLocations()
    {
        return array(
            'AK'=>'AK',
            'AL'=>'AL',
            'AR'=>'AR',
            'AZ'=>'AZ',
            'CA'=>'CA',
            'CO'=>'CO',
            'CT'=>'CT',
            'DC'=>'DC',
            'DE'=>'DE',
            'FL'=>'FL',
            'GA'=>'GA',
            'HI'=>'HI',
            'IA'=>'IA',
            'ID'=>'ID',
            'IL'=>'IL',
            'IN'=>'IN',
            'KS'=>'KS',
            'KY'=>'KY',
            'LA'=>'LA',
            'MA'=>'MA',
            'MD'=>'MD',
            'ME'=>'ME',
            'MI'=>'MI',
            'MN'=>'MN',
            'MO'=>'MO',
            'MS'=>'MS',
            'MT'=>'MT',
            'NC'=>'NC',
            'ND'=>'ND',
            'NE'=>'NE',
            'NH'=>'NH',
            'NJ'=>'NJ',
            'NM'=>'NM',
            'NV'=>'NV',
            'NY'=>'NY',
            'OH'=>'OH',
            'OK'=>'OK',
            'OR'=>'OR',
            'PA'=>'PA',
            'RI'=>'RI',
            'SC'=>'SC',
            'SD'=>'SD',
            'TN'=>'TN',
            'TX'=>'TX',
            'UT'=>'UT',
            'VA'=>'VA',
            'VT'=>'VT',
            'WA'=>'WA',
            'WI'=>'WI',
            'WV'=>'WV',
            'WY'=>'WY'
        );
    }

    /**
     * Description: Returns the types for API mode to be used in dropdown lists.
     * @return array
     */
    public static function getApiModeTypes()
    {
        return array(
            'live' => 'live',
            'test' => 'test'
        );
    }

	//returns an array of types selected for this user
	public function getSelectedTypes()
	{
		return explode(',', $this->type);
	}

	protected function beforeSave()
	{
		if (is_array($this->type))
			$this->type = implode(',', $this->type);

		if (empty($this->locked_until))
			$this->locked_until = null;
		if (empty($this->pw_exp))
			$this->pw_exp = null;
        if (empty($this->user_expire))
			$this->user_expire = null;

        if (isset($_POST['affiliation']))
        {
            //Affiliations
            $this->affiliation = $_POST['affiliation'];

            if ($this->affiliation == 'wds')
            {
                $this->wds_staff = 1;
                $this->client_id = null;
                $this->alliance_id = null;
            }

            else if ($this->affiliation == 'alliance')
            {
                $this->wds_staff = null;
                $this->client_id = null;
            }

            else if ($this->affiliation == 'client')
            {
                $this->wds_staff = null;
                $this->alliance_id = null;
            }
        }

		return parent::beforeSave();
	}

    protected function afterSave()
    {
        // Checking to see if oauth token activation check needs to occur
        if ($this->isNewRecord === false && $this->scenario === 'oauth')
        {
            // Oauth user is different active state, need to update oauth tokens
            if ($this->wasActive !== $this->active)
            {
                // Oauth user has been reactivated
                if (filter_var($this->active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true)
                {
                    // If Ouath2 Legacy usertype, set tokens back to null
                    if (in_array('OAuth2 Legacy', $this->getSelectedTypes()) === true)
                    {
                        Yii::app()->db->createCommand()->update('oa2_tokens', array(
                            'expires' => null
                        ), 'client_id = :username', array(
                            ':username' => $this->username
                        ));
                    }
                }
                // Oauth user has been deactivated
                else
                {
                    // Set all client token (access/refresh) timestamps to now
                    Yii::app()->db->createCommand()->update('oa2_tokens', array(
                        'expires' => time()
                    ), 'client_id = :username', array(
                        ':username' => $this->username
                    ));
                }
            }
        }

        return parent::afterSave();
    }

    public function generateOauthClientSecret()
    {
        $md5 = function() {
            return md5(base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), uniqid())));
        };

        return printf('%s-%s-%s-%s-%s', substr($md5(), 0, 8), substr($md5(), 0, 4), substr($md5(), 0, 4), substr($md5(), 0, 4), substr($md5(), 0, 12));
    }

	public function wdsAdminAllowedTypes()
	{
		return array(
            'Admin' => 'Admin',
			'Manager' => 'Manager',
            'Analytics' => 'Analytics',
            // Department
            'Risk' => 'Risk',
            'Risk Manager' => 'Risk Manager',
            'Engine View' => 'Engine View',
            'Engine' => 'Engine',
            'Engine Manager' => 'Engine Manager',
            'Response' => 'Response',
            'Response Manager' => 'Response Manager',
            //Fireshield
			'FS FRA' => 'FS FRA',
			'FS Editor' => 'FS Editor',
			'FS Fire Reviewer' => 'FS Fire Reviewer',
            //Pre Risk
			'PR Caller' => 'PR Caller',
			'PR Assessor' => 'PR Assessor',
			'PR Fire Reviewer' => 'PR Fire Reviewer',
			'PR Writer' => 'PR Writer',
			'PR Reviser' => 'PR Reviser',
			'PR Final' => 'PR Final'
		);
	}

    public function getActiveMask()
    {
        return $this->active ? '&#x2713;' : '&#x2716;';
    }

    public function getActiveFilter()
    {
        return array('0'=>'&#x2716;', '1'=>'&#x2713;');
    }

	public function checkWDSAdminAllowed()
	{
		foreach($this->getSelectedTypes() as $type)
		{
			if(in_array($type, $this->wdsAdminAllowedTypes()))
				return true;
		}
		return false;
	}

    // COMMENTED OUT 11-28-16, remove this + UserController->actionApiLogin() commented lines by 01-01-17 if no issues have been raised
    // - Matt

    //For old dashboard (2.0) the api call actually told you what client website the user was trying to log in to
    //public function checkDash20UserClientType($client)
    //{
    //    foreach($this->getSelectedTypes() as $type)
    //    {
    //        //wds user types are not restricted by client
    //        if(in_array($type, array('Dash WDS Manager', 'Dash WDS Response Staff', 'Dash WDS Field')))
    //            return true;
    //        elseif($client == 'usaa' && in_array($type, array('Dash USAA')))
    //            return true;
    //        elseif($client == 'chubb' && in_array($type, array('Dash Chubb Manager', 'Dash Chubb Regional Rep')))
    //            return true;
    //        elseif($client == 'liberty' && in_array($type, array('Dash LM All', 'Dash LM Liberty', 'Dash LM Safeco', 'Dash LM CA Liberty', 'Dash LM CA Safeco')))
    //            return true;
    //        elseif($client == 'crestbrook' && in_array($type, array('Dash Crestbrook')))
    //            return true;
    //    }
    //    return false;
    //}

    //If the user is trying to log into new dashboard, than use this call instead...
    public function checkPublicUserType($site)
    {
        $validUserTypes = array();

        if ($site == 'dash')
            array_push($validUserTypes, 'Dash WDS Staff', 'Dash Client', 'Dash Risk');
        else if ($site == 'engine')
            array_push($validUserTypes, 'Engine User');

        foreach($this->getSelectedTypes() as $type)
		{
            if(in_array($type, $validUserTypes))
				return true;
        }
        return false;
    }

	//checks the password expiration and returns false if its not expired or true if it is
	public function checkPWExp()
	{
		if(isset($this->pw_exp) && strtotime($this->pw_exp) > time())
			return false;
		else //pw_exp is either not set or pw is expired
			return true;
	}

	public function loginAttempt()
	{
		//update login attempts for user
		if(!isset($this->login_attempts))
			$this->login_attempts = 1;
		else
			$this->login_attempts++;
		$this->save();
	}

	public function checkMaxLoginAttempts($lockoutTime = "+30 minutes")
	{
        $maxAttempts = Yii::app()->systemSettings->maxLoginAttempts;

        //Check if attempts exceed 3 and lockout time greater than current time? (Still locked out)
        if($this->login_attempts > $maxAttempts && strtotime($this->locked_until) > time())
        {
			return true; //locked out
        }
        //Attempts exceed 3 BUT lockout time less than current time? (no longer locked out)
        elseif($this->login_attempts > $maxAttempts && strtotime($this->locked_until) < time())
		{
			$this->resetAttempts();
            return false; //not locked out
		}
         //Attempts has greater/equal to max attempts allowed, so need to set the lockout time (lock them out)
        elseif($this->login_attempts >= $maxAttempts)
		{
			$this->locked_until = date('Y-m-d H:i', strtotime($lockoutTime));
			$this->save();
            return true; //now locked out
		}
        //None of the above was reached, so user has less than 3 attempts
        else
        {
            $this->locked_until = null;
            $this->save();
            return false; //not locked out
        }

	}

    //checks to see if the user account has expired
	public function checkUserExp()
	{
        //Account has expired
		if(!empty($this->user_expire) && date('Y-m-d') > $this->user_expire)
			return true;
		else //account is OK
			return false;
	}

	public function checkPassComplexity($password)
	{
		/* Password complexity regEx for:
         * - atleast 1 upper and 1 lower case
         * - 8 to 20 characters long
         * - atleast one number
         * - atleast one special char
         */
		$pattern = "#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";
		if(preg_match($pattern, $password))
			return true;
		else
			return false;
	}

	/*
     * Function to check an instantiated users allowed IP ranges
     * with the passed in IP address presumably from $_SERVER['REMOTE_ADDR']
     * They are stored in a JSON object.
     * Returns True if the ip is in a valid range, false otherwise
     */
	public function checkAllowedIPs($ip)
	{
		//if local host then return true
		if($ip == "::1" || $ip == "127.0.0.1")
			return true;

		//Proper JSON Structure of ip ranges example
		/*
		[
        {"start":"192.168.0.1", "end":"192.168.0.100"},
        {"start":"10.0.0.1", "end":"10.0.0.50"}
		]
         */
		$allowed_ips = json_decode($this->ips_allowed);
		if(!isset($allowed_ips)) //ERROR Parsing the ips_allowed json for the user';
			return false;

		foreach($allowed_ips as $ip_range)
		{
			if(isset($ip_range->start, $ip_range->end)) //if these are not set then not a valid range
			{
				$start = ip2long($ip_range->start);
				$end = ip2long($ip_range->end);
				$ip_to_check = ip2long($ip);
				if($start != -1 && $start !== FALSE && $end != -1 && $end !== FALSE && $ip_to_check != -1 && $ip_to_check !== FALSE) //check to see they were all valid ip addys
				{
					if($ip_to_check >= $start && $ip_to_check <= $end)
						return true;
				}
			}
		}
		return false;
	}

	public function resetAttempts()
	{
		$this->login_attempts = 0;
		$this->locked_until = null;
        $this->last_login = date('Y-m-d H:i');
		return $this->save();
	}

    /**
     * Checks if the given password is correct.
     * @param string the password to be validated
     * @return boolean whether the password is valid
     */
	public function validatePassword($password)
	{
		if($this->hashPassword($password,$this->salt)===$this->password)
        {
            $this->auto_login_token = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 5).time();
            $this->save();
            return true;
        }
        else
            return false;
	}

	/**
     * Generates the password hash.
     * @param string password
     * @param string salt
     * @return string hash
     */
	public function hashPassword($password,$salt)
	{
		return md5($salt.$password);
	}

	/**
     * Generates a salt that can be used to generate a password hash.
     * @return string the salt
     */
	public function generateSalt()
	{
		return uniqid('',true);
	}

	public function userDropDownList($model, $attribute, $selected, $otherHTMLOptions = array())
	{
		$criteria=new CDbCriteria;
		$criteria->order = 'name ASC';
        $results = $this->findAll($criteria);

		$options = array();
        $selectedOptions = array();
		foreach($results as $result)
		{
            if($result->name != '')
            {
			$options[$result->id] = $result->name;
            }
            if(isset($selected))
            {
                if($result->id == $selected)
                {
                    $selectedOptions[$result->id] = array('selected'=>"selected");
                }

            }
		}
		$htmlOptions['options'] = $selectedOptions;
		$htmlOptions['empty'] = '';
        $htmlOptions = array_merge($htmlOptions, $otherHTMLOptions);
		return CHtml::activeDropDownList($model, $attribute, $options, $htmlOptions);
	}

    public function validateToken($reset_token)
    {
        if($this->reset_token !== $reset_token) //if token doesn't match stored one then false
            return false;
        else if(time() > (substr($reset_token, 5) + (60*30))) //if token (which is a 5 letter random char string followed by a unix timestamp) is older than 30 mins (30mins*60secs) then false
            return false;
        else
            return true;
    }

    public function validateAutoLoginToken($token)
    {
        if($this->auto_login_token !== $token) //if token doesn't match stored one then false
            return false;
        else if(time() > (substr($token, 5) + (60*60*9))) //if token (which is a 5 letter random char string followed by a unix timestamp) is older than 9 hours (60mins*60secs) then false
            return false;
        else
            return true;
    }

    public function requestResetPass($data)
    {

        $return_array = array();

        if(empty($data['return_url']))
        {
            $data['return_url'] = Yii::app()->getBaseUrl(true).Yii::app()->createUrl('site/login');
        }

        //lookup user and then send email with reset link with 30min token
        $user = User::model()->findByAttributes(array('username'=>$data['username']));
        if($user === NULL)
		{
            $return_array['error'] = 1;
            $return_array['errorFriendlyMessage'] = "ERROR: Username does not exist.";
            $return_array['errorMessage'] = "ERROR: Username does not exist.";
            return $return_array;
		}
        else if(empty($user->email))
        {
            $return_array['error'] = 2;
            $return_array['errorFriendlyMessage'] = "ERROR: Username does not have an email on file associated with it, Please contact WDS IT to have this set before you can reset your password.";
            $return_array['errorMessage'] = "ERROR: Username does not have an email on file associated with it, Please contact WDS IT to have this set before you can reset your password.";
            return $return_array;
        }
		else //found username and email is set
		{
            $reset_token = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 5).time();
            $user->reset_token = $reset_token;
            if(!$user->save())
            {
                $return_array['error'] = 3;
                $return_array['errorFriendlyMessage'] = "ERROR: Could not set reset_token for user.";
                $return_array['errorMessage'] = "ERROR: Could not set reset_token for user.";
                return $return_array;
            }
            Yii::import('application.extensions.phpmailer.PHPMailer');
            $mail = new PHPMailer(true);
            try {
                $mail->IsSMTP();
                $mail->SMTPDebug = 0;
                $mail->SMTPAuth = true;
                $mail->Host = Yii::app()->params['emailHost'];
                $mail->SMTPAutoTLS = false;
                $mail->SMTPOptions = Yii::app()->params['emailSMTPOptions'];
                $mail->Username = Yii::app()->params['emailUser'];
                $mail->Password = Yii::app()->params['emailPass'];
                $mail->SetFrom(Yii::app()->params['emailUser'], 'Wildfire Defense Systems');
                $mail->Subject = 'Password Reset Request';
                $body = "A password reset was issued for your Wildfire Defense Systems user account tied to this email address. Please click the below link to reset your password. \r\n\r\nIf you did not request your password to be reset, please ignore this email and your password will remain the same.\r\n\r\n";

                if (!empty($data['wds_fire']))
                {
                    $body .= Yii::app()->params['wdsfireBaseUrl'] . '/index.php/site/forgot-password-reset?reset_token=' . $reset_token;
                }
                else if (!empty($data['wds_engines']))
                {
                    $body .= Yii::app()->params['wdsenginesBaseUrl'] . '/index.php/site/forgot-password-reset?reset_token=' . $reset_token;
                }
                else
                {
                    $body .= Yii::app()->getBaseUrl(true).Yii::app()->createUrl('user/resetPass', array('reset_token'=>$reset_token, 'return_url'=>$data['return_url']));
                }
                $mail->Body = $body;
                $mail->AddAddress($user->email, $user->name);

                if ($mail->send())
                {
                    $return_array['error'] = 0;
                }
                else
                {
                    $return_array['error'] = 1;
                    $return_array['errorFriendlyMessage'] = 'Error sending email.';
                    $return_array['errorMessage'] = 'Error sending email.';
                }
            } catch (phpmailerException $e) {
                $return_array['error'] = 4;
                $return_array['errorFriendlyMessage'] = 'Error sending email.';
                $return_array['errorMessage'] = $e->errorMessage(); //Pretty error messages from PHPMailer
            } catch (Exception $e) {
                $return_array['error'] = 4;
                $return_array['errorFriendlyMessage'] = 'Error sending email.';
                $return_array['errorMessage'] = $e->getMessage(); //Boring error messages from anything else!
            }

            return $return_array;
        }
    }

    public function resetPass($data)
    {
        $return_array = array();

        // Lookup User
		$user = User::model()->findByAttributes(array('reset_token'=>$data['reset_token']));
		if($user === NULL)
		{
            $return_array['error'] = 1;
            $return_array['errorFriendlyMessage'] = "ERROR: Invalid Token";
            $return_array['errorMessage'] = "ERROR: Invalid Token";
            return $return_array;
		}
		else //found username
		{
			if(!$user->validateToken($data['reset_token']))
			{
				$return_array['error'] = 2;
                $return_array['errorFriendlyMessage'] = "ERROR: Expired or Invalid Token";
                $return_array['errorMessage'] = "ERROR: Expired or Invalid Token";
                return $return_array;
			}
			else //token validated, so update pw
			{
				if($user->checkPassComplexity($data['new_password']))
				{
					$user->password = $user->hashPassword($data['new_password'], $user->salt);
					$user->pw_exp = date('Y-m-d', strtotime('+ 90 days'));
                    $user->login_attempts = 0;
                    $user->locked_until = null;
					if($user->save())
					{

						/*
						*	Call Update_PristiniUser API from component page WDSAPIG2
						*/
						$provectus = new WDSAPIG2();
						$result = $provectus->wdsUpatePristiniUser($user,$user->password);

						$return_array['error'] = 0; // success
                        $return_array['data'] = array(
					        'return_url'=>$data['return_url'],
				        );
						return $return_array;
					}
					else
					{
                        $return_array['error'] = 3;
                        $return_array['errorFriendlyMessage'] = "ERROR: could not update password.";
                        $return_array['errorMessage'] = "ERROR: could not update password.";
                        return $return_array;
					}
				}
				else
				{
                    $return_array['error'] = 4;
                    $return_array['errorFriendlyMessage'] = "Error updating password. New Password must have 8-20 characters, with at least 1 uppercase and lowercase letter, 1 number, and 1 symbol";
                    $return_array['errorMessage'] = "Error updating password. New Password must have 8-20 characters, with at least 1 uppercase and lowercase letter, 1 number, and 1 symbol";
                    return $return_array;
				}
			}
		}
    }

    /*
     * Description: Retreives the auto-login token for the given user
     * @param $id - int the id of the user
     * @return - string the auto_login_token from the user table
     */
    public static function getAutoLoginToken($id){
        $sql = "select auto_login_token from [user] where id = :id";
        return Yii::app()->db->createCommand($sql)->bindParam(":id", $id, PDO::PARAM_STR)->queryScalar();
    }

    /*
     * Description: Helper function to get array of ClientIDs user is assigned to
     * @return - array of client ids user is assigned to
     */
    public function getClientIDs()
    {
        $returnArray = array();
        foreach($this->user_clients as $userClient)
        {
            $returnArray[] = $userClient->client_id;
        }

        return $returnArray;
    }

    /**
     * Retrieves a list of models (sub set of dashboard client users) based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchClientUsers($pageSize = 25)
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id, true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('email', $this->email, true);
        $criteria->compare('client_id', $this->client_id);
        $criteria->compare('alliance_id', $this->alliance_id);
        $criteria->compare('active', $this->active);
        $criteria->compare('removed', $this->removed);
        // search not empty client id
        $criteria->addCondition('client_id IS NOT NULL');
        // Omit api_mode
        $criteria->addCondition('api_mode IS NULL');
		$dataProvider = new WDSCActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*'),
			),
			'criteria' => $criteria,
            //'pagination' => array('PageSize' => 10)
		));

        if($pageSize == NULL)
        {
            $dataProvider->pagination = false;
        }
        else
        {
            $dataProvider->pagination->pageSize = $pageSize;
            $dataProvider->pagination->validateCurrentPage = false;
        }

        return $dataProvider;
	}

    /**
     * Retrieves a list of models (sub set of dashboard client users) based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchEngineUsers($pageSize = 25)
	{
		$criteria = new CDbCriteria;

		$criteria->compare('name',$this->name,true);
		$criteria->compare('type',$this->type,true);
        $criteria->compare('alliance_id', $this->alliance_id);
        $criteria->compare('active', $this->active);
        $criteria->compare('removed', $this->removed);

        // search for engine users only
        if(empty($this->type)){
            $criteria->addCondition("([type] LIKE '%Engine User%' OR [type] LIKE '%Engine Alliance Manager%')");
        }

		$dataProvider = new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder' => array('id' => CSort::SORT_DESC),
				'attributes' => array('*'),
			),
			'criteria' => $criteria
		));

        $dataProvider->pagination->pageSize = $pageSize;
        $dataProvider->pagination->validateCurrentPage = false;

        return $dataProvider;
	}

    /*
     * Return mask value 0/1 for the column removed
    */
    public function getRemovedMask()
    {
        return $this->removed ? '&#x2713;' : '&#x2716;';
    }

    /*
    * Return removed/not removed
    */
    public function getRemovedFilter()
    {
        return array('0'=>'&#x2716;', '1'=>'&#x2713;');
    }
    public function getProperties()
	{
		$properties = array();
        
        if (isset($this->member))
        {
            foreach($this->member->properties as $member_prop)
            {
                    if($member_prop->app_status == 'active')
                    {
                        $property = array(
                            'id'=>$member_prop->pid,
                            'name'=>trim($member_prop->policyholder_name),
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
		return $properties;
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
    /*
    *   sent user notification via email
    */
    public function sendAzureNotification($message)
    {
        $connectionString = "Endpoint=sb://wdspro.servicebus.windows.net/;SharedAccessKeyName=DefaultFullSharedAccessSignature;SharedAccessKey=xbMv/mf9nE0JI853iDqW6cXm9i3FjUQo3I2CgaDeiAY=";
        $hubName = "WDSpro";
        $hub = new AzureNotificationHub($connectionString, $hubName);
        /*if($this->platform === 'Android')
        {
            $payload = '{"data":{"message":"'.$message.'"}}';
            $notification = new AzureNotification("gcm", $payload);
        }
        elseif($this->platform === 'iOS')
        {
            $payload = '{"aps":{"alert":"'.$message.'"}}';
            $notification = new AzureNotification("apple", $payload);
        }*/
       // $hub->sendNotification($notification, $this->vendor_id);
    }
}