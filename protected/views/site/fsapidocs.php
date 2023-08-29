<h2>Fireshield API Documentation</h2>

<pre>
---------------------------- Fireshield API Guide -------------------------------------
- Last Updated: 2013-04-23
- Contact: Tyler Cross <tyler@reciprocityind.com>
- Current Version: 2.0

Intro Notes:

- All API calls require OAuth2 authentication with valid client credentials (Contact Tyler to setup your client):
authorization endpoint: https://dev.wildfire-defense.com/api/fireshield/v2/auth/
token endpoint: https://dev.wildfire-defense.com/api/fireshield/v2/token/
scope: fireshield
auth grant type: authorization_code (returns as a url param on the redirect uri)
auth response type: code

- TEMPORARALY Allowing authentication via a header 'Api-Key' = FSAPI-510fe7899f7402.96325482 but this will be depreciated soon, so need to use oauth2 


- All API calls that have incoming data require it as a POST variable named 'data'. Inside the 'data' POST variable will contain a JSON payload generally of the form:

{
   "data":
    {
        "something": "whatever",
        "somethingElse": "else",
        "someArray":{
            "arrayVal1": "val1",
            "arrayVal2": "val2",
        },
        "otherVar": "value"
    }
}

- All API call responses will be a JSON array and will always contain an 'error' value which will be either 0 or 1 (0 being no error, 1 being error).  If there is an error, it will also contain a technical error message called 'errorMessage' for debugging use and a non-tech message called 'errorFriendlyMessage' for use to display to the user.


--- Check Carrier Code ---

Description: Checks a given carrier key to see if it exists in the homeowners database table

URL: https://dev.wildfire-defense.com/api/fireshield/v2/checkCarrierCode/

data POST variable example:

{
   "data":
   {
      "carrierKey": "ABC123"
   }
}

successful return example:

{
   "error": 0
}

error return example:

{
    "error": 1,
    "errorFriendlyMessage": "This carrier code does not exist. Please verify that the code you entered matches the one given you and try again.",
    "errorMessage": "ERROR: Carrier-Code not found"
}


--- Create Fireshield User Account ---

Description: Creates an entry in the Fireshield User database table and returns a loginToken for use in user specific API calls (like Get Properties). Also returns a vendorID that is used to register with Parse.com for push notifications. Also returns an array of properties associated with the user. NOTE: carrierKey and lastName must match an existing entry in the Homeowners database table or an error will be returned.

URL: https://dev.wildfire-defense.com/api/fireshield/v2/createAccount/

data POST variable example:

{
    "data": {
        "account": {
            "emailAddress": "test@test.com",
            "firstName": "John",
            "lastName": "Smith",
            "password": "whatever"
        },
        "carrierKey": "123ABC"
    }
}

Success Return example:
{
    "error": 0,
    "data": {
        "loginToken": "43d44ae07336ac5ca768d413a6720ee6",
        "vendorID": "489BE6BA-A03A-4175-9099-3FE6DF674D5A",
        "properties": [
	            {
	                "name": "Josh Amidon",
	                "address": "123 Test Dr.",
	                "city": "Bozeman",
	                "state": "MT",
	                "zip": "59718",
	                "assessmentsAllowed":"1"
	            },
	            {
	                "name": "Josh Amidon",
	                "address": "321 Nowhere St.",
	                "city": "Bozeman",
	                "state": "MT",
	                "zip": "59715",
	                "assessmentsAllowed":"0"
	            }
        ]
    }
}

Error Return example:
{
  "error":1,
  "errorFriendlyMessage":"There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.",
  "errorMessage":"ERROR: No or incorrect API-Key header"
}


--- Fireshield User Login --

Description: Logs in an existing user (that was created using createAccount function) and returns a loginToken for use in user specific API calls (i.e. getProperties function). Also returns a vendorID that is used to register with Parse.com for push notifications. Also returns an array of properties associated with the user. The loginToken currently is a lifetime token.

URL: https://dev.wildfire-defense.com/api/fireshield/v2/login/

data POST variable example:

{
   "data": {
      "email": "test@test.com",
      "password": "password"
   }
}

Success Return example:
{
    "error": 0,
    "data": {
        "loginToken": "43d44ae07336ac5ca768d413a6720ee6",
        "vendorID": "489BE6BA-A03A-4175-9099-3FE6DF674D5A",
        "properties": [
	            {
	                "name": "Josh Amidon",
	                "address": "123 Test Dr.",
	                "city": "Bozeman",
	                "state": "MT",
	                "zip": "59718",
	                "assessmentsAllowed":"1"
	            },
	            {
	                "name": "Josh Amidon",
	                "address": "321 Nowhere St.",
	                "city": "Bozeman",
	                "state": "MT",
	                "zip": "59715",
	                "assessmentsAllowed":"0"
	            }
        ]
    }
}

