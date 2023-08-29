<?php

class ResDedicatedAgencyController extends Controller
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
                    'create',
                    'update',
                    'delete',
                    'search',
                    'downloadAgencyPdfs',
                    'downloadNewAgencyPdf'
                ),
				'users'=>array('@'),
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
    
    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    #region CRUD Controllers
    
	/**
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model=new ResDedicatedAgency('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['ResDedicatedAgency']))
			$model->attributes=$_GET['ResDedicatedAgency'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'admin' page.
	 */
	public function actionCreate()
	{
		$model=new ResDedicatedAgency;

		if(isset($_POST['ResDedicatedAgency']))
		{
			$model->attributes=$_POST['ResDedicatedAgency'];
			if($model->save())
                $this->redirect(array('admin'));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['ResDedicatedAgency']))
		{
			$model->attributes=$_POST['ResDedicatedAgency'];
			if($model->save())
				$this->redirect(array('admin'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}
    
	/**
     * Renders a view to search models by fire or manual coordinates
     */
	public function actionSearch()
	{
        $post = Yii::app()->request->getPost('AgencyVisitSearch');
        
        $searchForm = new stdClass;
        $searchForm->fire_id = null;
        $searchForm->lat = null;
        $searchForm->lon = null;
        
        $agencyVisits = null;
        
        if ($post)
        {
            if ($post['fire'])
            {
                $searchForm->fire_id = $post['fire'];
                
                $fire = ResFireName::model()->findByPk($searchForm->fire_id);
                $searchForm->lat = $fire->Coord_Lat;
                $searchForm->lon = $fire->Coord_Long;
                $agencyVisits = ResDedicatedAgency::model()->findModelsByLocation($searchForm->lat, $searchForm->lon);
            }
            
            else if ($post['lat'] && $post['lon'])
            {
                $searchForm->lat = $post['lat'];
                $searchForm->lon = $post['lon'];
                $agencyVisits = ResDedicatedAgency::model()->findModelsByLocation($searchForm->lat, $searchForm->lon);
            }
        }
        
        $criteria = new CDbCriteria;
        $criteria->select = 't.Fire_ID, t.Name';
        $criteria->join = 'INNER JOIN res_notice ON t.Fire_ID = res_notice.fire_id';
        $criteria->group = 't.Fire_ID, t.Name';
        $criteria->order = 't.Fire_ID DESC';
        
        $noticedFires = ResFireName::model()->findAll($criteria);
        
		$this->render('search', array(
            'searchForm' => $searchForm,
            'noticedFires' => $noticedFires,
            'agencyVisits' => $agencyVisits
        ));
	}
    
    public function actionDownloadAgencyPdfs($ids)
    {
        $criteria = new CDbCriteria;
        $criteria->addInCondition('id', explode(',', $ids));
        $agencyModels = ResDedicatedAgency::model()->findAll($criteria);
        return ResDedicatedAgency::model()->agencyVisitPDFTemplate($agencyModels);
    }
    
    public function actionDownloadNewAgencyPdf()
    {
        return ResDedicatedAgency::model()->agencyVisitPDFTemplate();
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return ResDedicatedAgency the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=ResDedicatedAgency::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
    
    #endregion
}
