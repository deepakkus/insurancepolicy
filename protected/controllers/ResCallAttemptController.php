<?php

class ResCallAttemptController extends Controller
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
                        'apiGetCallTotals',
                        'apiGetCallDetails'
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
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetCallTotals',
                    'apiGetCallDetails'
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

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    #region CRUD Controllers


    /**
     * API method: resCallList/apiGetCallTotals
     * Description: Gets policyholder call totals start for a time range and client
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2016-03-01",
     *         "endDate": "2016-06-01",
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetCallTotals()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

        $sql = "
            DECLARE @startdate DATE = :start_date
            DECLARE @enddate DATE = :end_date
            DECLARE @clientID INT = :client_id

            SELECT
                a.res_fire_id,
                n.Name,
                COUNT(DISTINCT a.property_id) AS policyholders_called,
                COUNT(a.id) as calls_made,
                (
                    SELECT COUNT(ca.id)
                    FROM res_call_attempt ca
                    INNER JOIN properties pr ON ca.property_id = pr.pid
                    WHERE ca.contact_type IN ('Successful Contact (Enroll/Decline)', 'Successful Contact (Undecided)', 'Inbound', 'Policyholder')
                        AND ca.date_called >= @startdate
                        AND ca.date_called < @enddate
                        AND ca.res_fire_id = a.res_fire_id
                        AND pr.client_id = @clientID
                ) totalcontact_success,
                (
                    SELECT COUNT(ca.id)
                    FROM res_call_attempt ca
                    INNER JOIN properties pr ON ca.property_id = pr.pid
                    WHERE ca.contact_type IN ('Successful Contact (Enroll/Decline)', 'Policyholder')
                        AND ca.prop_res_status = 'enrolled'
                        AND ca.date_called >= @startdate
                        AND ca.date_called < @enddate
                        AND ca.res_fire_id = a.res_fire_id
                        AND pr.client_id = @clientID
                ) enrolled,
                (
                    SELECT COUNT(ca.id)
                    FROM res_call_attempt ca
                    INNER JOIN properties pr ON ca.property_id = pr.pid
                    WHERE ca.contact_type IN ('Successful Contact (Enroll/Decline)', 'Policyholder')
                    AND ca.prop_res_status = 'declined'
                        AND ca.date_called >= @startdate
                        AND ca.date_called < @enddate
                        AND ca.res_fire_id = a.res_fire_id
                        AND pr.client_id = @clientID
                ) declined,
                (
                    SELECT COUNT(ca.id)
                    FROM res_call_attempt ca
                    INNER JOIN properties pr ON ca.property_id = pr.pid
                    WHERE ca.contact_type IN ('Successful Contact (Undecided)', 'Policyholder')
                        AND ca.date_called >= @startdate
                        AND ca.date_called < @enddate
                        AND ca.res_fire_id = a.res_fire_id
                        AND pr.client_id = @clientID
                ) undecided
            FROM res_call_attempt a
                INNER JOIN res_fire_name n ON a.res_fire_id = n.Fire_ID
                INNER JOIN properties p ON a.property_id = p.pid
            WHERE a.date_called >= @startdate
                AND a.date_called < @enddate
                AND p.client_id = @clientID
            GROUP BY a.res_fire_id,n.Name
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':start_date', $data['startDate'], PDO::PARAM_STR)
            ->bindParam(':end_date', $data['endDate'], PDO::PARAM_STR)
            ->bindParam(':client_id', $data['clientID'], PDO::PARAM_INT)
            ->queryAll();

        WDSAPI::echoResultsAsJson(array('data' => $results, 'error' => 0));
    }

    /**
     * API method: resCallList/apiGetCallDetails
     * Description: Gets policyholder calls details for a time range and client
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2016-03-01",
     *         "endDate": "2016-06-01",
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetCallDetails()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

        $sql = "
            SELECT
                n.Name,
                p.policy,
                u.name,
                a.attempt_number,
                a.prop_res_status,
                a.contact_type,
                a.date_called
            FROM res_call_attempt a
                INNER JOIN res_fire_name n ON a.res_fire_id = n.Fire_ID
                INNER JOIN properties p ON a.property_id = p.pid
                INNER JOIN [user] u ON a.caller_user_id = u.id
                INNER JOIN members m ON p.member_mid = m.mid
            WHERE a.date_called >= :start_date
                AND a.date_called < :end_date
                AND p.client_id = :client_id
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':start_date', $data['startDate'], PDO::PARAM_STR)
            ->bindParam(':end_date', $data['endDate'], PDO::PARAM_STR)
            ->bindParam('client_id', $data['clientID'], PDO::PARAM_INT)
            ->queryAll();

        WDSAPI::echoResultsAsJson(array('data' => $results, 'error' => 0));
    }


}