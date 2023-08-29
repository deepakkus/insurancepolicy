<?php

class ClientController extends Controller
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
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_FIRESHIELD => array(
                        'apiGetInfo'
                    ),
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetClientInfo'
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
			array('allow',
				'actions'=>array(
                    'index',
                    'stats',
                    'admin',
                    'create',
                    'update',
                    'delete'
                ),
				'users'=>array('@'),
				'expression' => 'in_array("Admin",$user->types)',
 			),
            array('allow',
				'actions'=>array(
                    'index',
                    'stats'
                ),
				'users'=>array('@'),
				'expression' => 'in_array("Manager",$user->types)',
 			),
			array('allow',
				'actions'=>array(
                    'fsWelcomeScreen',
                    'fsConditionHelp',
                    'apiGetInfo',
                    'apiGetClientInfo'
                ),
				'users'=>array('*'),
            ),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Action method for the fsWelcomeScreen view.
	 */
	public function actionFsWelcomeScreen($clientCode)
	{
        $this->layout = '//layouts/FSPlain';

        $this->render('fsWelcomeScreen',array(
			'clientCode' => $clientCode,
		));
	}

    /**
     * Action method for the fsConditionHelp view. This accommodates dynamic question sets help files.
     */
    public function actionFsConditionHelp($clientCode, $conditionID)
    {
        $this->layout = '//layouts/FSPlain';

        $this->render('fsConditionHelp', array(
            'clientCode' => $clientCode,
            'conditionID' => $conditionID,
        ));
    }

    /**
	 * Analytics on clients
	 */
	public function actionIndex()
	{

        $year = date('Y');

        $sql = "
            select
	            c.id,
	            c.name,
	            c.response_program_name,
	            c.response_disclaimer,
	            c.wds_fire,
	            c.wds_risk,
	            c.wds_pro,
	            c.policyholder_label,
	            c.analytics,
	            c.wds_education,
	            c.call_list,
	            c.dedicated,
	            c.unmatched,
	            c.client_call_list,
	            c.enrollment,
	            c.active,
	            u.num_users,
                n1.ptd_notices,
                n2.ytd_notices,
                s.states
            from
	            client c
            left outer join
	        (
		        select
			        count(id) as num_users,
			        client_id
		        from
			        [user]
		        where
			        client_id is not null
			        and active = 1
		        group by
			        client_id
	        ) u on u.client_id = c.id

            left outer join
	        (
		        select
			        count(notice_id) as ptd_notices,
			        client_id
		        from
			        res_notice
		        group by
			        client_id
	        ) n1 on n1.client_id = c.id

            left outer join
	        (
		        select
			        count(notice_id) as ytd_notices,
			        client_id
		        from
			        res_notice
                where
                    date_created >= '$year'
		        group by
			        client_id
	        ) n2 on n2.client_id = c.id

            left outer join
	        (
		        select
			        count(id) as states,
			        client_id
		        from
			        client_states
		        group by
			        client_id
	        ) s on s.client_id = c.id
            order by c.name asc;
	            ";

        $clients = Yii::app()->db->createCommand($sql)->queryAll();
        $this->render('index',array(
            'clients' => $clients
        ));
	}

	 /**
	 * Administration Grid for Clients.
	 */
	public function actionAdmin()
	{
        $clients = new Client('search');
        $clients->unsetAttributes();  // clear any default values

        if(isset($_GET['Client']))
        {
            $clients->attributes = $_GET['Client'];
        }

        //Yii::app()->user->logout();

        $this->render('admin',array(
            'clients' => $clients,
        ));
	}

	    /**
	 * Creates a new Client.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
        $client = new Client;
        $clientStates = array();

        if(isset($_POST['Client']))
        {
            $client->attributes = $_POST['Client'];

			if($client->save())
			{
                if (isset($_POST['ClientStates']))
                    ClientStates::updateClientStates($clientStates, $client);

				Yii::app()->user->setFlash('success', "Client #".$client->id." Created Successfully!");
				$this->redirect(array('admin',));
            }
        }

        //Set some default values
        $client->active = 1;
        $client->policyholder_label = 'policyholder';
        $client->enrolled_label = 'enrolled';
        $client->not_enrolled_label = 'not enrolled';

        $this->render('create',array(
            'client' => $client,
            'clientStates' => $clientStates
        ));
	}

    /**
	 * Analytics on clients
	 */
	public function actionStats($id)
	{

        $sqlUsers = "
            declare @client_id int = :client_id;
            select t.total, a.user_admin, m.user_manager, r.user_risk, d.user_disable from
            (select client_id, count(id) as total from [user] where client_id = @client_id and active = 1 group by client_id) t
            left outer join
            (select count(id) as user_admin, client_id from [user] where client_id = @client_id and type like '%Dash User Admin%' and active = 1 group by client_id) a on a.client_id = t.client_id
            left outer join
            (select count(id) as user_manager , client_id from [user] where client_id = @client_id and type like '%Dash User Manager%' and active = 1 group by client_id) m on m.client_id = t.client_id
            left outer join
            (select count(id) as user_risk , client_id from [user] where client_id = @client_id and type like '%Dash Risk%' and active = 1 group by client_id) r on r.client_id = t.client_id
            left outer join
            (select count(id) as user_disable , client_id from [user] where client_id = @client_id and active != 1 group by client_id) d on d.client_id = t.client_id

        ";

        $sqlRisk = "
            select
	            count(s.id) as total, t.type
            from risk_score s
            inner join
	            risk_score_type t on t.id = s.score_type
            where
	            s.client_id = :client_id
            group by
	            t.type";

        $sqlNotices = "
            select
	            count(notice_id) as total,
	            convert(varchar(4),date_created, 112) as [year]
            from
	            res_notice
            where
	            client_id = :client_id
            group by
	            convert(varchar(4),date_created, 112)
            order by
	            [year] asc
        ";

        $client = Client::model()->findByPk($id);
        $users = Yii::app()->db->createCommand($sqlUsers)->bindParam(':client_id', $id, PDO::PARAM_INT)->queryRow();
        $risk = Yii::app()->db->createCommand($sqlRisk)->bindParam(':client_id', $id, PDO::PARAM_INT)->queryAll();
        $notices = Yii::app()->db->createCommand($sqlNotices)->bindParam(':client_id', $id, PDO::PARAM_INT)->queryAll();

        $this->render('stats',array(
            'client' => $client,
            'users'=>$users,
            'risk'=>$risk,
            'notices' => $notices
        ));
	}

    /**
	 * Deletes a FSUser.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the Client to be deleted
	 */
	public function actionDelete($id)
	{
        if (Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();
            ClientStates::model()->deleteAll('client_id = :client_id', array(':client_id' => $id));

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

    /**
	 * Updates a Client.
	 */
	public function actionUpdate($id)
	{
        $client = $this->loadModel($id);

        $clientStates = ClientStates::model()->findAll(array(
            'select' => 'id, state_id',
            'condition' => 'client_id = :client_id',
            'params' => array(':client_id' => $id)
        ));

        if (isset($_POST['Client']))
        {
            $client->attributes = $_POST['Client'];

			if ($client->save())
			{
                if (isset($_POST['ClientStates']))
                    ClientStates::updateClientStates($clientStates, $client);

				Yii::app()->user->setFlash('success', "Client $id Updated Successfully!");
				$this->redirect(array('admin'));
			}
        }

        $clientAppQuestionSets = new ClientAppQuestionSet('search');
        $clientAppQuestionSets->unsetAttributes();  // clear any default values
        $clientAppQuestionSets->client_id = $client->id;
        if (isset($_GET['ClientAppQuestionSet']))
        {
            $clientAppQuestionSets->attributes = $_GET['ClientAppQuestionSet'];
        }

        $clientQuestions = new FSAssessmentQuestion('search');
        $clientQuestions->unsetAttributes();  // clear any default values
        $clientQuestions->client_id = $client->id;
        if (isset($_GET['FSAssessmentQuestion']))
        {
            $clientQuestions->attributes = $_GET['FSAssessmentQuestion'];
        }

        $this->render('update',array(
            'client' => $client,
            'clientStates' => $clientStates,
            'clientQuestions' => $clientQuestions,
            'clientAppQuestionSets' => $clientAppQuestionSets,
        ));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	private function loadModel($id)
	{
        $client = Client::model()->findByPk($id);
        if ($client === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $client;
	}

    public function actionApiGetInfo()
	{
		$data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('client_id',)))
			return;

		// Lookup client.
		$client = Client::model()->findByPk($data['client_id']);
		if($client === NULL)
		{
			return WDSAPI::echoJsonError('ERROR: Client with that id does not exist.', 'Client with that id does not exist.', 1);
		}
		else //found client
		{
            $return_array = array();
            $return_array['error'] = 0; // success
            $return_array['data'] = array(
                'name'=>$client->name,
                'code'=>$client->code,
                'report_logo_url'=>$client->report_logo_url,
                'response_program_name'=>$client->response_program_name,
                'response_disclaimer'=>$client->response_disclaimer,
            );
            return WDSAPI::echoResultsAsJson($return_array);
        }
	}

    /**
     * API method: client/apiGetClientInfo
     * Description: Gets client information for a given client name or id.  If client ID is given,
     * then that will be used instead of client name.
     *
     * Post data parameters:
     * @param string clientName
     * @param integer clientID ( optional )
     */
    public function actionApiGetClientInfo()
    {
        $data = NULL;
        $returnArray = array();

		if (!WDSAPI::getInputDataArray($data, array('clientName')))
			return;

        $clientID = isset($data['clientID']) ? $data['clientID'] : null;

        $client = null;

        $fields = array(
            'id',
            'name',
            'response_program_name',
            'response_disclaimer',
            'policyholder_label',
            'enrolled_label',
            'not_enrolled_label',
            'map_enrolled_color',
            'map_not_enrolled_color',
            'analytics',
            'logo_id',
            'wds_fire',
            'wds_risk',
            'wds_pro',
            'call_list',
            'client_call_list',
            'dedicated',
            'unmatched',
            'enrollment',
            'api',
            'wds_education'
        );

        if ($clientID)
        {
            $client = Client::model()->findByPk($data['client_id'], array(
                'select' => $fields
            ));
        }
        else
        {
            $client = Client::model()->findByAttributes(array('name' => $data['clientName']), array(
                'select' => $fields
            ));
        }

        if ($client == null)
        {
            return WDSAPI::echoJsonError('ERROR: No client found', 'A client could not be found');
        }

        $returnData = array(
            'client_id' => $client->id,
            'client_name' => $client->name,
            'response_program_name' => !empty($client->response_program_name) ? $client->response_program_name : null,
            'response_disclaimer' => !empty($client->response_disclaimer) ? $client->response_disclaimer : null,
            'policyholder_label' => !empty($client->policyholder_label) ? $client->policyholder_label : 'policyholder',
            'enrolled_label' => !empty($client->enrolled_label) ? $client->enrolled_label : 'enrolled',
            'not_enrolled_label' => !empty($client->not_enrolled_label) ? $client->not_enrolled_label : 'not enrolled',
            'map_enrolled_color' => !empty($client->map_enrolled_color) ? $client->map_enrolled_color : '#3399ff',
            'map_not_enrolled_color' => !empty($client->map_not_enrolled_color) ? $client->map_not_enrolled_color : '#00ccff',
            'analytics' => !empty($client->analytics) ? $client->analytics : null,
            'logo_id' => !empty($client->logo_id) ? $client->logo_id : null,
            'wds_fire' => !empty($client->wds_fire) ? $client->wds_fire : null,
            'wds_risk' => !empty($client->wds_risk) ? $client->wds_risk : null,
            'wds_pro' => !empty($client->wds_pro) ? $client->wds_pro : null,
            'call_list' => !empty($client->call_list) ? $client->call_list : null,
            'client_call_list' => !empty($client->client_call_list) ? $client->client_call_list : null,
            'dedicated' => !empty($client->dedicated) ? $client->dedicated : null,
            'unmatched' => !empty($client->unmatched) ? $client->unmatched : null,
            'enrollment' => !empty($client->enrollment) ? $client->enrollment : null,
            'api' => !empty($client->api) ? $client->api : null,
            'wds_education' => !empty($client->wds_education) ? $client->wds_education : null
        );

        $returnArray['data'] = $returnData;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}

?>