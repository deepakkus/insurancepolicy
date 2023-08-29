<?php
class AppController extends Controller
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
                        'apiCreateProperty',
                        'apiCheckRegistrationCode',
                        'apiCreateUser',
                        'apiGetRegistrationCodes',
                        'apiUserLogin',
                        'apiForgotPassword',
                        'apiGetAllProperties',
                        'apiGetQuestionSets',
                        'apiUploadAssessment',
                        'apiNewUploadAssessment',
                    ),
                    WDSAPI::WDS_PRO => array(
                    'apiCheckRegistrationCode',
                        'apiCreateUser',
                        'apiGetRegistrationCodes',
                        'apiUserLogin',
                        'apiForgotPassword',
                        'apiGetAllProperties',
                        'apiGetQuestionSets',
                        'apiUploadAssessment',
                        'apiNewUploadAssessment'
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
                    'apiCheckRegistrationCode',
                    'apiCreateUser',
                    'apiGetRegistrationCodes',
                    'apiUserLogin',
                    'apiForgotPassword',
                    'resetPassword',
                    'changePassword',
                    'faq',
                    'legal',
                    'requestResetApp2UserPW',
                    'resetApp2UserPW',
                    'apiGetAllProperties',
                    'apiGetQuestionSets',
                    'apiUpdateNewProperty',
                    'apiUploadAssessment',
                    'apiNewUploadAssessment',
                    'requestResetUserPW',
                    'resetUserPW'
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
     /**
     * Checks to see if the registration code is valid.
     */
    public function actionApiCheckRegistrationCode()
	{
        $returnArray = array();
        $data = NULL;
        $serviceType = '';
        $legacy = false;
        $secondLookUser = false;
        $userTypes = array();
        $headerChecker = '';
        if (!WDSAPI::getInputDataArray($data, array('registrationCode', 'ServiceOfferingCode')))
            return;
       
        //$registrationCode = RegistrationCode::model()->find("code = '".$data['registrationCode']."'");
        if($data['ServiceOfferingCode'] == 'SL')
        {
            $registrationCode = RegistrationCode::model()->find("code = '".$data['registrationCode']."'");
            if(!$registrationCode)
            {
                return WDSAPI::echoJsonError('ERROR: registration code not found', 'The given registration code was not valid for the user you entered.');
            }
            if(!$registrationCode->is_active)
            {
                return WDSAPI::echoJsonError('ERROR: Registration code not active', 'The given registration code is not active for the user you entered.');
            }
            $clientCode = substr($data['registrationCode'], 2, 2);
            $client = Client::model()->find("client_reg_code = ".$clientCode);
            if(!isset($client))
            {
                return WDSAPI::echoJsonError('ERROR: No Client Reg Code', 'There is no two digit client reg code for the given registration code.');
            }
            else
            {
                if(!$client->app)
                {
                    return WDSAPI::echoJsonError('ERROR: Not an app user', 'The given registration code is not valid for app user.');
                }
            }
            $serviceCode = substr($data['registrationCode'], 4, 2);
            $serviceOffering = ServiceOffering::model()->find("service_offering_code = '" . $serviceCode . "'");
            if(!isset($serviceOffering))
            {
                return WDSAPI::echoJsonError('ERROR: No Service offering Code', 'Service offering code not found for the given registration code.');
            }
            $serviceType = $this->getServiceType(1);
        }
        elseif(($data['ServiceOfferingCode'] == 'AP') || ($data['ServiceOfferingCode'] == 'FS'))
        {
                //No registration code found in registration_code table, so try to check with agent table
                $agent = Agent::model()->find("fs_carrier_key = '" .$data['registrationCode'] . "'");
                if (!isset($agent))
                {
                    $member = Member::model()->find("fs_carrier_key = '" .$data['registrationCode'] . "'");
                    if($data['ServiceOfferingCode'] == 'AP')
                    {
                        if (!isset($member))
                        {
                            return WDSAPI::echoJsonError('ERROR: registration code not found', 'The given registration code was not valid for the user you entered.');
                        }
                    }
                    elseif($data['ServiceOfferingCode'] == 'FS')
                    {
                        if (!isset($member))
                        {
                            return WDSAPI::echoJsonError('ERROR: registration code not found', 'The given registration code was not valid for the user you entered.');
                        }
                        if($member->mem_fireshield_status=='offered' || $member->mem_fireshield_status=='enrolled')
                        {
                            // Lookup the client for the member.
                            $client = Client::model()->findByPk($member->client_id);
                            if($client->name != 'USAA')
                            {
                                return WDSAPI::echoJsonError('ERROR: registration code not found', 'The given registration code was not an USAA client code you entered.');
                            }
                            $serviceType = $this->getServiceType(2);
                        }
                        else
                        {
                            return WDSAPI::echoJsonError('ERROR: No FS offered/enrolled user', 'The given registration code was not for FS offered/enrolled user.');
                        }
                    }
                }
                else
                {
                    // Lookup the client for the agent.
                    if($data['ServiceOfferingCode'] == 'AP')
                    {
                        $client = Client::model()->findByPk($agent->client_id);
                        $serviceType = $this->getServiceType(3);
                    }
                }
                //Check registration code from old table agent, set $legacy = true
                if(!isset($client))
                {
                    return WDSAPI::echoJsonError('ERROR: client not found', 'The given registration code was not associated with any client.');
                }
                $legacy = true;
        }
       
        $returnArray['error'] = 0; // success
        // Return the registration code .
        $returnArray['registrationCode'] = $data['registrationCode'];

        // Return the service type.
        $returnArray['serviceType'] = $serviceType;

        // Return the client code so the app knows what kind of user is using the app.
        $returnArray['client'] = $client->code;

        // Return the client name so the app knows what kind of user is using the app
        $returnArray['clientName'] = $client->name;

        // Return the client id so the app knows what kind of user is using the app.
        $returnArray['clientId'] = $client->id;

         // Return the legacy .
        $returnArray['legacy'] = $legacy;

        // Return the dynamic question set.
        $returnArray['questionSet'] = Client::model()->getQuestions($client->id);
       
        foreach($returnArray['questionSet'] as $qset)
        {
            
            if($qset['help_uri']!='')
            {
                $helpUrl = Yii::app()->getBaseUrl(true).'/'.$qset['help_uri'];
                $headers = get_headers($helpUrl);
                $headerChecker = substr($headers[0], 9, 3);
                if($headerChecker != "200")
                {
                    $returnArray['helpText'][] = '';
                }
                else
                {
                    $returnArray['helpText'][] = (file_get_contents($helpUrl));
                }
            }
            else
            {
                $returnArray['helpText'][] = '';
            }
        }
        // Optionally, return URL to be shown as a welcome screen in the app.
        if (!empty($client->welcome_screen_url))
        {
            $returnArray['welcomeScreenUrl'] = $client->welcome_screen_url;
        }
        WDSAPI::echoResultsAsJson($returnArray);
	}
    
    public function actionApiGenerateRegistrationCode()
    {
        $returnArray = array();
        $data = NULL;
        $userClient = UserClient::model()->findall();
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
    /*
    * upload assesment
    */
    public function actionApiUploadAssessment()
    {
        set_time_limit(1200);
		ini_set('max_execution_time', '1200');
		ini_set('memory_limit','256M');
		ini_set('post_max_size', '50M');

        //Required params check if not then return
        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'reportGuid')))
            return;

        //check if user if valid
        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            $user = User::model()->find("login_token = '".$data['loginToken']."'");
            if (!isset($user))
            {
                return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
            }
        }
        
        $incoming_report_path = Helper::getDataStorePath() . DIRECTORY_SEPARATOR .'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$data['reportGuid'].".zip";
        $existingReport = FSReport::model()->findByAttributes(array('report_guid'=>$data['reportGuid']));
        //check if report, based on guid, already exists
        $fsReport = FSReport::model()->findByAttributes(array('report_guid'=>$data['reportGuid']));
        //if already exists AND has a status of 'Importing' (which indicates an error happened probably on previous submit) then allow overwrite, otherwise if it exists with another status need to error out
        if(isset($fsReport) && $fsReport->status !== 'Importing')
        {
            return WDSAPI::echoJsonError('ERROR: This GUID report already exists with a status of '.$fsReport->status.', cannot overwrite.', 'The given guid was not valid.');
        }
        //if(is_file($incoming_report_path) || isset($existingReport))
        //{
        //    return WDSAPI::echoJsonError('ERROR: This GUID report already exists, cannot overwrite.', 'The given guid was not valid.');
        //}

        //initial report save before anything starts so that you can still lookup the status of the report.
        if(!isset($fsReport)) //create a new one if it doesn't already exist in importing status
            $fsReport = new FSReport();
        $fsReport->report_guid = $data['reportGuid'];
        if(isset($fsUser))
        {
            $fsReport->fs_user_id = $fsUser->id;
            $fsReport->type = '2.0';
        }
        else
        {
            $fsReport->fs_user_id = $user->id;
            if($user->type=='Second Look')
            {
                $fsReport->type = 'sl';
            }
            else
            {
                $fsReport->type = 'fso';
            }
        }
        $fsReport->status_date = date('Y-m-d H:i:s');
        $fsReport->status = 'Importing';
        
        $fsReport->notes = 'Recieved API Upload request, starting import for new assessment from user with loginToken: '.$data['loginToken'];
        if(!$fsReport->save())
        {
            return WDSAPI::echoJsonError('ERROR: Could not do initial save of report. Details: '.var_export($fsReport->getErrors(), true), 'Error saving Report.');
        }

        $errorMsg = '';
        if(isset($_FILES['assessmentzip'])){
            if (!move_uploaded_file($_FILES['assessmentzip']['tmp_name'], $incoming_report_path))
            {
                return WDSAPI::echoJsonError($errorMsg, 'There was an error recieving the assessmentzip POST param file. DEBUG: '.var_export($_FILES['assessmentzip'], true));
            }
        }
        $errorMsg = $fsReport->import2();

        if (!empty($errorMsg))
        {
            $fsReport->notes .= "\nError occured:\n".substr($errorMsg, 0, 2000);
            $fsReport->save();
            return WDSAPI::echoJsonError($errorMsg, 'Error importing Assessment Report');
        }
        else
            WDSAPI::echoResultsAsJson(array('error'=>0));
    }
    
     /**
     * Uploads a new assessment.
     * IIS URL Rewrite Rule: api/fireshield/v2/newUploadAssessment/
     */
	public function actionApiNewUploadAssessment()
	{
		set_time_limit(1200);
		ini_set('max_execution_time', '1200');
		ini_set('memory_limit','256M');
		ini_set('post_max_size', '50M');

		$guid = trim(com_create_guid(), '{}');
        $incoming_report_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$guid.'.zip';

		if (!move_uploaded_file($_FILES['assessmentzip']['tmp_name'], $incoming_report_path))
		{
			return WDSAPI::echoJsonError("ERROR: saving uploaded file");
		}

		$fsReport = new FSReport();
		$importError = $fsReport->import($guid);

		if ($importError)
		{
			return WDSAPI::echoJsonError($importError);
		}

		$returnArray = array();
		$returnArray['error'] = 0;
		$returnArray['data'] = array("reportGuid"=>$guid);

		WDSAPI::echoResultsAsJson($returnArray);
	}
    
    /*
    * Create Fireshield user
    */
    public function actionApiCreateFSUser()
    {
        $data = NULL;
        $returnArray = array();
        if (!WDSAPI::getInputDataArray($data, array('emailAddress', 'firstName', 'lastName', 'registrationCode')))
            return;
        $returnArray['error'] = 0;
        WDSAPI::echoResultsAsJson($returnArray);
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

        $return_array['error'] = 0; // successapiLogin
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
     * Creates a new user via the api 
     * @return string JSON of results
     */
    public function actionApiCreateUser()
	{
        $data = NULL;
        if (!WDSAPI::getInputDataArray($data, array('emailAddress', 'firstName', 'lastName', 'userName', 'password', 'registrationCode', 'platform', 'type')))
            return;
        if(!isset($data['registrationCode']) || ($data['registrationCode']==''))
        {
             return WDSAPI::echoJsonError ('ERROR: registration code was empty', 'Registration code can not be empty.');
        }
        if(!isset($data['emailAddress']) || ($data['emailAddress']==''))
        {
             return WDSAPI::echoJsonError ('ERROR: email address was empty', 'The given email address can not be empty.');
        }
        $registrationCode = RegistrationCode::model()->find("code = '".$data['registrationCode']."'");
        if (!isset($registrationCode))
        {
            // FS User validating email with respect to registratio code
            $validateFSuserEmail = Member::model()->validateFSUserEmail($data['registrationCode'], $data['emailAddress']);
            
            // If email is different return error message for FS Users
            if(!$validateFSuserEmail)
            {
                return WDSAPI::echoJsonError ('ERROR: email address does not exists', 'The given email address does not exist.');
            }

            //Duplicate Email check
            // Check to see if email is already in User table.
            $userEmailCheck = User::model()->find("email = '".$data['emailAddress']."'");
            // Check to see if email is already in FS Users table.
            $emailCheck = FSUser::model()->find("email = '".$data['emailAddress']."'");
            $fsmember = Member::model()->find("(email_1 = '".$data['emailAddress']."' OR email_2 ='".$data['emailAddress']."') AND fs_carrier_key = '".$data['registrationCode']."' AND client_id = 1  AND (mem_fireshield_status = 'offered' OR mem_fireshield_status = 'enrolled')");
            if(isset($userEmailCheck) || isset($emailCheck))
            {
                if(isset($fsmember))
                {
                  return WDSAPI::echoJsonError ('ERROR: Email address already is use', 'Email address already in use. Would you like to:'); 
                }
                else
                {
                    return WDSAPI::echoJsonError ('ERROR: email address was already taken', 'The given email address already taken.');
                }            
            }

            if(!isset($fsmember))
            {
                $fsUser = new FSUser();
                // Check to see if last name and carrier key match a record in the database.
                $member = Member::model()->find("last_name = '".$data['lastName']."' AND fs_carrier_key = '".$data['registrationCode']."'");
            
                if (!isset($member))
                {
                    $agent = Agent::model()->find("last_name = '".$data['lastName']."' AND fs_carrier_key = '".$data['registrationCode']."'");
                }

                if (!isset($member) && !isset($agent))
                {
                    return WDSAPI::echoJsonError('Failed to find a member or agent!', 'The user information entered was invalid. Please try again or contact technical support for further assistance.');
                }
                $salt = $fsUser->generateSalt();
                $vendor_id = trim(com_create_guid(), '{}');
                $attributes = array('email'=>$data['emailAddress'],
                                'first_name'=>$data['firstName'],
                                'last_name'=>$data['lastName'],
                                'password'=>$fsUser->hashPassword($data['password'],$salt),
                                'salt'=>$salt,
                                'login_token'=>md5($salt),
                                'vendor_id'=>$vendor_id,
                                'platform'=>$data['platform']);

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
            }
            else
            {
                $client = Client::model()->findByPk($fsmember->client_id);
                $memberId = $fsmember->mid;
                $type = '';
                if($data['type']== 'FS' || $data['type']== 'FS Offered')
                {
                    $type = 'FS Offered';
                }
                if(!isset($client))
                {
                    return WDSAPI::echoJsonError('ERROR: client not found', 'No client found for the given registration code you have entered.');
                }
               $user = new User();
               if(($fsmember->email_1!='')&&($fsmember->email_2!=''))
               {                
                    $emailCheck = User::model()->find("email = '".$fsmember->email_1."' OR email = '".$fsmember->email_2."'");
               }
               elseif(($fsmember->email_1=='')&&($fsmember->email_2!=''))
               {                
                    $emailCheck = User::model()->find("email = '".$fsmember->email_2."'");
               }
               elseif(($fsmember->email_1!='')&&($fsmember->email_2==''))
               {
                    $emailCheck = User::model()->find("email = '".$fsmember->email_1."'");
               }
               $username = User::model()->find("username = '".$data['userName']."'");
               if (isset($emailCheck))
                    return WDSAPI::echoJsonError ('ERROR: Email address already is use', 'Email address already in use. Would you like to:');
               if (isset($username))
                    return WDSAPI::echoJsonError ('ERROR: username was already in the Users table', 'The given username already has an account associated with it.'); 
               $salt = $user->generateSalt();
               $vendor_id = trim(com_create_guid(), '{}');
               $attributes = array('email'=>$data['emailAddress'],
                            'name'=>$data['firstName']." ".$data['lastName'],
                            'username'=>$data['userName'],
                            'password'=>$user->hashPassword($data['password'],$salt),
                            'salt'=>$salt,
                            'login_token'=>md5($salt),
                            'vendor_id'=>$vendor_id,
                            'type'=>$type,
                            'member_mid'=>$memberId,
                            'client_id'=>$client->id
                            );

                $user->attributes = $attributes;
                if ($user->save())
                {  
                    $returnArray['error'] = 0; // success
                    $returnArray['data']['userId'] = $user->id;
                    $returnArray['data']['loginToken'] = $user->login_token;
                    $returnArray['data']['vendorID'] = $user->vendor_id;
                    $returnArray['data']['serviceType'] = $data['type'];
                    $returnArray['data']['questionSet'] = Client::model()->getQuestions($client->id);
                    foreach($returnArray['data']['questionSet'] as $qset)
                    {
                        $helpUrl = Yii::app()->getBaseUrl(true).'/'.$qset['help_uri'];
            
                        if($qset['help_uri']!='')
                        {
                            $headers = get_headers($helpUrl);
                            $headerChecker = substr($headers[0], 9, 3);
                            if($headerChecker != "200")
                            {
                                $returnArray['data']['helpText'][] = '';
                            }
                            else
                            {
                                $returnArray['data']['helpText'][] = (file_get_contents($helpUrl));
                            }
                        }
                        else
                        {
                            $returnArray['data']['helpText'][] = '';
                        }
                    }
             
                }
                else
                {
                    return WDSAPI::echoJsonError('ERROR: creating a new user with given attributes.', NULL, 2);
                }
            }
        }
        else
        {
           $clientCode = substr($data['registrationCode'], 2, 2);
           $type = '';
           if($data['type']== 'SL' || $data['type']== 'Second Look')
           {
                $type = 'Second Look';
           }
           $client = Client::model()->find("client_reg_code = ".$clientCode);
           $user = new User();
           $emailCheck = User::model()->find("email = '".$data['emailAddress']."'");
           $username = User::model()->find("username = '".$data['userName']."'");
           if (isset($emailCheck))
                return WDSAPI::echoJsonError ('ERROR: email address was already in the Users table', 'The given email address already has an account associated with it.'.$data['emailAddress']);
           if (isset($username))
                return WDSAPI::echoJsonError ('ERROR: username was already in the Users table', 'The given username already has an account associated with it.'); 
           $salt = $user->generateSalt();
           $vendor_id = trim(com_create_guid(), '{}');
           $attributes = array('email'=>$data['emailAddress'],
                            'name'=>$data['firstName']." ".$data['lastName'],
                            'username'=>$data['userName'],
                            'password'=>$user->hashPassword($data['password'],$salt),
                            'salt'=>$salt,
                            'login_token'=>md5($salt),
                            'vendor_id'=>$vendor_id,
                            'type'=>$type,
                            'client_id'=>$client->id,
                            'registration_code_id'=>$registrationCode->id
                            );

            $user->attributes = $attributes;
            
            if ($user->save())
            {
                $client = Client::model()->findByPk($client->id); 
                $returnArray['error'] = 0; // success
                $returnArray['data']['userId'] = $user->id;
                $returnArray['data']['loginToken'] = $user->login_token;
                $returnArray['data']['vendorID'] = $user->vendor_id;
                $returnArray['data']['serviceType'] = $data['type'];
                $returnArray['data']['questionSet'] = Client::model()->getQuestions($client->id);
                foreach($returnArray['data']['questionSet'] as $qset)
                {
                    $helpUrl = Yii::app()->getBaseUrl(true).'/'.$qset['help_uri'];
            
                    if($qset['help_uri']!='')
                    {
                        $headers = get_headers($helpUrl);
                        $headerChecker = substr($headers[0], 9, 3);
                        if($headerChecker != "200")
                        {
                            $returnArray['data']['helpText'][] = '';
                        }
                        else
                        {
                            $returnArray['data']['helpText'][] = (file_get_contents($helpUrl));
                        }
                    }
                    else
                    {
                        $returnArray['data']['helpText'][] = '';
                    }
                }
             
            }
            else
            {
                return WDSAPI::echoJsonError('ERROR: creating a new user with given attributes.', NULL, 2);
            }
        }
        WDSAPI::echoResultsAsJson($returnArray);
    }
     /**
     * Logs in a user for a given username/password and registration code
     *  @return string JSON of results
     */
    public function actionApiUserLogin()
    {
        $data = NULL;
        if (!WDSAPI::getInputDataArray($data, array('username', 'password', 'ServiceOfferingCode')))
            return;
        $returnArray = array();
        //check email for new user
        $user = User::model()->find("email='".$data['username']."'");
        if($data['ServiceOfferingCode'] == 'SL')
        {
            if(!$user)
            {
                return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
            }
            else
            {
                $usertype = array();
                if($user->login_token == NULL)
                {
                    $salt = $user->salt;
                    $user->login_token = md5($salt);
                    $user->save();
                }
                if($user->vendor_id == NULL)
                {
                    $vendor_id = trim(com_create_guid(), '{}');
                    $user->vendor_id = $vendor_id;
                    $user->save();
                }
                $usertype = explode(",", $user->type);

                if(in_array("FS Offered", $usertype))
                {
                    return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
                }
                $returnArray['error'] = 0; // success
                $returnArray['data'] = array(
                    'loginToken'=>$user->login_token,
                    'vendorID'=>$user->vendor_id,
                    'userId'=>$user->id,
                    'serviceType'=>$this->getServiceType(1)
                );
                
            }
            
        }
        elseif($data['ServiceOfferingCode'] == 'FS')
        {
            if(!$user)
            {
                return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
            }
            else
            {$returnArray['error'] = 0; // success
                $usertype = explode(",", $user->type);

                if(in_array("Second Look", $usertype))
                {
                    return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
                }
                if(!$user->client_id)
                {
                    return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
                }
                    if($user->login_token == NULL)
                    {
                        $salt = $user->salt;
                        $user->login_token = md5($salt);
                        $user->save();
                    }
                    if($user->vendor_id == NULL)
                    {
                        $vendor_id = trim(com_create_guid(), '{}');
                        $user->vendor_id = $vendor_id;
                        $user->save();
                    }
                    $returnArray['error'] = 0; // success
                    $returnArray['data'] = array(
                        'loginToken'=>$user->login_token,
                        'vendorID'=>$user->vendor_id,
                        'userId'=>$user->id,
                        'serviceType'=>$this->getServiceType(2),
                        'questionSet'=>Client::model()->getQuestions($user->client_id)
                        );
             }
        }
        elseif($data['ServiceOfferingCode'] == 'AP')
        {
            if ((!isset($user) || $user->password !== $user->hashPassword($data['password'],$user->salt)))
		    {
                //check email for existing/old user
                $fsUser = FSUser::model()->find("email='".$data['username']."'");

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

                $returnArray['error'] = 0; // success
                $returnArray['data'] = array(
                    'loginToken'=>$fsUser->login_token,
                    'vendorID'=>$fsUser->vendor_id,
                    'properties'=>$fsUser->getProperties(),
                    'client'=>$client->code,
                    'questionSet'=>Client::model()->getQuestions($client->id),
                    'type'=>"App2.0",
                    'serviceType'=>$this->getServiceType(3)
                );

                if (isset($fsUser->member))
                {
                    if ($fsUser->member->is_tester)
                    {
                        $returnArray['data']['trialExpireDate'] = (new DateTime($fsUser->member->trial_expire_date))->format('Y-m-d H:i');
                    }
                }
            }
            else
            {
                return WDSAPI::echoJsonError('ERROR: login and/or password were incorrect.', 'Login and/or password were incorrect.');
            }
                        
        }
        
        WDSAPI::echoResultsAsJson($returnArray);
    }
    /**
     * Set new password for given email
     *  @return string JSON of results
     */
    public function actionApiForgotPassword()
    {
        $data = NULL;
        if (!WDSAPI::getInputDataArray($data, array('email')))
            return;
        // Check email.
        $user = User::model()->find("email='".$data['email']."'");
        if (!isset($user))
		{
            return WDSAPI::echoJsonError('ERROR: Invalid Email.', 'Sorry! this email does not exist.');
        }
        else
        {
            $returnArray['error'] = 0; // success
            $salt = $user->generateSalt();
            $newPass = "test123";
            $resetPass = $user->hashPassword($newPass, $salt);
			$user->password = $resetPass;
			$user->salt = $salt;
            $user->save();
            $returnArray['password'] = $newPass;
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
        $returnArray = array();
        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'streetAddress', 'city', 'state', 'zip')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            $user = User::model()->find("login_token = '".$data['loginToken']."'");
            if (!isset($user))
            {
                return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.');
            }
            else
            {
               $sql = "select member_mid from [user] where login_token = '". $data['loginToken']."'";
               $member_id = Yii::app()->db->createCommand($sql)->queryScalar();
               
               if(!$member_id)
               {
                    $name = explode(" ", $user->name); 
                    $mem_number = 'MEM1';
                    $mem_number = Member::model()->checkMember($mem_number);
                    $member = new Member;
                    $member->attributes = array('first_name' => $name[0],
                        'last_name' => $name[1],
                        'email_1' => $user->email,
                        'client_id' => $user->client_id,
                        'member_num' => $mem_number,
                        'mail_address_line_1' => $data['streetAddress'],
                        'mail_city' => $data['city'],
                        'mail_state' => $data['state'],
                        'fs_carrier_key' => '',
                        'mail_zip' => $data['zip']
                    );
                      
                   if (!$member->save())
                   {
                        return WDSAPI::echoJsonError('ERROR: failed to save the member', NULL, 4);
                   }
                    $user->member_mid = $member->mid;
                   if (!$user->save())
                   {
                        return WDSAPI::echoJsonError('ERROR: failed to save the user', NULL, 3);
                   }
                   $memberId = $member->mid;
               }
               else
               {
                    $memberId = $member_id;
               }
               $policyholderName = !empty($data['policyholderName']) ? $data['policyholderName'] : NULL;
               $properties_type = PropertiesType::model()->find("type ='Second Look'");
               $property = new Property;
               $property->attributes = array(
                'policyholder_name' => ($policyholderName),
                'member_mid' => ($memberId),
                'policy' => 'N/A',
                'address_line_1' => $data['streetAddress'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'type_id' => $properties_type->id,
                 'app_status' => 'active'
                );
                if (!$property->save())
                {
                    return WDSAPI::echoJsonError('ERROR: failed to save the property', NULL, 4);
                }
                $returnArray['error'] = 0; // success
                $returnArray['data'] = array("agentPropertyID" => $property->pid);
               // $returnArray['data1'] = $msql;
            }
        }
        else
        {
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
            $returnArray['error'] = 0; // success
            $returnArray['data'] = array("agentPropertyID" => $agentProperty->id);
        }
        
        
        

        WDSAPI::echoResultsAsJson($returnArray);
    }
    public function actionApiGetQuestionSets()
    {
        $data = NULL;
        $returnArray = array();
        $set_id = null;
        if (!WDSAPI::getInputDataArray($data, array('loginToken')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");
        if (!isset($fsUser))
        {
             $user = User::model()->find("login_token = '".$data['loginToken']."'");
             if (!isset($user))
             {
                 return WDSAPI::echoJsonError('ERROR: User Not Found.', 'Could not find user based on provided loginToken.', 1);
             } 
             $client_id = $user->client_id;
             
        }
        else
        {
            if(isset($fsUser->member->client_id))
                $client_id = $fsUser->member->client_id;
            elseif(isset($fsUser->agent->client_id))
                $client_id = $fsUser->agent->client_id;
            else
                return WDSAPI::echoJsonError('ERROR: App User Client Not Found.', 'Could not find a client for the app user that was found based on the loginToken.', 1);

            }            
            if(isset($data['setID']))
            {
                if(empty($data['setID'])) //if blank set id passed in, then use default set
                {
                    $defaultSet = ClientAppQuestionSet::model()->findByAttributes(array('client_id'=>$client_id, 'is_default'=>1, 'active'=>1));
                    $set_id = $defaultSet->id;
                }
                else
                    $set_id = $data['setID'];
            }

            $criteria = new CDbCriteria();
            $criteria->condition = "active = 1 AND type = 'field' AND client_id = ".$client_id;
            if(isset($set_id))
              $criteria->condition .= " AND set_id = ".$set_id;
            $criteria->order = 'order_by';
            $questions = FSAssessmentQuestion::model()->findAll($criteria);

            if (!isset($questions) || count($questions) == 0)
            {
                return WDSAPI::echoJsonError("ERROR: questions not found for the given client and/or set_id.".$set_id);
            }

            $sets = array();
            foreach($questions as $question)
            {
                $clientQuestionSet = ClientAppQuestionSet::model()->findByPk($question->set_id);
                if(!array_key_exists($question->set_id, $sets))//if not already in sets array then need to add it
                    $sets[$question->set_id] = array("ID"=>$clientQuestionSet->id, "Name"=>$clientQuestionSet->name, "Sections"=>array());

                if(!array_key_exists($question->section_type, $sets[$question->set_id]["Sections"])) //if not already in sections array then need to add it
                    $sets[$question->set_id]["Sections"][$question->section_type] = array('Title'=>$question->getSectionTitle(), 'Order'=>$question->section_type, 'Questions'=>array());

                $sets[$question->set_id]["Sections"][$question->section_type]['Questions'][] = array(
                        'ID'=>$question->id,
                        'Number'=>$question->question_num,
                        'SetID'=>$question->set_id,
                        'Order'=>intval($question->order_by),
                        'Label'=>$question->label,
                        'Title'=>$question->title,
                        'Description'=>utf8_encode($question->description),
                        'QuestionText'=>utf8_encode($question->question_text),
                        'HelpURI' => $question->help_uri,
                        'HelpText' => $question->overlay_image_help_text,
                        'PhotoText' => $question->photo_text,
                        'RequiredPhotos' => $question->number_of_required_photos,
                        'AllowNotes' => $question->allow_notes,
                        'ChoicesType' => $question->choices_type,
                        'Choices' => $question->getChoicesArray(),
                );
            }

            //remove section keys needed for sorting above
            $return_sets = array();
            foreach($sets as $set)
            {
                $return_sections = array();
                foreach($set["Sections"] as $section)
                    $return_sections[] = $section;
                $set["Sections"] = array_values($return_sections);
                $return_sets[] = $set;
            }
            $debug = var_export($sets,true);
            $returnArray['error'] = 0; // Success
            if(isset($set_id))
                $returnArray['data'] = array('Sections'=>$return_sets[0]['Sections']);
            else
                 $returnArray['data'] = array('Sets'=>$return_sets);
            WDSAPI::echoResultsAsJson($returnArray);
    }
	/*
     * Gets the properties for the current login user.
     * IIS URL Rewrite rule: api/fireshield/v2/getProperties
     */
    public function actionApiGetProperties()
	{
        $data = NULL;
        $returnArray = array();
        if (!WDSAPI::getInputDataArray($data, array('loginToken')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");
        if (!isset($fsUser))
        {
            $user = User::model()->find("login_token = '".$data['loginToken']."'");
            if (!isset($user))
            {
                return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.');
            }
            else
            {
                $returnArray['error'] = 0; // success
                $returnArray['data'] = array('properties'=>$user->getProperties());
            }               
        }
        else
        {
            $returnArray['error'] = 0; // success
            $returnArray['data'] = array('properties'=>$fsUser->getProperties());
        }

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

        //check if user if valid
        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            $user = User::model()->find("login_token = '".$data['loginToken']."'");
            if (!isset($user))
            {
                return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
            }
        }
        $returnArray['error'] = 0; // success

        if (isset($fsUser))
        {
            $returnArray['data'] = array('assessments'=>$fsUser->getAssessments());
        }
        if (isset($user))
        {
            $returnArray['data'] = array('assessments'=>$user->getAssessments());
        }
		
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
        {
            $user = User::model()->find("login_token = '".$data['loginToken']."'");
            if (!isset($user))
            {
                return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.');
            }
            else
            {
                $member = Member::model()->findByPk($user->member_mid);
                if (!isset($member))
                    return WDSAPI::echoJsonError('ERROR: could not find the member. Only member users can update properties.', 'ERROR: This user type is not allowed to update properties');
                $property = Property::model()->findByPk($data['agentPropertyID']);
                if(!isset($property))
                    return WDSAPI::echoJsonError('ERROR: could not find any property based on given propertyID.', 'ERROR: Could not find property to update.');
                if(isset($data['streetAddress']))
                    $property->address_line_1 = $data['streetAddress'];
                if(isset($data['city']))
                    $property->city = $data['city'];
                if(isset($data['state']))
                    $property->state = $data['state'];
                if(isset($data['zip']))
                    $property->zip = $data['zip'];
                if(isset($data['questionSetID']))
                    $property->question_set_id = $data['questionSetID'];
                if(isset($data['policyholderName']))
                    $property->policyholder_name = $data['policyholderName'];
                if(isset($data['status']) && ($data['status'] == 'active' || $data['status'] == 'canceled'))
                {
                
                   if($data['status'] == 'canceled')
                   {
                        $property->app_status = 'removed';
                   }
                   else
                   {
                       $property->app_status = $data['status'];
                   }
                    
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

                if (!$property->save())
                {
                    return WDSAPI::echoJsonError('ERROR: failed to save the member property', NULL, 2);
                }
            }
        }
        else
        {
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
        }
        $returnArray = array();
        $returnArray['error'] = 0; // success
        $returnArray['data'] = array("agentPropertyID" => $data['agentPropertyID']);
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

    //new App2 reset pass actions (requestResetApp2UserPW, resetApp2UserPW)
    public function actionRequestResetUserPW()
    {
        $error = null;
        $message = 'Please enter your WDSPro username and an email will be sent to your address on file with reset instructions:';
        if(isset($_POST['username']))
        {

            $fsUser = FSUser::model()->findByAttributes(array('email'=>$_POST['username']));
            if(!isset($fsUser))
            {
                $user = User::model()->findByAttributes(array('email'=>array($_POST['username'])));
                if(!$user || $_POST['username']=='')
                {
                    $error = 1;
                    $message = '<span style="color:red">Username does not exist. Please contact WDS for support if you do not know your username.</span>';
                }
                else
                {
                    $reset_token = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 5).time();
                    $user->reset_token = $reset_token;
                    if(!$user->save())
                    {
                        $error = 2;
                        $message = '<span style="color:red">Error setting reset token. Please contact WDS for support if error persists.</span>';
                    }
                    else //successfully set reset token
                    {
                        $subject = 'WDSPro Password Reset Request';
                        $body = "A password reset was issued for your WDSPro App user account tied to this email address. Please click the below link to reset your password. \r\n\r\nIf you did not request your password to be reset, please ignore this email and your password will remain the same.\r\n\r\n";
                        $body .= Yii::app()->getBaseUrl(true).Yii::app()->createUrl('app/resetUserPW', array('reset_token'=>$reset_token));
                        $sendMailResult = Helper::sendEmail($subject, $body, $user->email);
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
                    $body .= Yii::app()->getBaseUrl(true).Yii::app()->createUrl('app/resetUserPW', array('reset_token'=>$reset_token));
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
		$this->render('request_reset_pw', array('error'=>$error, 'message'=>$message));
	}

    public function actionResetUserPW($reset_token)
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
                    $user = User::model()->findByAttributes(array('reset_token'=>$reset_token));
                    if(!$user)
                    {
                        $error = 1;
                        $message = '<span style="color:red">Error: Invalid Reset Token.</span>';
                    }
                    else
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
                            $user->password = $user->hashPassword($_POST['new_pass'], $user->salt);
                            $user->reset_token = null;
                            if($user->save())
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
		$this->render('reset_pw', array('error'=>$error, 'message'=>$message, 'reset_token'=>$reset_token));
    }
    // Array to get service type text
    public function getServiceType($id)
    {
        $serviceTypeArray = array('1'=>"Second Look",'2'=>"FireShield",'3'=>"App2");
        return $serviceTypeArray[$id];
    }
}