<?php

class ClientDedicatedHoursController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            'postOnly + delete'
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
                    'create',
                    'update',
                    'delete',
                    'index'
                ),
                'users'=>array('@'),
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
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new ClientDedicatedHours('search');
        $model->unsetAttributes();
        if(isset($_GET['ClientDedicatedHours']))
            $model->attributes=$_GET['ClientDedicatedHours'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model = new ClientDedicatedHours;

        if (isset($_POST['ClientDedicatedHours']))
        {
            $model->attributes = $_POST['ClientDedicatedHours'];

            if ($model->save())
            {
                foreach ($model->clientIDs as $clientID)
                {
                    $clientDedicated = new ClientDedicated;
                    $clientDedicated->client_id = $clientID;
                    $clientDedicated->client_dedicated_hours_id = $model->id;
                    $clientDedicated->save();
                }

                Yii::app()->user->setFlash('success', 'Dedicated hour pool successfully added');

                return $this->redirect(array('admin'));
            }
        }

        return $this->render('create',array(
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
        $model = $this->loadModel($id);

        if (isset($_POST['ClientDedicatedHours']))
        {
            $model->attributes = $_POST['ClientDedicatedHours'];

            if ($model->save())
            {
                ClientDedicated::model()->deleteAll('client_dedicated_hours_id = :client_dedicated_hours_id', array(':client_dedicated_hours_id' => $id));

                foreach ($model->clientIDs as $clientID)
                {
                    $clientDedicated = new ClientDedicated;
                    $clientDedicated->client_id = $clientID;
                    $clientDedicated->client_dedicated_hours_id = $model->id;
                    $clientDedicated->save();
                }

                Yii::app()->user->setFlash('success', 'Dedicated hour pool successfully updated');

                return $this->redirect(array('admin'));
            }
        }

        $clientDedicatedModel = ClientDedicated::model()->findAll(':client_dedicated_hours_id = client_dedicated_hours_id', array(':client_dedicated_hours_id' => $id));

        $model->clientIDs = array_map(function($model) { return $model->client_id; }, $clientDedicatedModel);

        return $this->render('update',array(
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
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return ClientDedicatedHours the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=ClientDedicatedHours::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    public function actionIndex()
    {
        $dedicatedForm = new DedicatedHoursAnalyticsForm;

        $dedicatedServiceClients = ClientDedicatedHours::GetDedicatedServiceClients();

        // From beginning of year until now
        $dedicatedForm->startDate = date('Y-01-01');
        $dedicatedForm->endDate = date('Y-m-d');
        $dedicatedForm->clientID = current(array_keys($dedicatedServiceClients));

        if (isset($_POST['DedicatedHoursAnalyticsForm']))
        {
            $dedicatedForm->attributes = $_POST['DedicatedHoursAnalyticsForm'];
        }

        $resultsTotals = $dedicatedForm->getHoursByAssignment();
        $resultsTotalsWithClients = $dedicatedForm->getHoursByAssignment(true);

        $dedicatedPools = $dedicatedForm->getDedicatedHourPoolsByClientAndDate();
        $dedicatedHoursForClient = $dedicatedForm->getHoursByAssignmentForClient(EngScheduling::ENGINE_ASSIGNMENT_DEDICATED);
        
        return $this->render('index', array(
            'dedicatedServiceClients' => $dedicatedServiceClients,
            'dedicatedForm' => $dedicatedForm,
            'resultsTotals' => $resultsTotals,
            'resultsTotalsWithClients' => $resultsTotalsWithClients,
            'dedicatedPools' => $dedicatedPools,
            'dedicatedHoursForClient' => $dedicatedHoursForClient
        ));
    }
}
