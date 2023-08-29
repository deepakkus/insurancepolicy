<?php

/**
 * This command takes api methods from WDS Admin controllers and update the WDS wiki with content
 * from the api phpDoc syntax comments (https://en.wikipedia.org/wiki/PHPDoc).
 * 
 * http://wiki.wildfire-defense.com/index.php/
 * http://wiki.wildfire-defense.com/api.php/
 */
class ParseApiDocsCommand extends CConsoleCommand
{
    const BASEWIKIURL = 'http://wiki.wildfire-defense.com/index.php';
    const BASEWIKIAPIURL = 'http://wiki.wildfire-defense.com/api.php';

    private $editToken;
    private $httpUserAgent;
    private $allControllers;

    private $engineControllers = array(
        'AllianceController',
        'EngCrewManagementController',
        'EngEnginesController',
        'EngResourceOrderController',
        'EngSchedulingClientController',
        'EngSchedulingController',
        'EngSchedulingEmployeeController'
    );
    private $responseControllers = array(
        'ResCallListController',
        'ResDailyController',
        'ResDailyMapController',
        'ResDailyThreatController',
        'ResDedicatedAgencyController',
        'ResDedicatedController',
        'ResFireNameController',
        'ResFireObsController',
        'ResFireScrapeController',
        'ResMemberInfoUSAAController',
        'ResMonitorLogController',
        'ResNoticeController',
        'ResPerimetersController',
        'ResPolicyActionController',
        'ResPolicyPhotosController',
        'ResPostIncidentSummaryController',
        'ResPropertyAccessController',
        'ResPropertyStatusController',
        'ResSubmittalPackageController',
        'ResThreatController',
        'ResTriageZoneController',
        'ResTriggeredController',
        'WdsfireEnrollmentsController',
        'UnmatchedController'
    );
    private $riskControllers = array(
        'RiskBatchController',
        'RiskModelController',
        'RiskScoreController',
        'RiskStateMeansController'
    );
    private $userControllers = array(
        'UserController',
        'UserTrackingController'
    );
    private $miscControllers = array(
        'ClientController',
        'FileController',
        'GeogZipcodesController',
        'MemberController',
        'PropertyController'
    );

    private $processedActions = array();

	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;
        
        Yii::import('application.controllers.*');

        $this->allControllers = array_merge(
            $this->engineControllers,
            $this->responseControllers,
            $this->riskControllers,
            $this->userControllers,
            $this->miscControllers
        );

        $this->editToken = $this->getEditToken();
        $this->httpUserAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36';
        $this->parseAPIDocs();
        
        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Summary of parseAPIDocs
     */
    private function parseAPIDocs()
    {
        $dir = new \DirectoryIterator(Yii::getPathOfAlias('webroot.controllers'));

        // Iterate through each controller class
        foreach ($dir as $fileinfo)
        {
            $controllerName = basename($fileinfo->getFilename(), '.php');

            if (!$fileinfo->isDot() && in_array($controllerName, $this->allControllers))
            {
                // Create reflection class
                $reflection = new \ReflectionClass($controllerName);

                // Iterate through each method
                // Parse any doc block that have an "actionapi" string
                foreach ($reflection->getMethods() as $reflectionMethod)
                {
                    if (stripos($reflectionMethod->name, 'actionapi') !== false)
                    {
                        $controllerID = lcfirst($controllerName);
                        $actionID = lcfirst(str_replace('action', '', $reflectionMethod->name));
                        $actionPath = $controllerID . '/' . $actionID;
                        $title = 'WDS_API/' . $actionPath;

                        $url = Yii::app()->createAbsoluteUrl($actionPath);

                        $docBlock = $reflection->getMethod($reflectionMethod->name)->getDocComment();
                        $docBlockClean = $this->cleanInput($docBlock);
                        $docBlockCleanWiki = PHP_EOL . $url . PHP_EOL . PHP_EOL . '<pre>' . $docBlockClean . '</pre>' . PHP_EOL . PHP_EOL . '[[Category:WDSAPI]]' . PHP_EOL;

                        $success = $this->sendEditRequest($title, $docBlockCleanWiki, $this->editToken);

                        if ($success)
                        {
                            print self::BASEWIKIURL . '/' . $title . PHP_EOL;

                            // Saving edited api docs to a 2 dimensional array to link on
                            // http://wiki.wildfire-defense.com/index.php/WDS_API

                            if (!isset($this->processedActions[$controllerName])) {
                                $this->processedActions[$controllerName] = array();
                            }

                            $this->processedActions[$controllerName][$title] = $actionPath;
                        }
                    }
                }
            }
        }

        $this->linkAPIDocs();
    }

