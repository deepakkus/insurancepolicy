<?php

class SystemSettingsController extends Controller
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
                    'update'
                ),
				'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types)'
			),
			array('allow',
				'actions' => array(
                    'announcements'
                ),
				'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types) || in_array("Manager", $user->types)'
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
	 * Updates a particular model.
	 */
	public function actionUpdate()
	{
		$model = $this->loadModel(1);

		if (isset($_POST['SystemSettings']))
		{
			$model->attributes = $_POST['SystemSettings'];
			if ($model->save())
				Yii::app()->user->setFlash('success','System Settings Updated.');
		}

		$this->render('update',array(
			'model' => $model
		));
	}

    /**
     * Updates system announcements
     */
    public function actionAnnouncements()
    {
		$model = $this->loadModel(1);

		if (isset($_POST['SystemSettings']))
		{
			$model->attributes = $_POST['SystemSettings'];
			if ($model->save())
				$this->redirect(array('site/index'));
		}

		$this->render('announcements',array(
			'model' => $model
		));
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SystemSettings the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = SystemSettings::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
