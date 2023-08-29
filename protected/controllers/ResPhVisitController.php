<?php

class ResPhVisitController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            //'postOnly + deletePhoto',
             array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetAllActionsByFire',
                        'apiGetPolicyActionsAllFires',
                        'apiGetEnrollmentRecommendedPolicyholders',
                        'apiGetStats',
                        'apiCountResPhVisit',
                        'apiGetResPhVisit',
                        'apiResPhVisitUpdate',
                        'apiResPhVisitCreate',
                        'apiGetActionType',
                        'apiGetResPhVisits',
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiResPhVisitUpdate',
                        'apiResPhVisitCreate',
                        'apiGetResPhVisit',
                        'apiGetResPhVisits',
                        'apiGetActionType',
                        'apiCountResPhVisit',
                        'apiGetBaseInfo',
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
                    'admin',
                    'create',
                    'update',
                    'deletePhoto',
                    'policyAdd',
                    'policyAction',
                    'viewPolicyAction',
                    'viewPolicyActionData',
                    'getDispatchedFires',
                    'getPolicyGeoJson',
                    'getPolicyGeoJson',
                    'downloadvisitlist'
                ),
                'users' => array('@')
            ),
            array('allow',
                'actions' => array(
                    'apiGetAllActionsByFire',
                    'apiGetPolicyActionsAllFires',
                    'apiGetEnrollmentRecommendedPolicyholders',
                    'apiGetResPhVisit',
                    'apiGetResPhVisits',
                    'apiResPhVisitUpdate',
                    'apiResPhVisitCreate',
                    'apiGetBaseInfo',
                    'apiGetStats',
                ),
                'users' => array('*')
            ),
            array('deny',
                'users' => array('*')
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
     * Policy Action Grid
     */
    public function actionAdmin()
    {
        $model = new ResPhVisit('search_actions');
        $model->unsetAttributes();

        // Reset server variables

        if (isset($_GET['reset']))
        {
            if (isset($_SESSION['ph_visit_search_filters']))  { unset($_SESSION['ph_visit_search_filters']);  }
            if (isset($_SESSION['ph_visit_columns_to_show'])) { unset($_SESSION['ph_visit_columns_to_show']); }
            if (isset($_SESSION['ph_visit_search_sort']))     { unset($_SESSION['ph_visit_search_sort']);     }
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        // Getting vars from url

        $fireID = $_GET['fid'];
        $clientID = $_GET['cid'];

        if (isset($_GET['fn'], $_GET['cn']))
        {
            $fireName = urldecode($_GET['fn']);
            $clientName = urldecode($_GET['cn']);
        }
        else
        {
            $fireName = ResFireName::model()->findByPk($fireID, array('select' => 'Name'))->Name;
            $clientName = Client::model()->findByPk($clientID, array('select' => 'name'))->name;
        }

        // Filtering

        if (isset($_GET['ResPhVisit']))
        {
            $model->attributes = $_GET['ResPhVisit'];
            $_SESSION['ph_visit_search_filters'] = $_GET['ResPhVisit'];
        }
        elseif (isset($_SESSION['ph_visit_search_filters']))
        {
            $model->attributes = $_SESSION['ph_visit_search_filters'];
        }

        // Grid columns to show

        $columnsToShow = array(
            'memberFirstName',
            'memberLastName',
            'status',
            'date_action',
            'date_created',
            'date_updated',
            'userName',
            'lastUpdateUserName',
            'approvalUserName',
            'review_status',
            'comments',
            'publish_comments',
            'propertyAddress',
            'propertyPolicy',
            'response_status'
        );

        if (isset($_GET['columnsToShow']))
        {
            $_SESSION['ph_visit_columns_to_show'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        elseif (isset($_SESSION['ph_visit_columns_to_show']))
        {
            $columnsToShow = $_SESSION['ph_visit_columns_to_show'];
        }

        // Sorting

        $sort = 'id';

        if (isset($_GET['ResPhVisit_sort']))
        {
            $_SESSION['ph_visit_search_sort'] = $_GET['ResPhVisit_sort'];
            $sort = $_GET['ResPhVisit_sort'];
        }
        elseif (isset($_SESSION['ph_visit_search_sort']))
        {
            $sort = $_SESSION['ph_visit_search_sort'];
        }
        //Add reviewstatus removed for manager permission or higher
        if (in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types))
        {
            array_push($model::$reviewstatusType,"Removed");
        }
        $this->render('admin',array(
            'model' => $model,
            'fireID' => $fireID,
            'clientID' => $clientID,
            'fireName' => $fireName,
            'clientName' => $clientName,
            'columnsToShow' => $columnsToShow,
            'sort' => $sort
        ));
    }

    /**
     * Create a policy visit model instance
     * @return void
     */
    public function actionCreate()
    {
        $model = new ResPhVisit;

        $fireID = $_GET['fid'];
        $clientID = $_GET['cid'];
        $fireName = urldecode($_GET['fn']);
        $clientName = urldecode($_GET['cn']);
        $pid = $_GET['pid'];

        if (isset($_POST['ResPhVisit']))
        {
            $model->attributes = $_POST['ResPhVisit'];

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Action entry created successfully!');
                return $this->redirect(array('update','id' => $model->id, 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName));
            }
        }

        $this->render('create',array(
            'model'=>$model,
            'pid' => $pid,
            'fireID' => $fireID,
            'clientID' => $clientID,
            'fireName' => $fireName,
            'clientName' => $clientName
        ));
    }

    /**
     * Deletes a ph visit photo model instance
     * @param integer $id
     */
    public function actionDeletePhoto($id, $photoID)
    {
        $returnArray = array();
        $photomodel = ResPhPhotos::model()->findByPk($photoID);

        if ($photomodel)
        {
            // Remove corresponding image in file table
            $filemodel = File::model()->findByPk($photomodel->file_id);
            if ($filemodel)
                $filemodel->delete();

            $returnArray['response'] = 'true';
        }

        if ($returnArray['response'] != 'true')
            $returnArray['errorMessage'] = 'Phtoto Delete Failed';

        echo CJSON::encode($returnArray);
    }

    /**
     * Updates the policy visit form
     * @param integer $id
     * @param integer|null $photoID
     */
    public function actionUpdate($id)
    {
        $fireID = $_GET['fid'];
        $clientID = $_GET['cid'];
        $fireName = urldecode($_GET['fn']);
        $clientName = urldecode($_GET['cn']);
        $updateMade = false;
        $error = false;

        $model = ResPhVisit::model()->findByPk($id);

        // Visit Form Submitted
        if (isset($_POST['ResPhVisit']))
        {
            $updateMade = true;
            // Visit Actions filled out
            if (isset($_POST['ResPhActions']))
            {
                //go through each availible action type
                foreach(ResPhActionType::model()->findAll() as $actionType)
                {
                    $existingAction = ResPhAction::model()->findByAttributes(array('visit_id'=>$id, 'action_type_id'=>$actionType->id));
                    //if a related action does not already exist and it was selected, then create it
                    if(in_array($actionType->id, $_POST['ResPhActions']) && !isset($existingAction))
                    {
                        $resPhAction = new ResPhAction;
                        $resPhAction->visit_id = $id;
                        $resPhAction->action_type_id = $actionType->id;

                        if(!$resPhAction->save())
                        {
                            $error = true;
                            Yii::app()->user->setFlash('error', 'Error Saving one of the Policyholder Actions associated with the visit');
                        }
                    }
                    //else if it wasn't in the selected array but it exists from being previously selected, then delete it
                    else if(isset($existingAction) && !in_array($actionType->id, $_POST['ResPhActions']))
                    {
                        if(!$existingAction->delete())
                        {
                            $error = true;
                            Yii::app()->user->setFlash('error', 'Error deleting one of the Policyholder Actions associated with the visit');
                        }
                    }
                    //else wasn't selected and didn't exist from before OR was selected and continued to be selected
                }
            }
            else
            {
            //if nothing is selected but it exists from being previously selected, then delete it
                foreach(ResPhActionType::model()->findAll() as $actionType)
                {
                    $existingAction = ResPhAction::model()->findByAttributes(array('visit_id'=>$id, 'action_type_id'=>$actionType->id));
                    if(isset($existingAction) && !in_array($actionType->id, array($existingAction)))
                    {
                        if(!$existingAction->delete())
                        {
                            $error = true;
                            Yii::app()->user->setFlash('error', 'Error deleting one of the Policyholder Actions associated with the visit');
                        }
                   }
                }
             }
            //Qty Updates
            if(isset($_POST['ResPhActionTypeQty']))
            {
                foreach($_POST['ResPhActionTypeQty'] as $typeid => $qty)
                {
                    if($qty != '')
                    {
                        $existingAction = ResPhAction::model()->findByAttributes(array('visit_id'=>$id, 'action_type_id'=>$typeid));
                        if(!$existingAction)
                        {
                            $resPhAction = new ResPhAction;
                            $resPhAction->visit_id = $id;
                            $resPhAction->action_type_id = $typeid;
                            $resPhAction->qty = $qty;
                            $resPhAction->save();
                        }
                        else
                        {
                            ResPhAction::model()->updateAll(array(
                            'qty' => $qty
                            ), 'visit_id = :visit_id AND action_type_id = :action_type_id', array(
                                ':visit_id' => $id,
                                ':action_type_id' => $typeid
                            ));
                        }
                    }
                }
            }
            if(isset($_POST['ResPhActionTypeAllianceQty']))
            {
                foreach($_POST['ResPhActionTypeAllianceQty'] as $typeid => $qty)
                {
                    if($qty != '')
                    {
                        $existingAction = ResPhAction::model()->findByAttributes(array('visit_id'=>$id, 'action_type_id'=>$typeid));
                        if(!$existingAction)
                        {
                            $resPhAction = new ResPhAction;
                            $resPhAction->visit_id = $id;
                            $resPhAction->action_type_id = $typeid;
                            $resPhAction->alliance_qty = $qty;
                            $resPhAction->save();
                        }
                        else
                        {
                            ResPhAction::model()->updateAll(array(
                            'alliance_qty' => $qty
                            ), 'visit_id = :visit_id AND action_type_id = :action_type_id', array(
                                ':visit_id' => $id,
                                ':action_type_id' => $typeid
                            ));
                        }
                    }
                }
            }
            $model->attributes = $_POST['ResPhVisit'];

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Action Entry Updated Successfully!');
            }

        }

        //existing Photos updates
        if(isset($_POST['ExistingResPhPhotos']))
        {
            $updateMade = true;
            foreach($_POST['ExistingResPhPhotos'] as $id => $attributes)
            {
                $existingPhoto = ResPhPhotos::model()->findByPk($id);
                if(!isset($attributes['publish']))
                {
                    $attributes['publish'] = 0;
                }

                $existingPhoto->attributes = $attributes;

                // Image name update
                $file = File::model()->findByPk($existingPhoto->file_id);
                $file->name = $attributes['name'];
                if(!$file->SaveAttributes(array('name')))
                {
                    echo $file->getErrors;
                }

                if(!$existingPhoto->save())
                {
                    $error = true;
                    Yii::app()->user->setFlash('notice', 'Error Updating Existing Photo Details!');
                }
            }
        }

        //New Photo
        if(isset($_FILES['create_file_id']))
        {
            for($i = 0; $i < count($_FILES['create_file_id']['name']); $i++)
            {
                if(CUploadedFile::getInstanceByName("create_file_id[$i]"))
                {
                    $newPhoto = new ResPhPhotos;
                    $newPhoto->visit_id = $model->id;
                    $newPhoto->imageNo = $i;
                    //Saved correctly
                    if ($newPhoto->save())
                    {
                        Yii::app()->user->setFlash('success', 'New photo was successfully uploaded!');
                    }
                    //No Save
                    else
                    {
                        Yii::app()->user->setFlash('notice', 'New photo was not uploaded!');
                    }
                }
            }
        }

        if($updateMade)
        {
            //results output
            if($error)
            {
                Yii::app()->user->setFlash('notice', "Error Updating Policyholder Visit!");
            }
            else
            {
                Yii::app()->user->setFlash('success', "Successfully Updated Policyholder Visit");
            }
        }

        $photos = ResPhPhotos::model()->findAllBySql("SELECT * FROM res_ph_photos WHERE visit_id = :visit_id ORDER BY [order] ASC", array(':visit_id' => $model->id));
        $showStatus = false;//initialize $showStatus
        // make $showStatus = true for manager permission or higher
        if (in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types))
        {
            $showStatus = true;
        }
        $this->render('update',array(
            'model'=>$model,
            'photos' => $photos,
            'fireID' => $fireID,
            'clientID' => $clientID,
            'fireName' => $fireName,
            'clientName' => $clientName,
            'showStatus' => $showStatus
        ));
    }

    public function actionPolicyAdd()
    {
        $filtersForm = new FiltersForm;

        if (isset($_GET['FiltersForm']))
        {
            $filtersForm->filters = $_GET['FiltersForm'];
        }

        $fireID = $_GET['fid'];
        $clientID = $_GET['cid'];
        $fireName = urldecode($_GET['fn']);
        $clientName = urldecode($_GET['cn']);

        $dataArray = ResNotice::model()->getPolicyholdersByFire($fireID, $clientID);

        $filteredData = $filtersForm->filter($dataArray);

        $dataProvider = new CArrayDataProvider($filteredData, array(
            'id' => 'triggered-data-provider',
            'keyField' => 'pid',
            'keys' => array('*'),
            'sort' => array(
                'defaultOrder' => array('distance' => CSort::SORT_ASC),
                'attributes' => array('pid','threat','fname','lname','address','city','state','zip','coverage','response_status','distance')
            ),
            'pagination' => array(
                'pageSize' => 20
            )
        ));

        $this->render('policyAdd', array(
            'model' => $filtersForm,
            'dataProvider' => $dataProvider,
            'fireID' => $fireID,
            'clientID' => $clientID,
            'fireName' => $fireName,
            'clientName' => $clientName
        ));
    }

    public function actionPolicyAction()
    {
        $model = new ResPhVisit('search_edit');
        $model->unsetAttributes();

        if (isset($_GET['ResPhVisit']))
        {
            $model->attributes = $_GET['ResPhVisit'];
        }

        $fireID = $_GET['fid'];
        $clientID = $_GET['cid'];
        $fireName = urldecode($_GET['fn']);
        $clientName = urldecode($_GET['cn']);
        $pid = $_GET['pid'];

        $this->render('policyAction',array(
            'model' => $model,
            'pid' => $pid,
            'fireID' => $fireID,
            'clientID' => $clientID,
            'fireName' => $fireName,
            'clientName' => $clientName
        ));
    }

    /**
     * Download all Action List showing in the grid, getting data from model by fireid and client id
     * @param integer $fn
     * @param integer $cn
     * @param integer $fid
     * @param integer $cid
     */
    public function actionDownloadvisitlist($fn,$cn,$fid,$cid)
    {
        Yii::import('application.vendors.PHPExcel.*');
        $objPHPExcel = $this->createVisitList($fid, $cid);
        $client = $fn.', '.$cn;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $client . ' Unmatched ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * Creates actions list PHPExcel object and passes it back
     * @param integer $fireid
     * @param integer $clientID
     * @return PHPExcel instance
     */
    private function createVisitList($fireID, $clientID)
    {
        Yii::import('application.vendors.PHPExcel.*');
        $header = array('First Name','Last Name','Address','Policy Number','Response Enrollment Status','Status','Action Date','Date Created','Date Updated','Last Updated By','Submitted By','Approval User','Review Status','Engine Comments', 'Dashboard Comments');
        $model = new ResPhVisit();
        $unmatched = array();
        // $unmatched = Yii::app()->db->createCommand($sql)->bindParam(':clientID', $clientID)->queryAll();
        $obj = $model->search_actions('',$fireID,$clientID);
        $obj->pagination->pageSize = $model->count();
        $dataArray = $obj->getData();

        foreach($dataArray as $data)
        {
            $unmatched[] = array($data->memberFirstName,$data->memberLastName,$data->propertyAddress,$data->propertyPolicy,$data->response_status,$data->status,date('Y-m-d H:i', strtotime($data->date_action)),
            date('Y-m-d H:i', strtotime($data->date_created)),date('Y-m-d H:i', strtotime($data->date_updated)),$data->getLastUpdateUserName(),
            $data->userName,$data->approvalUserName,$data->review_status, $data->comments,$data->publish_comments);
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('PHVisits')
            ->setSubject('PHVisits')
            ->setDescription('PHVisits list download from WDSAdmin.')
            ->setKeywords('office PHPExcel php')
            ->setCategory('PHVisits file');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('PHVisits');

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

        // Write Data (this is the time consuming part)
        $row = 2;
        foreach ($unmatched as $result)
        {
            $activeSheet->fromArray($result, null, 'A' . $row);
            $row++;
        }

        // Setting auto column widths
        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell)
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);

        return $objPHPExcel;
    }
    /**
     * API Method: resPhVisit/apiGetEnrollmentRecommendedPolicyholders
     * Description: Gets count of all policyholders on enrollment recommended notices where
     * policyholders are not enrolled and threatened.
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2015-04-16",
     *         "endDate": "2015-04-16",
     *         "clientID": 2
     *     }
     * }
     */
	public function actionApiGetEnrollmentRecommendedPolicyholders()
	{
        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

        $sql = "
            SELECT f.Name [name], COUNT(DISTINCT t.property_pid) [count]
            FROM res_triggered t
                INNER JOIN res_notice n ON t.notice_id = n.notice_id
                INNER JOIN res_fire_name f ON f.Fire_ID = n.fire_id
            WHERE n.date_created >= :start_date
                AND n.date_created < :end_date
                AND n.client_id = :client_id
                AND t.response_status = 'not enrolled'
                AND t.threat = 1
                -- 'Enrollment/Response Recommended' or any dispatched fire
                AND (n.recommended_action = 'Enrollment/Response Recommended' OR n.wds_status = 1)
            GROUP BY f.Name, f.Fire_ID
            ORDER BY f.Name ASC
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':start_date', $data['startDate'], PDO::PARAM_STR)
            ->bindParam(':end_date', $data['endDate'], PDO::PARAM_STR)
            ->bindParam(':client_id', $data['clientID'], PDO::PARAM_INT)
            ->queryAll();

        WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $results));
    }

    /**
     * Render viewPolicyAction view
     */
    public function actionViewPolicyAction()
    {
        $model = new ViewPolicyActionForm;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'view-policyholder-action-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        $this->render('viewPolicyAction', array(
            'model' => $model
        ));
    }

    /**
     * Render data for viewPolicyAction method
     * @return mixed
     */
    public function actionViewPolicyActionData()
    {
        $model = new ViewPolicyActionForm;

        if (isset($_GET['ajax']) || isset($_GET['sort'])|| isset($_GET['page']))
        {
            $model->attributes = Yii::app()->session['ViewPolicyActionForm'];

            $dataProvider = $model->getSQLDataProvider();

            return $this->renderPartial('_viewPolicyActionDataGrid', array(
                'dataProvider' => $dataProvider
            ));
        }

        if (isset($_POST['ViewPolicyActionForm']))
        {
            $model->attributes = $_POST['ViewPolicyActionForm'];

            Yii::app()->session['ViewPolicyActionForm'] = $_POST['ViewPolicyActionForm'];

            // Map

            $criteria = new CDbCriteria;
            $criteria->select = array('notice_id','perimeter_id');
            $criteria->params[':client_id'] = $model->clientID;
            $criteria->params[':fire_id'] = $model->fireID;
            $criteria->order = 'notice_id DESC';
            $criteria->limit = 1;
            $criteria->condition = 'notice_id IN (
                SELECT MAX(notice_id)
                FROM res_notice
                WHERE fire_id = :fire_id AND client_id = :client_id
            )';

            $notice = ResNotice::model()->find($criteria);

            $perimeterID = $notice ? $notice->perimeter_id : null;

            // Grid

            $dataProvider = $model->getSQLDataProvider();

            return $this->renderPartial('_viewPolicyActionData', array(
                'clientID' => $model->clientID,
                'fireID' => $model->fireID,
                'policyholders' => $model->policyholders,
                'perimeterID' => $perimeterID,
                'dataProvider' => $dataProvider
            ));
        }
    }

    /**
     * Fill in select menu with dipsatched fires in last 6 months for selected client
     * @param integer $clientID
     */
    public function actionGetDispatchedFires($clientID)
    {
        $firesListData = ViewPolicyActionForm::getDispatchedFires($clientID);

        echo CHtml::tag('option', array('value' => ''), 'Select a fire');

        foreach ($firesListData as $fireID => $fireName)
            echo CHtml::tag('option', array('value' => $fireID), CHtml::encode($fireName));
    }

    /**
     * Retriving geojson for viewPolicyAction map
     * @param integer $clientID
     * @param integer $fireID
     * @param string $policyholders
     */
    public function actionGetPolicyGeoJson($clientID, $fireID, $policyholders)
    {
        $featureCollection = array();
        $featureCollection['type'] = 'FeatureCollection';
        $featureCollection['features'] = array();

        //SQL to select people for the grid
        $sql = ViewPolicyActionForm::getPolicySQL($policyholders);

        //Need the map symbology
        $client = Client::model()->find(array(
            'select' => 'map_enrolled_color,map_not_enrolled_color',
            'condition' => 'id = :client_id',
            'params' => array(':client_id' => $clientID)
        ));

        $result = Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':client_id1' => $clientID,
            ':client_id2' => $clientID,
            ':client_id3' => $clientID,
            ':client_id4' => $clientID,
            ':fire_id1' => $fireID,
            ':fire_id2' => $fireID,
            ':fire_id3' => $fireID,
            ':fire_id4' => $fireID
        ));

        if ($result)
        {
            foreach ($result as $row)
            {
                $featureCollection['features'][] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array($row['long'], $row['lat'])
                    ),
                    'properties' => array(
                        'pid' => $row['pid'],
                        'last_name' => $row['last_name'],
                        'address' => $row['address_line_1'] . ' ' . $row['city'] . ', ' . $row['state'],
                        'response_status' => $row['response_status'],
                        'distance' => (float)$row['distance'],
                        'enrolled_color' => $client->map_enrolled_color,
                        'not_enrolled_color' => $client->map_not_enrolled_color
                    )
                );
            }
        }

        echo json_encode($featureCollection);
    }

    /**
     * API Method: resPolicyAction/apiGetAllActionsByFire
     * Description: Gets all policy actions for a specific fire
     *
     * Post data parameters:
     * @param int noticeID - ID of the notice
     * @param int clientID - ID of the client
     * @param int fireID - ID of the fire
     *
     * Post data example:
     * { "data": { "noticeID": 7509, "fireID": 7371, "clientID": 2 } }
     */
    public function actionApiGetAllActionsByFire()
    {
        if (!WDSAPI::getInputDataArray($data, array('noticeID', 'clientID', 'fireID')))
            return;

        $noticeID = $data['noticeID'];
        $fireID = $data['fireID'];
        $clientID = $data['clientID'];
        $realTime = isset($data['realTime']) ? $data['realTime'] : 1; //Need to pass through the real time flag and change this to 0

        $returnArray['error'] = 0; // success
        $returnArray['data'] = ResPhVisit::getPolicyActionsByFire($noticeID, $fireID, $clientID, $realTime);

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resPhVisit/apiGetStats
     * Description: Gets various stats for given client/date-range about PHV's and PHA's
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2017-01-16",
     *         "endDate": "2017-04-16",
     *         "clientID": 2
     *     }
     * }
     *
     * Return array example:
     * [ 'error' => 0,
     *   'data' => [
     *      'Boulder Fire' => [
     *          'Location' => 'Boulder, CO',
     *          'Mob Date' => '2017-05-10',
     *          'Demob Date' => '2017-06-15',
     *          'Unique Properties Visited' => 45,
     *          'Total Properties Visited' => 51,
     *          'Status Totals' => [
     *              'Lost' => 1,
     *              'Damaged' => 3,
     *              'Undamaged' => 18,
     *              'Saved' => 1
     *          ],
     *          'Total Physical Actions' => 45,
     *          'Physical Action Totals' => [
     *              'Relocate Combustibles' => 3,
     *              'Fuel Mitigation' => 5,
     *              .... //NOTE: this is now a dynamic list based on the ResPhActionType table records
     *          ],
     *          'Total Recon Actions' => 33,
     *          'Recon Action Totals' => [
     *              'Homeowner Visit' => 2,
     *              'Left Brochure' => 5,
     *              .... //NOTE another dynamic list
     *          ]
     *      ],
     *      'Rocky Fire' => [
     *          ...
     *      ],
     *      'Sandy Fire' => [
     *          ...
     *      ]
     *    ]
     *  ]
     */
    public function actionApiGetStats()
    {
        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

        $statsByFire = array();
        $startDate = $data['startDate'];
        $endDate = date('Y-m-d', strtotime($data['endDate'] . ' -1 day'));
        $newstartDate = $startDate." 00:00:00";
        $newendDate = $endDate." 23:59:59";
        $clientID = $data['clientID'];

        //Get all possible Action Types
        $actionTypes = ResPhActionType::model()->findAllByAttributes(array('active' => 1));
        $allActionTypes = array(); //id => name key->value array
        $reconActionsCountInitializer = array(); //name => 0  (for initial count arrays)
        $physicalActionsCountInitializer = array(); //name => 0 (for initial count arrays)
        $physicalActionTypeIDs = array();
        $reconActionTypeIDs = array();
        foreach($actionTypes as $actionType)
        {
            $allActionTypes[$actionType->id] = $actionType->name;
            if($actionType->action_type == 'Recon')
            {
                $reconActionsInitializer[$actionType->name] = 0;
                $reconActionTypeIDs[] = $actionType->id;
            }
            else if($actionType->action_type == 'Physical')
            {
                $physicalActionsInitializer[$actionType->name] = 0;
                $physicalActionTypeIDs[] = $actionType->id;
            }
        }

        //Get all fires that were dispatched for given client and date range
       
        $sql = '
        SELECT * FROM res_fire_name WHERE fire_id IN (
            SELECT fire_id FROM res_notice
            WHERE client_id = :clientID
                AND wds_status = 1
            GROUP BY fire_id
        )';
        $dispatchedFires = ResFireName::model()->findAllBySql($sql, array(
            ':clientID' => $data['clientID']
        ));
        //Now loop through each fire and tally results
        foreach ($dispatchedFires as $fire)
        {
            //get mobilize date by looking up the first notice for this fire+client with status of Dispatched (1)
            $mobNotice = ResNotice::model()->findBySql(
                "SELECT * FROM res_notice WHERE fire_id = :fire_id AND client_id = :client_id AND wds_status = 1 ORDER BY notice_id ASC",
                array(
                    ':fire_id' => $fire->Fire_ID,
                    ':client_id' => $clientID,
                )
            );
            //get mobilize date by looking up the oldest notice for this fire+client with status of Demobilized (3)
            $demobNotice = ResNotice::model()->findBySql(
                "SELECT * FROM res_notice WHERE fire_id = :fire_id AND client_id = :client_id AND wds_status = 3 ORDER BY notice_id ASC",
                array(
                    ':fire_id' => $fire->Fire_ID,
                    ':client_id' => $clientID,
                )
            );

            //get all PHVisits
            $visits = ResPhVisit::model()->findAllBySql(
                "SELECT * FROM res_ph_visit WHERE client_id = :client_id AND fire_id = :fire_id AND date_action >= :start_date AND date_action<= :end_date AND review_status IN('published') AND status NOT IN ('unknown')",
                array(
                    ':fire_id' => $fire->Fire_ID,
                    ':client_id' => $clientID,
                    ':start_date' => $newstartDate,
                    ':end_date' => $newendDate,
                )
            );

            //initialize all the stat counters
            $totalVisited = 0;
            $totalUniqueVisited = 0;
            $uniquePIDs = array();
            $statusTotals = array('Undamaged' => 0, 'Damaged' => 0, 'Lost' => 0, 'Saved' => 0);
            $totalPhysicalActions = 0;
            $physicalActionTotals = $physicalActionsInitializer;
            $totalReconActions = 0;
            $reconActionTotals = $reconActionsInitializer;

            $policyStatusTallies = array();

            //sum up all needed PHV stats
            foreach($visits as $visit)
            {
                $totalVisited++;

                //unique visit tally logic
                if(!in_array($visit->property_pid, $uniquePIDs))
                {
                    $totalUniqueVisited++;
                    $uniquePIDs[] = $visit->property_pid;
                }

                // Policy Visit status tallies by property
                // Database Normalization Note:
                //   This section is convoluted, because statuses need to be pulled out into a persistent related
                //   table to each property over the course of a fire.  Right, now this code simulates that.

                if (!isset($policyStatusTallies[$visit->property_pid]))
                {
                    $policyStatusTallies[$visit->property_pid] = array('undamaged' => 0, 'damaged' => 0, 'lost' => 0, 'saved' => 0);
                }

                // If this property is read as "lost", then wipe ANY other statuses
                if ($visit->status === 'lost')
                {
                    $policyStatusTallies[$visit->property_pid]['saved'] = 0;
                    $policyStatusTallies[$visit->property_pid]['lost'] = 1;
                    $policyStatusTallies[$visit->property_pid]['undamaged'] = 0;
                    $policyStatusTallies[$visit->property_pid]['damaged'] = 0;
                }
                // If this property is read as "saved" and hasn't been lost, then wipe ANY other statuses
                elseif ($visit->status === 'saved' && $policyStatusTallies[$visit->property_pid]['lost'] !== 1)
                {
                    $policyStatusTallies[$visit->property_pid]['saved'] = 1;
                    $policyStatusTallies[$visit->property_pid]['lost'] = 0;
                    $policyStatusTallies[$visit->property_pid]['undamaged'] = 0;
                    $policyStatusTallies[$visit->property_pid]['damaged'] = 0;
                }
                elseif ($visit->status === 'damaged' &&
                    $policyStatusTallies[$visit->property_pid]['saved'] !== 1 &&
                    $policyStatusTallies[$visit->property_pid]['lost']  !== 1)
                {
                    $policyStatusTallies[$visit->property_pid]['undamaged'] = 0;
                    $policyStatusTallies[$visit->property_pid]['damaged'] = 1;
                }
                elseif ($visit->status === 'undamaged' &&
                    $policyStatusTallies[$visit->property_pid]['damaged'] !== 1 &&
                    $policyStatusTallies[$visit->property_pid]['saved']   !== 1 &&
                    $policyStatusTallies[$visit->property_pid]['lost']    !== 1)
                {
                    $policyStatusTallies[$visit->property_pid]['undamaged'] = 1;
                }

                //Get and Sum up all the Actions from this visit
                $actions = ResPhAction::model()->findAllByAttributes(array('visit_id' => $visit->id));
                foreach($actions as $action)
                {
                    //if it doesnt exist in the all action types array it means it's not active anymore and we are not going to worry about it.
                    if(key_exists($action->action_type_id, $allActionTypes))
                    {
                        $actionName = $allActionTypes[$action->action_type_id];
                        if(in_array($action->action_type_id, $physicalActionTypeIDs))
                        {
                            $totalPhysicalActions++;
                            $physicalActionTotals[$actionName]++;
                        }
                        else if(in_array($action->action_type_id, $reconActionTypeIDs))
                        {
                            $totalReconActions++;
                            $reconActionTotals[$actionName]++;
                        }
                    }
                }
            }

            // Adding policy tallies into cumulative totals
            foreach ($policyStatusTallies as $pid => $statusTallies)
            {
                foreach ($statusTallies as $status => $tally)
                {
                    $statusTotals[ucfirst($status)] += $tally;
                }
            }

            //add all the stats info to the main array under this fire
            if($totalVisited>0)
            {
            $statsByFire[$fire->Name] = array(
                    'Location' => $fire->City.', '.$fire->State,
                    'Mob Date' => (isset($mobNotice->date_created) ? date('Y-m-d', strtotime($mobNotice->date_created)) : 'n/a'),
                    'Demob Date' => (isset($demobNotice->date_created) ? date('Y-m-d', strtotime($demobNotice->date_created)) : 'n/a'),
                    'Unique properties visited' => $totalUniqueVisited,
                    'Total Property Visits' => $totalVisited,
                    'Status Totals' => $statusTotals,
                    'Total Physical Actions' => $totalPhysicalActions,
                    'Physical Action Totals' => $physicalActionTotals,
                    'Total Recon Actions' => $totalReconActions,
                    'Recon Action Totals' => $reconActionTotals,
                );
                }
        } //end of fire loop

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $statsByFire;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resPhVisit/apiGetPolicyActionsAllFires
     * Description: Gets all policy actions for a specific notice
     * (LEGACY FUNCTION, Use apiGetStats() instead)
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2015-04-16",
     *         "endDate": "2015-04-16",
     *         "clientID": 2
     *     }
     * }
     */
    public function actionApiGetPolicyActionsAllFires()
    {
        $data = NULL;
        $returnArray = array();
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

        $dateStart = $data['startDate'];
        $dateEnd = $data['endDate'];
        $clientID = $data['clientID'];
        //First get all the fires we were dispatched for in the given timeframe
        $sql = '
        SELECT * FROM res_fire_name WHERE fire_id IN (
            SELECT fire_id FROM res_notice
            WHERE date_created >= :startDate
                AND date_created <= :endDate
                AND client_id = :clientID
                AND wds_status = 1
            GROUP BY fire_id
        )';

        //Get all fires that were dispatched
        $dispatchedFires = ResFireName::model()->findAllBySql($sql, array(
            ':startDate' => $data['startDate'],
            ':endDate' => $data['endDate'],
            ':clientID' => $data['clientID']
        ));

        //Now loop through each fire and tally results
        foreach ($dispatchedFires as $fire)
        {
            $fireID = $fire->Fire_ID;

            //Each entry will have fire info and a summary of policyholders
            $result = array(
                'fire_id' => $fire->Fire_ID,
                'fire_name' => $fire->Name,
                'fire_city' => $fire->City,
                'fire_state' => $fire->State,
                'notice_id' => ResNotice::model()->findByAttributes(array(
                    'fire_id' => $fire->Fire_ID,
                    'client_id' => $data['clientID']
                ), array('order' => 'notice_id DESC'))->notice_id,
                'physical_actions' => 0,
                'recon_actions' => 0,
                'customer_service' => 0,
                'sprinklers' => 0,
                'gel' => 0,
                'fuel_mitigation' => 0
            );

            //Get total visits
            $criteria = new CDbCriteria;
            $criteria->select = "property_pid";
            $criteria->addCondition("client_id = " . $data['clientID']);
            $criteria->addCondition("fire_id = " . $fire->Fire_ID);
            $criteria->addCondition("date_action >= '" . $data['startDate'] . "'");
            $criteria->addCondition("date_action <= '" . $data['endDate'] . "'");
            $criteria->group = "date_action, property_pid";

            $totalVisits = ResPhVisit::model()->count($criteria);
            $result['policyholder_visits'] = ($totalVisits) ? $totalVisits : 0;

            //Get total homes
            $criteria->group = "property_pid";
            $totalHomes = ResPhVisit::model()->count($criteria);
            $result['policyholder_homes'] = ($totalHomes) ? $totalHomes : 0;

            // Get number of lost,safe,saved,damaged homes
            $sql = '
                SELECT
                    COUNT(id) AS [total], [status]
                FROM res_ph_visit
                WHERE id IN (
                    SELECT MAX(id)
                    FROM res_ph_visit
                    WHERE
                        fire_id = :fireID
                        AND date_action >= :startDate
                        AND date_action < :endDate
                        AND client_id = :clientID
                    GROUP BY property_pid
                )
                GROUP BY [status]';

            $queryResult = Yii::app()->db->createCommand($sql)
                ->bindValue(':startDate', $dateStart, PDO::PARAM_STR)
                ->bindValue(':endDate', $dateEnd, PDO::PARAM_STR)
                ->bindValue(':clientID', $clientID, PDO::PARAM_INT)
                ->bindValue(':fireID', $fireID, PDO::PARAM_INT)
                ->queryAll();

            $result['policyholder_lost'] = (int) array_filter($queryResult, function($row){ return($row['status'] == 'lost');});
            $result['policyholder_safe'] = (int) array_filter($queryResult, function($row){ return($row['status'] == 'undamaged');});
            $result['policyholder_saved'] = (int) array_filter($queryResult, function($row){ return($row['status'] == 'saved');});
            $result['policyholder_damaged'] = (int) array_filter($queryResult, function($row){ return($row['status'] == 'damaged');});

            $sql = "select
                        at.action_type, count(v.id) as total from res_ph_visit v
                    inner join
                        res_ph_action a on v.id = a.visit_id
                    inner join
                        res_ph_action_type at on at.id = a.action_type_id
                    where
                        v.client_id = :clientID
                        and v.fire_id = :fireID
                        and v.date_action >= :startDate
                        and v.date_action < :endDate
                    group by
                        at.action_type";

            $resultActionType = Yii::app()->db->createCommand($sql)
                ->bindValue(':startDate', $dateStart, PDO::PARAM_STR)
                ->bindValue(':endDate', $dateEnd, PDO::PARAM_STR)
                ->bindValue(':clientID', $clientID, PDO::PARAM_INT)
                ->bindValue(':fireID', $fireID, PDO::PARAM_INT)
                ->queryAll();

            foreach($resultActionType as $action){
                if($action['action_type'] == 'Physical'){
                    $result['physical_actions'] = $action['total'];
                }
                elseif($action['action_type'] == 'Recon'){
                    $result['recon_actions'] = $action['total'];
                }

                $result['customer_service'] = 0;
            }

            $sql = "select
                        at.name, count(v.id) as total from res_ph_visit v
                    inner join
                        res_ph_action a on v.id = a.visit_id
                    inner join
                        res_ph_action_type at on at.id = a.action_type_id
                    where
                        at.name in ('Sprinklers Set Up/Maintained', 'Gel Applied/Maintained', 'Fuel Mitigation')
                        and v.client_id = :clientID
                        and v.fire_id = :fireID
                        and v.date_action >= :startDate
                        and v.date_action < :endDate
                    group by
                        at.name";

            $resultActions = Yii::app()->db->createCommand($sql)
                ->bindValue(':startDate', $dateStart, PDO::PARAM_STR)
                ->bindValue(':endDate', $dateEnd, PDO::PARAM_STR)
                ->bindValue(':clientID', $clientID, PDO::PARAM_INT)
                ->bindValue(':fireID', $fireID, PDO::PARAM_INT)
                ->queryAll();

            foreach($resultActions as $action){
                if($action['name'] == 'Sprinklers Set Up/Maintained'){
                    $result['sprinklers'] = $action['total'];
                }
                elseif($action['name'] == 'Gel Applied/Maintained'){
                    $result['gel'] = $action['total'];
                }
                elseif($action['name'] == 'Fuel Mitigation'){
                    $result['fuel_mitigation'] = $action['total'];
                }
            }

            $returnData[] = $result;
        }

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resPhVisit/apiGetResPhVisit
     * Description: Gets a policyholder visit based on given primary key id schedule
     *
     * Post data parameters:
     * @param int id
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 111
     *     }
     * }
     */
    public function actionApiGetResPhVisit()
    {
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('id')))
        {
            return;
        }

        $resPhVisit = ResPhVisit::model()->findByPk($data['id']);

        if(isset($resPhVisit))
        {
            $returnData = $resPhVisit->attributes;

            //Ph Actions data
            $selectedActions = ResPhAction::model()->findAllByAttributes(array('visit_id' => $resPhVisit->id));
            $selectedActionsArray = array();
            $selectedqtyArray = array();
            foreach($selectedActions as $action)
            {
                $selectedActionsArray[] = $action->action_type_id;
                $selectedqtyArray[$action->action_type_id] = $action->qty;
            }
            $returnData['selectedActions'] = $selectedActionsArray;
            $returnData['selectedqtyArray'] = $selectedqtyArray;

            //mem prop data
            $returnData['propertyAddress'] = $resPhVisit->propertyAddress;
            $returnData['propertyPolicy'] = $resPhVisit->propertyPolicy;
            $returnData['memberFirstName'] = $resPhVisit->memberFirstName;
            $returnData['memberLastName'] = $resPhVisit->memberLastName;

            //base data
            $resPhActionTypes = ResPhActionType::model()->with('phActionCategory')->findAll('active = 1');
            $resPhActionTypeList = array();
            foreach($resPhActionTypes as $resPhActionType)
            {
                foreach($resPhActionType as $actionTypes)
                {
                    $types = array('id'=>$resPhActionType->id,'name'=>$resPhActionType->name,'units'=>$resPhActionType->units,'description'=>Helper::sanatizeWordString($resPhActionType->definition));
                }
                $resPhActionTypeList[$resPhActionType->phActionCategory->category][] = $types;
            }

            $returnData['resPhActionTypeList'] = $resPhActionTypeList;
            $returnData['statusList'] = ResPhVisit::model()->getStatusTypes();
            //echo var_export($returnData, true );
            return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $returnData));
        }
        else
        {
            WDSAPI::echoJsonError('Could not find Policyholder Visit');
            return NULL;
        }
    }

    /**
     * API Method: resPhVisit/apiGetResPhVisits
     * Description: Gets policyholder visits based on given date
     * NOTE: also returns visits that were done by users on the same engine schedule
     *
     * Post data parameters:
     * @param string date
     *
     * Post data example:
     * {
     *     "data": {
     *         "date": "2017-01-01"
     *     }
     * }
     */
    public function actionApiGetResPhVisits()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('date')))
            return;

        $resPhVisits = ResPhVisit::model()
            ->with('property','property.member', 'user', 'approvalUser')
            ->findAll(array(
                'condition' => 'date_action >= CONVERT(DATE, :start_date) AND date_action < DATEADD(DAY, 1, CONVERT(DATE, :end_date))',
                'params' => array(':start_date' => $data['date'], ':end_date' => $data['date']),
                'order' => 'date_action'
            )
        );

        $returnData = array();
        foreach($resPhVisits as $resPhVisit)
        {
            //return all db attributes
            $data = $resPhVisit->attributes;
            //as well as virtual/related table attributes
            $data['propertyAddress'] = $resPhVisit->propertyAddress;
            $data['propertyPolicy'] = $resPhVisit->propertyPolicy;
            $data['memberFirstName'] = $resPhVisit->memberFirstName;
            $data['memberLastName'] = $resPhVisit->memberLastName;
            $data['userName'] = $resPhVisit->userName;
            $data['propertystreetAddress'] = $resPhVisit->property->address_line_1;

            $returnData[] = $data;
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resPhVisit/apiResPhVisitCreate
     * Description: API Method to create a Policyholder Visit record and associated Actions
     *
     * Post data parameters:
     * @param ResPhVisit resPhVisit - the attributes
     * @param ResPhAction[] resPhActions - the related ResPhActions
     * @param integer userID - ID of the user submitting data
     *
     * Post data example:
     * {
     *     "data": {
     *         "resPhVisit": {
     *              "status": "damaged",
     *              "comments": "",
     *              "date_action": "2017-03-0815:50",
     *              "user_id": "693",
     *              "id": "3143",
     *              "property_pid": "406299",
     *              "client_id": "1007",
     *              "fire_id": "17504"
     *         },
     *         "resPhActions": ["20", "21", "13", "14"]
     *     }
     * }
     */
    public function actionApiResPhVisitCreate()
    {
        //validate and extract data input
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('resPhVisit', 'resPhActions')))
            return;

        $id = '';
        //create new Policyholder Visit with input data
        $resPhVisit = new ResPhVisit;
        $resPhVisit->attributes = $data['resPhVisit'];
        if ($resPhVisit->save())
        {
            $id = $resPhVisit->id;
            //after successful save we now have the visit id and can save the associated actions that were selected
            foreach($data['resPhActions'] as $actionID)
            {
                $resPhAction = new ResPhAction;
                $resPhAction->visit_id = $resPhVisit->id;
                $resPhAction->action_type_id = $actionID;
                if(!$resPhAction->save())
                {
                    WDSAPI::echoJsonError('Error Saving one of the Policyholder Actions associated with the visit');
                    return NULL;
                }
            }
            //Qty Updates
            if(isset($data['resPhActionTypeQty']))
            {
                foreach($data['resPhActionTypeQty'] as $typeid => $qty)
                {
                    if($qty != '' && is_numeric($qty))
                    Yii::app()->db->createCommand("UPDATE res_ph_action SET qty = $qty WHERE visit_id = $id AND action_type_id = $typeid")->execute();
                }
            }
            //return success
            return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => array('id'=>$resPhVisit->id, 'message'=>'Policyholder Visit created successfully!')));
        }
        else
        {
            WDSAPI::echoJsonError('Error Saving the Policyholder Visit');
            return NULL;
        }
    }

    /**
     * API Method: resPhVisit/apiResPhVisitUpdate
     * Description: API Method to update a Policyholder Visit record and associated Actions
     *
     * Post data parameters:
     * @param int id - the primary key of the existing ResPhVisit
     * @param ResPhVisit resPhVisit - the attributes
     * @param ResPhAction[] resPhActions - the related ResPhActions
     * @param integer userID - ID of the user submitting data
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": "2956",
     *         "resPhVisit": {
     *              "status": "damaged",
     *              "comments": "",
     *              "date_action": "2017-03-0815:50",
     *              "user_id": "693",
     *              "id": "3143",
     *              "property_pid": "406299",
     *              "client_id": "1007",
     *              "fire_id": "17504"
     *         },
     *         "resPhActions": ["20", "21", "13", "14"]
     *     }
     * }
     */
    public function actionApiResPhVisitUpdate()
    {
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('id', 'resPhVisit', 'resPhActions')))
            return;

        $id = $data['id'];
        $resPhVisit = ResPhVisit::model()->findByPk($data['id']);
        unset($data['resPhVisit']['id']); //bug fix to not set unsafe id attribute by below assignment
        $resPhVisit->attributes = $data['resPhVisit'];

        //go through each availible action type
        foreach(ResPhActionType::model()->findAll() as $actionType)
        {
            $existingAction = ResPhAction::model()->findByAttributes(array('visit_id'=>$id, 'action_type_id'=>$actionType->id));
            //if a related action does not already exist and it was selected, then create it
            if(in_array($actionType->id, $data['resPhActions']) && !isset($existingAction))
            {
                $resPhAction = new ResPhAction;
                $resPhAction->visit_id = $resPhVisit->id;
                $resPhAction->action_type_id = $actionType->id;
                if(!$resPhAction->save())
                {
                    WDSAPI::echoJsonError('Error Saving one of the Policyholder Actions associated with the visit');
                    return NULL;
                }
            }
            //else if it wasn't in the selected array but it exists from being previously selected, then delete it
            else if(isset($existingAction) && !in_array($actionType->id, $data['resPhActions']))
            {
                if(!$existingAction->delete())
                {
                    WDSAPI::echoJsonError('Error deleting one of the Policyholder Actions no longer associated with the visit');
                    return NULL;
                }
            }
            //else wasn't selected and didn't exist from before OR was selected and continued to be selected
        }

        //Qty Updates
        if(isset($data['resPhActionTypeQty']))
        {
            foreach($data['resPhActionTypeQty'] as $typeid => $qty)
            {
                if($qty != '' && is_numeric($qty))
                Yii::app()->db->createCommand("UPDATE res_ph_action SET qty = $qty WHERE visit_id = $id AND action_type_id = $typeid")->execute();
            }
        }

        //If the Visit has been updated from the API (engines site for example) and it is already published, then we need to set it to re-review.
        if($resPhVisit->review_status == 'published')
        {
            $resPhVisit->review_status = 're-review';
        }

        if ($resPhVisit->save())
        {
            return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => array('message'=>'Policyholder Visit Updated Successfully!')));
        }
        else
        {
            WDSAPI::echoJsonError('Error Saving the Policyholder Visit');
            return NULL;
        }
    }

    /**
     * API Method: resPhVisit/apiGetBaseInfo
     * Description: API Method to some base info for PHVs like the availible statuses and action types
     */
    public function actionApiGetBaseInfo()
    {
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('pid')))
            return;

        $resPhActionTypes = ResPhActionType::model()->with('phActionCategory')->findAll('active = 1');
        //$resPhActionTypeList = CHtml::listData($resPhActionTypes, 'id', 'name', 'phActionCategory.category');
        $resPhActionTypeList = array();
        foreach($resPhActionTypes as $resPhActionType)
        {
            foreach($resPhActionType as $actionTypes)
            {
                $types = array('id'=>$resPhActionType->id,'name'=>$resPhActionType->name,'units'=>$resPhActionType->units,'description'=>Helper::sanatizeWordString($resPhActionType->definition));
            }
            $resPhActionTypeList[$resPhActionType->phActionCategory->category][] = $types;
        }
        $returnData['resPhActionTypeList'] = $resPhActionTypeList;

        $returnData['statusList'] = ResPhVisit::model()->getStatusTypes();

        $prop = Property::model()->with('member')->findByPk($data['pid']);
        $returnData['propertyAddress'] = $prop->address_line_1.', '.$prop->city.', '.$prop->state;
        $returnData['propertyPolicy'] = $prop->policy;
        $returnData['memberFirstName'] = $prop->member->first_name;
        $returnData['memberLastName'] = $prop->member->last_name;
        $returnData['client_id'] = $prop->client_id;


        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}
