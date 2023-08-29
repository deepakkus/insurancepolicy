<?php

class WebUser extends CWebUser
{
    protected function afterLogout()
    {
        $this->loginRequired();
        return parent::afterLogout();
    }
    
	/**
     * Changes the current user with the specified identity information.
     * This method is called by {@link login} and {@link restoreFromCookie}
     * when the current user needs to be populated with the corresponding
     * identity information.
     *
     * This method is overidden to fix the: "session_regenerate_id(): Session object destruction failed" PHP warning
     *
     * Yii 1 Code: https://github.com/yiisoft/yii/blob/master/framework/web/CHttpSession.php#L175-185
     * Yii 2 Code: https://github.com/yiisoft/yii2/blob/master/framework/web/Session.php#L262-267
     *
     * @param mixed $id a unique identifier for the user
     * @param string $name the display name for the user
     * @param array $states identity states
	 */
	protected function changeIdentity($id, $name, $states)
	{
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            // Screw it, just don't delete old sessions ... we just can't get rid of this error!!!
            @session_regenerate_id(false);

            // add @ to inhibit possible warning due to race condition
            // https://github.com/yiisoft/yii2/pull/1812
            // @session_regenerate_id(true);
        }

		$this->setId($id);
		$this->setName($name);
		$this->loadIdentityStates($states);
	}
}