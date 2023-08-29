<?php

class EngCrewManagementController extends Controller
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
                    'viewEngineWebsite'
                ),
                'users'=>array('@'),
            ),
			array('allow', // Do Not allow 'Engine View' user type to access these actions
				'actions'=>array(
                    'update',
                    'delete',
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
     * @return EngCrewManagement the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=EngCrewManagement::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    #region CRUD controllers

	/**
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model=new EngCrewManagement('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['EngCrewManagement']))
			$model->attributes=$_GET['EngCrewManagement'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
     *  @param $userID - int the id of the user in which the crew management is being created for
	 */
	public function actionCreate($userID = null)
	{
        //New model
		$model = new EngCrewManagement;

        //Form has been submitted, so save data
		if(isset($_POST['EngCrewManagement']))
		{
			$model->attributes=$_POST['EngCrewManagement'];
			if($model->save()){
                $this->redirect(array('admin'));
            }
		}

        // Setting defaults in form so database will be checked as 'false' instead of 'null'.
        // Will result in grid showing boolean 'No' instead of blank if not selected in form.
        $model->alliance = false;
        $model->fire_officer = false;

        //If the user is coming from creating a new user, autofill as much of the crew info as you can
        if($userID){
            $user = User::model()->findByPk($userID);
            $userName = explode(" ", $user->name);
            $model->user_id = $user->id;
            $model->first_name = (isset($userName[0])) ? $userName[0] : '';
            $model->last_name = (isset($userName[1])) ? $userName[1] : '';
            $model->email = $user->email;
            //Alliance
            if($user->alliance_id){
                $model->alliance = true;
                $model->alliance_id = $user->alliance_id;
            }
        }

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

		if(isset($_POST['EngCrewManagement']))
		{
			$model->attributes=$_POST['EngCrewManagement'];
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
		$model = $this->loadModel($id);
        $photo_id = $model->photo_id;
        $model->delete();

        $filemodel = File::model()->findByPk($photo_id);
        if ($filemodel)
            $filemodel->delete();

        $this->redirect(array('admin'));

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

    /**
     * Redirect the user to the engines website using auto-login.
     * @param integer $crewID
     */
    public function actionViewEngineWebsite($crewID)
    {
        $url = Yii::app()->params['wdsenginesBaseUrl'] . '/index.php/site/auto-login?' .
            'u=' . Yii::app()->user->getState('username') .
            '&t=' . User::getAutoLoginToken(Yii::app()->user->getState('user_id')) .
            '&cid=' . $crewID;

        $this->redirect($url);
    }

    #endregion
}
