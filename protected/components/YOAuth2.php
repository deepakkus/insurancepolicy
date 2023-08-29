<?php

/**
 * @file
 * Sample OAuth2 Library PDO DB Implementation.
 */

define("PDO_DSN", Yii::app()->db->connectionString);
define("PDO_USER", Yii::app()->db->username);
define("PDO_PASS", Yii::app()->db->password);
define("PDO_OPTS", array(
  PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_SYSTEM
));

include "OAuth2.php";

/**
 * OAuth2 Library PDO DB Implementation.
 */
class YOAuth2 extends OAuth2 {

  private $db;
  protected $conf = array('display_error'=>true);

  /**
   * Overrides OAuth2::__construct().
   */
  public function __construct() {
    parent::__construct();

    try {
      $this->db = new PDO(PDO_DSN, PDO_USER, PDO_PASS, PDO_OPTS);
    } catch (PDOException $e) {
      die('Connection failed: ' . $e->getMessage());
    }
  }

  /**
   * Release DB connection during destruct.
   */
  function __destruct() {
    $this->db = NULL; // Release db connection
  }

  /**
   * Handle PDO exceptional cases.
   */
  private function handleException($e) {
    echo "Database error: " . $e->getMessage();
    exit;
  }

  protected function getSupportedScopes() {
    return array('fireshield', 'dash', 'usaaenrollment', 'engine');
  }

  /**
   * Little helper function to add a new client to the database.
   *
   * Do NOT use this in production! This sample code stores the secret
   * in plaintext!
   *
   * @param $client_id
   *   Client identifier to be stored.
   * @param $client_secret
   *   Client secret to be stored.
   * @param $redirect_uri
   *   Redirect URI to be stored.
   */
  //public function addClient($client_id, $client_secret, $redirect_uri) {
  //  try {
  //    $sql = "INSERT INTO [user] (username, client_secret, redirect_uri) VALUES (:client_id, :client_secret, :redirect_uri)";
  //    $stmt = $this->db->prepare($sql);
  //    $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
  //    $stmt->bindParam(":client_secret", $client_secret, PDO::PARAM_STR);
  //    $stmt->bindParam(":redirect_uri", $redirect_uri, PDO::PARAM_STR);
  //    $stmt->execute();
  //  } catch (PDOException $e) {
  //    $this->handleException($e);
  //  }
  //}

  /**
   * Implements OAuth2::checkClientCredentials().
   *
   * Do NOT use this in production! This sample code stores the secret
   * in plaintext!
   */
  protected function checkClientCredentials($client_id, $client_secret = NULL) {
    try {
      $sql = "SELECT client_secret FROM [user] WHERE username = :client_id";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($client_secret === NULL)
          return $result !== FALSE;

      return $result["client_secret"] == $client_secret;
    } catch (PDOException $e) {
      $this->handleException($e);
    }
  }

  /**
   * Implements OAuth2::getRedirectUri().
   */
  protected function getRedirectUri($client_id) {
    try {
      $sql = "SELECT redirect_uri FROM [user] WHERE username = :client_id";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($result === FALSE)
          return FALSE;

      return isset($result["redirect_uri"]) && $result["redirect_uri"] ? $result["redirect_uri"] : NULL;
    } catch (PDOException $e) {
      $this->handleException($e);
    }
  }

  /**
   * Implements OAuth2::getOauth2User().
   */
  public function getOauth2User($client_id) {
      try {
          $sql = "SELECT client_secret, redirect_uri, scope, active FROM [user] WHERE username = :client_id";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
          $stmt->execute();

          $result = $stmt->fetch(PDO::FETCH_ASSOC);

          if ($result === FALSE)
              return FALSE;

          return new YOauth2User($result['client_secret'], $result['redirect_uri'], $result['scope'], $result['active']);
      }
      catch (PDOException $e) {
          $this->handleException($e);
      }
  }

  /**
   * Implements OAuth2::getAccessToken().
   */
  protected function getAccessToken($access_token) {
    try {
        $type = 'access';
        $sql = "SELECT client_id, expires, scope FROM oa2_tokens WHERE type = :type AND oauth_token = :oauth_token";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":oauth_token", $access_token, PDO::PARAM_STR);
        $stmt->bindParam(":type", $type, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== FALSE ? $result : NULL;
    } catch (PDOException $e) {
      $this->handleException($e);
    }
  }

  /**
   * Implements OAuth2::getRefreshToken().
   */
  protected function getRefreshToken($refresh_token) {
      try {
          $type = 'refresh';
          $sql = "SELECT client_id, expires, scope, oauth_token AS refresh_token FROM oa2_tokens WHERE type = :type AND oauth_token = :oauth_token";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(":oauth_token", $refresh_token, PDO::PARAM_STR);
          $stmt->bindParam(":type", $type, PDO::PARAM_STR);
          $stmt->execute();

          $result = $stmt->fetch(PDO::FETCH_ASSOC);

          return $result !== FALSE ? $result : NULL;
      }
      catch (PDOException $e) {
          $this->handleException($e);
      }
  }

