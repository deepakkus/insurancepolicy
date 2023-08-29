<?php

/**
 * Uses the google web service to geocode an address. Returns the location (match) type and coordinates
 */
class GeocodeGoogle
{
    const ROOFTOP = 'ROOFTOP';
    const STREET = 'RANGE_INTERPOLATED';
    const APPROXIMATE = 'GEOMETRIC_CENTER';
    const REGION = 'APPROXIMATE';

    public static function getLocation($address)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address);

        $results = CurlRequest::getRequest($url);
        $results_object = json_decode($results);
        $returnArray = array();

        if ($results_object->status === 'OK')
        {
            $result = $results_object->results[0];

            $returnArray['error'] = 0;
            $returnArray['formatted_address'] = $result->formatted_address;
            $returnArray['geometry'] = array(
                'lat' => $result->geometry->location->lat,
                'lon' => $result->geometry->location->lng
            );
            $returnArray['location_type'] = self::location_type($result->geometry->location_type);
            $returnArray['error_message'] = '';
        }
        else if ($results_object->status === 'ZERO_RESULTS')
        {
            $returnArray['error'] = 1;
            $returnArray['error_message'] = 'No results were found.';
        }
        else if ($results_object->status === 'OVER_QUERY_LIMIT')
        {
            $returnArray['error'] = 1;
            $returnArray['error_message'] = 'Over the query limit.';
        }
        else if ($results_object->status === 'REQUEST_DENIED')
        {
            $returnArray['error'] = 1;
            $returnArray['error_message'] = 'The geocoding request was denied.';
        }
        else if ($results_object->status === 'INVALID_REQUEST')
        {
            $returnArray['error'] = 1;
            $returnArray['error_message'] = 'Request was invalid.';
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['error_message'] = 'An error occured.';
        }

        return $returnArray;
    }

    private static function location_type($type)
    {
        switch($type)
        {
            case self::ROOFTOP: return 'Rooftop';
            case self::STREET: return 'Street';
            case self::APPROXIMATE: return 'Approximate';
            case self::REGION: return 'Region';
        }
    }
}