<?php

class AssignmentController extends Controller
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
                    'index',
                    'assign',
                    'assignAuthItems',
                    'view',
                    'tree',
                    'details'
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
        $this->layout = $this->module->layout;
        $this->module->registerScripts();
        return parent::init();
    }

    /**
     * Render the wdsauth module assign view
     * Allows user to choose auth item to assign
     * @return mixed
     */
    public function actionIndex()
    {
		$model = new AuthItem('search');
		$model->unsetAttributes();

		if (isset($_GET['AuthItem']))
			$model->attributes = $_GET['AuthItem'];

        $dataProvider = $model->search();

		$this->render('index', array(
            'dataProvider' => $dataProvider,
			'model' => $model
		));
    }

    /**
     * Render the assign auth items view
     * Allows user to assign auth items to each other
     * @param string $name 
     */
    public function actionAssign($name)
    {
		$model = new AuthItem('search');
		$model->unsetAttributes();

        $authItem = $this->module->authManager->getAuthItem($name);
        $authItemChildren = $this->module->authManager->getItemChildren($name);

        $attachAuthItemForm = new AttachItemForm;
        $attachAuthItemForm->itemname = $name;
        $attachAuthItemForm->children = json_encode(array_keys($authItemChildren));

		if (isset($_GET['AuthItem']))
			$model->attributes = $_GET['AuthItem'];

        $dataProvider = $model->search($name);

		$this->render('assign', array(
            'attachAuthItemForm' => $attachAuthItemForm,
            'dataProvider' => $dataProvider,
			'model' => $model,
            'name' => $name
		));
    }

    /**
     * Accepts submitted auth items to assign
     * Will remove any currently assigned items that were not submitted
     * @return void
     */
    public function actionAssignAuthItems()
    {
        if (isset($_POST['AttachItemForm']))
        {
            $attachAuthItemForm = new AttachItemForm;
            $attachAuthItemForm->attributes = $_POST['AttachItemForm'];
            $attachAuthItemForm->children = json_decode($attachAuthItemForm->children);

            $authManager = $this->module->authManager;

            $authItem = $authManager->getAuthItem($attachAuthItemForm->itemname);
            $authItemChildren = $this->module->authManager->getItemChildren($attachAuthItemForm->itemname);

            // Revoke any children that arn't submitted
            foreach ($authItemChildren as $currentChild)
                if (in_array($currentChild->getName(), $attachAuthItemForm->children) === false)
                    $authItem->removeChild($currentChild->getName());

            // Add any children that are submitted
            foreach ($attachAuthItemForm->children as $child)
                if ($authItem->hasChild($child) === false)
                    $authItem->addChild($child);

            Yii::app()->user->setFlash('success', 'Auth items assigned successfully');

            return $this->redirect(array('index'));
            //return $this->redirect(array('assign', 'name' => $attachAuthItemForm->itemname));
        }

        $this->redirect(array('index'));
    }

    /**
     * Renders view tree visualization of auth item heirarchy
     * @param string $name 
     */
    public function actionTree($name)
    {
        $authManager = $this->module->authManager;

        // Recursively building permissions array
        function buildRecursiveAuthData($authManager, $authItem, &$data)
        {
            $children = $authItem->getChildren();

            if ($children)
            {
                $data['children'] = array();

                foreach ($children as $child)
                {
                    $data['children'][] = array(
                        'name' => $child->getName(),
                        'type' => $authManager->getAuthItemTypeName($child->getType()),
                        'parent' => $authItem->getName()
                    );

                    buildRecursiveAuthData($authManager, $child, $data['children'][count($data['children']) - 1]);
                }
            }
        }

        $authItem = $authManager->getAuthItem($name);

        $data = array();
        $data['name'] = $authItem->getName();
        $data['type'] = $authManager->getAuthItemTypeName($authItem->getType());
        $data['parent'] = null;

        buildRecursiveAuthData($authManager, $authItem, $data);

        echo $this->renderPartial('_tree', array(
            'name' => $name,
            'data' => $data
        ));
    }

    /**
     * Renders auth item detail view
     * @param string $name 
     */
    public function actionDetails($name)
    {
        $model = AuthItem::model()->findByAttributes(array('name' => $name));

        $this->renderPartial('_details', array(
            'model' => $model
        ));
    }
}