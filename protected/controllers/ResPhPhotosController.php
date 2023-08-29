<?php

class ResPhPhotosController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
            'postOnly + deletePhoto',
             array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetByVisitId',
                        'apiSave',
                        'apiDelete',
                        'apiUpdate',
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
                    'delete',
                    'saveFile',
                    'saveNotes',
                    'apiSaveNotes'
                ),
				'users' => array('@')
			),
            array('allow',
				'actions' => array(
                    'apiGetByVisitId',
                    'apiSave',
                    'apiDelete',
                    'saveNotes',
                    'apiSaveNotes',
                    'apiUpdate',
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
	 * Creates a ph visit photo model instance
	 * @param integer $id
	 */
    public function actionCreate()
    {

        // No file was uploaded
		if (!CUploadedFile::getInstanceByName('create_file_id'))
		{
			$error = true;
			Yii::app()->user->setFlash('notice', 'Please attach a photo first!');
		}
		//File was uploaded, so try to save
		else
		{
			$newPhoto = new ResPhPhotos;
            $newPhoto->visit_id = $_POST['visit_id'];

			//Saved correctly
            if ($newPhoto->save())
            {
                Yii::app()->user->setFlash('success', 'Photo "' .$newPhoto->file->name . '" successfully uploaded!');
            }
			//No Save
			else
			{
				Yii::app()->user->setFlash('notice', 'Photo was not uploaded!');
			}
		}

		$this->redirect(Yii::app()->request->urlReferrer . '&photoTab=1');

	}

	/**
	 * Deletes a ph visit photo model instance
	 * @param integer $id
	 */
    public function actionDelete($id, $photoID)
    {
        $photomodel = ResPhPhotos::model()->findByPk($photoID);

        if ($photomodel)
        {
            // Remove corresponding image in file table
            $filemodel = File::model()->findByPk($photomodel->file_id);
            if ($filemodel)
			{
                $filemodel->delete();
				Yii::app()->user->setFlash('success', "Photo deleted");
			}
			else
			{
				Yii::app()->user->setFlash('notice', "Could not delete photo");
			}

        }

		$this->redirect(Yii::app()->request->urlReferrer . '&photoTab=1');

    }

    /**
     * API Method: resPhPhotos/apiGetResPhPhotosByVisitId
     * Description: Gets all photos associated with a Policyholder Vist
     *
     * Post data parameters:
     * @param int visitID - ID of the ResPhVisit
     *
     * Post data example:
     * { "data": { "visitID": 2565 } }
     */
    public function actionApiGetByVisitId()
    {
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('visitID')))
           return;

        $returnData = array();
        $resPhPhotos = ResPhPhotos::model()->with('file')->findAllByAttributes(array('visit_id'=>$data['visitID']));
        foreach($resPhPhotos as $resPhPhoto)
        {
            $returnData[] = array(
                'id' => $resPhPhoto->id,
                'visit_id' => $resPhPhoto->visit_id,
                'file_id' => $resPhPhoto->file_id,
                'photoName' => $resPhPhoto->file->name,
                'notes' => $resPhPhoto->notes,
            );
        }

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $returnData));
    }

    /**
     * API Method: resPhPhotos/apiSave
     * Description: Save a new Photo associated to a ResPhVisit
     *
     * Post data parameters:
     * @param int visitID - ID of the ResPhVisit
     * @param string compressedFileData - ZLIB compressed string containg file data, for example: gzcompress(file_get_contents($path));
     * @param string compressedThumbData
     * @param string fileName - name of the image
     * @param string fileType - type of image
     *
     * Post data example:
     * {"data":{"visitID": "3144","compressedFileData": "[COMPESSED-URLENCODED-FILE-DATA]","compressedThumbData": "[COMPRESSED-URLENCODED-THUMB-DATA]","fileName": "a_photo_of_a_phv.jpg","fileType": "image/jpeg"}}
     */
    public function actionApiSave()
    {
        //check for valid input and extract to data array
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('visitID', 'compressedFileData', 'compressedThumbData', 'fileName', 'fileType')))
           return;

        //save temp image files
        $tempFileDataPath = tempnam(sys_get_temp_dir(),'RPP');
        $fileData = gzuncompress(urldecode($data['compressedFileData']));
        file_put_contents($tempFileDataPath, $fileData);

        $tempFileThumbPath = tempnam(sys_get_temp_dir(),'RPP');
        $thumbData = gzuncompress(urldecode($data['compressedThumbData']));
        file_put_contents($tempFileThumbPath, $thumbData);

        $fp = fopen($tempFileDataPath, 'rb');
        $content = fread($fp, filesize($tempFileDataPath));
        $content_full = '0x'.unpack('H*hex', $content)['hex'];
        fclose($fp);

        $fp = fopen($tempFileThumbPath, 'rb');
        $content = fread($fp, filesize($tempFileThumbPath));
        $content_thumb = '0x'.unpack('H*hex', $content)['hex'];
        fclose($fp);

        $command = Yii::app()->db->createCommand("INSERT INTO [file] ([name], [type], [data], [data_thumb]) VALUES ('".addslashes($data['fileName'])."', '".addslashes($data['fileType'])."', $content_full, $content_thumb)");
        $command->execute();

        $resPhPhoto = new ResPhPhotos();
        $resPhPhoto->visit_id = $data['visitID'];
        $resPhPhoto->file_id = (int)Yii::app()->db->getLastInsertID();
        if($resPhPhoto->save())
        {
            $resPhVisit = ResPhVisit::model()->findByPk($resPhPhoto->visit_id);
            $visitSaveResult = true;
            //If the Visit has been updated from the API (engines site for example) and it is already published, then we need to set it to re-review.
            if($resPhVisit->review_status == 'published')
            {
                $resPhVisit->review_status = 're-review';
                $visitSaveResult = $resPhVisit->save();
            }
            
            if($visitSaveResult)
            {
                return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $resPhPhoto->attributes));
            }
        }

        WDSAPI::echoJsonError('ERROR saving photo');
        return NULL;
    }

    /**
     * API Method: resPhPhotos/apiDelete
     * Description: Delete a ResPhPhoto associated to a ResPhVisit
     *
     * Post data parameters:
     * @param int id - ResPhPhoto id to be deleted
     *
     * Post data example:
     * { "data": { "id": 2565 } }
     */
    public function actionApiDelete()
    {
        $data = null;

        if (!WDSAPI::getInputDataArray($data, array('id')))
           return;

        $resPhPhoto = ResPhPhotos::model()->findByPk($data['id']);
        if ($resPhPhoto)
        {
            // Remove corresponding image in file table
            $fileModel = File::model()->findByPk($resPhPhoto->file_id);
            if ($fileModel)
            {
                if($resPhPhoto->delete() && $fileModel->delete())
                {
                    return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => 'Image Deleted Successfully.'));
                }
                else
                {
                    $errorCode = 202;
                    $errorMessage = "Error deleting fileModel or resPhPhoto model";
                }
            }
            else
            {
                $errorCode = 203;
                $errorMessage = "Error Finding File model.";
            }
        }
        else
        {
            $errorCode = 204;
            $errorMessage = "Error Finding ResPhPhotos model.";
        }

        WDSAPI::echoJsonError('ERROR deleting photo', $errorMessage, $errorCode);
        return NULL;
    }

    /**
     * API Method: resPhPhotos/apiUpdate
     * Description: Update a ResPhPhoto
     *
     * Post data parameters:
     * @param int id - Required - ResPhPhoto id to be updated
     * @param string notes - Optional - ResPhPhoto->notes
     * @param string fileName - Optional - related File->name to update
     * Post data example:
     * { "data": { "id": 2565, "notes": "some updated notes" } }
     */
    public function actionApiUpdate()
    {
        //check for required input and extract to $data array
        $data = null;
        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        //load up model based on id input
        $resPhPhoto = ResPhPhotos::model()->findByPk($data['id']);

        //check for optional notes param to update
        if(isset($data['notes']))
        {
            $resPhPhoto->notes = $data['notes'];
        }

        //save updates
        $errorCode = 0;
        if(!$resPhPhoto->save())
        {
            $errorCode = 220;
            $errorMessage = "Error Saving Updates to ResPhPhoto Model.";
        }

        //check for optional filename param to update in related File model
        if(isset($data['fileName']))
        {
            $file = File::model()->findByPk($resPhPhoto->file_id);
            $file->name = $data['fileName'];
            if(!$file->SaveAttributes(array('name')))
                echo $file->getErrors;
        }

        $resPhVisit = ResPhVisit::model()->findByPk($resPhPhoto->visit_id);
        //If the Visit has been updated from the API (engines site for example) and it is already published, then we need to set it to re-review.
        if($resPhVisit->review_status == 'published')
        {
            $resPhVisit->review_status = 're-review';
            if(!$resPhVisit->save())
            {
                $errorCode = 230;
                $errorMessage = "Error updating Visit to re-review status.";
            }
        }


        //return results
        if($errorCode === 0)
        {
            return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => 'Photo Updated Successfully.'));
        }
        else
        {
            WDSAPI::echoJsonError('ERROR Updating Photo', $errorMessage, $errorCode);
            return NULL;
        }

    }
}
