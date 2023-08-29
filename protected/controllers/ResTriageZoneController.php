<?php

class ResTriageZoneController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
			'postOnly + delete',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetGeoJson'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetGeoJson'
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
				'actions' => array(
                    'admin',
                    'create',
                    'update',
                    'copy',
                    'delete',
                    'getDispatchedFires',
                    'getNotices',
                    'getPerimeterIds',
                    'downloadEngineAttachments'
                ),
				'users' => array('@')
			),
            array('allow',
				'actions'=>array(
                    'apiGetGeoJson'
                ),
				'users'=>array('*')
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
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model = new ResTriageZone('search');
		$model->unsetAttributes();
		if(isset($_GET['ResTriageZone']))
			$model->attributes = $_GET['ResTriageZone'];

		$this->render('admin', array(
			'model' => $model
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'admin' page.
	 */
	public function actionCreate()
	{
        if (Yii::app()->request->isPostRequest)
        {
            $noticeID = Yii::app()->request->getPost('noticeID');
            $triageZoneAreas = Yii::app()->request->getPost('triageZones');
            $triageZoneAreas = json_decode($triageZoneAreas, true);

            // Create triage zone
            $triageZone = new ResTriageZone();
            $triageZone->notice_id = $noticeID;
            if (!$triageZone->save())
            {
                echo json_encode($triageZone->getErrors());
                return;
            }

            // Create zone areas
            foreach ($triageZoneAreas as $area)
            {
                $triageZoneArea = new ResTriageZoneArea();
                $triageZoneArea->triage_zone_id = $triageZone->id;
                $triageZoneArea->geog = GIS::convertGeoJsonToWkt(json_encode($area['geog']));
                $triageZoneArea->notes = $area['notes'];
                if (!$triageZoneArea->save())
                {
                    echo json_encode($triageZoneArea->getErrors());
                    return;
                }
            }

            echo json_encode(true);
            return;
        }

        $triageZone = new ResTriageZone;

		$this->render('create', array(
			'triageZone' => $triageZone,
		));
	}



	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{

		$triageZone = $this->loadModel($id);

        if (Yii::app()->request->isPostRequest)
        {
            $noticeID = Yii::app()->request->getPost('noticeID');
            $triageZoneAreas = Yii::app()->request->getPost('triageZones');
            $triageZoneAreas = json_decode($triageZoneAreas, true);

            // Update triage zone
            $triageZone->notice_id = $noticeID;
            $triageZone->date_updated = date('Y-m-d H:i');
            if (!$triageZone->save())
            {
                echo json_encode($triageZone->getErrors());
                return;
            }

            // Delete previous zone areas
            $triageZonesExist = ResTriageZoneArea::model()->exists('triage_zone_id = :triage_zone_id', array(
                ':triage_zone_id' => $id
            ));

            if ($triageZonesExist)
            {
                // Remove old triage zones
                ResTriageZoneArea::model()->deleteAllByAttributes(array('triage_zone_id' => $id));

                // Clear the old work zone entries out of the policyholders
                Yii::app()->db->createCommand()->update('res_triggered', array('priority' => 'NULL'), 'notice_id = :notice_id', array(
                    ':notice_id' => $noticeID
                ));
            }

            // Add new zone areas
            foreach ($triageZoneAreas as $area)
            {
                $triageZoneArea = new ResTriageZoneArea();
                $triageZoneArea->triage_zone_id = $triageZone->id;
                $triageZoneArea->geog = GIS::convertGeoJsonToWkt(json_encode($area['geog']));
                $triageZoneArea->notes = $area['notes'];
                if (!$triageZoneArea->save())
                {
                    echo json_encode($triageZoneArea->getErrors());
                    return;
                }
            }

            echo json_encode(true);
            return;
        }

        $resTriageZoneAreaModels = ResTriageZoneArea::model()->findAllByAttributes(array('triage_zone_id' => $id));

        // Creating json structure to populate triage areas on map
        $resTriageZoneAreas = array_map(function($model) {
            return array(
                'id' => $model->id,
                'triageZoneId' => $model->triage_zone_id,
                'geog' => GIS::convertWktToGeoJson($model->geog),
                'notes' => $model->notes
            );
        }, $resTriageZoneAreaModels);

		$this->render('update', array(
			'triageZone' => $triageZone,
            'resTriageZoneAreas' => $resTriageZoneAreas
		));
	}

    public function actionCopy($id)
    {
        $model = $this->loadModel($id);

		if (isset($_POST['ajax']) && $_POST['ajax'] === 'res-triage-zone-copy-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST['ResTriageZone']))
		{
			$model->attributes = $_POST['ResTriageZone'];

            $newModel = new ResTriageZone();
            $newModel->notice_id = $model->notice_id;
            if (!$newModel->save())
            {
                Yii::app()->user->setFlash('error', 'Something went wrong making a copy of this zone!');
                $this->redirect(array('admin'));
            }

            // Doing a direct query instead of relation to get the geog records as WKT
            $resTriageZoneAreas = ResTriageZoneArea::model()->findAllByAttributes(array('triage_zone_id' => $model->id));

            foreach ($resTriageZoneAreas as $area)
            {
                $newTriageZoneArea = new ResTriageZoneArea();
                $newTriageZoneArea->triage_zone_id = $newModel->id;
                $newTriageZoneArea->geog = $area->geog;
                $newTriageZoneArea->notes = $area->notes;
                $newTriageZoneArea->save();
            }

            Yii::app()->user->setFlash('success', 'Zones successfully copied for ' . $model->clientName . ' on ' . $model->fireName);
            $this->redirect(array('admin'));
		}

        $this->render('copy', array(
            'model' => $model
        ));
    }

	/**
	 * Delete all assocaited zone area
     * Delete zone
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
        ResTriageZoneArea::model()->deleteAllByAttributes(array('triage_zone_id' => $id));
		$this->loadModel($id)->delete();

		if (!isset($_GET['ajax']))
        {
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return ResTriageZone the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = ResTriageZone::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    /**
     * Returns html select options for dispatched fires
     * @param integer $clientID
     */
    public function actionGetDispatchedFires($clientID)
    {
        echo CHtml::tag('option', array('value' => false), 'Select a fire', true);
        foreach (ResTriageZone::getDispatchedFires($clientID) as $key => $value)
        {
            echo CHtml::tag('option', array('value' => $key), CHtml::encode($value), true);
        }
    }

    /**
     * Returns html select options for notices
     * @param integer $clientID
     * @param integer $fireID
     */
    public function actionGetNotices($clientID, $fireID)
    {
        echo CHtml::tag('option', array('value' => false), 'Select a notice', true);
        foreach (ResTriageZone::getDispatchedNotices($clientID, $fireID) as $key => $value)
        {
            echo CHtml::tag('option', array('value' => $key), CHtml::encode($value), true);
        }
    }

    /**
     * Returns json of information from a given notice
     * @param integer $noticeID
     */
    public function actionGetPerimeterIds($noticeID)
    {
        $notice = ResNotice::model()->findByPk($noticeID);
        echo CJSON::encode(array(
            'perimeterID' => $notice->perimeter_id,
            'clientID' => $notice->client_id
        ));
    }

    /**
     * Downloads a zip folder of the work zone kmz and list for the given fire/client(s)
     * @uses - $_POST data from form on admin - client and fire
     */
    public function actionDownloadEngineAttachments()
    {
        $clientIDs = $_POST['client'];
        $fireID = $_POST['fire'];
        $noticeID = array();
        $folderPath = "";
        $fireName = "";
        $foldersToDelete = array();
        $filesToDelete = array();

        foreach($clientIDs as $clientID)
        {
            //Select max notice for each client
            $sql = "select max(notice_id) as notice_id from res_notice where fire_id = :fire_id and client_id = :client_id";
            $noticeID[] = Yii::app()->db->createCommand($sql)
                ->bindValue(':fire_id', $fireID, PDO::PARAM_INT)
                ->bindValue(':client_id', $clientID, PDO::PARAM_INT)
                ->queryScalar();
        }

        //Select work zones based on notices
        foreach($noticeID as $notice)
        {
            $sql = "select z.*, a.notes from res_triage_zone z
            inner join res_triage_zone_area a on a.triage_zone_id = z.id
            where z.notice_id = :notice_id";

            $workZones = Yii::app()->db->createCommand($sql)
                ->bindValue(':notice_id', $notice, PDO::PARAM_INT)
                ->queryAll();

            if(!empty($workZones))
            {
                foreach($workZones as $zone)
                {
                    $kmz = new KMZWorkZone($zone['notice_id'], $zone['id'], $zone['notes']);
                    $name = $kmz->createKMZ();
                    $kmz->createList();
                    $folderPath = $kmz->baseFolderPath;
                    $fireName = $kmz->fireName;
                    if(!in_array($kmz->folderPath, $foldersToDelete))
                    {
                        $foldersToDelete[] = $kmz->folderPath;
                    }
                }
            }
        }

        if($folderPath)
        {
            $zipPath = $folderPath . '.zip';
            $zipFileName = $fireName . '.zip';

            //Create zip and folder to put the csv into
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE);

            /** @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folderPath),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $name => $file)
            {
                // Skip directories (they would be added automatically)
                if (!$file->isDir())
                {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                    $filesToDelete[] = $filePath;
                }
            }

            $zip->close();

            // Download the file
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename='$zipFileName'");
            readfile($zipPath);

            // Clean up folders and files after zip
            foreach ($filesToDelete as $file)
            {
                unlink($file);
            }

            // Diretories
            foreach ($foldersToDelete as $folder)
            {
                rmdir($folder);
            }

            //Folder
            rmdir($folderPath);
            //Zip file
            unlink($zipPath);
            exit;
        }
        else
        {
            Yii::app()->user->setFlash('error', 'No work zones found - check to make sure they are attached to a notice');
            $this->redirect(array('admin'));
        }

    }

    /**
     * API Method: resTriageZone/apiGetGeoJson
     * Description: Gets the triage zones for the given notice
     *
     * Post data parameters:
     * @param integer noticeID
     *
     * Post data example:
     * {
     *     "data": {
     *         "noticeID": 1245
     *     }
     * }
     */
    public function actionApiGetGeoJson()
	{
        $data = NULL;
        $returnArray = array();

		if (!WDSAPI::getInputDataArray($data, array('noticeID')))
			return;

        $result = GIS::getTriageZones($data['noticeID']);

        $featureCollection = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );

        foreach ($result as $row)
        {
            $featureCollection['features'][] = array(
                'type' => 'Feature',
                'geometry' => CJSON::decode(GIS::convertWktToGeoJson($row['geog'])),
                'properties' => array(
                    'notes' => 'Work Zone ' . $row['notes']
                )
            );
        }

        if (!empty($result))
        {
            $returnArray['data'] = $featureCollection;
            $returnArray['error'] = 0; // success
        }
        else
        {
            $returnArray['error'] = 1; // fail
            $returnArray['data'] = 0;
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }


}
