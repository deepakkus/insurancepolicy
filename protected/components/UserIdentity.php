<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    const ERROR_USERTYPE_INVALID = 3;
	const ERROR_USERIP_INVALID = 4;
    const ERROR_MAX_LOGIN_ATTEMPTS = 5;
    const ERROR_PASSWORD_EXPIRED = 6;
    const ERROR_USER_DEACTIVATED = 7;
    private $_id;

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
    {
            $user = User::model()->findByAttributes(array('username' => $this->username));

            if ($user===null)
            {
				$this->errorCode = self::ERROR_USERNAME_INVALID;
            }
            elseif (filter_var($user->active, FILTER_VALIDATE_BOOLEAN) === false)
            {
                $this->errorCode = self::ERROR_USER_DEACTIVATED;
            }
            else
            {
                $user->loginAttempt();
                if ($user->checkMaxLoginAttempts())
                {
                    $this->errorCode=self::ERROR_MAX_LOGIN_ATTEMPTS;
                }
                else if (!$user->validatePassword($this->password))
                {
				    $this->errorCode=self::ERROR_PASSWORD_INVALID;
                }
			    else if (!$user->checkWDSAdminAllowed())
                {
				    $this->errorCode=self::ERROR_USERTYPE_INVALID;
                }
                else if ($user->checkPWExp())
                {
                    $this->errorCode=self::ERROR_PASSWORD_EXPIRED;
                    $user->resetAttempts(); // Don't increment attempt on password expired
                }
                //else if(!$user->checkAllowedIPs($_SERVER['REMOTE_ADDR']))
                //{
                //    $this->errorCode=self::ERROR_USERIP_INVALID;
                //}
                else
                {
                    $user->resetAttempts();
                    $this->_id=$user->id;

                    //$assignment = AuthAssignment::model()->findByAttributes(array('userid' => $user->id));
                    //$this->setState('role', $assignment->itemname);

                    $this->username=$user->username;
                    $this->setState('user_id', $user->id);
                    $this->setState('types', explode(',',$user->type));
                    $this->setState('fullname', $user->name);
                    $this->setState('username', $user->username);
                    $this->setState('email', $user->email);
                    $this->setState('auto_login_token', $user->auto_login_token);
                    $this->setState('tfs_token', null);
                    $this->setState('tfs_refresh_token', null);
                    $this->setState('tfs_scope', null);
                    $this->errorCode=self::ERROR_NONE;
                }
            }
            return $this->errorCode===self::ERROR_NONE;
	}

        public function getId()
        {
            return $this->_id;
        }
}