Error Return example:
{
  "error":1,
  "errorMessage":"ERROR: login and/or password were incorrect.",
  "errorFriendlyMessage":"Login and/or password were incorrect."
}


--- Contact Us Create Entry ---

Description: Creates an entry in the Contact Us database table. NOTE: email address must be unique or an error will be returned.

URL: https://dev.wildfire-defense.com/api/fireshield/v2/createContactUs/

data POST variable example:

{
   "data": 
    { 
        "emailAddress": "test@test.com", 
        "provider": "USAA", 
        "from": "Fireshield No Carrier Code Screen"
    } 
}

Success Return example:
{
    "error": 0
}

Error Return example:
{
  "error":1,
  "errorMessage": "ERROR: email address was already in the FS Contact Us list.",
  "errorFriendlyMessage": "The given email address already has been submitted for contact us list."
}


--- Get Properties for Logged in User ---

Description: Looks up and returns all the properties of a FS User provided a loginToken from the login or create user methods

URL: https://dev.wildfire-defense.com/api/fireshield/v2/getProperties/

data POST variable example:

{
    "data":
    {
        "loginToken": "0cec2636d2a2575042af85e028fe0219"
    }
}

Success Return example:
{
    "error": 0,
    "data": {
        "properties": [
            {
                "name": "Josh Amidon",
                "address": "123 Test Dr.",
                "city": "Bozeman",
                "state": "MT",
                "zip": "59718",
                "assessmentsAllowed":"0"
            },
            {
                "name": "Josh Amidon",
                "address": "321 Nowhere St.",
                "city": "Bozeman",
                "state": "MT",
                "zip": "59715",
                "assessmentsAllowed":"1"
            }
        ]
    }
}

Error Return example:
{
  "error":1,
  "errorMessage": "ERROR: loginToken was not found, could not lookup user.",
  "errorFriendlyMessage": "The given login was not valid."
}    

--- New Upload Assessment ---
Description: Recieves a zip file that contains a JSON payload file with the assessment information, as well as an 'images' folder with accompaning images

URL: https://dev.wildfire-defense.com/api/fireshield/v2/newUploadAssessment/

NOTE: This function recieves a zip file in a post parameter called 'assessmentzip'. The JSON payload will come inside the zip file as payload.json and needs to include an 'images' folder with any applicable images.

payload.json file contents example:
{
  "responses":[
    {
      "media":[],
      "questionID":"1",
      "responseType":1   // 0='yes' 1='no' 2='not sure'
    },
    {
      "media":[],
      "questionID":"2",
      "responseType":0
    },
    {
      "media":[
        {
          "imageName":"something_unique_in_the_images_dir_included_in_the_zip.jpg"
	}
      ],
      "questionID":"3",
      "responseType":1
    },
    {
      "media":[],
      "questionID":"4",
      "responseType":2
    },  
    //.... for all questions
    {
      "media":[
        {
          "imageName":"something.jpg"
	},
	{
	  "imageName":"whatever.jpg"
        }
      ],
      "questionID":"X",
      "responseType":1
    },
  ],
  "loginToken":"381c1a2822f42951873fad589b2837c6",
  "startDate":1363280720.414764,
  "endDate":0,
  "version":1,
  "address":
  {
    "state":"MT",
    "addressLine1":"123 Test Dr.",
    "city":"Bozeman",
    "zip":"59718",
    "gps":
    {
      "longitude":0,
      "latitude":0
    }
  }
}

Success Return Example:
{
    "error": 0,
    "data": {
        "reportGuid": "489BE6BA-A03A-4175-9099-3FE6DF674D5A"
    }
}

Error Return example:
{
  "error":1,
  "errorFriendlyMessage":"There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.",
  "errorMessage":"ERROR: uploading file"
}


--- Upload Assessment --- DEPRECEATED Please Use newUploadAssessment

Description: Recieves a zip file that contains a JSON payload file with the assessment information, as well as an 'images' folder with accompaning images

URL: https://dev.wildfire-defense.com/api/fireshield/v2/uploadAssessment/

NOTE: This function recieves a zip file in a raw byte stream from the body of the request. This allows for a lower memory footprint on both client and server sides. Valid content-type and content-length headers must be set accordingly. The JSON payload will come inside the zip file as payload.json and needs to include an 'images' folder with any applicable images.