    private function linkAPIDocs()
    {
        // Engine API Methods

        $content = '== Engine API Methods ==' . PHP_EOL . PHP_EOL;
        foreach ($this->engineControllers as $controller)
        {
            if (isset($this->processedActions[$controller]))
            {
                foreach ($this->processedActions[$controller] as $title => $action)
                {
                    $content .= "* [[$title|$action]]" . PHP_EOL;
                }
            }
        }

        $success = $this->sendEditRequest('WDS_API', $content, $this->editToken, 3);
        if ($success)
            print "Methods linked for Engines" . PHP_EOL;

        // Response API Methods

        $content = '== Response API Methods ==' . PHP_EOL . PHP_EOL;
        foreach ($this->responseControllers as $controller)
        {
            if (isset($this->processedActions[$controller]))
            {
                foreach ($this->processedActions[$controller] as $title => $action)
                {
                    $content .= "* [[$title|$action]]" . PHP_EOL;
                }
            }
        }

        $success = $this->sendEditRequest('WDS_API', $content, $this->editToken, '4');
        if ($success)
            print "Methods linked for Response" . PHP_EOL;

        // Risk API Methods

        $content = '== Risk API Methods ==' . PHP_EOL . PHP_EOL;
        foreach ($this->riskControllers as $controller)
        {
            if (isset($this->processedActions[$controller]))
            {
                foreach ($this->processedActions[$controller] as $title => $action)
                {
                    $content .= "* [[$title|$action]]" . PHP_EOL;
                }
            }
        }

        $success = $this->sendEditRequest('WDS_API', $content, $this->editToken, '5');
        if ($success)
            print "Methods linked for Risk" . PHP_EOL;

        // User API Methods

        $content = '== User API Methods ==' . PHP_EOL . PHP_EOL;
        foreach ($this->userControllers as $controller)
        {
            if (isset($this->processedActions[$controller]))
            {
                foreach ($this->processedActions[$controller] as $title => $action)
                {
                    $content .= "* [[$title|$action]]" . PHP_EOL;
                }
            }
        }

        $success = $this->sendEditRequest('WDS_API', $content, $this->editToken, '6');
        if ($success)
            print "Methods linked for Users" . PHP_EOL;

        // Misc API Methods

        $content = '== Misc API Methods ==' . PHP_EOL . PHP_EOL;
        foreach ($this->miscControllers as $controller)
        {
            if (isset($this->processedActions[$controller]))
            {
                foreach ($this->processedActions[$controller] as $title => $action)
                {
                    $content .= "* [[$title|$action]]" . PHP_EOL;
                }
            }
        }

        $success = $this->sendEditRequest('WDS_API', $content, $this->editToken, '7');
        if ($success)
            print "Methods linked for Misc" . PHP_EOL;
    }

    /**
     * Removes phpDoc syntax from comment and returns text
     * @param string $comment 
     * @return string
     */
    private function cleanInput($comment)
    {
        $comment = trim(
            preg_replace(
                '#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#u',
                '$1',
                $comment
            )
        );

        // reg ex above is not able to remove */ from a single line docblock
        if (substr($comment, -2) == '*/') {
            $comment = trim(substr($comment, 0, -2));
        }

        // normalize strings
        $comment = str_replace(array("\r\n", "\r"), "\n", $comment);

        return $comment;
    }

    /**
     * Gets the csrftoken from mediawiki
     * For unregistered users, the token is always "+\", but you may want to
     * request it explicitly in case this changes in the future.
     * @return string
     */
    private function getEditToken()
    {
        $postData = array(
            'action' => 'query',
            'meta' => 'tokens',
            'rvprop' => 'timestamp',
            'format' => 'json'
        );

        $response = $this->cURLRequest($postData);
        $parsed_json = json_decode($response, true);
        $token = $parsed_json['query']['tokens']['csrftoken'];
        return $token;
    }

    /**
     * Gets the response for a specific title from mediawiki
     * @return string
     */
    private function getRequest($title)
    {
        $postData = array(
            'action' => 'query',
            'titles' => $title,
            'prop' => 'revisions',
            'rvprop' => 'content|timestamp',
            'format' => 'json'
        );

        $response = $this->cURLRequest($postData);
        print_r($response);
        print PHP_EOL . PHP_EOL;
    }

    /**
     * Edit a media wiki title with the given content
     * @param string $title 
     * @param string $content 
     * @param string $token
     * @param string $section
     * @return bool
     */
    private function sendEditRequest($title, $content, $token, $section = null)
    {
        $postData = array(
            'action' => 'edit',
            'title' => $title,
            'text' => $content,
            'token' => $token,
            'format' => 'json'
        );

        if (!is_null($section))
            $postData['section'] = $section;

        $response = $this->cURLRequest($postData);
        $parsed_json = json_decode($response, true);

        if (!isset($parsed_json['edit']['result']))
        {
            print 'There was a problem editing.' . PHP_EOL;
            print_r($parsed_json);
            die();
        }

        $success_text = $parsed_json['edit']['result'];
        $success = ($success_text === 'Success') ? true : false;
        return $success;
    }

    /**
     * Run a cURL POST request with the given post data
     * @param array $postData 
     * @return string
     */
    private function cURLRequest($postData)
    {
        $options = array(
            CURLOPT_URL            => self::BASEWIKIAPIURL,
            CURLOPT_POST           => count($postData),
            CURLOPT_POSTFIELDS     => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_ENCODING       => '',
            CURLOPT_USERAGENT      => $this->httpUserAgent,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST           => 1,
            // This is only needed if media is being posted as well
            //CURLOPT_HTTPHEADER     => 'Content-Type: application/x-www-form-urlencoded'
        ); 

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $content  = curl_exec($curl);

        $curl_errorno = curl_errno($curl);
        $curl_error = curl_error($curl);

        if ($curl_error)
        {
            die('There was a cURL error with error num: ' . $curl_errorno . "\nError: $curl_error\n");
        }

        curl_close($curl);

        return $content;
    }
}