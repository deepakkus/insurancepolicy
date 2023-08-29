<?php

class ResPerimetersController extends Controller
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
                        'apiGetPerimeterGeoJson',
                        'apiGetPerimeterGeoJsonBuffer',
                        'apiGetThreatGeoJson',
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetPerimeterGeoJson',
                        'apiGetPerimeterGeoJsonBuffer',
                        'apiGetThreatGeoJson',
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
                    'createPerimeter',
                    'createThreat',
                    'updatePerimeter',
                    'updateThreat',
                    'downloadPerimeterKML',
                    'downloadThreatKML',
                    'getPerimeterGeoJson',
                    'getPerimeterGeoJsonBuffer',
                    'getThreatGeoJson',
                    'getZipGeoJson',
                    'downloadDispatchedFireKML',
                    'downloadEngineFireKML',
                    'downloadRecentMonitoredFiresKML'
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array(
                    'apiGetPerimeterGeoJson',
                    'apiGetPerimeterGeoJsonBuffer',
                    'apiGetThreatGeoJson',
                    'getMonitoredFireKMLUpdate'
                ),
                'users'=>array('*'),
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ResPerimeters the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=ResPerimeters::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------

    /**
     * Fire Perimeters Grid
     */
    public function actionAdmin()
    {
        $model = new ResPerimeters('search');
        $model->unsetAttributes();

        if (isset($_GET['ResPerimeters']))
        {
            $model->attributes = $_GET['ResPerimeters'];
        }

        $this->render('admin',array(
            'model' => $model
        ));
    }

    /**
     * Creates new perimeter model.
     * @param integer $fire_id
     */
    public function actionCreatePerimeter($fire_id)
    {
        $model = new ResPerimeters;
        $uploadedFile = CUploadedFile::getInstance($model, 'kmlFileUpload');

        if ($uploadedFile)
        {
            $wkt = $model->getWKTFromUpload($uploadedFile);

            $location = new Location;
            $location->geog = $wkt;
            $location->type = 'perimeter';
            $isLocationSaved = $location->save();

            $model->fire_id = $fire_id;
            $model->perimeter_location_id = $location->getPrimaryKey();

            if ($isLocationSaved && $model->save())
            {
                Yii::app()->user->setFlash('success', 'Perimeter for: '.$model->resFireName->Name.' Created Successfully!');
                $this->redirect(array('/resPerimeters/admin'));
            }
        }

        $this->render('create',array(
            'model'=>$model
        ));
    }

    /**
     * Creates a new threat for given perimeter model
     * @param integer $id perimeter id
     */
    public function actionCreateThreat($id)
    {
        $model = ResPerimeters::model()->findByPk($id);
        $model->scenario = 'threat';

        $referrer = Yii::app()->request->urlReferrer;

        if (strpos($referrer, 'resMonitorLog/viewMonitoredFire') !== false || strpos($referrer, 'resPerimeters/admin') !== false)
        {
            Yii::app()->user->setState('createThreatReturnUrl', Yii::app()->request->urlReferrer);
        }

        if (isset($_POST['ResPerimeters']))
        {
            $model->attributes = $_POST['ResPerimeters'];

            if ($model->validate())
            {
                $uploadedFile = CUploadedFile::getInstance($model, 'kmlFileUpload');

                if ($uploadedFile)
                {
                    // File was uploaded
                    $wkt = $model->getWKTFromUpload($uploadedFile);
                }
                else
                {
                    // A threat to copy was choosen
                    $wkt = Yii::app()->db->createCommand('SELECT geog.STAsText() FROM location WHERE id = :id')->queryScalar(array(':id' => $model->threatIDToCopy));
                }

                $location = new Location;
                $location->geog = $wkt;
                $location->type = 'threat';
                $isLocationSaved = $location->save();

                $model->threat_location_id = $location->getPrimaryKey();

                if ($isLocationSaved && $model->save())
                {
                    $returnUrl = Yii::app()->user->getState('createThreatReturnUrl');
                    Yii::app()->user->setState('createThreatReturnUrl', null);
                    $this->redirect($returnUrl);
                }
            }
        }

        $this->render('create_threat', array(
            'model' => $model
        ));
    }

    /**
     * Updates perimeter model.
     * @param integer $id perimeter id
     */
    public function actionUpdatePerimeter($id)
    {
        $model = $this->loadModel($id);
        $uploadedFile = CUploadedFile::getInstance($model, 'kmlFileUpload');

        if ($uploadedFile)
        {
            $wkt = $model->getWKTFromUpload($uploadedFile);

            $result = Location::model()->updateByPk($model->perimeter_location_id, array(
                'geog' => $wkt
            ));

            if ($result && $model->save())
            {
                Yii::app()->user->setFlash('success', 'Perimeter for: '.$model->resFireName->Name.' Updated Successfully!');
                $this->redirect(array('/resPerimeters/admin'));
            }
        }

        $this->render('update',array(
            'model'=>$model
        ));
    }

    /**
     * Updates threat for perimeter model
     * @param integer $id perimeter id
     */
    public function actionUpdateThreat($id)
    {
        $model = ResPerimeters::model()->findByPk($id);
        $model->scenario = 'threat';

        $referrer = Yii::app()->request->urlReferrer;

        if (strpos($referrer, 'resMonitorLog/viewMonitoredFire') !== false || strpos($referrer, 'resPerimeters/admin') !== false)
        {
            Yii::app()->user->setState('updateThreatReturnUrl', Yii::app()->request->urlReferrer);
        }

        if (isset($_POST['ResPerimeters']))
        {
            $model->attributes = $_POST['ResPerimeters'];

            if ($model->validate())
            {
                $uploadedFile = CUploadedFile::getInstance($model, 'kmlFileUpload');

                if ($uploadedFile)
                {
                    // File was uploaded
                    $wkt = $model->getWKTFromUpload($uploadedFile);
                }
                else
                {
                    // A threat to copy was choosen
                    $wkt = Yii::app()->db->createCommand('SELECT geog.STAsText() FROM location WHERE id = :id')->queryScalar(array(':id' => $model->threatIDToCopy));
                }

                $isLocationSaved = Location::model()->updateByPk($model->threat_location_id, array(
                    'geog' => $wkt
                ));

                if ($isLocationSaved)
                {
                    // Trigger model after save functionality
                    $model->save();

                    $returnUrl = Yii::app()->user->getState('updateThreatReturnUrl');
                    Yii::app()->user->setState('updateThreatReturnUrl', null);
                    $this->redirect($returnUrl);
                }
            }
        }

        $this->render('update_threat', array(
            'model' => $model
        ));
    }

    /**
     * Deletes a particular model.
     * @param integer $id perimeter id
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
        {
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
    }

    /**
     * Downloads kml of the perimeter
     * @param integer $id
     */
    public function actionDownloadPerimeterKML($id)
    {
        $model = $this->loadModel($id);
        $model->wktPerimeter = Yii::app()->db->createCommand('SELECT geog.STAsText() FROM location WHERE id = :id')->queryScalar(array(':id' => $model->perimeter_location_id));
        $model->downloadPerimeterKML();
    }

    /**
     * Downloads kml of the perimeter
     * @param integer $id
     */
    public function actionDownloadThreatKML($id)
    {
        $model = $this->loadModel($id);
        $model->wktThreat = Yii::app()->db->createCommand('SELECT geog.STAsText() FROM location WHERE id = :id')->queryScalar(array(':id' => $model->threat_location_id));
        $model->downloadThreatKML();
    }

    /**
     * Retrieves the geojson object for a given fire perimeter. Used in any internal map that needs to show a perimeter
     * @param integer $perimeterID the ID of the perimeter to be selected
     */
    public function actionGetPerimeterGeoJson($perimeterID)
    {
        echo json_encode(ResPerimeters::getPerimeterGeoJson($perimeterID));
    }

    /**
     * Retrieves the geojson object for a given fire perimeter buffer. Used in any internal map that needs to show a buffer
     * @param integer $perimeterID the ID of the perimeter to be selected and buffered
     * @param integer $fourthRing if there is an alert distance that is greater than 3 miles, than give the miles to draw the outer ring
     */
    public function actionGetPerimeterGeoJsonBuffer($perimeterID, $fourthRing = 0)
    {
        echo json_encode(ResPerimeters::getPerimeterGeoJsonBuffer($perimeterID, $fourthRing));
    }

    /**
     * Retrieves the geojson object for a given threat perimeter. Used in any internal map that needs to show a threat
     * @param integer $perimeterID the ID of the perimeter to be erased out of the threat
     */
    public function actionGetThreatGeoJson($perimeterID)
    {
        echo json_encode(ResPerimeters::getThreatGeoJson($perimeterID));
    }

    /**
     * Downloads KML file of dispatched fires
     */
    public function actionDownloadDispatchedFireKML($id, $name = 'Fire')
    {
        $kmz = new KMZSmokecheck(null, trim($name), $id, 6);
        $kmz->downloadKMZ();
    }

    /**
     * Download fire KMZ by perimeter_id and client array
     * @param integer $id perimeter_id
     * @param integer $fid fire_id
     * @param string[] $cids array of client to display
     */
    public function actionDownloadEngineFireKML($id, $fid, $cids)
    {
        $client_ids = json_decode($cids);

        $kmz = new KMZEngine($id, $client_ids, null, false, $fid);
        $kmz->downloadKMZ();
    }

    /**
     * Downloads a network KML file
     */
    public function actionDownloadRecentMonitoredFiresKML()
    {
        $kml = '<?xml version="1.0" encoding="UTF-8"?>
        <kml xmlns="http://www.opengis.net/kml/2.2">
            <Folder>
            <name>WDS Recent Monitored Fires</name>
            <visibility>0</visibility>
            <open>1</open>
            <NetworkLink>
                <name>Monitored Fires</name>
                <visibility>1</visibility>
                <open>1</open>
                <description>This link updates with WDS monitored fires over the last 3 days</description>
                <refreshVisibility>1</refreshVisibility>
                <flyToView>0</flyToView>

                <!-- Network link will refresh every 1 hour -->
                <Link>
                    <href>' . $this->createAbsoluteUrl('/resPerimeters/getMonitoredFireKMLUpdate') . '</href>
                    <refreshMode>onInterval</refreshMode>
                    <refreshInterval>3600</refreshInterval>
                </Link>

            </NetworkLink>
            </Folder>
        </kml>';

        header('Content-Type: application/vnd.google-earth.kml+xml');
        header('Content-disposition: attachment; filename=WDSMonitoredFires.kml');
        print $kml;
    }

    /**
     * Update monitored fires KML network link
     */
    public function actionGetMonitoredFireKMLUpdate()
    {
        ResPerimeters::model()->downloadMonitoredFireKMLUpdate();
    }

    //-------------------------------------------------------------------API Calls----------------------------------------------------------------

    /**
     * API Method: resPerimeters/apiGetPerimeterGeoJson
     * Description: Get perimeter geojson feature collection for given perimeterID
     *
     * Post data parameters:
     * @param integer $perimeterID
     *
     * Post data example:
     * {
     *      "data": {
     *          "perimeterID": "6547"
     *      }
     * }
     */
    public function actionApiGetPerimeterGeoJson()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('perimeterID')))
            return;

        $result = ResPerimeters::getPerimeterGeoJson($data['perimeterID']);

        if (!empty($result))
        {
            $returnArray['data'] = $result;
            $returnArray['error'] = 0; // success
        }
        else
        {
            $returnArray['error'] = 1; // fail
            $returnArray['data'] = 0;
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resPerimeters/apiGetPerimeterGeoJsonBuffer
     * Description: Get perimeter buffer geojson feature collection for given perimeterID
     *
     * Post data parameters:
     * @param integer $perimeterID
     *
     * Post data example:
     * {
     *      "data": {
     *          "perimeterID": "6547"
     *      }
     * }
     */
    public function actionApiGetPerimeterGeoJsonBuffer()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('perimeterID')))
            return;

        $result = ResPerimeters::getPerimeterGeoJsonBuffer($data['perimeterID']);

        if (!empty($result))
        {
            $returnArray['data'] = $result;
            $returnArray['error'] = 0; // success
        }
        else
        {
            $returnArray['error'] = 1; // fail
            $returnArray['data'] = 0;
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resPerimeters/apiGetThreatGeoJson
     * Description: Get threat geojson feature collection for given perimeterID
     *
     * Post data parameters:
     * @param integer $perimeterID
     *
     * Post data example:
     * {
     *      "data": {
     *          "perimeterID": "6547"
     *      }
     * }
     */
    public function actionApiGetThreatGeoJson()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('perimeterID')))
            return;

        $result = ResPerimeters::getThreatGeoJson($data['perimeterID']);

        if (!empty($result))
        {
            $returnArray['data'] = $result;
            $returnArray['error'] = 0; // success
        }
        else
        {
            $returnArray['error'] = 1; // fail
            $returnArray['data'] = 0;
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }
    /**
     * Retrieves the geojson object for a given perimeter.
     * @param integer $perimeterID the ID of the perimeter 
     */
    public function actionGetZipGeoJson($perimeterID)
    {
        echo json_encode(GeogZipcodes::getNoticeGeoJson($perimeterID));
    }
}
