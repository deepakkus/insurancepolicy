<?php

class RiskStateMeansController extends Controller
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
                        'apiGetStateMean'
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
                    'admin',
                    'create',
                    'update',
                    'delete'
                ),
				'users' => array('@'),
			),
			array('allow',
				'actions' => array(
                    'apiGetStateMean'
                ),
				'users' => array('*')
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
		$model = new RiskStateMeans('search');
		$model->unsetAttributes();

		if (isset($_GET['RiskStateMeans']))
        {
			$model->attributes = $_GET['RiskStateMeans'];
        }

		$this->render('admin', array(
			'model' => $model
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'admin' page.
	 */
	public function actionCreate()
	{
		$model = new RiskStateMeans;

		if (isset($_POST['RiskStateMeans']))
		{
			$model->attributes=$_POST['RiskStateMeans'];
			if ($model->save())
            {
				$this->redirect(array('admin'));
            }
		}

        $model->version_id = RiskVersion::getLiveVersionID();

		$this->render('create', array(
			'model' => $model
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if (isset($_POST['RiskStateMeans']))
		{
			$model->attributes = $_POST['RiskStateMeans'];
			if ($model->save())
            {
				$this->redirect(array('admin'));
            }
		}

		$this->render('update', array(
			'model' => $model
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

		if (!isset($_GET['ajax']))
        {
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return RiskStateMeans the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=RiskStateMeans::model()->findByPk($id);
		if ($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    //-------------------------------------------------------------------API Calls----------------------------------------------------------------

    /**
     * API Method: riskStateMeans/apiGetStateMean
     * Description: Return state mean model for given decimal degrees coordinates.
     *
     * Post data parameters:
     * @param string lat
     * @param string lon
     *
     * Post data example:
     * {
     *     "data": {
     *         "lat" : "40.215412",
     *         "lon" : "-118.54215"
     *     }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "id": "1",
     *         "mean": "0.00064332",
     *         "std_dev": "0.00348408",
     *         "state_id": "5",
     *         "date_created": "2015-12-07 11:16:00.000",
     *         "date_updated": "2016-01-07 12:26:00.000",
     *         "state_abbr": "CA"
     *     },
     *     "error": 0
     * }
     */
    public function actionApiGetStateMean()
    {
        $data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('lat', 'lon')))
			return;

        $return_array = array(
            'data' => array(),
            'error' => 0
        );

        $stateMeanModel = RiskStateMeans::loadModelByLatLong($data['lat'], $data['lon']);

        if ($stateMeanModel)
        {
            $return_array['data'] = array(
                'id' => $stateMeanModel->id,
                'mean' => $stateMeanModel->mean,
                'std_dev' => $stateMeanModel->std_dev,
                'state_id' => $stateMeanModel->state_id,
                'date_created' => $stateMeanModel->date_created,
                'date_updated' => $stateMeanModel->date_updated,
                'state_abbr' => $stateMeanModel->state->abbr
            );
        }
        else
        {
            return WDSAPI::echoJsonError('There was an error.', 'A state mean model could not be found with the current version and these coordiantes: lat = ' . $data['lat'] . ', lon = ' . $data['lon']);
        }

        WDSAPI::echoResultsAsJson($return_array);
    }
}
