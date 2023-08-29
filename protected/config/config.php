<?php

/**
 * "config.json" is the default file that is choosen for environmental system
 * configurations.  However, if a "local.json" file is present, that file will
 * be used instead.
 */

$configFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.json';
$configLocalFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'local.json';

$configData = null;

// Use local.json
if (file_exists($configLocalFile) === true)
{
    $configData = json_decode(file_get_contents($configLocalFile), true);
}
// Use config.json
else
{
    if (file_exists($configFile) === false)
    {
        die('Application configuration file is missing!');
    }

    $configData = json_decode(file_get_contents($configFile), true);
}

if (json_last_error())
{
    die('There was an error parsing configuration file: ' . json_last_error_msg());
}

return $configData;