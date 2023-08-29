<?php

class ResPhActionTypeController extends Controller
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
                        'apiGetAll',
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
                    'manage',
                    'manageSearch',
                    'create',
                    'update'
                ),
				'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types)',
			),
            array('allow',
				'actions' => array(
                    'apiGetAll',
                ),
				'users' => array('*')
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
                'class' => 'WDSActionLogger',
                'blackList' => array(
                    'manageSearch'
                )
            )
        );
    }

    /**
     * Renders the manage view
     */
    public function actionManage()
    {
        $category = new ResPhActionCategory('search');

        $type = new ResPhActionType('search');
        $type->active = null;

        $this->render('manage', array(
            'category' => $category,
            'type' => $type
        ));
    }

    /**
     * Performs asynchronous sorting and filtering for the gridviews on the manage view.
     * @param integer $id ID of category
     */
    public function actionManageSearch($id)
    {
        $category = ResPhActionCategory::model()->findByPk($id);

		$type = new ResPhActionType('search');
		$type->unsetAttributes();

		if (isset($_GET['ResPhActionType']))
			$type->attributes = $_GET['ResPhActionType'];

		$this->renderPartial('_category', array(
            'data' => $category,
			'model' => $type,
		));
    }

	/**
     * Creates a new model.
     * @param integer $id ID of category
     */
    public function actionCreate($id)
    {
		$model = new ResPhActionType;
        $model->category_id = $id;

		if (isset($_POST['ResPhActionType']))
		{
			$model->attributes = $_POST['ResPhActionType'];

			if ($model->save())
				$this->redirect(array('manage'));
		}

		$this->render('create',array(
			'model' => $model,
		));
    }

	/**
     * Updates a particular model.
     * @param integer $id ID of visit
     */
    public function actionUpdate($id)
    {
		$model = ResPhActionType::model()->findByPk($id);

		if (isset($_POST['ResPhActionType']))
		{
			$model->attributes = $_POST['ResPhActionType'];

			if ($model->save())
				$this->redirect(array('manage'));
		}

		$this->render('update',array(
			'model' => $model,
		));
    }

    /**
     * API Method: resPhActionType/apiGetAll
     * Description: Gets all Active Policyholder Action Types
     * No input data required, just an access token with proper scope
     */
    public function actionApiGetAll()
    {
        $resPhActionTypes = ResPhActionType::model()->findAll('active = 1');
        $returnData = array();
        foreach($resPhActionTypes as $resPhActionType)
        {
            $returnData[] = array('id'=>$resPhActionType->id, 'name'=>$resPhActionType->name, 'type'=>$resPhActionType->action_type);
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}
