<?php

class WdsfireEnrollmentsController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
			'postOnly + delete',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetPolicyholder',
                        'apiPolicyholderSearch',
                        'apiCreateWdsfireEnrollment'
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
                'actions' => array(
                    'admin'
                ),
                'users' => array('@')
            ),
			array('allow',
				'actions' => array(
                    'apiGetPolicyholder',
                    'apiPolicyholderSearch',
                    'apiCreateWdsfireEnrollment'
                ),
				'users' => array('*')
            ),
            array('deny',
                'users' => array('*'),
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

	/**
     * Manages all models.
     */
	public function actionAdmin()
	{
		$model = new WdsfireEnrollments('search');
		$model->unsetAttributes();
		if (isset($_GET['WdsfireEnrollments']))
			$model->attributes = $_GET['WdsfireEnrollments'];

        $dataProvider = $model->search();

		$this->render('admin',array(
			'model' => $model,
            'dataProvider' => $dataProvider
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return WdsfireEnrollments the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=WdsfireEnrollments::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    /**
     * API Method: wdsfireEnrollments/apiGetPolicyholder
     * Description: Returns policyholder information using it's pid
     *
     * Post data parameters:
     * @param integer pid
     *
     * Post data example:
     * {
     *     "data": {
     *         "pid" : 10255
     *     }
     * }
     */
    public function actionApiGetPolicyholder()
    {
		$data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('pid')))
			return;

        $criteria = new CDbCriteria;
        $criteria->addCondition('t.pid = :pid');
        $criteria->params[':pid'] = $data['pid'];
        $criteria->with = array(
            'member' => array(
                'select' => array('mid','member_num','first_name','last_name')
            )
        );

        $property = Property::model()->find($criteria);

        $return_array = array(
            'data' => array(),
            'error' => 0
        );

        if ($property)
        {
            $return_array['data'] = array(
                'pid' => $property->pid,
                'first_name' => $property->member->first_name,
                'last_name' => $property->member->last_name,
                'address_line_1' => $property->address_line_1,
                'policy' => $property->policy,
                'member_num' => $property->member->member_num,
                'city' => $property->city,
                'state' => $property->state,
                'zip' => $property->zip,
                'lat' => $property->lat,
                'long' => $property->long,
                'response_status' => $property->response_status
            );
        }
        else
        {
            echo WDSAPI::echoJsonError('There was an error.', 'A property could not be found with that pid.');
        }

        echo WDSAPI::echoResultsAsJson($return_array);
    }

    /**
     * API Method: wdsfireEnrollments/apiPolicyholderSearch
     * Description: Returns policyholder information based on a combination of policyNumber,
     * memberNumber, and client as search paramaters.
     *
     * Post data parameters:
     * @param integer clientID
     * @param string policyNumber
     * @param string memberNumber
     * @param integer wildcardSearch
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID" : 3,
     *         "policyNumber": "H3226100072810",
     *         "memberNumber": "",
     *         "wildcardSearch": 0
     *     }
     * }
     */
    public function actionApiPolicyholderSearch()
    {
		$data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('clientID','policyNumber','memberNumber','wildcardSearch')))
			return;

        $policyNumber = $data['policyNumber'];
        $memberNumber = $data['memberNumber'];
        $wildcardSearch = filter_var($data['wildcardSearch'], FILTER_VALIDATE_BOOLEAN);

        if (empty($policyNumber) && empty($memberNumber))
            return WDSAPI::echoJsonError('Either a policy number or member number must be submitted in order to return a search result.');

        $criteria = new CDbCriteria;
        $criteria->with = array(
            'member' => array(
                'select' => array('mid','member_num','first_name','last_name')
            )
        );
        $criteria->limit = 20;
        $criteria->order = 't.policy ASC';
        $criteria->addCondition('t.policy_status = :status');
        $criteria->addCondition('t.client_id = :clientID');
        $criteria->params = array(
            ':status' => 'active',
            ':clientID' => $data['clientID']
        );

        if (!(empty($policyNumber)))
        {
            if ($wildcardSearch)
            {
                $criteria->addSearchCondition('t.policy', $policyNumber);
            }
            else
            {
                $criteria->addCondition('t.policy = :policyNumber');
                $criteria->params[':policyNumber'] = $policyNumber;
            }
        }

        if (!(empty($memberNumber)))
        {
            if ($wildcardSearch)
            {
                $criteria->addSearchCondition('member.member_num', $memberNumber);
            }
            else
            {
                $criteria->addCondition('member.member_num = :memberNumber');
                $criteria->params[':memberNumber'] = $memberNumber;
            }
        }

        $properties = Property::model()->findAll($criteria);

        $return_array = array(
            'data' => array(),
            'error' => 0
        );

        if ($properties)
        {
            foreach ($properties as $property)
            {
                $return_array['data'][] = array(
                    'pid' => $property->pid,
                    'first_name' => $property->member->first_name,
                    'last_name' => $property->member->last_name,
                    'address_line_1' => $property->address_line_1,
                    'policy' => $property->policy,
                    'member_num' => $property->member->member_num,
                    'city' => $property->city,
                    'state' => $property->state,
                    'zip' => $property->zip,
                    'lat' => $property->lat,
                    'long' => $property->long,
                    'response_status' => $property->response_status
                );
            }
        }
        else
        {
            return WDSAPI::echoJsonError('A policy could not be found for this policy number');
        }

        return WDSAPI::echoResultsAsJson($return_array);
    }


    /**
     * API Method: wdsfireEnrollments/apiCreateWdsfireEnrollment
     * Description: Creates a new wdsfire enrollment entry
     *
     * Post data parameters:
     * @param integer userID
     * @param integer clientID
     * @param integer fireID
     * @param integer pid
     * @param string status
     *
     * Post data example:
     * {
     *     "data": {
     *         "userID": 23
     *         "clientID" : 3,
     *         "fireID": null,
     *         "pid": "H3226100072810",
     *         "status": "enrolled"
     *     }
     * }
     */
    public function actionApiCreateWdsfireEnrollment()
    {
		$data = NULL;

		if (!WDSAPI::getInputDataArray($data, array('userID','clientID','fireID','pid','status')))
			return;

        $enrollmentStatus = CHtml::listData(EnrollmentStatus::model()->findAll(), 'status', 'id');

        if (!array_key_exists($data['status'], $enrollmentStatus))
        {
            return WDSAPI::echoJsonError('ERROR: enrollment status does not exist.', 'Enrollment status must be one of ' . implode(', ', array_keys($enrollmentStatus)) . '.');
        }

        $wdsEnrollment = new WdsfireEnrollments;
        $wdsEnrollment->user_id = $data['userID'];
        $wdsEnrollment->client_id = $data['clientID'];
        $wdsEnrollment->fire_id = $data['fireID'];
        $wdsEnrollment->pid = $data['pid'];
        $wdsEnrollment->status_id = $enrollmentStatus[$data['status']];
        $wdsEnrollment->date = date('Y-m-d H:i');

        try
        {
            if (!$wdsEnrollment->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $wdsEnrollment->getErrors());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        return WDSAPI::echoResultsAsJson(array('data' => null, 'error' => 0));
    }
}
