<?php

class ResNoticeController extends Controller
{
    const DASH_NOTICE_PAGE = 'notice-page';
    const DASH_NOTICE_FIRE_DETAILS = 'notice-fire-details';
    const DASH_NOTICE_POLICYHOLDER_ACTIONS = 'notice-policyholder-actions';
    const DASH_NOTICE_POLICYHOLDER_CALLS = 'notice-policyholder-calls';

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
                        'apiGetNotice',
                        'apiGetAllNoticeModels',
                        'apiGetRecentNoticesByClient',
                        'apiGetRecentNoticesByClients',
                        'apiGetRecentNoticesByDate',
                        'apiGetAllNoticesForAFire',
                        'apiGetRecentNoticesLibertyBoth',
                        'apiGetRecentNoticesChubbBoth',
                        'apiGetNoticeKMZ',
                        'apiGetEnrollmentNotice',
                        'apiGetMostRecentNoticeIdByClient'
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
                    'index',
                    'admin',
                    'create',
                    'update',
                    'delete',
                    'viewCallList',
                    'getFireDetails',
                    'getFirePerimeters',
                    'getFireThreats',
                    'getWdsStatusRadio',
                    'sendEmailNotification',
                    'viewNotice',
                    'viewFireHistory',
                    'landing',
                    'downloadKMZ',
                    'downloadNotices',
                    'fires',
                    'isClientFireDemob',
                    'resDetails',
                    'getThreatPerimeter'
                ),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetNotice',
                    'apiGetAllNoticeModels',
                    'apiGetRecentNoticesByClient',
                    'apiGetRecentNoticesByClients',
                    'apiGetRecentNoticesByDate',
                    'apiGetAllNoticesForAFire',
                    'apiGetRecentNoticesLibertyBoth',
                    'apiGetRecentNoticesChubbBoth',
                    'apiGetNoticeKMZ',
                    'apiGetEnrollmentNotice',
                    'apiGetMostRecentNoticeIdByClient'
                ),
                'users' => array('*')
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger',
            )
        );
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ResNotice the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=ResNotice::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------

    /**
     * Renders the index page (analytics)
     */
    public function actionIndex()
    {
        //Get date range...hardcoded for now
        $startDate = date('Y-01-01');
        $endDate = date('Y-m-d');
        if (isset($_POST['startdate']))
        {
            $startDate = $_POST['startdate'];
        }
        if (isset($_POST['enddate']))
        {
            $endDate = $_POST['enddate'];
        }
        //$ytdStart = '2016';//date('Y');
        $ytdStart = date('Y');
        $ytdEnd = date('Y', strtotime('+1 years', strtotime(date('Y'))));

        //Totals used throughout page
        $monthlyTotals = ResFireName::getHistoricalTally($startDate, $endDate, null);

        //All program fires for the timeframe
        $stateTally = ResFireName::getStateTally(ResFireName::getProgramFiresByDate($startDate, $endDate));

        //Totals per state
        $totalStates = ResFireName::getTotalFiresAllStates($stateTally);

        //Used throughout page
        $clients = Client::model()->findAllByAttributes(array('wds_fire'=>1));

        $this->render('index',
            array(
                'startDate'=>$startDate,
                'endDate'=>$endDate,
                'ytdStart'=>$ytdStart,
                'ytdEnd'=>$ytdEnd,
                'monthlyTotals'=>$monthlyTotals,
                'stateTally'=>$stateTally,
                'totalStates'=>$totalStates,
                'clients' => $clients
            )
        );
    }
    /*
    * Render Response details page
    */
    public function actionResDetails()
    {
        $clients = Client::model()->findAllBySql('select * from client where active = 1 order by name asc;');
        $startDate = date('Y-01-01');
        $endDate = date('Y-m-d');
        $nStartDate = '';
        $nEndDate = '';
        $clientIds = array();
        if (isset($_POST['startdate']))
        {
            $nStartDate = $_POST['startdate'];
        }
        else
        {
            $nStartDate = $startDate;
        }
        if (isset($_POST['enddate']))
        {
            $nEndDate = $_POST['enddate'];
        }
        else
        {
            $nEndDate = $endDate;
        }
        if (isset($_POST['Client']['id']))
        {
            $clientIds = $_POST['Client']['id'];
        }

        $fireData = ResNotice::getDispatchedFireList($clientIds, $nStartDate, $nEndDate, 'details');

        $this->render('resDetails', array(
                'clients' => $clients,
                'fireData' => $fireData,
                'startDate'=>$startDate,
                'endDate'=>$endDate
            ));
    }
    /**
     * Fire Notice Grid
     */
    public function actionAdmin()
    {
        $model = new ResNotice('search');
        $model->unsetAttributes();

        if(isset($_GET['ResNotice']))
            $model->attributes = $_GET['ResNotice'];

        $this->render('admin',array(
            'model' => $model
        ));
    }

    /**
     * Current fires - for now just dispatched
     */
    public function actionFires()
    {
        $fireData = ResNotice::getDispatchedFireList();

        $this->render('fires', array(
            'fireData' => $fireData
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate($client_id)
    {
        $model = new ResNotice;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'res-notice-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['ResNotice']))
        {
            $model->attributes=$_POST['ResNotice'];

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', "Notice Entry ".$model->fire->Name." Created Successfully!");
                $this->redirect(array('admin'));
            }
        }
        
        $this->render('create',array(
            'model'=>$model,
            'client_id'=>$client_id
        ));
    }

    /**
     * Deletes a model.
     * If delete is successful, the browser will be redirected to the 'admin' page.
     */
    public function actionDelete($id)
    {
        ResNotice::model()->findByPk($id)->delete();
        Yii::app()->user->setFlash('success', "Notice Deleted");
        return $this->redirect(array('admin'));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id, $client_id)
    {
        $model = $this->loadModel($id);

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'res-notice-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['ResNotice']))
        {
            $model->attributes=$_POST['ResNotice'];
            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Notice Entry ' . $model->fire->Name . ' Updated Successfully!');
                $this->redirect(array('admin'));
            }
        }

        $this->render('update',array(
            'model'=>$model,
            'fire_id'=>$model->fire_id,
            'client_id'=>$client_id
        ));
    }

    public function actionSendEmailNotification($id, $send = null)
    {
        $model = $this->loadModel($id);

        if ($send) //Send the email
        {
            $sendEmail = ResNotice::model()->sendEmailNotification($model);
            $this->render('sendEmail', array('sendEmail'=>$sendEmail, 'model'=>$model));
        }
        else //show confirmation page
        {
            $this->render('sendEmailConfirm', array('model'=>$model));
        }
    }

    /**
     * Redirect the to an appropriate Dashboard page using auto-login.
     * @param integer $clientid
     * @param integer $noticeid
     */
    public function actionViewNotice($clientid, $noticeid = null, $fireid = null, $status = null)
    {
        $url = Yii::app()->params['wdsfireBaseUrl'] . '/index.php/site/auto-login' .
            '?u=' . Yii::app()->user->getState('username') .
            '&t=' . User::getAutoLoginToken(Yii::app()->user->getState('user_id')) .
            '&cid=' . $clientid;

        if ($noticeid && $fireid)
        {
            $url .= '&id=' . $noticeid . '&fid=' . $fireid;
        }
        else if(isset($fireid))
        {
            $url .= '&fid=' . $fireid . '&status=' . $status;
        }
        $this->redirect($url);
    }

    /**
     * Shows the history for a fire and client
     * @param integer $clientID
     * @param integer $fireID
     */
    public function actionViewFireHistory($clientID, $fireID)
    {
        $models = ResNotice::model()->findAllByAttributes(array(
            'client_id' => $clientID,
            'fire_id' => $fireID
        ), array(
            'order' => 'notice_id DESC'
        ));

        $this->render('viewFireHistory', array('models'=>$models));
    }

    /**
     * Renders the response landing page
     */
    public function actionLanding()
    {
        $this->render('landing');
    }

    /**
     * Method downloads a KML
     * @param integer $id
     */
    public function actionDownloadKMZ($id)
    {
        $model = $this->loadModel($id);
        $kmz = new KMZNotice($model->perimeter_id, $model->fire_name, $model->client_id);
        $kmz->downloadKMZ();
    }

    /**
     * Selects and downloads in excel all notices for the given timeframe nad client (no grouping by fire)
     * @param string $dateStart All notices after or equal to this date
     * @param string $dateEnd   All notices up to this date
     * @param integer $clientID
     */
    public function actionDownloadNotices($dateStart, $dateEnd, $clientID)
    {
        $result = ResNotice::getAllNoticesByClient($dateStart, $dateEnd, $clientID);

        Yii::import('application.vendors.PHPExcel.*');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle("Notice Results for $dateStart - $dateEnd")
            ->setSubject("Notice Results for $dateStart - $dateEnd")
            ->setDescription('Notice download from WDSAdmin.');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Notices');

        // Setting header
        $header = array(
            'fire_id',
            'name',
            'city',
            'state',
            'date_created',
            'date_updated',
            'triggered_enrolled',
            'triggered_enrolled_exp',
            'triggered_eligible',
            'triggered_eligible_exp',
            'threatened_enrolled',
            'threatened_enrolled_exp',
            'threatened_eligible',
            'threatened_eligible_exp',
            'recommended_action',
            'status_type'
        );

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
        foreach ($result['data'] as $entry)
        {
            $write = array();
            foreach($header as $column)
                $write[$column] = $entry[$column];

            $activeSheet->fromArray($write, null, 'A' . $row);
            $row++;
        }

        // Setting auto column widths
        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell)
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);

        $client = Client::model()->findByPk($clientID)->name;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $client . ' Notice ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    //------------------------------------------------------------------- General Calls----------------------------------------------------------------

    // Method redirects the user to the call list with the correct fire information
    public function actionViewCallList($fireid, $clientid)
    {
        Yii::app()->user->setState('fireid', $fireid);
        Yii::app()->user->setState('clientid', $clientid);

        $this->redirect(array('resCallList/admin', 'wds_response_call_list_notice_link' => 1));
    }

    // Fire Information for Ajax Methods

    public function actionGetFireDetails()
    {
        $fire_id = $_POST['ResNotice']['fire_id'];
        $client_id = $_POST['ResNotice']['client_id'];

        $detailslist = $this->updateGetFireDetails($fire_id);

        foreach($detailslist as $value=>$name)
        {
            echo CHtml::tag('option', array('value'=>$value), CHtml::encode($name), true);
        }

        if (!$fire_id) { $fire_id = '0'; }

        echo CHtml::script("resNoticePopulatePerimeters($fire_id, '" . $this->createUrl('getFirePerimeters') . "')");
        echo CHtml::script("resNoticePopulateWdsStatusRadio($fire_id, $client_id, '" . $this->createUrl('getWdsStatusRadio') . "')");
    }
    /**
     * check to determine if perimeter is threatened
     * @param integer $perimeter_id
     */
    public function actionGetThreatPerimeter()
    {
        $perimeterID = Yii::app()->request->getPost('perimeterID');
        $sql = 'SELECT CASE WHEN threat_location_id IS NULL THEN 0 ELSE 1 END isAllowed FROM res_perimeters WHERE id = :perimeter_id';
        $result = Yii::app()->db->createCommand($sql)->queryScalar(array(
            ':perimeter_id' => $perimeterID
        ));
        if($result == 0)
        {
            echo 'Success';
        }
        else
        {
            echo 'Failed';
        }            
    }

    public function actionGetFirePerimeters()
    {
        $fireID = Yii::app()->request->getPost('fireID');

        $perimeterListData = $this->updateGetFirePerimeters($fireID);

        foreach ($perimeterListData as $value => $name)
        {
            echo CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
        }
    }

    public function actionGetWdsStatusRadio()
    {
        $fireID = Yii::app()->request->getPost('fireID');
        $clientID = Yii::app()->request->getPost('clientID');

        $model = new ResNotice;
        $model->fire_id = $fireID;
        $model->client_id = $clientID;

        echo CHtml::activeRadioButtonList($model, 'wds_status', $model->getWdsStatus(), array('separator' => "<br />"));
    }

    //Ajax action for checking if a demob notice has been made for a given fire+client
    public function actionIsClientFireDemob()
    {
        $fireID = Yii::app()->request->getPost('fireID');
        $clientID = Yii::app()->request->getPost('clientID');
        echo json_encode(array('isDemob'=>ResNotice::isClientFireDemob($fireID, $clientID)));
    }

    // Fire Information on Update

    public function updateGetFireDetails($fire_id)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'Obs_ID,Size,Containment,date_updated';
        $criteria->order = 'date_updated DESC';
        $criteria->addCondition('Fire_ID = '. $fire_id);

        return CHtml::listData(ResFireObs::model()->findAll($criteria), 'Obs_ID', function($data) {
            return "$data->Size acres, $data->Containment% contained - " . date_format(date_create($data->date_updated),'Y-m-d H:i');
        });
    }

    public function updateGetFirePerimeters($fire_id)
    {
        $perimeters = ResPerimeters::model()->findAll(array('condition'=>"fire_id = $fire_id",'order'=>'date_updated DESC'));
        return CHtml::listData($perimeters, 'id', function($data) {
            return  date_format(date_create($data->date_updated),'Y-m-d H:i');
        });
    }

    public function updateGetFireThreats($fire_id)
    {
        $perimeters = ResThreat::model()->findAll(array('condition'=>"fire_id = $fire_id",'order'=>'date_updated DESC'));
        return CHtml::listData($perimeters, 'id', function($data) {
            return date_format(date_create($data->date_updated),'Y-m-d H:i');
        });
    }
    /*
    * @param $data
    * set link for view notice
    * return $retval
    */
    public function getFireName($data)
    {
        $retval = '';
        $link = $this->createUrl("/resNotice/viewNotice", array("clientid"=>$data->client_id,"noticeid"=>$data->notice_id,"fireid"=>$data->fire_id));
        if(isset($data->fire->Name))
        {
            $retval = "<a target=_blank href = '$link' >" . CHtml::encode($data->fire->Name) . "</a>";        
        }
        return $retval;
    }
    //-------------------------------------------------------------------API Calls----------------------------------------------------------------

    /**
     * API Method: Gets a notice by ID.
     * Input data JSON should be in the following format:
     * {"data": {"clientID": 1, "noticeID": 123}}
     */
    public function actionApiGetNotice()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID', 'noticeID')))
            return;

        //Check to make sure client isn't pilot, if pilot hardcode clientID to be 999 (pilot)
        $wdsFire = Client::model()->findByPk($data['clientID'])->wds_fire;

        $clientID = ($wdsFire) ? $data['clientID'] : 999;
        $noticeID = $data['noticeID'];

        $notice = ResNotice::model()->findByAttributes(array('client_id' => $clientID, 'notice_id' => $noticeID));

        if (!isset($notice))
        {
            return WDSAPI::echoJsonError("ERROR: notice was not found for ID = $noticeID.");
        }

        $returnArray['error'] = 0; // success

        if ($wdsFire && $notice->client_id != $clientID)
        {
            return WDSAPI::echoJsonError("ERROR: notice was not found for ID = $noticeID.");
        }

        $noticeData = $notice->attributes;

        if($notice->fireObs && $notice->fire){
            //Fire Name
            $noticeData['fire_name'] = $notice->fire->Name;
            $noticeData['fire_city'] = $notice->fire->City;
            $noticeData['fire_state'] = $notice->fire->State;
            $noticeData['fire_contained'] = $notice->fire->Contained;
            $noticeData['fire_contained_date'] = $notice->fire->Contained_Date;

            //Fire Obs
            $noticeData['size'] = $notice->fireObs->Size;
            $noticeData['containment'] = $notice->fireObs->Containment;
        }

        $returnArray['data'] = $noticeData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resNotice/apiGetAllNoticeModels
     * Description: Gets dynamic datasets for the Dash notice view based on the
     * apiDataType paramater.
     *
     * Post data parameters:
     * @param integer $clientID
     * @param integer $noticeID
     * @param integer $realTime
     * @param integer $fireID
     * @param string $apiDataType - what dataset should be returned from this api?
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": "1",
     *         "noticeID": "4675",
     *         "realTime": "1",
     *         "fireID": "1675",
     *         "apiDataType": "notice-page"
     *     }
     * }
     */
    public function actionApiGetAllNoticeModels()
    {
        $data = NULL;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('clientID','noticeID','realTime','fireID','apiDataType')))
            return;

        // Check to make sure client isn't pilot, if pilot hardcode clientID to be 999 (pilot)
        $wdsFire = Client::model()->findByPk($data['clientID'])->wds_fire;

        $clientID = $wdsFire ? $data['clientID'] : 999;
        $noticeID = $data['noticeID'];
        $realTime = $data['realTime'];
        $fireID = $data['fireID'];
        $apiDataType = $data['apiDataType'];

        // Load notice first - if real time than we need to get the most recent notice
        $noticeData = $this->getNotice($clientID, $noticeID, $fireID, $realTime);

        // No notice was found - possibly a client was trying to access a notice for another client
        if (isset($noticeData['error']))
        {
            return WDSAPI::echoJsonError($noticeData['error']);
        }

        // If realtime than the notice ID supplied in call does not apply
        if ($realTime)
        {
            $noticeID = $noticeData['notice_id'];
        }

        if ($apiDataType === self::DASH_NOTICE_PAGE)
        {
            $returnData['model'] = $noticeData;
            $returnData['models'] = $this->getAllNoticesForFire($clientID, $noticeData['fire_id']);
        }
        else if ($apiDataType === self::DASH_NOTICE_FIRE_DETAILS)
        {
            $returnData[self::DASH_NOTICE_FIRE_DETAILS] = $this->getFireDetails($noticeData['obs_id'], $noticeData['fire_id'], $realTime);
        }
        else if ($apiDataType === self::DASH_NOTICE_POLICYHOLDER_ACTIONS)
        {
            $returnData[self::DASH_NOTICE_POLICYHOLDER_ACTIONS] = $this->getPolicyActionsByFire($noticeID, $noticeData['fire_id'], $clientID, $realTime);
        }
        else if ($apiDataType === self::DASH_NOTICE_POLICYHOLDER_CALLS)
        {
            $returnData[self::DASH_NOTICE_POLICYHOLDER_CALLS] = $this->getPolicyCalls($noticeID, $noticeData['fire_id'], $clientID);
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetRecentNoticesByClient
     * Description: Gets the most recent notices for a client grouped by fire.
     *
     * Post data parameters:
     * @param integer clientID - client to filter the results by
     * @param string startDate
     * @param string endDate
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1,
     *         "year": "2015",
     *         "updatedOnly": false
     *      }
     * }
     */
    public function actionApiGetRecentNoticesByClient()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        $wdsFire = Client::model()->findByPk($data['clientID'])->wds_fire;

        $clientID = ($wdsFire) ? $data['clientID'] : 999;
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $returnData = ResNotice::model()->getRecentNoticesByClient($clientID, $startDate, $endDate);

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetRecentNoticesByClients
     * Description: Gets the most recent notices for a client grouped by fire.
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     *
     * @param int[] clientIDs (optional)
     * @param string[] clientNames (optional)
     * One of the above vars MUST be included
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientIDs": [3,7],
     *         "startDate": "2016-01-01",
     *         "endDate": "2016-09-01"
     *      }
     * }
     */
    public function actionApiGetRecentNoticesByClients()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientNames')))
            return;

        $clientNames = isset($data['clientNames']) ? $data['clientNames'] : null;
        $clientIDs = isset($data['clientIDs']) ? $data['clientIDs'] : null;

        if ($clientNames !== null)
        {
            if (is_array($clientNames) === false)
            {
                return WDSAPI::echoJsonError('clientNames var must be an array', 'Bad input data');
            }

            $sql = "SELECT id FROM client WHERE name IN ('" . join("','", array_map('addslashes', $clientNames)) . "')";

            $clientIDs = Yii::app()->db->createCommand($sql)->queryColumn();

            if (count($clientIDs) === 0)
            {
                return WDSAPI::echoJsonError('no clientIDs were found with the client names recieved', 'Bad input data');
            }
        }
        elseif ($clientIDs !== null)
        {
            if (is_array($clientIDs) === false)
            {
                return WDSAPI::echoJsonError('clientIDs var must be an array', 'Bad input data');
            }
        }
        else
        {
            return WDSAPI::echoJsonError('either clientIDs or clientNames var must be populated as an array', 'Bad input data');
        }

        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $returnData = ResNotice::model()->getRecentNoticesByClients($clientIDs, $startDate, $endDate);

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetRecentNoticesByDate
     * Description: Gets the most recent notices for a client grouped by fire.
     *
     * Post data parameters:
     * @param int clientID - client to filter the results by.
     * @param string startDate - filters the results by date.
     * @param string endDate - filters the results by date.
     *
     * Post data example: {"data": {"clientID": 1, "limit": 6, "startDate": "2014-06-01", "endDate": "2014-06-08"}}
     */
    public function actionApiGetRecentNoticesByDate()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        $wdsFire = Client::model()->findByPk($data['clientID'])->wds_fire;

        $clientID = ($wdsFire) ? $data['clientID'] : 999;
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $returnData = ResNotice::model()->getRecentNoticesByClient($clientID, $startDate, $endDate);

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetAllNoticesForAFire
     * Description: Gets all notices for a given fire and client.
     *
     * Post data parameters:
     * @param int clientID - ID of the client to filter the results by.
     * @param int fireID - ID of the fire to filter the results by.
     * @param int limit - (optional) number of records to return. If not supplied, all notices will be returned.
     * @param int published - (optional) filters the results by published notices.
     *
     * Post data example: {"data": {"clientID": 1, "fireID": 123, "published": 1}}
     */
    public function actionApiGetAllNoticesForAFire()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID', 'fireID')))
            return;

        //First determine if client is pilot or not...if they are than hardcode to 999
        $wdsFire = Client::model()->findByPk($data['clientID'])->wds_fire;
        $clientID = ($wdsFire) ? $data['clientID'] : 999;

        $criteria = new CDbCriteria();

        $criteria->addCondition('client_id = ' . $data['clientID']);
        $criteria->addCondition('fire_id = ' . $data['fireID']);

        if (isset($data['published']))
            $criteria->addCondition('publish = ' . $data['published']);

        $criteria->order = 'notice_id DESC';

        if (isset($data['limit']))
            $criteria->limit = $data['limit'];

        $notices = ResNotice::model()->findAll($criteria);

        $returnData = array();

        foreach ($notices as $notice)
        {
            $notice_stats = $notice->attributes;

            if ($notice->fireObs && $notice->fire)
            {
                $noticeRelations = array();
                //Fire Name
                $noticeRelations['fire_name'] = $notice->fire->Name;
                $noticeRelations['fire_city'] = $notice->fire->City;
                $noticeRelations['fire_state'] = $notice->fire->State;
                $noticeRelations['fire_contained'] = $notice->fire->Contained;
                $noticeRelations['fire_contained_date'] = $notice->fire->Contained_Date;

                //Fire Obs
                $noticeRelations['size'] = $notice->fireObs->Size;
                $noticeRelations['containment'] = $notice->fireObs->Containment;

                $notice_stats += $noticeRelations;
            }
            $returnData[] = $notice_stats;
        }

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetRecentNoticesLibertyBoth
     * Description: Gets the most recent notice for liberty and safeco combined
     *
     * Post data parameters:
     * @param int fireID
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": 123
     *     }
     * }
     */
    public function actionApiGetRecentNoticesLibertyBoth()
    {
        $data = NULL;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('fireID')))
            return;

        $bothClients = array('3' => 'Liberty Mutual', '7' => 'Safeco');

        foreach ($bothClients as $key => $value)
        {
            $criteria = new CDbCriteria();

            $criteria->addCondition('client_id = ' . $key);
            $criteria->addCondition('fire_id = ' . $data['fireID']);
            $criteria->limit = 1;
            $criteria->addCondition('publish = 1');
            $criteria->order = 'notice_id DESC';

            $notice = ResNotice::model()->findAll($criteria);

            if (isset($notice[0]))
            {
                $returnData[$value] = $notice[0]->attributes;

                //if (is_null($notice[0]->geojson_file_id)) { $noticeData['geojson_file_id'] = ''; }

                //$returnData[$value]['perimeter_file_id'] = ($notice[0]->resPerimeters) ? $notice[0]->resPerimeters->file_id : '';
                //$returnData[$value]['threat_file_id'] = ($notice[0]->resThreat) ? $notice[0]->resThreat->file_id : '';
                //$returnData[$value]['zipcode_file_id'] = ($notice[0]->resPerimeters && $notice[0]->resPerimeters->resZipcode) ? $notice[0]->resPerimeters->resZipcode->file_id : '';
            }
        }

        //Combine the 2 clients (some fields don't add properly, but only care about exposures/triggers
        /*
        $returnData['Combined Totals'] = array_combine(
            array_keys($returnData['Liberty Mutual']),
            array_map(
                function($a, $b) { return $a + $b; },
                $returnData['Liberty Mutual'], $returnData['Safeco']
            )
        );
         * */

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetRecentNoticesChubbBoth
     * Description: Gets the most recent notice for chubb and safeco combined
     *
     * Post data parameters:
     * @param int fireID
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": 123
     *     }
     * }
     */
    public function actionApiGetRecentNoticesChubbBoth()
    {
        $data = NULL;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('fireID')))
            return;

        $criteria = new CDbCriteria;
        $criteria->addInCondition('name', array('Chubb','Ace'));
        $criteria->select = array('id','name');

        $clients = Client::model()->findAll($criteria);
        $bothClients = CHtml::listData($clients, 'id', 'name');

        foreach ($bothClients as $key => $value)
        {
            $criteria = new CDbCriteria();

            $criteria->addCondition('client_id = :client_id');
            $criteria->addCondition('fire_id = :fire_id');
            $criteria->limit = 1;
            $criteria->addCondition('publish = 1');
            $criteria->order = 'notice_id DESC';
            $criteria->params = array(
                ':client_id' => $key,
                ':fire_id' => $data['fireID']
            );

            $notice = ResNotice::model()->find($criteria);

            if (isset($notice))
            {
                $returnData[$value] = $notice->attributes;
            }
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resNotice/apiGetNoticeKMZ
     * Description: Downloads a KMZ document for a given notice
     *
     * Note: Returned data can be processed with the following PHP code:
     *     $content = pack('H*', $data);
     * $content can then be downloaded using either headers or framework methods
     *
     * Post data parameters:
     * @param integer noticeID
     *
     * Post data example:
     * {
     *     "data": {
     *         "noticeID": 8468
     *      }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "name": "Notice KMZ.kmz",
     *         "type": "application/vnd.google-earth.kmz",
     *         "data": "504b03041400000008006161564885478aade7250000098e010007..............."
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetNoticeKMZ()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data))
            return;

        $kmz = null;

        if (isset($data['noticeID']))
        {
            $model = ResNotice::model()->findByAttributes(array('notice_id' => $data['noticeID'], 'client_id' => $data['clientID']));
            $kmz = new KMZNotice($model->perimeter_id, $model->fire_name, $model->client_id);
        }
        else if (isset($data['monitorID']))
        {
            $model = ResMonitorLog::model()->findByPk($data['monitorID']);
            $kmz = new KMZNotice($model->Perimeter_ID, $model->resFireObs->resFireName->Name, $data['clientID']);
        }

        $filepath = $kmz->createKMZ();

        $fp = fopen($filepath, 'rb');
        $content = fread($fp, filesize($filepath));
        $content = unpack('H*hex', $content)['hex'];
        fclose($fp);

        $returnArray = array(
            'error' => 0,
            'data' => array()
        );

        if ($content)
        {
            $returnArray['data']['name'] = (isset($data['noticeID'])) ? 'Notice KMZ.kmz' : 'Fire KMZ.kmz';
            $returnArray['data']['type'] = 'application/vnd.google-earth.kmz';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
        }

        WDSAPI::echoResultsAsJson($returnArray);

        $kmz->removeKMZ();
    }

    /**
     * API Method: resNotice/apiGetEnrollmentNotice - Gets a notice in which the given policyholder is likely trying to enroll for.
     * Input data JSON should be in the following format:
     * {"data": {"clientID": 1, "pid": 123}}
     */
    public function actionApiGetEnrollmentNotice()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID', 'pid', 'date')))
            return;

        //Query params
        $pid = $data['pid'];
        $clientID = $data['clientID'];
        $date = $data['date'];

        $sql = "
        declare @pid int = :pid;
        declare @client_id int = :client_id;
        declare @date varchar(10) = :date;

        select top 1
            n.date_updated,
            n.notice_id,
            n.fire_id,
            l.id as call_list_id,
            s.status_type as wds_status,
            n.recommended_action,
            f.name,
            f.city,
            f.state
        from
            res_notice n
        inner join
            res_fire_name f on f.fire_id = n.fire_id
        inner join
            res_status s on s.id = n.wds_status
        inner join
            ( select id, property_id, res_fire_id from res_call_list where property_id = @pid) l on l.res_fire_id = n.fire_id
        where
            notice_id in ( select notice_id from res_triggered where property_pid = @pid)
            and (
                recommended_action = 'Enrollment/Response Recommended'
                or wds_status = 1
                or wds_status = 3
            )
            and n.date_updated >= @date
            and n.client_id = @client_id
        order by n.date_updated desc;";

        $fire = Yii::app()->db->createCommand($sql)
            ->bindParam(':date', $date, PDO::PARAM_STR)
            ->bindParam(':pid', $pid, PDO::PARAM_INT)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->queryRow();

        WDSAPI::echoResultsAsJson(array('error'=>0, 'data'=>$fire));
    }


    /**
     * API method: resNotice/apiGetMostRecentNoticeIdByClient
     * Description: Gets the most recent notices for a client
     *
     * Post data parameters:
     * @param int fireID
     * @param string clientName (optional)
     * @param int clientID (optional)
     *
     * Note: one of the above params must be given
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": 123456
     *         "clientName": "Chubb"
     *      }
     * }
     */
    public function actionApiGetMostRecentNoticeIdByClient()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('fireID')))
            return;

        $clientName = isset($data['clientName']) ? $data['clientName'] : null;
        $clientID = isset($data['clientID']) ? $data['clientID'] : null;

        if ($clientName === null && $clientID === null)
        {
            return WDSAPI::echoJsonError('ERROR: Param "clientName" or "clientID" must be passed in', 'Missing params to api');
        }

        if ($clientName)
        {
            $clientID = Yii::app()->db->createCommand('SELECT id FROM client WHERE name = :name')->queryScalar(array(
                ':name' => $data['clientName']
            ));

            if (!$clientID)
            {
                return WDSAPI::echoJsonError('ERROR: No client found', 'A client could not be found');
            }
        }

        $noticeID = Yii::app()->db->createCommand('SELECT TOP 1 notice_id FROM res_notice WHERE client_id = :client_id AND fire_id = :fire_id ORDER BY notice_id DESC')->queryScalar(array(
            ':client_id' => $clientID,
            ':fire_id' => $data['fireID']
        ));

        $returnArray['data'] = $noticeID === false ? null : $noticeID;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * Get's the data from the notice table
     * @param integer $clientID
     * @param integer $noticeID
     * @param integer $fireID
     * @param integer $realTime
     * @return array
     */
    public function getNotice($clientID, $noticeID, $fireID, $realTime)
    {
        // If realtime is set, than select most recent published notice for that fire instead of hard coded notice
        if ($realTime)
        {
            $criteria = new CDbCriteria;
            $criteria->condition = 't.client_id = :client_id AND t.fire_id = :fire_id AND t.publish = 1';
            $criteria->params[':client_id'] = $clientID;
            $criteria->params[':fire_id'] = $fireID;
            $criteria->limit = 1;
            $criteria->order = 't.notice_id DESC';
            $criteria->with = array('fireObs', 'fire' => array(
                'select' => 'Fire_ID,Name,City,State,Contained,Contained_Date'
            ));

            $notice = ResNotice::model()->find($criteria);

            if (!isset($notice) || empty($notice))
            {
                return array('error' => 'ERROR: notice was not found for ID = ' . $noticeID);
            }

            // Get date updated time - need to look at the notice, fire obs, policy details lists date updated times
            $sql = 'SELECT MAX(d.date_updated) FROM
            (
                SELECT date_updated FROM res_notice WHERE notice_id = :noticeID
                UNION
                SELECT date_updated FROM res_fire_obs WHERE fire_id = :fireID1
                UNION
                SELECT date_updated FROM res_ph_visit WHERE fire_id = :fireID2 and client_id = :clientID
            ) d';

            $dateUpdatedAll = Yii::app()->db->createCommand($sql)
                ->bindParam(':noticeID', $noticeID, PDO::PARAM_INT)
                ->bindParam(':fireID1', $fireID, PDO::PARAM_INT)
                ->bindParam(':fireID2', $fireID, PDO::PARAM_INT)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
                ->queryScalar();

        }
        // Selects the specific notice
        else
        {
            $notice = ResNotice::model()->findByPk($noticeID);
            $dateUpdatedAll = $notice->date_updated;
        }

        if (!isset($notice) || empty($notice))
        {
            return array('error' => 'ERROR: notice was not found for ID = ' . $noticeID);
        }

        $noticeData = $notice->attributes;

        // Dynamically figure out triggered/threatened totals
        if ($realTime)
        {
            $triggeredTotals = $this->getTriggeredTotals($notice->notice_id);

            $noticeData['triggered_enrolled_exp'] = $triggeredTotals['triggered_enrolled_exp'];
            $noticeData['triggered_enrolled'] = $triggeredTotals['triggered_enrolled'];
            $noticeData['threatened_enrolled_exp'] = $triggeredTotals['threatened_enrolled_exp'];
            $noticeData['threatened_enrolled'] = $triggeredTotals['threatened_enrolled'];
            $noticeData['triggered_eligible_exp'] = $triggeredTotals['triggered_eligible_exp'];
            $noticeData['triggered_eligible'] =  $triggeredTotals['triggered_eligible'];
            $noticeData['threatened_eligible_exp'] = $triggeredTotals['threatened_eligible_exp'];
            $noticeData['threatened_eligible'] = $triggeredTotals['threatened_eligible'];
        }

        if ($notice->fireObs && $notice->fire)
        {
            //Fire Name
            $noticeData['fire_name'] = $notice->fire->Name;
            $noticeData['fire_city'] = $notice->fire->City;
            $noticeData['fire_state'] = $notice->fire->State;
            $noticeData['fire_contained'] = $notice->fire->Contained;
            $noticeData['fire_contained_date'] = $notice->fire->Contained_Date;

            //Fire Obs
            $noticeData['size'] = $notice->fireObs->Size;
            $noticeData['containment'] = $notice->fireObs->Containment;
        }

        $noticeData['date_updated_all'] = $dateUpdatedAll;

        if(isset($notice->resEvacZones) && count($notice->resEvacZones) > 0)
        {
            $noticeData['has_evac_zones'] = true;
        }

        return $noticeData;
    }

    public function getFireDetails($obsID = null, $fireID = null, $realTime = null)
    {
        $fireDetails = null;

        //Get most recent set of fire details
        if ($realTime)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition('Fire_ID = :Fire_ID');
            $criteria->params = array(':Fire_ID' => $fireID);
            $criteria->order = 'Obs_ID DESc';
            $criteria->limit = 1;

            $fireDetails = ResFireObs::model()->find($criteria);
        }
        //Get the one bound to the notice
        else
        {
            $fireDetails = ResFireObs::model()->findByPk($obsID);
        }

        return $fireDetails->attributes;
    }

    /**
     * Get's the policyholder actions for a given fire/client
     * @param int $noticeID
     * @param int $fireID
     * @param int $clientID
     * @param boolean $realTime
     * @return array
     */
    public function getPolicyActionsByFire($noticeID, $fireID, $clientID, $realTime = 0)
    {
        return ResPhVisit::getPolicyActionsByFire($noticeID, $fireID, $clientID, $realTime);
    }

    public function getPolicyCalls($noticeID, $fireID, $clientID)
    {
        $criteria = new CDbCriteria;
        $criteria->select = 'date_created, notice_id';
        $criteria->addCondition('fire_id = :fire_id');
        $criteria->addCondition('client_id = :client_id');
        $criteria->params = array(':fire_id' => $fireID, ':client_id' => $clientID);
        $criteria->order = 'notice_id DESC';

        $notices = ResNotice::model()->findAll($criteria);

        $allcalls = false;

        // Determine if most recent notice and all calls can be fetched or if calls need to be restricted by notice date.
        // Older notices will show all calls up to the next notice.

        for ($i = 0; $i < count($notices); $i++)
        {
            if ($notices[$i]->notice_id == $noticeID)
            {
                if ($i === 0)
                {
                    $date = $notices[$i]->date_created;
                    $allcalls = true;
                }
                else
                {
                    $date = $notices[$i - 1]->date_created;
                }
            }
        }

        // Select all calls ... by date if necessary
        $callcriteria = new CDbCriteria;
        $callcriteria->addCondition('res_fire_id = :fire_id');
        $callcriteria->addCondition('client_id = :client_id');
        $callcriteria->params = array(':fire_id' => $fireID, ':client_id' => $clientID);
        if (!$allcalls)
        {
            $callcriteria->addCondition('date_called < :date');
            $callcriteria->params[':date'] = $date;
        }
        $callcriteria->order = 'date_called desc';

        $models = ResCallAttempt::model()->findAll($callcriteria);

        $returnData = array();

        foreach($models as $model)
        {
            if ($model->property)
            {
                if ($model->publish == 1)
                {
                    if (!isset($returnData[$model->property_id]))
                        $returnData[$model->property_id] = array();

                    if (!isset($returnData[$model->property_id]) && !isset($returnData[$model->property_id]['calls']))
                        $returnData[$model->property_id]['calls'] = array();

                    // Snapshot Status logic - first see if they exist in the triggered table, otherwise try to figure out when they enrolled
                    $triggeredEntry = ResTriggered::model()->findByAttributes(array('notice_id'=>$noticeID, 'property_pid'=>$model->property_id));
                    if($triggeredEntry)
                        $snapshotStatus = $triggeredEntry->response_status;
                    elseif ($model->property->response_enrolled_date && (date_create($model->property->response_enrolled_date) < date_create($date)))
                        $snapshotStatus = 'enrolled';
                    else
                        $snapshotStatus = $model->property->response_status;

                    $memberNumber = '';
                    if ($model->property->member) {
                        $memberNumber = $model->property->member->member_num;
                    }

                    $returnData[$model->property_id]['property_id'] = $model->property_id;
                    $returnData[$model->property_id]['member_name'] = $model->property->member ? $model->property->member->last_name . ', ' . $model->property->member->first_name : '';
                    $returnData[$model->property_id]['address'] = $model->property->address_line_1;
                    $returnData[$model->property_id]['city'] = $model->property->city;
                    $returnData[$model->property_id]['state'] = $model->property->state;
                    $returnData[$model->property_id]['response_status'] = $model->property->response_status;
                    $returnData[$model->property_id]['snapshot_status'] = $snapshotStatus;
                    $returnData[$model->property_id]['member_num'] = $memberNumber;
                    $returnData[$model->property_id]['policy'] = $model->property->policy;
                    $returnData[$model->property_id]['producer_name'] = ($clientID == '2' || $clientID == '1005') ? trim(explode('(', $model->property->producer)[0]) : '';
                    $returnData[$model->property_id]['agency_code'] = $model->property->agency_code;
                    $returnData[$model->property_id]['signature'] = $model->property->member ? $model->property->member->signed_ola : '';

                    $returnData[$model->property_id]['calls'][] = array(
                        'attempt_number' => $model->attempt_number,
                        'date_called' => $model->date_called,
                        'point_of_contact' => $model->point_of_contact,
                        'point_of_contact_description' => $model->point_of_contact_description,
                        'in_residence' => $model->in_residence,
                        'evacuated' => $model->evacuated,
                        'dashboard_comments' => $model->dashboard_comments
                    );
                }
            }
        }

        return $returnData;
    }

    /**
     * Return all the notifications (timestamps) for the given fire and client
     * @param integer $clientID
     * @param integer $fireID
     * @return array
     */
    public function getAllNoticesForFire($clientID, $fireID)
    {
        // Find the notices
        $criteria = new CDbCriteria();
        $criteria->condition = 't.client_id = :client_id AND t.fire_id = :fire_id';
        $criteria->order = 't.notice_id DESC';
        $criteria->params[':client_id'] = $clientID;
        $criteria->params[':fire_id'] = $fireID;
        $criteria->with = array(
            'fire' => array(
                'select' => 'Fire_ID,Name,City,State,Contained,Contained_Date'
            ), 'fireObs' => array(
                'select' => 'Obs_ID,Size,Containment'
            )
        );

        $notices = ResNotice::model()->findAll($criteria);

        // Determind if this method should return unpublished notices
        // This WILL be the case, if no notices have been published yet

        $shouldIncludedUnpublished = false;

        $publishedStatuses = array_map(function($notice) { return $notice->publish; }, $notices);

        if (in_array('1', $publishedStatuses) === false)
        {
            $shouldIncludedUnpublished = true;
        }

        //Stack to return
        $returnData = array();

        //Go through each and pull out small summary - don't need entire notice contents
        foreach ($notices as $notice)
        {
            $notice_stats = $notice->attributes;

            if ($notice->fireObs && $notice->fire)
            {
                $noticeRelations = array();
                //Fire Name
                $noticeRelations['fire_name'] = $notice->fire->Name;
                $noticeRelations['fire_city'] = $notice->fire->City;
                $noticeRelations['fire_state'] = $notice->fire->State;
                $noticeRelations['fire_contained'] = $notice->fire->Contained;
                $noticeRelations['fire_contained_date'] = $notice->fire->Contained_Date;

                //Fire Obs
                $noticeRelations['size'] = $notice->fireObs->Size;
                $noticeRelations['containment'] = $notice->fireObs->Containment;

                $notice_stats += $noticeRelations;
            }

            // Include un-published notices
            if ($shouldIncludedUnpublished)
            {
                $returnData[] = $notice_stats;
            }
            // Only include published notices
            elseif ($notice->publish === '1')
            {
                $returnData[] = $notice_stats;
            }
        }

        return $returnData;
    }

    /**
     * Tally up the totals for the 'real time' counts - mainly the enrolled/eligible counts
     * @param integer $noticeID
     * @return array
     */
    public function getTriggeredTotals($noticeID)
    {
        $returnArray = array(
            'triggered_enrolled_exp' => 0,
            'triggered_enrolled' => 0,
            'threatened_enrolled_exp'=> 0,
            'threatened_enrolled' => 0,
            'triggered_eligible_exp' => 0,
            'triggered_eligible' => 0,
            'threatened_eligible_exp'=> 0,
            'threatened_eligible' => 0
        );

        // Get all triggered entries
        $models = ResTriggered::model()
            ->with(array('property' => array(
                'select' => 'pid,response_status,coverage_a_amt'
            )))
            ->findAll('t.notice_id = :notice_id', array(
                ':notice_id' => $noticeID
            ));

        // Now run through them and count up the totals
        foreach ($models as $model)
        {
            if ($model->threat)
            {
                if($model->property->response_status == 'enrolled')
                {
                    $returnArray['threatened_enrolled'] +=1;
                    $returnArray['threatened_enrolled_exp'] += $model->property->coverage_a_amt;
                    $returnArray['triggered_enrolled'] +=1;
                    $returnArray['triggered_enrolled_exp'] += $model->property->coverage_a_amt;
                }
                else
                {
                    $returnArray['threatened_eligible'] +=1;
                    $returnArray['threatened_eligible_exp'] += $model->property->coverage_a_amt;
                    $returnArray['triggered_eligible'] +=1;
                    $returnArray['triggered_eligible_exp'] += $model->property->coverage_a_amt;
                }
            }
            else
            {
                if ($model->property->response_status == 'enrolled')
                {
                    $returnArray['triggered_enrolled'] +=1;
                    $returnArray['triggered_enrolled_exp'] += $model->property->coverage_a_amt;
                }
                else
                {
                    $returnArray['triggered_eligible'] +=1;
                    $returnArray['triggered_eligible_exp'] += $model->property->coverage_a_amt;
                }
            }
        }

        return $returnArray;
    }
}