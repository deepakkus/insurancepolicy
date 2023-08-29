<?php

/**
 * Helper functions for WDS API calls.
 *
 * If Oauth2 validation fails, user will get return headers of:
 *
 * HTTP/1.1 400/401/403 Unauthorized
 * WWW-Authenticate: OAuth realm='', error='', error_description='', scope=''
 */
class WDSAPI
{
    const SCOPE_DASH = 'dash';
    const SCOPE_FIRESHIELD = 'fireshield';
    const SCOPE_RISK = 'risk';
    const SCOPE_USAAENROLLMENT = 'usaaenrollment';
    const SCOPE_ENGINE = 'engine';
    const WDS_PRO = 'pro';
    /**
     * Checks the API key and returns a data array if all is well.
     * @return boolean accessGranted
     */
    static function checkAccessToken($scope, $checkHeaders = false)
    {

        $accessGranted = true;
        $oauth = new YOAuth2();

        if (!$oauth->verifyAccessToken($scope))
        {
            $accessGranted = false;
        }

        if ($accessGranted && $checkHeaders)
        {
            $headers = getallheaders();

            if (isset($headers['Api-Key']) && $headers['Api-Key'] != 'FSAPI-510fe7899f7402.96325482')
            {
                $accessGranted = false;
            }
        }

        return $accessGranted;
    }

    /**
     * Echos the given results array as JSON.
     * @param array $returnArray
     */
    static function echoResultsAsJson($returnArray)
    {
        header("Content-Type: text/json");
		echo json_encode($returnArray, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * Helper to return a JSON-formatted error message.
     * @param string $errorMessage Error message for developers
     * @param string $friendlyMessage Error message for end users
     * @param integer $errorCode (0 = success, >1 = error)
     */
    static function echoJsonError($errorMessage, $friendlyMessage = NULL, $errorCode = 1)
    {
        if (!isset($friendlyMessage))
        {
            $friendlyMessage = "There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.";
        }

		$returnArray = array();
        $returnArray['error'] = $errorCode;
        $returnArray['errorFriendlyMessage'] = $friendlyMessage;
        $returnArray['errorMessage'] = $errorMessage;

        ErrorLog::model()->add($errorMessage);

		self::echoResultsAsJson($returnArray);
    }

    /**
     * Retrieves the data input JSON and decodes it into an array for the API call.
     * @param array &$data reference variable to pass the data back in
     * @param array $requiredFields (Optional) required fields
     * @return boolean indicates whether or not the data was successfully retrieved.
     */
    static function getInputDataArray(&$data, $requiredFields = NULL)
    {
        // Make sure there is POST or GET data.
        if (!filter_input(INPUT_POST, 'data') && !filter_input(INPUT_GET, 'data'))
        {
            self::echoJsonError('ERROR: No data POST variable was sent');
            return NULL;
        }

        if (filter_input(INPUT_POST, 'data'))
            $data = json_decode(filter_input(INPUT_POST, 'data'), true);
        else
            $data = json_decode(filter_input(INPUT_GET, 'data'), true);

        //Problem with the json object
        if (json_last_error())
        {
            self::echoJsonError('ERROR: Improper json structure');
            return NULL;
        }

        $data = $data['data'];

        if (isset($requiredFields))
        {
            $errorMessage = self::validateRequiredFields($requiredFields, $data);

            if (isset($errorMessage))
            {
                self::echoJsonError($errorMessage);
                return NULL;
            }
        }

        return isset($data);
    }

    /**
     * Checks a data array for missing required fields.
     * @param array $requiredFields required field names that must exist in the data
     * @param array $data the data to validate
     * @return string error message if a field was not found
     */
    static function validateRequiredFields($requiredFields, $data)
    {
        $errorMessage = NULL;

        foreach ($requiredFields as $requiredField)
        {
            $found = FALSE;

            // Iterate over the "leaves" of the array, in case of a multi-dimensional array.
            // This assumes the required field key names are unique!
            foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($data)) as $key => $value)
            {
                if ($requiredField == $key && isset($value))
                {
                    $found = TRUE;
                    break;
                }
            }

            if (!$found)
            {
                $errorMessage = "ERROR: the required attribute '$requiredField' is missing or not set!";
                break;
            }
        }

        return $errorMessage;
    }

    /**
     * Gets a formatted string of a model's errors array.
     * @param mixed $model
     * @return string formatted error message
     */
    static function getFormattedErrors($model)
    {
        $errorMessage = '';

        $errors = $model->getErrors();

        if (isset($errors))
        {
            foreach ($errors as $attribute => $attributeErrors)
            {
                foreach ($attributeErrors as $value)
                {
                    $errorMessage .= "$value";
                }
            }
        }

        return $errorMessage;
    }

    /**
     * Get instance of user using access token submitted with request
     * @return User|null
     */
    static function getApiUserFromAccessToken()
    {
        $accessToken = '';

        if (isset($_POST[OAUTH2_TOKEN_PARAM_NAME]))
            $accessToken = $_POST[OAUTH2_TOKEN_PARAM_NAME];

        if (isset($_GET[OAUTH2_TOKEN_PARAM_NAME]))
            $accessToken = $_GET[OAUTH2_TOKEN_PARAM_NAME];

        $criteria = new CDbCriteria;
        $criteria->select = array('id','client_id','api_mode');
        $criteria->condition = 'username = ( SELECT client_id FROM oa2_tokens WHERE oauth_token = :access_token )';
        $criteria->params['access_token'] = $accessToken;

        return User::model()->find($criteria);
    }
}
