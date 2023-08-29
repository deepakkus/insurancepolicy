<?php

class EngShiftTicketStatusTypeController extends Controller
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
                    'admin',
                    'create',
                    'update',
                ),
                'users' => array('@')
            ),
             array('allow',
                'actions' => array(
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

    
    /**
     * Landing page
     */
    public function actionAdmin()
    {
        $shiftTicketStatusType = new EngShiftTicketStatusType();        
        if (isset($_GET['EngShiftTicketStatusType']))
        {
            $shiftTicketStatusType->attributes=$_GET['EngShiftTicketStatusType'];
        }
        $this->render('admin', array(
            'shiftTicketStatusType' => $shiftTicketStatusType,
        )); 
    }


    public function actionCreate()
    {
        $shiftTicketStatusType = new EngShiftTicketStatusType();        
        if (isset($_POST['EngShiftTicketStatusType']))
        {
            $shiftTicketStatusType->attributes = $_POST['EngShiftTicketStatusType'];
            if ($shiftTicketStatusType->save())
            {
                Yii::app()->user->setFlash('success', "Status Type Added Successfully");
                 return $this->redirect(array('admin'));
            }
        }
        $this->render('create', array(
            'shiftTicketStatusType' => $shiftTicketStatusType,
        ));
    }

    public function actionUpdate($id)
    {
        $shiftTicketStatusType = EngShiftTicketStatusType::model()->findByPk($id);
        if (isset($_POST['EngShiftTicketStatusType']))
        {
            $shiftTicketStatusType->attributes = $_POST['EngShiftTicketStatusType'];
            if ($shiftTicketStatusType->save())
            {
                Yii::app()->user->setFlash('success', "Status Type Updated Successfully");
                 return $this->redirect(array('admin'));
            }
        }
        $this->render('update', array(
            'shiftTicketStatusType' => $shiftTicketStatusType
        ));
    }

    public function actionDelete($id)
    {                
        if(Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            EngShiftTicketStatusType::model()->findByPk($id)->delete();
            Yii::app()->user->setFlash('error', "Status Type Deleted Successfully");
        }
    }


}

