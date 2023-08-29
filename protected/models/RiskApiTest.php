<?php

/**
 * The WDSAPI class performs requests that retrive auth codes, access tokens,
 * and data from the wdsapi
 */
class WDSAPIClientTest
{
    public $baseUrl;
    public $clientID;
    public $clientSecret;
    public $clientScope;
    public $redirectUri;

    const API_RISK_COORDINATES_ROUTE = 'api/get-risk-with-coordinates-v-1';
    const API_RISK_ADDRESS_ROUTE = 'api/get-risk-with-address-v-1';

    /**
     * Constructor
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        $difference = array_diff(array_keys($params), array("baseUrl","clientID","clientSecret","clientScope","redirectUri"));

        if (count($difference) > 0)
        {
            throw new Exception(sprintf("Params missing from WDSAPI class: %s", var_export($difference, true)));
        }

        extract($params);

        $this->baseUrl = $baseUrl;
        $this->clientID= $clientID;
        $this->clientSecret = $clientSecret;
        $this->clientScope = $clientScope;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Performs api post request
     * @param string $urlEndpoint
     * @param array $postData
     * @return array
     */
    private function curlRequest($urlEndpoint, $postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlEndpoint);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ));

        $result = curl_exec($ch);

        $ch_errorno = curl_errno($ch);
        $ch_error = curl_error($ch);

        if ($ch_error)
        {
            die("There was a cURL error with error num: " . $ch_errorno . ".  Error: " . $ch_error);
        }

        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * Retrieves wdsapi auth code
     * @return boolean|string
     */
    public function getAuthCode()
    {
        $authEndpoint = $this->baseUrl . "api/oa-2-auth";

        $jsonResult = $this->curlRequest($authEndpoint, array(
            "client_id" => $this->clientID,
            "response_type" => "code",
            "scope" => $this->clientScope
        ));

        if (!isset($jsonResult["auth_code"]))
        {
            return false;
        }

        return $jsonResult["auth_code"];
    }

    /**
     * Retrieves wdsapi access token
     * @return boolean|string
     */
    public function getAccessToken($authCode)
    {
        $tokenEndpoint = $this->baseUrl . "api/oa-2-token";

        $jsonResult = $this->curlRequest($tokenEndpoint, array(
            "grant_type" => "authorization_code",
            "client_id" => $this->clientID,
            "client_secret" => $this->clientSecret,
            "code" => $authCode,
            "redirect_uri" => $this->redirectUri,
            "scope" => $this->clientScope,
        ));

        if (!isset($jsonResult["access_token"]))
        {
            return false;
        }

        return $jsonResult["access_token"];
    }

    /**
     * Performs WDS API Get Risk by Coordinates call
     * @param float $lat
     * @param float $lon
     * @param string $accessToken
     * @return array
     */
    public function wdsApiGetRiskByCoords(float $lat, float $lon, string $accessToken)
    {
        return $this->curlRequest($this->baseUrl . self::API_RISK_COORDINATES_ROUTE, array(
            "access_token" => $accessToken,
            "data" => json_encode(array(
                "data" => array(
                   "lat" => $lat,
                   "lon" => $lon
               )
            ))
        ));
    }

    /**
     * Performs WDS API Get Risk by address call
     *
     * $addressComponents array must have the following keys:
     * - address
     * - city
     * - state
     * - zip
     *
     * @param array $addressComponents
     * @param string $accessToken
     * @return array
     */
    public function wdsApiGetRiskByAddress(array $addressComponents, string $accessToken)
    {
        $difference = array_diff(array_keys($addressComponents), array("address","city","state","zip"));

        if (count($difference) > 0)
        {
            throw new Exception(sprintf("Params missing from %s: %s", __METHOD__, var_export($difference, true)));
        }

        extract($addressComponents);

        return $this->curlRequest($this->baseUrl . self::API_RISK_ADDRESS_ROUTE, array(
            "access_token" => $accessToken,
            "data" => json_encode(array(
                "data" => array(
                    "address" => $address,
                    "city" => $city,
                    "state" => $state,
                    "zip" => $zip
               )
            ))
        ));
    }
}

