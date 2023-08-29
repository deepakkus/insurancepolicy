<?php

class ResPhActionController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
            'postOnly + deletePhoto',
             array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetActionType',
                        'apiGetVisitActions'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetActionType',
                        'apiGetVisitActions'
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
				'actions'=>array(
                    'admin',
                    'create'
                ),
				'users' => array('@')
			),
            array('allow',
				'actions' => array(
                    'apiGetActionType',
                    'apiGetVisitActions'
                ),
				'users' => array('*')
            ),
			array('deny',
				'users' => array('*')
			)
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

     /*
    API Method: ResPhVisit/actionApiGetActionType
     * Description: Get Action Type Dropdown
     */
    public function actionApiGetActionType()
    {
        if (!WDSAPI::getInputDataArray($data, array('cid')))
           return;
       // $data['cid'] = 1;
        $returnData  = array();
        $types = ResPhActionType::model()->findAllByAttributes(array('category_id' => $data['cid'], 'active' => true), array('order' => 'name ASC'));
        foreach($types as $type)
        {
            $returnData[] = array('id' => $type->id, 'name'=> $type->name);
        }
       // echo '<pre>'; print_r($returnData);
        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $returnData));
    }
    /*
    *   API Method: ResPhVisit/actionApiGetVisitActions
    *   Description: Get visit action name, qty
    */
    public function actionApiGetVisitActions()
    {
        if (!WDSAPI::getInputDataArray($data, array('vid')))
           return;
        $returnData  = array();
        $actions = ResPhAction::model()->getVisitActions($data['vid']);
        foreach($actions as $action)
        {
            $returnData[] = array('id' => $action->id, 'visit_id'=> $action->visit_id, 'action_type_id'=> $action->action_type_id, 'qty'=>$action->qty,'name'=>$action->actionTypeName);
        }
        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $returnData));
    }
}
