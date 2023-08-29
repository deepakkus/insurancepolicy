<?php

class EngShiftTicketNotesController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout='//layouts/column2';

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
                'actions' => array(
                    'notes',
                    'create',
                    'update',
                    'delete'
                ),
                'users' => array('@')
            ),
            array('deny',
                'users' => array('*')
            )
        );
    }

    /**
     * Get notes related to a shift ticket
     * @param integer $shift_ticket_id
     */
    public function actionNotes($shift_ticket_id)
    {
        $shiftTicketNotes = EngShiftTicketNotes::model()->findAll(array(
            'condition' => 'eng_shift_ticket_id = :id',
            'params' => array(':id' => $shift_ticket_id),
            'with' => array(
                'user' => array(
                    'select' => 'id,name'
                )
            ),
            'order'=>'t.id DESC'
        ));

        $shiftTicket = EngShiftTicket::model()->findByPk($shift_ticket_id);

        $this->renderPartial('_review_notes', array(
            'shiftTicketNotes' => $shiftTicketNotes,
            'shiftTicket' => $shiftTicket
        ));
    }

    /**
     * Creates a new model.
     * @param integer  $shift_ticket_id
     * @return string
     */
    public function actionCreate($shift_ticket_id)
    {
        $note = new EngShiftTicketNotes;
        $note->eng_shift_ticket_id = $shift_ticket_id;
        $note->user_id = Yii::app()->user->id;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'shift-ticket-modal-form')
        {
            echo CActiveForm::validate($note);
            Yii::app()->end();
        }

        if (isset($_POST['EngShiftTicketNotes']))
        {
            $note->attributes = $_POST['EngShiftTicketNotes'];
            $return = null;

            if ($note->save())
            {
                $return = array('success' => true, 'error' => '');
            }
            else
            {
                $return = array('success' => false, 'error' => var_export($note->getErrors(), true));
            }

            echo json_encode($return);
            Yii::app()->end();
        }

        return $this->renderPartial('_review_note', array(
            'note' => $note
        ));
    }

    /**
     * Updates a particular model.
     * @param integer $id
     * @return string|null
     */
    public function actionUpdate($id)
    {
        $note = $this->loadModel($id);

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'shift-ticket-modal-form')
        {
            echo CActiveForm::validate($note);
            Yii::app()->end();
        }

        if (isset($_POST['EngShiftTicketNotes']))
        {
            $note->attributes = $_POST['EngShiftTicketNotes'];
            $return = null;

            if ($note->save())
            {
                $return = array('success' => true, 'error' => '');
            }
            else
            {
                $return = array('success' => false, 'error' => var_export($note->getErrors(), true));
            }

            echo json_encode($return);
            Yii::app()->end();
        }

        return $this->renderPartial('_review_note', array(
            'note' => $note
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        $success = $model->delete();
        echo json_encode(array('success' => $success, 'error' => ($success ? '' : var_export($model->getErrors(), true))));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EngShiftTicketNotes the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=EngShiftTicketNotes::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }
}
