<?php

class AllianceController extends Controller
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
                ),
                'users'=>array('@'),
            ),
			array('allow', // Do Not allow 'Engine View' user type to access these actions
				'actions'=>array(
                    'update',
                    'delete'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Engine",$user->types) || in_array("Engine Manager",$user->types)'
			),
			array('allow', // Only allow 'Engine Manager' user type to access these actions
				'actions'=>array(
                    'create'
                ),
				'users'=>array('@'),
                'expression' => 'in_array("Engine Manager",$user->types)'
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
     * @return Alliance the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=Alliance::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
     * Performs the AJAX validation.
     * @param Alliance $model the model to be validated
     */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='alliance-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
    
    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    #region CRUD controllers

	/**
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model=new Alliance('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Alliance']))
			$model->attributes=$_GET['Alliance'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Alliance;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Alliance']))
		{
			$model->attributes=$_POST['Alliance'];
			if($model->save())
				$this->redirect(array('admin'));
		}

        //Default to active
        $model->active = 1;
        //Render view
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
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Alliance']))
		{
			$model->attributes=$_POST['Alliance'];
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
    
    #endregion
}
