<?php

class SiteController extends Controller
{

	/**
     * Declares class-based actions.
     */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
     * @return array action filters
     */
	public function filters()
	{
		return array(
			'accessControl'
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
                    
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types)',
			),
			array('allow',
				'actions'=>array(
                    'getImage',
                    'deleteAttachment',
                    'shiftplanner',
                    'selectedDate',
                    'error',
                    'wiki'
                ),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array(
                    'index',
                    'login',
                    'logout',
                    'getJpegImageRotate',
                    'getImageToken',
                    'page',
					'verification',
					'verification2',
					'verification3'
                ),
				'users'=>array('*')
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    public function behaviors()
    {
        // Only 'error' action is being logged

        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger',
                'blacklist' => array('index'),
            )
        );
    }

    public function actionPage()
    {
        $view = $_GET['view'];

        // Make sure there is no layout for the offline cache page.
        if ($view == 'offlineCache')
        {
            $this->layout = false;
        }

        $this->render("pages/$view");
    }

    public function actionWiki($page = '')
    {
        echo '<b>WDS API Wiki Docs:</b><br>';
        echo CHtml::link('- Check Carrier Code 2 -', array('site/wiki', 'page'=>'WDS_API_-_2.0_Check_Carrier_Code'));
        echo ' | '.CHtml::link('- Contact Us Entry - ', array('site/wiki', 'page'=>'WDS_API_-_Contact_Us_Create_Entry'));
        echo ' | '.CHtml::link('- Create Account - ', array('site/wiki', 'page'=>'WDS_API_-_2.0_Create_Account'));
        echo ' | '.CHtml::link('- User Login - ', array('site/wiki', 'page'=>'WDS_API_-_FS_User_Login'));
        echo '<br>';
        echo CHtml::link('- Get Properties -', array('site/wiki', 'page'=>'WDS_API_-_Get_Properties'));
        echo ' | '.CHtml::link('- Add Property -', array('site/wiki', 'page'=>'WDS_API_-_Create_Property'));
        echo ' | '.CHtml::link('- Update Property -', array('site/wiki', 'page'=>'WDS_API_-_Update_Property'));
        echo ' | '.CHtml::link('- Get Assessments - ', array('site/wiki', 'page'=>'WDS_API_-_Get_Assessments'));
        echo '<br>';
        echo CHtml::link('- Get Question Set 2 -', array('site/wiki', 'page'=>'WDS_API_-_2.0_Get_Question_Set'));
        echo ' | '.CHtml::link('- Upload Assessment 2 - ', array('site/wiki', 'page'=>'WDS_API_-_2.0_Upload_Assessment'));
        echo ' | '.CHtml::link('- Get Assessment Status 2 - ', array('site/wiki', 'page'=>'WDS_API_-_2.0_Get_Assessment_Status'));
        echo ' | '.CHtml::link('- Download Assessment 2 - ', array('site/wiki', 'page'=>'WDS_API_-_2.0_Download_Assessment'));

        echo "<br>-----------<br>";
        if(!empty($page))
        {
            $wds_wiki = file_get_contents('http://wiki.wildfire-defense.com/index.php/'.$page);
            $wds_wiki = substr($wds_wiki, strpos($wds_wiki, '<h1 id="firstHeading"'));
            $wds_wiki = substr($wds_wiki, 0, strpos($wds_wiki, '<div id="mw-navigation"'));
            echo $wds_wiki;
        }
    }

	public function actionFileBrowser() {

        $root = '';

        $_POST['dir'] = urldecode($_POST['dir']);

        if (file_exists($root . $_POST['dir'])) {
            $files = scandir($root . $_POST['dir']);
            natcasesort($files);
            if (count($files) > 2) { /* The 2 accounts for . and .. */
                echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
                // All dirs
                foreach ($files as $file) {
                    if (file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file)) {
                        echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
                    }
                }
                // All files
                foreach ($files as $file) {
                    $path = htmlentities($_POST['dir'] . $file);
                    $junk = substr($path, 0, strpos($path, 'images'));

                    $rUrl = str_replace($junk, '', $path);

                    if (file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file)) {
                        $ext = preg_replace('/^.*\./', '', $file);
                        if ($ext == "jpeg" || $ext == "jpg" || $ext == "gif" || $ext == "png" || $ext == "JPEG" || $ext == "JPG" || $ext == "GIF" || $ext == "PNG")
                            echo "<li><a href=\"#\" rel=\"" . $rUrl . "\"><img src=\"" . Yii::app()->createUrl('site/getImage', array('filepath' => $rUrl)) . "\" height=60 width=60>" . htmlentities($file) . "</img></a></li>";
                    }
                }
                echo "</ul>";
            }
        }
    }

    /**
     * Delete images via ajax call
     */
    public function actionDeleteAttachment()
    {
        if(isset($_POST['url'])){
            $url = $_POST['url'];
            unlink($url);
            $this->renderPartial('//assessment/photos');
        }
    }


	/**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
	public function actionIndex()
	{
		// uses the default layout 'protected/views/layouts/main.php'

        //If manager or admin than show the department analtyics, otherwise just show the generic index
        if(!Yii::app()->user->isGuest && (in_array('Analytics', Yii::app()->user->types)))
        {
            $clients = Client::model()->findAllBySql('select * from client where active = 1 order by name asc;');
            $fireData = ResNotice::getDispatchedFireList();
            
		    $this->render('indexAnalytics', array(
                'clients' => $clients,
                'fireData' => $fireData
            ));
        }
        //Generic index page
        else
        {
            $this->render('index');
        }
	}

	/**
     * Displays a view with the embedded shift planner
     */
	public function actionShiftplanner()
	{
		$this->render('shiftplanner');
	}

	//retrieves selected date based on get vars
	private function getSelectedDate()
	{
		$year = $month = $day = null;
		$selected_date = null;

		if(isset($_GET["year"]) && isset($_GET["month"]) && isset($_GET["day"])) {
			$year = filter_input(INPUT_GET, "year", FILTER_VALIDATE_INT);
			$month = filter_input(INPUT_GET, "month", FILTER_VALIDATE_INT);
			$day = filter_input(INPUT_GET, "day", FILTER_VALIDATE_INT);
		}

		if($year && $month && $day) {
			$selected_date = "$year-$month-$day ";
		}
		else
		{
			$selected_date = date('Y-m-d');
		}

		return $selected_date;
	}

	/**
     * This is the action to handle external exceptions.
     */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
     * Displays the contact page
     */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
     * Displays the login page
     */
	public function actionLogin()
	{
		$model=new LoginForm;

        //Already logged in, so just redirect to main page
        if (!Yii::app()->user->isGuest)
        {
            $this->redirect(Yii::app()->createUrl('site/index'));
        }

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{ 
			$model->attributes=$_POST['LoginForm'];
			$username = base64_encode($model['username']);
			$password = base64_encode($model['password']);
			$user = User::model()->findByAttributes(array('username' => $model['username']));
			$UserGUID = $user['UserGUID'];
			$MFAMethodDefault = $user['MFAMethodDefault']; //call default client

			//checking if user is not present in emF_User, then set UserGUID = null and then call WDSAPIG2
			$connection = Yii::app()->db;
			$sql = "SELECT * from emF_User where UserGUID = '$UserGUID' ";
			$command = $connection->createCommand($sql);
			$returnGuid = $command->execute();
			if(!$returnGuid)
			{
				$connection=Yii::app()->db;
				$model =  User::model()->findByAttributes(array('id'=>$user['id']));
				$model -> UserGUID = '{00000000-0000-0000-0000-000000000000}';
				$model -> save();
				
			}

			// validate user input and if $UserGUID not valid call G2 API
			if($UserGUID == NULL || $UserGUID=='{00000000-0000-0000-0000-000000000000}')
			{
				$apipass = $model['password'];
				$LastInsertId = $user['id'];
				$provectus = new WDSAPIG2();
				$UserGUID = $provectus->wdsCreatePritiniUser($user, $apipass, $LastInsertId);
			}
			// validate user input and redirect to the previous page if valid
			if($model->validate())
            {
				$wdsStaff = $user['wds_staff']; // fetch wds_staff
				$clientID = Yii::app()->params['defaultClient'];
				if($user['client_id'] != NULL)
				{
					$clientID = $user['client_id']; 
				}
				
					$clientUser = Client::model()->findByAttributes(array('id'=>$clientID));
					$activeClient =  $clientUser['MFAActiveClient'];
					$MFAMessage1 = $clientUser['MFAMessage1'];// fetch MFAMessage1 according to client
					$MFAMessage2 = $clientUser['MFAMessage2'];//fetch MFAMessage2 according to client

					//if $user['MFAActiveUser'] == 0 then direct logged in 
					if($user['MFAActiveUser'] == 0 || $user['MFAActiveUser'] == NULL){
						if($model->login())
						{
							$redirectUrl = '/index.php';
							if (empty($redirectUrl) || $redirectUrl == '/index.php')
								$redirectUrl = '/index.php?site=index';
							$this->redirect($redirectUrl);
						}
					}

				// condition apply for show screen 1(MFAMessage1) for phone no and email
				if(($user['MFAActiveUser'] == 1 && $user['MFAPhoneNumber'] == NULL) && (($wdsStaff == 0 && $clientID != 0 && $activeClient == 1) || ($wdsStaff != 0))) 
				{
					$this->render('verification',array('model'=>$model, 'UserGUID' => $UserGUID, 'username' => $username,
					'password' => $password, 'MFAMessage1' => $MFAMessage1));
				}
				else
				{
					//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
					$mfa = new WDSAPIG2MFA();
					$result = $mfa->wdsApiSendMFAURL($UserGUID, $MFAMethodDefault);
					$this->render('verification2',array('model'=>$model, 'UserGUID' => $UserGUID, 'username' => $username,
					'password' => $password, 'MFAMessage2' => $MFAMessage2,  'MFAMessage3' => ''));
				}
				
            }
			else if(!$model->validate())
            {
				$this->render('login',array('model'=>$model));
			}
		}
		else 
		{
			// display the login form		
			$this->render('login',array('model'=>$model));
		}
	}

	/**
     * Redirect to verification 2nd page.
     */
	public function actionVerification2()
	{
		$model=new LoginForm;
	
		if(isset($_POST['username']))
		{
			 $username = $_POST['username'];
		}
		if(isset($_POST['password']))
		{
			 $password = $_POST['password'];
		}
	
		if(isset($_POST['UserGUID']))
		{
			 $UserGUID = $_POST['UserGUID'];
		}
		$result = false;
		if(isset($_POST['LoginForm']))
		{
			$data = $_POST['LoginForm'];
			$countryCode = '+'.$_POST['countryCode'];
			$phoneNo = $data['phoneNo'];
			$email = $data['email'];
			
			$connection=Yii::app()->db;
			$sql = "UPDATE [user] SET MFAPhoneNumber = '$phoneNo', MFACountryCode = '$countryCode', MFAEmail = '$email'
			WHERE UserGUID = '$UserGUID' ";
			$command = $connection->createCommand($sql);
			$command->execute();

			$userDetails = User::model()->findByAttributes(array('UserGUID' => $UserGUID));
			$MFAMethodDefault = $userDetails['MFAMethodDefault'];
			$clientID = Yii::app()->params['defaultClient'];
			if($userDetails['client_id'] != NULL)
			{
				$clientID = $userDetails['client_id'];
			}	
			$client = Client::model()->findByAttributes(array('id'=>$clientID));
			$MFAMessage2 = $client['MFAMessage2'];

			//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
			$mfa = new WDSAPIG2MFA();
			$result = $mfa->wdsApiSendMFAURL($UserGUID, $MFAMethodDefault);
			if(!$result)
			{
				$connection=Yii::app()->db;
				$sql = "UPDATE [user] SET MFACountryCode = NULL, MFAPhoneNumber = NULL , MFAEmail = NULL
				WHERE UserGUID = '$UserGUID' ";
				$command = $connection->createCommand($sql);
				$command->execute();
			}
		}
		
		if($result)
		{
			$this->render('verification2',array('model'=>$model,'UserGUID'=>$UserGUID,'username' => $username,'password' => $password, 
			'MFAMessage2' => $MFAMessage2,  'MFAMessage3' => ''));
		}
		else
		{
			//error message
			Yii::app()->user->setFlash('error', "Your Credentials is not valid!!");
			$this->render('login',array('model'=>$model));
		}
	}
	/**
     * Redirect to verification 3rd page.
     */
	public function actionVerification3()
	{
		$model=new LoginForm;
		$result = false;
		$sms = '';
		$email = '';
		if(isset($_POST['username']))
		{
			 $username = base64_decode($_POST['username']);
		}
		if(isset($_POST['password']))
		{
			 $password = base64_decode($_POST['password']);
		}
		if(isset($_POST['UserGUID']))
		{
			  $UserGUID = $_POST['UserGUID'];
		}
		if(isset($_POST['resendSMS']))
		{
			$sms = $_POST['resendSMS'];
		}
		if(isset($_POST['resendEMAIL']))
		{
			$email = $_POST['resendEMAIL'];
		}
		//Fetch user and client details using $UserGUID
		$userDetails = User::model()->findByAttributes(array('UserGUID' => $UserGUID));
		$MFAMethodDefault = $userDetails['MFAMethodDefault'];
		$clientID = Yii::app()->params['defaultClient'];
		if($userDetails['client_id'] != NULL)
		{
			$clientID = $userDetails['client_id'];
		}
		$client = Client::model()->findByAttributes(array('id'=>$clientID));
		$MFAMessage2 = $client['MFAMessage2'];
		$MFAMessage3 = $client['MFAMessage3'];

		//$MFAMethodDefault  = 1 for SMS
		if($sms)
		{
			//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault for new access code 
			$MFAMethodDefault = 1; 
			$mfa = new WDSAPIG2MFA();
			$result = $mfa->wdsApiSendMFAURL($UserGUID, $MFAMethodDefault);
			if($result)
			{
				$this->render('verification2',array('model'=>$model,'UserGUID'=>$UserGUID, 'username' => base64_encode($_POST['username']),'password' => base64_encode($_POST['password']), 'MFAMessage2' => $MFAMessage2,  'MFAMessage3' => ''));
			}
		}
		//$MFAMethodDefault  = 0 for EMAIL
		if($email)
		{
			//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault for new access code 
			$MFAMethodDefault = 0; 
			$mfa = new WDSAPIG2MFA();
			$result = $mfa->wdsApiSendMFAURL($UserGUID, $MFAMethodDefault);
			if($result)
			{
				$this->render('verification2',array('model'=>$model, 'UserGUID'=>$UserGUID, 'username' => base64_encode($_POST['username']), 'password' => base64_encode($_POST['password']), 'MFAMessage2' => $MFAMessage2,  'MFAMessage3' => ''));
			}
		}
		if(isset($_POST['LoginForm']))
		{
			$model['username'] = $username;
			$model['password'] = $password;
			$model['UserGUID'] = $UserGUID;
			
			$code = $_POST['LoginForm']['code'];
			$mfa = new WDSAPIG2MFA();
			$result = $mfa->wdsApiVerifyMFAURL($UserGUID, $code);
			//if success true then user automatically logged in
			if($result)
			{
			
				if($model->login())
				{
					$redirectUrl = '/index.php';
					if (empty($redirectUrl) || $redirectUrl == '/index.php')
						$redirectUrl = '/index.php?site=index';
					$this->redirect($redirectUrl);
				}
			}
			else
			{
				$this->render('verification2',array('model'=>$model,'UserGUID'=>$UserGUID,'username' => $username,'password' => $password, 
				'MFAMessage2' => '', 'MFAMessage3' => $MFAMessage3));
			}
		}
	}

	/**
     * Logs out the current user and redirect to homepage.
     */
	public function actionLogout()
	{
		Yii::app()->user->logout();

        $this->redirect(Yii::app()->homeUrl);
	}

    /*
     * example: <img src="<?php echo Yii::app()->request->baseUrl.'/Index.php/site/getJpegImageRotate?token=A9er5726rTqncRNC&filepath='.urlencode(Yii::app()->basePath.'\images\logo.jpg') ?>" />
     *
     */
    public function actionGetJpegImageRotate($filepath, $token)
    {
        if($token === 'A9er5726rTqncRNC')
        {
            if (file_exists($filepath))
            {
                header('Content-Type: image/jpeg');
                $source = imagecreatefromjpeg($filepath);

                // Rotate

				$exif_data = @exif_read_data($filepath);

				if(isset($exif_data['Orientation']) && $exif_data['Orientation'] === 6)
					$rotate = imagerotate($source, -90, 0);
				elseif(isset($exif_data['Orientation']) && $exif_data['Orientation'] === 3)
					$rotate = imagerotate($source, 180, 0);
				elseif(isset($exif_data['Orientation']) && $exif_data['Orientation'] === 8)
					$rotate = imagerotate($source, 90, 0);
				else //no orientation changes needed
					$rotate = $source;

                // Output
                imagejpeg($rotate, NULL, 100);

                // Free the memory
                imagedestroy($source);

                exit;
            }
            else
                echo  'not found';
        }
        else
            echo 'unauthorized';
    }

	public function actionGetImage()
	{
		/* example: <img src="<?php echo Yii::app()->request->baseUrl.'/Index.php/site/getImage?filepath='.urlencode(Yii::app()->basePath.'\images\logo.png') ?>" /> */
		$filepath = $_GET['filepath'];
		if (file_exists($filepath)){
			$img = getimagesize($filepath);

			header('Content-Type: '.$img['mime']);
			header('Content-Length: ' . filesize($filepath));
			readfile($filepath);

			exit;
		}
		else
			echo  'not found';
	}

	public function actionGetImageToken($new_width=null, $new_height=null, $convertToJPG=false)
	{
		if($_GET['token'] == '123test')
		{
			$filepath = $_GET['filepath'];
			if (file_exists($filepath))
			{
				if(isset($new_width) && isset($new_height))
				{
					$img = imagecreatefromjpeg($filepath);
					list($width, $height) = getimagesize($filepath);
					$resized_img = imagecreatetruecolor($new_width, $new_height);
					imagecopyresampled($resized_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
					header('Content-Type: image/jpeg');
					imagejpeg($resized_img);
				}
				else
				{
					$img = getimagesize($filepath);
					header('Content-Type: '.$img['mime']);
					header('Content-Length: ' . filesize($filepath));
					readfile($filepath);
				}
				exit;
			}
			else
				echo  'not found';
		}
		else
			echo 'bad token';
	}
}
    
