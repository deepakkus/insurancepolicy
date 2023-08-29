<?php

return array(
    //Client coordinates
    'sample1' => array(
        'address_line_1'=>'816 N 15th Ave',
        'city'=>'Bozeman',
        'state'=>'MT',
        'zip'=>'59715',
        'response_status'=>'enrolled',
        'lat' => '37.32',
        'long' => '-110.2',
        'policy_status' => 'active',
        'geocode_level' => 'PARCEL',
        'wds_geocode_level' => 'client',
        'wds_geocoder' => '',
        'wds_match_score' => '',
        'geog' => 'POINT (-110.2 37.32)',
        'wds_lat' => '37.32',
        'wds_long' => '-110.2'
    ),
    //Mapbox geocoding, 91 score
    'sample2'=> array(
        'address_line_1'=>'1412 3rd St W',
        'city'=>'Kalispell',
        'state'=>'MT',
        'zip'=>'59901',
        'response_status'=>'not enrolled',
        'lat' => '48.1926',
        'long' => '-114.3335',
        'policy_status' => 'active',
        'geocode_level' => 'zipcode',
        'wds_geocode_level' => 'address',
        'wds_geocoder' => 'mapbox',
        'wds_match_score' => '.91',
        'geog' => 'POINT (-114.3335 48.1926)',
        'wds_lat' => '48.1926',
        'wds_long' => '-114.3335'
    ),
    //Mapbox geocoding, 72 score
    'sample3' => array(
        'address_line_1'=>'1585 U.S. Hwy 2 W',
        'city'=>'Kalispell',
        'state'=>'MT',
        'zip'=>'59901',
        'response_status'=>'not enrolled',
        'lat' => '48.1971',
        'long' => '-114.3528',
        'policy_status' => 'active',
        'geocode_level' => 'zipcode',
        'wds_geocode_level' => 'address',
        'wds_geocoder' => 'mapbox',
        'wds_match_score' => '.72',
        'geog' => 'POINT (-114.3528 48.1971)',
        'wds_lat' => '48.1971',
        'wds_long' => '-114.3528'
    ),
    //Mapbox geocoding, 68 score
    'sample4' => array(
        'address_line_1'=>'1584 U.S. Hwy 2 W',
        'city'=>'Kalispell',
        'state'=>'MT',
        'zip'=>'59901',
        'response_status'=>'not enrolled',
        'lat' => '48.198',
        'long' => '-114.567',
        'policy_status' => 'active',
        'geocode_level' => 'zipcode',
        'wds_geocode_level' => 'address',
        'wds_geocoder' => 'mapbox',
        'wds_match_score' => '.68',
        'geog' => 'POINT (-114.567 48.198)',
        'wds_lat' => '48.198',
        'wds_long' => '-114.567'
    ),
    //WDS Score
    'sample5' => array(
        'address_line_1'=>'1589 U.S. Hwy 2 W',
        'city'=>'Kalispell',
        'state'=>'MT',
        'zip'=>'59901',
        'response_status'=>'not enrolled',
        'lat' => '48.19899',
        'long' => '-114.56799',
        'policy_status' => 'active',
        'geocode_level' => 'zipcode',
        'wds_geocode_level' => 'wds',
        'wds_geocoder' => 'esri',
        'wds_match_score' => '.6',
        'geog' => 'POINT (-114.56799 48.19899)',
        'wds_lat' => '48.19899',
        'wds_long' => '-114.56799'
    )
);