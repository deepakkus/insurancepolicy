<?php

class ResDailyThreatController extends Controller
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
                        'apiGetDailyThreats',
                        'apiGetMostRecentThreat',
                        'apiGetDailyThreat'
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
                    'update',
                    'create',
                    'viewDailyStats'
                ),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetDailyThreats',
                    'apiGetMostRecentThreat',
                    'apiGetDailyThreat'
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
     * @return ResNotice the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=ResDailyThreat::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------

    /**
     * Need to have grid view here...
     */
    public function actionAdmin()
    {
        $model = new ResDailyThreat('search');
        $model->unsetAttributes();

        if (isset($_GET['ResDailyThreat']))
        {
            $model->attributes = $_GET['ResDailyThreat'];
        }

        $this->render('admin',array(
            'model' => $model
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model=new ResDailyThreat();

        if(isset($_POST['ResDailyThreat']))
        {
            $model->attributes=$_POST['ResDailyThreat'];
            if($model->save())
            {
                // Run dailies command
                pclose(popen('start php ' . Yii::app()->basePath . DIRECTORY_SEPARATOR  . 'yiic resdailies', 'r'));

                // Writing gacc map to file for Dash use
                GIS::writeDailyGaccMapToFile();

                Yii::app()->user->setFlash('success', "Daily Threat Entry ".$model->threat_id." Created Successfully!");
                $this->redirect(array('admin'));
            }
        }

        //Show form
        $this->render('create',array(
            'model'=>$model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        //Retreive fire
        $model=$this->loadModel($id);

        if(isset($_POST['ResDailyThreat']))
        {
            $model->attributes=$_POST['ResDailyThreat'];
            if ($model->save())
            {
                // Writing gacc map to file for Dash use
                GIS::writeDailyGaccMapToFile();

                Yii::app()->user->setFlash('success', "Daily Threat Entry ".$model->threat_id." Updated Successfully!");
                $this->redirect(array('admin'));
            }
        }

        //Get Form type from URL
        $type = Yii::app()->request->getQuery('type');

        //Show form
        $this->render('update',array(
            'model'=>$model,
            'type'=>$type,
        ));
    }

    /**
     * Loading top daily stats for the requested model id.  Displayed in
     * partial render for modal popup.
     * @param integer $id
     */
    public function actionViewDailyStats($id)
    {
        $model = $this->loadModel($id);

        // Get each top client's daily stats for requested day
        $models = ResDaily::model()->findAll(array(
            'alias' => 'd',
            'select' => array(
                'd.id',
                'd.monitored',
                'd.fires_triggered',
                'd.fires_responding',
                'd.exposure',
                'd.policy_triggered',
                'd.response_enrolled',
                'd.client_id'
            ),
            'with' => array(
                'client' => array(
                    'select' => array('id','name'),
                    'order' => 'name ASC'
                )
            ),
            'condition' => 'd.id IN (
                SELECT MAX(id)
                FROM res_daily
                WHERE CONVERT(DATE, date_created) = CONVERT(DATE, :date)
                GROUP BY client_id
            )',
            'params' => array(':date' => $model->date_created)
        ));

        $this->renderPartial('_daily_stats', array(
            'models' => $models
        ));
    }

    //-------------------------------------------------------------API Methods------------------------------------------------------------

    /**
     * API Method: resDailyThreat/apiGetDailyThreats
     * Description: Gets daily threats by a given daily entry.
     *
     * Post data parameters:
     * @param int daily_id - ID of the daily
     *
     * Post data example: {"data": {"daily_id": 123}}
     */
    public function actionApiGetDailyThreats()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data))
            return;

        $criteria = new CDbCriteria();

        $dailyID = $data['daily_id'];

        if (!$dailyID)
        {
            $daily = ResDailyThreat::model()->find(array(
               'order' => 'threat_id desc',
               'limit' => 1
            ));

            $returnData = $daily->attributes;
        }
        else
        {
            // First look up the daily entry.
            $daily = ResDaily::model()->findByPk($dailyID);

            if (!isset($daily))
                return WDSAPI::echoJsonError("ERROR: could not find the daily for the given daily ID: $dailyID");

            // Now lookup any threats that exist on the day the daily entry was created.
            $startDate = date('Y-m-d', strtotime($daily->date_created));
            $endDate = date('Y-m-d 23:59', strtotime($daily->date_created));

            $criteria = new CDbCriteria();
            $criteria->addBetweenCondition("date_created", $startDate, $endDate);

            if (!ResDailyThreat::model()->exists($criteria))
                return WDSAPI::echoJsonError("ERROR: could not find the daily for the given daily ID: $dailyID");

            $dailyThreats = ResDailyThreat::model()->find($criteria);

            $returnData = $dailyThreats->attributes;
        }

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * Save a daily threat.
     * @param array $data JSON data input
     * @return array returnArray
     */

    // COMMENTED OUT 12-13-16, remove this in one month from now if no issues have been discovered
    // - Matt

    /*
    private function saveDailyThreat($data, $isNewRecord)
    {
        if ($isNewRecord)
        {
            $threat = new ResDailyThreat();
        }
        else
        {
            if (isset($data['threat_id']))
            {
                $id = $data['threat_id'];

                $threat = ResDailyThreat::model()->findByPk($id);

                if (!isset($threat))
                    return WDSAPI::echoJsonError("ERROR: could not find a daily threat with ID: $id");
            }
            else
            {
                return WDSAPI::echoJsonError("ERROR: id was not provided!");
            }
        }

        try
        {
            foreach ($data as $key => $value)
            {
                $threat[$key] = $value;
            }
        }
        catch (Exception $ex)
        {
            return WDSAPI::echoJsonError('ERROR: ' . $ex->getMessage());
        }

        $now = new DateTime();
        $nowFormatted = $now->format("Y-m-d H:i:s");
        $threat->date_updated = $nowFormatted;

        if ($isNewRecord)
            $threat->date_created = $nowFormatted;

        // Save the daily threat.
        if (!$threat->save())
        {
            $errorMessage = WDSAPI::getFormattedErrors($threat);
            return WDSAPI::echoJsonError("ERROR: Failed to save the daily threat! $errorMessage");
        }

        $returnArray = array();
        $returnArray['error'] = 0; // success

        if ($isNewRecord)
            $returnArray['threat_id'] = $threat->threat_id;

        return $returnArray;
    }
    */

    /**
     * API Method: resDailyThreat/apiGetMostRecentThreat
     * Description: Retreives the most recent daily threat
     *
     *
     * Post data example:
     * { "data": { "threat_id": 123, "southwest": "moderate", ... } }
     */
    public function actionApiGetMostRecentThreat()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'date_created DESC';

        $threat = ResDailyThreat::model()->find($criteria);

        if (!isset($threat))
            return WDSAPI::echoJsonError("ERROR: a daily threat entry was not found.");

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $threat->attributes;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resDailyThreat/apiGetMostRecentThreat
     * Description: Retreives the most recent daily threat
     *
     *
     * Post data example:
     * { "data": { "threat_id": 123, "southwest": "moderate", ... } }
     */
    public function actionApiGetDailyThreat()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('threat_id')))
            return;

        $threat = ResDailyThreat::model()->findByPk($data['threat_id']);

        if (!isset($threat))
            return WDSAPI::echoJsonError("ERROR: a daily threat entry was not found for the requested threat_id.");

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $threat->attributes;

        WDSAPI::echoResultsAsJson($returnArray);
    }
}