<?php

class WdsStatesController extends Controller
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
                'actions' => array(
                    'update'
                ),
                'users' => array('@')
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
	 * Updates a particular model.
	 */
	public function actionUpdate()
	{
        $wdsstates = Yii::app()->db->createCommand()
            ->select('state_id')
            ->from(WDSStates::model()->tableName())
            ->queryColumn();

		if (isset($_POST['WDSStates'], $_POST['WDSStates']['state_id']))
		{
            $newstateids = $_POST['WDSStates']['state_id'];

            // Add new state if appropriate
            foreach ($newstateids as $stateid)
            {
                if (!in_array($stateid, $wdsstates))
                {
                    $wdsstate = new WDSStates;
                    $wdsstate->state_id = $stateid;
                    $wdsstate->save();
                }
            }

            // Delete old state if appropriate
            foreach ($wdsstates as $wdsstate)
            {
                if (!in_array($wdsstate, $newstateids))
                    WDSStates::model()->deleteAll('state_id = :state_id', array(':state_id' => $wdsstate));
            }

            Yii::app()->user->setFlash('success', 'WDS states updated');
            return $this->redirect(array('update'));
		}

        $stateformdata = CHtml::listData(GeogStates::model()->findAll(array('select' => 'id, abbr')), 'id', 'abbr');

		$this->render('update', array(
            'stateformdata' => $stateformdata,
            'wdsstates' => $wdsstates
		));
	}
}
