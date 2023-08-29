<?php
/*
********************
Fetch data from G2 api
and return guid
*/
class WDSAPIG2MFA
{
    private $apiURL;
	private $apiSendMFAURL;
	private $apiVerifyMFAURL;
    public function __construct()
	{
		$this->apiSendMFAURL = Yii::app()->params['apiBaseUrlG1G2'].'/v1/User/SendMFACode';
		$this->apiVerifyMFAURL = Yii::app()->params['apiBaseUrlG1G2'].'/v1/User/VerifyMFACode';
	}
    public function wdsAPICall($data, $type = NULL)
    {
		if($type == "V")
		{    
			$this->apiURL = $this->apiVerifyMFAURL;
		}
		else
		{
			$this->apiURL = $this->apiSendMFAURL;
		}
		$data_string = $data;
		$ch = curl_init($this->apiURL);       
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                    
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                     
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                         
            'Content-Type: application/json',                                                                               
            'Content-Length: ' . strlen($data_string))                                                                      
        );                                                                                                       
		$result = curl_exec($ch);
		$jsonData =  json_decode($result);
		
		//If No response is sent by the API or the result is not Success
		if(!isset($jsonData->Success) || !($jsonData->Success))
		{
			$reason = NULL;
			$result = strip_tags($result);
			if ((stripos($result, "HTTP 404") !== false)||($result == false)) {
				$reason = '404';
			}
			elseif(isset($jsonData->Error) || ($jsonData->Error))
			{
				$errorMessage = $jsonData->Error->Message;
				$errorDetails = $jsonData->Error->Details;
				if(($errorMessage == 'An internal error occurred during your request!') && ($errorDetails == 'An internal error occurred during your request!'))
				{
						$reason = 'twilio';
				}
			}
			else
			{
				$reason = 'other';
			}
			$jsonData = new stdClass; 
			$jsonData->Success = 0;
			$jsonData->Reason  = $reason;
		}
		return $jsonData;
    }
	/*
		Sennd GUIDString parameter
		Raw API data
		{
			"GUID": {
				"GUIDString": "{de9f7a3b-9fbd-48d1-bb3b-f7d765960cfe}"
			},
		}
		OR
		{ "GUID": { "GUIDString": "{f24f9e36-851a-4a34-9408-96bc2e2b328d}" }, "MFAMethodDefault":"1" }
		Send guid
	*/
	public function wdsApiSendMFAURL($UserGUID, $MFAMethodDefault, $MFACountryCode ='', $MFAPhoneNumber ='')
	{
			$userData = ' {
						  "GUID": {
							  "GUIDString": "'.$UserGUID.'"
						  },
						 "MFAMethodDefault":"'.$MFAMethodDefault.'",
						 "MFACountryCode":"'.$MFACountryCode.'",
						 "MFAPhoneNumber":"'.$MFAPhoneNumber.'"
						}';
			$result = $this->wdsAPICall($userData);
			return $result;
	}
	/*
		Sennd 2 parameter GUID and MFACodeValue
		Raw API data
				{
		  "GUID": {
			  "GUIDString": "{00000000-0000-0000-0000-100000000001}"
		  },
		  "MFACodeValue":"346813"
		}
		Return updated user status
	*/
	public function wdsApiVerifyMFAURL($UserGUID , $MFACodeValue = '')
	{	
		$userData = '
				{
				  "GUID": {
					  "GUIDString":"'.$UserGUID.'"
				  },
				  "MFACodeValue":"'.$MFACodeValue.'"
				}
				';
		$result = $this->wdsAPICall($userData, "V");
		return $result;
	}
}
?>