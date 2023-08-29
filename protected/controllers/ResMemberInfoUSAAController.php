<?php

class ResMemberInfoUSAAController extends Controller
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
                        'apiGetMemberActions',
                        'apiGetSingleMember'
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
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array(
                    'apiGetMemberActions',
                    'apiGetSingleMember'
                ),
				'users'=>array('*')),
			array('deny',
				'users'=>array('*'),
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
     * API Method: resMemberInfoUSAA/apiGetMemberActions
     * Description: Gets all member actions for a specific notice
     * 
     * Post data parameters:
     * @param int noticeID - ID of the notice
     * @param int response_status - response status
     * 
     * Post data example: 
     * { "data": { "noticeID": 123, "response_status": 0 } }
     */    
	public function actionApiGetMemberActions()
	{
        if (!WDSAPI::getInputDataArray($data, array('noticeID', 'response_status')))
            return;        
        
        $returnData = $this->getPolicyActions(null, $data['noticeID'], $data['response_status']);
        
        return WDSAPI::echoResultsAsJson($returnData);
	}
    
    /**
     * API Method: resMemberInfoUSAA/apiGetSingleMember
     * Description: Gets member actions for a specific action.
     * 
     * Post data parameters:
     * @param int actionID - ID of the action
     * 
     * Post data example: 
     * { "data": { "actionID": 123 } }
     */    
	public function actionApiGetSingleMember()
	{
        if (!WDSAPI::getInputDataArray($data, array('actionID')))
            return;

        $returnData = $this->getPolicyActions($data['actionID'], null, null);
        
        return WDSAPI::echoResultsAsJson($returnData);
	}

    /**
     * Gets policy actions by either action ID or notice ID and response status
     * @return array data
     */
    private function getPolicyActions($actionID, $noticeID, $responseStatus)
    {
        $returnData = array();
        
        $criteria = new CDbCriteria;
        $criteria->with = array('resTriggered' => array('joinType' => 'LEFT JOIN'));
        
        if (isset($actionID))
            $criteria->addCondition("t.Action_ID = $actionID");
        else
            $criteria->addCondition("t.Notice_ID = $noticeID");
        
        $memberActions = ResMemberInfoUsaa::model()->findAll($criteria);
        
        if ($memberActions != null)
        {
            foreach ($memberActions as $entry)
            {
                //Make sure the return value for response status is consistant whether it's from the triggered table or the property table
                $responseStatus = (isset($entry->resTriggered->response_status) && $entry->resTriggered->response_status) ? $entry->resTriggered->response_status : $entry->property->response_status;
                if($responseStatus == 1 || $responseStatus == 'enrolled')
                    $responseStatus = 'enrolled';
                else
                    $responseStatus = 'not enrolled';
                
                //Check if entry exists in triggered table, otherwise they aren't threatened (default)
                $threat = (isset($entry->resTriggered->threat) && $entry->resTriggered->threat) ? $entry->resTriggered->threat : 0;
                
                $returnData[] = array_merge($entry->attributes, array(
                    "Member_Name" => $entry->property->member->last_name . ", " . $entry->property->member->first_name,
                    "Member_Num" => $entry->property->member->member_num,
                    "Policy" => $entry->property->policy,
                    "Address" => $entry->property->address_line_1,
                    "City" => $entry->property->city,
                    "State" => $entry->property->state,
                    "Threat" => $threat,
                    "Response_Status" =>$responseStatus,
                ));
            }
        }
        
        return $returnData;
    }
}