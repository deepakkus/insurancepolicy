<?php

class ResPhActionCategoryController extends Controller
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
				'actions'=>array(
                    'create',
                    'update'
                ),
				'users' => array('@')
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
	 * Creates a new model.
	 */
	public function actionCreate()
	{
		$model = new ResPhActionCategory;

		if (isset($_POST['ResPhActionCategory']))
		{
			$model->attributes = $_POST['ResPhActionCategory'];

			if ($model->save())
				$this->redirect(array('resPhActionType/manage'));
		}

		$this->render('create',array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model = ResPhActionCategory::model()->findByPk($id);

		if (isset($_POST['ResPhActionCategory']))
		{
			$model->attributes = $_POST['ResPhActionCategory'];

			if ($model->save())
				$this->redirect(array('resPhActionType/manage'));
		}

		$this->render('update',array(
			'model' => $model,
		));
	}
}
