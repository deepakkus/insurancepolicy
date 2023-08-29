<?php

class ResMonitorLogController extends Controller
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
                        'apiGetMonitorLog',
                        'apiGetAllMonitorModels'
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
                    'delete',
                    'smokeCheck',
                    'viewMonitoredFire',
                    'monitorFire',
                    'downloadKMZ',
                    'download',
                    'downloadMatchedList'
                ),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetMonitorLog',
                    'apiGetAllMonitorModels'
                ),
                'users' => array('*')),
            array('deny',
                'users' => array('*'),
            ),
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
     * @param integer $id the ID of the model to be loaded
     * @return ResMonitoringLog the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=ResMonitorLog::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------

    /**
     * Monitoring Log Grid
     */
    public function actionAdmin()
    {
        $model = new ResMonitorLog('search');
        $model->unsetAttributes();

        if (isset($_GET['ResMonitorLog']))
        {
            $model->attributes = $_GET['ResMonitorLog'];
        }

        // Setting up Columns to Show and Session Variables
        $columnsToShow = array(
            'Fire_Name',
            'fire_alternate_name',
            'Fire_City',
            'Fire_State',
            'Fire_Size',
            'Fire_Containment',
            'Dispatcher',
            'monitored_time_stamp',
            'monitored_date_stamp',
            'closest',
            'client_triggered',
            'client_noteworthy',
            'Comments',
            'resFireObs',
            'Smoke_Check'
        );

        if (isset($_GET['columnsToShow']))
        {
            $_SESSION['res_monitor_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        else if (isset($_SESSION['res_monitor_columnsToShow']))
        {
            $columnsToShow = $_SESSION['res_monitor_columnsToShow'];
        }

        $this->render('admin', array(
            'model' => $model,
            'columnsToShow' => $columnsToShow
        ));
    }


    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate($obs_id, $page)
    {
        $model = new ResMonitorLog;

        if (isset($_POST['ResMonitorLog']))
        {
            $model->attributes = $_POST['ResMonitorLog'];

            $resFireObs = ResFireObs::model()->findByPk($obs_id);

            $perimeter = ResPerimeters::model()->find(array(
                'condition' => 'fire_id = :fire_id',
                'order' => 'id DESC',
                'limit' => 1,
                'params' => array(':fire_id' => $resFireObs->Fire_ID)
            ));

            $model->Perimeter_ID = $perimeter->id;

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Monitoring Log Entry ' . $model->Monitor_ID . ' Created Successfully!');
                $this->redirect(array('admin'));
            }
        }
        else
        {
            $model->Dispatcher = Yii::app()->user->name;
            $model->prefillMonitoringLogForm($obs_id);

            $this->render('create',array(
                'model' => $model,
                'page' => $page
            ));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id, $page)
    {
        $model = $this->loadModel($id);

        if (isset($_POST['ResMonitorLog']))
        {
            $model->attributes = $_POST['ResMonitorLog'];
            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Monitoring Log Entry ' . $model->Monitor_ID . ' Updated Successfully!');
                if ($page === 'monitor')
                {
                    $this->redirect(array('admin'));
                }
                else
                {
                    $this->redirect(array('smokeCheck'));
                }
            }
        }

        //Show form
        $this->render('update',array(
            'model' => $model,
            'page' => $page
        ));
    }

    /**
     * Deletes an entry in the Monitoring Log model
     * If delete is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionDelete($id)
    {
        if (Yii::app()->request->isPostRequest)
        {
            $this->loadModel($id)->delete();
            if (!isset($_GET['ajax']))
            {
                $this->redirect(array('admin'));
            }
        }
        else
        {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Smokecheck Grid
     */
    public function actionSmokeCheck()
    {
        $model = new ResMonitorLog('search');
        $model->unsetAttributes();

        if (isset($_GET['ResMonitorLog']))
        {
            $model->attributes = $_GET['ResMonitorLog'];
        }

        // Setting up Columns to Show and Session Variables
        $columnsToShow = array(
            'Fire_City',
            'Fire_State',
            'Fire_Size',
            'Fire_Containment',
            'Fire Fuels'
        );

        if (isset($_GET['columnsToShow']))
        {
            $_SESSION['res_monitor_columnsToShow'] = $_GET['columnsToShow'];
            $columnsToShow = $_GET['columnsToShow'];
        }
        else if (isset($_SESSION['res_monitor_columnsToShow']))
        {
            $columnsToShow = $_SESSION['res_monitor_columnsToShow'];
        }

        $this->render('smokeCheck', array(
            'model' => $model,
            'columnsToShow' => $columnsToShow
        ));
    }

    /**
     * Renders the 'new fire' page
     * One of the following might be met:
     *      user has supplied lat and longs
     *      user has attached a file
     *      user had give acres for point to acres
     *      user has submitted a smokecheck with the geojson
     */
    public function actionMonitorFire($dispatchLat = null, $dispatchLong = null)
    {
        $uploadedFile = CUploadedFile::getInstanceByName('kml');

        $smokecheck = Yii::app()->request->getPost('smokecheck');
        $pointtoacres = Yii::app()->request->getPost('pointtoacres');

        //Sessions used to pre populate forms later on when user is adding fire into system (fire name, fire details etc)
        Yii::app()->session['firePerimeter'] = null;
        Yii::app()->session['fireSize'] = null;
        Yii::app()->session['centroidLat'] = null;
        Yii::app()->session['centroidLong'] = null;

        //Used in the map
        $smokecheckResults = null;
        $nearbyPerimeters = null;
        $pointtoacresWkt = null;
        $alertDistance = null;
        $centroidLong = null;
        $centroidLat = null;
        $fileName = null;
        $geoJson = null;
        $long = null;
        $lat = null;

        if (isset($smokecheck['centroidLong'])) $centroidLong = substr($smokecheck['centroidLong'], 0, 9);
        if (isset($smokecheck['centroidLat'])) $centroidLat = substr($smokecheck['centroidLat'], 0, 8);
        if (isset($smokecheck['alertDistance'])) $alertDistance = $smokecheck['alertDistance'];
        if (isset($smokecheck['geoJson'])) $geoJson = $smokecheck['geoJson'];
        if (isset($smokecheck['long'])) $long = $smokecheck['long'];
        if (isset($smokecheck['lat'])) $lat = $smokecheck['lat'];

        if (isset($pointtoacres['long'])) $long = $pointtoacres['long'];
        if (isset($pointtoacres['lat'])) $lat = $pointtoacres['lat'];

        // If the user uploads a file
        if ($uploadedFile)
        {
            $fileName = GIS::getUploadedKmlKmz($uploadedFile);
        }
        else if (isset($smokecheck['kmlFileName']) && !empty($smokecheck['kmlFileName']))
        {
            // Cleanup kml file
            $tempDir = Yii::getPathOfAlias('webroot.tmp');

            if (file_exists($tempDir . DIRECTORY_SEPARATOR . $smokecheck['kmlFileName']))
            {
                unlink($tempDir . DIRECTORY_SEPARATOR . $smokecheck['kmlFileName']);
            }
        }

        // If the user submits point to acres
        if (isset($pointtoacres['acres']))
        {
            $pointtoacresWkt = GIS::pointToAcres($lat, $long, $pointtoacres['acres']);
        }

        // Run smokecheck
        if ($geoJson)
        {
            // Spatial and triggered info
            $result = GIS::runSmokecheck($geoJson, $centroidLat, $centroidLong);

            // Policyholder specific - shown as stats
            $smokecheckResults = $result['policyholderData'];

            // Set all sessions from the smokecheck result - these are used later to pre populate fire info if the user chooses to add into our system
            Yii::app()->session['firePerimeter'] = $result['firePerimeter'];
            Yii::app()->session['centroidLat'] = $result['centroidLat'];
            Yii::app()->session['centroidLong'] = $result['centroidLong'];
            Yii::app()->session['fireSize'] = $smokecheck['fireSize'];

            // Find old perimeters in vicinity
            $nearbyPerimeters = GIS::monitorGetNearbyOldPerimeters(Yii::app()->session['firePerimeter']);

            if ($nearbyPerimeters)
            {
                Yii::app()->user->setFlash('error', "There are past fires nearby, check and make sure this isn't a duplicate!");
            }
        }

        $this->render('monitorFire', array(
            'smokecheckResults' => $smokecheckResults,
            'nearbyPerimeters' => $nearbyPerimeters,
            'pointtoacresWkt' => $pointtoacresWkt,
            'alertDistance' => $alertDistance,
            'pointToAcres' => $pointtoacres,
            'centroidLong' => $centroidLong,
            'centroidLat' => $centroidLat,
            'dispatchLong'=>$dispatchLong,
            'dispatchLat'=>$dispatchLat,
            'fileName' => $fileName,
            'geoJson' => $geoJson,
            'long' => $long,
            'lat' => $lat
        ));
    }

    /**
     * Page showing fire stats, map and policyholder information for the DO to review
     * @param integer $id
     * @param string $page
     */
    public function actionViewMonitoredFire($id, $page)
    {
        $model = ResMonitorLog::model()->findByPk($id);
        $fire = $model->resFireObs->resFireName;
        $fireDetails = $model->resFireObs;
        $fireDetailsHistory = ResFireObs::model()->findAllByAttributes(array('Fire_ID' => $fireDetails->Fire_ID));
        $triggers = $model->resMonitorTriggered;

        $nearbyPerimeters = GIS::smokecheckGetNearbyOldPerimeters($model->Perimeter_ID);

        if ($nearbyPerimeters)
        {
            Yii::app()->user->setFlash('error', "There are past fires nearby!");
        }

        $this->render('viewMonitoredFire',array(
            'model' => $model,
            'fire' => $fire,
            'fireDetails' => $fireDetails,
            'fireDetailsHistory'=> $fireDetailsHistory,
            'nearbyPerimeters' => $nearbyPerimeters,
            'triggers' => $triggers,
            'page' => $page
        ));
    }

    /**
     * Download an Excel document of fires triggering a client
     * @param string $dateStart
     * @param string $dateEnd
     * @param integer $clientID
     */
    public function actionDownload($dateStart = null, $dateEnd = null, $clientID = 1)
    {
        //Reformat dates
        $dateStart = date('Y-m-d', strtotime($dateStart));
        $dateEnd = date('Y-m-d', strtotime($dateEnd . ' +1 day'));

        //Get monitor log entires
        $result = ResMonitorLog::getMonitoredFires($dateStart, $dateEnd, $clientID, null, 1);

        //Begin building excel file
        Yii::import('application.vendors.PHPExcel.*');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle("Monitor Log Results for $dateStart - $dateEnd")
            ->setSubject("Monitor Log Results for $dateStart - $dateEnd")
            ->setDescription('Monitor Log download from WDSAdmin.');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Monitor Log');

        // Setting header
        $header = array('Fire_ID','Name','City','State','Lat','Long','Size', 'Monitored_Date', 'Enrolled', 'Not Enrolled');

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
            $write = array(
                $entry['fire_id'],
                $entry['name'],
                $entry['city'],
                $entry['state'],
                $entry['coord_lat'],
                $entry['coord_long'],
                $entry['size'],
                $entry['monitored_date'],
                $entry['enrolled'],
                $entry['eligible']
            );

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
        header('Content-Disposition: attachment;filename="' . $client . ' Monitor Log ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    //------------------------------------------------------------------- General Calls----------------------------------------------------------------

    /**
     * Method downloads a KML
     * @param integer $perimeter_id
     * @param string $fire_name
     */
    public function actionDownloadKMZ($perimeter_id = null, $fire_name = null, $miles = 6)
    {
        if ($perimeter_id && $fire_name)
        {
            $kmz = new KMZSmokecheck(null, $fire_name, $perimeter_id, $miles);
            $kmz->downloadKMZ();
        }
        else if (Yii::app()->session['firePerimeter'])
        {
            $kmz = new KMZSmokecheck(Yii::app()->session['firePerimeter'], null, null, $miles);
            $kmz->downloadKMZ();
        }
    }

    private function fireName($data)
    {
        return isset($data->fire_name) ? $data->fire_name : '';
    }

    public function getFireNameUpdated($data, $page)
    {
        $retval = '';
        $link = $this->createUrl("/resMonitorLog/viewMonitoredFire", array("id" => $data->Monitor_ID, "page" => $page));
        $fireName = "<a href = '$link'>" . CHtml::encode($this->fireName($data)) . "</a>";

        if ($data->fire_containment == 100)
        {
            $retval = $fireName . '<br /><b><i style="color:green;">(Contained)</i></b>';
        }
        else if (isset($data->fire_contained) && $data->fire_contained == 1)
        {
            $retval = $fireName . '<br /><b><i style="color:green;">(Now Contained)</i></b>';
        }
        else
        {
            $retval = $fireName;
        }

        if ($data->Update_Fire == 1)
        {
            "<a href = '$link'>" . $retval .= '</a><br /><i style="color:red;">(Updated)</i>';
        }
        return $retval;
    }

    public function getFireContainment($data)
    {
        if ($data->resFireObs)
        {
            $retval = is_numeric($data->fire_containment) ? ($data->fire_containment == -1 ? 'unknown' : $data->fire_containment. '%') : $data->fire_containment;

            if ($data->fire_containment == 100)
                $retval = '<b style="color:green">' . $retval . '</b>';

            if (isset($data->fire_contained) && $data->fire_contained == 1)
                $retval .= '<br /><b><i style="color:green;">(100%)</i></b>';

            return $retval;
        }
        return '';
    }

    public function getMonitorFuels($data)
    {
        if (isset($data->resFireObs->resFireName->ResFuel))
        {
            return implode(', ', array_map(function($fuel) { return $fuel->resFuelType->Type; }, $data->resFireObs->resFireName->ResFuel));
        }
    }

    public function getMappingLinks($data,$row)
    {
        if ($data->resFireObs && $data->resFireObs->resFireName)
        {
            return CHtml::link('kml',$this->createUrl("/resMonitorLog/getMonitorFireKML",array('id'=>$data->Monitor_ID))) . '<br />' .
                   CHtml::link('map','https://maps.google.com/maps?q='.$data->fire_lat.', '.$data->fire_lon,array('target'=>'_blank')) . ' ' .
                   CHtml::tag('i',array('class'=>'icon-globe'));
        }
        else
        {
            return CHtml::link('kml',$this->createUrl("/resMonitorLog/getMonitorFireKML",array('id'=>$data->Monitor_ID))) . '<br /> ' .
                   CHtml::tag('i',array('class'=>'icon-globe'));
        }
    }

    /**
     * Function
     * Description: Retreives all fuel types for a given fire as an array
     *
     * Post data parameters:
     * @param int fire_id - The ID of the Fire to get the fuels for
     *
     */
    public function getFuels($fireID)
    {
        $returnArray = array();
        $fuels = ResFuel::model()->findAll("Fire_ID = $fireID");
        foreach($fuels as $fuel)
            $returnArray[] = $fuel->resFuelType->Type;

        return $returnArray;
    }

    //-------------------------------------------------------------------API Calls----------------------------------------------------------------

    /**
     * API Method: resMonitorLog/apiGetMonitorLog
     * Description: Gets all policy actions for a specific notice
     *
     * Post data parameters:
     * @param int limit (optional) - limits the number of results returned
     *
     * Post data example:
     * { "data": { "limit": 200 } }
     */
    public function actionApiGetMonitorLog()
    {
        $data = null;
        $returnArray = array();
        $returnArray['data']=array();

        WDSAPI::getInputDataArray($data, array('date'));

        $clientID = (isset($data['clientID'])) ? $data['clientID'] : null;

        $returnArray = ResMonitorLog::getMonitoredFires($data['date'], null, $clientID, $data['noteworthy'], $data['allFires']);

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resMonitorLog/apiGetAllMonitorModels
     * Description: Gets all the models to load the monitor log page
     *
     * Post data parameters:
     * @param int monitor_id - the id of the monitor log entry
     *
     * Post data example:
     * { "data": { "monitor_id": 200 } }
     */
    public function actionApiGetAllMonitorModels()
    {
        $data = null;
        $returnArray = array();
        $returnArray['data']=array();

        if (!WDSAPI::getInputDataArray($data, array('monitorID')))
            return;

        $monitorID = $data['monitorID'];
        $clientID = (isset($data['clientID'])) ? $data['clientID'] : null;

        $monitorLogData = $this->getMonitorLog($monitorID, $clientID);
        $fireDetailsData = $this->getFireDetails($monitorLogData['obs_id']);

        $returnArray = array(
            'monitorLog'=> $monitorLogData,
            'fireDetails'=> $fireDetailsData
        );

        WDSAPI::echoResultsAsJson(array('data'=>$returnArray, 'error'=>0));
    }

    //-------------------------------------------------------------------Helper Calls----------------------------------------------------------------

    public function getMonitorLog($id, $clientID)
    {

        $sql = "
        declare @id int = :id;
        select
            l.monitor_id,
            l.comments,
            l.monitored_date,
            l.obs_id,
            l.perimeter_id,
            l.media_event,
            f.fire_id,
            f.name,
            f.city,
            f.state,
            f.contained,
            f.contained_date,
            f.coord_lat,
            f.coord_long,
            t.enrolled,
            t.eligible,
            t.closest,
            t.noteworthy,
            (select min(monitored_date) from res_monitor_log where obs_id in (select obs_id from res_fire_obs where fire_id = o.fire_id)) as initial_date
        from
            res_monitor_log l
        inner join
            res_fire_obs o on o.obs_id = l.obs_id
        inner join
            res_fire_name f on f.fire_id = o.fire_id
        inner join
            (select monitor_id, enrolled, eligible, closest, noteworthy from res_monitor_triggered where monitor_id = @id and client_id = :client_id) t on t.monitor_id = l.monitor_id
        where
            l.monitor_id = @id
        ";

        $returnData = Yii::app()->db->createCommand($sql)
            ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
            ->bindParam(':id', $id, PDO::PARAM_INT)
            ->queryRow();

        return $returnData;
    }

    public function getFireDetails($id)
    {
        $model = ResFireObs::model()->findByPk($id);
        $returnData = $model->attributes;

        return $returnData;
    }

    public function actionDownloadMatchedList($monitor_Id)
    {
        Yii::import('application.vendors.PHPExcel.*'); 
        
        $model = ResMonitorLog::model()->findByPk($monitor_Id);
        $objPHPExcel = $this->createAllMatchedList($model->Perimeter_ID, $model->Alert_Distance);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="All Matched ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    //public function createAllMatchedList($ArrayClient)
    public function createAllMatchedList($perimeterID, $bufferDistance)
    {
        Yii::import('application.vendors.PHPExcel.*'); 
        $row = 2;
        $sql = '';
        $header = array();
        // Setting header
        $buffer = Helper::milesToMeters($bufferDistance);
        
        $header = array('Client','PID','Last','First','Address','City','County','State','Zip','Lat','Lon', 'Wds_Lat', 'Wds_long', 'Comments');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('Matched')
            ->setSubject('Matched')
            ->setDescription('Matched download from WDSAdmin.')
            ->setKeywords('office PHPExcel php')
            ->setCategory('unmatched file');
        
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);
        
        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Matched');
        
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
        //foreach($ArrayClient as $clientID)
        //{
            
            $sql = "
            DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = :perimeterID);
            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10);
            END
            DECLARE @bufferMeters float(24) = :buffer;
            DECLARE @buffer geography = @perimeter.STBuffer(@bufferMeters)
            DECLARE @boundingboxgeom geometry = geometry::STGeomFromWKB(@buffer.STAsBinary(), @buffer.STSrid).STEnvelope();

            SELECT * FROM (
                SELECT
                    c.name as client,
                    p.pid,
                    m.last_name,
                    m.first_name,
                    p.address_line_1,
                    p.city,
                    p.county,
                    p.state,
                    p.zip,
                    p.lat,
				    p.long,
                    p.wds_lat,
                    p.wds_long,
                    p.comments,
                    p.geog.STDistance(@perimeter) [distance]
                FROM properties p
                    INNER JOIN members m ON m.mid = p.member_mid
                    INNER JOIN client c ON c.id = m.client_id
                WHERE p.policy_status = 'active'
                    AND p.type_id = 1
                    AND p.wds_geocode_level != 'unmatched'
                    AND p.wds_lat >= @boundingboxgeom.STPointN(1).STY
                    AND p.wds_lat <= @boundingboxgeom.STPointN(3).STY
                    AND p.wds_long <= @boundingboxgeom.STPointN(2).STX
                    AND p.wds_long >= @boundingboxgeom.STPointN(4).STX
            ) s
            WHERE s.distance <= @bufferMeters 
            ";
        
        $matched = Yii::app()->db->createCommand($sql)
            ->bindParam(':buffer', $buffer, PDO::PARAM_STR)
            ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
            ->queryAll();
            // Write Data (this is the time consuming part)
        
        foreach ($matched as $result)
        {
            foreach($result as $key=>$val)
            {
                if($key!='distance')
                {
                    $resultArr[$key] = $val;
                }
            }
            $activeSheet->fromArray($resultArr, null, 'A' . $row);
          
            $row++;
        }
        
     // }
      // Setting auto column widths
        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell)
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        
        return $objPHPExcel;
    }
}