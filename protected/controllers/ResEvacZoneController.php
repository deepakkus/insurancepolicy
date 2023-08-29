<?php

class ResEvacZoneController extends Controller
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
                    'deleteNoticeEvacZones',
                    'getClientFires',
                    'getNotices',
                    'getPerimeterIds'
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
     * Manages all models
     */
	public function actionAdmin()
	{
        $notice = new ResNotice('search');
		$notice->unsetAttributes();
		if(isset($_GET['ResNotice']))
			$notice->attributes = $_GET['ResNotice'];

		$this->render('admin', array(
			'notice' => $notice,
		));
	}

	/**
     * Creates a new set of evaczones for a notice based on posted data.
     * If creation is successful, the browser will be redirected to the 'admin' page.
     */
	public function actionCreate($notice_id = null)
	{
        if (Yii::app()->request->isPostRequest)
        {
            $noticeID = Yii::app()->request->getPost('noticeID');
            $evacZones = json_decode(Yii::app()->request->getPost('evacZones'), true);
            foreach($evacZones as $evacZone)
            {
                // Create evac zone
                $newEvacZone = new ResEvacZone();
                $newEvacZone->notice_id = $noticeID;
                $newEvacZone->geog = GIS::convertGeoJsonToWkt(json_encode($evacZone['geog']));
                $newEvacZone->notes = $evacZone['notes'];
                if (!$newEvacZone->save())
                {
                    echo json_encode($newEvacZone->getErrors());
                    return;
                }
            }

            echo json_encode(true);
            return;
        }


        $notice = new ResNotice;
        if(isset($notice_id))
        {
            $notice = ResNotice::model()->with('resEvacZones')->findByPk($notice_id);
        }

		$this->render('create', array('notice'=>$notice));
	}

	/**
     * Updates a set of evac zones for a given notice id.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $notice_id the ID of the notice to update the evac zones for
     */
	public function actionUpdate($notice_id)
	{

		$notice = ResNotice::model()->with('resEvacZones')->findByPk($notice_id);

        if (Yii::app()->request->isPostRequest)
        {
            $noticeID = Yii::app()->request->getPost('noticeID');

            // Delete previous evac zones for the notice
            ResEvacZone::model()->deleteAllByAttributes(array('notice_id' => $noticeID));

            //save new evac zones for notice
            $evacZones = json_decode(Yii::app()->request->getPost('evacZones'), true);
            foreach($evacZones as $evacZone)
            {
                // Create evac zone
                $newEvacZone = new ResEvacZone();
                $newEvacZone->notice_id = $noticeID;
                $newEvacZone->geog = GIS::convertGeoJsonToWkt(json_encode($evacZone['geog']));
                $newEvacZone->notes = $evacZone['notes'];
                if (!$newEvacZone->save())
                {
                    echo json_encode($newEvacZone->getErrors());
                    return;
                }
            }

            echo json_encode(true);
            return;
        }

        // Doing a direct query instead of notice->resEvacZones relation to trigger beforefind fxn that gets the geog records as WKT
        $noticeResEvacZones = ResEvacZone::model()->findAllByAttributes(array('notice_id' => $notice_id));

        $mapEvacZones = array_map(function($mapEvacZone) {
            return array(
                'id' => $mapEvacZone->id,
                'noticeID' => $mapEvacZone->notice_id,
                'geog' => GIS::convertWktToGeoJson($mapEvacZone->geog),
                'notes' => $mapEvacZone->notes
            );
        }, $noticeResEvacZones);

		$this->render('update', array(
			'notice' => $notice,
            'mapEvacZones' => $mapEvacZones,
		));
	}

    public function actionCopy($notice_id)
    {
        $notice = ResNotice::model()->findByPk($notice_id);

		if (isset($_POST['ResNotice']['notice_id']))
		{
            $noticeToCopyZonesTo = ResNotice::model()->findByPk($_POST['ResNotice']['notice_id']);

            // Doing a direct query instead of relation to get the geog records as WKT
            $noticeResEvacZones = ResEvacZone::model()->findAllByAttributes(array('notice_id' => $notice_id));

            foreach ($noticeResEvacZones as $zone)
            {
                $newEvacZone = new ResEvacZone();
                $newEvacZone->notice_id = $noticeToCopyZonesTo->notice_id;
                $newEvacZone->geog = $zone->geog;
                $newEvacZone->notes = $zone->notes;
                $newEvacZone->save();
            }

            Yii::app()->user->setFlash('success', 'Evac Zones successfully copied to ' .$noticeToCopyZonesTo->recommended_action . " - " . date("Y-m-d H:i", strtotime($noticeToCopyZonesTo->date_created)) . ' Notice for ' . $noticeToCopyZonesTo->client_name . ' on ' . $noticeToCopyZonesTo->fire_name);
            $this->redirect(array('admin'));
		}

        $this->render('copy', array(
            'notice' => $notice
        ));
    }

	/**
     * Delete all evac zones associated with a notice
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $notice_id the ID of the notice to delete all the evac zones for
     */
	public function actionDeleteNoticeEvacZones($notice_id)
	{
        ResEvacZone::model()->deleteAllByAttributes(array('notice_id' => $notice_id));

		if (!isset($_GET['ajax']))
        {
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
	}

	/**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ResEvacZone the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model = ResEvacZone::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404,'The requested model does not exist.');
		return $model;
	}

    /**
     * Returns the Evac Zones associated with a give notice
     * @param integer $notice_id the ID of the notice to load associated evac zones for
     * @return ResEvacZone[] the evac zones associated with the notice
     */
	public function loadNoticeEvacZones($notice_id)
	{
		$evacZones = ResEvacZone::model()->findAllByAttributes(array('notice_id'=>$notice_id));
		return $evacZones;
	}

    /**
     * API Method: resEvacZone/apiGetGeoJson
     * Description: Gets the evac zones for the given notice
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

        $result = GIS::getEvacZones($data['noticeID']);

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
                    'notes' => 'Evac Zone ' . $row['notes']
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

    /**
     * Returns html select options for client fires
     * @param integer $clientID
     */
    public function actionGetClientFires($clientID)
    {
        echo CHtml::tag('option', array('value' => false), 'Select a fire', true);
        foreach (ResEvacZone::getClientFires($clientID) as $key => $value)
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
        foreach (ResEvacZone::getClientFireNotices($clientID, $fireID) as $key => $value)
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
}
