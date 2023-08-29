<?php
class FSContactUsController extends Controller
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
			'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_FIRESHIELD => array(
                        'apiCreate'
                    )
                )
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
                    'update',
                    'create',
                    'delete',
                    'sendConfirmationEmail'
                ),
				'users'=>array('@'),
                //'expression' => 'in_array("Admin",$user->types) || in_array("Manager",$user->types)',
			),
			array('allow',
				'actions'=>array(
                    'apiCreate'
                ),
				'users'=>array('*')),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	//NOTE: I put in a URL Rewrite rule in IIS so that api/fireshield/v2/createContactUs/ rewrites to index.php?r=fsContactUs/apiCreate
	public function actionApiCreate()
	{
        if (!WDSAPI::getInputDataArray($data, array('emailAddress','provider','from')))
            return;

		$return_array = array();

		//check to see if email is already in FSContactUs table
		$fsContactUs = FSContactUs::model()->find("email = '".$data['emailAddress']."'");

		if(!isset($fsContactUs))
			$fsContactUs = new FSContactUs();

		$attributes = array(
            'email' => $data['emailAddress'],
            'provider' => $data['provider'],
            'from' => $data['from'], // i.e. 'FS No Carrier Key Screen'
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'New'
		);

		$fsContactUs->attributes = $attributes;

		if ($fsContactUs->save())
		{
            $fsContactUs->sendConfirmationEmail();
			$return_array['error'] = 0; //error 0 = success, 1 = error occured
		}
		else
		{
			$return_array['error'] = 1; //error 0 = success, 1 = error occured
			$return_array['errorMessage'] = "ERROR: creating a new Fireshield Contact Us entry with given attributes.";
			$return_array['errorFriendlyMessage'] = "There was an error communicating with the Service Provider, please try again or contact support if problem persists.";
		}

        return WDSAPI::echoResultsAsJson($return_array);
	}

    /**
	 * Administration for FSContactUs.
	 */
	public function actionAdmin()
	{
        $model = new FSContactUs('search');
        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['FSContactUs']))
        {
            $model->attributes = $_GET['FSContactUs'];
        }

        $this->render('admin',array(
            'model' => $model,
        ));
	}

    /**
	 * Creates a new FSContactUs model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
        $model = new FSContactUs;
        $memberID = 0;

        if(isset($_POST['FSContactUs']))
        {
            $model->attributes = $_POST['FSContactUs'];

            // Before saving, check to see if a Member exists with the given email address.
            $memberID = Member::checkEmailExists('email_1', $model->email);

            // Also check the email_2 field for a match.
            if ($memberID <= 0)
            {
                $memberID = Member::checkEmailExists('email_2', $model->email);
            }

            if ($memberID <= 0)
            {
                // No matching email addresses were found, so proceed with the save.
                if($model->save())
                {
                    Yii::app()->user->setFlash('success', "Contact Us #".$model->id." Created Successfully!");
                    $this->redirect(array('admin',));
                }
            }
        }

        $this->render('create',array(
            'model' => $model,
            'existingMemberID' => $memberID,
        ));
	}

    /**
	 * Deletes the given FSContactUs model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
        if (Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

 	/**
	 * Updates the given FSContactUs model.
	 */
	public function actionUpdate($id)
	{
        $model = $this->loadModel($id);
        $memberID = 0;

        if (isset($_POST['FSContactUs']))
        {
            $model->attributes = $_POST['FSContactUs'];

            // Before saving, check to see if a Member exists with the given email address.
            $memberID = Member::checkEmailExists('email_1', $model->email);

            // Also check the email_2 field for a match.
            if ($memberID <= 0)
            {
                $memberID = Member::checkEmailExists('email_2', $model->email);
            }

            if ($memberID <= 0)
            {
                if ($model->save())
                {
                    Yii::app()->user->setFlash('success', "Contact Us $id Updated Successfully!");
                    $this->redirect(array('admin',));
                }
            }
        }

        $this->render('update',array(
            'model' => $model,
            'existingMemberID' => $memberID,
        ));
	}

    /**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
        $model = FSContactUs::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
	}

    public function actionSendConfirmationEmail($id)
    {
        $model = $this->loadModel($id);
        if($model->sendConfirmationEmail())
            echo "successfully sent confirmation email";
        else
            echo 'failed sending confirmation email';
    }
}
