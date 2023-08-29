<?php

/*
 * Mapbox Geocoder Method 8/6/2015
 *
 * Uses Mapbox web service to geocode an address. Returns location information.
 *
 * Current Documentation URL: https://www.mapbox.com/developers/api/geocoding/
 *
 * Example Output
 *
 * {
 *     "error": 0,
 *     "query": {
 *         "search",
 *         "address",
 *         "components"
 *     },
 *     "address_formatted": "201 Evergreen Dr, Bozeman, 59715, Montana, United States",
 *     "address": "201 Evergreen Dr",
 *     "city": "Bozeman",
 *     "zip": "59715",
 *     "geometry": {
 *         "lat": 45.696062,
 *         "lon": -111.03909
 *     },
 *     "location_type": "address",
 *     "location_score": 0.91666666666667,
 *     "data_usage": {
 *         "X-Rate-Limit-Interval": "60",
 *         "X-Rate-Limit-Limit": "600",
 *         "X-Rate-Limit-Remaining": "599",
 *         "X-Rate-Limit-Reset": "1438899318"
 *     },
 *     "error_message": ""
 * }
 *
 * Location Types:
 * country - Sovereign states and other political entities. Examples: United States, France, China, Russia.
 * region - First order administrative divisions within a country, usually provinces or states. Examples: California, Ontario, Essonne.
 * postcode - Postal code, varies by a country's postal system. Examples: 20009, CR0 3RL.
 * place - City, town, village or other municipality relevant to a country's address or postal system. Examples: Cleveland, Saratoga Springs, Berlin, Paris.
 * address - A street address with house number. Examples: 1600 Pennsylvania Ave NW, 1051 Market St, Oberbaumstrasse 7.
 * poi - Places of interest including commercial venues, major landmarks, parks, and other features. Examples: Yosemite National Park, Lake Superior.
 *
 */
class Geocode
{
    const TYPE_ADDRESS = 'address';
    const TYPE_PLACE = 'place';

    /**
     * Uses Mapbox web service to geocode an address. Returns location information.
     * @param string $address
     * @param string $type optional
     * @param int $clientTransaction optional - whether to save this as a client transaction or not (0 or 1)
     * @param int $clientID - if the clientTransaciton flag is set, than need a clientID
     * @param array $addressParts - an array with the address parts
     * @param string $geocoderTypeOverRide - can set to periment or places for specific use cases
     * @return array
     */
    public static function getLocation($address, $type = null, $clientTransaction = null, $clientID = null, $addressParts = null, $geocoderTypeOverRide = null)
    {
        //Perimanat or places - allow override
        $geocoder = ($geocoderTypeOverRide) ? $geocoderTypeOverRide : self::getEnvironment();

        //Match to place or address
        $filter = $type ? "types={$type}&" : '';

        //Construct URL for call
        $url = "https://api.mapbox.com/geocoding/v5/$geocoder/" . urlencode($address) . ".json?{$filter}access_token=" . Helper::MAPBOX_ACCESS_TOKEN;

        //Make call - get geocoding results
        $results = CurlRequest::getMapboxRequest($url);

        //Now parse results if we don't have an error
        if ($results['error'] === 0)
        {
            $results_object = $results['data'];

            if(isset($results_object->features[0]))
            {
                $result = $results_object->features[0];

                $returnArray['error'] = $results['error'];
                $returnArray['query'] = $results_object->query;
                $returnArray['address_formatted'] = $result->place_name;
                $returnArray['address'] = (isset($result->address) ? $result->address . ' ' : '') . $result->text;

                foreach($result->context as $context)
                {
                    $attr = current(explode('.', $context->id));

                    switch ($attr)
                    {
                        case 'place': $returnArray['city'] = $context->text; break;
                        case 'region': $returnArray['state'] = $context->text; break;
                        case 'postcode': $returnArray['zip'] = $context->text; break;
                        default: break;
                    }
                }

                $returnArray['geometry'] = array(
                    'lat' => $result->geometry->coordinates[1],
                    'lon' => $result->geometry->coordinates[0]
                );

                $returnArray['location_type'] = current(explode('.', $result->id));
                $returnArray['location_score'] = round($result->relevance, 3);
                $returnArray['data_usage'] = $results['data_usage'];
                $returnArray['error_message'] = '';
            }
            else
            {
                $returnArray['error'] = $results['error'];
                $returnArray['query'] = (isset($results_object->query)) ? $results_object->query : 'Query not availible';
                $returnArray['address_formatted'] = null;
                $returnArray['address'] = null;

                $returnArray['geometry'] = array(
                    'lat' => null,
                    'lon' => null
                );

                $returnArray['location_type'] = "unmatched";
                $returnArray['location_score'] = 0;
                $returnArray['data_usage'] = isset($results['data_usage']) ? $results['data_usage'] : null;
                $returnArray['error_message'] = '';

            }
        }
        else
        {
            $returnArray['error'] = $results['error'];
            $returnArray['error_message'] = $results['message'];
            //$returnArray['data_usage'] = $results['data_usage'];
        }

        //Record the client transaction for billing
        if (isset($clientTransaction) && $clientTransaction && isset($clientID) && $clientID)
        {
            self::saveTransaction($returnArray, $clientID);
        }

        return $returnArray;
    }



