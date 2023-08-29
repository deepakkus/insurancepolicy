<?php

$configData = require(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');

$config = array(
    'basePath' => dirname(__DIR__),
    'name' => 'Wildfire Defense Systems',
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.models.forms.*',
        'application.components.*',
        'application.filters.*',
        'application.behaviors.*'
    ),
    'components'=>array(
        'authManager' => array(
            'class' => 'application.modules.wdsauth.components.DbAuthManager'
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'info, error, warning',
                    'logFile' => 'consoleApplication.log',
                    'maxFileSize' => 1024,
                    'maxLogFiles' => 5,
                    'rotateByCopy' => true
                )
            )
        )
    )
);

$config['components']['db'] = $configData['database']['db'];
$config['components']['db']['class'] = 'EDbConnection';
$config['components']['riskdb'] = $configData['database']['riskdb'];
$config['components']['riskdb']['class'] = 'EDbConnection';
$config['params'] = $configData['params'];

return $config;