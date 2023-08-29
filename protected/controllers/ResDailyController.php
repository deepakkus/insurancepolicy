<?php

class ResDailyController extends Controller
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
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetDaily',
                        'apiGetMostRecentDaily'
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
                'actions' => array(),
                'users' => array('@')
            ),
            array('allow',
                'actions' => array(
                    'apiGetDaily',
                    'apiGetMostRecentDaily'
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

    //-------------------------------------------------------------API Methods------------------------------------------------------------

    /**
     * API Method: resDaily/apiGetDaily
     * Description: Gets a daily entry.
     *
     * Post data parameters:
     * @param int id - ID of the daily
     *
     * Post data example: {"data": {"id": 123}}
     */
    public function actionApiGetDaily()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        $daily = ResDaily::model()->findByPk($data['id']);

        if (!isset($daily))
        {
            return WDSAPI::echoJsonError("ERROR: a daily entry was not found for ID = {$data['id']}.");
        }

        $returnArray['error'] = 0; // success
        $returnArray['data'] = $daily->attributes;

        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resDaily/apiGetMostRecentDaily
     * Description: Gets the most recent published daily entry for a client.
     *
     * Post data parameters:
     * @param int client_id - ID of the client, 0 if no client
     * @param int published - (optional) flag indicating to return only published (1) or unpublished (0) dailies.
     *
     * Post data example:
     * {
     *     "data": {
     *         "client_id": "1",
     *         "published": "1"
     *     }
     * }
     */
    public function actionApiGetMostRecentDaily()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('client_id')))
            return;

        $client_id = $data['client_id'];

        $client = Client::model()->findByPk($client_id);
        $wds_fire = $client->wds_fire;

        $criteria = new CDbCriteria();
        $criteria->addCondition('client_id = :client_id');
        $criteria->params[':client_id'] = $client_id;

        if (isset($data['published']))
        {
            $criteria->addCondition('published = :published');
            $criteria->params[':published'] = $data['published'];
        }

        $criteria->order = 'date_created DESC';

        $daily = ResDaily::model()->find($criteria);

        if (!isset($daily) && $wds_fire)
        {
            return WDSAPI::echoJsonError("ERROR: a daily entry was not found for the given parameters.");
        }

        $returnArray['error'] = 0;
        $returnArray['data'] = $daily->attributes;
        $returnArray['data']['mapbox_layer_id'] = ($client && $client->mapbox_layer_id) ? $client->mapbox_layer_id : null;

        WDSAPI::echoResultsAsJson($returnArray);
    }
}