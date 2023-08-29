<?php

class m161222_155506_update_api_docs extends CDbMigration
{
	public function safeUp()
	{
        $this->truncateTable('api_documentation');
        
        //main API doc
        $main_doc = '## WDSAPI Developer Reference

---
### Table of Contents
* ####[Authentication (OAuth 2)](#authentication)
1. [Gathering Credentials](#creds)
2. [Getting an Auth Code](#authcode)
3. [Getting a Token](#token)
4. [Refreshing a Token](#refresh)

* ####[Making an API Call](#api-call)

### Authentication using OAuth 2 {#authentication}

All API calls require an Authentication Token which can be obtained using the following procedure which is in accordance with the OAuth 2 Standard. Click here for the full documentation on the **[OAuth 2 Standard][1]**

#####1.) Gather Credentials {#creds}
* API credentials can be found in the clients WDS Dashboard under the API main menu item and API Users sub-tab (look at the top center of this page to the right of the Documentation tab). If you do not see this area or credentials please contact your WDS representative for setup. You will need the Username(client_id), Client Secret, Redirect URI, and Scope to complete the authentication process.

#####2.) Get an Authentication Code {#authcode}
* An authentication code is first needed to further obtain a token for use. To obtain a authentication code a POST request needs to be made to the below endpoint with query string parameters sent in the body of the request: client_id, response_type, scope, and redirect_uri.  The only response_type currently available is "code".

* `POST` `https://dashboard.wildfire-defense.com/api/oa-2-auth`

* #####Ex. POST Body:
~~~~~~~~~~~~~~~~~~~~~~~~
client_id:my_client_id
response_type:code
scope:risk
redirect_uri:https://devdashboard.wildfire-defense.com/api/auth-redirect
~~~~~~~~~~~~~~~~~~~~~~~~

* #####OR as an encoded Query String:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
client_id=my_client_id&response_type=code&scope=risk_scope&redirect_uri=https%3A%2F%2Fdevdashboard.wildfire-defense.com%2Fapi%2Fauth-redirect
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

* The "code" is passed back as a GET query string param tacked onto the redirect_uri which your implementation will need to be able to follow as a header redirect from the authcode endpoint response. You can use your own redirect_uri if you would like (let WDS staff know so that we can setup your client user accordingly) or you can use the WDS supplied redirect URI (https://devdashboard.wildfire-defense.com/api/auth-redirect) by default which will pull the code from the GET param and put into an easy to use JSON object in the response body.

* #####Ex. Response:
~~~~~~~~~~~~~~~~
{
    "auth_code":"my-auth-code-12345566778899"
}
~~~~~~~~~~~~~~~~

* The auth code retrieved is used to get the access token in the next step.

* Authorization code expiration: *30 seconds*

#####3.) Get an Access Token {#token}
* Using the authentication code from the previous step along with the client credentials a POST request to the below token endpoint can now be made to get a token for use in API calls. A query string in the body of the request with the following parameters is required: client_id, client_secret, scope, redirect_uri, grant_type, and code.  The grant_type must be "authorization_code" when initially getting a token.

* `POST` `https://dashboard.wildfire-defense.com/api/oa-2-token`

* #####Ex. POST Body:
~~~~~~~~~~~~~~~~~~~~~~~
client_id:my_client_id
client_secret:c11111-5555-9999-11ff-999ffffggg777
scope:risk
redirect_uri:https://devdashboard.wildfire-defense.com/api/auth-redirect
grant_type:authorization_code
code:bd33333399999991dddddd
~~~~~~~~~~~~~~~~~~~~~~~
* #####OR as an encoded Query String
~~~~~~~~~~~~~~~~~~~~~~~
client_id=my_client_id&client_secret=c11111-5555-9999-11ff-999ffffggg777&scope=risk&redirect_uri=https%3A%2F%2Fdashboard.wildfire-defense.com%2Fapi%2Fauth-redirect&grant_type=authorization_code&code=bd33333399999991dddddd`
~~~~~~~~~~~~~~~~~~~~~~~
* The response will contain an access and refresh token along with expiration dates(unix timestamps) for both in JSON format.

* #####Ex. Response
~~~~~~~~~~~~~~~~~~~~
{
    "access_token": "99999988888xxxxxx55555zzzzzzzz",
    "expires": 1275796599,
    "scope": "risk",
    "refresh_token": "9999er55555er77777er",
    "refresh_expires": 1477002600
}
~~~~~~~~~~~~~~~~~~~~

* It is suggested to store the information received from this request in persistent storage, as it can be used re-used in subsequent requests.
* Access token expiration: *1 hour*
* Refresh token expiration: *14 days*

#####4.) Expired Access Token - Renewing an Access Token {#refresh}
* Before API calls are made, the expiration of a token should be checked and if it has expired then the refresh token should be used to get a new set. This can be done using the same token endpoint as above except using the refresh token and a refresh grant type instead. Required params for a refresh are: grant_type ( = refresh_token), client_id, client_secret, and refresh_token.

* `POST` `https://dashboard.wildfire-defense.com/api/oa-2-token`

* #####Ex. POST Body:
~~~~~~~~~~~~~~~~~~~~~
client_id:my_client_id
client_secret:c1111111-9999-9777-1414-55555eee6666
grant_type:refresh_token
refresh_token:b55555555efd000000b999999dfada
~~~~~~~~~~~~~~~~~~~~~

* #####OR as an Encoded Query String:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
grant_type=refresh_token&client_id=my_client_id&client_secret=cffffff-ffee-4444-9e88-bc1111111111&refresh_token=674fde754edf8765e84
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

* A new access token, refresh token, and respective exipiration dates will be returned in the same format as the initial get token response. This procedure should also be followed upon receiving a 401 Unauthorized (expired_token) response as per the OAuth 2 specifications.

* If your refresh_token is over 14 days old you will need do the authorization from the beginning.


### Making a WDS API Call {#api-call}

Using an access token from the above procedure you can now make calls to the WDS API.

* All WDS API functions use JSON for both request and response data.
* All API calls are HTTP POST requests made to a given URL Endpoint. In the example code a "base_url" is used which is typically:
`https://dashboard.wildfire-defense.com/`
* All API calls require the access_token as a query string parameter in the body of the POST request.
* #####All API calls that have incoming data require it as a POST variable named "data". Inside the "data" POST variable will contain a JSON payload generally of the form:
~~~~~~~~~~~~~~~~~~~~~
{
   "data":
    {
        "Something": "whatever",
        "somethingElse": "else",
        "someArray":{
            "arrayVal1": "val1",
            "arrayVal2": "val2",
        },
        "otherVar": "value"
    }
}
~~~~~~~~~~~~~~~~~~~~~

* #####Full Example POST body:
~~~~~~~~~~~~~~~~~~~~~~~
access_token:999aaaaaabbbbbb88888888cfd3b
data:{"data":{"lat":32.69,"lon":-117.18}}
~~~~~~~~~~~~~~~~~~~~~~~
* #####OR as a query string:
~~~~~~~~~~~~~~~~~~~~~~~
access_token=999aaaaaabbbbbb88888888cfd3b&data=%7B%22data%22%3A%7B%22lat%22%3A32.69%2C%22lon%22%3A-117.18%7D%7D
~~~~~~~~~~~~~~~~~~~~~~~

* All API call responses will be in JSON object that will always contain an "error" parameter which will be either 0 or 1 (0 being no error, 1 being error). If there is an error, it will also contain a technical error message called "errorMessage" for debugging use and a non-tech message called "errorFriendlyMessage" for use to display to the end user.

* #####Error Response Example:
~~~~~~~~~~~~~~~~~~~~~~~
{
  "error":1,
  "errorMessage": "ERROR: Something went wrong. Details: error code 999x",
  "errorFriendlyMessage": "There was an error communicating with the service provider. Please try again or contact support if problem persists."
}
~~~~~~~~~~~~~~~~~~~~~~~

* If successful and the function returns data, it will be included in the "data" parameter of the JSON object response.

* #####Successful Response Example:
~~~~~~~~~~~~~~~~~~~~~
{
    "error": 0,
    "data": {
        "result_val_1": 0.111,
        "result_val_2": "something",
        "some_result_array": [
              "foo",
              "bar",
              "blah"
        ],
        "final_val": 99
    }
}
~~~~~~~~~~~~~~~~~~~~~

[1]: http://tools.ietf.org/html/draft-ietf-oauth-v2-10
';
        
        $this->insert('api_documentation', array('name' => 'WDS API Developer Reference', 'docs' => $main_doc, 'active' => 1));

        $risk_coords_doc = '## Get WDS Risk With Coordinates

---

**`POST`** `https://dashboard.wildfire-defense.com/api/get-risk-with-coordinates-v-1`

Get a WDS risk response with a pair of WGS84 geographic coordinate system coordinates.

<br />
#### Request

**Example request**

~~~~~~~~~~~~~~~~~~~~~
{
    "data": {
        "lat": 41.990,
        "lon": -124.198
    }
}

~~~~~~~~~~~~~~~~~~~~~

<u>**Request Values**</u>

<table class="table table-striped table-bordered table-hover">
  <tr><th>Property</th><th>Description</th></tr>
  <tr><td>lat</td><td>Latitude in decimal degrees</td></tr>
  <tr><td>lon</td><td>Longitude in decimal degrees</td></tr>
</table>

<br />
#### Response

**Example response**

~~~~~~~~~~~~~~~~~~~~~
{
    "error": 0,
    "data": {
        "score_v": 0.98670957419389,
        "score_wds": 0.0059707491768271,
        "score_wds_text": "Moderate concern",
        "score_whp": 0.0060511718270343,
        "state_mean": 0.00064332,
        "std_dev": 0.00348408,
        "std_dev_text": "Within the 2nd above the mean"
    }
}
~~~~~~~~~~~~~~~~~~~~~

<u>**Response Values**</u>

<table class="table table-striped table-bordered table-hover">
  <tr><th>Property</th><th>Description</th></tr>
  <tr><td>score_v</td><td>WDS vulnerability score</td></tr>
  <tr><td>score_wds</td><td>WDS risk score</td></tr>
  <tr><td>score_wds_text</td><td>Human readable WDS risk score</td></tr>
  <tr><td>score_whp</td><td>WDS wildfire hazard potential score</td></tr>
  <tr><td>state_mean</td><td>State\'\'s mean risk score</td></tr>
  <tr><td>std_dev</td><td>Value it takes to be one standard deviation above or below the mean</td></tr>
  <tr><td>std_dev_text</td><td>Human readable risk score with standard deviation taken into account</td></tr>
</table>

<br />
#### Error

If the response works correctly, the `error` key in the response will hold a value of `0`.

If an error occurs, then the `error` key in the response will hold a value of `1`.

In addition, there will also be `errorMessage` and `errorFriendlyMessage` keys, giving more details about what error occurred.

**Example error response**

~~~~~~~~~~~~~~~~~~~~~
{
    "error": 1,
    "errorFriendlyMessage": "The state mean could not be loaded for these coordinates",
    "errorMessage": "ERROR: Something went wrong"
}
~~~~~~~~~~~~~~~~~~~~~
';
        $this->insert('api_documentation', array('name' => 'Method - Get WDS Risk With Coordinates', 'docs' => $risk_coords_doc, 'active' => 1));

        $risk_addr_doc = '## Get WDS Risk With Address

---

**`POST`** `https://dashboard.wildfire-defense.com/api/get-risk-with-address-v-1`

Get a WDS risk response with given address components.

<br />
#### Request

**Example request**

~~~~~~~~~~~~~~~~~~~~~
{
    "data": {
        "address": "3790 Sunday Court",
        "city": "Redding",
        "state": "CA",
        "zip": "96001"
    }
}
~~~~~~~~~~~~~~~~~~~~~

<u>**Request Values**</u>

<table class="table table-striped table-bordered table-hover">
  <tr><th>Property</th><th>Description</th></tr>
  <tr><td>address</td><td>Address component of requested risk address</td></tr>
  <tr><td>city</td><td>City component of requested risk address</td></tr>
  <tr><td>state</td><td>State component of requested risk address</td></tr>
  <tr><td>zip</td><td>Zipcode component of requested risk address</td></tr>
</table>

<br />
#### Response

**Example response**

~~~~~~~~~~~~~~~~~~~~~
{
    "error": 0,
    "data": {
        "score_v": 0.98670957419389,
        "score_wds": 0.0059707491768271,
        "score_wds_text": "Moderate concern",
        "score_whp": 0.0060511718270343,
        "state_mean": 0.00064332,
        "std_dev": 0.00348408,
        "std_dev_text": "Within the 2nd above the mean"
    }
}
~~~~~~~~~~~~~~~~~~~~~

<u>**Response Values**</u>

<table class="table table-striped table-bordered table-hover">
  <tr><th>Property</th><th>Description</th></tr>
  <tr><td>score_v</td><td>WDS vulnerability score</td></tr>
  <tr><td>score_wds</td><td>WDS risk score</td></tr>
  <tr><td>score_wds_text</td><td>Human readable WDS risk score</td></tr>
  <tr><td>score_whp</td><td>WDS wildfire hazard potential score</td></tr>
  <tr><td>state_mean</td><td>State\'\'s mean risk score</td></tr>
  <tr><td>std_dev</td><td>Value it takes to be one standard deviation above or below the mean</td></tr>
  <tr><td>std_dev_text</td><td>Human readable risk score with standard deviation taken into account</td></tr>
</table>

<br />
#### Error

If the response works correctly, the `error` key in the response will hold a value of `0`.

If an error occurs, then the `error` key in the response will hold a value of `1`.

In addition, there will also be `errorMessage` and `errorFriendlyMessage` keys, giving more details about what error occurred.

**Example error response**

~~~~~~~~~~~~~~~~~~~~~
{
    "error": 1,
    "errorFriendlyMessage": "The state mean could not be loaded for these coordinates",
    "errorMessage": "ERROR: Something went wrong"
}
~~~~~~~~~~~~~~~~~~~~~
';
        $this->insert('api_documentation', array('name' => 'Method - Get WDS Risk With Address', 'docs' => $risk_addr_doc, 'active' => 1));

        $java_ex = '### Java WDS API Code Example

~~~~~~~~~~~~~~~~~~~~~

import java.io.*;
import java.net.*;
import java.util.*;
import com.google.gson.Gson;

/**
* Example Usage of the WDSAPI Client Class
* Defines needed api credentials, instantiates
* the wdsapi class with them, and the makes a
* call to the wds risk method.
* NOTE: need to include gson (see details in class desc below)
*
* Compile:	javac -classpath .:gson-2.8.0.jar WDSAPIEx.java
* Run:		java -classpath .:gson-2.8.0.jar WDSAPIEx
*
* @author	Wildfire Defense Systems
* @version	1.0
* @since	2016-12-16
*/
public class WDSAPIEx
{
	/**
	* This is the main method which makes use of WDSAPI class.
	* @param args Unused.
	* @return Nothing. Outputs result from API call
	*/
	public static void main(String[] args)
	{
		try
		{
			String clientID = "<CLIENTID>";
			String clientSecret = "<CLIENTSECRET>";
			String redirectURI = "https://dashboard.wildfire-defense.com/api/auth-redirect";
			String scope = "risk";
			String apiBaseURL = "https://dashboard.wildfire-defense.com/";
			WDSAPI wdsAPI = new WDSAPI(clientID, clientSecret, redirectURI, scope, apiBaseURL);
			String result = wdsAPI.makeGetRiskAPICall(41.990, -124.198);
			System.out.println("Risk API Response: " + result + "\n");
		}
		catch (Exception e)
		{
			System.out.println("ERROR! Details: " + e.toString());
		}
	}
}

/**
* Desc:		WDS API Client Class with OAuth connector and RiskAPI method
* Notes:	Requires Google JSON lib (gson). https://github.com/google/gson
*			Dowload Link: http://repo1.maven.org/maven2/com/google/code/gson/gson/2.8.0/gson-2.8.0.jar
*
* @author	Wildfire Defense Systems
* @version	1.0
* @since	2016-12-16
*/
class WDSAPI
{
	private String token;
	private Long tokenExpiration;
	private String refreshToken;
	private Long refreshTokenExpiration;
	private String clientID;
	private String clientSecret;
	private String redirectURI;
	private String scope;
	private String apiBaseURL;
	private Gson gson;

	/**
	* This is the WDSAPI class constructor.
	* @param	wdsClientID OAuth2 Client ID
	* @param	wdsClientSecret OAuth2 Client Secret
	* @param	wdsRedirectURI OAuth2 redirect URI used for redirect after authentication
	* @param	wdsScope WDS API Scope
	* @param	wdsAPIBaseURL the base url used for oauth2 and api endpoints
	*/
	public WDSAPI(String wdsClientID, String wdsClientSecret, String wdsRedirectURI, String wdsScope, String wdsAPIBaseURL)
	{
		this.clientID = wdsClientID;
		this.clientSecret = wdsClientSecret;
		this.redirectURI = wdsRedirectURI;
		this.scope = wdsScope;
		this.apiBaseURL = wdsAPIBaseURL;

		//defaults
		this.token = null;
		this.tokenExpiration = null;
		this.refreshToken = null;
		this.refreshTokenExpiration = null;

		//json helper initialize
		this.gson = new Gson();

		//make initial authorization
		String authCode = this.getAuthCode();
		this.getToken("access", authCode);
	}

	//inner class used to decode OAuth2 auth json response
	private class AuthObj
	{
    	private String auth_code;
    }

    /**
	* Gets the OAuth2 authentication code to be used in the get token method
	*
	* @return authCode to be used to get the api authentication token
	*/
	private String getAuthCode()
	{
		String authCode = null;
		try
		{
			//setup post params
			Map<String,Object> authParams = new LinkedHashMap<>();
			authParams.put("client_id", this.clientID);
			authParams.put("response_type", "code");
			authParams.put("scope", this.scope);
			authParams.put("redirect_uri", this.redirectURI);
			byte[] authPostDataBytes = this.getPostDataBytes(authParams);
			//make post call to auth endpoint
			URL authEndpointURL = new URL(this.apiBaseURL + "api/oa-2-auth");
			String authCodeJSONStr = this.makePostRequest(authEndpointURL, authPostDataBytes);
			//decode auth call result to get auth code
			AuthObj authCodeObj = this.gson.fromJson(authCodeJSONStr, AuthObj.class);
			String authCode = authCodeObj.auth_code;

	        	//uncomment for debuging// System.out.print("Successfully Received AuthCode: " + authCode + "\n");
		}
		catch (Exception e)
		{
			System.out.println("ERROR! Details: " + e.toString());
		}

		return authCode;
	}

	//inner class used to decode oauth2 token JSON response
	private class TokenObj
	{
		private String access_token;
		private Long expires;
		private String refresh_token;
		private Long refresh_expires;
	}

	/**
	* Get OAuth2 Token for usage to make WDS API Calls (used to refresh the token as well if needed)
	*
	* @param type should be a String equal to either "access" or "refresh"
	* @param authCode should be a String containing the result of getAuthCode method
	* @return nothing but sets the class variables "token", "refreshToken", and their expirations for use in API calls
	*/
	private void getToken(String type, String authCode)
	{
		try
		{
			//setup token call post params
			Map<String,Object> tokenParams = new LinkedHashMap<>();
			if(type == "access")
			{
	        		tokenParams.put("grant_type", "authorization_code");
	        		tokenParams.put("client_id", this.clientID);
	        		tokenParams.put("client_secret", this.clientSecret);
	        		tokenParams.put("code", authCode);
	        		tokenParams.put("scope", this.scope);
	        		tokenParams.put("redirect_uri", this.redirectURI);
			}
			else if(type == "refresh")
			{
	        		tokenParams.put("grant_type", "refresh_token");
	        		tokenParams.put("client_id", this.clientID);
	        		tokenParams.put("client_secret", this.clientSecret);
	        		tokenParams.put("refresh_token", this.refreshToken);
			}
			byte[] tokenPostDataBytes = this.getPostDataBytes(tokenParams);

			//make post call to get token
			URL tokenEndpointURL = new URL(this.apiBaseURL + "api/oa-2-token");
			String tokenJSONStr = this.makePostRequest(tokenEndpointURL, tokenPostDataBytes);
			//decode token response json to get access_token, refresh_token, and expirations
			TokenObj tokenObj = this.gson.fromJson(tokenJSONStr, TokenObj.class);

			//set class vars for reference elsewhere
			this.token = tokenObj.access_token;
			this.tokenExpiration = tokenObj.expires;
			this.refreshToken = tokenObj.refresh_token;
			this.refreshTokenExpiration = tokenObj.refresh_expires;

			//uncomment for debuging// System.out.print("Successfully Received Access Token: " + this.token + " (expires: " + this.tokenExpiration.toString() + ")\n");
			//uncomment for debuging// System.out.print("Successfully Received Refresh Token: " + this.refreshToken + " (expires: " + this.refreshTokenExpiration.toString() + ")\n");
		}
		catch (Exception e)
		{
			System.out.println("ERROR! Details: " + e.toString());
		}
	}

	//internal class helper function to check if the token is expired and refresh it if not
	private void checkTokenExpiration()
	{
		//Current time plus 20 seconds (assuming the call may not actually be made exactly right this second)
		Long currTime = ((System.currentTimeMillis() + 20000) / 1000L) ;
		if(this.tokenExpiration < currTime)
		{
			System.out.print("Token has expired, getting a new one (tokenExpiration: " + this.tokenExpiration + ", currTime: " + currTime.toString() + ")\n");
			//if refresh token is expired then need to fully re-auth
			if(this.refreshTokenExpiration < currTime)
			{
				String authCode = this.getAuthCode();
				this.getToken("access", authCode);
			}
			else //use the refresh token to get a new access token
			{
				this.getToken("refresh", null);
			}
		}
	}

	//internal class used for setting up json encoding to risk api
	private class RiskObj
	{
		private int error;
		private double score_v;
		private double score_wds;
		private String score_wds_text;
		private double score_whp;
		private double state_mean;
		private double std_dev;
		private String std_dev_text;
	}

	//another internal class used for setting up json encoding to risk api
	private class RiskDataObj
	{
		private LatLonObj data;
	}

	//yet another internal class used for setting up json encoding to risk api
	private class LatLonObj
	{
		public double lat;
		public double lon;
	}

	/**
	* Make a call to the WDS Get Risk by Coordinates API Function
	*
	* @param lat latitude of property
	* @param lon longitude of property
	* @return result from get risk api call
	*/
	public String makeGetRiskAPICall(double lat, double lon)
	{
		String riskResponseStr = null;
		try
		{
			//make sure token is still active
			this.checkTokenExpiration();

			//Setup post params
			LatLonObj latLon = new LatLonObj();
			latLon.lat = lat;
			latLon.lon = lon;
			RiskDataObj getRiskData = new RiskDataObj();
			getRiskData.data = latLon;
			String latLonDataJSON = this.gson.toJson(getRiskData);
			Map<String,Object> apiParams = new LinkedHashMap<>();
			apiParams.put("access_token", this.token);
			apiParams.put("data", latLonDataJSON);
			byte[] apiPostDataBytes = this.getPostDataBytes(apiParams);
			//Make post call to Get Risk API
			URL apiEndpointURL = new URL(this.apiBaseURL + "api/get-risk-with-coordinates-v-1");
			riskResponseStr = this.makePostRequest(apiEndpointURL, apiPostDataBytes);
		}
		catch (Exception e)
		{
			System.out.println("ERROR! Details: " + e.toString());
		}
		return riskResponseStr;
	}

	/**
	* Helper function to get post params into byte array
	*
	* @param params a key value Map<String,Object> containing POST params to be encoded into a byte array
	* @return a byte[] array containing the post param data
	*/
	private byte[] getPostDataBytes(Map<String,Object> params)
	{
		byte[] returnByteArray = null;
		try
		{
			StringBuilder postData = new StringBuilder();
			for (Map.Entry<String,Object> param : params.entrySet())
			{
				if (postData.length() != 0) postData.append("&");
				postData.append(URLEncoder.encode(param.getKey(), "UTF-8"));
				postData.append("=");
				postData.append(URLEncoder.encode(String.valueOf(param.getValue()), "UTF-8"));
			}
			byte[] postDataBytes = postData.toString().getBytes("UTF-8");
	        	returnByteArray = postDataBytes;
		}
		catch (Exception e)
		{
			System.out.println("ERROR! Details: " + e.toString());
		}
		return returnByteArray;
	}

	/**
	* helper function to make a post http request
	*
	* @param url address to make the post request to
	* @param postDataBytes byte[] array of post data to send
	* @return string that contains the response of the call
	*/
	private String makePostRequest(URL url, byte[] postDataBytes)
	{
		String responseString = null;
		try
		{
			HttpURLConnection cnxn = (HttpURLConnection) url.openConnection();
			cnxn.setRequestMethod("POST");
			cnxn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
			cnxn.setRequestProperty("Content-Length", String.valueOf(postDataBytes.length));
			cnxn.setDoOutput(true);
			cnxn.getOutputStream().write(postDataBytes);

			BufferedReader responseReader = new BufferedReader(new InputStreamReader(cnxn.getInputStream(), "UTF-8"));

			StringBuilder responseStringBuilder = new StringBuilder();
			String line;
			while((line = responseReader.readLine()) != null)
			{
	        		responseStringBuilder.append(line);
			}

			responseString = responseStringBuilder.toString();
		}
		catch (Exception e)
		{
			System.out.println("ERROR! Details: " + e.toString());
		}
		return responseString;
	}
}


~~~~~~~~~~~~~~~~~~~~~
';
        $this->insert('api_documentation', array('name' => 'Java Example', 'docs' => $java_ex, 'active' => 1));

        $php_ex = '### PHP WDS API Code Example

**WDSAPIClient**

~~~~~~~~~~~~~~~~~~~~~
<?php

/**
 * The WDSAPI class performs requests that retrive auth codes, access tokens,
 * and data from the wdsapi
 */
class WDSAPI
{
    public $baseUrl;
    public $clientID;
    public $clientSecret;
    public $clientScope;
    public $redirectUri;

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
     * @return array
     */
    public function wdsApiGetRiskByCoords($lat, $lon, $accessToken)
    {
        $apiEndpoint = $this->baseUrl . "api/get-risk-with-coordinates-v-1";
        $data = array("lat"=>$lat, "lon"=>$lon);
        return $this->curlRequest($apiEndpoint, array(
            "access_token" => $accessToken,
            "data" => json_encode(array(
                "data" => $data
            ))
        ));
    }
}
~~~~~~~~~~~~~~~~~~~~~

**WDSAPIClient Usage**

~~~~~~~~~~~~~~~~~~~~~
<?php

include "WDSAPIClient.php";

$baseUrl = "https://dashboard.wildfire-defense.com/";
$clientID = "<client id>";
$clientSecret = "<client secret";
$clientScope = "<client scope>";
$redirectUri = $baseUrl . "api/auth-redirect";

$wdsapi = new WDSAPI(array(
    "baseUrl" => $baseUrl,
    "clientID" => $clientID,
    "clientSecret" => $clientSecret,
    "clientScope" => $clientScope,
    "redirectUri" => $redirectUri
));

$authcode = $wdsapi->getAuthCode();

$accessToken = $wdsapi->getAccessToken($authcode);

$result = $wdsapi->wdsApiGetRiskByCoords(32.69, -117.18, $accessToken);
~~~~~~~~~~~~~~~~~~~~~
';
        $this->insert('api_documentation', array('name' => 'PHP Example', 'docs' => $php_ex, 'active' => 1));

        $python_ex = '#### Python WDS API Code Example

**WDSAPIClient**

~~~~~~~~~~~~~~~~~~~~~
import requests
import json

class WDSAPIConnection(object):
    """The WDSAPIConnection class reforms HTTP/1.1 post requests and self terminates

    Attributes:
        url (str): Endpoint URL
        payload (dict): data to post to request
        response (obj): object created by requests module request
        headers (dict): headers to post to request

    """

    def __init__(self, url, payload):
        self.url = url
        self.payload = payload
        self.response = None
        self.headers = {
            "content-type": "application/x-www-form-urlencoded",
            "cache-control": "no-cache"
        }

    def __enter__(self):
        self.response = requests.request("POST", self.url, data=self.payload, headers=self.headers)
        return self.response

    def __exit__(self, exc_type, exc_val, exc_tb):
        self.response.connection.close()

class WDSAPI(object):
    """The WDSAPI class performs requests that retrive auth codes, access tokens, and data from the wdsapi.

    Attributes:
        baseUrl (str): Base URL
        clientID (str): API client ID
        clientSecret (str): API client secret
        clientScope (str): API client scope
        redirectUri (str): API client redirect uri
    """

    def __init__(self, baseUrl, clientID, clientSecret, clientScope, redirectUri):
        self.baseUrl = baseUrl
        self.clientID = clientID
        self.clientSecret = clientSecret
        self.clientScope = clientScope
        self.redirectUri = redirectUri

    def getAuthCode(self):
        """Perform request to get auth code

        Returns:
            string: The return value.

        """
        authURL = self.baseUrl + "api/oa-2-auth"
        payload = {
            "client_id": self.clientID,
            "response_type": "code",
            "scope": self.clientScope
        }
        with WDSAPIConnection(authURL, payload) as response:
            responseText = json.loads(response.text)
            if responseText.get("auth_code"):
                return responseText.get("auth_code")
            if responseText.get("error"):
                print ("There was an error getting auth code: %s" % responseText.get("error"))
            return None

    def getAccessToken(self, authCode):
        """Perform request to get access token

        Args:
            authCode: api auth code

        Returns:
            string: The return value.

        """
        tokenURL = self.baseUrl + "api/oa-2-token"
        payload = {
            "grant_type": "authorization_code",
            "client_id": self.clientID,
            "client_secret": self.clientSecret,
            "code": authCode,
            "redirect_uri": self.redirectUri,
            "scope": self.clientScope
        }
        with WDSAPIConnection(self.baseUrl, querystring, payload) as response:
            responseText = json.loads(response.text)
            if responseText.get("access_token"):
                return responseText.get("access_token")
            if responseText.get("error"):
                print ("There was an error getting access token: %s" % responseText.get("error"))
            return None

    def wdsApiGetRiskWithCoordsCall(self, accessToken, lat, lon):
        """Perform wdsapi request

        Args:
            lat: latitude for api call
            lon: longitude for api call
            accessToken: api access token

        Returns:
            dict: API return

        """
        riskByCoordsUrl = self.baseUrl + "api/get-risk-with-coordinates-v-1"
        payload = {
            "access_token": accessToken,
            "data": json.dumps({ "data": {"lat":lat,"lon":lon})
        }
        with WDSAPIConnection(self.baseUrl, payload) as response:
            try:
                return json.loads(response.text)
            except ValueError, e:
                return None
~~~~~~~~~~~~~~~~~~~~~

**WDSAPIClient Usage**

~~~~~~~~~~~~~~~~~~~~~
import WDSAPI

baseUrl = "https://dashboard.wildfire-defense.com/"
clientID = "<client id>"
clientSecret = "<client secret>"
clientScope = "risk"
clientRedirectUri = baseUrl + "api/auth-redirect"

# Creating new instance of WDSAPI class
wdsAPI = WDSAPI.WDSAPI(baseUrl, clientID, clientSecret, clientScope, clientRedirectUri)

# Getting auth code
authCode = wdsAPI.getAuthCode()

# Getting access token
accessToken = wdsAPI.getAccessToken(authCode)

# Performing the api call
data = wdsAPI.wdsApiGetRiskWithCoordsCall(accessToken, 32.69, -117.18)

print data
~~~~~~~~~~~~~~~~~~~~~
';
        $this->insert('api_documentation', array('name' => 'Python Example', 'docs' => $python_ex, 'active' => 1));

        $csharp_ex = '#### C# WDS API Code Example

~~~~~~~~~~~~~~~~~~~~~

using Newtonsoft.Json;
using System;
using System.IO;
using System.Net;
using System.Text;

class WDSAPI
{
    private string baseUrl { get; set; }
    private string clientID { get; set; }
    private string clientSecret { get; set; }
    private string clientScope { get; set; }
    private string redirectUri { get; set; }

    public WDSAPI()
    {
        baseUrl = "https://dashboard.wildfire-defense.com/";
        clientID = "<CLIENT_ID>";
        clientSecret = "<CLIENT_SECRET>";
        clientScope = "risk";
        redirectUri = "https://dashboard.wildfire-defense.com/api/auth-redirect";
    }

    /// <summary>
    /// First step of oauth - get the auth code
    /// </summary>
    /// <returns>
    /// The auth code string to be used to get the access token
    /// </returns>
    public string GetAuthCode()
    {
        string authendpoint = baseUrl + "api/oa-2-auth";
        string postData = "client_id=" + clientID;
        postData += "&response_type=code";
        postData += "&scope=" + clientScope;

        var data = Encoding.ASCII.GetBytes(postData);
        string redirectUri = GetRedirectUri(authendpoint, data);
        dynamic response = MakeCall(redirectUri, data);

        return response.auth_code;
    }

    /// <summary>
    /// Second step of oath - get the access token which can be used for making subsequent calls
    /// </summary>
    /// <param name="authCode">The authcode to authenticate with</param>
    /// <returns>
    /// An object containing the following token information: access_token, expires, scope, refresh_token, refresh_expires
    /// </returns>
    public dynamic GetAccessToken(string authCode)
    {
        string tokenEndpoint = baseUrl + "api/oa-2-token";

        string postData = "grant_type=authorization_code";
        postData += "&client_id=" + clientID;
        postData += "&client_secret=" + clientSecret;
        postData += "&code=" + authCode;
        postData += "&redirect_uri=" + redirectUri;
        postData += "&scope=" + clientScope;
        byte[] data = Encoding.ASCII.GetBytes(postData);

        return MakeCall(tokenEndpoint, data);
    }

    /// <summary>
    /// Get Risk By Coordinates WDS API Call
    /// </summary>
    /// <param name="lat">Latitude</param>
    /// <param name="lon">Longitude</param>
    /// <param name="accessToken">The access token received</param>
    /// <returns>
    /// The response object containing the risk values for these coordinates. See specifics in api doc for this method
    /// </returns>
    public dynamic WdsAPIGetRiskByCoordsCall(float lat, float lon, string accessToken)
    {
        string apiData = "{\"data\":{\"lat\":" + lat + ",\"lon\":" + lon + "}}";
        string postData = "access_token=" + accessToken;
        postData += "&data=" + apiData;
        byte[] data = Encoding.ASCII.GetBytes(postData);
        string endpoint = baseUrl + route;

        return MakeCall(endpoint, data);
    }


    /// <summary>
    /// This generic function takes an endpoint and post data, creates a call and returns the response
    /// </summary>
    /// <param name="endpoint">The url to make the call to</param>
    /// <param name="data">The post data to send</param>
    ///<returns>
    /// The json response which is converted to an object
    /// </returns>
    private dynamic MakeCall(string endpoint, byte[] data)
    {
        dynamic returnResponse;
        HttpWebRequest request = CreateRequest(endpoint, data);

        using (Stream stream = request.GetRequestStream())
        {
            stream.Write(data, 0, data.Length);
        }

        try
        {
            HttpWebResponse response = (HttpWebResponse)request.GetResponse();
            string responseString = new StreamReader(response.GetResponseStream()).ReadToEnd();
            returnResponse = JsonConvert.DeserializeObject(responseString);
        }
        catch
        {
            returnResponse = null;
        }

        return returnResponse;
    }

    /// <summary>
    /// Initialize and configure the httpwebrequest used to handle the calls
    /// </summary>
    /// <param name="uri">The endpoint to call</param>
    /// <param name="data">The post data to send</param>
    ///<returns>
    /// Request object (not the contents of the call)
    /// </returns>
    private HttpWebRequest CreateRequest(string uri, byte[] data)
    {
        HttpWebRequest request = (HttpWebRequest)WebRequest.Create(uri);
        request.AllowAutoRedirect = false;
        request.KeepAlive = false;
        request.Method = "POST";
        request.ContentType = "application/x-www-form-urlencoded";
        request.ContentLength = data.Length;

        return request;
    }

    /// <summary>
    /// For endpoints that use forwarding, manually get the link and create the 2nd call
    /// </summary>
    /// <param name="endpoint">The desired endpoint in which to call</param>
    /// <param name="data">The post data</param>
    ///<returns>
    /// The forwarding uri
    /// </returns>
    private string GetRedirectUri(string endpoint, byte[] data)
    {
        dynamic returnResponse;
        HttpWebRequest request = this.CreateRequest(endpoint, data);

        using (var stream = request.GetRequestStream())
        {
            stream.Write(data, 0, data.Length);
        }

        try
        {
            HttpWebResponse response = (HttpWebResponse)request.GetResponse();
            string responseString = new StreamReader(response.GetResponseStream()).ReadToEnd();
            returnResponse = response.Headers["Location"];
        }
        catch
        {
            returnResponse = null;
        }
        return returnResponse;
    }
}

~~~~~~~~~~~~~~~~~~~~~


**WDSAPIClient Usage**

~~~~~~~~~~~~~~~~~~~~~

WDSAPI wdsAPI = new WDSAPI();
string authCode = wdsAPI.GetAuthCode();
dynamic token = wdsAPI.GetAccessToken(authCode);

var result = wdsAPI.WdsAPIGetRiskByCoordsCall(32.69, -117.18, token);

~~~~~~~~~~~~~~~~~~~~~
';
        $this->insert('api_documentation', array('name' => 'C# Example', 'docs' => $csharp_ex, 'active' => 1));
	}

	//public function safeDown()
	//{
	//}
	
}