<?php

/**
 * Uses ESRI's web service to geocode an address. Returns the location (match) type and coordinates
 * 
 * API Reference: https://developers.arcgis.com/rest/geocode/api-reference/overview-world-geocoding-service.htm
 * Credits Used Reference: http://www.esri.com/SOFTWARE/ARCGIS/ARCGISONLINE/CREDITS
 * 
 * 
 * ArcGIS Account Management (credits) : https://wds.maps.arcgis.com/home/organization.html
 * ArcGIS Account Management (liscences) : https://my.esri.com/#/organization-profile
 *
 * Example of ESRI geocode response:
 * {
 *     "spatialReference": {
 *         "wkid": 4326,
 *         "latestWkid": 4326
 *     },
 *     "locations": [
 *         {
 *             "name": "514 N Black Ave, Bozeman, Montana, 59715",
 *             "extent": {
 *                 "xmin": -111.036823,
 *                 "ymin": 45.683493,
 *                 "xmax": -111.034823,
 *                 "ymax": 45.685493
 *             },
 *             "feature": {
 *                 "geometry": {
 *                     "x": -111.03582190656,
 *                     "y": 45.684493092084
 *                 },
 *                 "attributes": {
 *                     "Score": 85.23,
 *                     "Match_addr": "514 N Black Ave, Bozeman, Montana, 59715",
 *                     "Addr_type": "PointAddress",
 *                     "Side": "R"
 *                 }
 *             }
 *         }
 *     ]
 * }
 *
 * Example of invalid token response:
 * {
 *     "error": {
 *         "code": 498,
 *         "message": "Invalid Token",
 *         "details": []
 *     }
 * }
 * 
 * Example of GeocodeESRI return:
 * {
 *     "error": 0,
 *     "address_formatted": "29619 Double Eagle Cir, Fair Oaks Ranch, Texas, 78015",
 *     "location_type": "StreetAddress",
 *     "location_score": 90.92,
 *     "geometry": {
 *         "lat": 29.746823144873,
 *         "lon": -98.649672232787
 *     },
 *     "error_message": ""
 * }
 */
class GeocodeESRI
{
    const WDS_ESRI_CLIENT_ID = 'w2PUeLvthMShAhe6';
    const WDS_ESRI_CLIENT_SECRET = 'afe18829337a4b15bc90baec0c6a14be';

    const WDS_ESRI_TOKEN_BASE_URL = 'https://www.arcgis.com/sharing/rest/oauth2/token?';
    const WDS_ESRI_GEOCODE_BASE_URL = 'http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?';

    // Max allowed token expiration is 14 days (measured in minutes)
    const WDS_ESRI_TOKEN_EXPIRATION_14DAYS = '20160';
    const WDS_ESRI_TOKEN__EXPIRATION_1DAY = '1440';
    const WDS_ESRI_TOKEN__EXPIRATION_1HR = '60';
    const WDS_ESRI_TOKEN__EXPIRATION_1MIN = '1';

    /**
     * Generate a new ESRI access token for production geocode requests.
     * Not using the '$expiresInSeconds' variable because we are simply test for an error response on geocode.
     * @return string
     */
    private static function generateNewAccessToken()
    {
        $url = self::WDS_ESRI_TOKEN_BASE_URL . http_build_query(array(
            'client_id' => self::WDS_ESRI_CLIENT_ID,
            'client_secret' => self::WDS_ESRI_CLIENT_SECRET,
            'grant_type' => 'client_credentials',
            'expiration' => self::WDS_ESRI_TOKEN__EXPIRATION_1DAY,
            'f' => 'json'
        ));

        $results = json_decode(CurlRequest::getRequest($url));

        $accessToken = $results->access_token;
        $expiresInSeconds = $results->expires_in;

        return $accessToken;
    }

    /**
     * Get the ESRI access token from session.
     * If the session variable does not expires, generate a new one.
     * @return string
     */
    private static function getAccessToken()
    {
        $accessToken = '';

        if (isset($_SESSION['esri_access_token']))
        {
            $accessToken = $_SESSION['esri_access_token'];
        }
        else 
        {
            $accessToken = self::generateNewAccessToken();
            $_SESSION['esri_access_token'] = $accessToken;
        }

        return $accessToken;
    }

    /**
     * Generate a url endpoint to send as a geocoding request.
     * To be used for permanent data storeage.
     * @param string $address 
     * @param string $accessToken 
     * @return string
     */
    private static function generateRequestUrlPro($address, $accessToken)
    {
        return self::WDS_ESRI_GEOCODE_BASE_URL . http_build_query(array(
            'text' => $address,
            'outFields' => 'Score,Match_addr,Addr_type,Side',
            'outSr' => '4326',
            'maxLocations' => '1',
            'forStorage' => 'true',
            'token' => $accessToken,
            'f' => 'json'
        ));
    }

