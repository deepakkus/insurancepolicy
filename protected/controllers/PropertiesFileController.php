<?php

class PropertiesFileController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
			'postOnly + delete'
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
                    'create',
                    'update',
                    'delete',
                    'download'
                ),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

	/**
     * Attach a file to the given property
     * @param integer $pid
	 */
	public function actionCreate($pid)
	{
        $model = new PropertiesFile;

        if (isset($_POST['PropertiesFile']))
        {
            $model->attributes = $_POST['PropertiesFile'];
            $model->upload = CUploadedFile::getInstance($model, 'upload');

            // Make sure file is uploaded before saving
            if ($model->validate(array('upload')))
            {
                $fileID = File::model()->saveFile($model->upload);

                if ($fileID)
                {
                    $model->property_pid = $pid;
                    $model->file_id = $fileID;
                    if ($model->save())
                    {
                        Yii::app()->user->setFlash('success', 'File saved successfully!');
                        return $this->redirect(array('property/view', 'pid' => $pid, '#' => 'property-files'));
                    }
                }

                Yii::app()->user->setFlash('error', 'Something went wrong uploading the file!');
                return $this->redirect(array('property/view#property-files', 'pid' => $pid, '#' => 'property-files'));
            }
        }

        $this->render('attach_file', array(
            'model' => $model,
            'pid' => $pid
        ));
	}

	/**
	 * Updates a particular model.
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if (isset($_POST['PropertiesFile']))
		{
            $model->attributes = $_POST['PropertiesFile'];
            $model->upload = CUploadedFile::getInstance($model, 'upload');

            if ($model->upload)
            {
                $fileID = File::model()->saveFile($model->upload, $model->file_id);

                if ($fileID)
                {
                    if (!$model->save())
                    {
                        Yii::app()->user->setFlash('error', 'Something went wrong uploading the file!');
                        return $this->redirect(array('property/view', 'pid' => $model->property_pid, '#' => 'property-files'));
                    }

                    Yii::app()->user->setFlash('success', 'File saved successfully!');
                    return $this->redirect(array('property/view', 'pid' => $model->property_pid, '#' => 'property-files'));
                }
            }

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'File comments saved successfully!');
                return $this->redirect(array('property/view', 'pid' => $model->property_pid, '#' => 'property-files'));
            }
		}

		$this->render('attach_file',array(
			'model' => $model,
            'pid' => $model->property_pid
		));
	}

	/**
	 * Deletes a particular model.
	 * @param integer $id
	 */
	public function actionDelete($id)
	{
        $model = $this->loadModel($id);
        File::model()->deleteByPk($model->file_id);
	}

    /**
     * Download a file from the file table
     * @param integer $fileID 
     */
    public function actionDownload($fileID)
    {
        $file = File::model()->findByPk($fileID);

        if (isset($file))
        {
            $data = pack("H*", $file->data);
            header('Content-Type: ' . $file->type);
            header('Content-Length: ' . strlen($data));
            header('Content-disposition: attachment; filename=' . $file->name);
            print $data;
            exit;
        }
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return PropertiesFile the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=PropertiesFile::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