class RiskApiTest
{
    /**
     * Summary of executeTest
     */
    public static function executeTest()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $baseUrl = Yii::app()->params["wdsfireBaseUrl"] . "/";
        $clientID = null;
        $clientSecret = null;
        $clientScope = "risk";
        $redirectUri = $baseUrl . "api/auth-redirect";

        if (Yii::app()->params["env"] !== "pro")
        {
            $clientID = "oauth_risk_test";
            $clientSecret = "c1292848-9e35-97b1-14fd-5981b65e5f6636";
        }
        else
        {
            $clientID = "oauth_risk";
            $clientSecret = "3a7eb715-6bc9-4371-9a50-b071c23f63b936";
        }

        $exists = User::model()->exists(array(
            'condition' => 'username = :username',
            'params' => array(':username' => $clientID)
        ));

        if ($exists === false)
        {
            return <<<TEXT
Expecting to find oauth2 user with:

    clientID:     "{$clientID}"
    clientSecret: "{$clientSecret}"
    scope:        "{$clientScope}"
    redirectUri:  "{$redirectUri}"

Could not find!
TEXT;
        }

        $timeStart = microtime(true);

        $returnText = "";

        $returnText .= "\n";
        $returnText .= "-------STARTING RISK API TEST------------------------";
        $returnText .= "\n\n";

        $returnText .= "Using oauth2 user information:

    clientID:     \"{$clientID}\"
    clientSecret: \"{$clientSecret}\"
    scope:        \"{$clientScope}\"
    redirectUri:  \"{$redirectUri}\"\n\n";

        $returnText .= "oauth2 user can be switch from 'live' <--> 'test' by editing that user.\n\n";

        $returnText .= "Risk coordinates api route: \"" . WDSAPIClientTest::API_RISK_COORDINATES_ROUTE . "\"\n";
        $returnText .= "Risk address api route:     \"" . WDSAPIClientTest::API_RISK_ADDRESS_ROUTE . "\"\n\n";

        $wdsapi = new WDSAPIClientTest(array(
            "baseUrl" => $baseUrl,
            "clientID" => $clientID,
            "clientSecret" => $clientSecret,
            "clientScope" => $clientScope,
            "redirectUri" => $redirectUri
        ));

        $returnText .= "Getting auth code: ";

        $authcode = $wdsapi->getAuthCode();

        $returnText .= "{$authcode}\n";
        $returnText .= "Getting access token: ";

        $accessToken = $wdsapi->getAccessToken($authcode);

        $returnText .= "{$accessToken}\n\n";
        $returnText .= "Performing call to {$baseUrl}api/get-risk-with-coordinates-v-1\n";
        $returnText .= "Coordiantes: 32.69, -117.18\n\n";

        $result1 = $wdsapi->wdsApiGetRiskByCoords(32.69, -117.18, $accessToken);

        array_walk_recursive($result1, function(&$value) {
            if (is_float($value) === true) {
                $value = number_format($value, 8);
            }
        });

        $returnText .= print_r($result1, true);
        $returnText .= "\n";
        $returnText .= "Performing call to {$baseUrl}api/get-risk-with-address-v-1\n";
        $returnText .= "Address: 201 Evergreen Dr, Bozeman, Montana 59715\n\n";

        $result2 = $wdsapi->wdsApiGetRiskByAddress(array(
            "address" => "201 Evergreen Drive",
            "city" => "Bozeman",
            "state" => "MT",
            "zip" => "59715"
        ), $accessToken);

        array_walk_recursive($result2, function(&$value) {
            if (is_float($value) === true) {
                $value = number_format($value, 8);
            }
        });

        $returnText .= print_r($result2, true);

        $returnText .= "\n";
        $returnText .= "-------FINSHED RISK API TEST IN " . round(microtime(true) - $timeStart, 2) . " SECONDS------------------------";
        $returnText .= "\n";

        return $returnText;
    }
}