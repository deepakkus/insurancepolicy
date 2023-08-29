<?php

/**
 * This class is intended for saving generic model history in the "model_history" database table.
 * This behavior taps into the CActiveRecord behavior, "afterSave".
 *
 * Implementation example in CActiveRecord subclass:
 * 
 * public $historyUserID = null;
 *
 * public function behaviors()
 * {
 *     return array(
 *         'history' => array(
 *             'class' => 'HistoryBehavior',
 *             'historyBehaviorCallback' => array($this, 'historyBehaviorCallback'),
 *             // This callback function is optional
 *             // Intended for capturing userID for API methods when no one is logged in
 *             // If not implemented, logged in user or -1 will be captured
 *             'historyBehaviorCallbackUserID' => array($this, 'historyBehaviorCallbackUserID')
 *         )
 *     );
 * }
 *
 * public function historyBehaviorCallback()
 * {
 *     return json_encode($this->attributes);
 * }
 *
 * // This callback function is optional
 * // If not implemented, logged in user or -1 will be captured
 * public function historyBehaviorCallbackUserID()
 * {
 *     return $this->historyUserID;
 * }
 */
class HistoryBehavior extends CActiveRecordBehavior
{
    private $_historyBehaviorCallback;
    private $_historyBehaviorCallbackUserID = null;

	/**
     * Declares events and the corresponding event handler methods.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
		return array_merge(parent::events(), array(
			'onAfterSave' => 'historySave'
		));
    }

	/**
     * Responds to {@link CActiveRecord::onBeforeSave} event.
     * @param CModelEvent $event event parameter
     */
	public function historySave($event)
	{
        $historyModel = new ModelHistory;

        $historyModel->user_id = call_user_func($this->historyBehaviorCallbackUserID);
        $historyModel->date = date('Y-m-d H:i:s');
        $historyModel->table = $event->sender->tableName();
        $historyModel->table_pk = $event->sender->getPrimaryKey();
        $historyModel->data = call_user_func($this->historyBehaviorCallback);

        // In case the custom callback is implemented, but still returns a null value
        if ($historyModel->user_id === null)
        {
            $historyModel->user_id = -1;
        }

        if (!$historyModel->save())
        {
            Yii::log('ERROR SAVING HISTORY ENTRY: ' . var_export($historyModel->getErrors(), true), CLogger::LEVEL_ERROR, __METHOD__);
            throw new \CException('History Entry could not be saved.  See error log for details.');
        }
	}

    /**
     * Sets the history behavior callback function
     * @param callable $callback
     */
    public function setHistoryBehaviorCallback($callback)
    {
        $this->_historyBehaviorCallback = $callback;
    }

    /**
     * Gets the history behavior callback function
     * @return callable
     */
    public function getHistoryBehaviorCallback()
    {
        return $this->_historyBehaviorCallback;
    }

    /**
     * Sets the history behavior user id callback function
     * @param callable $callback
     */
    public function setHistoryBehaviorCallbackUserID($callback)
    {
        $this->_historyBehaviorCallbackUserID = $callback;
    }

    /**
     * Gets the history behavior user id callback function
     * @return callable
     */
    public function getHistoryBehaviorCallbackUserID()
    {
        // Default value of logged in user ID if custom callback is not set
        if ($this->_historyBehaviorCallbackUserID === null)
        {
            $this->_historyBehaviorCallbackUserID = function() { return isset(Yii::app()->user->id) ? Yii::app()->user->id : -1; };
        }

        return $this->_historyBehaviorCallbackUserID;
    }
}