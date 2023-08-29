<?php

class ResPostIncidentSummaryController extends Controller
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
                        'apiGetPostIncidentSummary',
                        'apiGetAllPostIncidentSummary'
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
                    'index',
                    'admin',
                    'create',
                    'update',
                    'search'
                ),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetPostIncidentSummary',
                    'apiGetAllPostIncidentSummary'
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ResNotice the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=ResPostIncidentSummary::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    public function actionAdmin()
    {
        $model=new ResPostIncidentSummary('search');
        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['ResPostIncidentSummary']))
            $model->attributes=$_GET['ResPostIncidentSummary'];

        $this->render('admin',
            array('model'=>$model)
        );
    }

    public function actionCreate($client)
    {
        $model=new ResPostIncidentSummary;

        if(isset($_POST['ResPostIncidentSummary']))
        {
            $model->attributes=$_POST['ResPostIncidentSummary'];
            if($model->save())
            {
                Yii::app()->user->setFlash('success', "Post Incident Summary ".$model->id." Created Successfully!");
                $this->redirect(array('admin'));
            }
        }

        $this->render('create',array(
            'model'=>$model,
            'client'=>$client
        ));

    }

    public function actionUpdate($id)
    {

        $model = $this->loadModel($id);
        $client = $_GET['client'];

        if(isset($_POST['ResPostIncidentSummary']))
        {
            $model->attributes=$_POST['ResPostIncidentSummary'];
            if($model->save())
            {
                Yii::app()->user->setFlash('success', "Post Incident Summary Entry ".$model->id." Updated Successfully!");
                $this->redirect(array('admin'));
            }
        }

        $this->render('update', array(
            'model'=>$model,
            'client'=>$client
        ));
    }

    /**
     * API method: resPostIncidentSummary/apiGetPostIncidentSummary
     * Description: Gets the post incident summary for a given fire
     *
     * Post data parameters:
     * @param int clientID - client to filter the results by.
     * @param int fireID - fire for which to get the post incident summary for
     *
     * Post data example: {"data": {"clientID": 3, "fireID": 7364}}
     */
    public function actionApiGetPostIncidentSummary()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('clientID', 'fireID')))
            return;

        //Get the post incident summary
        $criteria = new CDbCriteria;
        $criteria->addCondition("client_id = " . $data['clientID']);
        $criteria->addCondition("fire_id = " . $data['fireID']);
        if(isset($data['status']))
        {
            $criteria -> addCondition("published = " .$data['status']);
        }
        else
        {
            $criteria -> addCondition("published = 1");
        }
        
        //Get client object
        $client = Client::model()->findByPk($data['clientID']);
        $clientID = $client->id;

        //Get the post incident summary
        $model = ResPostIncidentSummary::model()->findAll($criteria);

        if ($model)
        {
            $fireID = $model[0]->fire->Fire_ID;

            // Fill in attributes from model, and get a few from the res_fire_name table
            $returnArray['data'] = $model[0]->attributes;
            $returnArray['data']['fire_name'] = $model[0]->fire->Name;
            $returnArray['data']['fire_city'] = $model[0]->fire->City;
            $returnArray['data']['fire_state'] = $model[0]->fire->State;
            $returnArray['data']['location_description'] = $model[0]->fire->Location_Description;
            $returnArray['data']['cause'] = $model[0]->fire->Cause;
            $returnArray['data']['contained'] = $model[0]->fire->Contained;

            // Return the final notice id, so that other data can get pulled
            // Note that a "demob" notice must exist!
            $criteria = new CDbCriteria;
            $criteria->addCondition("client_id = " . $data['clientID']);
            $criteria->addCondition("fire_id = " . $data['fireID']);
            $criteria->addCondition("wds_status = 3");
            $criteria->order = 'notice_id DESC';
            $finalNotice = ResNotice::model()->findAll($criteria);
            $returnArray['data']['final_notice_id'] = ($finalNotice && $finalNotice[0]) ? $finalNotice[0]->notice_id : null;
            $returnArray['data']['date_demobilized'] = ($finalNotice && $finalNotice[0]) ? $finalNotice[0]->date_created : null;


            // Get's the first dispatched notice...used to get the
            $sql = "select top 1 date_created from res_notice where fire_id = :fire_id and client_id = :client_id and wds_status = 1 order by notice_id asc";
            $dateDispatched = Yii::app()->db->createCommand($sql)
                ->bindParam(':fire_id', $fireID, PDO::PARAM_INT)
                ->bindParam(':client_id', $clientID, PDO::PARAM_INT)
                ->queryScalar();
            $returnArray['data']['date_dispatched'] = $dateDispatched;

            // Get the current size
            $criteria = new CDbCriteria;
            $criteria->addCondition("fire_id = " . $data['fireID']);
            $criteria->order = "Obs_ID DESC";
            $criteria->limit = 1;
            $fireObs = ResFireObs::model()->findAll($criteria);

            $returnArray['data']['size'] = null;
            $returnArray['data']['containment'] = null;

            if (isset($fireObs, $fireObs[0]))
            {
                $returnArray['data']['size'] = $fireObs[0]->Size;
                $returnArray['data']['containment'] = $fireObs[0]->Containment;
            }


            $finalNoticeDate = $finalNotice[0]->date_created;
            $clientID = $data['clientID'];

            // Now get a count of the policyholders visited
            $returnArray['data']['policyholders_visited'] = ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, null, null);
            // If client is signed up for wds edu (pre risk)
            $returnArray['data']['policyholders_edu_visited'] = ($client->wds_education) ? ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, null, true) : null;

            // Get a count of the policyholders that were lost
            $returnArray['data']['policyholders_lost'] =  ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, 'lost');
            // If client is signed up for wds edu (pre risk)
            $returnArray['data']['policyholders_edu_lost'] = ($client->wds_education) ? ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, 'lost', true) : null;

            // Get a count of the policyholders that were damaged
            $returnArray['data']['policyholders_damaged'] = ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, 'damaged');
            // If client is signed up for wds edu (pre risk)
            $returnArray['data']['policyholders_edu_damaged'] = ($client->wds_education) ? ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, 'damaged', true) : null;

            // Get a count of the policyholders that were saved by wds
            $returnArray['data']['policyholders_wds_saved'] = ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, 'saved');
            // If client is signed up for wds edu (pre risk)
            $returnArray['data']['policyholders_edu_saved'] = ($client->wds_education) ? ResPhVisit::getCountPolicyHomes($clientID, $fireID, $finalNoticeDate, 'saved', true) : null;

            $returnArray['data']['policyholders_physical_total'] = 0;
            $returnArray['data']['policyholders_physical_enrolled'] = 0;
            $returnArray['data']['policyholders_recon_total'] = 0;
            $returnArray['data']['policyholders_recon_enrolled'] = 0;

            $returnArray['data']['policyholders_physical_total'] +=    ResPhAction::getCountActionsByActionType($clientID, $fireID, false, 'Physical');
            $returnArray['data']['policyholders_physical_enrolled'] += ResPhAction::getCountActionsByActionType($clientID, $fireID, true, 'Physical');
            $returnArray['data']['policyholders_recon_total'] +=       ResPhAction::getCountActionsByActionType($clientID, $fireID, false, 'Recon');
            $returnArray['data']['policyholders_recon_enrolled'] +=    ResPhAction::getCountActionsByActionType($clientID, $fireID, true, 'Recon');

            $returnArray['data']['sprinklers'] =      ResPhAction::getCountActionsByName($clientID, $fireID, array('sprinklers', 'sprinklers_removed'));
            $returnArray['data']['gel'] =             ResPhAction::getCountActionsByName($clientID, $fireID, array('gel'));
            $returnArray['data']['fuel_mitigation'] = ResPhAction::getCountActionsByName($clientID, $fireID, array('fuel_mitigation'));
        }

        //No errors
        $returnArray['error'] = 0;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resPostIncidentSummary/apiGetAllPostIncidentSummary
     * Description: Gets all post incident summaries for a client.
     *
     * Post data parameters:
     * @param int clientID - client to filter the results by.
     * @param string startDate - filters the results by startDate.
     * @param string endDate - filters the results by endDate.
     *
     * Post data example: {"data": {"clientID": 3,"startDate": 2014-01-01,"endDate": 2014-01-31}}
     */
    public function actionApiGetAllPostIncidentSummary()
    {
        $data = NULL;
        $returnArray = array();
        $startDate = NULL;
        $endDate = NULL;
        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        //Get the post incident summaries
                
        if(isset($data['startDate']))
        {
            $startDate = $data['startDate'];
        }
        if(isset($data['endDate']))
        {
            $endDate = $data['endDate'];
        }
        $sql = "
        select 
        s.fire_id,
        s.client_id,
        s.client_id,
        s.wds_actions,
        s.date_created,
        s.date_updated,
        o.Obs_ID
        from res_post_incident_summary s 
        inner join res_fire_obs o on o.Fire_ID = s.fire_id
        where s.client_id = ".$data['clientID']."
        and s.published = 1
        and
        o.Obs_ID in
        (
        SELECT
            n.obs_id
            FROM res_notice n
        INNER JOIN res_fire_name f ON f.fire_id = n.fire_id
        WHERE notice_id IN (
            SELECT MAX(n.notice_id) AS notice_id FROM res_notice n
            INNER JOIN res_fire_obs o ON o.fire_id = n.fire_id
            WHERE
            (
                (n.date_updated >= '".$startDate."' and n.date_updated < '".$endDate."' ) OR
                (o.date_updated >= '".$startDate."' and o.date_updated < '".$endDate."' )
            )
            AND n.client_id = ".$data['clientID']."
			
            AND n.publish = 1
            GROUP BY n.fire_id
        )
		AND n.date_created>='".$startDate."' and n.date_created <='".$endDate."'
        )";

        $models = ResPostIncidentSummary::model()->findAllBySql($sql);
        foreach($models as $model){

            $returnData = array();

            //Fill in attributes from model, and get a few from the res_fire_name table
            $returnData = $model->attributes;
            $returnData['fire_name'] = $model->fire->Name;
            $returnData['fire_city'] = $model->fire->City;
            $returnData['fire_state'] = $model->fire->State;
            $returnData['fire_summary'] = $model->fire->Fire_Summary;
            $returnData['cause'] = $model->fire->Cause;
            $returnData['contained'] = $model->fire->Contained;

            $returnArray['data'][] = $returnData;

        }

        //No errors
        $returnArray['error'] = 0;

        WDSAPI::echoResultsAsJson($returnArray);
    }

}