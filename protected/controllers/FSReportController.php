<?php

class FSReportController extends Controller
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
                        'apiGetStatus',
                        'apiDownloadAssessment',
                        'apiNewUploadAssessment',
                        'apiUploadAssessment2',
                        'apiGetStatus2',
                        'apiDownloadAssessment2'
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
                    'delete',
                    'manualImport',
                    'test',
                    'fixMissingImages'
                ),
                'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types)',
            ),
			array('allow', // allow
				'actions'=>array(
                    'index',
                    'admin',
                    'download',
                    'update',
                    'showConditionHTML',
                    'getPDFReport',
                    'getKML',
                    'removeConditionPhoto',
                    'allReports'
                ),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array(
                    'apiGetStatus',
                    'apiDownloadAssessment',
                    'apiNewUploadAssessment',
                    'updateStatusCompleted',
                    'apiUploadAssessment2',
                    'apiGetStatus2',
                    'apiDownloadAssessment2',
                    'scheduleCall'
                ),
				'users'=>array('*')),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    /**
     * New API fxn for App 2.0 to upload completed assessments.
     * A GUID is a required post param so that it can be used to check the status in case this never returns
     */
    public function actionApiUploadAssessment2()
    {
        set_time_limit(1200);
		ini_set('max_execution_time', '1200');
		ini_set('memory_limit','256M');
		ini_set('post_max_size', '50M');

        //Required params check
        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'reportGuid')))
            return;

        //check if user if valid
        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");
        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
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
        $fsReport->fs_user_id = $fsUser->id;
        $fsReport->status_date = date('Y-m-d H:i:s');
        $fsReport->status = 'Importing';
        $fsReport->type = '2.0';
        $fsReport->notes = 'Recieved API Upload request, starting import for new assessment from user with loginToken: '.$data['loginToken'];
        if(!$fsReport->save())
        {
            return WDSAPI::echoJsonError('ERROR: Could not do initial save of report. Details: '.var_export($fsReport->getErrors(), true), 'Error saving Report.');
        }

        $errorMsg = '';

        if (!move_uploaded_file($_FILES['assessmentzip']['tmp_name'], $incoming_report_path))
        {
            return WDSAPI::echoJsonError($errorMsg, 'There was an error recieving the assessmentzip POST param file. DEBUG: '.var_export($_FILES['assessmentzip'], true));
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

    /**
     * Renders the index page (analytics)
     */
	public function actionIndex()
	{
        //Totals used in charts
        $startDate = date('Y-m', strtotime('-2 years', strtotime(date('Y-m-d')))) . "-01";
        $endDate = date('Y-m-d', strtotime('+1 months', strtotime(date('Y-m-d'))));
        $reportsPerMonth = FSReport::countCompletedReportsPerMonth($startDate, $endDate);
        $completedReports = FSReport::getReportsByDate($startDate, $endDate);
        $reportsPerState = FSReport::countReportsPerState($completedReports);

		$this->render('index',
            array(
                'startDate'=>$startDate,
                'endDate'=>$endDate,
                'reportsPerMonth'=>$reportsPerMonth,
                'completedReports'=>$completedReports,
                'reportsPerState'=>$reportsPerState
            )
        );
	}

	/**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
	public function actionUpdate($id)
	{
		$fsReport = $this->loadModel($id);
        //print_r($reportStatusHistory)
        //var_dump(array($fsReport));
        // Get the status history for this report.
        $reportStatusHistory = $fsReport->getStatusHistory();
		$message = '';
		$message_type = '';
		$previous_status = '';
		if(isset($_POST['FSReport']))
		{
			$previous_status = $fsReport->status;
			$fsReport->attributes = $_POST['FSReport'];
            $site_risk_override = false;
            if(!empty($_POST['FSReport']['condition_risk']))
                $site_risk_override = true;

			if($fsReport->save())
			{
				$message_type = 'success';
				$message = 'FS Report Updated Successfully!';
			}
		}

        $fsReport = $this->loadModel($id); //reload report cause for some reason it needs it here to update some relations stuff if the client or pid changed.
        $incomingReportPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$fsReport->report_guid.DIRECTORY_SEPARATOR;
        $outgoingReportPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$fsReport->report_guid.DIRECTORY_SEPARATOR;
		
        if(isset($_POST['FSConditions']))
		{
			foreach($_POST['FSConditions'] as $condition)
			{
				$fsCondition = FSCondition::model()->find('condition_num = '.$condition['condition_num'].' AND fs_report_id = '.$fsReport->id);
				$fsCondition->attributes = $condition;

                if ($fsReport->type == '2.0' || $fsReport->type == 'sl' || $fsReport->type == 'fso')
                    $set_id = $fsCondition->set_id; //need this for condition creation down below

                if(!empty($_FILES['new_photo_'.$fsCondition->id]['name']))
				{
					$new_photo_name = uniqid().'.jpg';
					$new_photo_path = $incomingReportPath.'images'.DIRECTORY_SEPARATOR.$new_photo_name;
					if(!is_dir($incomingReportPath.'images'))
						mkdir($incomingReportPath.'images');
					if(move_uploaded_file($_FILES['new_photo_'.$fsCondition->id]['tmp_name'], $new_photo_path))
					{
						$fsCondition->submitted_photo_path = rtrim($fsCondition->submitted_photo_path, '|');
						if(!(empty($fsCondition->submitted_photo_path)))
							$fsCondition->submitted_photo_path .= '|'.$new_photo_name;
						else
							$fsCondition->submitted_photo_path = $new_photo_name;
					}
				}

                //example photo save
                if(!empty($_FILES['example_image_'.$fsCondition->id]['name']))
                {
                    $question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $fsCondition->condition_num, 'client_id' => $fsReport->client->id));
                    $default_ex_photo_file_id = $question->example_image_file_id;
                    $uploaded_file = CUploadedFile::getInstanceByName('example_image_'.$fsCondition->id);
                    if(isset($fsCondition->example_image_file_id) && $fsCondition->example_image_file_id != $default_ex_photo_file_id)
                        $file_save_result = File::model()->saveFile($uploaded_file, $fsCondition->example_image_file_id);
                    else
                        $file_save_result = File::model()->saveFile($uploaded_file);

                    $fsCondition->example_image_file_id = $file_save_result;
                }

				$fsCondition->save();
			}

            if(!$site_risk_override)
    			$fsReport->condition_risk = $fsReport->calcConditionRisk();

			$fsReport->calcRiskLevel();

			//after recalcing risk levels then re-run thro conditions and make pdf report templates
			if($fsReport->type == 'fs')
			{
				if(is_null(array($fsReport->calcRiskLevel())))
                {
                    foreach($fsReport->conditions as $fsCondition)
				    {
					    if(!$fsCondition->createHTMLTemplate())
					    {
						    $message_type = 'error';
						    $message .= " ERROR: Failed to write FS HTML templates! (condition_num: ".$fsCondition->condition_num.", report_risk_level: ".$fsReport->risk_level.", condition_respons: ".$fsCondition->response.")";
					    }
				    }
                }
			}

			$fsReport->createKMLFile();

			if($fsReport->type == 'fs')
			{
				$fsReport->createSummaryHTMLTemplate();
				// Tell the report to display USAA references based on the member's client type.
				$showUSAATextInReport = $fsReport->member_client == 'USAA';
				$fsReport->createPDFReport(false, $showUSAATextInReport);

				$fsReport->createJSONFile();
				$fsReport->zipReport();

				if($previous_status != 'Completed' && $fsReport->status == 'Completed')
				{
					//get all fsUsers that are tied to this property/member
					$fsUsers = FSUser::model()->findAllByAttributes(array('member_mid'=>$fsReport->property->member_mid));
					foreach($fsUsers as $fsUser)
					{
						if($fsUser->vendor_id != null)
							$fsUser->sendPushNotification('Your WDSpro report is ready to download.');
					}
				}
			}
            elseif($fsReport->type == '2.0')
            {
                //create any conditions that arn't already created
                $fsReport->createConditions($set_id, $fsReport->fs_user->getClientID());
                //create 2.0 pdf report
                if(isset($_POST['refresh_pdfs']))
                {
                    $fsReport->createJSONFile2();
                    $fsReport->createPDFReport2();
                    $fsReport->zipReport();
                }

                if(isset($_POST['send_notification']) && !empty($_POST['notification_message']) && $fsReport->fs_user->vendor_id != null)
                {
                    $fsReport->fs_user->sendAzureNotification($_POST['notification_message']);
                }

                if(isset($_POST['send_emails']) && !empty($_POST['to_emails']))
                {
                    $attachments = array();
                    if(!empty($fsReport->email_download_types) && strpos($fsReport->email_download_types, 'EDU') !== false)
                        $attachments['WDSpro_EDU_Report.pdf'] = $outgoingReportPath.'report_edu.pdf';
                    if(!empty($fsReport->email_download_types) && strpos($fsReport->email_download_types, 'UW') !== false)
                        $attachments['WDSpro_UW_Report.pdf'] = $outgoingReportPath.'report_uw.pdf';;

                    $to_emails = $_POST['to_emails'];
                    //if there are multiple emails (comma seperated) need to send an email to each
                    if(strpos($to_emails, ',') > 0)
                    {
                        $to_emails = explode(',', $to_emails);
                        foreach($to_emails as $to_email)
                        {
                            $email_result = Helper::sendEmail('Your WDSpro report is ready', 'Attached is the completed report for the property at '.$fsReport->address_line_1.', '.$fsReport->city.', '.$fsReport->state.'.', $to_email, $attachments);
                        }
                    }
                    else //single email
                    {
                        $email_result = Helper::sendEmail('Your WDSpro report is ready', 'Attached is the completed report for the property at '.$fsReport->address_line_1.', '.$fsReport->city.', '.$fsReport->state.'.', $to_emails, $attachments);
                    }
                    if($email_result)
                        $message .= " Email was successfully sent!";
                    else
                        $message .= " There was an ERROR sending the email!";
                }
            }
            elseif($fsReport->type == 'sl' || $fsReport->type == 'fso')
            {
                
                //create any conditions that arn't already created
                $fsReport->createConditions($set_id, $fsReport->user->client_id);
                //create 2.0 pdf report
                if(isset($_POST['refresh_pdfs']))
                {
                    $fsReport->createJSONFile2();
                    $fsReport->createPDFReport2();
                    $fsReport->zipReport();
                }
                if($fsReport->user->id)
                {
                    if(isset($_POST['send_notification']) && !empty($_POST['notification_message']) && $fsReport->user->vendor_id != null)
                    {
                        $fsReport->user->sendAzureNotification($_POST['notification_message']);
                    }
                }
                if(isset($_POST['send_emails']) && !empty($_POST['to_emails']))
                {
                    $attachments = array();
                    if(!empty($fsReport->email_download_types) && strpos($fsReport->email_download_types, 'EDU') !== false)
                        $attachments['WDSpro_EDU_Report.pdf'] = $outgoingReportPath.'report_edu.pdf';
                    if(!empty($fsReport->email_download_types) && strpos($fsReport->email_download_types, 'UW') !== false)
                        $attachments['WDSpro_UW_Report.pdf'] = $outgoingReportPath.'report_uw.pdf';;

                    $to_emails = $_POST['to_emails'];
                    //if there are multiple emails (comma seperated) need to send an email to each
                    if(strpos($to_emails, ',') > 0)
                    {
                        $to_emails = explode(',', $to_emails);
                        foreach($to_emails as $to_email)
                        {
                            $email_result = Helper::sendEmail('Your WDSpro report is ready', 'Attached is the completed report for the property at '.$fsReport->address_line_1.', '.$fsReport->city.', '.$fsReport->state.'.', $to_email, $attachments);
                        }
                    }
                    else //single email
                    {
                        $email_result = Helper::sendEmail('Your WDSpro report is ready', 'Attached is the completed report for the property at '.$fsReport->address_line_1.', '.$fsReport->city.', '.$fsReport->state.'.', $to_emails, $attachments);
                    }
                    if($email_result)
                        $message .= " Email was successfully sent!";
                    else
                        $message .= " There was an ERROR sending the email!";
                }
            }
			else //agent report (type 'uw' or 'edu')
			{
				$fsReport->createAgentPDFReport();
			}
		}

		if(!empty($message_type))
			Yii::app()->user->setFlash($message_type, $message);

        // Need to set a template in the summary, if not already populated.
        if($fsReport->type == '2.0')
        {
            if (empty($fsReport->summary)) {
                $fsReport->summary = '<p><u><b>Wildfire concerns in your surrounding area:</b></u></p>'
                    . '<p>Add content here</p>'
                    . '<p><u><b>Additional actions you should take:</b></u></p>'
                    . '<ul><li>Add content here</li></ul>';
            }
            if (empty($fsReport->risk_summary)) {
                $fsReport->risk_summary = '<p><u><b>Wildfire risks on your property:</b></u></p>'
                    . '<ul><li><b>TITLE</b>: Add content here</li>'
                    . '<li><b>TITLE</b>: Add content here</li></ul>'
                    . '<p><u><b>Additional actions you should take:</b></u></p>'
                    . '<ul><li>Add content here</li></ul>';
            }


            //set some other defaults if they aren't already set
            if($fsReport->pdf_pass === null)
            {
                $report_options = $fsReport->getClientReportOptions();
                $fsReport->pdf_pass = (isset($report_options['app2-pdf-pw']) ? $report_options['app2-pdf-pw'] : null);
            }
            if($fsReport->level === null)
            {
                $questionSet = ClientAppQuestionSet::model()->findByPk($fsReport->getQuestionSetID());
                if(isset($questionSet))
                $fsReport->level = $questionSet->default_level;
            }
        }
        elseif($fsReport->type == 'sl' || $fsReport->type == 'fso')
        {
            if (empty($fsReport->summary)) {
                $fsReport->summary = '<p><u><b>Wildfire concerns in your surrounding area:</b></u></p>'
                    . '<p>Add content here</p>'
                    . '<p><u><b>Additional actions you should take:</b></u></p>'
                    . '<ul><li>Add content here</li></ul>';
            }
            if (empty($fsReport->risk_summary)) {
                $fsReport->risk_summary = '<p><u><b>Wildfire risks on your property:</b></u></p>'
                    . '<ul><li><b>TITLE</b>: Add content here</li>'
                    . '<li><b>TITLE</b>: Add content here</li></ul>'
                    . '<p><u><b>Additional actions you should take:</b></u></p>'
                    . '<ul><li>Add content here</li></ul>';
            }


            //set some other defaults if they aren't already set
            if($fsReport->pdf_pass === null)
            {
                $report_options = $fsReport->getClientReportOptions();
                $fsReport->pdf_pass = (isset($report_options['app2-pdf-pw']) ? $report_options['app2-pdf-pw'] : null);
            }
            if($fsReport->level === null)
            {
                $questionSet = ClientAppQuestionSet::model()->findByPk($fsReport->getQuestionSetID());
                if(isset($questionSet))
                $fsReport->level = $questionSet->default_level;
            }
        }
        else
        {
            if (empty($fsReport->summary)) {
                $fsReport->summary = '<p><u><b>Wildfire concerns in your surrounding area:</b></u></p>'
                    . '<p>Add content here</p>'
                    . '<p><u><b>Wildfire risks on your property:</b></u></p>'
                    . '<ul><li><b>TITLE</b>: Add content here</li>'
                    . '<li><b>TITLE</b>: Add content here</li></ul>'
                    . '<p><u><b>Additional actions you should take:</b></u></p>'
                    . '<ul><li>Add content here</li></ul>';
            }
        }
		if($fsReport->type == 'fs')
		{
			$this->render('update',array(
				'fsReport' => $fsReport,
				'reportStatusHistory' => $reportStatusHistory,
			));
		}
        elseif($fsReport->type == '2.0')
        {
            $this->render('update2',array(
                'fsReport' => $fsReport,
                'reportStatusHistory' => $reportStatusHistory,
            ));
        }
        elseif($fsReport->type == 'sl')
        {
            
            $this->render('update2',array(
                'fsReport' => $fsReport,
                'reportStatusHistory' => $reportStatusHistory,
            ));
        }
        elseif($fsReport->type == 'fso')
        {
            
            $this->render('update2',array(
                'fsReport' => $fsReport,
                'reportStatusHistory' => $reportStatusHistory,
            ));
        }
        else //agent report
        {
            if(isset($fsReport->property, $fsReport->property->member))
            {
                $mem_prop_info =
                    '<br />Member: '.$fsReport->property->member->first_name.' '.$fsReport->property->member->last_name.' (Mem #: '.$fsReport->property->member->member_num.' | MID: '.CHtml::link($fsReport->property->member_mid,array('member/update', 'mid'=>$fsReport->property->member_mid), array('target'=>'_blank')).')'.
                    '<br />Policy #: '.CHtml::link($fsReport->property->policy, array('property/update', 'pid'=>$fsReport->property_pid), array('target'=>'_blank')).
                    '<br />Address: '.$fsReport->property->address_line_1.' '.$fsReport->property->city.', '.$fsReport->property->state.' '.$fsReport->property->zip.'<br>';
            }
            else
                $mem_prop_info = '';

            if(isset($fsReport->pre_risk))
            {
                $pr_info =
                    'PreRisk Production Link: '.CHtml::link($fsReport->pre_risk->id, array('prerisk/update', 'id'=>$fsReport->pre_risk->id, 'type'=>'review'), array('target'=>'_blank')).
                    '<br>Status: '.$fsReport->pre_risk->status.
                    '<br>HA Date: '.$fsReport->pre_risk->ha_date.'<br>';
            }
            else
                $pr_info = '';

            $this->render('agentUpdate',array(
                'fsReport' => $fsReport,
                'reportStatusHistory' => $reportStatusHistory,
                'mem_prop_info' => $mem_prop_info,
                'pr_info' => $pr_info,
            ));
        }
	}

	/**
     * Grid view of FS Reports
     */
	public function actionAdmin($types = 'fs')
	{
		$fsReports = new FSReport('search');
		$fsReports->unsetAttributes(); //clear any default values
		if(isset($_GET['FSReport']))
		{
			$fsReports->attributes = $_GET['FSReport'];
		}

		//default cols
		if($types == 'agent')
			$columnsToShow = array('id', 'status', 'assigned_user_name', 'agent_first_name', 'agent_last_name', 'client_name', 'agent_property_address_line_1', 'agent_property_city', 'agent_property_state', 'submit_date', 'due_date',);
		else if($types == 'fs')
			$columnsToShow = array('id', 'status', 'assigned_user_name', 'member_first_name', 'member_last_name', 'property_address_line_1', 'submit_date', 'due_date', 'scheduled_call', );

        if(isset($_GET['columnsToShow']))
        {
            $_SESSION['wds_'.$types.'_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        elseif(isset($_SESSION['wds_'.$types.'_columnsToShow']))
            $columnsToShow = $_SESSION['wds_'.$types.'_columnsToShow'];

        $pageSize = 25;
        if(isset($_GET['pageSize']))
        {
            $_SESSION['wds_'.$types.'_pageSize'] = $_GET['pageSize'];
            $pageSize = $_GET['pageSize'];
        }
        elseif(isset($_SESSION['wds_'.$types.'_pageSize']))
            $pageSize = $_SESSION['wds_'.$types.'_pageSize'];

        $sort = 'due_date';

        if(isset($_GET['FSReport_sort']))
        {
            $_SESSION['wds_'.$types.'_sort'] = $_GET['FSReport_sort'];
            $sort = $_GET['FSReport_sort'];
        }
        elseif(isset($_SESSION['wds_'.$types.'_sort']))
            $sort = $_SESSION['wds_'.$types.'_sort'];

        $advSearch = NULL;
        if(isset($_GET['advSearch']))
        {
            $_SESSION['wds_'.$types.'_advSearch'] = $_GET['advSearch'];
            $advSearch = $_GET['advSearch'];
        }
        elseif(isset($_SESSION['wds_'.$types.'_advSearch']))
            $advSearch = $_SESSION['wds_'.$types.'_advSearch'];
		else //default, advSearch not set
		{
			$advSearch = array();
			$advSearch['statuses'] = $fsReports->getStatuses();
            $advSearch['haDateBegin'] = NULL;
            $advSearch['haDateEnd'] = NULL;
            $advSearch['statusDateBegin'] = NULL;
            $advSearch['statusDateEnd'] = NULL;
		}

		$advSearch['types'] = $types;

		$this->render('admin', array('fsReports' => $fsReports, 'columnsToShow' => $columnsToShow, 'pageSize' => $pageSize, 'advSearch' => $advSearch, 'sort' => $sort));
	}

    public function actionAllReports()
    {
        $fsReports = new FSReport('search');
		$fsReports->unsetAttributes(); //clear any default values

        //defaults
        $columnsToShow = array('id', 'type', 'status', 'assigned_user_name', 'address_line_1', 'city', 'state', 'submit_date', 'due_date', 'fs_user_client_name', 'scheduled_call');
        $pageSize = 50;
        $sort = 'id.desc';
        $advSearch = array();
        $advSearch['statuses'] = $fsReports->getStatuses();
        $advSearch['haDateBegin'] = NULL;
        $advSearch['haDateEnd'] = NULL;
        $advSearch['statusDateBegin'] = NULL;
        $advSearch['statusDateEnd'] = NULL;


        if(isset($_GET['clear_settings']))
        {
            unset($_SESSION['wds_all_app_reports_attr'], $_SESSION['wds_all_app_reports_columnsToShow'], $_SESSION['wds_all_app_reports_pageSize'], $_SESSION['wds_all_app_reports_sort'], $_SESSION['wds_all_app_reports_advSearch']);
            $this->redirect(array('allReports'));
        }
        else //load any settings from POST or saved in SESSION
        {
            if(isset($_POST['FSReport']))
            {
                $_SESSION['wds_all_app_reports_attr'] = $_POST['FSReport'];
                $fsReports->attributes = $_POST['FSReport'];
            }
            elseif(isset($_SESSION['wds_all_app_reports_attr']))
                $fsReports->attributes = $_SESSION['wds_all_app_reports_attr'];

            if(isset($_POST['columnsToShow']))
            {
                $_SESSION['wds_all_app_reports_columnsToShow'] = $_POST['columnsToShow'];
                $columnsToShow = $_POST['columnsToShow'];
            }
            elseif(isset($_SESSION['wds_all_app_reports_columnsToShow']))
                $columnsToShow = $_SESSION['wds_all_app_reports_columnsToShow'];

            if(isset($_POST['pageSize']))
            {
                $_SESSION['wds_all_app_reports_pageSize'] = $_POST['pageSize'];
                $pageSize = $_POST['pageSize'];
            }
            elseif(isset($_SESSION['wds_all_app_reports_pageSize']))
                $pageSize = $_SESSION['wds_all_app_reports_pageSize'];

            if(isset($_GET['FSReport_sort']))
            {
                $_SESSION['wds_all_app_reports_sort'] = $_GET['FSReport_sort'];
                $sort = $_GET['FSReport_sort'];
            }
            elseif(isset($_SESSION['wds_all_app_reports_sort']))
                $sort = $_SESSION['wds_all_app_reports_sort'];

            if(isset($_POST['advSearch']))
            {
                $_SESSION['wds_all_app_reports_advSearch'] = $_POST['advSearch'];
                $advSearch = $_POST['advSearch'];
            }
            elseif(isset($_SESSION['wds_all_app_reports_advSearch']))
                $advSearch = $_SESSION['wds_all_app_reports_advSearch'];
        }

		$this->render('allReports', array('fsReports' => $fsReports, 'columnsToShow' => $columnsToShow, 'pageSize' => $pageSize, 'advSearch' => $advSearch, 'sort' => $sort));
    }

	public function actionDownload()
    {
		set_time_limit(1800);
        ini_set('memory_limit','64M');
		$fsReports = new FSReport('search');
		$fsReports->unsetAttributes(); //clear any default values

		//default cols
		$columnsToShow = array('id', 'status', 'assigned_user_name', 'member_first_name', 'member_last_name', 'property_address_line_1', 'submit_date', 'due_date', 'scheduled_call', );
        if(isset($_GET['columnsToShow']))
        {
            $_SESSION['wds_fs_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        elseif(isset($_SESSION['wds_fs_columnsToShow']))
            $columnsToShow = $_SESSION['wds_fs_columnsToShow'];

        $pageSize = 25;
        if(isset($_GET['pageSize']))
        {
            $_SESSION['wds_fs_pageSize'] = $_GET['pageSize'];
            $pageSize = $_GET['pageSize'];
        }
        elseif(isset($_SESSION['wds_fs_pageSize']))
            $pageSize = $_SESSION['wds_fs_pageSize'];

        $sort = 'due_date.desc';

        if(isset($_GET['FSReport_sort']))
        {
            $_SESSION['wds_fs_sort'] = $_GET['FSReport_sort'];
            $sort = $_GET['FSReport_sort'];
        }
        elseif(isset($_SESSION['wds_fs_sort']))
            $sort = $_SESSION['wds_fs_sort'];

        $advSearch = NULL;
        if(isset($_GET['advSearch']))
        {
            $_SESSION['wds_fs_advSearch'] = $_GET['advSearch'];
            $advSearch = $_GET['advSearch'];
        }
        elseif(isset($_SESSION['wds_fs_advSearch']))
            $advSearch = $_SESSION['wds_fs_advSearch'];
		else //default, advSearch not set
		{
			$advSearch = array();
			$advSearch['statuses'] = $fsReports->getStatuses();
		}

		$fsReports->makeDownloadableReport($columnsToShow, $advSearch, $sort);
        header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".Yii::app()->user->name."_FSReports.csv\"");
        readfile(Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_FSReports.csv');
    }

	/**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
	public function loadModel($id)
	{
		$model=FSReport::model()->with('agent_property', 'property', 'agent', 'client', 'pre_risk')->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && ($_POST['ajax']==='homeowner-form' || $_POST['ajax']==='call-form'))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
     * Gets the pdf report for given FS Report GUID
     * @param integer $id the ID of the model to be displayed
     */
	public function actionGetPDFReport($guid, $usaa_ver=false, $type='')
	{
        $outgoingReportPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$guid.DIRECTORY_SEPARATOR;
		//var_dump(array($outgoingReportPath));
        $report_path = $outgoingReportPath.'report.pdf';
		if($usaa_ver == 1)
			$report_path = $outgoingReportPath.'usaaReport.pdf';

        if(!empty($type))
            $report_path = $outgoingReportPath.'report_'.$type.'.pdf';

		if (file_exists($report_path))
		{
		    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		    header("Cache-Control: public"); // needed for i.e.
		    header("Content-Type: application/pdf");
		    header("Content-Transfer-Encoding: Binary");
		    header("Content-Length:".filesize($report_path));
		    header("Content-Disposition: inline; filename=".basename($report_path));
		    readfile($report_path);
		    die();
		}
		else
		{
			echo  'not found';
		}
	}

	/**
     * Gets the kml file for given FS Report GUID
     * @param integer $id the ID of the model to be displayed
     */
	public function actionGetKML($guid)
	{
        $kml_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$guid.DIRECTORY_SEPARATOR.'ge.kml';

		if (file_exists($kml_path))
		{
		    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		    header("Cache-Control: public"); // needed for i.e.
		    header("Content-Type: application/vnd.google-earth.kml+xml");
		    header("Content-Transfer-Encoding: Binary");
		    header("Content-Length:".filesize($kml_path));
		    header("Content-Disposition: inline; filename=".basename($kml_path));
		    readfile($kml_path);
		    die();
		}
		else
		{
			echo  'not found';
		}
	}

	public function actionRemoveConditionPhoto($condition_id, $photo_name_to_delete)
	{
		$fs_condition = FSCondition::model()->findByPk($condition_id);

		//loop through and reset the submitted photo path for the condition with all the photos except the one to be removed.  Note that this does not currently Delete the actual photo file, just removes it from the list in the database so it wont show up
		$new_submitted_photo_path = '';
		foreach($fs_condition->getSubmittedPhotosArray() as $photo_name)
		{
			if($photo_name != $photo_name_to_delete)
				$new_submitted_photo_path .= $photo_name."|";
		}
		//remove trailing |
		$fs_condition->submitted_photo_path = rtrim($new_submitted_photo_path, '|');

		$fs_condition->save();

		return '';
	}

	/**
     * Get assessment status
     * IIS URL Rewrite Rule: api/fireshield/v2/getAssessmentStatus/
     */
    public function actionApiGetStatus()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'guid')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");


        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
        }

        $isUserAssessment = false;
        $assessments = $fsUser->getAssessments();
        foreach($assessments as $assessment)
        {
            if($assessment['guid'] == $data['guid'])
                $isUserAssessment = true;
        }

        if (!$isUserAssessment)
        {
            return WDSAPI::echoJsonError('ERROR: assessment guid was not valid for logged in user.', 'The given guid was not valid.');
        }

		$returnArray = array();
        $returnArray['error'] = 0; // Success
        $returnArray['data'] = array('assessments'=>$fsUser->getAssessments());

        $fs_report = FSReport::model()->find("report_guid = '" . $data['guid'] . "'");

        if (is_null($fs_report))
        {
            return WDSAPI::echoJsonError('Report GUID not found in the database');
        }

        $status = 0; //all status's except Completed should return 0 (In Progress)
        if (isset($fs_report->status) && $fs_report->status == 'Completed')
        {
            $status = 1;
        }
        $returnArray['data'] = array("guid"=>$fs_report->report_guid, "status"=>$status); //status 0 = In Progress, 1 = Complete

        WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
     * Get assessment status for 2.0 app
     */
    public function actionApiGetStatus2()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'reportGuid')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
        }

        $fsReport = FSReport::model()->findByAttributes(array('report_guid'=>$data['reportGuid'], 'fs_user_id'=>$fsUser->id));
        if(!isset($fsReport))
        {
            return WDSAPI::echoJsonError('ERROR: assessment guid was not found for logged in user.', 'The given guid was not valid.');
        }

        $status = 'In Progress'; //all status's except Completed or Error should return In Progress)
        if(!isset($fsReport->status) || $fsReport->status == 'Error')
        {
            $status = 'Error';
        }
        elseif ($fsReport->status == 'Completed')
        {
            $status = 'Completed';
        }

        $returnArray['data'] = array("status"=>$status);

        WDSAPI::echoResultsAsJson($returnArray);
	}

	//ONLY should work in dev environment
	public function actionUpdateStatusCompleted($guid)
	{
		if(Yii::app()->params['env'] == 'dev')
		{
			$fsReport = FSReport::model()->find("report_guid='$guid'");
			if(isset($fsReport))
			{
				$fsReport->status = 'Completed';
				$fsReport->save();
				$fsUser = FSUser::model()->findByAttributes(array('member_mid'=>$fsReport->property->member->mid));
				if($fsUser->sendPushNotification('Your WDSpro report is ready to download.') == false)
					echo 'Error sending push notification ';
				echo 'successfully set assessment status to completed';
			}
			else {
				echo 'could not find assessment with guid:'.$guid;
			}
		}
		else
			echo "ERROR: you should not be doing this in the produciton environment and this action will be reported.";
	}

	/**
     * Downloads an assessment.
     * IIS URL Rewrite Rule: api/fireshield/v2/downloadAssessment/
     */
    public function actionApiDownloadAssessment()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'guid')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
        }

        $isUserAssessment = false;
        $assessments = $fsUser->getAssessments();
        foreach($assessments as $assessment)
        {
            if($assessment['guid'] == $data['guid'])
                $isUserAssessment = true;
        }

        if (!$isUserAssessment)
        {
            return WDSAPI::echoJsonError('ERROR: assessment guid was not valid for logged in user.', 'The given guid was not valid.');
        }

        $fs_report = FSReport::model()->find("report_guid = '" . $data['guid'] . "'");

        if (is_null($fs_report))
        {
            return WDSAPI::echoJsonError('Assessment GUID not found in the database');
        }

        if ($fs_report->status == 'Completed')
        {
            $downloadFile = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$data['guid'].DIRECTORY_SEPARATOR.'report.zip';

            if (file_exists($downloadFile))
            {
                header(filter_input(INPUT_SERVER, "SERVER_PROTOCOL") . " 200 OK");
                header("Cache-Control: public"); // needed for i.e.
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($downloadFile));
                header("Content-Disposition: attachment; filename=report.zip");
                readfile($downloadFile);
                die();
            }
            else
            {
                return WDSAPI::echoJsonError('Report download file not found.');
            }
        }
        else
        {
            return WDSAPI::echoJsonError('Report status is In Progress, wait until Completed.', 'Assessment is still in progress, wait until Complete.');
        }

        WDSAPI::echoResultsAsJson(array());
	}

    /**
     * Downloads an completed report.zip for a given user.
     */
    public function actionApiDownloadAssessment2()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken', 'reportGuid')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: loginToken was not found, could not lookup user.', 'The given login was not valid.');
        }

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");

        $fsReport = FSReport::model()->findByAttributes(array('report_guid'=>$data['reportGuid'], 'fs_user_id'=>$fsUser->id));
        if(!isset($fsReport))
        {
            return WDSAPI::echoJsonError('ERROR: assessment guid was not found for logged in user.', 'The given guid was not valid.');
        }

        if ($fsReport->status == 'Completed')
        {
            $downloadFile = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$data['reportGuid'].DIRECTORY_SEPARATOR.'report.zip';

            if (file_exists($downloadFile))
            {
                header(filter_input(INPUT_SERVER, "SERVER_PROTOCOL") . " 200 OK");
                header("Cache-Control: public"); // needed for i.e.
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($downloadFile));
                header("Content-Disposition: attachment; filename=report.zip");
                readfile($downloadFile);
                die();
            }
            else
            {
                return WDSAPI::echoJsonError('Report download file not found.');
            }
        }
        else
        {
            return WDSAPI::echoJsonError('Report status is In Progress, wait until Completed.', 'Assessment is still in progress, wait until Complete.');
        }

        WDSAPI::echoResultsAsJson(array());
	}

	public function actionShowConditionHTML($condition_num, $report_guid)
	{
        $outgoingReportPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$report_guid.DIRECTORY_SEPARATOR;
        $outgoingPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR;
		$file_path = $outgoingReportPath.'html'.DIRECTORY_SEPARATOR.$condition_num.'.html';

        $html = "";

        if(file_exists($file_path))
        {
		    $html = file_get_contents($file_path);
        }
		$tempCSS = "<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic'>
            <style>
			html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
				margin: 0;
				padding: 0;
				border: 0;
				font-size: 100%;
				font: inherit;
				vertical-align: baseline;
			}
			/* HTML5 display-role reset for older browsers */
			article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
				display: block;
			}
			body {
				line-height: 1;
			}
			ol, ul {
				list-style: none;
			}
			blockquote, q {
				quotes: none;
			}
			blockquote:before, blockquote:after, q:before, q:after {
				content: '';
				content: none;
			}
			table {
				border-collapse: collapse;
				border-spacing: 0;
			}

			/* end reset  */

			body {
				font-family: 'PT Sans', helvetica, Arial, sans-serif;
				font-weight: normal;
				color: #dddddd;
				line-height: 1.25em;
				font-size: 12pt;
			}
			p {
				padding-bottom: 5px;
			}
			strong {
				font-weight:bold;
			}
			.background {
				width: 100%;
				height: 100%;
				background: url('".Yii::app()->request->baseUrl."/index.php?r=site/getImage&filepath=".urlencode($outgoingPath."default/html/img/background-retina.png")."') no-repeat;
				/* background: transparent; */
				-webkit-background-size: 640px 960px;
				position: fixed;
			}
			.container {
				width: 100%;
				height: 100%;
				position: absolute;
			}

			.header {
				color: #444343;
				font-size: 18pt;
				text-align: center;
				line-height: 2.2em;
				font-weight: bold;
				background: url('".Yii::app()->request->baseUrl."/index.php?r=site/getImage&filepath=".urlencode($outgoingPath."default/html/img/title-background-retina.png")."') no-repeat;
				/* background: url('../img/title-background-retina.png') no-repeat; */
				-webkit-background-size: 305px 55px;
				margin: 10px auto 0px auto;
				height: 55px;
				width: 305px;
			}
			.content {
				padding: 0px 20px;
			}
			.image-container {
				background: url('".Yii::app()->request->baseUrl."/index.php?r=site/getImage&filepath=".urlencode($outgoingPath."default/html/img/image-border-retina.png")."') no-repeat;
				/*background: url('../img/image-border-retina.png') no-repeat; */
				-webkit-background-size: 152px 154px;
				float: left;
				height: 155px;
				width: 152px;
				position: relative;
				padding-right: 5px;
			}
			.right {
				float: right;
				padding-right: 0px;
			}
			.image {
				position: absolute;
				top: 11px;
				left: 14px;
			}
			.media-container {
				background: url('".Yii::app()->request->baseUrl."/index.php?r=site/getImage&filepath=".urlencode($outgoingPath."default/html/img/media-container-retina.png")."') no-repeat;
				/* background: url('../img/media-container-retina.png') no-repeat; */
				-webkit-background-size: 272px 158px;
				height: 158px;
				width: 272px;
				position: relative;
				margin-top:15px;
			}
			.media {
				position: absolute;
				top: 11px;
				left: 25px;
				height:123px;
				width:219px;
			}
			.gradient{
				background: #ebebeb; /* Old browsers */
			background: -moz-linear-gradient(top,  #ebebeb 0%, #eeeeee 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ebebeb), color-stop(100%,#eeeeee)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top,  #ebebeb 0%,#eeeeee 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top,  #ebebeb 0%,#eeeeee 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top,  #ebebeb 0%,#eeeeee 100%); /* IE10+ */
			background: linear-gradient(to bottom,  #ebebeb 0%,#eeeeee 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ebebeb', endColorstr='#eeeeee',GradientType=0 ); /* IE6-9 */

			border-top: solid 1px #ffffff;

			-webkit-box-shadow: 0 6px 6px -6px black;
				   -moz-box-shadow: 0 6px 6px -6px black;
						box-shadow: 0 6px 6px -6px black;

			}

			.strong{
				font-weight:bold;
			}

			.slim{
				font-weight: 200;
			}

			.slim b{
				font-weight: bold;
			}
			</style>
		";
		$html = str_replace('<script src="fontAdjust.js" type="text/javascript"></script>', '<script src="js/fontAdjust.js" type="text/javascript"></script>', $html);
        $html = str_replace('<meta name="viewport" content=" initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>', '<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>', $html);
		$html = str_replace('<link href="css/style.css" rel="stylesheet" media="screen" type="text/css">', $tempCSS, $html);
		$html = str_replace('<img style="height:122px;width:122px" class="image" src="img/', '<img style="height:122px;width:122px" class="image" src="'.Yii::app()->request->baseUrl.'/index.php?r=site/getImage&filepath='.urlencode($outgoingPath.'default/html/img/'), $html);
		echo $html;
	}

	public function actionScheduleCall()
	{
		$headers = AHtml::getHttpRequestHeaders();
		$oauth = new YOAuth2();
		$message = null;
		if($oauth->verifyAccessToken('fireshield'))  //check oauth
		{
			if(isset($_POST['data']) || isset($_GET['data']))
			{
				if(isset($_POST['data']))
					$data = json_decode($_POST['data'], true);
				else
					$data = json_decode($_GET['data'], true);

				//check if all required params are there
				if(isset($data['loginToken']) && isset($data['reportGuid']) && isset($data['userTimeZone']))
				{
					//check if report exists
					$fsReport = FSReport::model()->findByAttributes(array('report_guid'=>$data['reportGuid']));
					if(isset($fsReport))
					{
						//check if report belongs to logged in user
						$fsUser = FSUser::model()->findByAttributes(array('login_token'=>$data['loginToken']));
						$is_user_assessment = false;
						if(isset($fsUser))
						{
							$assessments = $fsUser->getAssessments();
							foreach($assessments as $assessment)
							{
								if($assessment['guid'] == $fsReport->report_guid)
									$is_user_assessment = true;
							}
						}

						if($is_user_assessment)
						{
							//minimum date needs to be 2 business days in the future
							if(date('w') == 6) //if saturday
								$min_date = date('Y-m-d', strtotime('+3 days'));
							else if(date('w') == 5 || date('w') == 4) //if thurs or friday
								$min_date = date('Y-m-d', strtotime('+4 days'));
							else //any other day of week
								$min_date = date('Y-m-d', strtotime('+2 days'));

							//get timezone offset
							//$userDateTimeZone = new DateTimeZone($_GET['time_zone']);
							//$wdsDateTimeZone = new DateTimeZone('America/Denver');
							//$userTime = new DateTime("now", $userDateTimeZone);
							//$wdsTime = new DateTime("now", $wdsDateTimeZone);
							//$timeZoneOffSet = $wdsDateTimeZone->getOffset($wdsTime) - $userDateTimeZone->getOffset($userDateTimeZone); //number of seconds difference between the 2 timezones
							//$offset = $timeZoneOffset/60; //change into hours
							$offset = 0;

							//availible hours 10am-3pm MST
							$min_hour = 10 + $offset;
							$max_hour = 15 + $offset;

							$this->layout = '//layouts/fireshield';
							$access_token = $oauth->getAccessTokenParams();
							$this->render('schedule_call',array('access_token'=>$access_token, 'login_token'=>$data['loginToken'], 'time_zone'=>$data['userTimeZone'], 'report_guid'=>$data['reportGuid'], 'min_date'=>$min_date, 'min_hour'=>$min_hour, 'max_hour'=>$max_hour));

						}
						else
						{
							echo 'Error: Report does not belong to logged in user';
						}
					}
					else
					{
						echo 'Error: Report does not exist';
					}
				}
				else
				{
					echo 'Error: Missing Required Data Vars in JSON data payload';
				}
			}
			else if(isset($_POST['login_token']) && isset($_POST['report_guid']) && isset($_POST['time_zone']) && isset($_POST['submit']))
			{
				//check if report exists
				$fsReport = FSReport::model()->findByAttributes(array('report_guid'=>$_POST['report_guid']));
				if(isset($fsReport))
				{
					//check if report belongs to logged in user
					$fsUser = FSUser::model()->findByAttributes(array('login_token'=>$_POST['login_token']));
					$is_user_assessment = false;
					if(isset($fsUser))
					{
						$assessments = $fsUser->getAssessments();
						foreach($assessments as $assessment)
						{
							if($assessment['guid'] == $fsReport->report_guid)
								$is_user_assessment = true;
						}
					}

					if($is_user_assessment)
					{
						if(isset($_POST['call_date']) && isset($_POST['call_time']))
						{
							$fsReport->scheduled_call = $_POST['call_date'] .' '. $_POST['call_time'];
							$fsReport->scheduled_call_tz = $_POST['time_zone'];

                            //email notification
                            Helper::sendEmail('Call scheduled for FireShield L3 report', 'A WDSpro follow-up call was scheduled at '.$fsReport->scheduled_call.' '.$fsReport->scheduled_call_tz .' for a L3 FS Report with ID: '.$fsReport->id, 'ha@wildfire-defense.com');

							if($fsReport->save())
							{
                                //return/redirect for app handleing (add to calendar fxnality)
								$data = array('startTime'=>strtotime($fsReport->scheduled_call), 'endTime'=>strtotime($fsReport->scheduled_call.' + 30 minutes'), 'title'=>'WDSpro Report Phone Call', 'details'=>'Discuss your WDSpro report and answer any questions you may have.');
								$this->redirect("fireshield://appointment/?data=".urlencode(json_encode($data)));
							}
							else
								echo 'Error: There was an issue saving the call date and time';
						}
						else
						{
							echo 'Error: Missing Requied Call Parameters';
						}
					}
					else
					{
						echo 'Error: Report does not belong to logged in user';
					}
				}
				else
				{
					echo 'Error: Report does not exist';
				}
			}
			else
			{
				echo 'Error: Missing data Post Param OR submit params';
			}
		} //end oauth check
	}

	/**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$report = $this->loadModel($id);
			foreach($report->conditions as $condition)
				$condition->delete();
			$report->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('allReports'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

    public function actionManualImport($guid)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors', '1');

        $importError = FSReport::model()->import($guid);
        if($importError !== false)
            echo $importError;
        else
        {
            $fsReport = FSReport::model()->findByAttributes(array('report_guid'=>$guid));
            echo CHtml::link('Successfully imported Report '.$fsReport->id, 'fsReport/update', array('id'=>$fsReport->id));
        }
    }

    //fix json to pick up extra pictures for missing pictures issue
    public function actionFixMissingImages($report_id)
    {
        $report = FSReport::model()->findByPk($report_id);
        $reportImagesPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$report->report_guid.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR;
        if($images = scandir($reportImagesPath))
        {
            echo "Starting directory scan to fix images for report $report_id <br><br>";
            foreach($images as $image)
            {
                if($image != '.' && $image != '..')
                {
                    $condition_num = substr($image,0,strpos($image, '_'));
                    $condition = FSCondition::model()->findByAttributes(array('fs_report_id'=>$report_id, 'condition_num'=>$condition_num));
                    if(isset($condition))
                    {
                        if(isset($condition->submitted_photo_path))
                        {
                            if(!in_array($image, explode('|', $condition->submitted_photo_path)))
                                $condition->submitted_photo_path .= "|$image";
                        }
                        else if(!isset($condition->submitted_photo_path))
                        {
                            $condition->submitted_photo_path = $image;
                            $condition->pic_to_use = 1;
                        }
                        else
                        {
                            //image was already in there
                        }

                        if($condition->save())
                        {
                            echo 'Condition '.$condition->condition_num." submitted_photo_path was set = '".$condition->submitted_photo_path."'. <br><br>";
                        }
                        else
                            echo "Error: Condition $condition_num save failed (Details: ".$condition->getErrors().") <br><br>";
                    }
                    else
                        echo "Could not find condition (num: $condition_num)";
                }
                else
                    echo "Skipping '.' OR '..' <br><br>";
            }
        }
        else
            echo "Error: The directory $reportImagesPath could not be scanned. <br><br>";

        echo "Done with image fix for report $report_id.";
    }

	public function actionTest()
	{
		if(Yii::app()->params['env'] == 'dev')
		{

            echo Helper::addWeekDays('01/19/2016 05:50 PM', 5);

            //update carrier keys
            //		$members = Member::model()->findAll();
            //		foreach($members as $member)
            //		{
            //			$member->fs_carrier_key = substr(str_shuffle("23456789ABCDEFGHJKMNPQRSTUVWXYZ"), 0, 8);
            //			$member->save();
            //		}
            //		echo 'done updating carrier keys';

            //test push of FS_USER
            //$fsUser = FSUser::model()->findByPk('1157');
            //$fsUser->sendPushNotification('Test Push', 'Your Report Is Ready TT');
            //echo 'done sending push to: '.$fsUser->email;

            //PARSE REST CALL
            //$APPLICATION_ID = "37jCwtiA33xkqCjGdFCbbXLFlifvR2679LAbch7D";
            //$REST_API_KEY = "eo0xSa2PjOYUXKkpeqexArmZPN5qo8dym5txoFAh";

            //$url = 'https://api.parse.com/1/push';
            //$data = array(
            //    'where' => array(
            //        'fireShieldUser' => array(
            //            '$inQuery' => array(
            //                'where' => array(
            //                    'vendorId'=>'6B442EAC-8D9C-47F6-BB24-419E62A68DA9'
            //                ),
            //                'className' => '_User'
            //            ),
            //        ),
            //    ),
            //    'data' => array(
            //        'alert' => 'Test Push From WDS backend',
            //        'sound' => 'default',
            //        'badge' => '1',
            //        'title' => 'Test FT',
            //    ),
            //);
            //$_data = json_encode($data);
            //echo "data=".$_data."<br /><br />";
            //$headers = array(
            //    'X-Parse-Application-Id: ' . $APPLICATION_ID,
            //    'X-Parse-REST-API-Key: ' . $REST_API_KEY,
            //    'Content-Type: application/json',
            //    'Content-Length: ' . strlen($_data),
            //);

            //$curl = curl_init($url);

            //curl_setopt($curl, CURLOPT_POST, 1);
            //curl_setopt($curl, CURLOPT_POSTFIELDS, $_data);
            //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            //curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            //$result = curl_exec($curl);
            //if($result)
            //    echo 'Done. Result: '.$result;
            //else
            //    echo 'Error: '.curl_error($curl);
            //var_dump($result);

            //-------ouath2 api call-----------------------
            //make post request to get authorization code
            //		$post_string = 'client_id=usaaenrollment_site';
            //		$post_string .= '&grant_type=authorization_code';
            //		$post_string .= '&response_type=code';
            //		$post_string .= '&scope=fireshield';
            //		$redirect_uri = 'https://usaaenrollment.wildfire-defense.com/authorization/';
            //		$post_string .= '&return_uri='.urlencode($redirect_uri);
            //		$ch = curl_init();
            //		curl_setopt($ch,CURLOPT_URL, 'https://dev.wildfire-defense.com/api/fireshield/v2/auth/');
            //		curl_setopt($ch, CURLOPT_POST, 5);
            //		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            //		curl_setopt($ch, CURLOPT_HEADER, TRUE);
            //		curl_setopt($ch,CURLOPT_POSTFIELDS, $post_string);
            //		$result = curl_exec($ch);
            //		curl_close($ch);
            //
            //		//strip out the authcode from the results location header which is the return_uri with the code as a GET param
            //		$auth_code = substr($result, stripos($result, 'Location: '.$redirect_uri)); //strip off previous headers
            //		$auth_code = substr($auth_code, 0, stripos($auth_code, "\r\n")); //strip off rest of the content
            //		$auth_code = str_replace('Location: '.$redirect_uri.'?code=', '', $auth_code); //grab just the code param out of the uri
            //		//echo $auth_code;
            //
            //		//make post request to get token
            //		$post_string = 'client_id=usaaenrollment_site';
            //		$post_string .= '&client_secret=cccd82b6-ddee-4321-9b82-bc8d11873471';
            //		$post_string .= '&code='.$auth_code;
            //		$post_string .= '&grant_type=authorization_code';
            //		$post_string .= '&scope=fireshield';
            //		$post_string .= '&redirect_uri='.urlencode($redirect_uri);
            //		$ch = curl_init();
            //		curl_setopt($ch,CURLOPT_URL, 'https://dev.wildfire-defense.com/api/fireshield/v2/token/');
            //		curl_setopt($ch, CURLOPT_POST, 6);
            //		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            //		curl_setopt($ch,CURLOPT_POSTFIELDS, $post_string);
            //		$result = curl_exec($ch);
            //		curl_close($ch);
            //		$result_array = json_decode($result, true);
            //		$access_token = $result_array['access_token'];
            //		//echo $access_token;
            //
            //		//make api call
            //		$ch = curl_init();
            //		curl_setopt($ch,CURLOPT_URL, 'https://dev.wildfire-defense.com/api/fireshield/v2/uploadAssessment/?access_token='.$access_token);
            //
            //		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            //		$zip_file = file_get_contents("C://inetpub//vhosts//reciprocityind.com//dev-subdomain//wds-tyler//protected//fs_reports//abc123.zip");
            //		curl_setopt($ch, CURLOPT_POSTFIELDS, $zip_file);
            //		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/zip", 'Content-Length: ' . strlen($zip_file)));
            //		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            //		$result = curl_exec($ch);
            //		curl_close($ch);
            //		echo $result;


            //		$retVal = array();
            //        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
            //        foreach( $fields as $field ) {
            //            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
            //                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
            //                if( isset($retVal[$match[1]]) ) {
            //                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
            //                } else {
            //                    $retVal[$match[1]] = trim($match[2]);
            //                }
            //            }
            //        }
            //		//here is the header info parsed out
            //		echo '<pre>';
            //		print_r($retVal);
            //		echo '</pre>';
            //		//here is the redirect
            //		if (isset($retVal['Location'])){
            //			 echo $retVal['Location'];
            //		} else {
            //			 //keep in mind that if it is a direct link to the image the location header will be missing
            //			 echo $_GET[$urlKey];
            //		}

            //close connection



            //B2B upload routine
            //		$ch = curl_init();
            //		if(!$ch)
            //		{
            //			$error = curl_error($ch);
            //			die("cURL session could not be initiated.  ERROR: $error.");
            //		}
            //
            //		$report_path = Yii::app()->basePath.'/fs_reports/outgoing/Fireshield_TEST.zip';
            //		$fp = fopen($report_path, 'r');
            //		if(!$fp)
            //		{
            //			$error = curl_error($ch);
            //			die("$report_path could not be read.");
            //		}
            //
            //		curl_setopt($ch, CURLOPT_URL, "sftp://wildfiredef:f3eCHVEi@b2bxb.usaa.com:8022/ReceiveWDSInboundFiles/Fireshield_TEST.zip");
            //		curl_setopt($ch, CURLOPT_UPLOAD, 1);
            //		curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
            //		curl_setopt($ch, CURLOPT_INFILE, $fp);
            //		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($report_path));
            //
            //		//this is where I get the failure
            //		$exec = curl_exec ($ch);
            //		if(!$exec)
            //		{
            //			$error = curl_error($ch);
            //			die("File $docname could not be uploaded.  ERROR: $error.");
            //		}
            //
            //		curl_close ($ch);
            //		echo "SUCCESS!";

		}
		else
			echo "ERROR: you should not be doing this in the produciton environment and this action will be reported.";
	}
}
