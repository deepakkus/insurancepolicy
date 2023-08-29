<?php

//----------Example Usage----------------
/*
require('WDSAPIClient.php');
$client = new WDSAPIClient('https://dev.wildfire-defense.com/', 'wds_dash', 'cf3d82b6-ffed-4321-9e72-bc8d11873714', 'dashauth://authorization', 'dash');

$username = 'dashtest';
$password = '123dash';
$result = $client->wdsAPICall('user/apiLogin', array('username'=>$username, 'password'=>$password));
if($result['error'] === 0) //success
{
	print_r($result);
}
else //error == 1
{
	echo $result['errorMessage'];
}
*/
/*--------OUTPUT ON SUCCESS--------
Array
(
    [error] => 0
    [data] => Array
        (
            [name] => Dash Tester
            [username] => dashtest
            [types] => Array
                (
                    [0] => LM CA Liberty
                    [1] => LM CA Safeco
                )

        )

)
--------------------------------------*/


/***************************/
/*Author: Tyler Cross      */
/*Simple Client to connect */
/*to WDS API using oAuth2. */
/*Change first 5 variables */
/*to configure access creds*/
/***************************/
class WDSAPIClient
{
	private $apiBaseURL;
	private $oAuth2ClientID;
	private $oAuth2ClientSecret;
	private $oAuth2RedirectUri;
	private $oAuth2Scope;
	public $oAuth2Token;
	private $ch;
	
	public function __construct($baseURL, $clientID, $clientSecret, $redirectURI, $scope)
	{
		$this->apiBaseURL = $baseURL;
		$this->oAuth2ClientID = $clientID;
		$this->oAuth2ClientSecret = $clientSecret;
		$this->oAuth2RedirectUri = $redirectURI;
		$this->oAuth2Scope = $scope;
		$this->oAuth2GetToken();
	}

	public function oAuth2GetToken()
	{
		$authEndpoint = $this->apiBaseURL.'?r=oa2/auth';
		$tokenEndpoint = $this->apiBaseURL.'?r=oa2/token';
		$responseType = 'code';
		$grantType = 'authorization_code';

		//request authorization code
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $authEndpoint);
		$postData = array(
            'client_id'=>$this->oAuth2ClientID,
            'response_type'=>$responseType,
            'scope'=>$this->oAuth2Scope
        );
		curl_setopt($this->ch, CURLOPT_POST, count($postData));
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'wds_api_cookie');  //could be empty, but cause problems on some hosts
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, 'wds_api_cookies');  //could be empty, but cause problems on some hosts
		$headers = curl_exec($this->ch);
		//curl_close($this->ch);
		//get auth code out of location header
		foreach(explode("\n", $headers) as $header)
		{
            if(strpos($this->oAuth2RedirectUri,'?') === FALSE)
                $codePart = '?code=';
            else
                $codePart = '&code=';

    		$isLocationHeader = strpos($header, 'Location: '.$this->oAuth2RedirectUri.$codePart);
			if($isLocationHeader !== false)
			{
				$authCode = trim(substr($header, strpos($header, $codePart)+strlen($codePart)));
				break;
			}
		}

		//request token
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $tokenEndpoint);
		$postData = array(
			'grant_type'=>$grantType,
			'client_id'=>$this->oAuth2ClientID,
			'client_secret'=>$this->oAuth2ClientSecret,
			'code'=>$authCode,
			'redirect_uri'=>$this->oAuth2RedirectUri,
			'scope'=>$this->oAuth2Scope,
		);
		curl_setopt($this->ch, CURLOPT_POST, count($postData));
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'wds_api_cookie');  //could be empty, but cause problems on some hosts
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, 'wds_api_cookies');  //could be empty, but cause problems on some hosts
		$result = curl_exec($this->ch);
		$result = json_decode($result, true);
		//curl_close($this->ch);
		$this->oAuth2Token = $result['access_token'];
	}

	public function wdsAPICall($route, $data)
	{
		$this->ch = curl_init();
		$postData['access_token'] = $this->oAuth2Token;
		$postData['data'] = json_encode(array('data'=>$data));
		curl_setopt($this->ch,CURLOPT_URL, $this->apiBaseURL.'?r='.$route);
		curl_setopt($this->ch, CURLOPT_POST, count($postData));
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'wds_api_cookie');  //could be empty, but cause problems on some hosts
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, 'wds_api_cookies');  //could be empty, but cause problems on some hosts
		$result = curl_exec($this->ch);
		//curl_close($this->ch);
		return json_decode($result, true);
	}

}
