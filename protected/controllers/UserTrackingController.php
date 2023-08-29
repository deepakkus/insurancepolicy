<?php

class UserTrackingController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiCreateUserAction'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiCreateUserAction'
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
                'actions' => array(
                    'admin',
                    'view',
                    'viewUser',
                    'viewUserDetails'
                ),
                'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types)',
            ),
			array('allow',
				'actions' => array(
                    'apiCreateUserAction'
                ),
				'users' => array('*')
            ),
            array('deny',
                'users' => array('*'),
            )
        );
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new UserTracking('search');
        $model->unsetAttributes();
        if(isset($_GET['UserTracking']))
            $model->attributes=$_GET['UserTracking'];

        $dataProvider = $model->search();

        $this->render('admin',array(
            'model' => $model,
            'dataProvider' => $dataProvider
        ));
    }

    /**
     * Renders jui modal view for the admin grid
     * @param mixed $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->loadModel($id);

        return $this->renderPartial('_modalView', array(
            'model' => $model
        ));
    }

    /**
     * Renders a view of actions taken by user and form
     * @return mixed
     */
    public function actionViewUser()
    {
        $userTrackingForm = new UserTrackingForm();
        $stats = null;

        if (isset($_POST['UserTrackingForm']))
        {
            $userTrackingForm->attributes = $_POST['UserTrackingForm'];
            $stats = $userTrackingForm->getTrackedUserStats();
        }

        return $this->render('view_user', array(
            'userTrackingForm' => $userTrackingForm,
            'stats' => $stats
        ));
    }

    /**
     * Renders a view of action details by user
     * @return mixed
     */
    public function actionViewUserDetails()
    {
        $userTrackingFormData = null;

        if (isset($_POST['UserTrackingForm']))
        {
            $_SESSION['UserTrackingForm'] = $_POST['UserTrackingForm'];
            $userTrackingFormData = $_POST['UserTrackingForm'];
        }
        else if (isset($_SESSION['UserTrackingForm']))
        {
            $userTrackingFormData = $_SESSION['UserTrackingForm'];
        }

        if (!is_null($userTrackingFormData))
        {
            $userTrackingForm = new UserTrackingForm();
            $userTrackingForm->attributes = $userTrackingFormData;

            $model = new UserTracking('search');
            $model->unsetAttributes();
            if (isset($_GET['UserTracking']))
                $model->attributes = $_GET['UserTracking'];

            $dataProvider = $model->search($userTrackingForm);

            return $this->render('admin', array(
                'model' => $model,
                'dataProvider' => $dataProvider
            ));
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return UserTracking the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=UserTracking::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * API Method: userTracking/apiCreateUserAction
     * Description: Save a user action.
     *
     * Post data parameters:
     * @param integer userID
     * @param integer clientID
     * @param integer fireID
     * @param string ip
     * @param string route
     * @param string data - an escaped json string of GET/POST request vars
     *
     * Post data example:
     * {
     *     "data": {
     *         "userID" : 59,
     *         "clientID": 3,
     *         "fireID": 59,
     *         "ip": "65.121.112.246",
     *         "route": "user\/update",
     *         "data": "{\"get\":{\"id\":\"703\"},\"post\":[]}"
     *     }
     * }
     *
     * @return array
     * {
     *     "data": null,
     *     "error": 0
     * }
     */
    public function actionApiCreateUserAction()
    {
        $data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('userID','clientID','platformID','fireID','ip','route','data')))
			return;

        $logEntry = new UserTracking;
        $logEntry->user_id = $data['userID'];
        $logEntry->client_id = $data['clientID'];
        $logEntry->platform_id = $data['platformID'];
        $logEntry->fire_id = $data['fireID'];
        $logEntry->ip = $data['ip'];
        $logEntry->date = date('Y-m-d H:i');
        $logEntry->route = $data['route'];
        $logEntry->data = $data['data'];

        try
        {
            if (!$logEntry->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $logEntry->getErrors());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        return WDSAPI::echoResultsAsJson(array('data' => null, 'error' => 0));
    }
}
