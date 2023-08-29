<?php

class UserTrackingPlatformController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl'
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
                    'update'
                ),
                'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types)',
            ),
            array('deny',
                'users' => array('*'),
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
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model = new UserTrackingPlatform('search');
		$model->unsetAttributes();
		if (isset($_GET['UserTrackingPlatform']))
			$model->attributes = $_GET['UserTrackingPlatform'];

		$this->render('admin',array(
			'model' => $model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new UserTrackingPlatform;

		if (isset($_POST['UserTrackingPlatform']))
		{
			$model->attributes = $_POST['UserTrackingPlatform'];
			if($model->save())
				$this->redirect(array('admin'));
		}

		$this->render('create',array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if (isset($_POST['UserTrackingPlatform']))
		{
			$model->attributes = $_POST['UserTrackingPlatform'];
			if ($model->save())
				$this->redirect(array('admin'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return UserTrackingPlatform the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = UserTrackingPlatform::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
