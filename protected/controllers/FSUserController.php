<?php
class FSUserController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_FIRESHIELD => array(
                        'apiCheckCarrierCode2',
                        'apiCreate',
                        'apiCreate2',
                        'apiDelete',
                        'apiLogin',
                        'apiGetAssessments',
                        'apiGetProperties',
                        'apiUpdateProperty',
                        'apiResetAssessmentsAllowed',
                        'apiCreateProperty'
                    )
                )
            )
		);
	}

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
            )
        );
    }

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
                    'admin'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types) || in_array("Manager",$user->types)'
			),
			array('allow',
				'actions'=>array(
                    'test',
                    'create',
                    'update',
                    'testAzureNotification'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types)'
			),
			array('allow',
				'actions'=>array(
                    'apiCheckCarrierCode2',
                    'apiCreate',
                    'apiCreate2',
                    'apiDelete',
                    'apiLogin',
                    'apiGetAssessments',
                    'apiGetProperties',
                    'apiUpdateProperty',
                    'apiResetAssessmentsAllowed',
                    'apiCreateProperty',
                    'resetPassword',
                    'changePassword',
                    'faq',
                    'legal',
                    'requestResetApp2UserPW',
                    'resetApp2UserPW'
                ),
				'users'=>array('*')),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    public function actionTestAzureNotification($message, $vendor_id)
    {
        $fsUser = FSUser::model()->findByAttributes(array('vendor_id'=>$vendor_id));
        $fsUser->sendAzureNotification($message);
    }

    public function actionTest()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $result = exec("gpg --output C:\temp\test_decrypt_from_yii.csv --decrypt C:\Users\TylerC\Desktop\20160517_MOE_WDS.CSV.pgp");
        echo var_export($result, true);
    }

    /**
     * Checks to see if the carrier code (registration code) is valid.
     * For now it returns an error if it is coming from a "Member" user type (usaa/fs)
     */
    public function actionApiCheckCarrierCode2()
	{
        $returnArray = array();
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('carrierKey')))
            return;

        $member = Member::model()->find("fs_carrier_key = '".strtoupper($data['carrierKey']."'"));

        if (!isset($member))
        {
            // No member was found with that carrier key, so try looking it up in the agents table.
            $agent = Agent::model()->find("fs_carrier_key = '" . strtoupper($data['carrierKey'] . "'"));

            if (!isset($agent))
            {
                return WDSAPI::echoJsonError('ERROR: carrierKey not found', 'The given registration code was not valid for the user you entered.');
            }
            else
            {
                // Lookup the client for the agent.
                $client = Client::model()->findByPk($agent->client_id);
            }
        }
        else //found member with that carrier code...should not be using this version of the app
        {
            return WDSAPI::echoJsonError('ERROR: PolicyHolder User Not allowed.', 'This Registration Code is for the WDS Pro App, not WDS Pro for Insurers. Please download the WDS Pro App and try again.', 2);
        }

        $returnArray['error'] = 0; // success

        // Return the client code so the app knows what kind of user is using the app.
        $returnArray['client'] = $client->code;

        // Return the dynamic question set.
        $returnArray['questionSet'] = Client::model()->getQuestions($client->id);

        // Optionally, return URL to be shown as a welcome screen in the app.
        if (!empty($client->welcome_screen_url))
        {
            $returnArray['welcomeScreenUrl'] = $client->welcome_screen_url;
        }

        WDSAPI::echoResultsAsJson($returnArray);
	}
    
    //new App2 reset pass actions (requestResetApp2UserPW, resetApp2UserPW)
    public function actionRequestResetApp2UserPW()
    {
        $error = null;
        $message = 'Please enter your WDSPro username and an email will be sent to your address on file with reset instructions:';
        if(isset($_POST['username']))
        {

            $fsUser = FSUser::model()->findByAttributes(array('email'=>$_POST['username']));
            if(!isset($fsUser))
            {
                $error = 1;
                $message = '<span style="color:red">Username does not exist. Please contact WDS for support if you do not know your username.</span>';
            }
            else //successfully found user
            {
                $reset_token = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 5).time();
                $fsUser->reset_token = $reset_token;
                if(!$fsUser->save())
                {
                    $error = 2;
                    $message = '<span style="color:red">Error setting reset token. Please contact WDS for support if error persists.</span>';
                }
                else //successfully set reset token
                {
                    $subject = 'WDSPro Password Reset Request';
                    $body = "A password reset was issued for your WDSPro App user account tied to this email address. Please click the below link to reset your password. \r\n\r\nIf you did not request your password to be reset, please ignore this email and your password will remain the same.\r\n\r\n";
                    $body .= Yii::app()->getBaseUrl(true).Yii::app()->createUrl('fsUser/resetApp2UserPW', array('reset_token'=>$reset_token));
                    $sendMailResult = Helper::sendEmail($subject, $body, $fsUser->email);
                    if($sendMailResult === true) //successfully sent reset instructions email
                    {
                        $error = 0;
                        $message = '<span style="color:green">An Email was successfully sent to the account on file. Close this window and check your email for further instructions to reset your WDSPro App user password.</span>';
                    }
                    else
                    {
                        $error = 3;
                        $message = '<span style="color:red">Error sending reset email. Please contact WDS for support if error persists.</span>';
                    }
                }
            }
        }
        $this->layout = '//layouts/mainNoMenu';
		$this->render('app2_request_reset_pw', array('error'=>$error, 'message'=>$message));
	}

    public function actionResetApp2UserPW($reset_token)
    {
        $error = null;
        $message = 'Please enter your new WDSPro User password:';

        if(isset($_POST['new_pass']))
        {
            if(isset($_POST['new_pass_confirm']) && $_POST['new_pass'] === $_POST['new_pass_confirm'])
            {
                // Lookup User
                $fsUser = FSUser::model()->findByAttributes(array('reset_token'=>$reset_token));
                if($fsUser === NULL)
		        {
                    $error = 1;
                    $message = '<span style="color:red">Error: Invalid Reset Token.</span>';
		        }
		        else //successfully found fsuser based on reset_token
		        {
                    //validate token expiration
                    if(time() > (substr($reset_token, 5) + (60*30))) //if token (which is a 5 letter random char string followed by a unix timestamp) is older than 30 mins (30mins*60secs) then false
                    {
                        $error = 2;
                        $message = '<span style="color:red">Error: Invalid Reset Token.</span>';
                    }
                    else //successfull token validation
                    {
                        //update fsuser password
                        $fsUser->password = $fsUser->hashPassword($_POST['new_pass'], $fsUser->salt);
                        $fsUser->reset_token = null;
                        if($fsUser->save())
                        {
                            $error = 0; // success
                            $message = '<span style="color:green">SUCCESS: You have successfully updated your WDSPro App user password. Please close this window and proceed to login to the WDSPro App with your new password.</span>';
                        }
                        else
                        {
                            $error = 3;
                            $message = "ERROR: Could not update WDSPro App User password. Please contact Wildfire Defense Systems for support.";
                        }
                    }
                }
            }
            else
            {
                $error = 4;
                $message = '<span style="color:red">Error: Passwords do not match.</span>';
            }
        }

        $this->layout = '//layouts/mainNoMenu';
		$this->render('app2_reset_pw', array('error'=>$error, 'message'=>$message, 'reset_token'=>$reset_token));
    }

	public function actionChangePassword()
	{
		$headers = AHtml::getHttpRequestHeaders();
		$oauth = new YOAuth2();
		$message = null;
		if($oauth->verifyAccessToken('fireshield'))  //check oauth
		{
			if(isset($_POST['FSUser']))
			{
				if($_POST['new_pass'] == $_POST['new_pass_confirm'])
				{
					$fsUser = FSUser::model()->findByAttributes(array('email'=>$_POST['FSUser']['email']));
					if(isset($fsUser) && $fsUser->password == md5($_POST['FSUser']['password'].$fsUser->salt))
					{
						$fsUser->salt = uniqid();
						$fsUser->password = md5($_POST['new_pass'].$fsUser->salt);
						$fsUser->login_token = md5($fsUser->salt);
						if($fsUser->save())
							$this->redirect("fireshield://changePassword/?newLoginToken=".$fsUser->login_token);
						else
							$message = 'Change Password Error: There was an issue communicating with the service provider.';
					}
					else
					{
						$message = 'Login Error: Email or Current Password incorrect.';
					}
				}
				else
					$message = 'Change Password Error: New Password and Confirm did not match.';
			}
			$this->layout = '//layouts/fireshield';
            $this->pageTitle = 'Change Password';
			$access_token = $oauth->getAccessTokenParams();
			$this->render('change_pass', array('message'=>$message, 'access_token'=>$access_token));
		}
	}

	public function actionResetPassword()
	{
		if(isset($_GET['user'], $_GET['reset_token'])) //reset link from email
		{
			$fsUser = FSUser::model()->findByAttributes(array('email'=>$_GET['user'], 'login_token'=>$_GET['reset_token']));
			if(isset($fsUser))
			{
				$fsUser->salt = uniqid();
				$new_pass = substr(str_shuffle("23456789ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 8);
				$fsUser->password = md5($new_pass.$fsUser->salt);
				$fsUser->login_token = md5($fsUser->salt);
				if(!$fsUser->save())
					$message = 'Reset Password Error: There was an issue communicating with the service provider.';
				else
				{
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
					$mail->SetFrom(Yii::app()->params['emailUser'], 'FireShield');
					$mail->Subject = 'Fireshield Password Reset';
					$body = "Your FireShield account password has been reset to: ".$new_pass."\r\n\r\n After logging in with this temporary password you can update your password in the Change Password option in the App.";
					$mail->Body = $body;
					$mail->AddAddress($fsUser->email, $fsUser->first_name.' '.$fsUser->last_name);
					if($mail->Send())
						$message = "Success: An email has been sent to you with a new temporary password for your FireShield account.";
					else
						$message = "Error sending email.";
				}
			}
			else
				$message = 'Error: This is not a valid password reset link.';

			$this->layout = '//layouts/fireshield';
            $this->pageTitle = 'Reset Password';
			$this->render('reset_pass', array('message'=>$message));
		}
		else
		{
			//$headers = AHtml::getHttpRequestHeaders();
			//$oauth = new YOAuth2();
			$message = null;
			//if($oauth->verifyAccessToken('fireshield'))  //check oauth
			//{
				if(isset($_POST['email']))
				{
					$fsUser = FSUser::model()->findByAttributes(array('email'=>$_POST['email']));
					if(isset($fsUser))
					{
						Yii::import('application.extensions.phpmailer.JPhpMailer');
						$mail = new JPhpMailer;
						$mail->IsSMTP();
						$mail->SMTPDebug = 0;
						$mail->SMTPAuth = true;
						$mail->SMTPSecure = 'ssl';
						$mail->Host = 'mail.wds.bz';
						$mail->Port = '465';
						$mail->Username = 'fireshield@wildfire-defense.com';
						$mail->Password = '4ScyHOVTbtxl';
						$mail->SetFrom('fireshield@wildfire-defense.com', 'FireShield');
						$mail->Subject = 'Fireshield Password Reset Request';
						$body = "A password reset was issued for your FireShield user account tied to this email address. Please click the below link to reset your password. \r\n\r\nIf you did not request your password to be reset, please ignore this email and your password will remain the same.\r\n\r\n";
						$body .= Yii::app()->getBaseUrl(true).$this->createUrl('fsUser/resetPassword', array('user'=>$fsUser->email, 'reset_token'=>$fsUser->login_token));
						$mail->Body = $body;
						$mail->AddAddress($fsUser->email, $fsUser->first_name.' '.$fsUser->last_name);
						if($mail->Send())
							$message = "Success: An email has been sent to you with instructions to reset your password.";
						else
							$message = "Error sending email.";
					}
					else
						$message = "Reset Password Error: Email is not associated with a FireShield account.";
				}

				$this->layout = '//layouts/fireshield';
                $this->pageTitle = 'Reset Password';
				$this->render('reset_pass', array('message'=>$message));
			//}
		}
	}

	public function actionFAQ()
	{
		$this->layout = '//layouts/fireshield';
        $this->pageTitle = 'FAQ';
		$this->render('faq');
	}

	public function actionLegal()
	{
		$this->layout = '//layouts/fireshield';
        $this->pageTitle = 'Legal';
		$this->render('legal');
	}

	//ONLY should work in dev environment
	public function actionApiDelete($email)
	{
		if(Yii::app()->params['env'] == 'dev')
		{
			$fsUser = FSUser::model()->find("email = '".$email."'");
			if(isset($fsUser))
			{
				foreach($fsUser->member->properties as $member_prop)
				{
					foreach($member_prop->fs_reports as $fs_report)
					{
						$fs_report->delete();
					}
				}
				foreach($fsUser->member->properties as $member_prop)
				{
					$member_prop->fs_assessments_allowed = 1;
					$member_prop->save();
				}
				if($fsUser->delete())
					echo "Fireshield User $email Successfully Deleted AND deleted all assessments tied to this User/Member/Properties. And reset the allowed assessments for all associated properties.";
				else
					echo "Error deleting user $email. contact tyler if problem persists";
			}
			else
				echo "Error, user $email did not exist in the Fireshield users table";
		}
		else
			echo "ERROR: you should not be doing this in the produciton environment and this action will be reported.";
	}

	//ONLY should work in dev environment
	public function actionApiResetAssessmentsAllowed($email)
	{
		if(Yii::app()->params['env'] == 'dev')
		{
			$fsUser = FSUser::model()->find("email = '".$email."'");
			if(isset($fsUser))
			{
				foreach($fsUser->member->properties as $member_prop)
				{
					$member_prop->fs_assessments_allowed = 1;
					if($member_prop->save())
						echo "User Property ".$member_prop->address_line_1." reset to allow assessments.<br />";
					else
						echo "ERROR resetting user property ".$member_prop->address_line_1." to allow assessments. (Details:".var_dump($member_prop->getErrors())."<br />";
				}
			}
			else
				echo "Error, user $email did not exist in the Fireshield users table";
			echo '<br />Done';
		}
		else
			echo "ERROR: you should not be doing this in the produciton environment and this action will be reported.";
	}

	/**
     * Logs in a user for a given email and password.
     * IIS Rewrite rule: api/fireshield/v2/login/
     */
    public function actionApiLogin()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('email', 'password')))
            return;

        // Check email and password pair.
        $fsUser = FSUser::model()->find("email='".$data['email']."'");

        if (!isset($fsUser) || $fsUser->password !== $fsUser->hashPassword($data['password'],$fsUser->salt))
		{
            return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
        }

        if (isset($fsUser->member))
        {
            // Check for expired trial dates.
            if ($fsUser->member->is_tester && new DateTime($fsUser->member->trial_expire_date) <= new DateTime())
            {
                return WDSAPI::echoJsonError('ERROR: member trial has expired.', 'Your trial has expired.');
            }

            // Look up the client for this member.
            $client = Client::model()->findByAttributes(array('name' => $fsUser->member->client));
        }
        else
        {
            if (!isset($fsUser->agent))
            {
                return WDSAPI::echoJsonError('ERROR: could not find agent.');
            }

            // Look up the client for the agent.
            $client = Client::model()->findByPk($fsUser->agent->client_id);
        }

        // If a client was not found, there was a problem.
        if (!isset($client))
        {
            return WDSAPI::echoJsonError('ERROR: could not find client.');
        }

        $return_array['error'] = 0; // success
        $return_array['data'] = array(
            'loginToken'=>$fsUser->login_token,
            'vendorID'=>$fsUser->vendor_id,
            'properties'=>$fsUser->getProperties(),
            'client'=>$client->code,
            'questionSet'=>Client::model()->getQuestions($client->id),
            'type'=>$fsUser->getType(),
        );

        if (isset($fsUser->member))
        {
            if ($fsUser->member->is_tester)
            {
                $return_array['data']['trialExpireDate'] = (new DateTime($fsUser->member->trial_expire_date))->format('Y-m-d H:i');
            }
        }

        WDSAPI::echoResultsAsJson($return_array);
	}

	/*
     * Creates an account for the logged in user.
     * IIS URL Rewrite Rule: api/fireshield/v2/createAccount/
     */
    public function actionApiCreate()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('emailAddress', 'firstName', 'lastName', 'password', 'carrierKey')))
            return;

        // Check to see if email is already in FS Users table.
        $emailCheck = FSUser::model()->find("email = '".$data['account']['emailAddress']."'");

        if (isset($emailCheck))
            return WDSAPI::echoJsonError ('ERROR: email address was already in the FS Users table', 'The given email address already has an account associated with it.');

        $fsUser = new FSUser();

        // Check to see if last name and carrier key match a record in the database.
        $member = Member::model()->find("last_name = '".$data['account']['lastName']."' AND fs_carrier_key = '".$data['carrierKey']."'");

        if (!isset($member))
        {
            $agent = Agent::model()->find("last_name = '".$data['account']['lastName']."' AND fs_carrier_key = '".$data['carrierKey']."'");
        }

        if (!isset($member) && !isset($agent))
        {
            return WDSAPI::echoJsonError('Failed to find a member or agent!', 'The user information entered was invalid. Please try again or contact technical support for further assistance.');
        }

        $salt = uniqid();
        $vendor_id = trim(com_create_guid(), '{}');
        $attributes = array('email'=>$data['account']['emailAddress'],
                            'first_name'=>$data['account']['firstName'],
                            'last_name'=>$data['account']['lastName'],
                            'password'=>md5($data['account']['password'].$salt),
                            'salt'=>$salt,
                            'login_token'=>md5($salt),
                            'vendor_id'=>$vendor_id);

        if (isset($member))
            $attributes['member_mid'] = $member->mid;
        else
            $attributes['agent_id'] = $agent->id;

        $fsUser->attributes = $attributes;

        if ($fsUser->save())
        {
            $returnArray['error'] = 0; // success
            $returnArray['data'] = array('loginToken'=>$fsUser->login_token, 'vendorID'=>$fsUser->vendor_id, 'properties'=>$fsUser->getProperties());
        }
        else
        {
            return WDSAPI::echoJsonError('ERROR: creating a new Fireshield User with given attributes.', NULL, 2);
        }

        WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
     * Creates a user via the api (for App2)
     * @return string JSON of results
     */
    public function actionApiCreate2()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('emailAddress', 'firstName', 'lastName', 'password', 'carrierKey', 'platform')))
            return;

        // Check to see if email is already in FS Users table.
        $emailCheck = FSUser::model()->find("email = '".$data['account']['emailAddress']."'");

        if (isset($emailCheck))
            return WDSAPI::echoJsonError ('ERROR: email address was already in the FS Users table', 'The given email address already has an account associated with it.');

        $fsUser = new FSUser();

        // Check to see if last name and carrier key match a record in the database.
        $member = Member::model()->find("last_name = '".$data['account']['lastName']."' AND fs_carrier_key = '".$data['carrierKey']."'");

        if (!isset($member))
        {
            $agent = Agent::model()->find("last_name = '".$data['account']['lastName']."' AND fs_carrier_key = '".$data['carrierKey']."'");
        }

        if (!isset($member) && !isset($agent))
        {
            return WDSAPI::echoJsonError('Failed to find a member or agent!', 'The user information entered was invalid. Please try again or contact technical support for further assistance.');
        }

        $salt = $fsUser->generateSalt();
        $vendor_id = trim(com_create_guid(), '{}');
        $attributes = array('email'=>$data['account']['emailAddress'],
                            'first_name'=>$data['account']['firstName'],
                            'last_name'=>$data['account']['lastName'],
                            'password'=>$fsUser->hashPassword($data['account']['password'],$salt),
                            'salt'=>$salt,
                            'login_token'=>md5($salt),
                            'vendor_id'=>$vendor_id,
                            'platform'=>$data['account']['platform']);

        if (isset($member))
            $attributes['member_mid'] = $member->mid;
        else
            $attributes['agent_id'] = $agent->id;

        $fsUser->attributes = $attributes;

        if ($fsUser->save())
        {
            $returnArray['error'] = 0; // success
            $returnArray['data'] = array('loginToken'=>$fsUser->login_token, 'vendorID'=>$fsUser->vendor_id, 'properties'=>$fsUser->getProperties(), 'type'=>$fsUser->getType());
        }
        else
        {
            return WDSAPI::echoJsonError('ERROR: creating a new Fireshield User with given attributes.', NULL, 2);
        }

        WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
     * Creates a property for the currently logged in agent user.
     * IIS URL Rewrite Rule: api/fireshield/v2/createProperty
     * @return JSON new agent property ID
     */
    public function actionApiCreateProperty()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'streetAddress', 'city', 'state', 'zip')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.');
        }

        $agent = Agent::model()->findByPk($fsUser->agent_id);

        if (!isset($agent))
        {
            return WDSAPI::echoJsonError('ERROR: could not find the agent. Only agent users can create properties.', 'ERROR: This user type is not allowed to create properties');
        }

        $lat = !empty($data['lat']) ? $data['lat'] : NULL;
        $long = !empty($data['long']) ? $data['long'] : NULL;
        $questionSetID = !empty($data['questionSetID']) ? $data['questionSetID'] : NULL;
        $workOrderNum = !empty($data['workOrderNum']) ? $data['workOrderNum'] : NULL;
        $policyholderName = !empty($data['policyholderName']) ? $data['policyholderName'] : NULL;

        $agentProperty = new AgentProperty();
        $agentProperty->attributes = array('agent_id' => $agent->id,
            'work_order_num' => $workOrderNum,
            'address_line_1' => $data['streetAddress'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'lat' => $lat,
            'long' => $long,
            'question_set_id' => $questionSetID,
            'policyholder_name' => $policyholderName,
        );

        if (!$agentProperty->save())
        {
            return WDSAPI::echoJsonError('ERROR: failed to save the agent property', NULL, 2);
        }

        $returnArray = array();
        $returnArray['error'] = 0; // success
        $returnArray['data'] = array("agentPropertyID" => $agentProperty->id);

        WDSAPI::echoResultsAsJson($returnArray);
    }

	/*
     * Gets the properties for the current login user.
     * IIS URL Rewrite rule: api/fireshield/v2/getProperties
     */
    public function actionApiGetProperties()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.');
        }

		$returnArray = array();
        $returnArray['error'] = 0; // success
        $returnArray['data'] = array('properties'=>$fsUser->getProperties());

		WDSAPI::echoResultsAsJson($returnArray);
	}

	/**
     * Gets assessments for the currently logged in user.
     * IIS URL Rewrite Rule: api/fireshield/v2/getAssessments/
     */
    public function actionApiGetAssessments()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
        }

        $returnArray['error'] = 0; // success
		$returnArray['data'] = array('assessments'=>$fsUser->getAssessments());

        WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
	 * Administration for FSUsers.
	 */
	public function actionAdmin($type = 'fs')
	{
        $model = new FSUser('search');
        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['FSUser']))
        {
            $model->attributes = $_GET['FSUser'];
        }

        $this->render('admin',array(
            'model' => $model,
			'type' => $type,
        ));
	}

    /**
	 * Creates a new FSUser.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
        $fsUser = new FSUser;
        $origPassword = "";

        if(isset($_POST['FSUser']))
        {
            $userAttr = $_POST['FSUser'];

            if ($userAttr['password'] !== '')
            {
                $origPassword = $userAttr['password'];
                $userAttr['salt'] = $fsUser->generateSalt();
                $userAttr['password'] = $fsUser->hashPassword($userAttr['password'], $userAttr['salt']);
            }

            $fsUser->attributes = $userAttr;

            // If the email address already exists, then display an error.
            if (!$fsUser->checkIsEmailUnique())
            {
                Yii::app()->user->setFlash('error', "The email address $fsUser->email already exists in the system!");

                // Set unhashed password so the hashed version doesn't get returned to the form.
                $fsUser->password = $origPassword;
            }
            else
            {
                if($fsUser->save())
                {
                    Yii::app()->user->setFlash('success', "Fire Shield User #".$fsUser->id." Created Successfully!");
                    $this->redirect(array('admin',));
                }
            }
        }

        $this->render('create',array(
            'model' => $fsUser,
        ));
	}

    /**
	 * Deletes a FSUser.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the FSUser to be deleted
	 */
	public function actionDelete($id)
	{
        if (Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

    /**
	 * Updates a FSUser.
	 */
	public function actionUpdate($id)
	{
        $fsUser = $this->loadModel($id);
        $origEmail = $fsUser->email;

        if (isset($_POST['FSUser']))
        {
            $userAttr = $_POST['FSUser'];

            if ($userAttr['password'] !== '')
                $userAttr['password'] = $fsUser->hashPassword($userAttr['password'], $fsUser->salt);
            else
                $userAttr['password'] = $fsUser->password;

            $fsUser->attributes = $userAttr;

            $errorOccurred = false;

            // If the email address was changed, check to make sure the new address is unique.
            if ($fsUser->email !== $origEmail)
            {
                if (!$fsUser->checkIsEmailUnique())
                {
                    Yii::app()->user->setFlash('error', "The email address $fsUser->email already exists in the system!");
                    $errorOccurred = true;
                }
            }

            if (!$errorOccurred)
            {
                if ($fsUser->save())
                {
                    Yii::app()->user->setFlash('success', "Fire Shield User $id Updated Successfully!");
                    if($fsUser->member_mid!=NULL)
                      $this->redirect(array('admin&type=fs'));
                    elseif($fsUser->agent_id!=NULL)
                      $this->redirect(array('admin&type=agent'));
                    else
                      $this->redirect(array('admin'));
                }
            }
        }

        $fsUser->password = '';
        $this->render('update',array(
            'model' => $fsUser,
        ));
	}

    public function actionApiUpdateProperty()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'agentPropertyID')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.');

        $agent = Agent::model()->findByPk($fsUser->agent_id);
        if (!isset($agent))
            return WDSAPI::echoJsonError('ERROR: could not find the agent. Only agent users can update properties.', 'ERROR: This user type is not allowed to update properties');

        $agentProperty = AgentProperty::model()->with('fs_reports')->findByPk($data['agentPropertyID']);
        if(!isset($agentProperty))
            return WDSAPI::echoJsonError('ERROR: could not find agent property based on given propertyID.', 'ERROR: Could not find property to update.');

        if(isset($data['streetAddress']))
            $agentProperty->address_line_1 = $data['streetAddress'];
        if(isset($data['city']))
            $agentProperty->city = $data['city'];
        if(isset($data['state']))
            $agentProperty->state = $data['state'];
        if(isset($data['zip']))
            $agentProperty->zip = $data['zip'];
        if(isset($data['lat']))
            $agentProperty->lat = $data['lat'];
        if(isset($data['long']))
            $agentProperty->long = $data['long'];
        if(isset($data['questionSetID']))
            $agentProperty->question_set_id = $data['questionSetID'];
        if(isset($data['workOrderNum']))
            $agentProperty->work_order_num = $data['workOrderNum'];
        if(isset($data['policyholderName']))
            $agentProperty->policyholder_name = $data['policyholderName'];

        if(isset($data['status']) && ($data['status'] == 'active' || $data['status'] == 'canceled'))
        {
            $agentProperty->status = $data['status'];
            //commenting below out per PBI 2397
            //if($data['status'] == 'canceled' && !empty($agentProperty->fs_reports))
            //{
                //foreach($agentProperty->fs_reports as $fs_report)
                //{
                //    $fs_report->status = 'Canceled';
                //    $fs_report->save();
                //}
            //}
        }

        if (!$agentProperty->save())
        {
            return WDSAPI::echoJsonError('ERROR: failed to save the agent property', NULL, 2);
        }

        $returnArray = array();
        $returnArray['error'] = 0; // success

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
        $model = FSUser::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
	}
}
