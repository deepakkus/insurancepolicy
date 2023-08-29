<?php

class MapboxGeocodeTestCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $time_start = microtime(true);

        $address = '426375 AUGUSTA DR';
        $city = 'LAKE ARROWHEAD';
        $state = 'CA';
        $zip = '80420';

        $address = sprintf('%s, %s, %s %s', $address, $city, $state, $zip);

        $geocodeMapbox = Geocode::getLocation($address, 'address');
        $geocodeESRI = GeocodeESRI::getLocation($address);

        print "\n\n";
        print "Mapbox Results: \n";
        print_r($geocodeMapbox);
        print "\n\n";
        print "ESRI Results: \n";
        print_r($geocodeESRI);
        print "\n\n";

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }
}