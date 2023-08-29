<?php

/**
 * WDSActionLogger is a class that handels the 'onBeforeAction' event for Controllers
 * The controller should extend from CController or its child classes.
 *
 * The "blackList" property is an optional array to be configured with actions to ignore.
 *      If there are no blacklisted actions, just don't include the key
 *
 * Example use in controller:
 *
 *  public function behaviors()
 *  {
 *      return array(
 *          'wdsLogger' => array(
 *              'class' => 'WDSActionLogger'
 *              'blackList' => array(
 *                  'action1',
 *                  'action2'
 *              )
 *          )
 *      )
 *  }
 *
 * @author Matt Eiben
 */
class WDSActionLogger extends CBehavior
{
    /**
     * Optional behavior property of actions to ignore
     * @var array
     */
    private $_blackList = array();

	/**
     * Declares events and the corresponding event handler methods.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
		return array(
			'onBeforeAction' => 'logUserAction'
		);
    }

	/**
     * Responds to onBeforeAction event.
     * @param CEvent $event event parameter
     */
	public function logUserAction($event)
	{
        $route = $event->sender->route;
        $action = substr($route, strpos($route, '/') + 1);

        // Check to make sure this action isn't blacklisted
        if (!in_array($action, $this->blackList))
        {
            $data = array(
                'get' => $_GET,
                'post' => $_POST
            );

            array_walk_recursive($data, function(&$value, $key) {
                if ($key === 'password')
                {
                    $value = '***********';
                }
            });

            // Ignoring grid ajax requests, if they're not delete requests
            if ((isset($data['get']['ajax']) || isset($data['post']['ajax'])) &&  stripos($action, 'delete') === false)
            {
                return;
            }

            $userID = isset(Yii::app()->user->id) ? Yii::app()->user->id : '';

            $logEntry = new UserTracking;
            $logEntry->user_id = $userID;
            $logEntry->platform_id = 1;
            $logEntry->ip = $_SERVER['REMOTE_ADDR'];
            $logEntry->date = date('Y-m-d H:i');
            $logEntry->route = $route;
            $logEntry->data = json_encode($data);

            if (!$logEntry->save())
            {
                Yii::log('ERRORS SAVING LOG ENTRY: ' . var_export($logEntry->getErrors(), true), CLogger::LEVEL_ERROR, __METHOD__);
                Yii::log('ERRORS SAVING WITH ROUTE: ' . $route, CLogger::LEVEL_ERROR, __METHOD__);
                throw new CDbException('Log Entry could not be saved.  See error log for details.');
            }
        }
	}

    /**
     * Sets the blacklisted actions to check against
     * @param callable $value
     */
    public function setBlackList($value)
    {
        $this->_blackList = $value;
    }

    /**
     * Gets the blacklisted actions set in the configuration
     * @return callable
     */
    public function getBlackList()
    {
        return $this->_blackList;
    }
}