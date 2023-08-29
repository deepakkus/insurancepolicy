<?php

class ResPropertyAccessController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetPropertyAccess',
                        'apiCreatePropertyAccess',
                        'apiUpdatePropertyAccess'
                    )
                )
            )
        );
    }

	/**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(),
				'users' => array('@'),
			),
			array('allow',
				'actions' => array(
                    'apiGetPropertyAccess',
                    'apiCreatePropertyAccess',
                    'apiUpdatePropertyAccess'
                ),
				'users' => array('*')),
			array('deny',
				'users' => array('*'),
			),
		);
	}
    
    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
            )
        );
    }
    
    /**
     * API Method: resPropertyAccess/apiGetPropertyAccess
     * Description: Get a property access entry by id
     * 
     * Post data parameters:
     * @param int id
     * 
     * Post data example: 
     * {
     *     "data": {
     *         "id": 593
     *     }
     * }
     */
    public function actionApiGetPropertyAccess()
    {
        $data = NULL;
        
        if (!WDSAPI::getInputDataArray($data, array('pid')))
            return;
        
        $model = ResPropertyAccess::model()->findByAttributes(array('property_id' => $data['pid']));
        
        $returnArray = array(
            'error' => 0,
            'data' => array()
        );
        
        if ($model)
        {
            $returnArray['data'] = array(
                'id' => $model->id,
                'property_id' => $model->property_id,
                'address_verified' => $model->address_verified,
                'best_contact_number' => $model->best_contact_number,
                'access_issues' => $model->access_issues,
                'gate_code' => $model->gate_code,
                'suppression_resources' => $model->suppression_resources,
                'other_info' => $model->other_info
            );
        }
        
        return WDSAPI::echoResultsAsJson($returnArray);
    }
    
    /**
     * API Method: resPropertyAccess/apiCreatePropertyAccess
     * Description: Create a new property access for WDSFire
     * 
     * Post data parameters:
     * @param int property_id
     * @param int address_verified
     * @param string best_contact_number
     * @param string access_issues
     * @param string gate_code
     * @param string suppression_resources
     * @param string other_info
     * 
     * Post data example: 
     * {
     *     "data": {
     *         "property_id": 123456,
     *         "address_verified": 1,
     *         "best_contact_number": "564-874-8912",
     *         "access_issues": "",
     *         "gate_code": "1245",
     *         "suppression_resources": "He has three sprinklers",
     *         "other_info": ""
     *     }
     * }
     */
    public function actionApiCreatePropertyAccess()
    {
        $data = NULL;
        
        $requiredFields = array('property_id','address_verified','best_contact_number','access_issues','gate_code','suppression_resources','other_info');
        
        if (!WDSAPI::getInputDataArray($data, $requiredFields))
            return;
        
        $propertyAccess = new ResPropertyAccess;
        $propertyAccess->property_id = $data['property_id'];
        $propertyAccess->address_verified = $data['address_verified'];
        $propertyAccess->best_contact_number = $data['best_contact_number'];
        $propertyAccess->access_issues = $data['access_issues'];
        $propertyAccess->gate_code = $data['gate_code'];
        $propertyAccess->suppression_resources = $data['suppression_resources'];
        $propertyAccess->other_info = $data['other_info'];
        
        try
        {
            if (!$propertyAccess->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $propertyAccess->getErrors());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }
        
        return WDSAPI::echoResultsAsJson(array('data' => null, 'error' => 0));
    }
    
    /**
     * API Method: resPropertyAccess/apiUpdatePropertyAccess
     * Description: Update an existing property access for WDSFire
     * 
     * Post data parameters:
     * @param int id
     * @param int property_id
     * @param int address_verified
     * @param string best_contact_number
     * @param string access_issues
     * @param string gate_code
     * @param string suppression_resources
     * @param string other_info
     * 
     * Post data example: 
     * {
     *     "data": {
     *         "id": 593,
     *         "property_id": 123456,
     *         "address_verified": 1,
     *         "best_contact_number": "564-874-8912",
     *         "access_issues": "",
     *         "gate_code": "1245",
     *         "suppression_resources": "He has three sprinklers",
     *         "other_info": ""
     *     }
     * }
     */
    public function actionApiUpdatePropertyAccess()
    {
        $data = NULL;
        
        $requiredFields = array('id','property_id','address_verified','best_contact_number','access_issues','gate_code','suppression_resources','other_info');
        
        if (!WDSAPI::getInputDataArray($data, $requiredFields))
            return;
        
        $propertyAccess = ResPropertyAccess::model()->findByPk($data['id']);
        
        if ($propertyAccess)
        {
            $propertyAccess->property_id = $data['property_id'];
            $propertyAccess->address_verified = $data['address_verified'];
            $propertyAccess->best_contact_number = $data['best_contact_number'];
            $propertyAccess->access_issues = $data['access_issues'];
            $propertyAccess->gate_code = $data['gate_code'];
            $propertyAccess->suppression_resources = $data['suppression_resources'];
            $propertyAccess->other_info = $data['other_info'];
            
            try
            {
                if (!$propertyAccess->save())
                    return WDSAPI::echoJsonError('ERROR: There was a database error.', $propertyAccess->getErrors());
            }
            catch (CDbException $e)
            {
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
            }
            
            return WDSAPI::echoResultsAsJson(array('data' => null, 'error' => 0));
        }
        
        return WDSAPI::echoJsonError('There was an error.', 'No model could be found with this id: ' . $data['id']);
    }
}