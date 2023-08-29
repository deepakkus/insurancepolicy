<?php

/**
 * This controller is intended to house ONLY client facing api methods.
 *
 * @author Matt Eiben
 */
class ApiController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_RISK => array(
                        'getWdsRiskWithCoordinatesV1',
                        'getWdsRiskWithAddressV1'
                    )
                )
            )
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array(
                    'getWdsRiskWithCoordinatesV1',
                    'getWdsRiskWithAddressV1'
                ),
                'users' => array('*')),
            array('deny',
                'users' => array('*')
            )
        );
    }

    /**
     * API Method: api/getWdsRiskWithCoordinatesV1 | api/getriskwithcoordinates/v1
     * Description: Gets WDS Risk score data for given lat/lon
     *
     * Post data parameters:
     * @param float $lat
     * @param float $lon
     *
     * Post data example:
     * {
     *      "data": {
     *          "lat": 41.990,
     *          "lon": -124.198
     *      }
     * }
     *
     * @return array example
     * {
     *      "error": 0,
     *      "data": {
     *          "score_v": 0.98670957419389,
     *          "score_wds": 0.0059707491768271,
     *          "score_wds_text": "Moderate concern",
     *          "score_whp": 0.0060511718270343,
     *          "state_mean": 0.00064332,
     *          "std_dev": 0.00348408,
     *          "std_dev_text": "Within the 2nd above the mean",
     *          "risk_version": "v2.0"
     *      }
     * }
     */
    public function actionGetWdsRiskWithCoordinatesV1()
    {
        $user = WDSAPI::getApiUserFromAccessToken();

        $data = null;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('lat','lon')))
            return;

        //For live mode
        if ($user->api_mode == 'live')
        {
            $riskScore = new RiskScore;
            $riskScore->lat = round($data['lat'], 5);
            $riskScore->long = round($data['lon'], 5);
            $riskScore->client_id = $user->client_id;
            $riskScore->user_id = $user->id;
            $riskScore->date_created = date('Y-m-d H:i');
            $riskScore->geocoded = 0;
            $riskScore->version_id = RiskVersion::getLiveVersionID();

            $model = new RiskModel();

            try
            {
                $riskResult = $model->executeRiskModel($riskScore->lat, $riskScore->long, RiskModel::RISK_QUERY_TABULAR);
            }
            catch (Exception $e)
            {
                Yii::log('ERROR: Could not get return from risk model: ' . var_export($e->getMessage(), true), CLogger::LEVEL_ERROR, __METHOD__);
                return WDSAPI::echoJsonError('ERROR: Something went wrong!', 'The risk model could not be executing properly');
            }

            //Apply rounding
            $riskResult['score_v'] = number_format(round($riskResult['score_v'], 8), 8);
            $riskResult['score_whp'] = number_format(round($riskResult['score_whp'], 8), 8);
            $riskResult['score_wds'] = number_format(round($riskResult['score_wds'], 8), 8);

            $riskScore->score_v = $riskResult['score_v'];
            $riskScore->score_whp = $riskResult['score_whp'];
            $riskScore->score_wds = $riskResult['score_wds'];
            $riskScore->score_type = RiskScoreType::TYPE_API;

            if (!$riskScore->save())
            {
                Yii::log('ERROR: Problem saving risk score in api method "apiGetRiskSaveScore": ' . var_export($riskScore->getErrors(), true), CLogger::LEVEL_ERROR, __METHOD__);
                return WDSAPI::echoJsonError('ERROR: Something went wrong');
            }

            $stateMeanModel = RiskStateMeans::loadModelByLatLong($data['lat'], $data['lon']);

            if (!$stateMeanModel)
                return WDSAPI::echoJsonError('ERROR: Something went wrong', 'The state mean could not be loaded for these coordinates');

            $stdDevText = RiskScore::getStandardDevText($stateMeanModel->mean, $stateMeanModel->std_dev, $riskScore->score_wds);
            $wdsRiskText = RiskScore::getRiskConcern($stateMeanModel->mean, $stateMeanModel->std_dev, $riskScore->score_wds, false);

            $returnArray['error'] = 0;
            $returnArray['data'] = $riskResult;
            $returnArray['data']['state_mean'] =  number_format(round($stateMeanModel->mean, 8), 8);
            $returnArray['data']['std_dev'] = number_format(round($stateMeanModel->std_dev, 8), 8);
            $returnArray['data']['std_dev_text'] = $stdDevText;
            $returnArray['data']['score_wds_text'] = $wdsRiskText;
            $returnArray['data']['risk_version'] = RiskVersion::getLiveVersionName();

            ksort($returnArray['data']);
        }
        //For test mode - static response
        else
        {
            //Error - user provided invalid lat, or lat of wrong type
            if ($data['lat'] < 0 || is_float($data['lat']) == false)
            {
                $returnArray['error'] = 1;
                $returnArray['errorFriendlyMessage'] = "There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.";;
                $returnArray['errorMessage'] = "The risk model could not be executing properly";
            }
            else
            {
                $stateMeanModel = RiskStateMeans::loadModelByLatLong($data['lat'], $data['lon']);
                //Out of our service area
                if (!$stateMeanModel)
                {
                    $returnArray['error'] = 1;
                    $returnArray['errorFriendlyMessage'] = "There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.";;
                    $returnArray['errorMessage'] = "The state mean could not be loaded for these coordinates";
                }
                //Correct response - no errors
                else
                {
                    $returnArray['error'] = 0;
                    $returnArray['data'] = array(
                        "score_v" => 0.98670957419389,
                        "score_wds" => 0.0059707491768271,
                        "score_wds_text" => "Moderate concern",
                        "score_whp" => 0.0060511718270343,
                        "state_mean" => 0.00064332,
                        "std_dev" => 0.00348408,
                        "std_dev_text" => "Within the 2nd above the mean",
                        "risk_version" => "v2.0",
                        "test_mode" => 1,
                        "user_api_mode" => $user->api_mode,
                    );
                }
            }
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: api/getWdsRiskWithAddressV1 | api/getriskwithaddress/v1
     * Description: Gets WDS Risk score data for given addres components
     * Also logs a risk score entry.
     *
     * Post data parameters:
     * @param string $address
     * @param string $city
     * @param string $state
     * @param string $zip
     *
     * Post data example:
     * {
     *      "data": {
     *          "address": "514 N Black Ave",
     *          "city": "Bozeman",
     *          "state": "MT",
     *          "zip": "59715"
     *      }
     * }
     *
     * @return array example
     * {
     *      "error": 0,
     *      "data": {
     *          "score_v": 0.98670957419389,
     *          "score_wds": 0.0059707491768271,
     *          "score_wds_text": "Moderate concern",
     *          "score_whp": 0.0060511718270343,
     *          "state_mean": 0.00064332,
     *          "std_dev": 0.00348408,
     *          "std_dev_text": "Within the 2nd above the mean",
     *          "risk_version": "v2.0"
     *      }
     * }
     */
    public function actionGetWdsRiskWithAddressV1()
    {
        $user = WDSAPI::getApiUserFromAccessToken();

        $data = null;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('address','city','state','zip')))
            return;

        if (empty($data['address']) || empty($data['city']) || empty($data['state']) || empty($data['zip']))
            return WDSAPI::echoJsonError('ERROR: Missing attributes', 'All fields "address","city","state","zip" must be filled out!');

        //For live mode
        if ($user->api_mode == 'live')
        {
            $riskScore = new RiskScore;
            $riskScore->address = $data['address'];
            $riskScore->city = $data['city'];
            $riskScore->state = $data['state'];
            $riskScore->zip = $data['zip'];
            $riskScore->client_id = $user->client_id;
            $riskScore->user_id = $user->id;
            $riskScore->date_created = date('Y-m-d H:i:s');
            $riskScore->version_id = RiskVersion::getLiveVersionID();

            $address = sprintf('%s, %s, %s %s', $data['address'], $data['city'], $data['state'], Helper::splitZipCode($data['zip']));

            $geocode = Geocode::getLocation($address, 'address');

            /* Perform geocoding + risk model if geocoding succeeds */

            // Good match, use the coordinates
            if (isset($geocode['location_type']))
            {
                $stateAbbrGeocode = Helper::convertStateToAbbr(strtoupper($geocode['state']));
                $stateAbbr = Helper::convertStateToAbbr(strtoupper($riskScore->state));

                $stateAbbrGeocode = $stateAbbrGeocode ? $stateAbbrGeocode : $geocode['state'];
                $stateAbbr = $stateAbbr ? $stateAbbr : $riskScore->state;

                $riskScore->lat = $geocode['geometry']['lat'];
                $riskScore->long = $geocode['geometry']['lon'];
                $riskScore->match_type = $geocode['location_type'];
                $riskScore->match_score = $geocode['location_score'];
                $riskScore->match_address = $geocode['address_formatted'];
                // wds_geocode_level is unmatched if geocode is bad location score or result state != query state
                $riskScore->wds_geocode_level = ($geocode['location_type'] === 'address' && $geocode['location_score'] > .74 && $stateAbbrGeocode === $stateAbbr) ? 'address' : 'unmatched'; //J Chadwick 9-20-2018: Changed .74 to .49 | J Chadwick 9-26-2018: Changed back to default.
                $riskScore->geocoded = 1;
            }
            // Something went wrong with the geocoding
            else
            {
                $riskScore->lat = null;
                $riskScore->long = null;
                $riskScore->match_type = 'unmatched';
                $riskScore->match_score = 0;
                $riskScore->match_address = 'could not match';
                $riskScore->wds_geocode_level = 'unmatched';
                $riskScore->geocoded = 1;
            }

            $riskResult = null;

            // Geocoder worked, now get risk
            if ($riskScore->lat && $riskScore->long && $riskScore->wds_geocode_level === 'address')
            {
                $model = new RiskModel;

                try
                {
                    $riskResult = $model->executeRiskModel($riskScore->lat, $riskScore->long, RiskModel::RISK_QUERY_TABULAR);
                }
                catch (Exception $e)
                {
                    Yii::log('ERROR: Could not get return from risk model: ' . $e->getMessage(), CLogger::LEVEL_ERROR, __METHOD__);
                    return WDSAPI::echoJsonError('ERROR: Something went wrong!', 'The risk model could not be executing properly');
                }

                //Apply rounding
                $riskResult['score_v'] = number_format(round($riskResult['score_v'], 8), 8);
                $riskResult['score_whp'] = number_format(round($riskResult['score_whp'], 8), 8);
                $riskResult['score_wds'] = number_format(round($riskResult['score_wds'], 8), 8);

                $riskScore->score_v = $riskResult['score_v'];
                $riskScore->score_whp = $riskResult['score_whp'];
                $riskScore->score_wds = $riskResult['score_wds'];
                $riskScore->score_type = RiskScoreType::TYPE_API;
            }
            // Unmatched by geocoder
            else
            {
                $riskScore->score_v = 0;
                $riskScore->score_whp = 0;
                $riskScore->score_wds = 0;
                $riskScore->score_type = RiskScoreType::TYPE_API;
            }

            if (!$riskScore->save())
            {
                Yii::log('ERROR: Problem saving risk score in api method "apiGetRiskSaveScore": ' . var_export($riskScore->getErrors(), true), CLogger::LEVEL_ERROR, __METHOD__);
                return WDSAPI::echoJsonError('ERROR: Something went wrong', 'The risk score could not be saved');
            }

            // Client return

            if ($riskResult === null)
                return WDSAPI::echoJsonError('Geocode failed!');

            $stateMeanModel = RiskStateMeans::loadModelByLatLong($riskScore->lat, $riskScore->long);

            if (!$stateMeanModel)
                return WDSAPI::echoJsonError('ERROR: Something went wrong', 'The state mean could not be loaded for the geocoded address coordinates');

            $stdDevText = RiskScore::getStandardDevText($stateMeanModel->mean, $stateMeanModel->std_dev, $riskScore->score_wds);
            $wdsRiskText = RiskScore::getRiskConcern($stateMeanModel->mean, $stateMeanModel->std_dev, $riskScore->score_wds, false);

            $returnArray['error'] = 0;
            $returnArray['data'] = $riskResult;
            $returnArray['data']['state_mean'] =  number_format(round($stateMeanModel->mean, 8), 8);
            $returnArray['data']['std_dev'] = number_format(round($stateMeanModel->std_dev, 8), 8);
            $returnArray['data']['std_dev_text'] = $stdDevText;
            $returnArray['data']['score_wds_text'] = $wdsRiskText;
            $returnArray['data']['risk_version'] = RiskVersion::getLiveVersionName();

            ksort($returnArray['data']);
        }
        //For test mode - static response
        else
        {
            $address = sprintf('%s, %s, %s %s', $data['address'], $data['city'], $data['state'], Helper::splitZipCode($data['zip']));

            $geocode = Geocode::getLocation($address, 'address', null, null, null, 'mapbox.places');

            // Good match, use the coordinates
            if (isset($geocode['location_type']) && $geocode['location_score'] > .74)
            {
                $stateMeanModel = RiskStateMeans::loadModelByLatLong($geocode['geometry']['lat'], $geocode['geometry']['lon']);
                //Out of our service area
                if (!$stateMeanModel)
                {
                    $returnArray['error'] = 1;
                    $returnArray['errorFriendlyMessage'] = "There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.";;
                    $returnArray['errorMessage'] = "The state mean could not be loaded for these coordinates";
                }
                //Success!
                else
                {
                    $returnArray['error'] = 0;
                    $returnArray['data'] = array(
                        "score_v" => 0.98670957419389,
                        "score_wds" => 0.0059707491768271,
                        "score_wds_text" => "Moderate concern",
                        "score_whp" => 0.0060511718270343,
                        "state_mean" => 0.00064332,
                        "std_dev" => 0.00348408,
                        "std_dev_text" => "Within the 2nd above the mean",
                        "risk_version" => RiskVersion::getLiveVersionName(),
                    );
                }
            }
            // Something went wrong with the geocoding
            else
            {
                $returnArray['error'] = 1;
                $returnArray['errorFriendlyMessage'] = "There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.";;
                $returnArray['errorMessage'] = "Geocode failed!";
            }
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }
}
