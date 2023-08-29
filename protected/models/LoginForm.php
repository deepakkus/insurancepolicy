<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
	public $UserGUID;
	public $email;
	public $phoneNo;
	public $code;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password', 'required'),
			// rememberMe needs to be a boolean
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('password', 'authenticate'),
			array('phoneNo, code', 'numerical', 'integerOnly'=>true),
			array('phoneNo', 'length', 'min'=>10, 'max'=>10),
			array('code', 'length', 'min'=>6, 'max'=>6),
			array('email', 'email')
			);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Remember me next time',
			'phoneNo'=>'Phone Number.',
			'code'=>'Access Code',
			'email'=>'Email'
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			if(!$this->_identity->authenticate())
			{
				if($this->_identity->errorCode === UserIdentity::ERROR_PASSWORD_INVALID || $this->_identity->errorCode === UserIdentity::ERROR_USERNAME_INVALID)
					$this->addError('password','Incorrect username or password.');
				elseif($this->_identity->errorCode === UserIdentity::ERROR_USERTYPE_INVALID)
					$this->addError('password','Users type is not allowed in WDS Admin.');
				elseif($this->_identity->errorCode === UserIdentity::ERROR_USERIP_INVALID)
					$this->addError('password','Users IP is not allowed in WDS Admin.');
                elseif($this->_identity->errorCode === UserIdentity::ERROR_MAX_LOGIN_ATTEMPTS)
					$this->addError('password','User Locked out due to too many login attemps.');
                elseif($this->_identity->errorCode === UserIdentity::ERROR_PASSWORD_EXPIRED)
					$this->addError('password','Users Password is expired, use the Reset Form at the bottom to choose a new one.');
                elseif($this->_identity->errorCode === UserIdentity::ERROR_USER_DEACTIVATED)
					$this->addError('password','User has been deactivated.');
				else
					$this->addError('password','Login Error');
			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
		{
			$duration=$this->rememberMe ? 3600*12 : 0; // 12 hrs
			Yii::app()->user->login($this->_identity,$duration);
			$user = User::model()->findByAttributes(array('username'=>$this->username));            
            if($user->UserGUID == NULL || $user->UserGUID=='{00000000-0000-0000-0000-000000000000}')
            { 
				$apipass = $this->password;
				$LastInsertId = $user->id;
                $provectus = new WDSAPIG2();
                $guid = $provectus->wdsCreatePritiniUser($user, $apipass, $LastInsertId);
            }
			return true;
		}
		else
			return false;
	}
}
