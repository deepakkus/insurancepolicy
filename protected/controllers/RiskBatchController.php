<?php

class RiskBatchController extends Controller
{

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
                    'admin',
                    'createFile',
                    'updateFile',
                    'runBatch',
                    'downloadBatch',
                    'batchStats',
                    'runPif'
                ),
				'users'=>array('@'),
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
	public function actionAdmin()
	{
        $model = new RiskBatchFile('search');
        $this->render('admin',array(
            'model' => $model
        ));
	}

	/**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'admin' page.
     */
	public function actionCreateFile()
	{
		$model=new RiskBatchFile;

		if (isset($_POST['RiskBatchFile']))
		{
			$model->attributes = $_POST['RiskBatchFile'];
            $model->type = 'csv';
            $model->status = 'uploaded';
            $model->version_id = RiskVersion::getLiveVersionID();
			if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Batch File: ' . $model->id . ' Created Successfully!');
				$this->redirect(array('/riskBatch/admin'));
            }
		}

        $this->render('create',array(
            'model'=>$model,
        ));
	}

    /**
     * Select which client to run PIF for
     */
    public function actionRunPif()
	{
        $model = new RiskBatchFile();

        if (isset($_POST['RiskBatchFile'])){
            $model->attributes = $_POST['RiskBatchFile'];
            $model->type = 'pif';
            $model->status = 'uploaded';
			if ($model->save())
            {
                $model->importPif();
                $model->runRisk();
                Yii::app()->user->setFlash('success', 'Batch File: ' . $model->id . ' Created Successfully! Risk will start processing.');
				$this->redirect(array('/riskBatch/admin'));
            }
        }

        $this->render('runPif', array('model'=>$model));
    }

	/**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id
     */
	public function actionUpdateFile($id)
	{
        $model = RiskBatchFile::model()->findByPk($id);

        if (isset($_POST['RiskBatchFile']))
		{
			$model->attributes = $_POST['RiskBatchFile'];
			if ($model->save())
			{
				Yii::app()->user->setFlash('success', 'Batch Entry ' . $model->id . ' Updated Successfully!');
				$this->redirect(array('admin'));
			}
		}

		$this->render('update', array('model'=>$model));
	}

    /**
     * Maps fields of imported csv file and runs the bulk risk run
     * @param integer $id
     */
    public function actionRunBatch($id)
	{
        $model = RiskBatchFile::model()->findByPk($id);

        $drive_path = Helper::getDataStorePath();
        $file_path = $drive_path . 'import_risk' . DIRECTORY_SEPARATOR . $model->file_name;

        // Is file readable.
        // This feels very hacky, but the is_readable() and is_writable() functions always return false, even when true
        $isError = false;

		if (file_exists($file_path))
		{
            try
            {
                $fh = fopen($file_path, 'r');
                if (!$fh)
                {
                    throw new Exception("Could not open the file!");
                }
            }
            catch (Exception $e)
            {
                $isError = true;
            }
		}
		else
		{
			$isError = true;
		}

        if ($isError === true)
        {
            Yii::app()->user->setFlash('error', 'Could not read "' . $file_path . '"');
            $this->redirect(array('/riskBatch/admin'));
        }

        // Field mapping submitted
        if (isset($_POST['FieldMap']))
        {
            //Import the csv into our system
            $rows = $model->importCSV($file_path);

            //Now run the risk on all properties
            $model->runRisk();

            Yii::app()->user->setFlash('success', 'Imported ' . strval($rows - 1) . ' entries. Now starting risk analysis.');

            $this->redirect(array('/riskBatch/admin'));
        }
        else
        {
            $fh = fopen($file_path, 'r');
            $headerFields = array_map('strtolower', array_map('trim', fgetcsv($fh)));
            fclose($fh);

            $this->render('fieldMap', array(
                'model' => $model,
                'headerFields' => $headerFields
            ));
        }
	}

    /**
     * Summary of actionDownloadBatch: Selects entries for the given batch, writes them to CSV than forces download
     * @param integer $id
     * @param integer $client_id
     */
    public function actionDownloadBatch($id, $client_id)
	{
        //Set the local variables for the script
        $client = NULL;
        if($client_id !=  NULL)
        {
            $client = Client::model()->findByPk($client_id);
        }
        $csvFolderPath = $client ? Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . $client->name:'Risk-Bulk-Download';
        $zipPath = $csvFolderPath . '.zip';
        $zipFileName = $client ? $client->name . '.zip' : 'Risk-Bulk-Download.zip';

        //Create zip and folder to put the csv into
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        if (!file_exists($csvFolderPath))
        {
            mkdir($csvFolderPath);
        }
        //Counter to keep track of the offset
        $offset = 0;
        $limit = 10000;

        //Get data
        $result = self::getRiskBatch($id, $offset, $limit);
        // Write the file to system
        $filePath = $csvFolderPath . DIRECTORY_SEPARATOR . "wds_risk_batch.csv";
        if(!empty($result))
        {
            $headers = array_keys($result[0]);
        }
        $headers[] = 'standard_deviation_explination';
        $out = fopen($filePath, 'w');
        fputcsv($out, $headers);

        //Loop through chunks of data and write to csv - don't want to select and write all at once, as there could be 100's of thousands (or a million)
        if(!empty($result))
        {
            while($result){

            if($out == null){
                $out = fopen($filePath, 'a');
            }

            foreach($result as $row)
            {
                $row['standard_deviation_explination'] = RiskScore::getStandardDevText($row['state_mean'], $row['std_dev'], $row['score_wds']);
                fputcsv($out, array_values($row));
            }

            //Close file and incriment offset
            fclose($out);
            $out = null;
            $offset +=$limit;

            //Get more data
            $result = self::getRiskBatch($id, $offset, $limit);

        }
        }
        else{
            if($out == null){
                $out = fopen($filePath, 'a');
            }
                fputcsv($out, array("No data found"));
                fclose($out);
            $out = null;
        }

        //Add to zip file
        $zip->addFile($filePath, "wds_risk_batch.csv");
        $zip->close();

        // Download the file
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename='$zipFileName'");
        readfile($zipPath);

        //Cleanup files
        unlink($filePath);
        //Folder
        rmdir($csvFolderPath);
        //Zip file
        unlink($zipPath);

	}

    /**
     * Render the page with stats on a given batch
     * @param float $batchFileID
     * @param float $clientID
     */
    public function actionBatchStats($batchFileID, $clientID)
    {
        $this->render('batchStats',array(
            'results'=>RiskScore::getRiskBatchScoreAnalytics($batchFileID, $clientID),
            'states'=>RiskScore::getRiskBatchStateAnalytics($batchFileID, $clientID)
        ));
    }

    /**
     * Summary of getStandardDevText: Displays more human readable text as to whe wds_score means in relation to the the standard dev. and state mean
     * @param int $batchID
     * @param int $offset
     * @param int $limit
     */
    private static function getRiskBatch($batchID, $offset, $limit)
    {

        $sql = "SELECT
                    rs.id,
	                rs.address,
	                rs.city,
	                rs.state,
	                rs.zip,
	                rs.match_address,
	                rs.lat,
	                rs.long,
	                rs.score_v,
	                rs.score_whp,
	                rs.score_wds,
	                p.policy,
	                m.member_num,
	                sm.mean as state_mean,
	                sm.std_dev
                FROM
	                risk_score rs
                LEFT OUTER JOIN
	                properties p ON rs.property_pid = p.pid
                LEFT OUTER JOIN
	                members m ON p.member_mid = m.mid
                INNER JOIN
	                geog_states s ON rs.state = s.abbr
                LEFT JOIN
	                risk_state_means sm ON sm.state_id = s.id
                WHERE
	                rs.batch_file_id = :batch_id
	                AND rs.wds_geocode_level = 'address'
                ORDER BY rs.state ASC, rs.city ASC, rs.address ASC, rs.id ASC
                OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':batch_id', $batchID, PDO::PARAM_INT)
            ->bindParam(':offset', $offset, PDO::PARAM_INT)
            ->bindParam(':limit', $limit, PDO::PARAM_INT)
            ->queryAll();
    }

}

