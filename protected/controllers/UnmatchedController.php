<?php

class UnmatchedController extends Controller
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
                        'apiGetUnmatchedList'
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
                    'unmatched',
                    'unmatchedUpdate',
                    'findUnmatched',
                    'downloadUnmatchedList',
                    'downloadAllUnmatchedList'
                ),
				'users'=>array('@'),
			),
			array('allow',
				'actions' => array(
                    'apiGetUnmatchedList'
                ),
				'users' => array('*')
            ),
			array('deny',
				'users'=>array('*'),
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
     * Manages all models.
     */
	public function actionIndex()
	{
        $this->render('index');
	}

	/**
     * View to add or update coordinates for unmatched
     */
    public function actionUnmatched($pid = null)
    {
        $criteria = new CDbCriteria();
		$criteria->with = array('member');
        
        if ($pid)
        {
            $criteria->addCondition('pid = :pid');
            $criteria->params = array(':pid' => $pid);
        }
        else
        {
            $criteria->condition = 'pid is null';
        }
        
        $dataProvider = new CActiveDataProvider('Property', array(
            'sort'=>array(
                'attributes' => array('*'),
            ),
            'criteria' => $criteria,
        ));
        
        $this->render('unmatched', array(
            'dataProvider' => $dataProvider
        ));
    }
    
    public function actionUnmatchedUpdate($pid)
    {
        $model = new CoordinatesForm();
        $lat = '';
        $long = '';
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'unmatched-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST['CoordinatesForm']))
		{
            $model->attributes = $_POST['CoordinatesForm'];
            if ($model->validate())
            {
                $property = Property::model()->findByPk($pid);
                $property->geog = "POINT ({$_POST['CoordinatesForm']['lon']} {$_POST['CoordinatesForm']['lat']})";
                $property->wds_lat = $_POST['CoordinatesForm']['lat'];
                $property->wds_long = $_POST['CoordinatesForm']['lon'];
                $property->wds_geocode_level = Property::GEOCODE_WDS;
                $property->wds_geocoder = null;
                $property->wds_match_address = null;
                $property->wds_match_score = null;
                $property->wds_geocode_date = date('Y-m-d H:i');
                $property->geocoded = true;
                $lat = $property->wds_lat;
                $long = $property->wds_long;
                // Flag this save to be recorded as a transaction in "properties_location_history"
                $property->geocoded = true;

                if ($property->save())
                {
                    Yii::app()->user->setFlash('success', 'Unmatched property for pid ' . $property->pid  . ' has been updated!');
                    return $this->redirect(array('/unmatched/unmatched'));
                }
            }
		}

        $coordinates = Yii::app()->db->createCommand('SELECT wds_lat lat, wds_long lon, geog.Lat , geog.Long FROM properties WHERE pid = :pid')->queryRow(true, array(
            ':pid' => $pid
        ));
        $model->lat = $coordinates['lat'];
        $model->lon = $coordinates['lon'];
        
        $this->render('unmatched_update',array(
            'model' => $model
        ));
    }
    
    /**
     * Get unmatched stats by client from database and returns with the following format:
     * {
     *      22222: "1",
     *      33333: "13",
     *      77777: "7"
     * }
     * @param mixed $clientName 
     */
    public function actionFindUnmatched($clientID)
    {
        $sql = "SELECT COUNT(pid) AS [count], zip 
            FROM properties 
            WHERE wds_geocode_level = 'unmatched' AND client_id = :clientID AND policy_status = 'active'
            GROUP BY zip ORDER BY zip";

        $unmatched = Yii::app()->db->createCommand($sql)
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->queryAll();
        
        $returnArray = array();
        foreach ($unmatched as $data)
            $returnArray[$data['zip']] = $data['count'];
        
        echo json_encode($returnArray);
    }
    
    /**
     * Recieves an array of zipcodes and downloads a list of unmatched in those zips
     * @param string $zipcodes - comma seperated list of zipcodes
     * @param integer $client_id - client id
     */
    public function actionDownloadUnmatchedList($zipcodes, $client_id)
    {
        Yii::import('application.vendors.PHPExcel.*'); 
        
        $zipcodes = array_map('trim', explode(',', $zipcodes));

        $objPHPExcel = $this->createUnmatchedList($zipcodes, $client_id);

        $client = Client::model()->findByPk($client_id)->name;
        
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
     * Recieves an array of zipcodes and downloads a list of All unmatched in those zips
     * @param string $zipcodes - comma seperated list of zipcodes
     * @param integer $monitor_Id - Monitor_ID
     */
    public function actionDownloadAllUnmatchedList($zipcodes, $monitor_Id)
    {
        Yii::import('application.vendors.PHPExcel.*'); 
        $ArrayClient = array();
        $zipcodes = array_map('trim', explode(',', $zipcodes));
        //get all unmatched client_id's
        $model = ResMonitorLog::model()->findByPk($monitor_Id);
        $triggers = $model->resMonitorTriggered;
        foreach($triggers as $trigger)
        {
            if($trigger->unmatched)
            {
                $ArrayClient[] = $trigger->client_id;
            }
        }
        $objPHPExcel = $this->createAllUnmatchedList($zipcodes, $ArrayClient); 

        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="All Unmatched ' . date('Y-m-d H:i') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    /**
     * Creates unmatched list PHPExcel object and passes it back
     * @param array $zipcodes 
     * @param string $clientID
     * @param boolean $staff (optional)
     * @return PHPExcel instance
     */
    private function createUnmatchedList($zipcodes, $clientID, $staff = true)
    {
        Yii::import('application.vendors.PHPExcel.*'); 

        $sql = '';
        $header = array();
        //If internal staff - taylor to staff use case (pid, lat, long, comments
        if($staff)
        {
            $sql = "
            SELECT 
                p.pid,
                m.last_name,
                m.first_name,
                p.address_line_1,
                p.city,
                p.county,
                p.state,
                p.zip,
                p.response_status
            FROM properties p
                INNER JOIN members m ON m.mid = p.member_mid
            WHERE p.wds_geocode_level = 'unmatched' 
                AND m.client_id = :clientID 
                AND p.policy_status = 'active' 
                AND p.type_id = 1
                AND zip IN ('" . implode("','", $zipcodes) . "')
            ORDER BY p.zip";

            // Setting header
            $header = array('PID','Last','First','Address','City','County','State','Zip','Response Status','Latitude', 'Longitude', 'Comments');
        }

        //Client facing - taylor towards client use case = producer number, no pid, lat, long, comment
        else
        {
            $sql = "
            SELECT 
                m.last_name,
                m.first_name,
                p.address_line_1,
                p.city,
                p.county,
                p.state,
                p.zip,
                p.response_status, 
                p.producer,
                p.agency_name,
                p.agency_code,
                p.policy
            FROM properties p
                INNER JOIN members m ON m.mid = p.member_mid
            WHERE p.wds_geocode_level = 'unmatched' 
                AND m.client_id = :clientID 
                AND p.policy_status = 'active' 
                AND p.type_id = 1
                AND zip IN ('" . implode("','", $zipcodes) . "')
            ORDER BY p.zip";

            // Setting header
            $header = array('Last Name','First Name','Address','City','County','State','Zip','Response Status', 'Producer', 'Agency Name', 'Agency Code', 'Policy Number');
        }
        
        $unmatched = Yii::app()->db->createCommand($sql)
            ->bindParam(':clientID', $clientID)
            ->queryAll();
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('Unmatched')
            ->setSubject('Unmatched')
            ->setDescription('Unmatched download from WDSAdmin.')
            ->setKeywords('office PHPExcel php')
            ->setCategory('unmatched file');
        
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);
        
        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Unmatched');
        
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
     * Creates All unmatched list PHPExcel object and passes it back
     * @param array $zipcodes 
     * @param array $ArrayClient 
     * @param boolean $staff (optional)
     * @return PHPExcel instance for all unmatched clients
     */
    private function createAllUnmatchedList($zipcodes, $ArrayClient,$staff = true)
    {

        
        Yii::import('application.vendors.PHPExcel.*'); 
        $row = 2;
        $sql = '';
        $header = array();
        // Setting header
        if($staff){
         $header = array('Client','PID','Last','First','Address','City','County','State','Zip','Comments');
         }
         else{
            // Setting header
            $header = array('Last','First','Address','City','County','State','Zip','Response Status', 'Producer');
        }
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator('Wildfire Defense Systems')
            ->setLastModifiedBy('Wildfire Defense Systems')
            ->setTitle('Unmatched')
            ->setSubject('Unmatched')
            ->setDescription('Unmatched download from WDSAdmin.')
            ->setKeywords('office PHPExcel php')
            ->setCategory('unmatched file');
        
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $objPHPExcel->setActiveSheetIndex(0);
        
        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Unmatched');
        
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
        //If internal staff - taylor to staff use case (pid, lat, long, comments
        foreach($ArrayClient as $clientID)
        {
        if($staff){
            $sql = "
            SELECT 
                c.name,
                p.pid,
                m.last_name,
                m.first_name,
                p.address_line_1,
                p.city,
                p.county,
                p.state,
                p.zip,
                p.comments
            FROM properties p
                INNER JOIN members m ON m.mid = p.member_mid
                INNER JOIN client c ON c.id = p.client_id
            WHERE p.wds_geocode_level = 'unmatched' 
                AND m.client_id = :clientID 
                AND p.policy_status = 'active' 
                AND p.type_id = 1
                AND zip IN ('" . implode("','", $zipcodes) . "')
            ORDER BY p.zip";


        }

        //Client facing - taylor towards client use case = producer number, no pid, lat, long, comment
        else{
            $sql = "
            SELECT 
                m.last_name,
                m.first_name,
                p.address_line_1,
                p.city,
                p.county,
                p.state,
                p.zip,
                p.response_status, 
                p.producer
            FROM properties p
                INNER JOIN members m ON m.mid = p.member_mid
            WHERE p.wds_geocode_level = 'unmatched' 
                AND m.client_id = :clientID 
                AND p.policy_status = 'active' 
                AND p.type_id = 1
                AND zip IN ('" . implode("','", $zipcodes) . "')
            ORDER BY p.zip";


        }
        
        $unmatched = Yii::app()->db->createCommand($sql)
            ->bindParam(':clientID', $clientID)
            ->queryAll();
        
        
        
        // Write Data (this is the time consuming part)
        
        foreach ($unmatched as $result)
        {
            $activeSheet->fromArray($result, null, 'A' . $row);
            $row++;
        }

        }
        // Setting auto column widths
        $cellIterator = $activeSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell)
            $activeSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        
        return $objPHPExcel;
    }
    /**
     * API method: unmatched/apiGetUnmatchedList
     * Description: Gets the most recent notices for a client grouped by fire.
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
     *         "name": "Unmatched List.xlsx",
     *         "type": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         "data": "504b03041400000008006161564885478aade7250000098e010007..............."
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetUnmatchedList()
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('noticeID')))
            return;
        
        $model = ResNotice::model()->findByPk($data['noticeID']);
        $zipcodes = explode(',', $model->zip_codes);
        $clientID = $model->client_id;
        Yii::import('application.vendors.PHPExcel.*');
        
        $objPHPExcel = $this->createUnmatchedList($zipcodes, $clientID, false);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $filepath = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . 'Unmatched List.xlsx';
        $objWriter->save($filepath);
        
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
            $returnArray['data']['name'] = 'Unmatched List.xlsx';
            $returnArray['data']['type'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
        }

        WDSAPI::echoResultsAsJson($returnArray);
        
        if (file_exists($filepath))
            unlink($filepath);
    }
}