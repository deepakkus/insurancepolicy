<?php

class CurlRequest
{
    static public function getRequest($url)
    {
        if (!function_exists('curl_init'))
        {
            die("Can't find a cURL module");
        }
        
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36';
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_ENCODING       => '',
            CURLOPT_USERAGENT      => $user_agent,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL            => $url
        ); 

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $content  = curl_exec($curl);
        
        //$curl_info = curl_getinfo($curl);
        $curl_errorno = curl_errno($curl);
        $curl_error = curl_error($curl);

        if ($curl_error)
        {
            Yii::log('ERRORS WITH CURL RESPONSE: Error - ' . $curl_error, CLogger::LEVEL_ERROR, __METHOD__);
            Yii::log('ERRORS WITH CURL RESPONSE: Error Number - ' . $curl_errorno, CLogger::LEVEL_ERROR, __METHOD__);
            throw new CException('CURL request received an error.  See error log for details.');
        }
        
        curl_close($curl);
        
        return $content;
    }
    
    static public function getMapboxRequest($url)
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_ENCODING       => '',
            CURLOPT_USERAGENT      => $user_agent,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL            => $url
        ); 

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response  = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        $header_array = self::http_parse_headers($header);
        
        $mapbox_data = array(
            'X-Rate-Limit-Interval' => (isset($header_array['X-Rate-Limit-Interval'])) ? $header_array['X-Rate-Limit-Interval'] : 'no data availible',
            'X-Rate-Limit-Limit' => (isset($header_array['X-Rate-Limit-Limit'])) ? $header_array['X-Rate-Limit-Limit'] : 'no data availible',
            'X-Rate-Limit-Remaining' => (isset($header_array['X-Rate-Limit-Remaining'])) ? $header_array['X-Rate-Limit-Remaining'] : 'no data availible',
            'X-Rate-Limit-Reset' => (isset($header_array['X-Rate-Limit-Reset'])) ? $header_array['X-Rate-Limit-Reset'] : 'no data availble'
        );
        
        $curl_error = curl_error($curl);
        $curl_errorno = curl_errno($curl);
        $return_array = array();

        if ($curl_error)
        {
            $return_array['error'] = $curl_errorno;
            $return_array['message'] = $curl_error;
            $return_array['data_usage'] = null;
            $return_array['data'] = json_decode($body);
        }
        else if ($httpcode === 200)
        {
            $return_array['error'] = 0;
            $return_array['message'] = 'request was successful';
            $return_array['data_usage'] = $mapbox_data;
            $return_array['data'] = json_decode($body);
        }
        else if ($httpcode === 429)
        {
            $return_array['error'] = 1;
            $return_array['message'] = 'query limits have been exeeded';
            $return_array['data_usage'] = $mapbox_data;
            $return_array['data'] = null;
        }
        else
        {
            print $httpcode;
            $return_array['error'] = 1;
            $return_array['message'] = 'something went wrong';
            $return_array['data'] = null;
        }
        
        curl_close($curl);
        
        return $return_array;
    }
    
    private static function http_parse_headers($raw_headers)
    {
        
        if (!function_exists('http_parse_headers')) {
            
            $headers = array();
            $key = '';

            foreach(explode("\n", $raw_headers) as $i => $h) {
                $h = explode(':', $h, 2);

                if (isset($h[1])) {
                    if (!isset($headers[$h[0]]))
                        $headers[$h[0]] = trim($h[1]);
                    elseif (is_array($headers[$h[0]])) {
                        $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                    }
                    else {
                        $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                    }

                    $key = $h[0];
                }
                else { 
                    if (substr($h[0], 0, 1) == "\t")
                        $headers[$key] .= "\r\n\t".trim($h[0]);
                    elseif (!$key) 
                        $headers[0] = trim($h[0]); 
                }
            }

            return $headers;
            
        }
        else
        {
            return http_parse_headers($raw_headers);
        }
        
        
        /*
        if (!function_exists('http_parse_headers'))
        {
            $retVal = array();
            $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
            foreach ($fields as $field)
            {
                if (preg_match('/([^:]+): (.+)/m', $field, $match))
                {
                    $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', 
                        function($m){ 
                            return 'strtoupper("\0")'; 
                        }, 
                        strtolower(trim($match[1]))
                    );
                    
                    if (isset($retVal[$match[1]]))
                    {
                        $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                    }
                    else
                    {
                        $retVal[$match[1]] = trim($match[2]);
                    }
                }
            }
            return $retVal;
        }
        else
        {
            return http_parse_headers($header);
        }
         * 
         * */
    }
}