    /**
     * Uses Mapbox web service to reverse geocode a set of coordinates. Returns location information.
     * @param string $lat
     * @param string $long
     * @return array
     */
    public static function reverseGeocode($lat, $long)
    {

        //Construct URL for call
        $mapboxToken = Helper::MAPBOX_ACCESS_TOKEN;
        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/$long,$lat.json?types=region&access_token=$mapboxToken";

        //Make call - get geocoding results
        $results = CurlRequest::getMapboxRequest($url);

        //Now parse results if we don't have an error
        if ($results['error'] === 0)
        {
            //Parse through object
            $results_object = $results['data'];

            if (isset($results_object->features) && !empty($results_object->features))
            {
                $result = $results_object->features[0];

                //Build results
                $returnArray['location_type'] = current(explode('.', $result->id));
                $returnArray['text'] = $result->text;
                $returnArray['state_abbr'] = Helper::convertStateToAbbr(strtoupper($result->text));
                $returnArray['place_name'] = $result->place_name;
                $returnArray['data_usage'] = isset($results['data_usage']) ? $results['data_usage'] : null;
                $returnArray['error_message'] = '';
            }
            else
            {
                $returnArray['error'] = "Unmatched";
                $returnArray['error_message'] = "No results were found";
            }

        }
        else
        {
            $returnArray['error'] = $results['error'];
            $returnArray['error_message'] = $results['message'];
        }

        return $returnArray;
    }

    /**
     * Saves the transaction so that billing will have a record of the geocode
     * @param array $result
     * @param int $clientID
     */
    public static function saveTransaction($result, $clientID)
    {
        //Save this in the client transation table
        $model = new ClientTransaction;

        //Assign values from the current transaction
        $model->client_id = $clientID;
        $model->address = (isset($result['address'])) ? $result['address'] : 'error with geocode';
        $model->city = (isset($result['city'])) ? $result['city'] : 'error with geocode';
        $model->state = (isset($result['state'])) ? Helper::convertStateToAbbr(strtoupper($result['state'])) : '';
        $model->score = isset($result['location_score']) ? $result['location_score'] : 0;
        $model->service = "geocode";
        $model->type = "wdsrisk";
        $model->status = (isset($result['location_score']) && isset($result['location_type']) && $result['location_score'] > .75 && $result['location_type'] == 'address') ? 1 : 0;
        $model->date_created = date("Y-m-d H:i");

        if (!$model->save())
        {
            print_r($model->getErrors());
        }
    }

    /*
     *  Determine if we're using the perimant or the standard places.
     *  Might need to also base this on a parameter - ie we don't need to use the perimant places for production
     *  Geocoding that will only be used in a map and not saved
     * As for now we'll just use javascript for the places and this for the permanent
    */
    public static function getEnvironment()
    {
        //Determine if we're local or dev
        $dev = (Yii::app()->params['env'] === 'pro') ? false : true;

        //Set appropriate geocoder call
        return ($dev) ? 'mapbox.places' : 'mapbox.places-permanent';
    }
}