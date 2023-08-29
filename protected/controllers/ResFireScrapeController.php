<?php

class ResFireScrapeController extends Controller
{
    /**
     * @return array action filters
     */
	public function filters()
	{
		return array(
			'accessControl',
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
                    'index',
                    'viewedChecked',
                    'map'
                ),
				'users' => array('@'),
			),
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
     * Fire Name Grid
     * @return mixed
     */
	public function actionIndex()
	{
        $models = ResFireScrape::model()->findAll();
        
        $dateStamp = count($models) ? date('Y-m-d H:i', strtotime($models[0]->date_created)) : null;

        $this->renderPartial('index', array(
            'models' => $models,
            'dateStamp' => $dateStamp
        ), false, true);
    }
    
    /**
     * Responds to AJAX call of index view and marked that fire as viewed
     */
    public function actionViewedChecked()
    {
        if (Yii::app()->request->isPostRequest)
        {
            $incNum = Yii::app()->request->getPost('incNum');
            $viewed = Yii::app()->request->getPost('viewed');
            
            $viewedBool = filter_var($viewed, FILTER_VALIDATE_BOOLEAN);
            
            $retVal = array();
            
            $model = ResFireScrapeViewed::model()->findByAttributes(array('inc_num' => $incNum));
            
            if ($model)
            {
                $model->viewed = $viewed;
                $model->save();
                
                $retval['error'] = 0;
                $retval['data'] = array(
                    'incNum' => $incNum,
                    'viewed' => $viewedBool
                );
            }
            else
            {
                $retval['error'] = 1;
                $retval['data'] = array(
                    'message' => 'Could not find model'
                );
            }
            
            echo CJSON::encode($retval);
        }
    }
    
    public function actionMap()
    {
        $models = ResFireScrape::model()->findAll();
        
        $dateStamp = count($models) ? date('Y-m-d H:i', strtotime($models[0]->date_created)) : null;
        
        $feature_collection = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
        
        foreach ($models as $model)
        {
            if ($model->point)
            {
                $feature_collection['features'][] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array((float) $model->lon, (float) $model->lat)
                    ),
                    'properties' => array(
                        'popup' => $this->markerPopup($model),
                        'icon' => array(
                            'iconUrl' => $this->markerIconUrl($model),
                            'iconSize' => array(40, 36),
                            'iconAnchor' => array(18, 35),
                            'popupAnchor' => array(0, -20)
                        )
                    )
                );
            }
        }
        
        $this->renderPartial('map', array(
            'feature_collection' => $feature_collection,
            'dateStamp' => $dateStamp
        ), false, true);
    }
    
    private function markerPopup($model)
    {
        return '<table>
            <tr><th align="right">Time &nbsp;- &nbsp;</th><td>' . date('H:i', strtotime($model->acres)) . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('fuels') . ' &nbsp;- &nbsp;</th><td>' . $model->fuels . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('ic') . ' &nbsp;- &nbsp;</th><td>' . $model->ic . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('inc_num') . ' &nbsp;- &nbsp;</th><td>' . $model->inc_num . '</td></tr>
            <tr><th align="right">Lat/Lon &nbsp;- &nbsp;</th><td>' . round($model->lat, 6) . ', ' . round($model->lon, 6) . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('location') . ' &nbsp;- &nbsp;</th><td>' . $model->location . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('name') . ' &nbsp;- &nbsp;</th><td>' . $model->name . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('resources') . ' &nbsp;- &nbsp;</th><td>' . $model->resources . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('type') . ' &nbsp;- &nbsp;</th><td>' . $model->type . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('web_comment') . ' &nbsp;- &nbsp;</th><td>' . $model->web_comment . '</td></tr>
            <tr><th align="right">' . $model->getAttributeLabel('dispatch') . ' &nbsp;- &nbsp;</th><td>' . $model->dispatch . '</td></tr>
        </table>';
    }
    
    private function markerIconUrl($model)
    {
        if ($model->type === 'Prescribed Fire')
        {
            if ($model->viewed) return 'images/firescraper/fire-icon-yellow-viewed.png';
            return 'images/firescraper/fire-icon-yellow.png';
        }
        if ($model->viewed) return 'images/firescraper/fire-icon-red-viewed.png';
        return 'images/firescraper/fire-icon-red.png';
    }
    
    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ResFireName the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=ResFireScrape::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}