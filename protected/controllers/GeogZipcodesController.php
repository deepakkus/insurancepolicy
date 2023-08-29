<?php

class GeogZipcodesController extends Controller
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
                        'apiGetNoticeGeoJson'
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
                ),
				'users'=>array('@'),
			),
            array('allow',
				'actions'=>array(
                    'apiGetNoticeGeoJson'
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
		$model=GeogZipcodes::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
    
    //-------------------------------------------------------------------API Calls----------------------------------------------------------------
    #region API
    
    /**
     * Retrieves the geojson zipcode object for a given fire perimeter.
     * @param integer $perimeterID the ID of the perimeter to get zipcodes for
     */
    public function actionApiGetNoticeGeoJson() 
    {
        $data = NULL;
        $returnArray = array();
        
        if (!WDSAPI::getInputDataArray($data, array('perimeterID')))
            return;   
        
        $result = GeogZipcodes::model()->getNoticeGeoJson($data['perimeterID']);
        
        if(!empty($result))
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
    
    #endregion
}
