<?php

class ApiDocumentationController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
			'postOnly + delete, renderMarkdown',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetAvailableApiDocs',
                        'apiGetApiDocs'
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
                    'create',
                    'update',
                    'delete',
                    'renderMarkdown'
                ),
				'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types)'
			),
			array('allow',
				'actions' => array(
                    'apiGetAvailableApiDocs',
                    'apiGetApiDocs'
                ),
				'users' => array('*')),
			array('deny',
				'users' => array('*')
			)
		);
	}

	/**
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model = new ApiDocumentation('search');
		$model->unsetAttributes();
		if (isset($_GET['ApiDocumentation']))
			$model->attributes = $_GET['ApiDocumentation'];

		$this->render('admin', array(
			'model' => $model
		));
	}

	/**
	 * Creates a new model.
	 */
	public function actionCreate()
	{
		$model = new ApiDocumentation;

		if (isset($_POST['ApiDocumentation']))
		{
			$model->attributes = $_POST['ApiDocumentation'];
			if ($model->save())
				$this->redirect(array('admin'));
		}

		$this->render('create', array(
			'model' => $model
		));
	}

	/**
	 * Updates a particular model.
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if (isset($_POST['ApiDocumentation']))
		{
			$model->attributes = $_POST['ApiDocumentation'];
			if ($model->save())
				$this->redirect(array('admin'));
		}

		$this->render('update', array(
			'model' => $model
		));
	}

	/**
	 * Deletes a particular model.
	 * @param integer $id
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if (!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

    /**
     * Prints markdown from post text request
     */
    public function actionRenderMarkdown()
    {
        $postText = Yii::app()->request->getPost('text');
        $postID = Yii::app()->request->getPost('id');

        if ($postText || $postID)
        {
            if ($postID)
            {
                $model = ApiDocumentation::model()->findByPk($postID);
                $text = $model->docs;
            }
            else
            {
                $text = $postText;
            }

            $markdown = new CMarkdown;
            $markdown->purifyOutput = true;
            $html = $markdown->transform($text);

            header('Content-Type: text/html');
            header('Content-Length: ' . strlen($html));
            echo $html;
        }
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return ApiDocumentation the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=ApiDocumentation::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    /**
     * API Method: apiDocumentation/apiGetAvailableApiDocs
     * Description: Gets id and name of each availible wds api doc
     *
     * Return example:
     * {
     *     "error": 0,
     *     "data": {[
     *          {
     *              "id": 1,
     *              "name": "WDS API Documentation"
     *          }, {
     *              "id": 2,
     *              "name": "Get Risk V1"
     *          }
     *      ]}
     * }
     */
    public function actionApiGetAvailableApiDocs()
    {
        $data = null;
        $returnArray = array();
        $returnData = array();

		$models = ApiDocumentation::model()->findAllByAttributes(array('active' => true), array(
            'select' => 'id, name',
            'order' => 'id asc'
        ));

        if (!$models)
        {
            return WDSAPI::echoJsonError('No Api Documentation was found');
        }

        foreach ($models as $model)
        {
            $returnData[] = array(
                'id' => $model->id,
                'name' => $model->name
            );
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: apiDocumentation/apiGetApiDocs
     * Description: Get api documentation for given id
     *
     * Post data parameters:
     * @param integer id
     *
     * Post data example:
     * {
     *      "data": {
     *          "id": 3
     *      }
     * }
     *
     * Return example:
     * {
     *     "error": 0,
     *     "data": {
     *         "id": 1,
     *         "name": "WDS API Documentation",
     *         "docs": "api doc text here ......"
     *     }
     * }
     */
    public function actionApiGetApiDocs()
    {
        $data = null;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        $model = ApiDocumentation::model()->findByPk($data['id'], 'active = 1');

        if (!$model)
        {
            return WDSAPI::echoJsonError('No Api Documentation entry was found for this id: ' . $data['id']);
        }

        $returnData = array(
            'id' => $model->id,
            'name' => $model->name,
            'docs' => $model->docs
        );

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }
}
