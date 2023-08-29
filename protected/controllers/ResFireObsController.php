<?php

class ResFireObsController extends Controller
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
                        'apiGetFireObs',
                        'apiGetFireObsByFireID'
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
                    'getNoaaWeather'
                ),
				'users' => array('@'),
			),
			array('allow',
				'actions' => array(
                    'apiGetFireObs',
                    'apiGetFireObsByFireID'
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
    
    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    
    /**
     * Fire Details Grid
	 */
	public function actionAdmin()
	{
        $model = new ResFireObs('search');
        $model->unsetAttributes();  // clear any default values
        
        if(isset($_GET['ResFireObs']))
        {
            $model->attributes = $_GET['ResFireObs'];
        }

        $this->render('admin',array(
            'model' => $model
        ));
    }
    
    /**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($fireid)
	{
		$model = new ResFireObs;

		if (isset($_POST['ResFireObs']))
		{
			$model->attributes = $_POST['ResFireObs'];
			if ($model->save())
			{
				Yii::app()->user->setFlash('success', "Fire Detail Entry for ".CHtml::encode($model->resFireName->Name)." Created Successfully!");
				$this->redirect(array('admin'));
			}
		}
        
        if (isset(Yii::app()->session['fireSize']) && isset(Yii::app()->session['fireID'])){
            //Get the size which was calculated from the perimeter on the monitor fire process
            $model->Size = (Yii::app()->session['fireID'] == $fireid) ? Yii::app()->session['fireSize'] : null;

            //Clear them out
            unset(Yii::app()->session['fireSize']);
            unset(Yii::app()->session['fireID']);
        }      
        
        $fire = ResFireName::model()->findByPk($fireid);
        
		$this->render('create',array(
			'model' => $model,
            'fire' => $fire
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
		$model = $this->loadModel($id);
        
        //Get Form type from URL
        $source = Yii::app()->request->getQuery('source');
        $fireID = Yii::app()->request->getQuery('fireid'); 
        $fire = ResFireName::model()->findByPk($fireID);
        
        if (isset($_POST['ResFireObs']))
		{
			$model->attributes = $_POST['ResFireObs'];
			if ($model->save())
			{
				Yii::app()->user->setFlash('success', "Fire Detail Entry for ".CHtml::encode($model->resFireName->Name)." Updated Successfully!");
                
                if ($source === 'notice')
                {
                    $noticeurl = Yii::app()->user->getState('noticeurl');
                    Yii::app()->user->setState('noticeurl', null);
                    $this->redirect($noticeurl);
                }
                else
                {
                    $this->redirect(array('admin'));
                }
			}
		}
        
        // If request comes from a notice, save the notice url to a session variable.
        if ($source === 'notice') { Yii::app()->user->setState('noticeurl', Yii::app()->request->urlReferrer); }
        
        //Show form
        $this->render('update',array(
			'model'=>$model,
            'fire'=>$fire
		));
    }
    
    
    /**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return ResFireObs the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=ResFireObs::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
    
    //------------------------------------------------------------------- General Calls----------------------------------------------------------------
    
    /**
     * Method recieves coordinates and returns a JSON response of weather data from NOAA
     * Post/get data parameters:
     * @param float latitude
     * @param float longitude
     * @return JSON array of NOAA weather data
     */   
    public function actionGetNoaaWeather()
    {
        if (Yii::app()->request->isPostRequest)
        {
            $lat = Yii::app()->request->getPost('lat');
            $lon = Yii::app()->request->getPost('lon');
            
            $url = 'http://forecast.weather.gov/MapClick.php?lat=' . $lat . '&lon=' . $lon . '&unit=0&lg=english&FcstType=json';
            
            $content = CurlRequest::getRequest($url);
            $response = CJSON::decode($content,true);
            echo CJSON::encode($response);
        }
    }
    
    //-------------------------------------------------------------API Methods------------------------------------------------------------
    
    /**
     * API Method: resFireObs/apiGetFireObs
     * Description: Gets Fire Obs for a given Obs ID.
     * 
     * Post data parameters:
     * @param int Obs_ID - ID of the Obs
     * 
     * Post data example: 
     * { 
     *     "data": { 
     *         "Obs_ID": 123 
     *     }
     * }
     */    
    public function actionApiGetFireObs() 
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('Obs_ID')))
            return;
        
        $obsID = $data['Obs_ID'];
        
        $resFireObs = ResFireObs::model()->findByPk($obsID);

        if (!isset($resFireObs))
            return WDSAPI::echoJsonError("ERROR: fire obs were not found for ID = $obsID.");
        
        $returnArray['error'] = 0; // success
        $returnArray['data'] = $resFireObs->attributes;

        WDSAPI::echoResultsAsJson($returnArray);
    }
    
    /**
     * API Method: resFireObs/apiGetFireObsByFireID
     * Description: Gets Fire Obs for a given Fire ID.
     * 
     * Post data parameters:
     * @param int Fire_ID - ID of the fire
     * 
     * Post data example: 
     * {
     *     "data": { 
     *         "Fire_ID": 123
     *     }
     * }
     */    
    public function actionApiGetFireObsByFireID() 
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('Fire_ID')))
            return;
        
        $fireID = $data['Fire_ID'];
        
        $criteria = new CDbCriteria();
        $criteria->addCondition('Fire_ID = ' . $fireID);
        $criteria->order = 'Obs_ID DESC';
        
        $resFireObs = ResFireObs::model()->findAll($criteria);

        if (!isset($resFireObs))
            return WDSAPI::echoJsonError("ERROR: fire obs were not found for Fire ID = $fireID.");
        
        $returnData = array();
        
        foreach ($resFireObs as $model)
        {
            $returnData[] = $model->attributes;
        }
        
        $returnArray['error'] = 0; // success
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }    

}