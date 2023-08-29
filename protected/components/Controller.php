<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
    
    /**
     * @var string cache manifest for the html tag in certain layouts. If the controller sets this,
     * the document's html tag will render the manifest attribute for caching purposes. Pages that
     * do not require caching should leave this set to NULL.
     */
    public $htmlManifest = NULL;
    
    /**
     * Sets the page title.
     * @param type $title Name of the page
     */
    protected function setTitle($title) 
    {
        $this->pageTitle = Yii::app()->name . ' - ' . $title;
    }
    
    protected function beforeAction($action)
    {
        $this->onBeforeAction(new CEvent($this));
        return parent::beforeAction($action);
    }
    
	/**
     * This event is raised before an action is run
     * @param CEvent $event the event parameter
     */
    public function onBeforeAction($event)
    {
        $this->raiseEvent('onBeforeAction', $event);
    }
}