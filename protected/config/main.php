<?php

$configData = require(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');

$config = array(
    'basePath' => dirname(__DIR__),
    'name' => 'Wildfire Defense Systems',
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'bootstrap.helpers.TbHtml',
        'application.models.*',
        'application.models.forms.*',
        'application.components.*',
        'application.filters.*',
        'application.behaviors.*'
    ),
    'aliases' => array(
        'xupload' => 'ext.xupload',
        'bootstrap' => realpath(__DIR__ . '/../extensions/bootstrap')
    ),
    'modules' => array(
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => 'sonor999',
            'ipFilters' => false
        ),
        'wdsauth' => array(
            'class' => 'application.modules.wdsauth.WdsAuthModule'
        )
    ),
    'components' => array(
        'systemSettings' => array(
            'class' => 'WDSSystemSettings'
        ),
        'user'=>array(
            'class' => 'WebUser',
            'allowAutoLogin' => true,
            'authTimeout' => 60 * 60 * 1,
            'loginRequiredAjaxResponse' => 'YII_LOGIN_REQUIRED'
        ),
        'bootstrap' => array(
            'class' => 'ext.bootstrap.components.Bootstrap',
            'responsiveCss' => true,
            'coreCss' => true
        ),
        'authManager' => array(
            'class' => 'application.modules.wdsauth.components.DbAuthManager'
        ),
        'errorHandler' => array(
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                    'logFile' => 'Application.log',
                    'maxFileSize' => 1024,
                    'maxLogFiles' => 5,
                    'rotateByCopy' => true
                )
            )
        ),
        'session' => array(
            'class' => 'system.web.CHttpSession',
            'savePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions',
			'sessionName' => '_wdsadminSessionId'
        )
    )
);

$config['components']['db'] = $configData['database']['db'];
$config['components']['riskdb'] = $configData['database']['riskdb'];
$config['params'] = $configData['params'];

if (isset($configData['log']['webLog']))
{
    $config['components']['log']['routes'][] = $configData['log']['webLog'];
}

return $config;