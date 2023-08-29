<?php

class EngShiftTicketActivityTypeController extends Controller
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
                        'apiGetTypes',
                    )
                )
            )
        );
    }

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
                    'admin',
                    'create',
                    'update'
                ),
				'users' => array('@'),
                'expression' => 'in_array("Admin", $user->types)',
			),
            array('allow',
                'actions' => array(
                    'apiGetTypes',
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
                'class' => 'WDSActionLogger',
                'blackList' => array()
            )
        );
    }

    /**
     * Shows grid view of all existing types and a create link
     */
    public function actionAdmin()
    {
		$engShiftTicketActivityType = new EngShiftTicketActivityType('search');
		$engShiftTicketActivityType->unsetAttributes();

		if (isset($_GET['EngShiftTicketActivityType']))
			$engShiftTicketActivityType->attributes = $_GET['EngShiftTicketActivityType'];

		$this->render('admin', array(
            'engShiftTicketActivityType' => $engShiftTicketActivityType,
		));
    }

	/**
     * Creates a new ST Activity Type.
     */
    public function actionCreate()
    {
		$engShiftTicketActivityType = new EngShiftTicketActivityType;

		if (isset($_POST['EngShiftTicketActivityType']))
		{
			$engShiftTicketActivityType->attributes = $_POST['EngShiftTicketActivityType'];

			if ($engShiftTicketActivityType->save())
				$this->redirect(array('admin'));
		}
        
		$this->render('create',array(
			'engShiftTicketActivityType' => $engShiftTicketActivityType,
		));
    }

	/**
     * Updates a particular ST Activity Type.
     * @param integer $id ID of type
     */
    public function actionUpdate($id)
    {
		$engShiftTicketActivityType = EngShiftTicketActivityType::model()->findByPk($id);

		if (isset($_POST['EngShiftTicketActivityType']))
		{
			$engShiftTicketActivityType->attributes = $_POST['EngShiftTicketActivityType'];

			if ($engShiftTicketActivityType->save())
				$this->redirect(array('admin'));
		}

		$this->render('update',array(
			'engShiftTicketActivityType' => $engShiftTicketActivityType,
		));
    }

    /**
     * API Method: engShiftTicketActivityType/apiGetTypes
     * Description: Returns active shift ticket activity types
     * including their descriptions.
     */
    public function actionApiGetTypes()
    {
        if (!WDSAPI::getInputDataArray($data, array()))
            return;

        $returnData = array();
        $returnArray = array();

        $types = EngShiftTicketActivityType::model()->findAll('active = 1');

        if (isset($types))
        {
            foreach ($types as $type)
            {
                $returnData[] = array(
                    'id' => $type->id,
                    'type' => $type->type,
                    'description' => $type->description,
                );
            }

            $returnArray['error'] = 0;
            $returnArray['data'] = $returnData;
        }
        else
        {
            $returnArray['error'] = 1;
            $returnArray['errorMessage'] = "No active shift ticket activity types were found.";
            $returnArray['errorFriendlyMessage'] = "We're sorry, something went wrong. Please contact WDS for assistance.";
            $returnArray['data'] = null;
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }
}
