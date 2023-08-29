<?php

class EngShiftTicketActivityController extends Controller
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
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetActivity',
                        'apiGetActivities',
                        'apiCreateActivity',
                        'apiUpdateActivity',
                        'apiDeleteActivity'
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
                    'renderReviewActivities',
                    'reviewActivity',
                    'createActivity',
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array(
                    'apiGetActivity',
                    'apiGetActivities',
                    'apiCreateActivity',
                    'apiUpdateActivity',
                    'apiDeleteActivity'
                ),
                'users'=>array('*'),
            ),
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
     * Render the tabke of shift ticket activites to review on the shift ticket form
     * @param integer $shiftTicketId
     * @return mixed
     */
    public function actionRenderReviewActivities($shiftTicketId)
    {
        $shiftTicketActivities = EngShiftTicketActivity::model()->findAll(array(
            'condition' => 'eng_shift_ticket_id = :id',
            'params' => array(':id' => $shiftTicketId),
            'with' => array(
                'engShiftTicketActivityType' => array(
                    'select' => 'type'
                )
            ),
            'order'=>'start_time'
        ));

        $shiftTicket = EngShiftTicket::model()->findByPk($shiftTicketId);
        $timeOverlap = 0;
        if(Yii::app()->user->hasState("timeOverlap"))
        {
            $timeOverlap = Yii::app()->user->getState("timeOverlap");
            Yii::app()->user->setState("timeOverlap", null);
        }
        return $this->renderPartial('_review_activities', array(
            'shiftTicketActivities' => $shiftTicketActivities,
            'shiftTicket' => $shiftTicket,
            'timeOverlap' => $timeOverlap
        ));
    }

    /**
     * Render view for review of shift ticket activity
     * @param integer $id
     * @return mixed
     */

    public function actionReviewActivity($id, $status = null)

    {
        $activity = $this->loadModel($id);

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'shift-ticket-modal-form')
        {
            echo CActiveForm::validate($activity);
            Yii::app()->end();
        }

        if (isset($_POST['EngShiftTicketActivity']))
        {
            $activity->attributes = $_POST['EngShiftTicketActivity'];
            $return = null;
            if ($activity->save())
            {
                $return = array('success' => true, 'error' => '');
            }
            else
            {
                $return = array('success' => false, 'error' => var_export($activity->getErrors(), true));
            }
            echo json_encode($return);
            Yii::app()->end();
        }

        $shiftTicket = EngShiftTicket::model()->findByPk($activity->eng_shift_ticket_id);
        $crew = EngCrewManagement::model()->find('user_id = :user_id', array(':user_id' => $shiftTicket->submitted_by_user_id));
        $assignments = EngCrewManagement::model()->getAssignments($crew->id, $shiftTicket->date);

        if ($status)
        {
            return $this->render('view', array(
            'activity' => $activity,
            'assignments' => $assignments,
            'shiftTicket' => $shiftTicket
            ));
        }
             return $this->renderPartial('_review_activity', array(
            'activity' => $activity,
            'assignments' => $assignments,
        ));
    }

    public function actionCreateActivity($shift_ticket_id)
    {
        $activity = new EngShiftTicketActivity();
        $activity->eng_shift_ticket_id = $shift_ticket_id;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'shift-ticket-modal-form')
        {
            echo CActiveForm::validate($activity);
            Yii::app()->end();
        }

        if (isset($_POST['EngShiftTicketActivity']))
        {
            $activity->attributes = $_POST['EngShiftTicketActivity'];
            $return = null;
            if ($activity->save())
            {
                $return = array('success' => true, 'error' => '');
            }
            else
            {
                $return = array('success' => false, 'error' => var_export($activity->getErrors(), true));
            }
            echo json_encode($return);
            Yii::app()->end();
        }

        return $this->renderPartial('_review_activity', array(
            'activity' => $activity,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EngSchedulingEmployee the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=EngShiftTicketActivity::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /* --------------------------------------- API Calls --------------------------------------------------*/

    /**
     * API Method: engShiftTicketActivity/apiGetActivity
     * Get's a shift ticket activity (individual time entry)
     *
     * Post data parameters:
     * @uses integer activity_id
     *
     * Return example:
     * {
     *     "data": {
     *         "id": "11",
     *         "eng_shift_ticket_activity_type_id": "3",
     *         "start_time": "08:00:10",
     *         "end_time": "10:00:50",
     *         "comment": "Comments here",
     *     }
     * }
     */
    public function actionApiGetActivity()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('activity_id')))
            return;

        $model = EngShiftTicketActivity::model()->findByPk($data['activity_id']);

        $returnArray['error'] = 0;
        $returnArray['data'] = $model->attributes;

        return WDSAPI::echoResultsAsJson($returnArray);

    }

    /**
     * API Method: engShiftTicketActivity/apiGetActivities
     * Get's a shift ticket activity (individual time entry)
     *
     * Post data parameters:
     * @uses integer activity_id
     *
     * Return example:
     * {
     *     "data": {
     *      [
     *         "id": "11",
     *         "eng_shift_ticket_activity_type_id": "3",
     *         "start_time": "08:00:00",
     *         "end_time": "10:00:00",
     *         "comment": "Comments here",
     *      ],
     *      [
     *         "id": "12",
     *         "eng_shift_ticket_activity_type_id": "4",
     *         "start_time": "10:00:00",
     *         "end_time": "11:00:00",
     *         "comment": "Comments here",
     *      ],
     *     }
     * }
     */
    public function actionApiGetActivities()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        $shiftTicketActivity = new EngShiftTicketActivity();
        $shiftTicketActivity->eng_shift_ticket_id = $data['id'];
        $activities = $shiftTicketActivity->getAllActivities();

        $returnArray['error'] = 0;
        $returnArray['data'] = $activities;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engShiftTicketActivity/apiCreateActivity
     * Creates an action for a shift ticket
     *
     * Post data parameters:
     * @param integer eng_shift_ticket_id
     * @param integer eng_shift_ticket_activity_type_id
     * @param string start_time
     * @param string end_time
     * @param string comment
     * @param string billable
     *
     * Post data example:
     * {
     *     "data": {
     *         "eng_shift_ticket_id": "11",
     *         "eng_shift_ticket_activity_type_id": "3",
     *         "start_time": "08:00",
     *         "end_time": "10:00",
     *         "comment": "Comments here",
     *     }
     * }
     */
    public function actionApiCreateActivity()
    {
        $data = NULL;

        $requiredFields = array('eng_shift_ticket_id','eng_shift_ticket_activity_type_id','start_time','end_time','comment','crew_id','billable');

        if (!WDSAPI::getInputDataArray($data, $requiredFields))
            return;

        $shiftTicketActivity = new EngShiftTicketActivity();

        $shiftTicketActivity->eng_shift_ticket_id = $data['eng_shift_ticket_id'];
        $shiftTicketActivity->eng_shift_ticket_activity_type_id = $data['eng_shift_ticket_activity_type_id'];
        $shiftTicketActivity->start_time = $data['start_time'];
        $shiftTicketActivity->end_time = $data['end_time'];
        $shiftTicketActivity->comment = $data['comment'];
        $shiftTicketActivity->res_ph_visit_id = $data['res_ph_visit_id'];
        $shiftTicketActivity->billable = $data['billable'];
        $shiftTicketActivity->tracking_location = $data['tracking_location'];
        $shiftTicketActivity->tracking_location_end = $data['tracking_location_end'];

        $starttime = date('H:i',strtotime($data['start_time']));
        $endtime = date('H:i',strtotime($data['end_time']));

        if(EngShiftTicketActivity::model()->count('eng_shift_ticket_id=:sid AND ((:st >= start_time AND :stt < end_time) OR (:et BETWEEN start_time AND end_time))',array('sid'=>$data['eng_shift_ticket_id'],'st'=>$starttime,'stt'=>$starttime,'et'=>$endtime)) > 0)
        {
                $activities = $shiftTicketActivity->getAllActivities();
                $returnArray['error'] = 1;
                $returnArray['data'] = $activities;
        }
        else
        {
            if($starttime==$endtime)
            {
                $activities = $shiftTicketActivity->getAllActivities();
                $returnArray['error'] = 3;
                $returnArray['data'] = $activities;
            }
            else
            {
                if ($shiftTicketActivity->save())
                {
                    $activities = $shiftTicketActivity->getAllActivities();
                    $returnArray['error'] = 0;
                    $returnArray['data'] = $activities;
                }
                else
                {
                    $returnArray['error'] = 1;
                    $returnArray['data'] = null;
                }
            }
       }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engShiftTicketActivity/apiUpdateActivity
     * Updates an action for a shift ticket
     *
     * Post data parameters:
     * @param integer id
     * @param integer eng_shift_ticket_activity_type_id
     * @param string start_time
     * @param string end_time
     * @param string comment
     * @param string billable
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": "11",
     *         "eng_shift_ticket_activity_type_id": "3",
     *         "start_time": "08:00",
     *         "end_time": "10:00",
     *         "comment": "Comments here",
     *     }
     * }
     */
    public function actionApiUpdateActivity()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('id','eng_shift_ticket_activity_type_id','start_time','end_time','comment','billable')))
            return;

        $shiftTicketActivity = EngShiftTicketActivity::model()->findByPk($data['id']);

        $shiftTicketActivity->eng_shift_ticket_activity_type_id = $data['eng_shift_ticket_activity_type_id'];
        $shiftTicketActivity->start_time = $data['start_time'];
        $shiftTicketActivity->end_time = $data['end_time'];
        $shiftTicketActivity->comment = $data['comment'];
        $shiftTicketActivity->res_ph_visit_id = $data['res_ph_visit_id'];
        $shiftTicketActivity->billable = $data['billable'];
        $shiftTicketActivity->tracking_location = $data['tracking_location'];
        $shiftTicketActivity->tracking_location_end = $data['tracking_location_end'];

        $starttime = date('H:i',strtotime($data['start_time']));
        $endtime = date('H:i',strtotime($data['end_time']));

        if(EngShiftTicketActivity::model()->count('eng_shift_ticket_id=:sid AND id <> :id AND ((:st >= start_time AND :stt < end_time) OR (:et BETWEEN start_time AND end_time))',array('sid'=>$shiftTicketActivity->eng_shift_ticket_id,'id'=>$shiftTicketActivity->id,'st'=>$starttime,'stt'=>$starttime,'et'=>$endtime)) > 0)
        {
             $shiftTicketActivity->SaveAttributes(array('eng_shift_ticket_activity_type_id','comment','billable'));
             $activities = $shiftTicketActivity->getAllActivities();

                $returnArray['error'] = 1;
                $returnArray['data'] = $activities;
        }
        else
        {
            if($starttime==$endtime)
            {
                $activities = $shiftTicketActivity->getAllActivities();
                $returnArray['error'] = 3;
                $returnArray['data'] = $activities;
            }
            else
            {   
                if ($shiftTicketActivity->save())
                {
                    $activities = $shiftTicketActivity->getAllActivities();

                    $returnArray['error'] = 0;
                    $returnArray['data'] = $activities;
                }
                else
                {
                    $returnArray['error'] = 1;
                    $returnArray['data'] = null;
                }
            }
       }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engShiftTicketActivity/apiDeleteActivity
     * Deletes an action for a shift ticket
     *
     * Post data parameters:
     * @param integer activity_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "activity_id": "11",
     *     }
     * }
     */
    public function actionApiDeleteActivity()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('activity_id')))
            return;

        $model = EngShiftTicketActivity::model()->findByPk($data['activity_id']);
        $model->delete();
        $activities = $model->getAllActivities();
        $model->delete();

        $returnArray['error'] = 0;
        $returnArray['data'] = $activities;

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}