payload.json file contents example:
{
  "responses":[
    {
      "media":[],
      "questionID":"1",
      "responseType":1   // 0='yes' 1='no' 2='not sure'
    },
    {
      "media":[],
      "questionID":"2",
      "responseType":0
    },
    {
      "media":[
        {
          "imageName":"something_unique_in_the_images_dir_included_in_the_zip.jpg"
	}
      ],
      "questionID":"3",
      "responseType":1
    },
    {
      "media":[],
      "questionID":"4",
      "responseType":2
    },  
    //.... for all questions
    {
      "media":[
        {
          "imageName":"something.jpg"
	},
	{
	  "imageName":"whatever.jpg"
        }
      ],
      "questionID":"X",
      "responseType":1
    },
  ],
  "loginToken":"381c1a2822f42951873fad589b2837c6",
  "startDate":1363280720.414764,
  "endDate":0,
  "version":1,
  "address":
  {
    "state":"MT",
    "addressLine1":"123 Test Dr.",
    "city":"Bozeman",
    "zip":"59718",
    "gps":
    {
      "longitude":0,
      "latitude":0
    }
  }
}

Success Return Example:
{
    "error": 0,
    "data": {
        "reportGuid": "489BE6BA-A03A-4175-9099-3FE6DF674D5A"
    }
}

Error Return example:
{
  "error":1,
  "errorFriendlyMessage":"There was an error communicating with the service provider.  Please try your request again or contact technical support if this issue persists.",
  "errorMessage":"ERROR: uploading file"
}


--- Get Assessments for Logged in User ---

Description: Looks up and returns all the Assessments taken by a FS User provided a loginToken from the login or create user methods

Note: Status is either 0 => "In Progress" or 1 => "Completed"

URL: https://dev.wildfire-defense.com/api/fireshield/v2/getAssessments/

data POST variable example:

{
    "data":
    {
        "loginToken": "0cec2636d2a2575042af85e028fe0219"
    }
}

Success Return example:
{
    "error": 0,
    "data": {
        "properties": [
            {
                "guid": "5467A17D-4B76-40AA-AAD9-E0EBEFC06C01",
                "status": 1,
                "submitDate": 12345666.1234567,
                "address": {
                	"addressLine1": "123 Test Dr.",
                	"city": "Bozeman",
                	"state": "MT",
                	"zip": "59718"
                }	
            },
            {
	        "guid": "54671234-4346-40BD-AAD9-E0EBEABC2345",
	        "status": 0,
		"submitDate": 12345666.1234567,
		"address": {
			"addressLine1": "123 Test Dr.",
			"city": "Bozeman",
			"state": "MT",
			"zip": "59718"
                }
            }
        ]
    }
}

Error Return example:
{
  "error":1,
  "errorMessage": "ERROR: loginToken was not found, could not lookup user.",
  "errorFriendlyMessage": "The given login was not valid."
}    


--- Get Assessment Status --

Description: Looks up the status of a currently logged in users assessment given the guid

URL: https://dev.wildfire-defense.com/api/fireshield/v2/getAssessmentStatus/

Note: Status is either 0 => "In Progress" or 1 => "Completed"

data POST variable example:

{
    "data":
    {
        "loginToken": "0cec2636d2a2575042af85e028fe0219",
        "guid": "5467A17D-4B76-40AA-AAD9-E0EBEFC06C01"
    }
}

Success Return example:
{
    "error": 0,
    "data": 
    {
        "guid": "5467A17D-4B76-40AA-AAD9-E0EBEFC06C01",
        "status": 1
    }
}

Error Return example:
{
  "error":1,
  "errorMessage": "ERROR: loginToken was not found, could not lookup user.",
  "errorFriendlyMessage": "The given login was not valid."
}    


--- Download Assessment --

Description: Downloads a currently logged in users assessment given the guid

URL: https://dev.wildfire-defense.com/api/fireshield/v2/downloadAssessment/

data POST variable example:

{
    "data":
    {
        "loginToken": "0cec2636d2a2575042af85e028fe0219",
        "guid": "5467A17D-4B76-40AA-AAD9-E0EBEFC06C01"
    }
}

Success Return will be a zip file (with proper content headers) called report.zip with several directories with html and image files and a payload.json in it. See example that should be with this documentation for example (it is long)

Error Return Example:
{
  "error":1,
  "errorMessage": "ERROR: loginToken was not found, could not lookup user.",
  "errorFriendlyMessage": "The given login was not valid."
}  


--- TEMP FUNCTIONS FOR USE DURING TESTING ---

For changing the status of an uploaded report to 'Completed'
https://dev.wildfire-defense.com/index.php?r=fsReport/updateStatusCompleted&guid=5467A17D-4B76-40AA-AAD9-E0EBEFC06C01

To Delete a Fireshield User
https://dev.wildfire-defense.com/index.php?r=fsUser/apiDelete&email=whatever@wherever.net
	


</pre>

