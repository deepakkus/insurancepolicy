<?php

include('YOauth2.php');

class OA2Controller extends Controller
{
    private $oauth;

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
            )
        );
    }

    public function init()
    {
        $this->oauth = new YOauth2();
    }

	public function actionAuth()
	{
		$params = $this->oauth->getAuthorizeParams();
		$this->oauth->finishClientAuthorization(true, $params);
	}

	public function actionToken()
	{
		$this->oauth->grantAccessToken();
	}

	public function actionTest()
	{
        if (Yii::app()->params['env'] === 'local')
        {
            //request iOS FS authorization code
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:81?r=oa2/auth');
            $postData = array(
                'client_id'=> 'iOSfireshield0001',
                'response_type'=>'code',
            );
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'wds_api_cookie');  //could be empty, but cause problems on some hosts
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'wds_api_cookies');  //could be empty, but cause problems on some hosts
            $headers = curl_exec($ch);
            //var_dump($headers);
            //die();
            //get auth code out of location header
            foreach(explode("\n", $headers) as $header)
            {
                $isLocationHeader = strpos($header, 'Location: fireshieldauth://authorization/?code=');
                if($isLocationHeader !== false)
                {
                    $authCode = trim(substr($header, strpos($header, '?code=')+6));
                    break;
                }
            }
            //echo '<br>Auth Code Recieved: '.$authCode.'<br>';
            //request token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:81?r=oa2/token');
            $postData = array(
                'grant_type'=> 'authorization_code',
                'client_id' => 'iOSfireshield0001',
                'client_secret'=> 'cf3d82b6-ffed-4321-9e72-bc8d11873714',
                'code'=> $authCode,
                'redirect_uri'=>'fireshieldauth://authorization/',
            );
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'wds_api_cookie');  //could be empty, but cause problems on some hosts
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'wds_api_cookies');  //could be empty, but cause problems on some hosts
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            $oAuth2Token = $result['access_token'];
            echo '<br>Access Token Recieved: '.$oAuth2Token.'<br>';

            //test api call (check a dev FS Carrier code)
            $ch = curl_init();
            $postData['access_token'] = $oAuth2Token;
            $postData['data'] = json_encode(array('data'=>array('carrierKey'=>'SFUX4ACV')));
            curl_setopt($ch,CURLOPT_URL, 'http://localhost:81?r=member/apiCheckCarrierCode');
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'wds_api_cookie');  //could be empty, but cause problems on some hosts
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'wds_api_cookies');  //could be empty, but cause problems on some hosts
            $result = curl_exec($ch);
            echo '<br>TEST Check Carrier Key API Call: '.var_export(json_decode($result, true), true);
        }
	}

    public function actionAuthRedirect()
    {
        if (isset($_GET['code']))
        {
            $json = json_encode(array(
                'auth_code' => $_GET['code']
            ));

            header('HTTP/1.1 ' . OAUTH2_HTTP_FOUND);
            header('Content-Type: application/json');
            header('Content-Length: ' . strlen($json));
            echo $json;
            exit;
        }
        else if (isset($_GET['error']))
        {
            $error = json_encode(array(
                'error' => $_GET['error']
            ));

            header('HTTP/1.1 ' . OAUTH2_HTTP_FOUND);
            header('Content-Type: application/json');
            header('Content-Length: ' . strlen($error));
            echo $error;
            exit;
        }
        else
        {
            header('HTTP/1.1 500 Internal Server Error');
            exit;
        }
    }
}
?>
