<?php

class EngSchedulingClientController extends Controller
{
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
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetDailyEngines'
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
                    'index',
                    'create',
                    'update',
                    'admin',
                    'view',
                    'delete'
                ),
				'users'=>array('@'),
			),
            array('allow',
				'actions' => array(
                    'apiGetDailyEngines'
                ),
				'users' => array('*')
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
     * @return EngSchedulingClient the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=EngSchedulingClient::model()->findByPk($id);
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
		$model=new EngSchedulingClient('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['EngSchedulingClient']))
			$model->attributes=$_GET['EngSchedulingClient'];

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
		$model=new EngSchedulingClient;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['EngSchedulingClient']))
		{
			$model->attributes=$_POST['EngSchedulingClient'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
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

		if(isset($_POST['EngSchedulingClient']))
		{
            if(isset($_POST['EngSchedulingClient']['client_id']) && $_POST['EngSchedulingClient']['client_id'][0]>0){
                foreach($_POST['EngSchedulingClient']['client_id'] as $clientId){
                    $model=$this->loadModel($id);
                    $model->attributes = $_POST['EngSchedulingClient'];
                    $model->client_id = $clientId;
                    $model->save();
                }
				$this->redirect(array('view','id'=>$model->id));
            }

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

    //-------------------------------------------------------------------API Calls----------------------------------------------------------------
    #region API

	/**
     * API Method: property/apiGetDailyEngines
     * Description: Gets all engines assigned to the client for a given day.
     *
     * Post data parameters:
     * @param integer clientID,
     * @param string date
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1,
     *         "date": "2015-06-01"
     *     }
     * }
     */
    public function actionApiGetDailyEngines()
    {
        $data = NULL;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('clientID', 'date')))
            return;

        //Get variables to be used in query
        $clientID = $data['clientID'];
        $date1 = $data['date'];
        $date2 = date('Y-m-d', strtotime('+1 days', strtotime($data['date'])));
        $dedicated = EngScheduling::ENGINE_ASSIGNMENT_DEDICATED;
        $response = EngScheduling::ENGINE_ASSIGNMENT_RESPONSE;
        $onHold = EngScheduling::ENGINE_ASSIGNMENT_ONHOLD;
        $preRisk = EngScheduling::ENGINE_ASSIGNMENT_PRERISK;

        //Was getting too complicated for criteria...select scheduled engines for that day, but if same
        //engine is scheduled twice (switched assignment halfway through day) than only show the most recent
        //assignment
        $sql = "select * from eng_scheduling_client
            where id in (
	            select max(c.id) as id from eng_scheduling_client c
	            inner join eng_scheduling s on c.engine_scheduling_id = s.id
	            where c.client_id = $clientID and s.assignment in ('$dedicated', '$response', '$onHold', '$preRisk')
	            and c.start_date < '$date2' and c.end_date >= '$date1'
	            group by s.engine_id
            )";

        $models = EngSchedulingClient::model()->findAllBySql($sql);

        foreach ($models as $model)
        {
            $returnData[] = array(
                'city'=>(isset($model->engineScheduling)) ? $model->engineScheduling->city : '',
                'state'=>(isset($model->engineScheduling)) ? $model->engineScheduling->state : '',
                'assignment'=>(isset($model->engineScheduling)) ? $model->engineScheduling->assignment : '',
                'comment'=>(isset($model->engineScheduling)) ? $model->engineScheduling->comment : '',
                //'date_updated'=>(isset($model->engineScheduling)) ? $model->engineScheduling->date_updated : '',
            );
        }

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        WDSAPI::echoResultsAsJson($returnArray);

    }

    #endregion API
}
