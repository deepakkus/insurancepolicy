<?php

class AppSettingController extends Controller
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
			'postOnly + delete',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_FIRESHIELD => array(
                        'apiGetSettings'
                    ),
                     WDSAPI::SCOPE_ENGINE => array(
                        'apiGetSettings'
                    )
                )
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
			array('allow',
				'actions'=>array(
                    'apiGetSettings'
                ),
				'users'=>array('*'),
            ),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    public function actionApiGetSettings()
    {
		$data = NULL;
		
		if (!WDSAPI::getInputDataArray($data, array()))
			return;
        
        //figure out client_id by login_token
        if(isset($data['loginToken']))
        {
            $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");
            if (!isset($fsUser))
            {
                return WDSAPI::echoJsonError('ERROR: App User Not Found.', 'Could not find app user based on provided loginToken.', 1);
            }
            else
            {
                if(isset($fsUser->member->client_id))
                    $client_id = $fsUser->member->client_id;
                elseif(isset($fsUser->agent->client_id))
                    $client_id = $fsUser->agent->client_id;
                else
                    return WDSAPI::echoJsonError('ERROR: App User Client Not Found.', 'Could not find a client for the app user that was found based on the loginToken.', 1);
            }
        }
        else
        {
            $client_id = '0';  //settings with All selected for client will be returned by default
        }

        $filters = array();

        if(isset($data['appContext'])) //filter results by app_context
            $filters['application_context'] = $data['appContext'];

        if(isset($data['name'])) //filter results by name
            $filters['name'] = $data['name'];

        if(!empty($filters))
        {
            $appSettings = AppSetting::model()->findAllByAttributes($filters);
        }
        else
            $appSettings = AppSetting::model()->findAll();

        //Do client_id filtering
        $settings = array();
        foreach($appSettings as $appSetting)
        {
            if(in_array($client_id, $appSetting->getSelectedClients()) || in_array('0',$appSetting->getSelectedClients())) //if its either All or the fs_users client
            {
                $settings[] = array(
                    'applicationContext'=>$appSetting->application_context,
                    'platformContext'=>$appSetting->platform_context,
                    'name'=>$appSetting->name,
                    'dataType'=>$appSetting->data_type,
                    'value'=>$appSetting->value,
                    'effDate'=>$appSetting->effective_date,
                    'expDate'=>$appSetting->expiration_date,
                    'minRes'=>$appSetting->minimum_resolution,
                );
            }
        }
        
		$returnArray = array();
        $returnArray['error'] = 0; // success
        $returnArray['data'] = array('settings'=>$settings);
        
		WDSAPI::echoResultsAsJson($returnArray);
        
    }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new AppSetting;

		if(isset($_POST['AppSetting']))
		{
			$model->attributes=$_POST['AppSetting'];
			if($model->save())
				$this->redirect(array('admin',));
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

		if(isset($_POST['AppSetting']))
		{
			$model->attributes=$_POST['AppSetting'];
			if($model->save())
				$this->redirect(array('admin',));
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
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new AppSetting('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['AppSetting']))
			$model->attributes=$_GET['AppSetting'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return AppSetting the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=AppSetting::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param AppSetting $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='app-setting-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
