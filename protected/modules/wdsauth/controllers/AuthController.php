<?php

class AuthController extends Controller
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
                'actions' => array(
                    'manage',
                    'assign',
                    'createAuthItem',
                    'updateAuthItem',
                    'viewAuthItem',
                    'deleteAuthItem',
                    'assign'
                ),
                'users'=>array('@')
            ),
            array('deny',
                'users' => array('*')
            )
        );
    }

    public function init()
    {
        $this->defaultAction = 'manage';
        $this->layout = $this->module->layout;
        $this->module->registerScripts();
        return parent::init();
    }

    /**
     * Render the wdsauth module manage view
     * Manages CRUD operations for auth items
     * @return mixed
     */
    public function actionManage()
    {
        $viewAuthItemForm = new ViewAuthItemForm;

		if (isset($_POST['ajax']) &&  in_array($_POST['ajax'], array('view-role-form','view-task-form','view-operation-form')))
		{
			echo CActiveForm::validate($viewAuthItemForm);
			Yii::app()->end();
		}

		return $this->render('manage', array(
            'viewAuthItemForm' => $viewAuthItemForm
        ));
    }

    /**
     * Create a new auth item
     * @param integer $type CAuthItem const
     * @return mixed
     */
    public function actionCreateAuthItem($type)
    {
        $authItemForm = new AuthItemForm;
        $authItemForm->scenario = 'create';
        $authItemForm->isNewRecord = true;
        $authItemForm->type = $type;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'auth-item-form')
        {
            echo CActiveForm::validate($authItemForm);
            Yii::app()->end();
        }

        if (isset($_POST['AuthItemForm']))
        {
            $authItemForm->attributes = $_POST['AuthItemForm'];
            $authItemName = sprintf('%s.%s', $authItemForm->platform, $authItemForm->name);
            $this->module->authManager->createAuthItem($authItemName, $authItemForm->type, $authItemForm->description);
            return $this->redirect(array('manage', '#' => $this->module->authManager->getAuthItemTypeName($type)));
        }

        return $this->renderPartial('_auth_item_form', array(
            'authItemForm' => $authItemForm,
            'type' => $type
        ));
    }

    /**
     * Update an auth item
     * @param integer $type CAuthItem constant
     * @param string $name
     * @return mixed
     */
    public function actionUpdateAuthItem($type, $name)
    {
        $authItemForm = new AuthItemForm;
        $authItemForm->scenario = 'update';
        $authItemForm->isNewRecord = false;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'auth-item-form')
        {
            echo CActiveForm::validate($authItemForm);
            Yii::app()->end();
        }

        if (isset($_POST['AuthItemForm']))
        {
            $authItemForm->attributes = $_POST['AuthItemForm'];
            $oldName = sprintf('%s.%s', $authItemForm->oldPlatform, $authItemForm->oldName);
            if ($authItemForm->platform === $authItemForm->oldPlatform && $authItemForm->name === $authItemForm->oldName)
                $oldName = null;
            $authItem = $authItemForm->createCAuthItem($this->module->authManager);
            $this->module->authManager->saveAuthItem($authItem, $oldName);
            return $this->redirect(array('manage', '#' => $this->module->authManager->getAuthItemTypeName($type)));
        }

        $authItem = $this->module->authManager->getAuthItem($name);

        $authItemForm->loadModelFromAuthItem($authItem);

        return $this->renderPartial('_auth_item_form', array(
            'authItemForm' => $authItemForm,
            'type' => $type,
            'name' => $name
        ));
    }

    /**
     * View auth item
     * @param integer $type CAuthItem constant
     * @return mixed
     */
    public function actionViewAuthItem($type)
    {
        if (isset($_POST['ViewAuthItemForm']))
        {
            $viewAuthItemForm = new ViewAuthItemForm;
            $viewAuthItemForm->attributes = $_POST['ViewAuthItemForm'];

            $name = '';
            switch ($type)
            {
                case CAuthItem::TYPE_ROLE: $name = $viewAuthItemForm->role; break;
                case CAuthItem::TYPE_TASK: $name = $viewAuthItemForm->task; break;
                case CAuthItem::TYPE_OPERATION: $name = $viewAuthItemForm->operation; break;
            }

            $authItem = $this->module->authManager->getAuthItem($name);

            $childrenDataProvider = AuthItem::searchChildrenAuthItems($name);
            $parentsDataProvider = AuthItem::searchParentAuthItems($name);
            $usersDataProvider = AuthAssignment::searchAuthItemUsers($name);

            $typeName = $this->module->authManager->getAuthItemTypeName($type);

            return $this->renderPartial('_auth_item_view', array(
                'childrenDataProvider' => $childrenDataProvider,
                'parentsDataProvider' => $parentsDataProvider,
                'usersDataProvider' => $usersDataProvider,
                'authItem' => $authItem,
                'typeName' => $typeName
            ));
        }
    }

    /**
     * Delete an auth item
     */
    public function actionDeleteAuthItem()
    {
        if (Yii::app()->request->isPostRequest)
        {
            $name = Yii::app()->request->getPost('name');
            $type = Yii::app()->request->getPost('type');
            $this->module->authManager->removeAuthItem($name);
            Yii::app()->user->setFlash('success', $name . ' was successfully deleted');
        }

        return $this->redirect(array('manage', '#' => $this->module->authManager->getAuthItemTypeName($type)));
    }

    /**
     * Render the wdsauth module assign view
     * Allows user to assign auth items to each other
     * @return mixed
     */
    public function actionAssign()
    {
        return $this->render('assign');
    }
}