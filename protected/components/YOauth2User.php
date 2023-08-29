<?php

/**
 * YOauth2User represents an auth2 retrieved from persistent storage.
 * The YOauth2User will have a client secret, redirect uri, and an array of
 * avilible scopes that auth2 user is authorized for.
 *
 * @property string $clientSecret
 * @property string $redirectUri
 * @property array $scope the allowed client scope
 * @property boolean|null $active
 *
 * @author Matt Eiben <meiben@wildfire-defense.com>
 */
class YOauth2User extends CComponent
{
	private $_clientSecret;
	private $_redirectUri;
	private $_scope;
    private $_active;

	/**
	 * Constructor.
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $scope comma seperate string of scopes
	 */
	public function __construct($clientSecret, $redirectUri, $scope, $active)
	{
		$this->_clientSecret = $clientSecret;
		$this->_redirectUri = $redirectUri;
		$this->_scope = $scope;
        $this->_active = $active;
	}

	/**
	 * @return string oauth2 user client secret
	 */
	public function getClientSecret()
	{
		return $this->_clientSecret;
	}

    /**
     * @return string oauth2 user redirect uri
     */
    public function getRedirectUri()
    {
        return $this->_redirectUri;
    }

    /**
     * @return array availible oauth2 user scopes
     */
    public function getScope()
    {
        if (empty($this->_scope))
            return array();

        return explode(',', $this->_scope);
    }

    /**
     * @return boolean|null oauth2 user active state
     */
    public function getActive()
    {
        return filter_var($this->_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