    /**
     * Generate a url endpoint to send as a geocoding request.
     * @param string $address
     * @return string
     */
    private static function generateRequestUrlDev($address)
    {
        return self::WDS_ESRI_GEOCODE_BASE_URL . http_build_query(array(
            'text' => $address,
            'outFields' => 'Score,Match_addr,Addr_type,Side',
            'outSr' => '4326',
            'maxLocations' => '1',
            'f' => 'json'
        ));
    }

    /**
     * Get location data from the ESRI geocoding service.
     * This method is used for permanent data storage and will consume ESRI credits.
     * @param string $address 
     * @param integer $attempt (optional) 
     * @return array
     */
    public static function getLocationPro($address, $attempt = 0)
    {
        $accessToken = self::getAccessToken();

        $url = self::generateRequestUrlPro($address, $accessToken);
        $results = json_decode(CurlRequest::getRequest($url));

        $returnArray = array();

        // An error occurred, probably expired token
        if (isset($results->error))
        {
            // Invalid token, documented error codes are as follows:
            // https://developers.arcgis.com/rest/geocode/api-reference/geocoding-service-output.htm#ESRI_SECTION2_8CBF0ACE9919482384ED1EF4D4E1441D
            if ($results->error->code == 498 || $results->error->code == 499)
            {
                // Removing old token, re-calling function
                unset($_SESSION['esri_access_token']);

                if ($attempt > 10)
                {
                    $returnArray['error'] = 1;
                    $returnArray['error_message'] = "10 geocode attempts reached.\nThere may be an ESRI token issue.";
                    Yii::log('ERROR GEOCODING WITH PRODUCTION ESRI, 10 geocode attempts reached.  There may be an ESRI token issue.', CLogger::LEVEL_ERROR, __METHOD__);
                    return $returnArray;
                }

                return self::getLocationPro($address, $attempt + 1);
            }
            // Log unknown error
            else
            {
                if (isset($results->error->code, $results->error->message, $results->error->details) && is_array($results->error->details))
                {
                    Yii::log('ERROR GEOCODING WITH PRODUCTION ESRI, error code: ' . $results->error->code, CLogger::LEVEL_ERROR, __METHOD__);
                    Yii::log('ERROR GEOCODING WITH PRODUCTION ESRI, error message: ' . $results->error->message, CLogger::LEVEL_ERROR, __METHOD__);
                    Yii::log('ERROR GEOCODING WITH PRODUCTION ESRI, error details: ' . var_export($results->error->details, true)  , CLogger::LEVEL_ERROR, __METHOD__);
                    Yii::log('ERROR GEOCODING WITH PRODUCTION ESRI, address used: ' . $address, CLogger::LEVEL_ERROR, __METHOD__);
                    $returnArray['error'] = 1;
                    $returnArray['error_message'] = 'Unknown error was found with message ' . $results->error->message;
                }
            }
        }
        else
        {
            if (isset($results->locations) && count($results->locations) && isset($results->locations[0]->feature->geometry))
            {
                $result = $results->locations[0];

                $returnArray['error'] = 0;
                $returnArray['address_formatted'] = $result->feature->attributes->Match_addr;
                $returnArray['location_type'] = $result->feature->attributes->Addr_type;
                $returnArray['location_score'] = $result->feature->attributes->Score;
                $returnArray['geometry'] = array(
                    'lat' => $result->feature->geometry->y,
                    'lon' => $result->feature->geometry->x
                );
                $returnArray['error_message'] = '';
            }
            else
            {
                $returnArray['error'] = 1;
                $returnArray['error_message'] = 'No results were found.';
            }
        }

        return $returnArray;
    }

    /**
     * Get location data from the ESRI geocoding service.
     * @param string $address
     * @return array
     */
    public static function getLocationDev($address)
    {
        $url = self::generateRequestUrlDev($address);
        $results = json_decode(CurlRequest::getRequest($url));
        $returnArray = array();

        if (count($results->locations) && isset($results->locations[0]->feature->geometry))
        {
            $result = $results->locations[0];

            $returnArray['error'] = 0;
            $returnArray['address_formatted'] = $result->feature->attributes->Match_addr;
            $returnArray['location_type'] = $result->feature->attributes->Addr_type;
            $returnArray['location_score'] = $result->feature->attributes->Score;
            $returnArray['geometry'] = array(
                'lat' => $result->feature->geometry->y,
                'lon' => $result->feature->geometry->x
            );
            $returnArray['error_message'] = '';
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['error_message'] = 'No results were found.';
        }

        return $returnArray;
    }

    /**
     * Get location data from the ESRI geocoding service.
     * This method will automatically choose "getLocationDev" or "getLocationPro" based on environment.
     * The method "getLocationPro" is used for permanent data storage and will consume ESRI credits.
     * @param string $address
     * @param integer $attempt (optional)
     * @return array
     */
    public static function getLocation($address)
    {
        $IS_DEV = Yii::app()->params['env'] !== 'pro';

        return $IS_DEV ? self::getLocationDev($address) : self::getLocationPro($address);
    }
}