<?php

class AgentController extends Controller
{
    const AGENT_COLUMNS_TO_SHOW_KEY = 'wds_agent_columnsToShow';
    const AGENT_PAGE_SIZE_KEY = 'wds_agent_pageSize';
    const AGENT_SORT_KEY = 'wds_agent_sort';
    
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
			'accessControl', // perform access control for CRUD operations
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
			array('allow', // allow
				'actions'=>array('admin', 'update', 'create'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    /**
	 * The main agents grid.
	 */
	public function actionAdmin()
	{
        $agents = new Agent('search');
        $agents->unsetAttributes();

        if (filter_has_var(INPUT_GET, 'Agent'))
        {
            $agents->attributes = filter_input(INPUT_GET, 'Agent', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }
        
        // Default columns to show.
        $columnsToShow = array(
            10=>'id', 
            20=>'agent_num', 
            30=>'first_name', 
            40=>'last_name', 
            50=>'fs_carrier_key', 
            60=>'agent_client_name',
            70=>'agent_type'
        );

        if (filter_has_var(INPUT_GET, 'columnsToShow'))
        {
            $columnsToShow = filter_input(INPUT_GET, 'columnsToShow', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            $_SESSION[self::AGENT_COLUMNS_TO_SHOW_KEY] = $columnsToShow;            
        }
        elseif (isset($_SESSION[self::AGENT_COLUMNS_TO_SHOW_KEY]))
        {
            $columnsToShow = $_SESSION[self::AGENT_COLUMNS_TO_SHOW_KEY];
        }
                       
        $pageSize = 25;

        if (filter_has_var(INPUT_GET, 'pageSize'))
        {
            $pageSize = filter_input(INPUT_GET, 'pageSize');
            $_SESSION[self::AGENT_PAGE_SIZE_KEY] = $pageSize;
        }            
        elseif (isset($_SESSION[self::AGENT_PAGE_SIZE_KEY]))
        {
            $pageSize = $_SESSION[self::AGENT_PAGE_SIZE_KEY];
        }

        $sort = 'id';

        if (filter_has_var(INPUT_GET, 'Agent_sort'))
        {
            $sort = filter_input(INPUT_GET, 'Agent_sort');
            $_SESSION[self::AGENT_SORT_KEY] = $sort;
        }
        elseif (isset($_SESSION[self::AGENT_SORT_KEY]))
        {
            $sort = $_SESSION[self::AGENT_SORT_KEY];
        }
                
        $this->render('admin',array(
            'agents' => $agents,
            'columnsToShow' => $columnsToShow, 
            'pageSize' => $pageSize, 
            'sort' => $sort,
        ));
	}

    /**
	 * Creates a new Agent.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$agent = new Agent;

        if (filter_has_var(INPUT_POST, 'Agent')) 
        {
            $agent->attributes = filter_input(INPUT_POST, 'Agent', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            
            $selectedClientID = $_POST['Agent']['client'];
            $agent->client = Client::model()->findByPk($selectedClientID);
            
            if ($agent->save())
            {
				Yii::app()->user->setFlash('success', "Agent ".$agent->id." was created successfully!");
				$this->redirect(array('admin'));
            }
        }
        		
		$this->render('create',array(
			'agent' => $agent,
		));
	}

    /**
	 * Updates a particular Agent.
	 * @param integer $id the ID of the Agent to be updated
	 */
	public function actionUpdate($id)
	{
		$agent = $this->loadModel($id);

        if (filter_has_var(INPUT_POST, 'Agent'))
        {
            $agent->attributes = filter_input(INPUT_POST, 'Agent', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            $selectedClientID = $_POST['Agent']['client'];
            $agent->client = Client::model()->findByPk($selectedClientID);

            if ($agent->save())
            {
				Yii::app()->user->setFlash('success', "Agent $id was updated successfully!");
				$this->redirect(array('admin'));                
            }
        }
        
		$this->render('update',array(
			'agent' => $agent,
		));
	}
    
   	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	private function loadModel($id)
	{
		$model = Agent::model()->findByPk($id);
		
        if ($model === null)
			throw new CHttpException(404,'The requested page does not exist.');
        
		return $model;
	}

}