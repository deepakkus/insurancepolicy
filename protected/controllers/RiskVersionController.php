<?php

class RiskVersionController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl'
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
                    'versions',
                    'create',
                    'update',
                    'makeLive'
                ),
                'users' => array('@'),
            ),

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
     * View all risk versions
     */
    public function actionVersions()
    {
        $version = new RiskVersion('search');

        $this->render('versions', array(
            'version' => $version
        ));
    }

    /**
     * Create new risk model version
     */
    public function actionCreate()
    {
        $model = new RiskVersion;

        if (isset($_POST['RiskVersion']))
        {
            $model->attributes=$_POST['RiskVersion'];
            $model->is_live = 0;

            if ($model->save())
            {
                $this->redirect(array('versions'));
            }
        }

        $this->render('create', array(
            'model' => $model
        ));
    }

    /**
     * Update risk model version
     * @param integer $id
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);

        if (isset($_POST['RiskVersion']))
        {
            $model->attributes=$_POST['RiskVersion'];

            if ($model->save())
            {
                $this->redirect(array('versions'));
            }
        }

        $this->render('update', array(
            'model' => $model
        ));
    }

    /**
     * Make request risk version the live version
     * @param integer $id
     */
    public function actionMakeLive($id)
    {
        $result1 = null;
        $result2 = null;

        try
        {
            $tableName = RiskVersion::model()->tableName();
            $result1 = Yii::app()->db->createCommand()->update($tableName, array('is_live' => '0'));
            $result2 = Yii::app()->db->createCommand()->update($tableName, array('is_live' => '1'), 'id = :id', array(
                ':id' => $id
            ));
        }
        catch (CDbException $exception)
        {
            Yii::app()->user->setFlash('error', $exception->getMessage());
        }

        if ($result1 && $result2)
        {
            Yii::app()->user->setFlash('success', 'Risk version updated successfully!');
        }

        return $this->redirect(array('riskVersion/versions'));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return RiskVersion the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=RiskVersion::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }
}
