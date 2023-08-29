<?php

class ClientAppQuestionSetController extends Controller
{
	/**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
	public $layout='//layouts/column2';

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

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
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
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array(
                    'create',
                    'update',
                    'admin',
                    'delete'
                ),
				'users'=>array('@'),
				'expression' => 'in_array("Admin",$user->types)',
 			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
     * Creates a new model.
     * If creation is successful, the browser will be redirected back to admin (gridview) page.
     */
	public function actionCreate($client_id)
	{
		$clientAppQuestionSet = new ClientAppQuestionSet;

        if(isset($_POST['ClientAppQuestionSet']))
        {
            $clientAppQuestionSet->attributes = $_POST['ClientAppQuestionSet'];
			if($clientAppQuestionSet->save())
			{
				Yii::app()->user->setFlash('success', "Client App Question Set Created Successfully!");
				$this->redirect(array('client/update', 'id'=>$clientAppQuestionSet->client_id));
            }
        }
		else {
			$clientAppQuestionSet->client_id = $client_id;
		}

        $this->render('create',array(
            'clientAppQuestionSet' => $clientAppQuestionSet,
        ));
	}

	/**
     * Updates a particular model.
     * If update is successful, the browser will be redirected back to client edit page.
     * @param integer $id the ID of the model to be updated
     */
	public function actionUpdate($id)
	{
		$clientAppQuestionSet = $this->loadModel($id);

        if (isset($_POST['ClientAppQuestionSet']))
        {
            $clientAppQuestionSet->attributes = $_POST['ClientAppQuestionSet'];
			if ($clientAppQuestionSet->save())
			{
				Yii::app()->user->setFlash('success', "Question Updated Successfully!");
				$this->redirect(array('client/update', 'id'=>$clientAppQuestionSet->client_id));
			}
        }

        $this->render('update',array(
            'clientAppQuestionSet' => $clientAppQuestionSet,
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
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model = new ClientAppQuestionSet('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['ClientAppQuestionSet']))
			$model->attributes=$_GET['ClientAppQuestionSet'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ClientAppQuestionSet the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model = ClientAppQuestionSet::model()->findByPk($id);
		if($model === null)
			throw new CHttpException(404,'The requested model does not exist.');
		return $model;
	}

	/**
     * Performs the AJAX validation.
     * @param ClientAppQuestionSet $model the model to be validated
     */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='client-app-question-set-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
