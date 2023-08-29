<?php

class PreRiskController extends Controller
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
			'accessControl'
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
                    'index',
                    'admin',
                    'update',
                    'create',
                    'metrics',
                    'calendar',
                    'calendarEventFeed'
                ),
				'users'=>array('@'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}
    
    /**
     * Renders the index page (analytics)
     */
	public function actionIndex()
	{
        //dates for timeframe
        $startDate = date('Y-m', strtotime('-2 years', strtotime(date('Y-m'))));
        $endDate = date('Y-m-d', strtotime('+1 months', strtotime(date('Y-m-d'))));
        
        //Totals used in charts
        $completedTotal = PreRisk::countCompletedAssessments();
        $total = PreRisk::model()->count();
        $assessmentsPerMonth = PreRisk::countCompletedAssessmentsPerMonth($startDate, $endDate);
        $callCampaign = PreRisk::countCallCampaignStatus(date('F'), date('Y'));
        
		$this->render('index',
            array(
                'startDate'=>$startDate,
                'endDate'=>$endDate,
                'completedTotal'=>$completedTotal,
                'total'=>$total,
                'assessmentsPerMonth'=>$assessmentsPerMonth,
                'callCampaign'=>$callCampaign
            )
        );
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new PreRisk;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['PreRisk']))
		{
			$model->attributes=$_POST['PreRisk'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', "Pre Risk Entry ".$model->id." Created Successfully!");
				$this->redirect(array('admin'));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['PreRisk']))
		{
			$model->attributes=$_POST['PreRisk'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', "Pre Risk Entry ".$model->id." Updated Successfully!");
				$this->redirect(array('admin'));
			}
		}
        //Get Form type from URL
        $type = Yii::app()->request->getQuery('type');
                
		$this->render('update',array(
			'model'=>$model,
                        'type'=>$type,
		));
	}
    
    /**
     * Displays the Calendar
     */
    public function actionCalendar()
    {   
        $this->render('calendar');
    }
    
    /**
     * This function gets calendar data based on the date and returns the data as json
     */
    public function actionCalendarEventFeed()
    {
        //$firstMonthDate = date('Y-m-d', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
        //$lastMonthDate = date('Y-m-t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));*/
        
        // TODO: NEEDS TO BE FILTERED BY 'Status' - 'TO BE SCHEDULED'
        
        $engine_num = CHttpRequest::getParam('engine_id');
        
        $date = date('Y-m-d');
        $datas = PreRisk::model()->findAll(array(
            'condition'=>"ha_date >= '$date' and engine = '$engine_num'"
        ));
        
        if ($datas)
        {
            $events = array();
            foreach($datas as $data)
            {
                $formattedDate = date('g:ia, D jS M Y',strtotime($data->ha_date . ' ' . $data->ha_time));
                $events[] = array(
                    'title' => "Assessment - $formattedDate",
                    'fname' => ($data->property && $data->property->member) ? $data->property->member->first_name : 'fname',
                    'lname' => ($data->property && $data->property->member) ? $data->property->member->last_name : 'lname',
                    'address' => ($data->property) ? $data->property->address_line_1 : 'address',
                    'city' => ($data->property) ? $data->property->city : 'city',
                    'state' => ($data->property) ? $data->property->state : 'state',
                    'zip' => ($data->property) ? $data->property->zip : 'zip',
                    'status' => $data->status,
                    'assignedBy' => $data->assigned_by,
                    'date' => $data->ha_date,
                    'time' => $data->ha_time,
                    'start' => date(DateTime::ISO8601,strtotime($data->ha_date . ' ' . $data->ha_time)),
                    'homeownerPresent' => $data->homeowner_to_be_present,
                    'okWithoutHomeowner' => $data->ok_to_do_wo_member_present,
                    'allDay' => false,
                    'appointmentInformation' => $data->appointment_information,
                    'contactDate'=>$data->contact_date,
                    'engine' => $data->engine,
                    'id'=>$data->id
                );
            }
            echo CJSON::encode($events);
        }
    }
    
    /**
     * This function gets engines availible for the calendar
     */
    public function calendarEventEngines()
    {
        $date = date('Y-m-d');
        
        $datas = PreRisk::model()->findAll(array(
            'condition'=>"ha_date >= '$date'",
            'select'=>'engine',
            'distinct'=>true
        ));
        
        if ($datas)
        {
            return array_map(function($data) { return $data->engine; }, $datas);
        }
    }
	
	/**
	 * Grid view of Pre Risk Table
	 */
	public function actionAdmin($download = false, $exportCalendar = false)
	{
        $model = new PreRisk('search');
        $model->unsetAttributes(); //clear any default values

        if(isset($_POST['PreRisk']))
        {
                $_SESSION['wds_pr_searchAttr'] = $_POST['PreRisk'];
                $model->attributes=$_POST['PreRisk'];
        }
        elseif (isset($_SESSION['wds_pr_searchAttr']))
        {
                $model->attributes=$_SESSION['wds_pr_searchAttr'];
        }

        //default cols
        $columnsToShow = array('id', 'member_member_num', 'member_first_name', 'member_middle_name', 'member_last_name', 'status', 'property_address_line_1', 'property_city', 'property_state', 'engine', 'ha_time', 'ha_date', 'call_list_month', 'call_list_year',);
        if(isset($_POST['columnsToShow']))
        {
            $_SESSION['wds_pr_columnsToShow'] = $_POST['columnsToShow'];
            $columnsToShow = $_POST['columnsToShow'];
        }
        elseif(isset($_SESSION['wds_pr_columnsToShow']))
            $columnsToShow = $_SESSION['wds_pr_columnsToShow'];

        if(!$download)
        {
            $pageSize = 25;
            if(isset($_POST['pageSize']))
            {
                $_SESSION['wds_pr_pageSize'] = $_POST['pageSize'];
                $pageSize = $_POST['pageSize'];
            }
            elseif(isset($_SESSION['wds_pr_pageSize']))
                $pageSize = $_SESSION['wds_pr_pageSize'];
        }

        $sort = 'call_list_year.desc';

        if(isset($_POST['PreRisk_sort']))
        {
            $_SESSION['wds_pr_sort'] = $_POST['PreRisk_sort'];
            $sort = $_POST['PreRisk_sort'];
        }
        elseif(isset($_SESSION['wds_pr_sort']))
            $sort = $_SESSION['wds_pr_sort'];

        $advSearch = NULL;
        if(isset($_POST['advSearch']))
        {
            $_SESSION['wds_pr_advSearch'] = $_POST['advSearch'];
            $advSearch = $_POST['advSearch'];
        }
        //check if session advsearch is set (and all the required attributes are set too, or it will break the form)
        elseif(isset($_SESSION['wds_pr_advSearch']))
        {
            $advSearch = $_SESSION['wds_pr_advSearch'];

        }
        else //default, advSearch not set
        {
            $advSearch = array();
            $advSearch['statuses'] = $model->wdsStatuses(); //all statuses selected is default
            $advSearch['completionDate1'] = NULL;
            $advSearch['completionDate2'] = NULL;
            $advSearch['followUpDate1'] = NULL;
            $advSearch['followUpDate2'] = NULL;
            $advSearch['haDateBegin'] = NULL;
            $advSearch['haDateEnd'] = NULL;
        }

        if(array_key_exists('statuses', $advSearch) == false)
            $advSearch['statuses'] = $model->wdsStatuses();
        elseif($advSearch['statuses'] == '')
            $advSearch['statuses'] = $model->wdsStatuses();

        if($download)
        {
            $model->makeDownloadableReport($columnsToShow, $advSearch, $sort);
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"".Yii::app()->user->name."_PRReport.csv\"");
            readfile(Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_PRReport.csv');
        }
        else if ($exportCalendar)
        {
            $errorMessage = $model->writeSchedulesICS($advSearch, $sort);
            if (isset($errorMessage)) {
                Yii::app()->user->setFlash('error', $errorMessage);
                $this->render('admin', array('model' => $model, 'columnsToShow' => $columnsToShow, 'pageSize' => $pageSize, 'advSearch' => $advSearch, 'sort' => $sort));
            }
        }
        else
        {
            $this->render('admin', array('model' => $model, 'columnsToShow' => $columnsToShow, 'pageSize' => $pageSize, 'advSearch' => $advSearch, 'sort' => $sort));
        }
    }
	        
	//does calculations and shows a metrics view for summary data on PreRisk entries
	public function actionMetrics()
	{
		$model = new PreRisk();
		$metricsStatuses = array('CANCELLED - SNOW', 
				'COMPLETED - Delivered to Member', 
				'Contacted 4 Times', 
				'DECEASED', 
				'Declined', 
				'More Info Required', 
				'NEED MORE INFO - Letter Mailed', 
				'NO LONGER INSURED - PER USAA', 
				'Postponed', 
				'Scheduled',
				);
		
		if(isset($_POST) && !empty($_POST['months']) && !empty($_POST['years']) && !empty($_POST['states']))
		{
			$states = $_POST['states'];
			$months = $_POST['months'];
			$years = $_POST['years'];
			$metrics = array();

			$declinedStatuses = array('Declined (Do Not Contact)', 
				'Declined (USAA - Approval Denied)', 
				'Declined (USAA - Approval Pending)', 
				'Declined (USAA - Approved)'
				);
			$postponedStatuses = array('Postponed',
			'Postponed - Fire',
			'Postponed - Previously (Contacted 4 Times)',
			'Postponed - Previously (Declined)',
			'Postponed - Previously (More Info Required)',
			'Postponed - Previously (Previously Canceled - Snow)',
			'Postponed - Previously (Scheduled)',
				);
			$scheduledStatuses = array('Scheduled' => 'Scheduled',
			'Scheduled - Previously (Contacted 4 Times)',
			'Scheduled - Previously (Declined USAA Approval Pending)',
			'Scheduled - Previously (More Info Required)',
			'Scheduled - Previously (Postponed - Fire)',
			'Scheduled - Previously (Postponed)',
			'Scheduled - Previously (Previously Canceled - Snow)',
				);
			
			foreach($metricsStatuses as $status)
			{
				$statePart = '[property].[state] IN (';
				foreach($states as $state)
				{
					$statePart .= "'".$state."',";
                    if($state == 'CA')
                         $statePart .= "'CALIFORNIA',";           
			        elseif($state == 'CO')
                        $statePart .= "'COLORADO',";
			        elseif($state == 'TX')
                        $statePart .= "'TEXAS',";
			        elseif($state == 'NV')
                        $statePart .= "'NEVADA',";
			        elseif($state == 'AZ')
                        $statePart .= "'ARIZONA',";
			        elseif($state == 'NM')
                        $statePart .= "'NEW MEXICO',";
                    elseif($state == 'OR')
                        $statePart .= "'OREGON',";
                    elseif($state == 'UT')
                        $statePart .= "'UTAH',";
                    elseif($state == 'MT')
                        $statePart .= "'MONTANA',";
                    elseif($state == 'ID')
                        $statePart .= "'IDAHO',";
                    elseif($state == 'WA')
                        $statePart .= "'WASHINGTON',";
				}
				$statePart = rtrim($statePart, ',').')';

				$monthPart = '[call_list_month] IN (';
				foreach($months as $month)
				{
					$monthPart .= "'".$month."',";
				}
				$monthPart = rtrim($monthPart, ',').')';

				$yearPart = '[call_list_year] IN (';
				foreach($years as $year)
				{
					$yearPart .= "'".$year."',";
				}
				$yearPart = rtrim($yearPart, ',').')';

				if($status == 'Declined')
				{
					$statusPart = '[status] IN (';
					foreach($declinedStatuses as $declinedStatus)
					{
						$statusPart .= "'".$declinedStatus."',";
					}
					$statusPart = rtrim($statusPart, ',').')';
				}
				else if($status == 'Postponed')
				{
					$statusPart = '[status] IN (';
					foreach($postponedStatuses as $postponedStatus)
					{
						$statusPart .= "'".$postponedStatus."',";
					}
					$statusPart = rtrim($statusPart, ',').')';
				}
				else if($status == 'Scheduled')
				{
					$statusPart = '[status] IN (';
					foreach($scheduledStatuses as $scheduledStatus)
					{
						$statusPart .= "'".$scheduledStatus."',";
					}
					$statusPart = rtrim($statusPart, ',').')';
				}
				else 
				{
					$statusPart = "[status] = '".$status."'";
				}

				$metrics[$status] = $model->with('property')->count($statePart.' AND '.$monthPart.' AND '.$yearPart.' AND '.$statusPart);
			}
				
				
				//THIS WAS FOR EACH STATE BREAK DOWN, SWITCHED TO JUST TOTALS
//				if($state != '')
//				{
//					$metrics[$state] = array();
//					foreach($model->wdsStatuses() as $status)
//					{
//						$state_status_models = $model->findAllByAttributes(array('state' => $state, 'status' => $status, 'call_list_year' => $_POST['PreRisk']['call_list_year'], 'call_list_month' => $_POST['PreRisk']['call_list_month']));
//						$metrics[$state][$status] = count($state_status_models);
//						if(isset($metrics['totals'][$status]))
//							$metrics['totals'][$status] += count($state_status_models);
//						else
//							$metrics['totals'][$status] = count($state_status_models);
//					}
//				}
			
		}
		else
		{
			$metrics = null;
			$states = null;
			$months = null;
			$years = null;
		}

		$this->render('metrics', array('model' => $model, 'metrics' => $metrics, 'months' => $months, 'years' => $years, 'states' => $states, 'metricsStatuses' => $metricsStatuses));
	}
        

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return PreRisk the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=PreRisk::model()->findByPk($id)->with('property', 'member');
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
        

	/**
	 * Performs the AJAX validation.
	 * @param PreRisk $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='pre-risk-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
