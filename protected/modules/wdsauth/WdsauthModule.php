<?php

class WdsauthModule extends CWebModule
{
    /**
     * @var string Default controller called on by WdsauthModule
     */
    public $defaultController = 'auth';

    /**
     * @var CAuthManager Yii authManager
     */
    public $authManager;

    /**
     * @var boolean Whether to enable business rules.
     */
    public $enableBizRule = false;

    /**
     * @var boolean Whether to enable data for business rules.
     */
    public $enableBizRuleData = false;

    /**
     * @var string Layout file to use for module.
     */
    public $layout = 'main';

    /**
     * @var string String the path to the application layout file.
     */
    public $appLayout = 'application.views.layouts.main';

    private $_assetsUrl;

    /**
     * Initializes WdsauthModule
     */
    public function init()
    {
        parent::init();

        // import the module-level models and components
        $this->setImport(array(
            'bootstrap.helpers.TbHtml',
            'wdsauth.models.*',
            'wdsauth.models.forms.*',
            'wdsauth.components.*'
        ));

        $this->layoutPath = Yii::getPathOfAlias('wdsauth.views.layouts');
        $this->authManager = Yii::app()->authManager;
    }

    /**
     * The pre-filter for controller actions.
     * This method is invoked before the currently requested controller action and all its filters
     * are executed.
     *
     * @param CController $controller the controller
     * @param CAction $action the action
     * @return boolean whether the action should be executed.
     */
    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action))
        {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @return string The base URL that contains all published asset files of wdsauth
     */
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl === null)
            $this->_assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('wdsauth.assets'));

        return $this->_assetsUrl;
    }

	/**
     * @param string $value The base URL that contains all published asset files of wdsauth
     */
	public function setAssetsUrl($value)
	{
		$this->_assetsUrl = $value;
	}

    /**
     * Register the necessary scripts.
     * Using package with script to set correct dependency for jquery ui.
     */
    public function registerScripts()
    {
        $assetsUrl = $this->getAssetsUrl();

        $authJsPackage = array(
            'baseUrl' => $assetsUrl,
            'js' => array('/js/wdsauth.js'),
            'depends' => array('jquery.ui')
        );

        Yii::app()->getClientScript()
            ->registerCssFile($assetsUrl . '/css/wdsauth.css')
            ->addPackage('wds-auth-js', $authJsPackage)
            ->registerPackage('wds-auth-js');
    }
}
