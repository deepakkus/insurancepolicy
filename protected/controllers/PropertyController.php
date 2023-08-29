<?php

class PropertyController extends Controller
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
                    WDSAPI::SCOPE_DASH => array(
                        'apiEnroll',
                        'apiLoadPolicyholders',
                        'apiGetPolicyNotice',
                        'apiGetPolicyNoticeQueryCount',
                        'apiGetRealtimePoliciesForFire',
                        'apiGetPolicyholderByProperty',
                        'apiGetPolicyholdersByFire',
                        'apiGetByMemberNumber',
                        'apiGetPolicyStatusChanges',
                        'apiCreateContactEntry',
                        'apiGetContactEntry',
                        'apiUpdateContactEntry',
                        'apiCountNewEnrollments',
                        'apiCountCurrentEnrollments',
                        'apiGetAllPropertyModels',
                        'apiGetCallAttempts'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetPolicyForEngine',
                        'apiGetPolicyForEngineQueryCount'
                    ),
                    WDSAPI::SCOPE_USAAENROLLMENT => array(
                        'apiEnroll',
                    )
                )
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
                    'create',
                    'update',
                    'refreshRisk'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin", Yii::app()->user->types) || in_array("Manager", Yii::app()->user->types)'
			),
			array('allow',
				'actions'=>array(
                    'admin',
                    'view',
                    'propertyAccess',
                    'getGeoJson',
                    'getWdsGeocodeReport',
                    'downloadUnmatchedByClient'
                ),
				'users'=>array('@')
			),
			array('allow',
				'actions'=>array(
                    'getGeoJson',
                    'apiEnroll',
                    'apiLoadPolicyholders',
                    'apiGetPolicyNotice',
                    'apiGetPolicyNoticeQueryCount',
                    'apiGetPolicyForEngine',
                    'apiGetPolicyForEngineQueryCount',
                    'apiGetRealtimePoliciesForFire',
                    'apiGetPolicyholderByProperty',
                    'apiGetPolicyholdersByFire',
                    'apiGetByMemberNumber',
                    'apiGetPolicyStatusChanges',
                    'apiCreateContactEntry',
                    'apiGetContactEntry',
                    'apiUpdateContactEntry',
                    'apiCountNewEnrollments',
                    'apiCountCurrentEnrollments',
                    'apiGetAllPropertyModels',
                    'apiGetCallAttempts'
                 ),
				'users'=>array('*')
            ),
			array('deny',
				'users'=>array('*')
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
	public function loadModel($pid)
	{
		$model = Property::model()->findByPk($pid);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Updates a particular model.
	 * @param integer $pid the ID of the model to be updated
	 */
	 /*
	public function actionUpdate($pid)
	{
		$property = $this->loadModel($pid);
		$member = $property->member;

        // Get the status history for this property.
        $responseStatusHistory = $property->getStatusHistory('response_status');
        $fireShieldStatusHistory = $property->getStatusHistory('fireshield_status');
        $preRiskStatusHistory = $property->getStatusHistory('pre_risk_status');
        $policyStatusHistory = $property->getStatusHistory('policy_status');

        // Additional Contacts
        $additionalContacts = new CActiveDataProvider('Contact', array(
			'sort' => array('defaultOrder' => array('priority' => false)),
			'criteria' => array(
                'condition' => 'property_pid='.$pid,
            ),
		));

        // Get Enrollment changes done on the dashboard
        $wdsFireEnrollments = new CActiveDataProvider('WdsFireEnrollments', array(
			'criteria' => array(
                'order'=>'date DESC',
                'condition' => 'pid='.$pid,
            ),
		));

        // Location History
        $locationHistory = new CActiveDataProvider('PropertiesLocationHistory', array(
            'sort' => array('defaultOrder' => array('id' => CSort::SORT_DESC)),
            'criteria' => array(
                'condition' => 'property_pid = :pid',
                'params' => array(':pid' => $pid)
            )
        ));

        // Property Files
        $propertyFiles = new CActiveDataProvider('PropertiesFile', array(
            'sort' => array('defaultOrder' => array('id' => CSort::SORT_DESC)),
            'criteria' => array(
                'condition' => 'property_pid = :pid',
                'params' => array(':pid' => $property->pid)
            )
        ));

        // Property
		if(isset($_POST['Property']))
		{
			$property->attributes = $_POST['Property'];
            
			if($property->save())
			{
				Yii::app()->user->setFlash('success', "Property $pid Updated Successfully!");
				$this->redirect(array('admin',));

                // TODO: set this if coming from the member update page...
                //$this->redirect(array('member/update&mid=' . $property->member_mid));
			}
		}

		$this->render('update',array(
			'property'=>$property,
			'member'=>$member,
            'responseStatusHistory' => $responseStatusHistory,
            'fireShieldStatusHistory' => $fireShieldStatusHistory,
            'preRiskStatusHistory' => $preRiskStatusHistory,
            'policyStatusHistory' => $policyStatusHistory,
            'additionalContacts' => $additionalContacts,
            'wdsFireEnrollments' => $wdsFireEnrollments,
            'locationHistory' => $locationHistory,
            'propertyFiles' => $propertyFiles
		));
	}
	*/
   
	/**
	 * Views a particular member details.
	 * @param integer $mid the ID of the member to be viewed
	 */
	public function actionView($pid)
	{
		$property = $this->loadModel($pid);
		$member = $property->member;

        // Get the status history for this property.
        $responseStatusHistory = $property->getStatusHistory('response_status');
        $fireShieldStatusHistory = $property->getStatusHistory('fireshield_status');
        $preRiskStatusHistory = $property->getStatusHistory('pre_risk_status');
        $policyStatusHistory = $property->getStatusHistory('policy_status');

        // Get Enrollment changes done on the dashboar
        $wdsFireEnrollments = new CActiveDataProvider('WdsFireEnrollments', array(
			'criteria' => array(
                'order'=>'date DESC',
                'condition' => 'pid = :pid',
                'params' => array(':pid' => $pid)
            ),
		));

        // Additional Contacts
        $additionalContacts = new CActiveDataProvider('Contact', array(
			'sort' => array('defaultOrder' => array('priority' => CSort::SORT_ASC)),
			'criteria' => array(
                'condition' => 'property_pid = :pid',
                'params' => array(':pid' => $pid)
            ),
		));

        // Location History
        $locationHistory = new CActiveDataProvider('PropertiesLocationHistory', array(
            'sort' => array('defaultOrder' => array('id' => CSort::SORT_DESC)),
            'criteria' => array(
                'condition' => 'property_pid = :pid',
                'params' => array(':pid' => $pid)
            )
        ));

        // Property Files
        $propertyFiles = new CActiveDataProvider('PropertiesFile', array(
            'sort' => array('defaultOrder' => array('id' => CSort::SORT_DESC)),
            'criteria' => array(
                'condition' => 'property_pid = :pid',
                'params' => array(':pid' => $property->pid)
            )
        ));

        // Property Access
        $propertyAccess = ResPropertyAccess::model()->findByAttributes(array('property_id' => $pid));
        if (!isset($propertyAccess))
            $propertyAccess = new ResPropertyAccess;

		$this->render('view',array(
			'property'=>$property,
			'member'=>$member,
            'responseStatusHistory' => $responseStatusHistory,
            'fireShieldStatusHistory' => $fireShieldStatusHistory,
            'preRiskStatusHistory' => $preRiskStatusHistory,
            'policyStatusHistory' => $policyStatusHistory,
            'additionalContacts' => $additionalContacts,
            'locationHistory' => $locationHistory,
            'propertyFiles' => $propertyFiles,
            'propertyAccess' => $propertyAccess,
			'readOnly' => true,
            'wdsFireEnrollments' => $wdsFireEnrollments,
		));
	}

    /**
     * Updated WDSrisk score for property in risk_score table
     * @param integer $pid
     */
    public function actionRefreshRisk($pid)
    {
        $property = $this->loadModel($pid);
        $refreshRiskForm = new RefreshRiskForm;

        if (isset($_POST['RefreshRiskForm']))
        {
			$refreshRiskForm->attributes = $_POST['RefreshRiskForm'];
            if ($refreshRiskForm->validate())
            {
                $riskScore = new RiskScore();
                $result = $riskScore->setRiskScore($property, $refreshRiskForm->id);
                if ($result['error'] === false)
                    Yii::app()->user->setFlash('success', "Property has been updated in the risk_score table for pid: $pid.<br />" . $result['message']);
                else
                    Yii::app()->user->setFlash('error', "Something went wrong getting WDSrisk for pid: $pid.<br />" . $result['message']);
                $this->redirect(array('view', 'pid' => $pid));
            }
        }

		$this->render('refresh_risk', array(
            'property' => $property,
            'refreshRiskForm' => $refreshRiskForm
		));
    }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($member_mid) //can only be created from an existing member
	{
		$property = new Property;
		$member = Member::model()->findByPk($member_mid);

		if(isset($_POST['Property']))
		{
			$property->attributes = $_POST['Property'];
			if($property->save())
			{
                //save member contact info
                if (!empty($member->home_phone))
                    Helper::savePropContact($property->pid, 'home', 'Primary 1', $member->first_name.' '.$member->last_name, 'Self', $member->home_phone, null);
                if (!empty($member->work_phone))
                    Helper::savePropContact($property->pid, 'work', 'Primary 2', $member->first_name.' '.$member->last_name, 'Self', $member->work_phone, null);
                if (!empty($member->cell_phone))
                    Helper::savePropContact($property->pid, 'cell', 'Primary 3', $member->first_name.' '.$member->last_name, 'Self', $member->cell_phone, null);
                if (!empty($member->email_1))
                    Helper::savePropContact($property->pid, 'email', 'Primary 4', $member->first_name.' '.$member->last_name, 'Self', $member->email_1, null);
                if (!empty($member->email_2))
                    Helper::savePropContact($property->pid, 'email', 'Secondary 1', $member->first_name.' '.$member->last_name, 'Self', $member->email_2, null);

				Yii::app()->user->setFlash('success', "Property ".$property->pid." Created Successfully!");
				$this->redirect(array('admin',));
			}
		}

        //Set a few default values
		$property->member_mid = $member->mid;
        $property->client_id = $member->client_id;
        $property->type_id = 1;

		$this->render('create',array(
			'property'=>$property,
			'member'=>$member,
		));
	}

	/**
	 * Grid view of Properties
	 */
	public function actionAdmin()
	{
        //Temporary duct tape to speed up this search - need to impliment this through the config once the character set is figured out
        Yii::app()->db->setAttribute(PDO::SQLSRV_ATTR_ENCODING,PDO::SQLSRV_ENCODING_SYSTEM);
		$properties = new Property('search');
		$properties->unsetAttributes(); //clear any default values
        //set type_id to default of PIF
        $properties->type_id = 1;

        if(isset($_GET['reset']))
            unset($_SESSION['wds_prop_filters'], $_SESSION['wds_prop_columnsToShow'], $_SESSION['wds_prop_pageSize'], $_SESSION['wds_prop_sort'],$_SESSION['wds_prop_advSearch'], $_COOKIE['wds_prop_filters'], $_COOKIE['wds_prop_columnsToShow'], $_COOKIE['wds_prop_pageSize'], $_COOKIE['wds_prop_sort'],$_COOKIE['wds_prop_advSearch']);

        if(isset($_GET['Property']))
        {
            $properties->attributes = $_GET['Property'];
            $_SESSION['wds_prop_filters'] = $_GET['Property'];
        }
        elseif(isset($_SESSION['wds_prop_filters']))
            $properties->attributes = $_SESSION['wds_prop_filters'];

        $columnOrder = array(
            10 => 'client_id',
            20 => 'pid',
            30 => 'member_mid',
            40 => 'type_id',
            50 => 'agent_id',
            60 => 'member_member_num',
            70 => 'policy',
            80 => 'location',
            90 => 'member_first_name',
            100 => 'member_last_name',
            110 => 'address_line_1',
            120 => 'address_line_2',
            130 => 'city',
            140 => 'state',
            150 => 'county',
            160 => 'zip',
            170 => 'zip_supp',
            180 => 'lat',
            190 => 'long',
            200 => 'comments',
            210 => 'policy_status',
            220 => 'policy_status_date',
            230 => 'policy_effective',
            240 => 'policy_expiration',
            250 => 'response_status',
            260 => 'res_status_date',
            265 => 'response_enrolled_date',
            270 => 'response_auto_enrolled',
            280 => 'fireshield_status',
            290 => 'fs_status_date',
            300 => 'pre_risk_status',
            310 => 'pr_status_date',
            320 => 'member_home_phone',
            330 => 'member_work_phone',
            340 => 'member_cell_phone',
            350 => 'member_email_1',
            360 => 'rated_company',
            370 => 'coverage_a_amt',
            380 => 'producer',
            390 => 'agency_code',
            400 => 'agency_name',
            410 => 'lob',
            420 => 'rated_company',
            430 => 'geocode_level',
            440 => 'geo_risk',
            450 => 'dwelling_type',
            460 => 'multi_family',
            470 => 'roof_type',
            480 => 'app_status',
            490 => 'fs_assessments_allowed',
            500 => 'question_set_id',
            510 => 'member_fs_carrier_key',
            520 => 'wds_geocode_level',
            530 => 'wds_geocoder',
            540 => 'wds_match_address',
            550 => 'wds_match_score',
            560 => 'wds_geocode_date',
            570 => 'wds_lat',
            580 => 'wds_long',
            590 => 'wds_risk',
            600 => 'last_update',
            610 => 'flag',
            620 => 'transaction_type',
            630 => 'transaction_effective',
            640 => 'property_access_gate_code',

        );

		//default cols
		$columnsToShow = array(
            5 => 'client_id',
            10 => 'pid',
            20 => 'member_mid',
            30 => 'member_first_name',
            40 => 'member_last_name',
            50 => 'address_line_1',
            60 => 'city',
            70 => 'state',
            80 => 'geo_risk',
            90 => 'policy',
            100 => 'policy_status',
            110 => 'fireshield_status',
            120 => 'response_status',
            130 => 'pre_risk_status',
            140 => 'type_id',

        );

        if(isset($_GET['columnsToShow']))
        {
            $_SESSION['wds_prop_columnsToShow'] = $_GET['columnsToShow'];
            $_COOKIE['wds_prop_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        elseif(isset($_SESSION['wds_prop_columnsToShow']))
            $columnsToShow = $_SESSION['wds_prop_columnsToShow'];
        elseif(isset($_COOKIE['wds_prop_columnsToShow']))
            $columnsToShow = $_COOKIE['wds_prop_columnsToShow'];
        $pageSizeMethod = NULL;
		$pageSize = 25;
		if(isset($_GET['pageSize']))
		{
			$_SESSION['wds_prop_pageSize'] = $_GET['pageSize'];
            $_COOKIE['wds_prop_pageSize'] = $_GET['pageSize'];
			$pageSize = $_GET['pageSize'];
            $pageSizeMethod = 'post';
		}
		elseif(isset($_SESSION['wds_prop_pageSize']))
			$pageSize = $_SESSION['wds_prop_pageSize'];
        elseif(isset($_COOKIE['wds_prop_pageSize']))
			$pageSize = $_COOKIE['wds_prop_pageSize'];

        $sort = 'pid';

        if(isset($_GET['Property_sort']))
        {
        	$_SESSION['wds_prop_sort'] = $_GET['Property_sort'];
            $_COOKIE['wds_prop_sort'] = $_GET['Property_sort'];
            $sort = $_GET['Property_sort'];
        }
        elseif(isset($_SESSION['wds_prop_sort']))
            $sort = $_SESSION['wds_prop_sort'];
        elseif(isset($_COOKIE['wds_prop_sort']))
            $sort = $_COOKIE['wds_prop_sort'];

        $advSearch = NULL;

        if(isset($_GET['advSearch']))
        {
            $_SESSION['wds_prop_advSearch'] = $_GET['advSearch'];
            $_COOKIE['wds_prop_advSearch'] = $_GET['advSearch'];
            $advSearch = $_GET['advSearch'];
        }
        elseif(isset($_SESSION['wds_prop_advSearch']))
            $advSearch = $_SESSION['wds_prop_advSearch'];
        elseif(isset($_COOKIE['wds_prop_advSearch']))
            $advSearch = $_COOKIE['wds_prop_advSearch'];
		else
		{
			$advSearch = array();
            $advSearch['clients'] = array();
            $advSearch['states'] = array();
			$advSearch['fs_statuses'] = array();
            $advSearch['response_statuses'] = array();
            $advSearch['policy_statuses'] = array();
            $advSearch['resEnrolledDateBegin'] = NULL;
            $advSearch['resEnrolledDateEnd'] = NULL;
            $advSearch['policyEffDateBegin'] = NULL;
            $advSearch['policyEffDateEnd'] = NULL;
            $advSearch['lastUpdateDateBegin'] = NULL;
            $advSearch['lastUpdateDateEnd'] = NULL;
            $advSearch['member_first_name'] = NULL;
            $advSearch['member_last_name'] = NULL;
            $advSearch['address_line_1'] = NULL;
		}

		$this->render('admin', array(
            'properties' => $properties,
            'columnsToShow' => $columnsToShow,
            'pageSize' => $pageSize,
            'advSearch' => $advSearch,
            'sort' => $sort,
            'columnOrder'=>$columnOrder,
            'pageSizeMethod' => $pageSizeMethod
        ));
	}

	/**
     * View to edit property access form
     */
	 /*
    public function actionPropertyAccess($pid)
    {
        $propertyAccess = ResPropertyAccess::model()->findByAttributes(array('property_id' => $pid));

        if (!isset($propertyAccess))
        {
            $propertyAccess = new ResPropertyAccess();
            $propertyAccess->property_id = $pid;
        }

		if (isset($_POST['ResPropertyAccess']))
		{
			$propertyAccess->attributes = $_POST['ResPropertyAccess'];

            if ($propertyAccess->save())
            {
                Yii::app()->user->setFlash('success', 'Property details were updated successfully!');
                $this->redirect(array('view', 'pid' => $propertyAccess->property_id));
            }
		}

        $this->render('property_access', array(
            'propertyAccess' => $propertyAccess
        ));
    }
	*/
    /**
     * Returns a policyholder geojson object for rendering in a map
     */
	public function actionGetGeoJson($perimeterID = null, $bufferDistance, $clientID = null)
	{
        echo json_encode(Property::getGeoJson($perimeterID, $bufferDistance, $clientID));
    }

    /**
     * Download an excel report of wds found policies that are 1 miles from client given coordinates
     */
    public function actionGetWdsGeocodeReport()
    {
        $results = GIS::getWdsGeocodeReport();

        Yii::import('application.vendors.PHPExcel.*');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('WDS Geocode Status Report')
            ->setSubject('WDS Geocode Status Report')
            ->setDescription('WDS Geocode Status Report');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('WDS Geocode Status Report');

        // Setting header
        $header = array('PID','Address','City','State','Zip','Client','Client Lat','Client Long','Geocode Level','Policy','Reponse Status','WDS Lat','WDS Long','Miles Apart');

        $style = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 11,
                'bold' => true,
                'color' => array('rgb' => '1F497D')
            ),
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('rbg' => '4F81BD')
                )
            )
        );

        $headerRange = PHPExcel_Cell::stringFromColumnIndex(0) . '1:' . PHPExcel_Cell::stringFromColumnIndex(count($header) - 1) . '1';
        $activeSheet->getStyle($headerRange)->applyFromArray($style);
        $activeSheet->getRowDimension(1)->setRowHeight(20);
        $activeSheet->fromArray($header, null, 'A1');

        $row = 2;
        foreach ($results as $result)
        {
            // Removing meters from excel output
            array_splice($result, count($result) - 2, 1);
            $activeSheet->fromArray($result, null, 'A' . $row);
            $row++;
        }

        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell)
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="WDS Geocode Status Report ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * Downloads and excel list of the unmatched properties for a given client
     */
	public function actionDownloadUnmatchedByClient($clientID)
	{

        $sql = "
            select
	            m.first_name,
	            m.last_name,
	            p.address_line_1,
	            p.city,
	            p.state,
	            p.policy_effective,
	            p.response_status
            from
	            properties p
            inner join
	            members m on m.mid = p.member_mid
            where
	            p.wds_geocode_level = 'unmatched'
	            and p.client_id = :client_id
	            and p.type_id = 1
	            and p.policy_status = 'active'
        ";

        $properties = Yii::app()->db->createCommand($sql)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->queryAll();

        Yii::import('application.vendors.PHPExcel.*');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('WDS Unmatched')
            ->setSubject('WDS Unmatched')
            ->setDescription('WDS Unmatched');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('WDS Unmatched');

        // Setting header
        $header = (isset($properties[0])) ? array_keys($properties[0]) : array('No Properties');

        $style = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 11,
                'bold' => true,
                'color' => array('rgb' => '1F497D')
            ),
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('rbg' => '4F81BD')
                )
            )
        );

        $headerRange = PHPExcel_Cell::stringFromColumnIndex(0) . '1:' . PHPExcel_Cell::stringFromColumnIndex(count($header) - 1) . '1';
        $activeSheet->getStyle($headerRange)->applyFromArray($style);
        $activeSheet->getRowDimension(1)->setRowHeight(20);
        $activeSheet->fromArray($header, null, 'A1');

        $row = 2;
        foreach ($properties as $result)
        {
            $activeSheet->fromArray($result, null, 'A' . $row);
            $row++;
        }

        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell){
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="WDS Unmatched ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * API Method: property/apiEnroll
     * Description: Enrolls a property in the response program.
     *
     * Post data parameters:
     * @param int property_id - ID of the property to enroll
     * @param string response_status - (optional) allows the property's response status to be set to either 'enrolled' or 'not enrolled'.
     *
     * If response_status is 'enrolled', an email is sent to the correct recipients
     *
     * Post data example:
     * {
     *     "data": {
     *         "property_id": 123
     *     }
     * }
     */
	public function actionApiEnroll()
	{
        $data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('property_id')))
			return;

        $property = Property::model()->findByPk($data['property_id']);

        if (!isset($property))
        {
            return WDSAPI::echoJsonError('ERROR: property does not exist.', 'This property does not exist for Wildfire Defense Systems.');
        }

        $responseStatus = 'enrolled';

        if (isset($data['response_status']))
        {
            if ($data['response_status'] === 'not enrolled')
                $responseStatus = 'not enrolled';
            else if ($data['response_status'] === 'declined')
                $responseStatus = 'declined';
            else if ($data['response_status'] === 'enrolled')
                $responseStatus = 'enrolled';
            else if ($data['response_status'] === 'ineligible')
                $responseStatus = 'ineligible';
            else
                return WDSAPI::echoJsonError("ERROR: an invalid response_status was provided. Must be either 'enrolled','not enrolled', 'ineligible', or 'declined'.");
        }

        $property->response_status = $responseStatus;
        $property->response_enrolled_date = $responseStatus === 'enrolled' ? date('Y-m-d H:i:s') : null;
        $property->res_status_date = date('Y-m-d H:i:s');

        if (!$property->save())
            return WDSAPI::echoJsonError('ERROR: failed to save the property.', 'Failed to enroll this property in the Wildfire Defense Systems database.');


        // Check to see if policyholder is currently being triggered on a fire, and if so notify WDS
        if ($responseStatus == 'enrolled')
        {
            if (isset($data['fireID']) && !empty($data['fireID']))
            {
                // Fire Dashboard
                pclose(popen('start php ' . Yii::app()->basePath . DIRECTORY_SEPARATOR  . 'yiic resenrollemail ' . $property->pid  . ' ' . $data['fireID'], 'r'));
            }
            else
            {
                // USAA enrollment website
                pclose(popen('start php ' . Yii::app()->basePath . DIRECTORY_SEPARATOR  . 'yiic resenrollemail ' . $property->pid, 'r'));
            }
        }

        // Return success.
        $returnArray = array();
        $returnArray['error'] = 0;

        WDSAPI::echoResultsAsJson($returnArray);
	}

	/**
     * API Method: property/apiGetPolicyNoticeQueryCount
     * Description: Gets count policyholder information for a given notice and client.
     *
     * Post data parameters:
     * @param integer noticeID
     * @param integer clientID
     * @param array compareArray - (optional) associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "noticeID": 123,
     *         "clientID": 9,
     *          "compareArray": {
     *              "column1": "text",
     *              "column2": "text"
     *          }
     *     }
     * }
     */
    public function actionApiGetPolicyNoticeQueryCount()
    {
		$data = NULL;
        $returnArray = array();

		if (!WDSAPI::getInputDataArray($data, array('noticeID', 'clientID')))
			return;

        $realTime = isset($data['realTime']) ? $data['realTime'] : null;

        $criteria = new CDbCriteria();
        $criteria->addCondition('t.notice_id = :notice_id');
        $criteria->addCondition('t.client = :client_id');
        $criteria->params = array(':notice_id' => $data['noticeID'], ':client_id' => $data['clientID']);
        $criteria->select = array('t.property_pid','t.response_status','t.distance','t.threat');
        $criteria->with = array(
            'property' => array(
                'select' => array('pid','member_mid','address_line_1','producer','agency_code', 'agency_phone')
            ),
            'property.member' => array(
                'select' => array('mid','first_name','last_name')
            )
        );

        foreach ($data['compareArray'] as $key => $value)
        {
            if ($key === 'fname')
                $criteria->addSearchCondition('member.first_name', $value);
            else if ($key === 'lname')
                $criteria->addSearchCondition('member.last_name', $value);
            else if ($key === 'address')
                $criteria->addSearchCondition('property.address_line_1', $value);
            else if ($key === 'snapshot_status')
            {
                $criteria->addCondition(($realTime ? 'property' : 't') . '.response_status = :value');
                $criteria->params[':value'] = $value;
            }
            else if ($key === 'threat')
                $criteria->addSearchCondition('t.threat', $value);
            else if ($key === 'distance')
                $criteria->compare('t.distance', $value); // compare allows '<' or '>' comparisons
            else if ($key === 'producer')
                $criteria->addSearchCondition('property.producer', $value);
            else if ($key === 'agency_code')
                $criteria->addSearchCondition('property.agency_code', $value);
            else if ($key === 'agency_phone')
                $criteria->addSearchCondition('property.agency_phone', $value);
        }

        $count = ResTriggered::model()->count($criteria);

        $returnArray['data'] = $count;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

	/**
     * API Method: property/apiGetPolicyNotice
     * Description: Gets policyholder information for a given notice and client.
     *
     * Post data parameters:
     * @param integer noticeID
     * @param integer clientID
     * @param integer limit - used for limiting number of results
     * @param integer offset - used for pagination
     * @param array sortArray - associative array of column => SORT_ASC/SORT_DESC for sorting
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "noticeID": 123,
     *         "clientID": 9,
     *          "limit": 20,
     *          "offset": 60,
     *          "sortArray": {
     *              "lname": 3
     *          }
     *          "compareArray": {
     *              "column1": "text",
     *              "column2": "text"
     *          }
     *     }
     * }
     */
    public function actionApiGetPolicyNotice()
	{
		$data = NULL;
        $returnData = array();
        $returnArray = array();

		if (!WDSAPI::getInputDataArray($data, array('noticeID', 'clientID')))
			return;

        $realTime = isset($data['realTime']) ? $data['realTime'] : null;
        
        $sortDirection = function($sort)
        {
            return ($sort == SORT_ASC) ? 'ASC' : 'DESC';
        };
        
        $criteria = new CDbCriteria();
        $criteria->select = array('t.property_pid','t.response_status','t.priority','t.distance','t.threat');
        $criteria->with = array(
            'property' => array(
                'select' => array('pid','member_mid','address_line_1','city','state','zip','coverage_a_amt','policy','producer', 'lob', 'response_status','pre_risk_status','fireshield_status','agency_code','agency_name','location','agency_phone')
            ),
            'property.member' => array(
                'select' => array('mid','first_name','last_name','salutation','member_num','home_phone','work_phone','cell_phone','email_1')
            ),
            'property.ph_visits' => array(
                'select' => array('status', 'id', 'property_pid', 'review_status', 'date_updated', 'date_action')
            )
        );
        $criteria->addCondition('t.notice_id = :notice_id');
        $criteria->addCondition('t.client = :client_id');
        $criteria->limit = $data['limit'];
        $criteria->offset = $data['offset'];
        $criteria->params = array(':notice_id' => $data['noticeID'], ':client_id' => $data['clientID']);

        // Sorting
        
        $sortArray = array();
        foreach ($data['sortArray'] as $key => $sort)
        {
            
            if ($key === 'fname')
                $sortArray[] = 'member.first_name ' . $sortDirection($sort);
            else if ($key === 'lname')
                $sortArray[] = 'member.last_name ' . $sortDirection($sort);
            else if ($key === 'snapshot_status')
                $sortArray[] = ($realTime ? 'property' : 't') . '.response_status ' . $sortDirection($sort);
            else if ($key === 'distance')
                $sortArray[] = 't.distance ' . $sortDirection($sort);
            else if ($key === 'threat')
                $sortArray[] = 't.threat ' . $sortDirection($sort);
            else if ($key === 'producer')
                $sortArray[] = 'property.producer ' . $sortDirection($sort);
            else if ($key === 'agency_code')
                $sortArray[] = 'property.agency_code ' . $sortDirection($sort);
            else if ($key === 'agency_phone')
                $sortArray[] = 'property.agency_phone ' . $sortDirection($sort);
        }
        
        $criteria->order = implode(',', $sortArray);

        // Searching

        foreach ($data['compareArray'] as $key => $value)
        {
            if ($key === 'fname')
            {
                $criteria->addSearchCondition('member.first_name', $value);
            }
            else if ($key === 'lname')
            {
                $criteria->addSearchCondition('member.last_name', $value);
            }
            else if ($key === 'address')
            {
                $criteria->addSearchCondition('property.address_line_1', $value);
            }
            else if ($key === 'snapshot_status')
            {
                $criteria->addCondition(($realTime ? 'property' : 't') . '.response_status = :value');
                $criteria->params[':value'] = $value;
            }
            else if ($key === 'threat')
            {
                $criteria->addSearchCondition('t.threat', $value);
            }
            else if ($key === 'distance')
            {
                $criteria->compare('t.distance', $value); // compare allows '<' or '>' comparisons
            }
            else if ($key === 'status')
            {
                $criteria->addCondition('ph_visits.status = :value');
                $criteria->params[':value'] = $value;
            }
            else if ($key === 'producer')
            {
                $criteria->addSearchCondition('property.producer', $value);
            }
            else if ($key === 'agency_code')
            {
                $criteria->addSearchCondition('property.agency_code', $value);
            }
            else if ($key === 'agency_phone')
            {
                $criteria->addSearchCondition('property.agency_phone', $value);
            }
        }
          
        // Find models

        $model = ResTriggered::model();
        $model->setDbCriteria($criteria);
        
        $triggeredData = $model->findAll($criteria);
        $returnArray['data'] = $triggeredData;
        $returnArray['error'] = 0;
        foreach ($triggeredData as $triggered)
        {
            if (!isset($triggered->property))
                continue;

            $property = $triggered->property;

            if (!isset($property->member))
                continue;

            $member = $property->member;
            $memberNumber = $member->member_num;
            if (!isset($property-> ph_visits))
                continue;

            $phVStatus = '';
            $historyModels = '';
            $res_ph_visit = '';
            $phVActionDate = '';

            $ph_visits = $property->ph_visits;

             foreach($ph_visits as $phV)
             {
                 if($phV -> property_pid == $property->pid)
                 {
                      if($phV -> review_status == 'published')
                      {
                            $phVStatus =  $phV -> status;
                            $phVActionDate =  date("m/d/Y",strtotime($phV -> date_action));
                      }
                    if($phV -> review_status == 're-review')
                    {
                        $historyModels = ResPhVisit::getVisitHistory($phV -> id);
                        foreach($historyModels as $historyModel)
                        {

                            if($historyModel['review_status'] == 'published')
                            {
                            $res_ph_visit = ResPhVisit::model()->find(array('condition'=>'id='.$historyModel['id']));
                                if(date("Y-m-d h:i:s",strtotime($res_ph_visit -> date_updated)) == date("Y-m-d h:i:s",strtotime($historyModel['date_updated'])))
                                      {
                                        $phVStatus =  $historyModel['status'];
                                      }
                            }

                         }
                     }
                  }
             }
            
            $entry = array(
                'pid' => $property->pid,
                'fname' => ($member->first_name)?$member->first_name:'No Information',
                'lname' => ($member->last_name)?$member->last_name:'No Information',
                'salutation' => ($member->salutation)?$member->salutation:'No Information',
                'address' => ($property->address_line_1)?$property->address_line_1:'No Information',
                'city' => ($property->city)?$property->city:'No Information',
                'state' => ($property->state)?$property->state:'No Information',
                'zip' => ($property->zip)?$property->zip:'No Information',
                'coverage' => ($property->coverage_a_amt)?$property->coverage_a_amt:'No Information',
                'policy' => ($property->policy)?$property->policy:'No Information',
                'member_number' => ($memberNumber)?$memberNumber:'No Information',
                'producer' => ($property->producer)?$property->producer:'No Information',
                'lob' => ($property->lob)?$property->lob:'No Information',
                'response_status' => $property->response_status,
                'pre_risk_status' => ($property->pre_risk_status == 'enrolled') ? 'YES' : 'NO',
                'fireshield_status' => ($property->fireshield_status)?$property->fireshield_status:'No Information',
                'home_phone' => ($member->home_phone)?$member->home_phone:'No Information',
                'work_phone' => ($member->work_phone)?$member->work_phone:'No Information',
                'cell_phone' => ($member->cell_phone)?$member->cell_phone:'No Information',
                'email'=>($member->email_1)?$member->email_1:'No Information',
                'snapshot_status' => ($triggered->response_status)?$triggered->response_status:'No Information',
                'priority' => ($triggered->priority)?$triggered->priority:'No Information',
                'distance' => ($triggered->distance)?round($triggered->distance, 2):'No Information',
                'threat' => $triggered->threat,
                'agency_code' => ($property->agency_code)?$property->agency_code:'No Information',
                'agency_name' => ($property->agency_name)?$property->agency_name:'No Information',
                'location' => ($property->location)?$property->location:'No Information',
                'status' => ($phVStatus) ? $phVStatus : 'No Information',
                'agency_phone' => ($property->agency_phone)?$property->agency_phone:'No Information', 
                'date_action' => ($phVActionDate) ? $phVActionDate : 'No Information'
            );
            // Not all clients need producer info
            if (isset($data['producer']) && $data['producer'])
                $entry['producer'] = $property->producer;
            
            $returnData[] = $entry;
        }
        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;
        
        return WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
     * API Method: property/apiGetPolicyForEngineQueryCount
     * Description: Gets count policyholder information for the most recent notice
     *
     * Post data parameters:
     * @param integer fireID
     * @param array clientIDs
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": 378,
     *         "clientIDs": [3, 7, 1004],
     *          "compareArray": {
     *              "column1": "text",
     *              "column2": "text"
     *          }
     *     }
     * }
     */
    public function actionApiGetPolicyForEngineQueryCount()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('fireID', 'clientIDs')))
            return;

        if (!is_array($data['clientIDs']))
            return WDSAPI::echoJsonError('Incorrect parameter', '"clientIDs" must be an array');

        // For each client figure out the most recent notice for the given fire
        $sql = 'SELECT MAX(notice_id) notice_id
        FROM res_notice
        WHERE client_id IN (' . implode(',', $data['clientIDs']) . ') AND fire_id = :fire_id
        GROUP BY client_id';

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':fire_id', $data['fireID'], PDO::PARAM_INT)
            ->queryAll();

        $noticeIDs = array_map(function($result) { return $result['notice_id']; }, $results);

        // Create query to get policy count
        $criteria = new CDbCriteria();
        $criteria->addInCondition('notice_id', $noticeIDs);
        $criteria->select = array('id','distance','response_status','priority','distance','threat');
        $criteria->with = array(
            'property' => array(
                'select' => array('pid','address_line_1','city','state','zip','coverage_a_amt','policy','producer','response_status','pre_risk_status','fireshield_status','response_enrolled_date','wds_lob')
            ),
            'property.member' => array(
                'select' => array('mid','member_num','first_name','last_name','salutation','home_phone','work_phone','cell_phone','email_1','client')
            ),
            'property.res_property_access' => array(
                'select' => array('access_issues','gate_code','suppression_resources','other_info')
            )
        );

        // Searching

        if (isset($data['compareArray']))
        {
            foreach ($data['compareArray'] as $key => $value)
            {
                if ($key === 'fname')
                    $criteria->addSearchCondition('member.first_name', $value);
                else if ($key === 'lname')
                    $criteria->addSearchCondition('member.last_name', $value);
                else if ($key === 'address')
                    $criteria->addSearchCondition('property.address_line_1', $value);
                else if ($key === 'city')
                    $criteria->addSearchCondition('property.city', $value);
                else if ($key === 'state')
                    $criteria->addSearchCondition('property.state', $value);
                else if ($key === 'zip')
                    $criteria->addSearchCondition('property.zip', $value);
                else if ($key === 'snapshot_status')
                {
                    $criteria->addCondition('t.response_status = :snapshot_status');
                    $criteria->params[':snapshot_status'] = $value;
                }
                else if ($key === 'response_status')
                {
                    $criteria->addCondition('property.response_status = :response_status');
                    $criteria->params[':response_status'] = $value;
                }
                else if ($key === 'response_enrolled_date')
                    $criteria->addSearchCondition('property.response_enrolled_date', $value);
                else if ($key === 'priority')
                    $criteria->addSearchCondition('t.priority', $value);
                else if ($key === 'distance')
                    $criteria->compare('t.distance', $value);
                else if ($key === 'threat')
                    $criteria->addSearchCondition('t.threat', $value);
                else if ($key === 'client_name')
                    $criteria->addSearchCondition('member.client', $value);
                else if ($key === 'access_issues')
                    $criteria->addSearchCondition('res_property_access.access_issues', $value);
                else if ($key === 'gate_code')
                    $criteria->addSearchCondition('res_property_access.gate_code', $value);
                else if ($key === 'suppression_resources')
                    $criteria->addSearchCondition('res_property_access.suppression_resources', $value);
                else if ($key === 'other_info')
                    $criteria->addSearchCondition('res_property_access.other_info', $value);
                else if ($key === 'wds_lob')
                    $criteria->addSearchCondition('property.wds_lob', $value);
            }
        }

        $count = ResTriggered::model()->count($criteria);

        $returnArray['data'] = $count;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

	/**
     * API Method: property/apiGetPolicyForEngine
     * Description: Gets policyholder information for the most recent notice for given clientIDs and fireID
     *
     * Post data parameters:
     * @param integer fireID
     * @param array clientIDs
     * @param integer limit - used for limiting number of results
     * @param integer offset - used for pagination
     * @param array sortArray - associative array of column => SORT_ASC/SORT_DESC for sorting
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": 378,
     *         "clientIDs": [3, 7, 1004],
     *          "limit": 20,
     *          "offset": 60,
     *          "sortArray": {
     *              "lname": 3
     *          }
     *          "compareArray": {
     *              "column1": "text",
     *              "column2": "text"
     *          }
     *     }
     * }
     */
    public function actionApiGetPolicyForEngine()
	{
        $data = NULL;
        $returnData = array();
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('fireID', 'clientIDs')))
            return;

        if (!is_array($data['clientIDs']))
            return WDSAPI::echoJsonError('Incorrect parameter', '"clientIDs" must be an array');

        // For each client figure out the most recent notice for the given fire
        $sql = 'SELECT MAX(notice_id) notice_id
        FROM res_notice
        WHERE client_id IN (' . implode(',', $data['clientIDs']) . ') AND fire_id = :fire_id
        GROUP BY client_id';

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':fire_id', $data['fireID'], PDO::PARAM_INT)
            ->queryAll();

        $noticeIDs = array_map(function($result) { return $result['notice_id']; }, $results);

        $sortDirection = function($sort)
        {
            return ($sort == SORT_ASC) ? 'ASC' : 'DESC';
        };

        // Create query to get policies to return
        $criteria = new CDbCriteria();
        $criteria->addInCondition('notice_id', $noticeIDs);
        $criteria->select = array('id','distance','response_status','priority','distance','threat');
        $criteria->with = array(
            'property' => array(
                'select' => array('pid','address_line_1','city','state','zip','coverage_a_amt','policy','producer','response_status','pre_risk_status','fireshield_status','response_enrolled_date','wds_lob')
            ),
            'property.member' => array(
                'select' => array('mid','member_num','first_name','last_name','salutation','home_phone','work_phone','cell_phone','email_1','client')
            ),
            'property.res_property_access' => array(
                'select' => array('access_issues','gate_code','suppression_resources','other_info')
            )
        );

        $criteria->limit = $data['limit'];
        $criteria->offset = $data['offset'];

        if (isset($data['sortArray']))
        {
            // Sorting
            $sortArray = array();

            foreach ($data['sortArray'] as $key => $sort)
            {
                if ($key === 'fname')
                    $sortArray[] = 'member.first_name ' . $sortDirection($sort);
                else if ($key === 'lname')
                    $sortArray[] = 'member.last_name ' . $sortDirection($sort);
                else if ($key === 'address')
                    $sortArray[] = 'property.address_line_1 ' . $sortDirection($sort);
                else if ($key === 'city')
                    $sortArray[] = 'property.city ' . $sortDirection($sort);
                else if ($key === 'state')
                    $sortArray[] = 'property.state ' . $sortDirection($sort);
                else if ($key === 'zip')
                    $sortArray[] = 'property.zip ' . $sortDirection($sort);
                else if ($key === 'response_status')
                    $sortArray[] = 'property.response_status ' . $sortDirection($sort);
                else if ($key === 'response_enrolled_date')
                    $sortArray[] = 'property.response_enrolled_date ' . $sortDirection($sort);
                else if ($key === 'priority')
                    $sortArray[] = 't.priority ' . $sortDirection($sort);
                else if ($key === 'distance')
                    $sortArray[] = 't.distance ' . $sortDirection($sort);
                else if ($key === 'threat')
                    $sortArray[] = 't.threat ' . $sortDirection($sort);
                else if ($key === 'client_name')
                    $sortArray[] = 'member.client ' . $sortDirection($sort);
                else if ($key === 'access_issues')
                    $sortArray[] = 'res_property_access.access_issues  ' . $sortDirection($sort);
                else if ($key === 'gate_code')
                    $sortArray[] = 'res_property_access.gate_code  ' . $sortDirection($sort);
                else if ($key === 'suppression_resources')
                    $sortArray[] = 'res_property_access.suppression_resources  ' . $sortDirection($sort);
                else if ($key === 'other_info')
                    $sortArray[] = 'res_property_access.other_info  ' . $sortDirection($sort);
                else if ($key === 'wds_lob')
                    $sortArray[] = 'property.wds_lob  ' . $sortDirection($sort);
            }

            $criteria->order = implode(',', $sortArray);
        }

        // Searching

        if (isset($data['compareArray']))
        {
            foreach ($data['compareArray'] as $key => $value)
            {
                if ($key === 'fname')
                    $criteria->addSearchCondition('member.first_name', $value);
                else if ($key === 'lname')
                    $criteria->addSearchCondition('member.last_name', $value);
                else if ($key === 'address')
                    $criteria->addSearchCondition('property.address_line_1', $value);
                else if ($key === 'city')
                    $criteria->addSearchCondition('property.city', $value);
                else if ($key === 'state')
                    $criteria->addSearchCondition('property.state', $value);
                else if ($key === 'zip')
                    $criteria->addSearchCondition('property.zip', $value);
                else if ($key === 'snapshot_status')
                {
                    $criteria->addCondition('t.response_status = :snapshot_status');
                    $criteria->params[':snapshot_status'] = $value;
                }
                else if ($key === 'response_status')
                {
                    $criteria->addCondition('property.response_status = :response_status');
                    $criteria->params[':response_status'] = $value;
                }
                else if ($key === 'response_enrolled_date')
                    $criteria->addSearchCondition('property.response_enrolled_date', $value);
                else if ($key === 'priority')
                    $criteria->addSearchCondition('t.priority', $value);
                else if ($key === 'distance')
                    $criteria->compare('t.distance', $value);
                else if ($key === 'threat')
                    $criteria->addSearchCondition('t.threat', $value);
                else if ($key === 'client_name')
                    $criteria->addSearchCondition('member.client', $value);
                else if ($key === 'access_issues')
                    $criteria->addSearchCondition('res_property_access.access_issues', $value);
                else if ($key === 'gate_code')
                    $criteria->addSearchCondition('res_property_access.gate_code', $value);
                else if ($key === 'suppression_resources')
                    $criteria->addSearchCondition('res_property_access.suppression_resources', $value);
                else if ($key === 'other_info')
                    $criteria->addSearchCondition('res_property_access.other_info', $value);
                else if ($key === 'wds_lob')
                    $criteria->addSearchCondition('property.wds_lob', $value);
            }
        }

        $triggeredData = ResTriggered::model()->findAll($criteria);

        foreach ($triggeredData as $triggered)
        {
            if (!isset($triggered->property))
                continue;

            $property = $triggered->property;
            $propertyAccess = ($property->res_property_access) ? $property->res_property_access : null;
            $lastPhVisit = $property->getLastPhVisit($data['fireID']);
            $resPhVisitModel = new ResPhVisit('search');
            $phVisits = $resPhVisitModel->findAllByAttributes(
                                array('property_pid' => $property->pid, 'fire_id' => $data['fireID']),
                                array(
                                    'condition'=>'review_status!=:status',
        	                        'params'=>array('status'=>'Removed'),
                                ));
            $phVisitsList = isset($phVisits) ? CHtml::listData($phVisits, 'id', 'date_action') : array();

            if (!isset($property->member))
                continue;

            $member = $property->member;
            $memberNumber = $member->member_num;

            $entry = array(
                'pid' => $property->pid,
                'fname' => $member->first_name,
                'lname' => $member->last_name,
                'salutation' => $member->salutation,
                'address' => $property->address_line_1,
                'city' => $property->city,
                'state' => $property->state,
                'zip' => $property->zip,
                'coverage' => $property->coverage_a_amt,
                'policy' => $property->policy,
                'member_number' => $memberNumber,
                'producer' => $property->producer,
                'response_status' => $property->response_status,
                'response_enrolled_date' => $property->response_enrolled_date,
                'action_date' => (isset($lastPhVisit->date_action)) ? date("Y-m-d", strtotime($lastPhVisit->date_action)) : '',
                'pre_risk_status' => ($property->pre_risk_status == 'enrolled') ? 'YES' : 'NO',
                'fireshield_status' => $property->fireshield_status,
                'response_enrolled_date' => $property->response_enrolled_date,
                'home_phone' => $member->home_phone,
                'work_phone' => $member->work_phone,
                'cell_phone' => $member->cell_phone,
                'email' => $member->email_1,
                'snapshot_status' => $triggered->response_status,
                'priority' => $triggered->priority,
                'distance' => round($triggered->distance, 2),
                'threat' => $triggered->threat,
                'client_id' => $triggered->client,
                'client_name' => $member->client,
                'access_issues' => ($propertyAccess) ? $propertyAccess->access_issues : '',
                'gate_code' => ($propertyAccess) ? $propertyAccess->gate_code : '',
                'suppression_resources' => ($propertyAccess) ? $propertyAccess->suppression_resources : '',
                'other_info' => ($propertyAccess) ? $propertyAccess->other_info : '',
                'wds_lob' => $property->wds_lob,
                'ph_visits' => $phVisitsList
            );

            $returnData[] = $entry;
        }

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
	}

    /**
     * API Method: property/apiGetRealtimePoliciesForFire
     * Description: Gets policyholder models for most recent perimeter on fire.
     *
     * Post data parameters:
     * @param integer pid
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 3
     *         "fireID": 124
     *     }
     * }
     */
    public function actionApiGetRealtimePoliciesForFire()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('clientID','fireID')))
            return;

        $results = GIS::getMonitoredPoliciesByFire($data['clientID'], $data['fireID']);

        $returnArray['data'] = $results;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

	/**
     * API Method: property/apiGetPolicyholderByProperty
     * Description: Gets policyholder information for a single property.
     *
     * Post data parameters:
     * @param integer pid
     *
     * Post data example:
     * {
     *     "data": {
     *         "pid": 12456
     *     }
     * }
     */
    public function actionApiGetPolicyholderByProperty()
	{
		$data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('pid')))
			return;

        $property = Property::model()->findByPk($data['pid']);

        if (!isset($property))
            return WDSAPI::echoJsonError('ERROR: Failed to find the policyholder!');

        $returnArray = array(
            'fname' => $property->member->first_name,
            'lname' => $property->member->last_name,
            'member_number' => $property->member->member_num,
            'address' => $property->address_line_1,
            'city' => $property->city,
            'state' => $property->state,
            'zip' => $property->zip,
            'policy' => $property->policy,
        );

        return WDSAPI::echoResultsAsJson($returnArray);
	}

	/**
     * API Method: property/apiGetByMemberNumber
     * Description: Gets properties for a given member number.
     *
     * Post data parameters:
     * @param integer member_num
     *
     * Post data example:
     * {
     *     "data": {
     *         "member_num": 12456
     *     }
     * }
     */
    public function actionApiGetByMemberNumber()
	{
		if (!WDSAPI::getInputDataArray($data, array('member_num')))
			return;

        $memberNum = $data['member_num'];
        $clientID = $data['client_id'];

        $criteria = new CDbCriteria();
        $criteria->join = 'INNER JOIN client ON client.name = t.client';
        $criteria->addCondition("member_num = '$memberNum'");
        $criteria->addCondition("client.id = '$clientID'");

        $member = Member::model()->find($criteria);

        if (!isset($member))
            return WDSAPI::echoJsonError("ERROR: a member was not found with number = $memberNum", "A member was not found for the given member number.");

        $returnData = array();
        $returnData['first_name'] = $member->first_name;
        $returnData['last_name'] = $member->last_name;
        $returnData['properties'] = array();

        $properties = Property::model()->findAllByAttributes(array('member_mid' => $member->mid));

        foreach ($properties as $property)
        {
            $returnData['properties'][] = array(
                "pid" => $property->pid,
                "address_line_1" => $property->address_line_1,
                "address_line_2" => $property->address_line_2,
                "city" => $property->city,
                "state" => $property->state,
                "zip" => $property->zip,
                "policy" => $property->policy,
                "response_status" => $property->response_status,
                "coverage" => $property->coverage_a_amt,
            );
        }

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
	}

	/**
     * API Method: property/apiGetPolicyholdersByFire
     * Description: Gets policyholder information for a given fire.
     *
     * Post data parameters:
     * @param integer fireID
     * @param integer clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": 12456,
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetPolicyholdersByFire()
	{
		$data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('fireID', 'clientID')))
			return;

        $resNotice = new ResNotice();

        $returnArray = $resNotice->getPolicyholdersByFire($data['fireID'], $data['clientID']);

        return WDSAPI::echoResultsAsJson($returnArray);
	}

	/**
     * API Method: property/apiGetPolicyStatusChanges
     * Description: API Method to get all the property policies that had their status
     * changed to one of the given statues in the given date range.  Returns json data
     * with an array of pids that were cancelled or expired policies in the date range.
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param array statuses
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2014-06-25",
     *         "endDate": "2014-06-27",
     *         "statuses": [
     *             "expired",
     *             "canceled"
     *         ]
     *     }
     * }
     */
    public function actionApiGetPolicyStatusChanges()
    {
        $data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'statuses')))
			return;

        $property = new Property();

        $returnArray = $property->getStatusChanges($data['startDate'].' 00:00:00', $data['endDate'].' 23:59:59', $data['statuses']);

        return WDSAPI::echoResultsAsJson($returnArray);
    }

	/**
     * API Method: property/apiLoadPolicyholders
     * Description: Counts up and returns the talley for the notice...enrolled threatened,
     * enrolled triggered, eligible threatened, eligible triggered.  Returns json array of
     * the pids for the selected group of policyholders.
     *
     * Post data example:
     * {
     *     "data": [
     *         {
     *             "pid": "123456",
     *             "threat": "1"
     *         },
     *         {
     *             "pid": "123456",
     *             "threat": "0"
     *         }
     *     ]
     * }
     */
    public function actionApiLoadPolicyholders()
	{
        //Need to fully optimize this...narrow down fields that are selected, maybe turn into batches for the database??
		$data = NULL;
        $return_array = array(
            'enrolled_triggered'=>0,
            'enrolled_threatened'=>0,
            'enrolled_triggered_exposure'=>0,
            'enrolled_threatened_exposure'=>0,
            //Eligible
            'eligible_triggered'=>0,
            'eligible_threatened'=>0,
            'eligible_triggered_exposure'=>0,
            'eligible_threatened_exposure'=>0
        );

		if (!WDSAPI::getInputDataArray($data))
			return;
        if(count($data)>15000)
        {
            WDSAPI::echoJsonError('ERROR: You have exceeded the limit for policyholder selections!');
            return NULL;
        }

        foreach($data as $entry)
        {
            $property = Property::model()->findByPk($entry["pid"]);
            if($property !== null)
            {
                $responseStatus = $property->response_status;
                $exposure = ($property->coverage_a_amt) ? intval($property->coverage_a_amt) : 0;
                if($responseStatus == 'enrolled')
                {
                    $return_array['enrolled_triggered']+=1;
                    $return_array['enrolled_triggered_exposure']+=$exposure;
                    if($entry['threat'])
                    {
                        $return_array['enrolled_threatened']+=1;
                        $return_array['enrolled_threatened_exposure']+=$exposure;
                    }
                }
                else
                {
                    $return_array['eligible_triggered']+=1;
                    $return_array['eligible_triggered_exposure']+=$exposure;
                    if($entry['threat'])
                    {
                        $return_array['eligible_threatened']+=1;
                        $return_array['eligible_threatened_exposure']+=$exposure;
                    }
                }
            }
        }

		return WDSAPI::echoResultsAsJson($return_array);

	}

    /**
     * API Method: property/apiCreateContactEntry
     * Description: Inserts new entries into the Contact Table from the Chubb enrollment form.
     *
     * @param array contacts
     *     - an index array composed of an associative array of contact information
     *     - note that the 'priority' field should be set to 'Manual' for a new entry from the Chubb Enrollment Form (up to the user to implement)
     *
     * Post data example:
     *  {
     *      "data": {
     *          "contacts": [
     *              {
     *                  "property_pid":123,
     *                  "type": "Home",
     *                  "priority": "Manual",
     *                  "name": "John Doe",
     *                  "relationship": "Self",
     *                  "detail": "123-456-7890",
     *                  "notes": null
     *              },
     *              {
     *                  "property_pid":123,
     *                  "type": "Mobile",
     *                  "priority": "Manual",
     *                  "name": "John Doe",
     *                  "relationship": "Self",
     *                  "detail": "123-456-7890",
     *                  "notes": null
     *              }
     *          ]
     *      }
     *  }
     */

    public function actionApiCreateContactEntry()
    {
        $data = null;

		if (!WDSAPI::getInputDataArray($data))
			return;

        if (!isset($data['contacts']))
            return WDSAPI::echoJsonError('ERROR: the attribute \'contacts\' is not set!');

        foreach ($data['contacts'] as $contact)
        {
            if (!isset($contact['property_pid']))
                return WDSAPI::echoJsonError('ERROR: the attribute \'property_pid\' in contact must be set!');

            try
            {
                $contactEntry = new Contact();
                foreach ($contact as $key => $value)
                {
                    $contactEntry->$key = $value;
                }
                if (!$contactEntry->save())
                {
                    $errorMessage = WDSAPI::getFormattedErrors($contactEntry);
                    return WDSAPI::echoJsonError("ERROR: Failed to save the new contacts entry! \n$errorMessage");
                }
            }
            catch (Exception $ex)
            {
                return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
            }
        }

        return WDSAPI::echoResultsAsJson(array('error'=>0, 'message'=>'Save was successful'));
    }

    /**
     * API Method: property/apiGetContactEntry
     * Description: Gets contact entry based off pid and an optional type parameter.
     *
     * @param integer property_pid
     * @param integer priority - (optional)
     * @param integer id - (optional) id of the specific contact entry
     *      Ex: 'Manual','Primary 1','Primary 2'
     *
     * Post data example:
     * {
     *      "data": {
     *          "property_pid": 136463,
     *          "priority": "Manual"
     *      }
     * }
     */

    public function actionApiGetContactEntry()
    {
        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('property_pid')))
            return;

        $criteria = new CDbCriteria();
        $criteria->order = 'priority ASC';

        if(isset($data['priority']))
        {
            $priority = $data['priority'];
            $pid = $data['property_pid'];
            $criteria->addCondition("priority='$priority'",'AND');
            $criteria->addCondition("property_pid='$pid'",'AND');
            $contacts = Contact::model()->findAll($criteria);
        }
        elseif(isset($data['id']))
        {
            $contacts[] = Contact::model()->findByPk($data['id']);
        }
        else
        {
            $pid = $data['property_pid'];
            $criteria->addCondition("property_pid='$pid'",'AND');
            $contacts = Contact::model()->findAll($criteria);
        }

        if (empty($contacts))
            return WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>null));

        $returnArray = array();

        try
        {
            foreach($contacts as $contact)
            {
                $returnArray[] = $contact->attributes;
            }
        }
        catch (Exception $ex)
        {
            return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
        }

        return WDSAPI::echoResultsAsJson(
            array('error'=>0,
                'message'=>'Get was successful',
                'data'=>$returnArray
            )
        );
    }

    /**
     * API Method: property/apiUpdateContactEntry
     * Description: Recieves a json array representing a Contact object and updates the database.
     *
     * @param array contacts
     *
     * Post data example:
     *  {
     *      "data": {
     *          "contacts": [
     *              {
     *                  "id": "234",
     *                  "property_pid":136461,
     *                  "type": "Home",
     *                  "priority": "Manual",
     *                  "name": "John Doe",
     *                  "relationship": "Self",
     *                  "detail": "123-456-7890",
     *                  "notes": null
     *              },
     *              {
     *                  "id": "235",
     *                  "property_pid":136463,
     *                  "type": "Mobile",
     *                  "priority": "Manual",
     *                  "name": "John Doe",
     *                  "relationship": "Self",
     *                  "detail": "123-456-7890",
     *                  "notes": null
     *              }
     *          ]
     *      }
     *  }
     *
     */

    public function actionApiUpdateContactEntry()
    {

        $data = null;

		if (!WDSAPI::getInputDataArray($data))
            return;

        if (!isset($data['contacts']))
            return WDSAPI::echoJsonError('ERROR: the attribute \'contacts\' is not set!');

        foreach ($data['contacts'] as $contact)
        {
            if (!isset($contact['id']))
                return WDSAPI::echoJsonError('ERROR: the attribute \'id\' is not set for your contact!');

            $contact_db = Contact::model()->findByPk($contact['id']);

            if (!$contact_db)
                return WDSAPI::echoResultsAsJson(array('error'=>1, 'message'=>'There are no records matching pid = ' . $contact['property_pid'] . ' and id = ' . $contact['id']));

            try
            {
                foreach($contact as $key=>$value)
                {
                    $contact_db->$key = $value;
                }

                if (!$contact_db->save())
                {
                    $errorMessage = WDSAPI::getFormattedErrors($contact_db);
                    return WDSAPI::echoJsonError("ERROR: Failed to update contacts entry! \n$errorMessage");
                }
            }
            catch (Exception $ex)
            {
                return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
            }
        }

        return WDSAPI::echoResultsAsJson(array('error'=>0, 'message'=>'Update was successful'));
    }

    /**
	 * API Method: property/apiCountNewEnrollments
     * Description: Counts the total number of new enrollments for a given client and date range
     *
     * Post data parameters:
     * @param integer clientID
     * @param string dateStart
     * @param string dateEnd
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1,
     *         "dateStart": "2014-06-11",
     *         "dateEnd": "2014-07-11"
     *     }
     * }
     */
	public function actionApiCountNewEnrollments()
	{

        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('clientID', 'dateStart', 'dateEnd')))
            return;

        //Get Parameters for search
        $clientID = $data['clientID'];
        $dateStart = $data['dateStart'];
        $dateEnd = date('Y-m-d', strtotime('+1 days', strtotime($data['dateEnd'])));

        $command = Yii::app()->db->createCommand("
            select count(pid) as count, state from properties
            where
	            client_id = :clientID
                and response_enrolled_date >= :dateStart
	            and response_enrolled_date < :dateEnd
            group by state;"
        );

        $command->bindParam(":clientID", $clientID, PDO::PARAM_INT);
        $command->bindParam(":dateStart", $dateStart, PDO::PARAM_STR);
        $command->bindParam(":dateEnd", $dateEnd, PDO::PARAM_STR);

        $count = $command->queryAll();

        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>$count));

    }

    /**
     * API Method: property/apiCountCurrentEnrollments
     * Description: Counts the total number of new enrollments for a given client and date range.
     *
     * Post data parameters:
     * @param integer clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1
     *     }
     * }
     */
	public function actionApiCountCurrentEnrollments()
	{
        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        //Get Parameters for search
        $clientName = (Client::model()->findByPk($data['clientID'])) ? Client::model()->findByPk($data['clientID'])->name : '';

        //Get count
        $count = Property::getResponseEnrollmentCount($clientName);

        //Return
        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>$count));
    }

    /**
     * API Method: property/apiGetAllPropertyModels Gets all models that are used for showing policyholder information. Intended to be broken apart on the client side
     * Input data JSON should be in the following format:
     * {"data": {"clientID": 1, "pid": 123}}
     */
    public function actionApiGetAllPropertyModels()
    {

        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('clientID', 'pid')))
            return;

        $property = Property::model()->findByAttributes(array('pid'=>$data['pid'], 'client_id'=>$data['clientID']));
        $fireID = (isset($data['fireID'])) ? $data['fireID'] : null;

        $returnArray = array(
            'property'=> ($property) ? $this->getPropertyData($property) : null,
            'property_access' => ($property) ? $this->getPropertyAcccessData($property): null,
            'call_attempts' => ($property) ? $this->getCallAttemptsData($property, $fireID): null,
            'contacts' => ($property) ? $this->getContactData($property): null
        );

        //Return
        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>$returnArray));

    }

    /**
     * API Method: property/apiGetCallAttempts Gets all call attempts. Intended to be broken apart on the client side
     * Input data JSON should be in the following format:
     * {"data": {"clientID": 1, "pid": 123}}
     */
    public function actionApiGetCallAttempts()
    {

        $data = null;

		if (!WDSAPI::getInputDataArray($data, array('fireID', 'pid')))
            return;

        $property = Property::model()->findByPk($data['pid']);
        $fireID = (isset($data['fireID'])) ? $data['fireID'] : null;

        $returnArray = array(
            'call_attempts' => ($property) ? $this->getCallAttemptsData($property, $fireID): null,
        );

        //Return
        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>$returnArray));

    }

    /**
     * Helper Function: Returns the basic information on the policy (name, address, etc)
     * param $property - a property object
     */
    public function getPropertyData($property)
    {
        return array(
            'pid' => $property->pid,
            'first_name' =>$property->member->first_name,
            'last_name' => $property->member->last_name,
            'address_line_1' => $property->address_line_1,
            'city' => $property->city,
            'state' => $property->state,
            'response_status' => $property->response_status,
            'policy' => $property->policy,
            'member_num' => $property->member->member_num,
            'contacts' => $this->getContactData($property),
            'lob' => $property->lob
        );
    }

    /**
     * Helper Function: Returns any property access data pertaining to the policy
     * param $property - a property object
     */
    public function getPropertyAcccessData($property)
    {
        if(isset($property->res_property_access))
            return $property->res_property_access->getAttributes();
        else
            return null;
    }

    /**
     * Helper Function: Returns all call attempts that were made through wdsfire for the given policy (and fire if included)
     * param $property - a property object
     * param $fireID - the id of the fire
     */
    public function getCallAttemptsData($property, $fireID)
    {
        $callAttempts = null;
        $returnArray = array();

        if ($fireID)
        {
            $sql = "select * from res_call_attempt where res_fire_id = :fire_id and property_id = :pid and platform = 2";
            $callAttempts = ResCallAttempt::model()->findAllBySql($sql, array(":pid"=>$property->pid, "fire_id" => $fireID));
        }
        else
        {
            $sql = "select * from res_call_attempt where res_fire_id is null and property_id = :pid and platform = 2";
            $callAttempts = ResCallAttempt::model()->findAllBySql($sql, array(":pid"=>$property->pid));
        }

        foreach($callAttempts as $attempt)
            $returnArray[] = array_merge($attempt->getAttributes(), array('caller_user_name' => $attempt->caller_user->name));

        return $returnArray;
    }

    /**
     * Helper Function: Not necesarry for now, but can hook this to the related contacts table when everyone's in there
     * param $property - a property object
     */
    public function getContactData($property)
    {
        if ($property->contacts)
        {
            $contacts = array();
            foreach($property->contacts as $entry)
                $contacts[$entry->type] = $entry->detail;
            return $contacts;
        }
        else
        {
            return array(
                'phone' => $property->member->home_phone,
                'work_phone' => $property->member->work_phone,
                'cell_phone' => $property->member->cell_phone
            );
        }
    }
}
