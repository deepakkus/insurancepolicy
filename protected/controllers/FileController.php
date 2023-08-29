<?php

class FileController extends Controller
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
                        'apiGetMapLayer',
                        'apiGetAttachment',
                        'apiGetHazardsLayer',
                        'apiGetSmokeLayer',
                        'apiGetGaccLayer',
                        'apiGetFileById',
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetAttachment',
                        'apiUpdate',
                        'apiGetFileById'
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
                    'loadFile',
                    'loadThumbnail',
                    'downloadFile',
                    'edit',
                    'downloadMou'
                ),
				'users'=>array('@'),
			),
            array('allow',
				'actions'=>array(
                    'loadFileToken',
                    'apiGetMapLayer',
                    'apiGetAttachment',
                    'apiGetHazardsLayer',
                    'apiGetSmokeLayer',
                    'apiGetGaccLayer',
                    'apiUpdate'
                ),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
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
     * This method loads a file from the db
     */
    public function actionLoadFile()
	{
		/* example: <img src="<?php echo Yii::app()->request->baseUrl.'/index.php?r=file/loadFile&id=123' ?>" /> */
        /* example: CHtml::image($this->createUrl('/file/loadFile', array('id'=>$data->file_id)) */

        if (isset($_GET['id']))
        {
            $id = $_GET['id'];

		    $file = File::model()->findByPk($id);
		    if (isset($file))
            {
                if($file->type == 'image/jpg')
                {
                    $file->type = 'image/jpeg';
                }
			    header('Content-Type: '.$file->type);
			    print pack("H*", $file->data);
                exit();
		    }
		    else
			    echo  'not found';
        }
	}

    /**
     * This method loads a thumbnail image from the db
     */
    public function actionLoadThumbnail()
	{
        /* example: CHtml::image($this->createUrl('/file/loadThumbnail', array('id'=>$data->file_id)) */

        if (isset($_GET['id']))
        {
            $id = $_GET['id'];

		    $file = File::model()->findByPk($id);
		    if (isset($file))
            {
                if($file->type == 'image/jpg')
                {
                    $file->type = 'image/jpeg';
                }
			    header('Content-Type: '.$file->type);
			    print pack("H*", $file->data_thumb);
                exit();
		    }
		    else
			    echo  'not found';
        }
	}

    public function actionDownloadFile()
    {
        if ($_GET['id'])
        {
            $id = $_GET['id'];
		    $file = File::model()->findByPk($id);
		    if (isset($file))
            {
                header("Content-type: $file->type");
                header("Content-disposition: attachment; filename=$file->name");
			    print pack("H*", $file->data);
                exit();
		    }

            return $this->redirect(Yii::app()->request->urlReferrer);
        }

        return $this->redirect(Yii::app()->request->urlReferrer);
    }

    public function actionLoadFileToken()
    {
        /* example: <img src="<?php echo Yii::app()->request->baseUrl.'/index.php?r=file/loadFile?id=123&token=WildFireDefenseSystems2014' ?>" /> */

        if (isset($_GET['id']) && isset($_GET['token']))
        {
            $id = $_GET['id'];
            $token = $_GET['token'];

            if($token == 'WildFireDefenseSystems2014')
            {
                $file = File::model()->findByPk($id);
                if (isset($file))
                {
                    header('Content-Type: '.$file->type);
                    print pack("H*", $file->data);
                    exit();
                }
                else
                    echo  'not found';
            }
        }
    }

    /**
     * Edits a photo in WDSAdmin
     * Can accept a file table id or a relative file location
     * Can accept a return url parameter ... or will return to site index
     *
     * Ex request:
     * http://localhost:81/index.php?r=file/edit&id=8819
     * http://localhost:81/index.php?r=file/edit&filepath=images/SAMPLE_PHOTO.jpg
     *
     * Ex request with return URL:
     * http://localhost:81/index.php?r=file/edit&id=8819&url= + Yii::app()->request->requestUri
     *
     * @param integer $id
     * @param string $filepath
     * @param string $url
     */
    public function actionEdit($id = null, $filepath = null, $url = null)
    {
        $imageEditFileName = null;
        $imageEditTmpFilePath = null;
        $imageEditId = null;
        $imageEditFilePath = null;
        $returnUrl = !is_null($url) ? $url : $this->createUrl('/site/index');

        if (!isset($_POST['imageEditTmpFilePath']))
        {
            $imageEditTmpFilePath = Yii::getPathOfAlias('webroot.tmp') . DIRECTORY_SEPARATOR . uniqid(rand());
            $imageEditTmpFilePath = substr($imageEditTmpFilePath, strpos($imageEditTmpFilePath, 'tmp'));
        }

        if (isset($_POST['imageEditFileName'])) $imageEditFileName = $_POST['imageEditFileName'];
        if (isset($_POST['imageEditTmpFilePath'])) $imageEditTmpFilePath = $_POST['imageEditTmpFilePath'];
        if (isset($_POST['imageEditId'])) $imageEditId = $_POST['imageEditId'];
        if (isset($_POST['imageEditFilePath'])) $imageEditFilePath = $_POST['imageEditFilePath'];

        // Create temp file from database
        if ($id !== null && !isset($_POST['imageEditTmpFilePath']))
        {
            $file = File::model()->findByPk($id);

            if (!$file)
                throw new CHttpException(404, 'File not found for id: ' . $id . '!');

            $imageEditTmpFilePath = $imageEditTmpFilePath . '.' . strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
            file_put_contents($imageEditTmpFilePath, pack('H*', $file->data));
            $imageEditFileName = $file->name;
            $imageEditId = $id;
            $imageEditFilePath = null;
        }

        // Create temp file from another file on disk
        if ($filepath !== null && !isset($_POST['imageEditTmpFilePath']))
        {
            if (file_exists($filepath) === false)
                throw new CHttpException(404, 'File not found for path: "' . $filepath . '"!');

            $pathinfo = pathinfo($filepath);
            $imageEditTmpFilePath = $imageEditTmpFilePath . '.' . strtolower($pathinfo['extension']);
            copy($filepath, $imageEditTmpFilePath);
            $imageEditFileName = $pathinfo['basename'];
            $imageEditId = null;
            $imageEditFilePath = $filepath;
        }

        // Resetting image back to original DB/Disk version
        if (isset($_POST['image']['reset']))
        {
            if (file_exists($imageEditTmpFilePath)) unlink($imageEditTmpFilePath);
            echo json_encode(array('error' => false, 'message' => 'Success'));
            return;
        }

        // Save image back to DB/Disk
        if (isset($_POST['image']['save']))
        {
            // Save if DB
            if ($imageEditId)
            {
                $file = File::model()->findByPk($imageEditId);

                // Saving thumbnail, if there is one
                if ($file->data_thumb)
                {
                    $image = new ImageResize($imageEditTmpFilePath);
                    $thumbnailTmpFile = Yii::getPathOfAlias('webroot.tmp') . DIRECTORY_SEPARATOR . uniqid(rand());
                    $thumbnailTmpFile = substr($imageEditTmpFilePath, strpos($imageEditTmpFilePath, 'tmp'));
                    $thumbnailTmpFile = $thumbnailTmpFile . '.' . strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                    if ($image->getSourceWidth() > 200)
                    {
                        $image->resizeToWidth(200);
                        $image->crop(200, 200);
                    }
                    $image->save($thumbnailTmpFile);
                    $fp = fopen($thumbnailTmpFile, 'rb');
                    $thumbnailcontent = fread($fp, filesize($thumbnailTmpFile));
                    $thumbnailcontent = '0x' . unpack('H*hex', $thumbnailcontent)['hex'];
                    fclose($fp);
                    unlink($thumbnailTmpFile);
                    Yii::app()->db->createCommand("UPDATE [file] SET [data_thumb] = $thumbnailcontent WHERE id = $imageEditId")->execute();
                }

                // Saving image data
                $fp = fopen($imageEditTmpFilePath, 'rb');
                $content = fread($fp, filesize($imageEditTmpFilePath));
                $content = '0x' . unpack('H*hex', $content)['hex'];
                fclose($fp);
                Yii::app()->db->createCommand("UPDATE [file] SET [data] = $content WHERE id = $imageEditId")->execute();
            }

            // Save if Disk
            if ($imageEditFilePath)
            {
                copy($imageEditTmpFilePath, $imageEditFilePath);
            }

            if (file_exists($imageEditTmpFilePath)) unlink($imageEditTmpFilePath);
            echo json_encode(array('error' => false, 'message' => 'Success'));
            return;
        }

        // Image editing functions
        if (isset($_POST['image']))
        {
            // Rotation
            if (isset($_POST['image']['rotate']))
            {
                $degrees = intval($_POST['image']['rotate']);
                list($width, $height, $sourcetype) = getimagesize($imageEditTmpFilePath);
                // Perform rotation
                switch ($sourcetype) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($imageEditTmpFilePath);
                        $image = imagerotate($image, $degrees * -1, 0);
                        imagejpeg($image, $imageEditTmpFilePath, 100);
                        imagedestroy($image);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($imageEditTmpFilePath);
                        $image = imagerotate($image, $degrees * -1, 0);
                        imagepng($image, $imageEditTmpFilePath, 0);
                        imagedestroy($image);
                        break;
                    default: break;
                }
                echo json_encode(array('error' => false, 'message' => 'Success'));
                return;
            }
            // Cropping
            if (isset($_POST['image']['crop']))
            {
                $crop = $_POST['image']['crop'];
                list($width, $height, $sourcetype) = getimagesize($imageEditTmpFilePath);
                // Transform values from HTML into scaled value based on image true dimensions
                $cropX = $crop['x'] * ($width / $crop['imageWidth']);
                $cropY = $crop['y'] * ($height / $crop['imageHeight']);
                $cropWidth = $crop['selectionWidth'] * ($width / $crop['imageWidth']);
                $cropHeight = $crop['selectionHeight'] * ($height / $crop['imageHeight']);
                // Perform crop
                switch ($sourcetype) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($imageEditTmpFilePath);
                        $image = imagecrop($image, array('x' => $cropX , 'y' => $cropY, 'width' => $cropWidth, 'height'=> $cropHeight));
                        imagejpeg($image, $imageEditTmpFilePath, 100);
                        imagedestroy($image);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($imageEditTmpFilePath);
                        $image = imagecrop($image, array('x' => $cropX , 'y' => $cropY, 'width' => $cropWidth, 'height'=> $cropHeight));
                        imagepng($image, $imageEditTmpFilePath, 0);
                        imagedestroy($image);
                        break;
                    default: break;
                }
                echo json_encode(array('error' => false, 'message' => 'Success'));
                return;
            }
        }

        $this->render('edit', array(
            'imageEditFileName' => $imageEditFileName,
            'imageEditTmpFilePath' => $imageEditTmpFilePath,
            'imageEditId' => $imageEditId,
            'imageEditFilePath' => $imageEditFilePath,
            'returnUrl' => $returnUrl
        ));
    }

    /**
     * Download Colorado MOU document with given filename
     * @param string $fileName
     */
    public function actionDownloadMou($fileName)
    {
        Yii::app()->request->sendFile($fileName, file_get_contents(Yii::app()->basePath . '\response\mou\\' . $fileName), null, true);
    }

    /**
     * API Method: file/apiUpdate
     * Description: Api Update Image content by id and type
     *
     * Post data parameters:
     * @param int id - id of file to be updated
     * @param string content - main data content
     * @param string thumbContent - thumb data content
     *
     * Post data example:
     * { "data": { "id": 2565, "content": "[DATA_CONTENT_BYTES]", "thumbContent"[THUMB_DATA_CONTENT_BYTES]" } }
     */
    public function actionApiupdate()
    {
        $data = null;
		if (!WDSAPI::getInputDataArray($data, array('id', 'content', 'thumbContent')))
			return;

        Yii::app()->db->createCommand("UPDATE [file] SET [data] = ".$data['content'].", [data_thumb] = ".$data['thumbContent']." WHERE id = ".$data['id'])->execute();

        WDSAPI::echoResultsAsJson(array('error'=>0,'data'=>'success'));
    }

    /**
     * API method: file/apiGetMapLayer
     * Description: Return file text by fileID
     *
     * Post data parameters:
     * @param int fileID - id of the file to return.
     *
     * Post data example:
     * {
     *     "data": {
     *         "fileID": 1,
     *     }
     * }
     */
    public function actionApiGetMapLayer()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('fileID')))
            return;

        $file = File::model()->findByPk($data['fileID']);
		if (isset($file))
        {
			header('Content-Type: '.$file->type);
			$json = pack("H*", $file->data);

            $returnArray['error'] = 0;
            $returnArray['data'] = json_decode($json, true);

            WDSAPI::echoResultsAsJson($returnArray);
		}
    }

    /**
     * API method: file/apiGetAttachment
     * Description: Return file data by fileID
     *
     * Post data parameters:
     * @param int fileID - id of the file to return.
     *
     * Post data example:
     * {
     *     "data": {
     *         "fileID": 1,
     *     }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "name" : "filename.json",
     *         "type": "application/json",
     *         "data": "7b2274797065223a2246656174757265436f6c6c656374696f6e222c226......",
     *         "data_thumb": null
     *     },
     *     "error": 0
     * }
     */
    public function  actionApiGetAttachment()
    {
        $data = null;
        $returnArray = array();

		if (!WDSAPI::getInputDataArray($data, array('fileID')))
			return;

        $file = File::model()->findByPk($data['fileID']);

        if (isset($file))
        {
            $returnArray['error'] = 0;
            $returnArray['data'] = array();
            $returnArray['data']['name'] = $file->name;
            $returnArray['data']['type'] = $file->type;
            $returnArray['data']['data'] = $file->data;
            $returnArray['data']['data_thumb'] =  $file->data_thumb;
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['errorFriendlyMessage'] = "No Attachment Available. If this problem persistes, contact WDS.";
            $returnArray['errorMessage'] = "Could not find an attachment for the given ID";
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: file/apiGetHazardsLayer
     * Description: Retrieve the hazards layer from the downloads folder
     *
     * Post data example:
     * {
     *     "data": {}
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "name" : "hazards.json",
     *         "type": "application/json",
     *         "data": "7b2274797065223a2246656174757265436f6c6c656374696f6e222c226......"
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetHazardsLayer()
    {
        $returnArray = array();

		$filepath = Yii::getPathOfAlias('application.downloads') . DIRECTORY_SEPARATOR . 'hazards.json';

        if (file_exists($filepath))
        {
            $fp = fopen($filepath, 'rb');
            $content = fread($fp, filesize($filepath));
            $content = unpack('H*hex', $content)['hex'];
            fclose($fp);

            $returnArray['error'] = 0;
            $returnArray['data'] = array();
            $returnArray['data']['name'] = 'hazards.json';
            $returnArray['data']['type'] = 'application/json';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['errorFriendlyMessage'] = 'No Attachment Available. If this problem persistes, contact WDS.';
            $returnArray['errorMessage'] = 'The hazards file was not present';
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: file/apiGetSmokeLayer
     * Description: Retrieve the smoke layer from the downloads folder
     *
     * Post data example:
     * {
     *     "data": {}
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "name" : "smoke.json",
     *         "type": "application/json",
     *         "data": "7b2274797065223a2246656174757265436f6c6c656374696f6e222c226......"
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetSmokeLayer()
    {
        $returnArray = array();

		$filepath = Yii::getPathOfAlias('application.downloads') . DIRECTORY_SEPARATOR . 'smoke.json';

        if (file_exists($filepath))
        {
            $fp = fopen($filepath, 'rb');
            $content = fread($fp, filesize($filepath));
            $content = unpack('H*hex', $content)['hex'];
            fclose($fp);

            $returnArray['error'] = 0;
            $returnArray['data'] = array();
            $returnArray['data']['name'] = 'smoke.json';
            $returnArray['data']['type'] = 'application/json';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['errorFriendlyMessage'] = 'No Attachment Available. If this problem persistes, contact WDS.';
            $returnArray['errorMessage'] = 'The smoke file was not present';
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: file/apiGetGaccLayer
     * Description: Retrieve the gacc layer from the downloads folder
     *
     * Post data example:
     * {
     *     "data": {}
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "name" : "gacc_map.json",
     *         "type": "application/json",
     *         "data": "7b2274797065223a2246656174757265436f6c6c656374696f6e222c226......"
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetGaccLayer()
    {
        $returnArray = array();

		$filepath = Yii::getPathOfAlias('application.downloads') . DIRECTORY_SEPARATOR . 'gacc_map.json';

        if (file_exists($filepath))
        {
            $fp = fopen($filepath, 'rb');
            $content = fread($fp, filesize($filepath));
            $content = unpack('H*hex', $content)['hex'];
            fclose($fp);

            $returnArray['error'] = 0;
            $returnArray['data'] = array();
            $returnArray['data']['name'] = 'gacc_map.json';
            $returnArray['data']['type'] = 'application/json';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['errorFriendlyMessage'] = 'No Attachment Available. If this problem persistes, contact WDS.';
            $returnArray['errorMessage'] = 'The gacc file was not present';
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }


}