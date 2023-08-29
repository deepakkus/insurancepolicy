<?php
/*
********************
Fetch data from G2 api
and return guid
*/
class WDSAPIG2
{
    private $apiURL;
	private $apiLoginURL;
	private $apiUpdateURL;
    public function __construct()
	{
		$this->apiLoginURL = Yii::app()->params['apiBaseUrlG1G2'].'/v1/emF_User/CreatePristiniUser';
		$this->apiUpdateURL = Yii::app()->params['apiBaseUrlG1G2'].'/v1/emF_User/Update_PristiniUser';
	}
    public function wdsAPICall($data, $type = NULL)
    {
        $guid = NULL;
		$updateStatus = 'API Data';
		$data_string = ($data);                                                                                   
        if($type == "L")
		{    
			$this->apiURL = $this->apiLoginURL;
		}
		else
		{
			$this->apiURL = $this->apiUpdateURL;
		}
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
		/* print_r($result); die(); */
		$jsonData =  json_decode($result);
		if($type == "L")
		{ 
			$guid = !empty($jsonData->Result->GUID) ?$jsonData->Result->GUID->GUIDString : '{00000000-0000-0000-0000-000000000000}';
			return $guid;
		}
		else
		{
			return $updateStatus;
		}
		
    }
	/*
		Sennd 4 parameter $user, $SystemUser, $apipass , $LastInsertId
		Raw API data
		{
			 { 
				"UserUsername": "apitest12", 
				"UserPassword": "apitest12",
				"UserPasswordType":"valid", 
				"UserPasswordChangedDateTime": "07/25/19 13:37:50 +00:00", 
				"UserEnabled":"true", 
				"UserEmailAddress": "apitest12@excellimatrix.com", 
				"UserFirstName": "Api Test", 
				"UserMiddleName":"", 
				"UserLastName":"",
				"UserInitials":"", 
				"UserSalt":"5d3062dd9f13c5.30058999", 
				"UserLegacyID": 44 ,
				"MobileTelephone" : "",
				"WorkTelephone" : "",
				"WorkTelephoneExt" : "",
				"UserDeleted":false,
				"UserProfileImage":"abc.jpeg",
				"UserPasswordExpired": "2019-10-25"
			}
		}
		Return guid
	*/
	public function wdsCreatePritiniUser($user , $apipass = '', $LastInsertId = '')
	{
		$userData = '{ 
			"UserUsername": "'.$user->username.'", 
			"UserPassword": "'.$apipass.'",
			"UserPasswordType":"valid", 
			"UserEnabled":"true", 
			"UserEmailAddress": "'.$user->email.'", 
			"UserFirstName": "'.$user->name.'", 
			"UserMiddleName":"", 
			"UserLastName":"",
			"UserInitials":"", 
			"UserSalt":"'.$user->salt.'", 
			"UserLegacyID": "'.$LastInsertId.'",
			"UserPasswordChangedDateTime": "'.date("m/d/y H:i:s P",time()).'",
			"UserPasswordExpired": "'.$user->pw_exp.'",
			"MobileTelephone":"'.$user->MobileTelephone.'",
			"WorkTelephone":"",
			"WorkTelephoneExt": "",
			"UserProfileImage":"'.$user->UserProfileImage.'",
			"UserDeleted":"false"  
			}';
			if($user->UserGUID == NULL || $user->UserGUID=='{00000000-0000-0000-0000-000000000000}')
			{
				$guid = $this->wdsAPICall($userData, "L");
				$user->UserGUID = $guid;
				$user->save();
				if($LastInsertId != '')
				{
					// UPDATE user_guid in User table 
					$sql = Yii::app()->db->createCommand("UPDATE [user] SET UserGUID = '$guid' WHERE id = ".$LastInsertId)->execute();
				}
			}
	}
	/*
		Sennd 2 parameter $model and $apipass
		Raw API data
		{
			"GUID": {
				"GUIDString": "{de9f7a3b-9fbd-48d1-bb3b-f7d765960cfe}"
			},
			"UserPassword": "adm123",
			"UserPasswordChangedDateTime": "07/25/19 13:37:50 +00:00",
			"UserEnabled": true,
			"UserSalt": "5964a600dc7bc7.56267549", 
			"UserDeleted": false
		}
		Return updated user status
	*/
	public function wdsUpatePristiniUser($model , $apipass = '')
	{
			$updatePassword = "";
			if ($apipass != "")
			{
				$updatePassword = $apipass;
			}
			$status = true;
			if($model->active == 0)
			{
				$status = false;
			}

			$delete = false;
			if($model->removed == 1)
			{
				$delete = true;
			}
			$userData = '
					{
						"GUID": {
							"GUIDString": "'.$model->UserGUID.'"
						},';
						if($updatePassword) 
						{
							$userData .='"UserPassword": "'.$updatePassword.'",';
							$userData .='"UserSalt": "'.$model->salt.'",';
							$userData .='"UserPasswordChangedDateTime": "'.date("m/d/y H:i:s P",time()).'",';
						}
						$userData .='"UserEnabled": '.$status.', 
						"UserDeleted": '.$delete.'
					}';
			$result = $this->wdsAPICall($userData, "U");
			return $result;
	}
}
?>