<?php

class EngSchedulingEmployeeController extends Controller
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
                        'apiGetAllAssignedFires',
                        'apiGetAssignedFire',
                        'apiGetAllAssignments',
                        'apiGetResourceOrder'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetAllAssignedFires',
                        'apiGetAssignedFire',
                        'apiGetAllAssignments',
                        'apiGetResourceOrder'
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
                    'create',
                    'update',
                    'admin',
                    'view',
                    'delete',
                    'copyScheduledEmployees'
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array(
                    'apiGetAllAssignedFires',
                    'apiGetAssignedFire',
                    'apiGetAllAssignments',
                    'apiGetResourceOrder'
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EngSchedulingEmployee the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=EngSchedulingEmployee::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param EngSchedulingEmployee $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='eng-scheduling-employee-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->render('view',array(
            'model'=>$this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model=new EngSchedulingEmployee;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['EngSchedulingEmployee']))
        {
            $model->attributes=$_POST['EngSchedulingEmployee'];
            if($model->save())
                $this->redirect(array('view','id'=>$model->id));
        }

        $this->render('create',array(
            'model'=>$model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model=$this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['EngSchedulingEmployee']))
        {
            $model->attributes=$_POST['EngSchedulingEmployee'];
            if($model->save())
                $this->redirect(array('view','id'=>$model->id));
        }

        $this->render('update',array(
            'model'=>$model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $dataProvider=new CActiveDataProvider('EngSchedulingEmployee');
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new EngSchedulingEmployee('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['EngSchedulingEmployee']))
            $model->attributes=$_GET['EngSchedulingEmployee'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    /**
     * Recieve engine_scheduling_id and copy employees from that to new entry
     */
    public function actionCopyScheduledEmployees($lastid, $engineid)
    {
        $lastEmployeeModels = EngSchedulingEmployee::model()->findAllByAttributes(array('engine_scheduling_id' => $lastid));
        $currentEngineModel = EngScheduling::model()->findByPk($engineid);

        foreach($lastEmployeeModels as $employee)
        {
            $model = new EngSchedulingEmployee;
            $model->crew_id = $employee->crew_id;
			$model->scheduled_type = 'FFT';
            $model->start_time = '09:00';
            $model->end_time = '18:00';
            $model->start_date = Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm', $currentEngineModel->start_date . ' ' . $currentEngineModel->start_time);
            $model->end_date = Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm', $currentEngineModel->end_date . ' ' . $currentEngineModel->end_time);
            $model->engine_scheduling_id = $engineid;
            $model->save();
        }

        $this->redirect(array('/engScheduling/update', 'id' => $engineid, 'employeeID' => ''));
    }

    /**
     * API Method: engSchedulingEmployee/apiGetAllAssignedFires
     * Description: Sending top 20 fire information for a given crew id.
     *
     * Post data parameters:
     * @param integer crew_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "crew_id" : 59
     *     }
     * }
     */
    public function actionApiGetAllAssignedFires()
    {
        $data = NULL;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('crew_id')))
            return;

        $sql = "
            SELECT TOP 10
                s.id scheduling_id,
	            FORMAT(e.start_date, 'MM/dd/yyyy') employee_start,
	            FORMAT(e.end_date, 'MM/dd/yyyy') employee_end,
	            f.Name fire_name,
	            f.City city,
	            f.State state
            FROM eng_scheduling_employee e
            INNER JOIN eng_scheduling s ON e.engine_scheduling_id = s.id
            LEFT OUTER JOIN res_fire_name f ON s.fire_id = f.Fire_ID
            WHERE s.fire_id IS NOT NULL AND e.crew_id = :crew_id
            ORDER BY e.start_date DESC
        ";

        $results = Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':crew_id' => $data['crew_id']
        ));

        foreach ($results as $result)
        {
            $returnData[] = array(
                'scheduling_id' => $result['scheduling_id'],
                'employee_start' => $result['employee_start'],
                'employee_end' => $result['employee_end'],
                'fire_name' => $result['fire_name'],
                'city' => $result['city'],
                'state' => $result['state']
            );
        }

        $returnArray['error'] = 0;
        $returnArray['data']= $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engSchedulingEmployee/apiGetAssignedFire
     * Description: Sending fire information for a given crew id and optional scheduling id
     *
     * Post data parameters:
     * @param integer crew_id
     * @param integer sid (optional) scheduling id
     *
     * Post data example:
     * {
     *     "data": {
     *         "crew_id" : 59
     *     }
     * }
     *
     * Return Example:
     * {
     *   "error": 0,
     *   "data": {
     *     "id": "3877",
     *     "start_date": "02/01/2017",
     *     "end_date": "10/06/2017",
     *     "fire_id": "17504",
     *     "scheduling_id": "3877",
     *     "fire_name": "South Boulder Fire",
     *     "city": "Boulder",
     *     "state": "CO",
     *     "fire_officer": "ERIC MORRIS",
     *     "fire_officer_office_contact": "406-586-5400",
     *     "fire_officer_mobile_contact": "406-570-6208",
     *     "fire_officer_email": "emorris@wildfire-defense.com",
     *     "perimeter_id": "10190",
     *     "clients": [
     *       {
     *         "id": "1007",
     *         "name": "Insurance Company",
     *         "noticeID": "15811",
     *         "triageNoticeID": "15811"
     *       },
     *       {
     *         "id": "2",
     *         "name": "Chubb",
     *         "noticeID": "15813",
     *         "triageNoticeID": null
     *       }
     *     ]
     *   }
     * }
     */
    public function actionApiGetAssignedFire()
    {
        $data = NULL;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('crew_id')))
            return;

        $sid = isset($data['sid']) ? $data['sid'] : null;

        $sql = "
            DECLARE @now datetime = GETDATE();

            SELECT
                e.id,
	            e.start_date employee_start,
	            e.end_date employee_end,
	            s.fire_id,
	            s.id scheduling_id,
	            f.Name fire_name,
	            f.City city,
	            f.State state,
	            CASE
		            WHEN s.fire_officer_id IS NULL THEN 'contact WDS'
		            ELSE c.first_name + ' ' + c.last_name
	            END fire_officer,
	            ISNULL(c.work_phone, 'contact WDS') fire_officer_office_contact,
	            ISNULL(c.cell_phone, 'contact WDS') fire_officer_mobile_contact,
	            ISNULL(c.email, 'contact WDS') fire_officer_email
            FROM eng_scheduling_employee e
            INNER JOIN eng_scheduling s ON e.engine_scheduling_id = s.id
            LEFT OUTER JOIN res_fire_name f ON s.fire_id = f.Fire_ID
            LEFT OUTER JOIN eng_crew_management c ON s.fire_officer_id = c.id
            WHERE s.fire_id IS NOT NULL AND e.crew_id = :crew_id
        ";

        $params = array();
        $params[':crew_id'] = $data['crew_id'];

        // A schedule id is received
        if ($sid !== null)
        {
            $sql .= ' AND s.id = :schedule_id';
            $params[':schedule_id'] = $sid;
        }
        // Get data from current date
        else
        {
            $sql .= ' AND e.start_date <= @now AND e.end_date >= @now';
        }

        $result = Yii::app()->db->createCommand($sql)->queryRow(true, $params);

        // Schedule was found
        if ($result)
        {
            // Get clients

            $clientsSQL = '
                DECLARE @now datetime = GETDATE();

                SELECT e.client_id, c.name
                FROM eng_scheduling_client e
                INNER JOIN client c ON e.client_id = c.id
                WHERE engine_scheduling_id = :engine_scheduling_id
            ';

            if ($sid === null)
            {
                $clientsSQL .= ' AND start_date <= @now and end_date >= @now';
            }

            $scheduledClients = Yii::app()->db->createCommand($clientsSQL)->queryAll(true, array(
                ':engine_scheduling_id' => $result['scheduling_id']
            ));

            $clientIDs = array();
            $clients = array();
            foreach ($scheduledClients as $scheduledClient)
            {
                $clientIDs[] = $scheduledClient['client_id'];
                $clients[] = array(
                    'id' => $scheduledClient['client_id'],
                    'name' => $scheduledClient['name'],
                    'noticeID' => null,
                    'triageNoticeID' => null
                );
            }

            // Perimeter

            $perimeterSQL = 'SELECT TOP 1 id FROM res_perimeters WHERE fire_id = :fire_id ORDER BY id DESC';

            $perimerID = Yii::app()->db->createCommand($perimeterSQL)->queryScalar(array(
                ':fire_id' => $result['fire_id']
            ));

            // Notices

            $notices = array();

            if ($clientIDs)
            {
                $noticesSQL = '
                    SELECT
                        MAX(n.notice_id) [notice_id],
                        MAX(z.notice_id) [triage_notice_id],
                        n.client_id
                    FROM res_notice n
                    LEFT OUTER JOIN res_triage_zone z ON n.notice_id = z.notice_id
                    WHERE n.fire_id = :fire_id
                        AND n.client_id IN (' . implode(',', $clientIDs) . ')
                    GROUP BY n.client_id
                    ORDER BY client_id ASC
                ';

                $notices = Yii::app()->db->createCommand($noticesSQL)->queryAll(true, array(
                    ':fire_id' => $result['fire_id']
                ));
            }

            // Adding notice ID and triage notice ID into clients array

            foreach ($notices as $notice)
            {
                foreach ($clients as $index => $client)
                {
                    if ($client['id'] == $notice['client_id'])
                    {
                        $clients[$index]['triageNoticeID'] = $notice['triage_notice_id'];
                        $clients[$index]['noticeID'] = $notice['notice_id'];
                    }
                }
            }

            $returnData = array(
                'id' => $result['id'],
                'employee_start' => $result['employee_start'],
                'employee_end' =>$result['employee_end'],
                'fire_id' => $result['fire_id'],
                'scheduling_id' => $result['scheduling_id'],
                'fire_name' => $result['fire_name'],
                'city' => $result['city'],
                'state' => $result['state'],
                'fire_officer'=> $result['fire_officer'],
                'fire_officer_office_contact'=> $result['fire_officer_office_contact'],
                'fire_officer_mobile_contact'=> $result['fire_officer_mobile_contact'],
                'fire_officer_email'=> $result['fire_officer_email'],
                'perimeter_id' => $perimerID,
                'clients' => $clients
            );
        }
        // Schedule was NOT found
        else
        {
            $returnData = null;
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $returnData;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: engSchedulingEmployeeController/apiGetAllAssignments
     * Description: Get all resource orders (aka assignments) for the given crew member for the given season.
     *
     * Post data parameters:
     * @param integer crew_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "crew_id" : 59
     *     }
     * }
     */
    public function actionApiGetAllAssignments()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('crew_id')))
            return;

        $sql = '
            SELECT
                e.scheduled_type,
                e.start_date,
                e.end_date,
                s.assignment,
                s.city,
                s.state,
                f.name fire_name,
                en.engine_name,
                en.type engine_type,
                s.resource_order_id,
                e.id,
                s.id scheduling_id
            FROM eng_scheduling_employee e
            INNER JOIN eng_scheduling s ON e.engine_scheduling_id = s.id
            LEFT OUTER JOIN res_fire_name f ON f.fire_id = s.fire_id
            INNER JOIN eng_engines en ON en.id = s.engine_id
            WHERE e.crew_id = :crew_id
                AND s.resource_order_id is not null
                AND e.start_date >= DATEPART(YEAR, GETDATE())
            ORDER BY s.id DESC;
        ';

        $result = Yii::app()->db->createCommand($sql)
            ->bindParam(':crew_id', $data['crew_id'], PDO::PARAM_INT)
            ->queryAll();

        $returnArray['error'] = 0;
        $returnArray['data']= $result;

        return WDSAPI::echoResultsAsJson($returnArray);
    }


    /**
     * API Method: engSchedulingEmployeeController/apiGetResourceOrder
     * Description: Get the resource order in PDF format for the given schedule
     *
     * Post data parameters:
     * @param integer scheduling_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "scheduling_id" : 59
     *     }
     * }
     */
    public function actionApiGetResourceOrder()
    {
        $data = NULL;
        $returnData = array();

        if (!WDSAPI::getInputDataArray($data, array('scheduling_id')))
            return;

        $model = EngScheduling::model()->findByPk($data['scheduling_id']);

        //Get the file
        $filepath = EngResourceOrder::model()->downloadResourceOrderPDF($model, false);
        $fp = fopen($filepath, 'rb');
        $content = fread($fp, filesize($filepath));
        $content = unpack('H*hex', $content)['hex'];
        fclose($fp);

        $returnArray = array(
            'error' => 0,
            'data' => array()
        );

        if ($content)
        {
            $returnArray['data']['name'] = 'Resource Order.pdf';
            $returnArray['data']['type'] = 'application/pdf';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}