  /**
   * Implements OAuth2::setAccessToken().
   */
  protected function setAccessToken($oauth_token, $client_id, $expires, $scope = NULL)
  {
      $user = User::model()->find(array(
          'select' => array('id', 'type'),
          'condition' => 'username = :client_id',
          'params' => array(':client_id' => $client_id)
      ));

      if ($user)
      {
          // Existing clients are set to null for backwards compatibility
          if (in_array('OAuth2 Legacy', $user->getSelectedTypes()) === true)
          {
              $expires = NULL;
          }
      }

      try
      {
          $type = 'access';
          $sql = "INSERT INTO oa2_tokens (oauth_token, client_id, expires, scope, type) VALUES (:oauth_token, :client_id, :expires, :scope, :type)";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(":oauth_token", $oauth_token, PDO::PARAM_STR);
          $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
          $stmt->bindParam(":expires", $expires, PDO::PARAM_INT);
          $stmt->bindParam(":scope", $scope, PDO::PARAM_STR);
          $stmt->bindParam(":type", $type, PDO::PARAM_STR);

          $stmt->execute();
      }
      catch (PDOException $e)
      {
          $this->handleException($e);
      }
  }

    /**
    * Implements OAuth2::setRefreshToken().
    */
    protected function setRefreshToken($refresh_token, $client_id, $expires, $scope = NULL)
    {
        try
        {
            $type = 'refresh';
            $sql = "INSERT INTO oa2_tokens (oauth_token, client_id, expires, scope, type) VALUES (:oauth_token, :client_id, :expires, :scope, :type)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":oauth_token", $refresh_token, PDO::PARAM_STR);
            $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
            $stmt->bindParam(":expires", $expires, PDO::PARAM_INT);
            $stmt->bindParam(":scope", $scope, PDO::PARAM_STR);
            $stmt->bindParam(":type", $type, PDO::PARAM_STR);

            $stmt->execute();
        }
        catch (PDOException $e)
        {
            $this->handleException($e);
        }
    }

  /**
   * Implements OAuth2::unsetRefreshToken
   * @param mixed $refresh_token
   * @return void
   */
  protected function unsetRefreshToken($refresh_token) {
      try {
          $type = 'refresh';
          $sql = "DELETE FROM oa2_tokens WHERE type = :type AND oauth_token = :oauth_token";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(":oauth_token", $refresh_token, PDO::PARAM_STR);
          $stmt->bindParam(":type", $type, PDO::PARAM_STR);

          $stmt->execute();
      }
      catch (PDOException $e) {
          $this->handleException($e);
      }
  }

  /**
   * Overrides OAuth2::getSupportedGrantTypes().
   */
  protected function getSupportedGrantTypes() {
    return array(
      OAUTH2_GRANT_TYPE_AUTH_CODE,
      OAUTH2_GRANT_TYPE_REFRESH_TOKEN,
    );
  }

  /**
   * Overrides OAuth2::getAuthCode().
   */
  protected function getAuthCode($code) {
    try {
      $sql = "SELECT code, client_id, redirect_uri, expires, scope FROM oa2_auth_codes WHERE code = :code";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(":code", $code, PDO::PARAM_STR);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result !== FALSE ? $result : NULL;
    } catch (PDOException $e) {
      $this->handleException($e);
    }
  }

  /**
   * Overrides OAuth2::setAuthCode().
   */
  protected function setAuthCode($code, $client_id, $redirect_uri, $expires, $scope = NULL)
  {
      try
      {
          //custom code for setting scope of iOS FS use case that doesn't pass in scope anywhere
          if(!isset($scope) && $client_id === 'iOSfireshield0001')
              $scope = 'fireshield';

          $sql = "INSERT INTO oa2_auth_codes (code, client_id, redirect_uri, expires, scope) VALUES (:code, :client_id, :redirect_uri, :expires, :scope)";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(":code", $code, PDO::PARAM_STR);
          $stmt->bindParam(":client_id", $client_id, PDO::PARAM_STR);
          $stmt->bindParam(":redirect_uri", $redirect_uri, PDO::PARAM_STR);
          $stmt->bindParam(":expires", $expires, PDO::PARAM_INT);
          $stmt->bindParam(":scope", $scope, PDO::PARAM_STR);

          $stmt->execute();
      }
      catch (PDOException $e)
      {
          $this->handleException($e);
      }
  }
}