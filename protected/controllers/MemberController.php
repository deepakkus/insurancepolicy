<?php

class MemberController extends Controller
{
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
                        'apiGetInfo',
                        'apiCheckCarrierCode',
                    ),
                    WDSAPI::SCOPE_USAAENROLLMENT => array(
                        'apiGetInfo'
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
                    'enrollPrint',
                    'update',
                    'create',
                    'trialGenerator'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types) || in_array("Manager",$user->types)',
			),
			array('allow',
				'actions'=>array(
                    'admin',
                    'view'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array(
                    'apiGetInfo',
                    'unenrollProperties',
                    'apiCheckCarrierCode',
                ),
				'users'=>array('*')
            ),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
	public function loadModel($mid)
	{
		$model = Member::model()->findByPk($mid);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    //------------------------------------------------------------- Actions ------------------------------------------------------------
    #region Actions

	/**
	 * Updates a particular model.
	 * @param integer $mid the ID of the model to be updated
	 */
	public function actionUpdate($mid)
	{
		$member = $this->loadModel($mid);
		$fireShieldStatusHistory = $member->getStatusHistory('mem_fireshield_status');

		if(isset($_POST['Member']))
		{
			$member->attributes = $_POST['Member'];
			if($member->save())
			{
				Yii::app()->user->setFlash('success', "Member $mid Updated Successfully!");
				$this->redirect(array('admin',));
			}
		}

		$this->render('update',array(
			'member'=>$member,
			'fireShieldStatusHistory' => $fireShieldStatusHistory,
		));
	}

	/**
	 * Views a particular member details.
	 * @param integer $mid the ID of the member to be viewed
	 */
	public function actionView($mid)
	{
		$member = $this->loadModel($mid);
		$fireShieldStatusHistory = $member->getStatusHistory('mem_fireshield_status');

		$this->render('view',array(
			'member'=>$member,
			'fireShieldStatusHistory' => $fireShieldStatusHistory,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$member = new Member;
        $member->scenario = 'adminCreateForm';
		if(isset($_POST['Member']))
		{
			$member->attributes = $_POST['Member'];
            $client = Client::model()->findByAttributes(array("name" => $member->client));
            $member->client_id = ($client) ? $client->id : null;
			if($member->save())
			{
				Yii::app()->user->setFlash('success', "Member ".$member->mid." Created Successfully!");
				$this->redirect(array('admin',));
			}
		}

		$this->render('create',array(
			'member'=>$member,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin($download = false)
	{
		$members = new Member('search');
		$members->unsetAttributes();  // clear any default values
        //set type_id to default of PIF
        $members->type_id = 1;
		if(isset($_GET['Member']))
		{
			$members->attributes = $_GET['Member'];
		}

        // Default columns to show.
        $columnsToShow = array(
            10 => 'mid',
            20 => 'member_num',
            30 => 'first_name',
            40 => 'last_name',
            50 => 'home_phone',
            60 => 'work_phone',
            70 => 'cell_phone',
            80 => 'email_1',
            90 => 'mail_address_line_1',
            100 => 'mail_city',
            110 => 'mail_state',
            120 => 'mail_zip',
            130 => 'client_id',
            140 => 'type_id',
        );

        if (isset($_GET['columnsToShow']))
        {
            $_SESSION['wds_mem_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        elseif (isset($_SESSION['wds_mem_columnsToShow']))
        {
            $columnsToShow = $_SESSION['wds_mem_columnsToShow'];
        }

		if(!$download)
		{
			$pageSize = 25;
			if (isset($_GET['pageSize']))
			{
				$_SESSION['wds_mem_pageSize'] = $_GET['pageSize'];
				$pageSize = $_GET['pageSize'];
			}
			elseif (isset($_SESSION['wds_mem_pageSize']))
			{
				$pageSize = $_SESSION['wds_mem_pageSize'];
			}
		}

        $sort = 'mid';

        if (isset($_GET['Member_sort']))
        {
        	$_SESSION['wds_mem_sort'] = $_GET['Member_sort'];
            $sort = $_GET['Member_sort'];
        }
        elseif (isset($_SESSION['wds_mem_sort']))
        {
            $sort = $_SESSION['wds_mem_sort'];
        }

        $advSearch = NULL;

        if (isset($_GET['advSearch']))
        {
            $_SESSION['wds_mem_advSearch'] = $_GET['advSearch'];
            $advSearch = $_GET['advSearch'];
        }
        elseif (isset($_SESSION['wds_mem_advSearch']))
        {
            $advSearch = $_SESSION['wds_mem_advSearch'];
        }
		else //default, advSearch not set
		{
			$advSearch = array();
			$advSearch['fs_statuses'] = array('not enrolled'=>'not enrolled', 'ineligible'=>'ineligible', 'offered'=>'offered', 'enrolled'=>'enrolled', 'declined'=>'declined',);
		}

		if($download)
		{
			$members->makeDownloadableReport($columnsToShow, $advSearch, $sort);
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".Yii::app()->user->name."_MemReport.csv\"");
			readfile(Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_MemReport.csv');
		}
		else
		{
			$this->render('admin',array(
				'members' => $members,
				'columnsToShow' => $columnsToShow,
				'pageSize' => $pageSize,
				'advSearch' => $advSearch,
				'sort' => $sort,
			));
		}
	}

    /*
     * Displays a printable paper-copy of the USAA enrollment form for a specific member.
     */
    public function actionEnrollPrint($mid)
    {
        $this->setPageTitle('USAA/WDS Response Enrollment | Wildfire Defense Systems');
        $this->layout = '//layouts/printable';

        $member = $this->loadModel($mid);

		$this->render('enrollPrint',array(
			'member' => $member,
		));
    }

    /**
     * Displays the trial generator view for creating multiple trial members/properties.
     * POST handles the generation of the trials.
     */
    public function actionTrialGenerator()
    {
        $member = new Member();
        $property = new Property();
        $generatorCount = 0;
        $generatedData = array();

        if (isset($_POST['Member']) && isset($_POST['Property']))
		{
            if (isset($_POST['generateCount']))
                $generatorCount = intval($_POST['generateCount']);

            if ($generatorCount <= 0)
            {
                Yii::app()->user->setFlash('error', "Number of members to generate must be greater than zero!");
            }
            else
            {
                $success = true;

                for ($i = 1; $i <= $generatorCount; $i++)
                {
                    $member = new Member();
                    $property = new Property();

                    $member->attributes = $_POST['Member'];
                    $property->attributes = $_POST['Property'];

                    $member->is_tester = 1;
                    $member->last_name = $member->last_name . sprintf('%02d', $i);
                    $member->member_num = $member->member_num . sprintf('%02d', $i);
                    $member->mem_fireshield_status = 'offered';
                    $member->mem_fs_status_date = date('Y-m-d H:i:s');

                    if (!$member->save())
                    {
                        Yii::app()->user->setFlash('error', "Failed to create member $member->member_num!");
                        $success = false;
                        break;
                    }

                    $property->member_mid = $member->mid;
                    $property->address_line_1 = $i . ' ' . $property->address_line_1;
                    $property->fireshield_status = 'offered';
    				$property->fs_status_date = date('Y-m-d H:i:s');
                    $property->response_status = 'not enrolled';
    				$property->res_status_date = date('Y-m-d H:i:s');
                    $property->pre_risk_status = 'not enrolled';
    				$property->pr_status_date = date('Y-m-d H:i:s');
                    $property->policy_status = 'active';
    				$property->policy_status_date = date('Y-m-d H:i:s');

                    if (!$property->save())
                    {
                        Yii::app()->user->setFlash('error', "Failed to create property $property->address_line_1!");
                        $success = false;
                        break;
                    }

                    array_push($generatedData, array(
                        'id' => $property->pid,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'carrier_key' => $member->fs_carrier_key,
                    ));
                }

                if ($success)
                    Yii::app()->user->setFlash('success', "Members created successfully!");
            }
		}
        else
        {
            $member->unsetAttributes();
            $property->unsetAttributes();

            // Set some defaults.
            $member->client = 'Trial';
            $property->city = 'Boston';
            $property->county = 'Suffolk';
            $property->state = 'MA';
            $property->zip = '99999';
            $property->geo_risk = 2;
            $property->fs_assessments_allowed = 1;
            $property->policy = '999';
        }

        $this->render('trialGenerator', array(
            'member' => $member,
            'property' => $property,
            'generatorCount' => $generatorCount,
            'generatedData' => $generatedData,
        ));
    }

    //ONLY should work in dev environment
	public function actionUnenrollProperties($member_num)
	{
		if(Yii::app()->params['env'] == 'dev')
		{
			$member = Member::model()->find("member_num = '$member_num'");
			foreach($member->properties as $property)
			{
				$property->response_status = 'eligible';
				if($property->save())
					echo 'UnEnrolled '.$property->address_line_1.'<br />';
				else
					echo "ERROR unenrolling ".$property->address_line_1."<br />";
			}
			echo 'Done';
		}
		else
			echo "ERROR: this is only availible in the dev environment, you should not be doing this in produciton";
	}

    #endregion

    //-------------------------------------------------------------API Methods------------------------------------------------------------
    #region API

    //currently just for talking to the usaaenrollment.wildfire-defense.com site, but could be used else where.
	public function actionApiGetInfo()
	{
        if (!WDSAPI::getInputDataArray($data, array('member_num','client')))
            return;

		$return_array = array();

		if (!empty($data['spouse_member_num']))
		{
			$member = Member::model()->findByAttributes(array('member_num' => $data['spouse_member_num'], 'client' => $data['client']));
		}
		else
		{
			$member = Member::model()->findByAttributes(array('member_num' => $data['member_num'], 'client' => $data['client']));
		}

		if (isset($member))
		{
			$properties = array();
            $client_states = array();
			foreach ($member->properties as $property)
			{
				//only return if policy is active
				if ($property->policy_status == 'active')
				{
					$properties[] = array(
                        'id' => $property->pid,
                        'address' => $property->address_line_1,
                        'city' => $property->city,
                        'state' => $property->state,
                        'response_status' => $property->response_status,
                        'response_enrolled_date' => $property->response_enrolled_date,
                        'response_auto_enrolled' => $property->response_auto_enrolled
                    );
				}
			}
            $clientStates = ClientStates::model()->findAll(array(
                'select' => 'id, state_id',
                'condition' => 'client_id = :client_id',
                'params' => array(':client_id' => $member->client_id)
            ));
            foreach($clientStates as $client)
            {
                $state = GeogStates::model()->findByAttributes(array(
                    'id'=>$client->state_id
                ));
                $client_states[] = array(
                    'state' => $state->abbr
                );
                rsort($client_states);
            }
			$member_info = array('clientStates' => $client_states, 'first_name' => $member->first_name, 'last_name' => $member->last_name, 'properties' => $properties);
			$return_array['data'] = $member_info;
			$return_array['error'] = 0;
		}
		else
		{
			$return_array['error'] = 1;
			$return_array['errorFriendlyMessage'] = "This member does not exist in Wildfire Defense Systems database.";
			$return_array['errorMessage'] = "ERROR: member # does not exist in WDS database.";
		}

        return WDSAPI::echoResultsAsJson($return_array);
    }

    /**
     * Checks to see if the carrier code (registration code) is valid.
     * LEGACY FUNCTION! USE THE ONE IN FSUSER INSTEAD GOING FORWARD
     */
    public function actionApiCheckCarrierCode()
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
        else
        {
            // Look up the client for the member.
            $client = Client::model()->findByAttributes(array('name' => $member->client));

            // If this is a test member, return a trial expire date, creating one if necessary.
            if ($member->is_tester)
            {
                if (!isset($member->trial_expire_date))
                {
                    $trialExpireDate = new DateTime();
                    $trialExpireDate->add(new DateInterval('P7D')); // Add 7 days
                    $member->trial_expire_date = $trialExpireDate->format('Y-m-d H:i');
                    $member->save();
                }

                // Check for expired trial dates.
                if (new DateTime($member->trial_expire_date) <= new DateTime())
                {
                    return WDSAPI::echoJsonError('ERROR: carrierKey no longer valid', 'Your trial has expired.');
                }
                else
                {
                    $returnArray['trialExpireDate'] = (new DateTime($member->trial_expire_date))->format('Y-m-d H:i');
                }
            }
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

    #endregion

}
