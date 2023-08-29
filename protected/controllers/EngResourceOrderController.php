<?php

class EngResourceOrderController extends Controller
{
    const ADV_SEARCH = 'wds_eng_resource_advSearch';
    
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
                    'resourceOrderModel',
                    'resourceOrderCreateModel'
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EngScheduling the loaded model
     * @throws CHttpException
     */
	public function loadModel($id)
	{
		$model=EngResourceOrder::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
    
    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------
    #region CRUD controllers

	/**
     * Manages all models.
     */
	public function actionAdmin($resetAdvSearch = null)
	{
        if (!is_null($resetAdvSearch))
        {
            $_SESSION[self::ADV_SEARCH] = null;
            $this->redirect(array('/engResourceOrder/admin'));
        }
        
		$model=new EngResourceOrder('search');
		$model->unsetAttributes();
		if(isset($_GET['EngResourceOrder']))
			$model->attributes=$_GET['EngResourceOrder'];
        
        // ADVANCED SEARCH
        
        $advSearch = array(
            'eng-clients' => array(),
            'eng-assignments' => array()
        );
        
        if (isset($_GET['ajax']))
        {
            if (!isset($_GET['advSearch']))
            {
                $_SESSION[self::ADV_SEARCH] = null;
            }
            else if (isset($_GET['advSearch']['eng-clients']) && isset($_GET['advSearch']['eng-assignments']))
            {
                $advSearch = $_GET['advSearch'];
                $_SESSION[self::ADV_SEARCH] = $advSearch;
            }
            else if (isset($_GET['advSearch']['eng-clients']) && !isset($_GET['advSearch']['eng-assignments']))
            {
                $advSearch = array(
                    'eng-clients' => $_GET['advSearch']['eng-clients'],
                    'eng-assignments' => array()
                );
                $_SESSION[self::ADV_SEARCH] = $advSearch;
            }
            else if (!isset($_GET['advSearch']['eng-clients']) && isset($_GET['advSearch']['eng-assignments']))
            {
                $advSearch = array(
                    'eng-clients' => array(),
                    'eng-assignments' => $_GET['advSearch']['eng-assignments']
                );
                $_SESSION[self::ADV_SEARCH] = $advSearch;
            }
            else
            {
                $_SESSION[self::ADV_SEARCH] = null;
            }
        }
        else if (isset($_SESSION[self::ADV_SEARCH]))
        {
            $advSearch = $_SESSION[self::ADV_SEARCH];
        }
        
		$this->render('/engResourceOrder/admin',array(
            'advSearch' => $advSearch,
			'model'=>$model
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'admin' page.
	 */
	public function actionCreate()
	{
        $model=new EngResourceOrder;
        
		if(isset($_POST['EngResourceOrder']))
		{
			$model->attributes=$_POST['EngResourceOrder'];
            $model->user_id = Yii::app()->user->id;
            $model->date_created = date('Y-m-d H:i');
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'RO ' . $model->id . ' Created Successfully!');
				$this->redirect(array('admin'));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}
    
    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be updated
     */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
        
        if(isset($_POST['EngResourceOrder']))
		{
			$model->attributes=$_POST['EngResourceOrder'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'RO ' . $model->id . ' Update Successfully!');
				$this->redirect(array('admin'));
			}
		}
        
        Yii::app()->format->dateFormat = 'm/d/Y';
        Yii::app()->format->timeFormat = 'H:i';
        
        $model->form_ordered_date = Yii::app()->format->date($model->date_ordered);
        $model->form_ordered_time = Yii::app()->format->time($model->date_ordered);
        
        $this->render('update',array(
			'model'=>$model
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
        
        // Remove resource order from scheduled engines
        
        if (!empty($model->engScheduling))
        {
            foreach($model->engScheduling as $schedule)
                $schedule->saveAttributes(array('resource_order_id'=>null));
        }
        
        $model->delete();

		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}
    
    /**
     * Render Content and new Model Instance for Resource Order Model
     */
    public function actionResourceOrderModel($clientid)
    {
		$criteria=new CDbCriteria;
        $criteria->join = 'inner join eng_scheduling as s on t.id = s.resource_order_id
                           inner join eng_scheduling_client as c on c.engine_scheduling_id = s.id';
        $criteria->addCondition('c.client_id = ' . $clientid);
        
		$dataProvider = new CActiveDataProvider('EngResourceOrder', array(
			'sort' => array(
				'defaultOrder'=>array('id'=>CSort::SORT_DESC),
				'attributes' => array(
                    '*',
				)
			),
			'criteria'=>$criteria,
            'pagination' => array('PageSize'=>10)
		));
        
        return $this->renderPartial('/engScheduling/_form_ro_modal',array(
            'dataProvider'=>$dataProvider
        ));
    }

    /**
     * Render create form content intended for modal presentaiton
     * @return mixed
     */
    public function actionResourceOrderCreateModel()
    {
        $model = new EngResourceOrder;
        
        if (isset($_POST['EngResourceOrder']))
        {
            $model->attributes = $_POST['EngResourceOrder'];
            $model->user_id = Yii::app()->user->id;
            $model->date_created = date('Y-m-d H:i');
            if ($model->save())
            {
                echo json_encode(array('error' => 0, 'message' => 'RO ' . $model->id . ' Created Successfully!'));
                return;
            }
        }

        $this->renderPartial('create',array(
            'model' => $model,
        ));
    }
    
    #endregion
}
