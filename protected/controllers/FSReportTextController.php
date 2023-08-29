<?php

class FSReportTextController extends Controller
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
			array('allow', // allow
				'actions'=>array(
                    'admin',
                    'update',
                    'create'
                ),
				'users'=>array('@'),
                //'expression' => 'in_array("Admin",$user->types) || in_array("Manager",$user->types)',
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	/**
	 * Updates a particular model.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$fsReportText=$this->loadModel($id);

		if(isset($_POST['FSReportText']))
		{
			$fsReportText->attributes=$_POST['FSReportText'];
			if($fsReportText->save())
			{
				Yii::app()->user->setFlash('success', "FS Report Text Updated Successfully!");
				$this->redirect(array('admin',));
			}
			else
			{
				Yii::app()->user->setFlash('error', "ERROR UPDATING FS Report Text!");
				$this->redirect(array('admin',));
			}
		}

		$this->render('update',array(
			'fsReportText'=>$fsReportText,
		));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$fsReportText = new FSReportText;

		if(isset($_POST['FSReportText']))
		{
			$fsReportText->attributes=$_POST['FSReportText'];
			if($fsReportText->save())
			{
				Yii::app()->user->setFlash('success', "FS Report Text Created Successfully!");
				$this->redirect(array('admin',));
			}
			else
			{
				Yii::app()->user->setFlash('error', "ERROR CREATING NEW FS Report Text!");
				$this->redirect(array('admin',));
			}
		}

		$this->render('create',array(
			'fsReportText'=>$fsReportText,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$fsReportTexts = new FSReportText('search');
		$fsReportTexts->unsetAttributes();  // clear any default values
		if(isset($_GET['FSReportText']))
		{
			$fsReportTexts->attributes=$_GET['FSReportText'];
		}
		$this->render('admin',array('fsReportTexts'=>$fsReportTexts,));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=FSReportText::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
