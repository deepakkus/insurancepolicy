<?php

class ImportFileController extends Controller
{
    const IMPORT_FILES_COLUMNS_TO_SHOW_KEY = 'wds_import_files_columnsToShow';
    const IMPORT_FILES_PAGE_SIZE_KEY = 'wds_import_files_pageSize';
    const IMPORT_FILES_SORT_KEY = 'wds_import_files_sort';

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
			array('allow', // allow
				'actions'=>array(
                    'update',
                    'create'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types)',
			),
            array('allow', // allow
				'actions'=>array(
                    'admin',
                    'downloadFile',
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types) || in_array("Manager",$user->types)',
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    /**
     * Download one of the static files availible for reference
     */
    public function actionDownloadFile($fileName)
    {
        if($fileName === 'pif-template-non-standard.csv')
        {
            $filePath = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'response' . DIRECTORY_SEPARATOR . 'one_off_pif_adds' . DIRECTORY_SEPARATOR . $fileName;
            $fileType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }
        elseif($fileName === 'One-OffLogic.docx')
        {
            $filePath = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'response' . DIRECTORY_SEPARATOR . 'one_off_pif_adds' . DIRECTORY_SEPARATOR . $fileName;
            $fileType = 'text/csv';
        }
        else
        {
            die("ERROR: not a vailid file name.");
        }

		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		header("Cache-Control: public"); // needed for i.e.
		header("Content-Type: ".$fileType);
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length:".filesize($filePath));
		header("Content-Disposition: inline; filename=".basename($filePath));
		readfile($filePath);
		die();
    }

    /**
     * The main Import Files Grid.
     */
	public function actionAdmin()
	{
        $importFiles = new ImportFile('search');
        $importFiles->unsetAttributes();

        if (filter_has_var(INPUT_GET, 'ImportFile'))
        {
            $importFiles->attributes = filter_input(INPUT_GET, 'ImportFile', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }

        $pageSize = 25;

        if (filter_has_var(INPUT_GET, 'pageSize'))
        {
            $pageSize = filter_input(INPUT_GET, 'pageSize');
            $_SESSION[self::IMPORT_FILES_PAGE_SIZE_KEY] = $pageSize;
        }
        elseif (isset($_SESSION[self::IMPORT_FILES_PAGE_SIZE_KEY]))
        {
            $pageSize = $_SESSION[self::IMPORT_FILES_PAGE_SIZE_KEY];
        }

        $sort = 'id.desc';

        if (filter_has_var(INPUT_GET, 'ImportFile_sort'))
        {
            $sort = filter_input(INPUT_GET, 'ImportFile_sort');
            $_SESSION[self::IMPORT_FILES_SORT_KEY] = $sort;
        }
        elseif (isset($_SESSION[self::IMPORT_FILES_SORT_KEY]))
        {
            $sort = $_SESSION[self::IMPORT_FILES_SORT_KEY];
        }

        $this->render('admin',array(
            'importFiles' => $importFiles,
            'pageSize' => $pageSize,
            'sort' => $sort,
        ));
	}

    /**
     * Creates a new ImportFile.
     * If creation is successful, the browser will be redirected back to the 'admin' page.
     * @param boolean $standard Whether this is a standard import file, or a one-off spur of the moment PIF import
     */
	public function actionCreate($standard = true)
	{
		$importFile = new ImportFile;

        if (filter_has_var(INPUT_POST, 'ImportFile'))
        {

            if(!$standard){
                //Move file and record name
                $uploadedFile = CUploadedFile::getInstanceByName('csv');
                $moveTo = Helper::getDataStorePath() . 'import_files' . DIRECTORY_SEPARATOR . $uploadedFile->name;
                //If import_files folder does not exists then create it.
                if (!file_exists(Helper::getDataStorePath() . 'import_files')) {
                    mkdir(Helper::getDataStorePath() . 'import_files', 0777, true);
                }
                //Don't want to over-write files, so only if it doesn't exist
                if(!file_exists($moveTo)){
                    move_uploaded_file($uploadedFile->tempName, $moveTo);
                }
                else{
                    Yii::app()->user->setFlash('error', "Import file with this name already exists. Please rename and upload again");
                    $this->redirect(array('admin'));
                }

                //Set attributes
                $importFile->file_path = $uploadedFile->name;
                $importFile->type = 'Non-standard';
            }

            $importFile->attributes = filter_input(INPUT_POST, 'ImportFile', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
           
            if ($importFile->save())
            {
				Yii::app()->user->setFlash('success', "Import File ".$importFile->id." was created successfully!");
				$this->redirect(array('admin'));
            }
        }

        $this->render('create',array(
            'importFile' => $importFile,
            'standard' => $standard
        ));

	}

    /**
     * Updates a particular ImportFile.
     * @param integer $id the ID of the ImportFile to be updated
     * @param boolean $standard Whether this is a standard import file, or a one-off spur of the moment PIF import
     */
	public function actionUpdate($id)
	{
		$importFile = $this->loadModel($id);

        if (filter_has_var(INPUT_POST, 'ImportFile'))
        {
            $importFile->attributes = filter_input(INPUT_POST, 'ImportFile', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            //Move file and record name
            $uploadedFile = CUploadedFile::getInstanceByName('csv');
            if($uploadedFile){
                $moveTo = Helper::getDataStorePath() . 'import_files' . DIRECTORY_SEPARATOR . $uploadedFile->name;

                //Don't want to over-write files, so only if it doesn't exist
                if(!file_exists($moveTo)){
                    move_uploaded_file($uploadedFile->tempName, $moveTo);
                }
                else{
                    Yii::app()->user->setFlash('error', "Import file with this name already exists. Please rename and upload again");
                    $this->redirect(array('admin'));
                }

                move_uploaded_file($uploadedFile->tempName, $moveTo);

                //Set attributes
                $importFile->file_path = $uploadedFile->name;
            }

            if ($importFile->save())
            {
				Yii::app()->user->setFlash('success', "Import File $id was updated successfully!");
				$this->redirect(array('admin'));
            }
        }

        $standard = ($importFile->type == 'Non-standard') ? false : true;

		$this->render('update',array(
			'importFile' => $importFile,
            'standard' => $standard
		));
	}

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
	private function loadModel($id)
	{
		$model = ImportFile::model()->findByPk($id);

        if ($model === null)
			throw new CHttpException(404,'The requested page does not exist.');

		return $model;
	}

}