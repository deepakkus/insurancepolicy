<?php

class ResCallListController extends Controller
{
    const ATTRIBUTES = 'wds_response_call_list_searchAttr';
    const ATTRIBUTES_CLIENT = 'wds_response_call_list_searchAttr_client';
    const ATTRIBUTES_FIRE = 'wds_response_call_list_searchAttr_fire';
    const COLUMNS_TO_SHOW = 'wds_response_call_list_columnsToShow';
    const PAGE_SIZE = 'wds_response_call_list_pageSize';
    const SORT = 'wds_response_call_list_sort';
    const NOTICE_LINK = 'wds_response_call_list_notice_link';
    const RATTRIBUTES = 'wds_response_call_list_searchAttrs';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            // Enable YiiBooster for only certain views.
            array('ext.bootstrap.filters.BootstrapFilter + admin, update'),
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetCallsByFire',
                        'apiGetClientCallAttempts',
                        'apiGetCallAttempt',
                        'apiCreateClientCallAttempt',
                        'apiUpdateClientCallAttempt',
                        'apiGetPolicyCallsByDate',
                        'apiGetClientCallLog',
                        'apiGetClientCallsNoticeQueryCount',
                        'apiGetClientCallsNotice'
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
                'actions'=>array(
                    'admin',
                    'admin2',
                    'update',
                    'create',
                    'assignCallerToCalls',
                    'publishCalls',
                    'searchCalls'
                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetCallsByFire',
                    'apiGetClientCallAttempts',
                    'apiGetCallAttempt',
                    'apiCreateClientCallAttempt',
                    'apiUpdateClientCallAttempt',
                    'apiGetPolicyCallsByDate',
                    'apiGetClientCallLog',
                    'apiGetClientCallsNoticeQueryCount',
                    'apiGetClientCallsNotice'
                ),
                'users'=>array('*')),
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

    //-----------------------------------------------------------CRUD controllers ------------------------------------------------------

    /**
     * The main response call list grid.
     */
    public function actionAdmin($resetFilters = null, $download = false)
    {
        //Temporary duct tape to speed up this search - need to impliment this through the config once the character set is figured out
        Yii::app()->db->setAttribute(PDO::SQLSRV_ATTR_ENCODING,PDO::SQLSRV_ENCODING_SYSTEM);

        $model = new ResCallList('search');
        $model->unsetAttributes();

        // NOTICE CALL LIST LINK
        if (filter_has_var(INPUT_GET, self::NOTICE_LINK))
        {
            if (Yii::app()->user->getState('clientid') && Yii::app()->user->getState('fireid'))
            {
                $notice_call_list = ResCallList::model()->findByAttributes(array(
                    'client_id' => Yii::app()->user->getState('clientid'),
                    'res_fire_id' => Yii::app()->user->getState('fireid')
                ));

                Yii::app()->user->setState('clientid', null);
                Yii::app()->user->setState('fireid', null);

                if ($notice_call_list)
                {
                    $model->client_name = $notice_call_list->client->name;
                    $model->fire_name = $notice_call_list->fire->Name;

                    $_SESSION[self::ATTRIBUTES] = $model->attributes;
                    $_SESSION[self::ATTRIBUTES_CLIENT] = $notice_call_list->client->name;
                    $_SESSION[self::ATTRIBUTES_FIRE] = $notice_call_list->fire->Name;
                }
            }
        }

        // FILTER ATTRIBUTES
        if (isset($resetFilters))
        {
            $_SESSION[self::ATTRIBUTES] = null;
            $_SESSION[self::SORT] = null;
            $this->redirect(array('admin'));
        }
        else if (filter_has_var(INPUT_GET, ResCallList::modelName()))
        {
            $input = filter_input(INPUT_GET, ResCallList::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $model->attributes = $input;
            $_SESSION[self::ATTRIBUTES] = $input;
        }
        else if (isset($_SESSION[self::ATTRIBUTES]))
        {
            $model->attributes = $_SESSION[self::ATTRIBUTES];

            if (isset($_SESSION[self::ATTRIBUTES_CLIENT]) && isset($_SESSION[self::ATTRIBUTES_FIRE]))
            {
                $model->client_name = $_SESSION[self::ATTRIBUTES_CLIENT];
                $model->fire_name = $_SESSION[self::ATTRIBUTES_FIRE];
            }
        }

        // COLUMNS TO SHOW
        $columnsToShow = array(
            'do_not_call',
            'assigned_caller_user_name',
            'client_name',
            'fire_name',
            'res_triggered_priority',
            'res_triggered_threat',
            'res_triggered_distance',
            'res_triggered_response_status',
            'member_first_name',
            'member_last_name',
            'property_address_line_1',
            'property_city',
            'property_state'
        );

        if (filter_has_var(INPUT_POST, self::COLUMNS_TO_SHOW))
        {
            $columnsToShow = filter_input(INPUT_POST, self::COLUMNS_TO_SHOW, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            $_SESSION[self::COLUMNS_TO_SHOW] = $columnsToShow;
        }
        else if (isset($_SESSION[self::COLUMNS_TO_SHOW]))
        {
            $columnsToShow = $_SESSION[self::COLUMNS_TO_SHOW];
        }

        // PAGE SIZE
        $pageSize = 25;
        $pageSizeMethod = NULL;
        if (filter_has_var(INPUT_POST, self::PAGE_SIZE))
        {
            $pageSize = filter_input(INPUT_POST, self::PAGE_SIZE);
            $_SESSION[self::PAGE_SIZE] = $pageSize;
            $pageSizeMethod = 'post';
        }
        else if (isset($_SESSION[self::PAGE_SIZE]))
        {
            $pageSize = $_SESSION[self::PAGE_SIZE];
        }

        // SORTING
        $sort = 'id.desc';
        if (filter_has_var(INPUT_GET, ResCallList::modelName() . '_sort')) {
            $sort = filter_input(INPUT_GET, ResCallList::modelName() . '_sort');
            $_SESSION[self::SORT] = $sort;
        }
        else if (isset($_SESSION[self::SORT]))
        {
            $sort = $_SESSION[self::SORT];
        }

        $dataProvider = $model->search($pageSize, $sort, $pageSizeMethod);

        // Find the caller type users.
        $userCriteria = new CDbCriteria();
        $userCriteria->addSearchCondition('type', 'caller');
        $userCriteria->addSearchCondition('type', 'response', true, 'OR');
        $userCriteria->order = 'name';
        $callerUsers = User::model()->findAll($userCriteria);

        // Get the client names.
        $clientCriteria = new CDbCriteria();
        $clientCriteria->order = 'name';
        $clients = Client::model()->findAll($clientCriteria);

        // Get the fire names.
        $fireNames = ResNotice::model()->getFireNames($model->client_name);
        $noticeTypes = ResNotice::model()->findAll(array('select'=>'wds_status','distinct'=>true));

        if($download)
        {
            $model->makeDownloadableReport($columnsToShow, $sort);
        }
        else
        {
            $this->render('admin', array(
                'dataProvider' => $dataProvider,
                'model' => $model,
                'columnsToShow' => $columnsToShow,
                'columnsToShowName' => self::COLUMNS_TO_SHOW,
                'pageSize' => $pageSize,
                'pageSizeName' => self::PAGE_SIZE,
                'callerUsers' => $callerUsers,
                'clients' => $clients,
                'fireNames' => $fireNames,
                'noticeTypes' => $noticeTypes
            ));
        }
    }

    public function actionAdmin2($resetFilters = null, $download = false)
    {
        //Temporary duct tape to speed up this search - need to impliment this through the config once the character set is figured out
        Yii::app()->db->setAttribute(PDO::SQLSRV_ATTR_ENCODING,PDO::SQLSRV_ENCODING_SYSTEM);

        $model = new ResCallList('search');
        $model->unsetAttributes();

        // NOTICE CALL LIST LINK
        if (filter_has_var(INPUT_GET, self::NOTICE_LINK))
        {
            if (Yii::app()->user->getState('clientid') && Yii::app()->user->getState('fireid'))
            {
                $notice_call_list = ResCallList::model()->findByAttributes(array(
                    'client_id' => Yii::app()->user->getState('clientid'),
                    'res_fire_id' => Yii::app()->user->getState('fireid')
                ));

                Yii::app()->user->setState('clientid', null);
                Yii::app()->user->setState('fireid', null);

                if ($notice_call_list)
                {
                    $model->client_name = $notice_call_list->client->name;
                    $model->fire_name = $notice_call_list->fire->Name;

                    $_SESSION[self::ATTRIBUTES] = $model->attributes;
                    $_SESSION[self::ATTRIBUTES_CLIENT] = $notice_call_list->client->name;
                    $_SESSION[self::ATTRIBUTES_FIRE] = $notice_call_list->fire->Name;
                }
            }
        }
        $filter_search = array(
            'do_not_call'=>'',
            'assigned_caller_user_name'=>'',
            'client_name'=>'',
            'fire_name'=>'',
            'notice_type'=>'',
            'res_triggered_priority'=>'',
            'res_triggered_threat'=>'',
            'res_triggered_distance'=>'',
            'res_triggered_response_status'=>'',
            'triggered'=>'',
            'evacuated'=>'',
            'published'=>'',
            'dashboard_comments'=>'',
            'general_comments'=>'',
            'prop_res_status'=>'',
            'property_id'=>'',
            'property_address_line_1'=>'',
            'property_address_line_2'=>'',
            'property_city'=>'',
            'property_state'=>'',
            'property_zip'=>'',
            'member_num'=>'',
            'member_first_name'=>'',
            'member_last_name'=>''

        );
        if(isset($_SESSION[self::RATTRIBUTES]))
        {
            $filter_search = $_SESSION[self::RATTRIBUTES];
        }
        // FILTER ATTRIBUTES
        if (isset($resetFilters))
        {
            $_SESSION[self::ATTRIBUTES] = null;
            $_SESSION[self::SORT] = null;
            $this->redirect(array('admin'));
        }
        else if (filter_has_var(INPUT_GET, ResCallList::modelName()))
        {
            $input = filter_input(INPUT_GET, ResCallList::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $model->attributes = $input;
            $_SESSION[self::ATTRIBUTES] = $input;
        }
        else if (isset($_SESSION[self::ATTRIBUTES]))
        {
            $model->attributes = $_SESSION[self::ATTRIBUTES];

            if (isset($_SESSION[self::ATTRIBUTES_CLIENT]) && isset($_SESSION[self::ATTRIBUTES_FIRE]))
            {
                $model->client_name = $_SESSION[self::ATTRIBUTES_CLIENT];
                $model->fire_name = $_SESSION[self::ATTRIBUTES_FIRE];
            }
        }

        // COLUMNS TO SHOW
        $columnsToShow = array(
            'do_not_call',
            'assigned_caller_user_name',
            'client_name',
            'fire_name',
            'res_triggered_priority',
            'res_triggered_threat',
            'res_triggered_distance',
            'res_triggered_response_status',
            'member_first_name',
            'member_last_name',
            'property_address_line_1',
            'property_city',
            'property_state'
        );

        if (filter_has_var(INPUT_POST, self::COLUMNS_TO_SHOW))
        {
            $columnsToShow = filter_input(INPUT_POST, self::COLUMNS_TO_SHOW, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            $_SESSION[self::COLUMNS_TO_SHOW] = $columnsToShow;
        }
        else if (isset($_SESSION[self::COLUMNS_TO_SHOW]))
        {
            $columnsToShow = $_SESSION[self::COLUMNS_TO_SHOW];
        }

        // PAGE SIZE
        $pageSize = 25;
        $pageSizeMethod = NULL;
        if (filter_has_var(INPUT_POST, self::PAGE_SIZE))
        {
            $pageSize = filter_input(INPUT_POST, self::PAGE_SIZE);
            $_SESSION[self::PAGE_SIZE] = $pageSize;
            $pageSizeMethod = 'post';
        }
        else if (isset($_SESSION[self::PAGE_SIZE]))
        {
            $pageSize = $_SESSION[self::PAGE_SIZE];
        }

        // SORTING
        $sort = 'id.desc';
        if (filter_has_var(INPUT_GET, ResCallList::modelName() . '_sort')) {
            $sort = filter_input(INPUT_GET, ResCallList::modelName() . '_sort');
            $_SESSION[self::SORT] = $sort;
        }
        else if (isset($_SESSION[self::SORT]))
        {
            $sort = $_SESSION[self::SORT];
        }

        $dataProvider = $model->search($pageSize, $sort, $pageSizeMethod);
        $offsetSql = " OFFSET 0 ROWS FETCH NEXT ".$pageSize." ROWS ONLY";  
        $appendSql = '';
        $filter_search_criteria = false;
        if($download)
        {
            $offsetSql = '';
            if($filter_search['assigned_caller_user_name']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " u.name LIKE '%".$filter_search['assigned_caller_user_name']."%'";
                $filter_search_criteria = true;
            }
            if($filter_search['client_name']!='')
            {
                $appendSql = "WHERE c.name LIKE '%".$filter_search['client_name']."%'";
                $filter_search_criteria = true;
            }
            if($filter_search['fire_name']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " f.Name LIKE '%".$filter_search['fire_name']."%'";
                $filter_search_criteria = true;
            }
            if($filter_search['notice_type']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " n.wds_status = '".$filter_search['notice_type']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['res_triggered_priority']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " rt.priority = '".$filter_search['res_triggered_priority']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['res_triggered_threat']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " rt.threat = '".$filter_search['res_triggered_threat']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['res_triggered_distance']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " rt.distance = '".$filter_search['res_triggered_distance']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['res_triggered_response_status']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " rt.response_status = '".$filter_search['res_triggered_response_status']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['triggered']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " t.triggered = '".$filter_search['triggered']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['evacuated']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.evacuated = '".$filter_search['evacuated']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['published']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.publish= '".$filter_search['published']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['dashboard_comments']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.dashboard_comments = '".$filter_search['dashboard_comments']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['general_comments']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.general_comments = '".$filter_search['general_comments']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['prop_res_status']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.prop_res_status = '".$filter_search['prop_res_status']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['property_id']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " p.pid = '".$filter_search['property_id']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['property_address_line_1']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " p.address_line_1 = '".$filter_search['property_address_line_1']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['property_address_line_2']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " p.address_line_2 = '".$filter_search['property_address_line_2']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['property_city']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " p.city LIKE '%".$filter_search['property_city']."%'";
                $filter_search_criteria = true;
            }
            if($filter_search['property_state']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " p.state LIKE '%".$filter_search['property_state']."%'";
                $filter_search_criteria = true;
            }
            if($filter_search['property_zip']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " p.zip = '".$filter_search['property_zip']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['member_num']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " m.member_num = '".$filter_search['member_num']."'";
                $filter_search_criteria = true;
            }
            
            if($filter_search['member_first_name']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " m.first_name = '".$filter_search['member_first_name']."'";
                $filter_search_criteria = true;
            }
            if($filter_search['member_last_name']!='')
            {
                $appendSql .= ($filter_search_criteria)?" AND":" WHERE";
                $appendSql .= " m.last_name = '".$filter_search['member_last_name']."'";
                $filter_search_criteria = true;
            }
        }    
        $sql = "SELECT t.id AS calllistid,t.*,u.id,
                u.name AS username,
                c.id,c.name AS clientname,
                f.Fire_ID, sca.prop_res_status, sca.publish, sca.dashboard_comments, sca.general_comments, sca.evacuated,
                f.Name AS firename,n.*,rt.priority,rt.threat,rt.distance,rt.response_status AS rtresponsestatus,p.*,m.* FROM res_call_list t
        LEFT OUTER JOIN [user] u ON t.assigned_caller_user_id = u.id
        LEFT OUTER JOIN client c ON (t.client_id = c.id) 
        LEFT OUTER JOIN res_fire_name f ON (t.res_fire_id = f.Fire_ID)
        LEFT OUTER JOIN res_notice n ON (n.notice_id = (SELECT
        MAX(notice_id) FROM res_notice rsn WHERE rsn.fire_id = t.res_fire_id AND
        rsn.client_id = t.client_id)) 
        LEFT JOIN res_triggered rt ON (n.notice_id = rt.notice_id AND
        t.property_id = rt.property_pid) 
        LEFT OUTER JOIN properties p ON (t.property_id = p.pid)  
        LEFT OUTER JOIN res_call_attempt sca ON (sca.id = (SELECT MAX(id) FROM res_call_attempt a WHERE a.call_list_id = t.id))  
        LEFT OUTER JOIN members m ON
        (p.[member_mid] = m.mid) ".$appendSql." ORDER BY t.id DESC" .$offsetSql;
        //Pagination counter sql
        $pageSql = "(SELECT count(*) AS totalcount FROM res_call_list t
        LEFT OUTER JOIN [user] u ON t.assigned_caller_user_id = u.id
        LEFT OUTER JOIN client c ON (t.client_id = c.id) 
        LEFT OUTER JOIN res_fire_name f ON (t.res_fire_id = f.Fire_ID)
        LEFT OUTER JOIN res_notice n ON (n.notice_id = (SELECT
        MAX(notice_id) FROM res_notice rsn WHERE rsn.fire_id = t.res_fire_id AND
        rsn.client_id = t.client_id)) 
        LEFT JOIN res_triggered rt ON (n.notice_id = rt.notice_id AND
        t.property_id = rt.property_pid) 
        LEFT OUTER JOIN properties p ON (t.property_id = p.pid)
        LEFT OUTER JOIN res_call_attempt sca ON (t.id = (SELECT MAX(id) FROM res_call_attempt a WHERE a.call_list_id = t.id))  
        LEFT OUTER JOIN members m ON
        (p.[member_mid] = m.mid))";
        $pages = Yii::app()->db->createCommand($pageSql)->queryRow();
        $dataItems = Yii::app()->db->createCommand($sql)->queryAll();
        // Find the caller type users.
        $userCriteria = new CDbCriteria();
        $userCriteria->addSearchCondition('type', 'caller');
        $userCriteria->addSearchCondition('type', 'response', true, 'OR');
        $userCriteria->order = 'name';
        $callerUsers = User::model()->findAll($userCriteria);

        // Get the client names.
        $clientCriteria = new CDbCriteria();
        $clientCriteria->order = 'name';
        $clients = Client::model()->findAll($clientCriteria);

        // Get the fire names.
        $fireNames = ResNotice::model()->getFireNames($model->client_name);
        $noticeTypes = ResNotice::model()->findAll(array('select'=>'wds_status','distinct'=>true));
        //echo $sql;die;
        if($download)
        {
            $model->makeDownloadableReport2($columnsToShow, $sort, $dataItems);
        }
        else
        {
            $this->render('admin2', array(
                'dataProvider' => $dataItems,
                'model' => $model,
                'columnsToShow' => $columnsToShow,
                'columnsToShowName' => self::COLUMNS_TO_SHOW,
                'pageSize' => $pageSize,
                'pageSizeName' => self::PAGE_SIZE,
                'callerUsers' => $callerUsers,
                'clients' => $clients,
                'fireNames' => $fireNames,
                'noticeTypes' => $noticeTypes,
                'pages' => $pages,
                'filter' => $filter_search
            ));
        }
    }
    /**
     * Manually adding policyholder to the call list grid
     */
    public function actionCreate($pid = null)
    {
        $model = new ResCallList();
        if(isset($_POST['ResCallList']['property_id']) && $pid == null)
        {
            $pid = $_POST['ResCallList']['property_id'];
        }
        if(isset($_POST['ResCallList']))
        {
            $model->attributes = $_POST['ResCallList'];
            if($model->save())
            {
                Yii::app()->user->setFlash('success', 'Call List Entry for PID ' . $model->property_id . ' Created Successfully!');
                $this->redirect(array('admin'));
            }
        }

        // Create List Data for Dropdowns
        $fires = Yii::app()->db->createCommand(
            'select distinct c.res_fire_id, f.Name from res_call_list c
             inner join res_fire_name f on f.Fire_ID = c.res_fire_id ORDER BY f.Name')->queryAll();

        $fires_list_data = array();
        foreach ($fires as $fire) {
            $fires_list_data[$fire['res_fire_id']] = $fire['Name'];
        }

        $clients = Yii::app()->db->createCommand(
            'select distinct client_id, name from res_call_list
             inner join client on client.id = res_call_list.client_id')->queryAll();

        $clients_list_data = array();
        foreach ($clients as $client) {
            $clients_list_data[$client['client_id']] = $client['name'];
        }

        // DATA PROVIDER FOR ADD PROPERTY GRID
        $properties = Property::model();

        $criteria = new CDbCriteria();
        $criteria->with = array('member',);
        if ($pid)
            $criteria->condition = "pid = $pid";
        else
            $criteria->condition = 'pid is null';

        $dataProvider = new CActiveDataProvider($properties, array(
            'sort'=>array(
                'attributes'=>array('*'),
            ),
            'criteria'=>$criteria,
        ));

        $columnsArray = array(
            'pid',
            'address_line_1',
            'city',
            'state',
            'zip',
            'member.last_name',
            'member.first_name'
        );

        $this->render('create', array(
            'model' => $model,
            'fireslist' => $fires_list_data,
            'clientslist' => $clients_list_data,
            'dataProvider' => $dataProvider,
            'columnsArray' => $columnsArray
        ));
    }

    /**
     * Updates a Response Call List model.
     * @param integer $id ID of the model to be updated
     */
    public function actionUpdate($id, $callAttemptID = NULL)
    {
        $model = $this->loadModel($id);
        $saveStatus = 0;
        $callSaveStatus = 0;
        if (filter_has_var(INPUT_POST, ResCallList::modelName()))
        {
            $model->attributes = filter_input(INPUT_POST, ResCallList::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $model->save();
        }

        $propertyAccess = ResPropertyAccess::model()->findByAttributes(array('property_id' => $model->property_id));

        if (!isset($propertyAccess))
        {
            $propertyAccess = new ResPropertyAccess();
            $propertyAccess->property_id = $model->property_id;
        }

        if (filter_has_var(INPUT_POST, ResPropertyAccess::modelName()))
        {
            $propertyAccess->attributes = filter_input(INPUT_POST, ResPropertyAccess::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if($propertyAccess->save())
            {
                $saveStatus = 1;
            }

        }

        if (!empty($callAttemptID))
        {
            $callAttempt = ResCallAttempt::model()->findByPk($callAttemptID);
        }
        else
        {
            $fireID = $model->notice->fire_id;
            $propertyID = $model->property_id;

            // First determine how many call attempts have been made for this fire and property.
            $attemptNumber = ResCallAttempt::model()->countByAttributes(array('res_fire_id' => $fireID, 'property_id' => $propertyID));

            $attemptNumber++;

            // Create a new call attempt with a new attempt number.
            $callAttempt = new ResCallAttempt();
            $callAttempt->res_fire_id = $fireID;
            $callAttempt->property_id = $propertyID;
            $callAttempt->attempt_number = $attemptNumber;
            $callAttempt->call_list_id = $model->id;
        }
        if (filter_has_var(INPUT_POST, ResCallAttempt::modelName()))
        {
            $callAttempt->attributes = filter_input(INPUT_POST, ResCallAttempt::modelName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $callAttempt->client_id = $model->client_id;
            $callAttempt->platform = ResCallAttempt::PLATFORM_ADMIN;
            //on create snapshot what the current related properties response status is
            if($callAttempt->isNewRecord)
            {
                $prop = Property::model()->findByPk($callAttempt->property_id);
                $callAttempt->prop_res_status = $model->property->response_status;
            }
            if($callAttempt->caller_user_id!='' && $callAttempt->date_called!='' && $callAttempt->contact_type)
            {
                $callSaveStatus = 1;
            }
            $callAttempt->save();

        }
        if($saveStatus == 1 && $callSaveStatus==1)
        {
            Yii::app()->user->setFlash('success', 'Information saved successfully!');
            return $this->redirect(array('update','id' => $id, 'callAttemptID' => $callAttempt->id));
        }

        $callAttemptSearch = new ResCallAttempt('search');
        $callAttemptSearch->unsetAttributes();
        $callAttemptSearch->res_fire_id = $model->notice->fire_id;
        $callAttemptSearch->property_id = $model->property_id;
        $callAttemptSearchSort = 'id.desc';
        $callAttemptsDataProvider = $callAttemptSearch->search($callAttemptSearchSort);

        $callAttemptsColumnsToShow = array(
            10 => 'id',
            20 => 'attempt_number',
            30 => 'date_called',
            40 => 'caller_user_name',
            50 => 'point_of_contact',
            60 => 'point_of_contact_description',
            70 => 'in_residence',
            80 => 'evacuated',
            90 => 'publish',
            100 => 'contact_type',
            110 => 'prop_res_status'
        );

        // Find the caller type users.
        $userCriteria = new CDbCriteria();

        $userCriteria->addSearchCondition('type', 'caller');
        $userCriteria->addSearchCondition('type', 'response', true, 'OR');
        $userCriteria->order = 'name';
        $callerUsers = User::model()->findAll($userCriteria);

        $pre_risks = new PreRisk('search');
        $pre_risks->unsetAttributes();
        $pre_risks->property_pid = $model->property->pid;

        $this->render('update', array(
            'model' => $model,
            'propertyAccess' => $propertyAccess,
            'callAttemptsDataProvider' => $callAttemptsDataProvider,
            'callAttemptsColumnsToShow' => $callAttemptsColumnsToShow,
            'callAttempt' => $callAttempt,
            'callerUsers' => $callerUsers,
            'pre_risks' => $pre_risks
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    private function loadModel($id)
    {
        $model = ResCallList::model()->with(
            'assigned_caller_user',
            'client',
            'fire',
            'property',
            'property.member',
            'call_attempt')->findByPk($id);

        if ($model === null)
            throw new CHttpException(404,'The requested page does not exist.');

        return $model;
    }

    //------------------------------------------------------------------- General Calls----------------------------------------------------------------

    /**
     * Assigns a caller to an array of call list entries.
     * Note: if you want to clear out an assigned caller, send in 0 for the assignedCallerUserID.
     * This will set all the assigned callers to NULL for the given call list IDs.
     * @param json data with assigned caller user ID and the call list IDs.
     *      Example:
     *      {"data": {"assignedCallerUserID": 123, "callListIDs": [1, 2, 3]}}
     */
    public function actionAssignCallerToCalls()
    {
        $data = null;

        // Note: this originally was an API method, but since it is only used within WDS Admin,
        // we don't need to use oauth. We'll handle the input/output as JSON in the same way
        // to make it easy to be called from javascript via ajax.
        if (!WDSAPI::getInputDataArray($data, array('assignedCallerUserID', 'callListIDs')))
            return;
        $assignedCallerUserID = $data['assignedCallerUserID'];

        if ($assignedCallerUserID <= 0)
            $assignedCallerUserID = NULL;

        $callListIDs = $data['callListIDs'];

        if (!is_array($callListIDs))
            return WDSAPI::echoJsonError("ERROR: callListIDs is not an array!");

        if (count($callListIDs) <= 0)
            return WDSAPI::echoJsonError("ERROR: no callListIDs were provided!");
        $callername = '';
        foreach ($callListIDs as $callListID)
        {
            $callListItem = ResCallList::model()->findByPk($callListID);

            if (!isset($callListItem))
                return WDSAPI::echoJsonError("ERROR: call list entry not found for ID = $callListID.");

            $callListItem->assigned_caller_user_id = $assignedCallerUserID;
            
            $callListItem->save();
            $callername = ($callListItem->assigned_caller_user) ?$callListItem->assigned_caller_user->name : '';
        }

        $returnArray['error'] = 0; // success
        $returnArray['callername'] = $callername; 
        WDSAPI::echoResultsAsJson($returnArray);
    }
    public function actionSearchCalls()
    {
        $data = null;
        //$clients = Client::model()->findAll($clientCriteria);

        // Get the fire names.
       // $fireNames = ResNotice::model()->getFireNames($model->client_name);
        //if (!WDSAPI::getInputDataArray($data, array('firstname')))
           // return;
           $firstname = '';
           $lastname = '';
           $propertyid = '';
           $property_address_line_1 = '';
           $property_address_line_2 = '';
           $assigned_caller_user_name = '';
           $res_triggered_distance = '';
           $client_name = '';
           $fire_name = '';
           $property_city = '';
           $property_state = '';
           $property_zip = '';
           $member_num = '';
           $res_call_attempt_dashboard_comments = '';
           $res_call_attempt_general_comments = '';
           $res_call_attempt_evacuated = '';
           $do_not_call = '';
           $res_notice_wds_status ='';
           $res_triggered_threat = '';
           $res_triggered_response_status = '';
           $resCallList_triggered = '';
           $ResCallAttempt_publish = '';
           $ResCallAttempt_prop_res_status = '';
           $res_triggered_priority = '';
           $filterdata = '';
           $filteritem = '';
           $appendSql = '';
           $page = 0;
           $pageSize = 25;
           $stpage = 0;
           $sortdata = '';
           $sortSql = ' t.id DESC';
           $sorttype = "asc";
           $pageOffset = 0;
           $pagination = true;
           $searchcriteria = false;
           $curpage = 2;
           $search_criteria = false;
           $mnoticetype = '';
           $mclient = '';
           $mfire = '';
           $mcaller = '';
           $mdonotcall = '';
           $mthreat = '';
           $mtriggered = '';
           $mresponsestatus = '';
           $mpublish = '';
           $mcallstatus = '';
           $mlastname = '';
           $sortClass = '';
           $pageSizeMethod = NULL;
        
        if (filter_has_var(INPUT_POST, self::PAGE_SIZE))
        {
            $pageSize = filter_input(INPUT_POST, self::PAGE_SIZE);
            $_SESSION[self::PAGE_SIZE] = $pageSize;
            $pageSizeMethod = 'post';
        }
        else if (isset($_SESSION[self::PAGE_SIZE]))
        {
            $pageSize = $_SESSION[self::PAGE_SIZE];
        }

        $filter_search = array(
            'do_not_call'=>'',
            'assigned_caller_user_name'=>'',
            'client_name'=>'',
            'fire_name'=>'',
            'notice_type'=>'',
            'res_triggered_priority'=>'',
            'res_triggered_threat'=>'',
            'res_triggered_distance'=>'',
            'res_triggered_response_status'=>'',
            'triggered'=>'',
            'evacuated'=>'',
            'published'=>'',
            'dashboard_comments'=>'',
            'general_comments'=>'',
            'prop_res_status'=>'',
            'property_id'=>'',
            'property_address_line_1'=>'',
            'property_address_line_2'=>'',
            'property_city'=>'',
            'property_state'=>'',
            'property_zip'=>'',
            'member_num'=>'',
            'member_first_name'=>'',
            'member_last_name'=>''

        );
        if(Yii::app()->request->isPostRequest)
        {
            /*if(isset($_POST['lastname']) && $_POST['lastname']!=''){
                $lastname = $_POST['lastname'];
            }*/
            $filter_data_val = array(
            'firstname'=>'',
            'lastname'=>''
            );
            /*$gd_filter_data = array(
            array('dbcolumn'=>'m.first_name',
            'searchcolumn'=>'firstname',
            'searchdata'=>'')
            );*/
            $gd_filter_data = array(
            array('dbcolumn'=>'t.do_not_call',
            'searchcolumn'=>'rcldonotcall',
            'filterdata'=>'do_not_call',
            'msearch'=>'mdonotcall',
            'searchdata'=>''),
            
             array('dbcolumn'=>'u.name',
            'searchcolumn'=>'callerUser',
            'filterdata'=>'assigned_caller_user_name',
            'msearch'=>'mcaller',
            'searchdata'=>''),
            
            array('dbcolumn'=>'c.name',
            'searchcolumn'=>'client',
            'filterdata'=>'client_name',
            'msearch'=>'mclient',
            'searchdata'=>''),
            
            array('dbcolumn'=>'f.Name',
            'searchcolumn'=>'fire',
            'filterdata'=>'fire_name',
            'msearch'=>'mfire',
            'searchdata'=>''),
                       
 			array('dbcolumn'=>'n.wds_status',
            'searchcolumn'=>'wds_status',
            'filterdata'=>'notice_type',
            'msearch'=>'mnoticetype',
            'searchdata'=>''),
            
            array('dbcolumn'=>'rt.priority',
            'searchcolumn'=>'res_triggered_priority',
            'filterdata'=>'res_triggered_priority',
            'msearch'=>'mrtpriority',
            'searchdata'=>''),
            
            array('dbcolumn'=>'rt.threat',
            'searchcolumn'=>'threat',
            'filterdata'=>'res_triggered_threat',
             'msearch'=>'mthreat',
            'searchdata'=>''),
            
            array('dbcolumn'=>'rt.distance',
            'searchcolumn'=>'res_triggered_distance',
            'filterdata'=>'res_triggered_distance',
             'msearch'=>'mrtdistance',
            'searchdata'=>''),
            
             array('dbcolumn'=>'rt.response_status',
            'searchcolumn' => 'responseStatus',
            'filterdata'=> 'res_triggered_response_status',
            'msearch'=>'mresponsestatus',
            'searchdata'=>''),
            
            array('dbcolumn'=>'t.triggered',
            'searchcolumn'=>'triggered',
            'filterdata'=>'triggered',
            'msearch'=>'mtriggered',
            'searchdata'=>''),
            
             array('dbcolumn'=>'sca.evacuated',
            'searchcolumn'=>'res_call_attempt_evacuated',
            'filterdata'=>'evacuated',
            'msearch'=>'mrevacuated',
            'searchdata'=>''),
            
            array('dbcolumn'=>'sca.publish',
            'searchcolumn'=>'callpublish',
            'filterdata'=>'published',
            'msearch'=>'mpublish',
            'searchdata'=>''),
            
             array('dbcolumn'=>'sca.dashboard_comments',
            'searchcolumn'=>'res_call_attempt_dashboard_comments',
            'filterdata'=>'dashboard_comments',
            'msearch'=>'mdcomments',
            'searchdata'=>''),
            
            array('dbcolumn'=>'sca.general_comments',
            'searchcolumn'=>'res_call_attempt_general_comments',
            'filterdata'=>'general_comments',
            'msearch'=>'mgcomments',
            'searchdata'=>''),
            
            array('dbcolumn'=>'sca.prop_res_status',
            'searchcolumn'=>'propresstatus',
            'filterdata'=>'prop_res_status',
            'msearch'=>'mcallstatus',
            'searchdata'=>''),
            
            array('dbcolumn'=>'p.pid',
            'searchcolumn'=>'propertyid',
            'filterdata'=>'property_id',
            'msearch'=>'mpropertyid',
            'searchdata'=>''),
            
            array('dbcolumn'=>'p.address_line_1',
            'searchcolumn'=>'property_address_line_1',
            'filterdata'=>'property_address_line_1',
            'msearch'=>'maddress1',
            'searchdata'=>''),
            
            array('dbcolumn'=>'p.address_line_2',
            'searchcolumn'=>'property_address_line_2',
            'filterdata'=>'property_address_line_2',
            'msearch'=>'maddress2',
            'searchdata'=>''),
            
             array('dbcolumn'=>'p.city',
            'searchcolumn'=>'property_city',
            'filterdata'=>'property_city',
            'msearch'=>'mpcity',
            'searchdata'=>''),
            
            array('dbcolumn'=>'p.state',
            'searchcolumn'=>'property_state',
            'filterdata'=>'property_state',
            'msearch'=>'mpstate',
            'searchdata'=>''),
            
            array('dbcolumn'=>'p.zip',
            'searchcolumn'=>'property_zip',
            'filterdata'=>'property_zip',
            'msearch'=>'mpzip',
            'searchdata'=>''),
            
            array('dbcolumn'=>'m.member_num',
            'searchcolumn'=>'member_num',
            'filterdata'=>'member_num',
            'msearch'=>'mnumber',
            'searchdata'=>''),
                 
            array('dbcolumn'=>'m.first_name',
            'searchcolumn'=>'firstname',
            'filterdata'=>'member_first_name',
            'msearch'=>'mfirstname',
            'searchdata'=>''),
            
             array('dbcolumn'=>'m.last_name',
            'searchcolumn'=>'lastname',
            'filterdata'=>'member_last_name',
            'msearch'=>'mlastname',
            'searchdata'=>'')
            );
            if (filter_has_var(INPUT_POST, 'lastname'))
            {
                $lastname = filter_input(INPUT_POST, 'lastname');
            }
            if (filter_has_var(INPUT_POST, 'firstname'))
            {
                $firstname = filter_input(INPUT_POST, 'firstname');
            }
            for($row=22; $row<23; $row++)
            {
                if (filter_has_var(INPUT_POST, $gd_filter_data[$row]['searchcolumn']))
                {
                    /*$gd_filter_data[$row]['searchdata'] = filter_input(INPUT_POST, $gd_filter_data[$row]['searchcolumn']);
                    
                    $appendSql = ($search_criteria)?" AND":" WHERE";
                    if($gd_filter_data[$row]['searchdata'])
                    {
                        $firstname = $gd_filter_data[$row]['searchdata'];
                        $appendSql .= " ".$gd_filter_data[$row]['dbcolumn']." LIKE '%".$gd_filter_data[$row]['searchdata']."%'";
                        $filter_search[$gd_filter_data[$row]['filterdata']] = $firstname;
                    }
                    if($gd_filter_data[$row]['searchdata']!='')
                    {
                        $search_criteria = true;
                    }*/
                }
            }
            
            //--------
          /*  if(isset($_POST['page']) && $_POST['page']!=''){
                $page = $_POST['page'];
                $curpage = $page + 1;
            }
            if(isset($_POST['sortdata']) && $_POST['sortdata']!=''){
                $sortdata = $_POST['sortdata'];
            }
            if(isset($_POST['sorttype']) && $_POST['sorttype']!=''){
                $sorttype = $_POST['sorttype'];
            }
            if(isset($_POST['searchcriteria']) && $_POST['searchcriteria']!=''){

                $searchcriteria = $_POST['searchcriteria'];
            }
            if(isset($_POST['filterdata']) && $_POST['filterdata']!=''){
                $filterdata = $_POST['filterdata'];
            }
            if(isset($_POST['filteritem']) && $_POST['filteritem']!=''){
                $filteritem = $_POST['filteritem'];
            }*/
            //======
            
            if (filter_has_var(INPUT_POST, 'propertyid'))
            {
                $propertyid = filter_input(INPUT_POST, 'propertyid');
            }
            if (filter_has_var(INPUT_POST, 'property_address_line_1'))
            {
                $property_address_line_1 = filter_input(INPUT_POST, 'property_address_line_1');
            }
            if (filter_has_var(INPUT_POST, 'property_address_line_2'))
            {
                $property_address_line_2 = filter_input(INPUT_POST, 'property_address_line_2');
            }
            if (filter_has_var(INPUT_POST, 'res_triggered_distance'))
            {
                $res_triggered_distance = filter_input(INPUT_POST, 'res_triggered_distance');
            }
            if (filter_has_var(INPUT_POST, 'property_city'))
            {
                $property_city = filter_input(INPUT_POST, 'property_city');
            }
            if (filter_has_var(INPUT_POST, 'property_state'))
            {
                $property_state = filter_input(INPUT_POST, 'property_state');
            }
            if (filter_has_var(INPUT_POST, 'property_zip'))
            {
                $property_zip = filter_input(INPUT_POST, 'property_zip');
            }
            if (filter_has_var(INPUT_POST, 'member_num'))
            {
                $member_num = filter_input(INPUT_POST, 'member_num');
            }
            if (filter_has_var(INPUT_POST, 'res_call_attempt_dashboard_comments'))
            {
                $res_call_attempt_dashboard_comments = filter_input(INPUT_POST, 'res_call_attempt_dashboard_comments');
            }
            if (filter_has_var(INPUT_POST, 'res_call_attempt_general_comments'))
            {
                $res_call_attempt_general_comments = filter_input(INPUT_POST, 'res_call_attempt_general_comments');
            }
            if (filter_has_var(INPUT_POST, 'res_call_attempt_evacuated'))
            {
                $res_call_attempt_evacuated = filter_input(INPUT_POST, 'res_call_attempt_evacuated');
            }
            if (filter_has_var(INPUT_POST, 'res_triggered_priority'))
            {
                $res_triggered_priority = filter_input(INPUT_POST, 'res_triggered_priority');
            }
            if (filter_has_var(INPUT_POST, 'assigned_caller_user_name'))
            {
                $assigned_caller_user_name = filter_input(INPUT_POST, 'assigned_caller_user_name');
            }
            if (filter_has_var(INPUT_POST, 'client_name'))
            {
                $client_name = filter_input(INPUT_POST, 'client_name');
            }
            if (filter_has_var(INPUT_POST, 'fire_name'))
            {
                $fire_name = filter_input(INPUT_POST, 'fire_name');
            }
            if (filter_has_var(INPUT_POST, 'do_not_call'))
            {
                $do_not_call = filter_input(INPUT_POST, 'do_not_call');
            }
            if (filter_has_var(INPUT_POST, 'res_notice_wds_status'))
            {
                $res_notice_wds_status = filter_input(INPUT_POST, 'res_notice_wds_status');
            }
            if (filter_has_var(INPUT_POST, 'res_triggered_threat'))
            {
                $res_triggered_threat = filter_input(INPUT_POST, 'res_triggered_threat');
            }
            if (filter_has_var(INPUT_POST, 'res_triggered_response_status'))
            {
                $res_triggered_response_status = filter_input(INPUT_POST, 'res_triggered_response_status');
            }
            if (filter_has_var(INPUT_POST, 'resCallList_triggered'))
            {
                $resCallList_triggered = filter_input(INPUT_POST, 'resCallList_triggered');
            }
            if (filter_has_var(INPUT_POST, 'ResCallAttempt_publish'))
            {
                $ResCallAttempt_publish = filter_input(INPUT_POST, 'ResCallAttempt_publish');
            }
            if (filter_has_var(INPUT_POST, 'ResCallAttempt_prop_res_status'))
            {
                $ResCallAttempt_prop_res_status = filter_input(INPUT_POST, 'ResCallAttempt_prop_res_status');
            }
            if (filter_has_var(INPUT_POST, 'filterdata'))
            {
                $filterdata = filter_input(INPUT_POST, 'filterdata');
            }
            if (filter_has_var(INPUT_POST, 'filteritem'))
            {
                $filteritem = filter_input(INPUT_POST, 'filteritem');
            }
            if (filter_has_var(INPUT_POST, 'page'))
            {
                $page = filter_input(INPUT_POST, 'page');
                $curpage = $page + 1;
            }
            if (filter_has_var(INPUT_POST, 'sortdata'))
            {
                $sortdata = filter_input(INPUT_POST, 'sortdata');
            }
            if (filter_has_var(INPUT_POST, 'sorttype'))
            {
                $sorttype = filter_input(INPUT_POST, 'sorttype');
            }
            if (filter_has_var(INPUT_POST, 'searchcriteria'))
            {
                $searchcriteria = filter_input(INPUT_POST, 'searchcriteria');
            }
            if (filter_has_var(INPUT_POST, 'mclient'))
            {
                $mclient = filter_input(INPUT_POST, 'mclient');
                $client_name = $mclient;
            }
            if (filter_has_var(INPUT_POST, 'mfire'))
            {
                $mfire = filter_input(INPUT_POST, 'mfire');
                $fire_name = $mfire;
            }
            if (filter_has_var(INPUT_POST, 'mcaller'))
            {
                $mcaller = filter_input(INPUT_POST, 'mcaller');
                $assigned_caller_user_name = $mcaller;
            }
            if (filter_has_var(INPUT_POST, 'mdonotcall'))
            {
                $mdonotcall = filter_input(INPUT_POST, 'mdonotcall');
                $do_not_call = $mdonotcall;
            }
            if (filter_has_var(INPUT_POST, 'mnoticetype'))
            {
                $mnoticetype = filter_input(INPUT_POST, 'mnoticetype');
                $res_notice_wds_status = $mnoticetype;
            }
            if (filter_has_var(INPUT_POST, 'mthreat'))
            {
                $mthreat = filter_input(INPUT_POST, 'mthreat');
                $res_triggered_threat = $mthreat;
            }
            if (filter_has_var(INPUT_POST, 'mtriggered'))
            {
                $mtriggered = filter_input(INPUT_POST, 'mtriggered');
                $resCallList_triggered = $mtriggered;
            }
            if (filter_has_var(INPUT_POST, 'mresponsestatus'))
            {
                $mresponsestatus = filter_input(INPUT_POST, 'mresponsestatus');
                $res_triggered_response_status = $mresponsestatus;
            }
            if (filter_has_var(INPUT_POST, 'mpublish'))
            {
                $mpublish = filter_input(INPUT_POST, 'mpublish');
                $ResCallAttempt_publish = $mpublish;
            }
            if (filter_has_var(INPUT_POST, 'mcallstatus'))
            {
                $mcallstatus = filter_input(INPUT_POST, 'mcallstatus');
                $ResCallAttempt_prop_res_status = $mcallstatus;
            }
            if (filter_has_var(INPUT_POST, 'mfirstname'))
            {
                $mfirstname = filter_input(INPUT_POST, 'mfirstname');
                $firstname = $mfirstname;
            }
            if (filter_has_var(INPUT_POST, 'mlastname'))
            {
                $mlastname = filter_input(INPUT_POST, 'mlastname');
                $lastname = $mlastname;
            }
            if (filter_has_var(INPUT_POST, 'mpropertyid'))
            {
                $mpropertyid = filter_input(INPUT_POST, 'mpropertyid');
                $propertyid = $mpropertyid;
            }
            if (filter_has_var(INPUT_POST, 'maddress1'))
            {
                $maddress1 = filter_input(INPUT_POST, 'maddress1');
                $property_address_line_1 = $maddress1;
            }
            if (filter_has_var(INPUT_POST, 'maddress2'))
            {
                $maddress2 = filter_input(INPUT_POST, 'maddress2');
                $property_address_line_2 = $maddress2;
            }
            if (filter_has_var(INPUT_POST, 'mpcity'))
            {
                $mpcity = filter_input(INPUT_POST, 'mpcity');
                $property_city = $mpcity;
            }
            if (filter_has_var(INPUT_POST, 'mpstate'))
            {
                $mpstate = filter_input(INPUT_POST, 'mpstate');
                $property_state = $mpstate;
            }
            if (filter_has_var(INPUT_POST, 'mpzip'))
            {
                $mpzip = filter_input(INPUT_POST, 'mpzip');
                $property_zip = $mpzip;
            }
            if (filter_has_var(INPUT_POST, 'mnumber'))
            {
                $mnumber = filter_input(INPUT_POST, 'mnumber');
                $member_num = $mpstate;
            }
            if (filter_has_var(INPUT_POST, 'mdcomments'))
            {
                $mdcomments = filter_input(INPUT_POST, 'mdcomments');
                $res_call_attempt_dashboard_comments = $mdcomments;
            }
            if (filter_has_var(INPUT_POST, 'mgcomments'))
            {
                $mgcomments = filter_input(INPUT_POST, 'mgcomments');
                $res_call_attempt_general_comments = $mgcomments;
            }
            if (filter_has_var(INPUT_POST, 'mrevacuated'))
            {
                $mrevacuated = filter_input(INPUT_POST, 'mrevacuated');
                $res_call_attempt_evacuated = $mdcomments;
            }
            if (filter_has_var(INPUT_POST, 'mrtpriority'))
            {
                $mrtpriority = filter_input(INPUT_POST, 'mrtpriority');
                $res_triggered_priority = $mrtpriority;
            }
            if (filter_has_var(INPUT_POST, 'mrtdistance'))
            {
                $mrtdistance = filter_input(INPUT_POST, 'mrtdistance');
                $res_triggered_distance = $mrtdistance;
            }
        }
        //text field search-------
        if($res_triggered_priority!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " rt.priority = '".$res_triggered_priority."'";
            $filter_search['res_triggered_priority'] = $res_triggered_priority;
            $search_criteria = true;
        }
        if($res_triggered_distance!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " rt.distance =".$res_triggered_distance;
            $filter_search['res_triggered_distance'] = $res_triggered_distance;
            $search_criteria = true;
        }
        if($res_call_attempt_evacuated!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " sca.evacuated LIKE '%".$res_call_attempt_evacuated."%'";
            $filter_search['evacuated'] = $res_call_attempt_evacuated;
            $search_criteria = true;
        }
        if($propertyid!='')
        { 
            $appendSql .= ($search_criteria)?" AND":" WHERE";       
            $appendSql .= " p.pid =".$propertyid;
            $filter_search['property_id'] = $propertyid;
            $search_criteria = true;
        }
        if($property_address_line_1!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " p.address_line_1 LIKE '%".$property_address_line_1."%'";
            $filter_search['property_address_line_1'] = $property_address_line_1;
            $search_criteria = true;
        }
        if($property_address_line_2!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " p.address_line_2 LIKE '%".$property_address_line_2."%'";
            $filter_search['property_address_line_2'] = $ResCallAttempt_prop_res_status;
            $search_criteria = true;
        }
        if($property_city!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " p.city LIKE '%".$property_city."%'";
            $filter_search['property_city'] = $property_city;
            $search_criteria = true;
        }
        if($property_state!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " p.state LIKE '%".$property_state."%'";
            $filter_search['property_state'] = $property_state;
            $search_criteria = true;
        }
        if($property_zip!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " p.zip LIKE '%".$property_zip."%'";
            $filter_search['property_zip'] = $property_zip;
            $search_criteria = true;
        }
        if($member_num!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " m.member_num LIKE '%".$member_num."%'";
            $filter_search['member_num'] = $member_num;
            $search_criteria = true;
        }
        if($firstname!='')
        {
            $field = "m.first_name";
            $appendSql = ($search_criteria)?" AND":" WHERE";
            $appendSql .= " ".$field." LIKE '%".$firstname."%'";
            $filter_search['member_first_name'] = $firstname;
            $search_criteria = true;
        }
        if($lastname!='')
        {
            $appendSql .= ($search_criteria)?" AND":" WHERE";
            $appendSql .= " m.last_name LIKE '%".$lastname."%'";
            $filter_search['member_last_name'] = $lastname;
            $search_criteria = true;
        }
        /*if($assigned_caller_user_name!='')
        {
            $appendSql .= ($firstname!='')?" AND":" WHERE";
            $appendSql .= " u.name LIKE '%".$assigned_caller_user_name."%'";
        }*/
        $mclient = ($filteritem=='client')?$filterdata:$mclient;
        $mfire = ($filteritem=='fire')?$filterdata:$mfire;
        $mcaller = ($filteritem=='callerUser')?$filterdata:$mcaller;
        $mdonotcall = ($filteritem=='rcldonotcall')?$filterdata:$mdonotcall;
        $mnoticetype = ($filteritem=='wds_status')?$filterdata:$mnoticetype;
        $mthreat = ($filteritem=='threat')?$filterdata:$mthreat;
        $mtriggered = ($filteritem=='triggered')?$filterdata:$mtriggered;
        $mresponsestatus = ($filteritem=='responseStatus')?$filterdata:$mresponsestatus;
        $mpublish = ($filteritem=='callpublish')?$filterdata:$mpublish;
        $mcallstatus = ($filteritem=='propresstatus')?$filterdata:$mcallstatus;
        //dropdown search ----
        if($filterdata!='' || $filteritem!='')
        {
            if($filteritem =='client')
            {
                $client_name = $filterdata;
            }
            if($filteritem =='fire')
            {
                $fire_name = $filterdata;
            }
            if($filteritem =='callerUser')
            {
                $assigned_caller_user_name = $filterdata;
            }
            if($filteritem =='wds_status')
            {
                $res_notice_wds_status = $filterdata;
            }
            if($filteritem =='threat')
            {
                $res_triggered_threat = $filterdata;
            }
            if($filteritem =='triggered')
            {
                $resCallList_triggered = $filterdata;
            }
            if($filteritem =='rcldonotcall')
            {
                $do_not_call = $filterdata;
            }
            if($filteritem =='responseStatus')
            {
                $res_triggered_response_status = $filterdata;
            }
            if($filteritem =='callpublish')
            {
                $ResCallAttempt_publish = $filterdata;
            }
            if($filteritem =='propresstatus')
            {
                $ResCallAttempt_prop_res_status = $filterdata;
            }
            if($do_not_call!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " t.do_not_call =".$do_not_call;
                $search_criteria = true;
            }
            if($assigned_caller_user_name!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " u.name LIKE '%".$assigned_caller_user_name."%'";
                $filter_search['assigned_caller_user_name'] = $assigned_caller_user_name;
                $search_criteria = true;
            }
            if($client_name!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " c.name LIKE '%".$client_name."%'";
                $filter_search['client_name'] = $client_name;
                $search_criteria = true;
            }
            if($fire_name!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " f.Name LIKE '%".$fire_name."%'";
                $filter_search['fire_name'] = $fire_name;
                $search_criteria = true;
            }
            if($res_notice_wds_status!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " n.wds_status = '".$res_notice_wds_status."'";
               // $filter_search['res_notice_wds_status'] = $res_notice_wds_status;
                $search_criteria = true;
            }
            
            if($res_triggered_threat!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " rt.threat =".$res_triggered_threat;
                $filter_search['res_triggered_threat'] = $res_triggered_threat;
                $search_criteria = true;
            }
            
            if($res_triggered_response_status!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " rt.response_status = '".$res_triggered_response_status."'";
                $filter_search['res_triggered_response_status'] = $res_triggered_response_status;
                $search_criteria = true;
            }
            if($resCallList_triggered!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " t.triggered = '".$resCallList_triggered."'";
                $filter_search['triggered'] = $resCallList_triggered;
                $search_criteria = true;
            }
            if($ResCallAttempt_publish!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.publish = ".$ResCallAttempt_publish;
                $filter_search['publish'] = $ResCallAttempt_publish;
                $search_criteria = true;
            }
            if($res_call_attempt_dashboard_comments!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.dashboard_comments LIKE '%".$res_call_attempt_dashboard_comments."%'";
                $filter_search['dashboard_comments'] = $res_call_attempt_dashboard_comments;
                $search_criteria = true;
            }
            if($res_call_attempt_general_comments!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.general_comments LIKE '%".$res_call_attempt_general_comments."%'";
                $filter_search['general_comments'] = $res_call_attempt_general_comments;
                $search_criteria = true;
            }
            if($ResCallAttempt_prop_res_status!='')
            {
                $appendSql .= ($search_criteria)?" AND":" WHERE";
                $appendSql .= " sca.prop_res_status = '".$ResCallAttempt_prop_res_status."'";
                $filter_search['prop_res_status'] = $ResCallAttempt_prop_res_status;
                $search_criteria = true;
            }
        }
        
        if($sortdata!='')
        {
            if($sortdata=='Client Name' &&  $sorttype=='desc')
            {
                $sortSql = " clientname DESC";
            }
            elseif($sortdata=='Client Name')
            {
                $sortSql = " clientname ASC";
            }
            if($sortdata=='Fire Name' &&  $sorttype=='desc')
            {
                $sortSql = " firename DESC";
            }
            elseif($sortdata=='Fire Name')
            {
                $sortSql = " firename ASC";
            }
            
            if($sortdata=='Distance' &&  $sorttype=='desc')
            {
                $sortSql = " rt.distance DESC";
            }
            elseif($sortdata=='Distance')
            {
                $sortSql = " rt.distance ASC";
            }

            if($sortdata=='Do Not Call' &&  $sorttype=='desc')
            {
                $sortSql = " t.do_not_call DESC";
            }
            elseif($sortdata=='Do Not Call')
            {
                $sortSql = " t.do_not_call ASC";
            }

            if($sortdata=='Assigned Caller' &&  $sorttype=='desc')
            {
                $sortSql = " username DESC";
            }
            elseif($sortdata=='Assigned Caller')
            {
                $sortSql = " username ASC";
            }
            if($sortdata=='Notice Type' &&  $sorttype=='desc')
            {
                $sortSql = " n.wds_status DESC";
            }
            elseif($sortdata=='Notice Type')
            {
                $sortSql = " n.wds_status ASC";
            }
            if($sortdata=='Priority' &&  $sorttype=='desc')
            {
                $sortSql = " rt.priority DESC";
            }
            elseif($sortdata=='Priority')
            {
                $sortSql = " rt.priority ASC";
            }
            if($sortdata=='Threat' &&  $sorttype=='desc')
            {
                $sortSql = " rt.threat DESC";
            }
            elseif($sortdata=='Threat')
            {
                $sortSql = " rt.threat ASC";
            }
            if($sortdata=='Response Status' &&  $sorttype=='desc')
            {
                $sortSql = " rt.response_status DESC";
            }
            elseif($sortdata=='Response Status')
            {
                $sortSql = " rt.response_status ASC";
            }
            if($sortdata=='Triggered' &&  $sorttype=='desc')
            {
                $sortSql = " t.triggered DESC";
            }
            elseif($sortdata=='Triggered')
            {
                $sortSql = " t.triggered ASC";
            }
              if($sortdata=='Property ID' &&  $sorttype=='desc')
            {
                $sortSql = " p.pid DESC";
            }
            elseif($sortdata=='Property ID')
            {
                $sortSql = " p.pid ASC";
            }
            if($sortdata=='Address Line 1' &&  $sorttype=='desc')
            {
                $sortSql = " p.address_line_1 DESC";
            }
            elseif($sortdata=='Address Line 1')
            {
                $sortSql = " p.address_line_1 ASC";
            }
            if($sortdata=='Address Line 2' &&  $sorttype=='desc')
            {
                $sortSql = " p.address_line_2 DESC";
            }
            elseif($sortdata=='Address Line 2')
            {
                $sortSql = " p.address_line_2 ASC";
            }
            if($sortdata=='City' &&  $sorttype=='desc')
            {
                $sortSql = " p.city DESC";
            }
            elseif($sortdata=='City')
            {
                $sortSql = " p.city ASC";
            }
            if($sortdata=='State' &&  $sorttype=='desc')
            {
                $sortSql = " p.state DESC";
            }
            elseif($sortdata=='State')
            {
                $sortSql = " p.state ASC";
            }

            if($sortdata=='Zip' && $sorttype=='asc')
            {
                $sortSql = " p.zip ASC";
            }
            elseif($sortdata=='Zip' && $sorttype=='desc')
            {
                $sortSql = " p.zip DESC";
            }
             if($sortdata=='Member Num' &&  $sorttype=='desc')
            {
                $sortSql = " m.member_num DESC";
            }
             elseif($sortdata=='Member Num')
            {
                $sortSql = " m.member_num ASC";
            }
            if($sortdata=='First Name' &&  $sorttype=='desc')
            {
                $sortSql = " m.first_name DESC";
            }
            elseif($sortdata=='First Name')
            {
                $sortSql = " m.first_name ASC";
            }
            
            if($sortdata=='Last Name' &&  $sorttype=='desc')
            {
                $sortSql = " m.first_name DESC";
            }
            elseif($sortdata=='Last Name')
            {
                $sortSql = " m.first_name ASC";
            }
           
        }
        // COLUMNS TO SHOW
        $columnsToShow = array(
            'do_not_call',
            'assigned_caller_user_name',
            'client_name',
            'fire_name',
            'res_triggered_priority',
            'res_triggered_threat',
            'res_triggered_distance',
            'res_triggered_response_status',
            'member_first_name',
            'member_last_name',
            'property_address_line_1',
            'property_city',
            'property_state'
        );
        

        $grid_column = array(
    array(
    'id'=>'s_do_not_call',
    'label'=>'Do Not Call',
    'column'=>'do_not_call',
    'gridcolumn'=>'do_not_call',
    'filterid'=>'do_not_call'
    ),
    array(
    'id'=>'assign_caller',
    'label'=>'Assigned Caller',
    'column'=>'username',
    'gridcolumn'=>'assigned_caller_user_name',
    'filterid'=>'assigned_caller_user_name'
    ),
    array(
    'id'=>'s_client_name',
    'label'=>'Client Name',
    'column'=>'clientname',
    'gridcolumn'=>'client_name',
    'filterid'=>'client_name'
    ),
    array(
    'id'=>'s_fire_name',
    'label'=>'Fire Name',
    'column'=>'firename',
    'gridcolumn'=>'fire_name',
    'filterid'=>'fire_name'
    ),
    array(
    'id'=>'nocite_type',
    'label'=>'Notice Type',
    'column'=>'wds_status',
    'gridcolumn'=>'notice_type',
    'filterid'=>'notice_type'
    ),
    array(
    'id'=>'priority',
    'label'=>'Priority',
    'column'=>'priority',
    'gridcolumn'=>'res_triggered_priority',
    'filterid'=>'res_triggered_priority'
    ),
    array(
    'id'=>'threat',
    'label'=>'Threat',
    'column'=>'threat',
    'gridcolumn'=>'res_triggered_threat',
    'filterid'=>'res_triggered_threat'
    ),
    array(
    'id'=>'distance',
    'label'=>'Distance',
    'column'=>'distance',
    'gridcolumn'=>'res_triggered_distance',
    'filterid'=>'res_triggered_distance'
    ),
    array(
    'id'=>'response_status',
    'label'=>'Response Status',
    'column'=>'rtresponsestatus',
    'gridcolumn'=>'res_triggered_response_status',
    'filterid'=>'res_triggered_response_status'
    ),
    array(
    'id'=>'triggered',
    'label'=>'Triggered',
    'column'=>'triggered',
    'gridcolumn'=>'triggered',
    'filterid'=>'triggered'
    ),
    array(
    'id'=>'',
    'label'=>'Evacuated',
    'column'=>'evacuated',
    'gridcolumn'=>'evacuated',
    'filterid'=>'evacuated'
    ),
    array(
    'id'=>'',
    'label'=>'Published',
    'column'=>'publish',
    'gridcolumn'=>'published',
    'filterid'=>'published'
    ),
    array(
    'id'=>'',
    'label'=>'Dashboard Comments',
    'column'=>'dashboard_comments',
    'gridcolumn'=>'dashboard_comments',
    'filterid'=>'dashboard_comments'
    ),
    array(
    'id'=>'',
    'label'=>'General Comments',
    'column'=>'general_comments',
    'gridcolumn'=>'general_comments',
    'filterid'=>'general_comments'
    ),
    array(
    'id'=>'',
    'label'=>'Call Status',
    'column'=>'prop_res_status',
    'gridcolumn'=>'prop_res_status',
    'filterid'=>'prop_res_status'
    ),
    array(
    'id'=>'p_id',
    'label'=>'Property ID',
    'column'=>'property_id',
    'gridcolumn'=>'property_id',
    'filterid'=>'property_id'
    ),
    array(
    'id'=>'address_line_1',
    'label'=>'Address Line 1',
    'column'=>'address_line_1',
    'gridcolumn'=>'property_address_line_1',
    'filterid'=>'property_address_line_1'
    ),
    array(
    'id'=>'address_line_2',
    'label'=>'Address Line 2',
    'column'=>'address_line_2',
    'gridcolumn'=>'property_address_line_2',
    'filterid'=>'property_address_line_2'
    ),
    array(
    'id'=>'p_city',
    'label'=>'City',
    'column'=>'city',
    'gridcolumn'=>'property_city',
    'filterid'=>'property_city'
    ),
    array(
    'id'=>'p_state',
    'label'=>'State',
    'column'=>'state',
    'gridcolumn'=>'property_state',
    'filterid'=>'property_state'
    ),
    array(
    'id'=>'p_zip',
    'label'=>'Zip',
    'column'=>'zip',
    'gridcolumn'=>'property_zip',
    'filterid'=>'property_zip'
    ),
    array(
    'id'=>'s_member_num',
    'label'=>'Member Num',
    'column'=>'member_num',
    'gridcolumn'=>'member_num',
    'filterid'=>'member_num'
    ),
    array(
    'id'=>'first_name',
    'label'=>'First Name',
    'column'=>'first_name',
    'gridcolumn'=>'member_first_name',
    'filterid'=>'firstname'
    ),
    array(
    'id'=>'last_name',
    'label'=>'Last Name',
    'column'=>'last_name',
    'gridcolumn'=>'member_last_name',
    'filterid'=>'lastname'
    ),
    );
    $grid = new WDSGrid();
        if($search_criteria)
        {
            $_SESSION[self::RATTRIBUTES] = $filter_search ;
        }
        elseif(isset($_SESSION[self::RATTRIBUTES]))
        {
            $filter_search = $_SESSION[self::RATTRIBUTES];
        }
        if (filter_has_var(INPUT_POST, self::COLUMNS_TO_SHOW))
        {
            $columnsToShow = filter_input(INPUT_POST, self::COLUMNS_TO_SHOW, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            $_SESSION[self::COLUMNS_TO_SHOW] = $columnsToShow;
        }
        else if (isset($_SESSION[self::COLUMNS_TO_SHOW]))
        {
            $columnsToShow = $_SESSION[self::COLUMNS_TO_SHOW];
        }
        if($page>0)
        {
            $stpage = $pageSize * ($page -1);
            $pageOffset = ($page -1) * $pageSize;
        }
        
        $toPage = $page * $pageSize;
        $stratPage = $stpage + 1;
        $sql = "SELECT t.id AS calllistid,t.*,u.id,
            u.name AS username,
            c.id,c.name AS clientname,
            f.Fire_ID, sca.prop_res_status AS cprop_res_status, sca.publish AS cpublish, sca.dashboard_comments, 
    sca.general_comments, sca.evacuated, f.Name AS firename,n.*,rt.priority,rt.threat,rt.distance,rt.response_status AS rtresponsestatus,p.*,m.* FROM res_call_list t
    LEFT OUTER JOIN [user] u ON t.assigned_caller_user_id = u.id
    LEFT OUTER JOIN client c ON (t.client_id = c.id) 
    LEFT OUTER JOIN res_fire_name f ON (t.res_fire_id = f.Fire_ID)
    LEFT OUTER JOIN res_notice n ON (n.notice_id = (SELECT
    MAX(notice_id) FROM res_notice rsn WHERE rsn.fire_id = t.res_fire_id AND
    rsn.client_id = t.client_id)) 
    LEFT JOIN res_triggered rt ON (n.notice_id = rt.notice_id AND
    t.property_id = rt.property_pid) 
    LEFT OUTER JOIN properties p ON (t.property_id = p.pid) 
    LEFT OUTER JOIN res_call_attempt sca ON (sca.id = (SELECT MAX(id) FROM res_call_attempt a WHERE a.call_list_id = t.id))  
    LEFT OUTER JOIN members m ON
    (p.[member_mid] = m.mid) ".$appendSql." ORDER BY ".$sortSql." OFFSET ".$pageOffset." ROWS
    FETCH NEXT ".$pageSize." ROWS ONLY";
    $pageSql = "(SELECT count(*) AS totalcount FROM res_call_list t
    LEFT OUTER JOIN [user] u ON t.assigned_caller_user_id = u.id
    LEFT OUTER JOIN client c ON (t.client_id = c.id) 
    LEFT OUTER JOIN res_fire_name f ON (t.res_fire_id = f.Fire_ID)
    LEFT OUTER JOIN res_notice n ON (n.notice_id = (SELECT
    MAX(notice_id) FROM res_notice rsn WHERE rsn.fire_id = t.res_fire_id AND
    rsn.client_id = t.client_id)) 
    LEFT JOIN res_triggered rt ON (n.notice_id = rt.notice_id AND
    t.property_id = rt.property_pid) 
    LEFT OUTER JOIN properties p ON (t.property_id = p.pid)
    LEFT OUTER JOIN res_call_attempt sca ON (sca.id = (SELECT MAX(id) FROM res_call_attempt a WHERE a.call_list_id = t.id))  
    LEFT OUTER JOIN members m ON
    (p.[member_mid] = m.mid)".$appendSql.")";
    $pages = Yii::app()->db->createCommand($pageSql)->queryRow();
    $dataItems = Yii::app()->db->createCommand($sql)->queryAll();
    
    $sortClass1 = ($sortdata=='Distance' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass2 = ($sortdata=='Priority' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass3 = ($sortdata=='Threat' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass4 = ($sortdata=='Client Name' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass5 = ($sortdata=='Fire Name' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass6 = ($sortdata=='Notice Type' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass7 = ($sortdata=='Response Status' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";

    $sortClass8 = ($sortdata=='Property ID' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass9 = ($sortdata=='Address Line 1' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass10 = ($sortdata=='Address Line 2' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass11 = ($sortdata=='City' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass12 = ($sortdata=='State' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass13 = ($sortdata=='Zip' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass14 = ($sortdata=='Member Num' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass15 = ($sortdata=='First Name' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass16 = ($sortdata=='Last Name' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";

    $sortClass17 = ($sortdata=='Assigned Caller' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";   
    $sortClass18 = ($sortdata=='Do Not Call' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    $sortClass19 = ($sortdata=='Triggered' && $sorttype=='asc')?"class='sort-link asc'":"class='sort-link desc'";
    
        $html = '<table style="border:1px solid #dddddd;border-collapse: separate;border-radius:4px" id="res_call_list">';
        $html .= '<thead>
        <tr class="hdr_anch">
          <th class="checkbox-column" id="gridResponseCallList_c0"><input type="checkbox" value="1" name="gridResponseCallList_c0_all" id="gridResponseCallList_c0_all"></th>
          <th class="button-column" id="gridResponseCallList_c1">Update</th>';
          foreach($columnsToShow as $columnToShow)
          {
            if($columnToShow=='do_not_call')
            {
                $html .= '<th id="gridResponseCallList_c2"><a '.$sortClass18.' id ="s_do_not_call" href = "">Do Not Call</a><span class="caret"></span></th>';
            }
            if($columnToShow=='assigned_caller_user_name')
            {
                $html .= '<th id="gridResponseCallList_c3"><a '.$sortClass17.' id ="assign_caller" href = "">Assigned Caller</a><span class="caret"></span></th>';
            }
            if($columnToShow=='client_name')
            {
                $html .= '<th id="gridResponseCallList_c4"><a '.$sortClass4.' id ="s_client_name" href = "">Client Name</a><span class="caret"></span></th>';
            }
            if($columnToShow=='fire_name')
            {
                $html .= '<th id="gridResponseCallList_c5"><a '.$sortClass5.' id ="s_fire_name" href = "">Fire Name</a><span class="caret"></span></th>';
            }
            if($columnToShow=='notice_type')
            {
                $html .= '  <th id="gridResponseCallList_c6"><a id ="nocite_type" href = "">Notice Type</a><span class="caret"></span></th>';
            }
            if($columnToShow=='res_triggered_priority')
            {
                $html .= '<th id="gridResponseCallList_c7"><a '.$sortClass2.' id ="priority" href = "">Priority</a><span class="caret"></span></th>';
            }
            if($columnToShow=='res_triggered_threat')
            {
                $html .= '<th id="gridResponseCallList_c8"><a '.$sortClass3.' id ="threat" href = "">Threat</a><span class="caret"></span></th>';
            }
            if($columnToShow=='res_triggered_distance')
            {
                $html .= '<th id="gridResponseCallList_c9"><a '.$sortClass1.' id ="distance" href = "">Distance</a><span class="caret"></span></th>';
            }
            if($columnToShow=='res_triggered_response_status')
            {
                $html .= '<th id="gridResponseCallList_c10"><a '.$sortClass7.' id ="response_status" href = "">Response Status</a><span class="caret"></span></th>';
            }
            if($columnToShow=='triggered')
            {
                $html .= '<th id="gridResponseCallList_c11"><a '.$sortClass19.' id ="triggered" href = "">Triggered<span class="caret"></span></th>';
            }
            if($columnToShow=='evacuated')
            {
                $html .= '<th id="gridResponseCallList_c12">Evacuated</th>';
            }
            if($columnToShow=='published')
            {
                $html .= '<th id="gridResponseCallList_c13">Published</th>';
            }
            if($columnToShow=='dashboard_comments')
            {
                $html .= '<th id="gridResponseCallList_c14">Dashboard Comments</th>';
            }
            if($columnToShow=='general_comments')
            {
                $html .= '<th id="gridResponseCallList_c15">General Comments</th>';
            }
            if($columnToShow=='prop_res_status')
            {
                $html .= '<th id="gridResponseCallList_c16">Call Status</th>';
            }
            if($columnToShow=='property_id')
            {
                $html .= '<th id="gridResponseCallList_c17"><a '.$sortClass8.' id ="p_id" href = "">Property ID</a><span class="caret"></span></th>';
            }
            
            if($columnToShow=='property_address_line_1')
            {
                $html .= '<th id="gridResponseCallList_c18"><a '.$sortClass9.' id ="address_line_1" href = "">Address Line 1</a><span class="caret"></span></th>';
            }
            if($columnToShow=='property_address_line_2')
            {
                $html .= '<th id="gridResponseCallList_c19"><a '.$sortClass10.' id ="address_line_2" href = "">Address Line 2</a><span class="caret"></span></th>';
            }
            if($columnToShow=='property_city')
            {
                $html .= '<th id="gridResponseCallList_c20"><a '.$sortClass11.' id ="p_city" href = "">City</a><span class="caret"></span></th>';
            }
            if($columnToShow=='property_state')
            {
                $html .= '<th id="gridResponseCallList_c21"><a '.$sortClass12.' id ="p_state" href = "">State</a><span class="caret"></span></th>';
            }
            if($columnToShow=='property_zip')
            {
                $html .= '<th id="gridResponseCallList_c22"><a '.$sortClass13.' id ="p_zip" href = "">Zip</a><span class="caret"></span></th>';
            }
            if($columnToShow=='member_num')
            {
            	$html .= '<th id="gridResponseCallList_c23"><a '.$sortClass14.' id ="s_member_num" href = "">Member Num</a><span class="caret"></span></th>';
            }
            if($columnToShow=='member_first_name')
            {
                $html .= '<th id="gridResponseCallList_c24"><a '.$sortClass.' id ="first_name" href = "">First Name</a><span class="caret"></span></th>';
            }
            if($columnToShow=='member_last_name')
            {
                $html .= '<th id="gridResponseCallList_c25"><a '.$sortClass.' id ="last_name" href = "">Last Name</a><span class="caret"></span></th>';
            }
          }
     
          $html .= '<tr class="filters">
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          ';
          $fcolumns = 0;
            foreach($columnsToShow as $columnToShow)
            {
            if($columnToShow=='do_not_call')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('do_not_call', $do_not_call, array('' => '','1' => 'Selected', '0' => 'Not-Selected'), array('data-filter'=> 'rcldonotcall'));
                $html .= '</div></td>';
            }
            if($columnToShow=='assigned_caller_user_name')
            {
                $html .= '<td><div class="filter-container">';
                $html .=  '<select name="assigned_caller_user_name" id="assigned_caller_user_name" data-filter="callerUser">';
                $html .= '<option value=""></option>';
                    $callerUsersList = User::model()->findAllBySQL("select * from [user] where type Like '%caller%' or type Like '%response%' order by name");
                     foreach ($callerUsersList as $ass_user) {
                     $aname = $ass_user['name'];
                     $html .= '<option value="'.$aname.'" '.($ass_user['name'] == $assigned_caller_user_name ? 'selected="selected"' : '').'>'.$ass_user['name'].'</option>';
                       }                  
                  $html .='</select>
                    </div></td>';
            }
            if($columnToShow=='client_name')
            {
                $html .= '<td><div class="filter-container">';
                $html .=  '<select name="client_name" id="client_name" data-filter="client">';
                $html .= '<option value=""></option>';
                $clientsList = Client::model()->findAll(array('order' => 'name ASC'));
                  foreach ($clientsList as $client) {
                   $cname = $client['name'];
                   $html .= '<option value="'.$cname.'" '.($client['name'] == $client_name ? 'selected="selected"' : '').'>'.$client['name'].'</option>';
                   }
                $html .='</select>';
                $html .= '</div></td>';
            }
            if($columnToShow=='fire_name')
            {
                $html .= '<td><div class="filter-container">';
                $fireNames = ResNotice::model()->getFireNames();
				$firefilter = CHtml::listData($fireNames, 'name', 'name');
				$html .= CHtml::dropDownList('fire_name', $fire_name, $firefilter, array('empty' => '','data-filter' => 'fire'));
                $html .= '</div></td>';
            }
            if($columnToShow=='notice_type')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('res_notice_wds_status', $res_notice_wds_status, array(''=>'','1' => 'Dispatched', '2' => 'Non Dispatched','3' => 'Demobed'), array('data-filter'=> 'wds_status'));
                $html .= '</div></td>';
            }
            if($columnToShow=='res_triggered_priority')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('res_triggered_priority', $res_triggered_priority, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='res_triggered_threat')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('res_triggered_threat', $res_triggered_threat, array(''=>'','1' => 'Yes', '0' => 'No'), array('data-filter'=> 'threat'));
                $html .= '</div></td>';
            }
            if($columnToShow=='res_triggered_distance')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('res_triggered_distance', $res_triggered_distance, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='res_triggered_response_status')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('res_triggered_response_status', $res_triggered_response_status, array(''=>'','enrolled' => 'Enrolled', 'not enrolled' => 'Not enrolled','declined' => 'Declined', 'ineligible' => 'Ineligible'), array('data-filter'=> 'responseStatus'));
                $html .= '</div></td>';
            }
            if($columnToShow=='triggered')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('resCallList_triggered', $resCallList_triggered, array(''=>'','1' => 'Yes', '0' => 'No'), array('data-filter'=> 'triggered'));
                $html .= '</div></td>';
            }
            if($columnToShow=='evacuated')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('res_call_attempt_evacuated', $res_call_attempt_evacuated, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='published')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('ResCallAttempt_publish', $ResCallAttempt_publish, array(''=>'','1' => 'Yes', '0' => 'No'), array('data-filter'=> 'callpublish'));
                $html .= '</div></td>';
            }
            if($columnToShow=='dashboard_comments')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::textField('res_call_attempt_dashboard_comments', $res_call_attempt_dashboard_comments, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='general_comments')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('res_call_attempt_general_comments', $res_call_attempt_general_comments, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='prop_res_status')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::dropDownList('ResCallAttempt_prop_res_status', $ResCallAttempt_prop_res_status, array(''=>'','enrolled' => 'Enrolled', 'not enrolled' => 'Not enrolled'), array('data-filter'=> 'propresstatus'));
                $html .= '</div></td>';
            }
            if($columnToShow=='property_id')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('propertyid', $propertyid, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='property_address_line_1')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('property_address_line_1', $property_address_line_1, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='property_address_line_2')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::textField('property_address_line_2', $property_address_line_2, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='property_city')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('property_city', $property_city, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='property_state')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::textField('property_state', $property_state, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='property_zip')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('property_zip', $property_zip, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='member_num')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::textField('member_num', $member_num, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='member_first_name')
            {
                $html .= '<td><div class="filter-container">';          
                $html .= CHtml::textField('firstname', $firstname, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            if($columnToShow=='member_last_name')
            {
                $html .= '<td><div class="filter-container">';
                $html .= CHtml::textField('lastname', $lastname, array('style' => 'width:90px'));
                $html .= '</div></td>';
            }
            $fcolumns++;
        }
            $html .= '
        </tr>
      </thead>';
      $html .= '<tfoot>
        <tr>
          <td colspan="26"><div id="egw0" style="position:relative" class="pull-left">&nbsp;
              <button class="disabled bulk-actions-btn btn btn-primary btn-small" id="btnLaunchAssignCallerDialog1" name="yt0" type="button">Assign Caller</button>
              &nbsp;&nbsp;
              <button class="disabled bulk-actions-btn btn btn-primary btn-small" id="btnPublishCaller" name="yt1" type="button">Pubish Calls</button>
              &nbsp;
              <div style="position:absolute;top:0;left:0;height:100%;width:100%;display:block;" class="bulk-actions-blocker"></div>
            </div></td>
        </tr>
      </tfoot>';
            $html .= '<tbody>';
            if(empty($dataItems))
            {
                $html .= '<tr><td colspan="26">No results found</td></tr>';
            }
            foreach($dataItems as $data)
            {
               $do_not_call =  ($data['do_not_call'] == 0)? '' : 'DNC';
               $notice_types = ResNotice::getDispatchedType($data['wds_status']);
               $triggered = ($data['triggered'] == 1)? 'Yes' : 'No';
               $publish = ($data['cpublish'] == 1)? 'Yes' : 'No';
               $threat = ($data['threat'] == 1)? 'Yes' : 'No';
               $html .= '<tr class="odd">
          <td style="text-align: center;"><input style="width: 30px;" value="'.$data['calllistid'].'" id="gridResponseCallList_c0_0" type="checkbox" name="gridResponseCallList_c0[]"></td>
          <td class="button-column"><a class="update" title="Update" rel="tooltip" href="'.$this->createUrl('resCallList/update',array('id'=>$data['calllistid'])).'"><i class="icon-pencil"></i></a></td>'; 
           $columns = 0;
           foreach($columnsToShow as $columnToShow)
           {
             if($columnToShow=='do_not_call')
             {
                $html .= '<td>'. $do_not_call .'</td>';
             }
             if($columnToShow=='assigned_caller_user_name')
            {
                $html .= '<td class="assign_caller_'.$data['calllistid'].'">'. $data['username'] .'</td>';
            }
            if($columnToShow=='client_name')
            {
                $html .= '<td>'. $data['clientname'] .'</td>';
            }
            if($columnToShow=='fire_name')
            {
                $html .= '<td>'. $data['firename']  .'</td>';
            }
            if($columnToShow=='notice_type')
            {
                $html .= '<td>'. $notice_types .'</td>';
            }
            if($columnToShow=='res_triggered_priority')
            {
                $html .= '<td>'. $data['priority']  .'</td>';
            }
            if($columnToShow=='res_triggered_threat')
            {
                $html .= '<td>'. $threat .'</td>';
            }
            if($columnToShow=='res_triggered_distance')
            {
                $html .= '<td>'. number_format($data['distance'],2) .'</td>';
            }
            if($columnToShow=='res_triggered_response_status')
            {
                $html .= '<td>'. $data['rtresponsestatus'] .'</td>';
            }
            if($columnToShow=='triggered')
            {
                $html .= '<td>'. $triggered .'</td>';
            }
            if($columnToShow=='evacuated')
            {
                $html .= '<td>'. $data['evacuated'] .'</td>';
            }
            if($columnToShow=='published')
            {
                $html .= '<td>'. $publish .'</td>';
            }
            if($columnToShow=='dashboard_comments')
            {
                $html .= '<td>'. $data['dashboard_comments'] .'</td>';
            }
            if($columnToShow=='general_comments')
            {
                $html .= '<td>'. $data['general_comments'] .'</td>';
            }
            if($columnToShow=='prop_res_status')
            {
                $html .= '<td>'. $data['cprop_res_status'] .'</td>';
            }
            if($columnToShow=='property_id')
            {
                $html .= '<td>'. $data['property_id'] .'</td>';
            }
            
            if($columnToShow=='property_address_line_1')
            {
                $html .= '<td>'. $data['address_line_1'] .'</td>';
            }
            if($columnToShow=='property_address_line_2')
            {
                $html .= '<td>'. $data['address_line_2'] .'</td>';
            }
            if($columnToShow=='property_city')
            {
                $html .= '<td>'. $data['city'] .'</td>';
            }
            if($columnToShow=='property_state')
            {
            	$html .= '<td>'. $data['state'] .'</td>';
            }
            if($columnToShow=='property_zip')
            {
            	$html .= '<td>'. $data['zip'] .'</td>';
            }
            if($columnToShow=='member_num')
            {
            	$html .= '<td>'. $data['member_num'] .'</td>';
            }
            if($columnToShow=='member_first_name')
            {
            	$html .= '<td>'. $data['first_name'] .'</td>';
            }
            if($columnToShow=='member_last_name')
            {
            	$html .= '<td>'. $data['last_name'] .'</td>';
            }
           }
            
            }
$html .= '</tbody><input type="hidden" id = "sortdata" value ="'.$sortdata.'"><input type="hidden" id = "sorttype" value ="'.$sorttype.'">
<input type="hidden" id = "filterdata" value ="'.$filterdata.'">
<input type="hidden" id = "filteritem" value ="'.$filteritem.'">
<input type="hidden" id = "mcaller" value ="'.$mcaller.'">
<input type="hidden" id = "mclient" value ="'.$mclient.'">
<input type="hidden" id = "mfire" value ="'.$mfire.'">
<input type="hidden" id = "mdonotcall" value ="'.$mdonotcall.'">
<input type="hidden" id = "mnoticetype" value ="'.$mnoticetype.'">
<input type="hidden" id = "mthreat" value ="'.$mthreat.'">
<input type="hidden" id = "mtriggered" value ="'.$mtriggered.'">
<input type="hidden" id = "mresponsestatus" value ="'.$mresponsestatus.'">
<input type="hidden" id = "mpublish" value ="'.$mpublish.'">
<input type="hidden" id = "mcallstatus" value ="'.$mcallstatus.'">
<input type="hidden" id = "mfirstname" value ="'.$firstname.'">
<input type="hidden" id = "mlastname" value ="'.$lastname.'">
<input type="hidden" id = "mpropertyid" value ="'.$propertyid.'">
<input type="hidden" id = "maddress1" value ="'.$property_address_line_1.'">
<input type="hidden" id = "maddress2" value ="'.$property_address_line_2.'">
<input type="hidden" id = "mpcity" value ="'.$property_city.'">
<input type="hidden" id = "mpstate" value ="'.$property_state.'">
<input type="hidden" id = "mpzip" value ="'.$property_zip.'">
<input type="hidden" id = "mnumber" value ="'.$member_num.'">

<input type="hidden" id = "mdcomments" value ="'.$res_call_attempt_dashboard_comments.'">
<input type="hidden" id = "mgcomments" value ="'.$res_call_attempt_general_comments.'">
<input type="hidden" id = "mrevacuated" value ="'.$res_call_attempt_evacuated.'">
<input type="hidden" id = "mrtpriority" value ="'.$res_triggered_priority.'">
<input type="hidden" id = "mrtdistance" value ="'.$res_triggered_distance.'">


</table>';
        if($pageSize>$pages['totalcount'])
        {
            $toPage = $pages['totalcount'];
            $pagination = false;
        }
        else
        {
            if($searchcriteria)
            {
                $toPage = ($page +1) * $pageSize;
            }
            else
            {
                $toPage = ($page ) * $pageSize;
            }
            if($toPage>$pages['totalcount'])
            {
                $toPage = $pages['totalcount'];
            }
        }
        
        if(($pages['totalcount']%$pageSize)>0)
        {
           $page = 1;
        }
       
        $totalpages = (int)($pages['totalcount']/$pageSize) + $page;
        $returnArray['error'] = 0; // success
        $returnArray['data'] = $html;
        $returnArray['pages'] = $page;
        $returnArray['toPage'] = $toPage;
        $returnArray['totalpages'] = $pages['totalcount'];
        $returnArray['stratPage'] = $stratPage;
        $returnArray['pagination'] = $pagination;
        $returnArray['pagecounter'] = $totalpages;
        $returnArray['nextpage'] = $curpage;
        $returnArray['f'] = $appendSql;
        $returnArray['sql'] = $page."->".$appendSql.$sortSql."->".$sortdata."->".$sorttype."->".$sql.'c-'.$filterdata.'cl'.$client_name.$fire_name;
        WDSAPI::echoResultsAsJson($returnArray);
    }
    /**
     * Publishes calls for an array of call list entries.
     * @param json data with assigned caller user ID and the call list IDs.
     $gd_filter_data[$row]['searchdata']*      Example:
     *  {
     *      "data": {
     *          "publishedCallType": 1,
     *          "callListIDs": [1, 2, 3]
     *      }
     *  }
     */
    public function actionPublishCalls()
    {
        $data = null;

        if (!WDSAPI::getInputDataArray($data, array('publishedCallType', 'callListIDs')))
            return;

        $callListIDs = $data['callListIDs'];
        $publishedType = $data['publishedCallType'];
        $publishedType = $publishedType == -1 ? null : $publishedType;

        if (!is_array($callListIDs))
            return WDSAPI::echoJsonError("ERROR: callListIDs is not an array!");

        if (count($callListIDs) <= 0)
            return WDSAPI::echoJsonError("ERROR: no callListIDs were provided!");

        $criteria = new CDbCriteria();
        $criteria->addInCondition('call_list_id', $callListIDs);

        $models = ResCallAttempt::model()->findAll($criteria);

        if ($models)
        {
            foreach($models as $model)
            {
                $model->publish = $publishedType;
                $model->save();
            }
        }

        $returnArray['error'] = 0; // success
        $returnArray['cl'] = $callListIDs; 

        WDSAPI::echoResultsAsJson($returnArray);
    }

    public function getGridDashboardComments($data)
    {
        if (isset($data->dashboard_comments))
        {
            return implode('<br /><hr class="separator" />', $data->dashboard_comments);
        }
    }

    public function getGridGeneralComments($data)
    {
        if (isset($data->general_comments))
        {
            return implode('<br /><hr class="separator" />', $data->general_comments);
        }
    }

    //------------------------------------------------------------------- API Calls----------------------------------------------------------------

    /**
     * API Method: resCallList/apiGetCallsByFire
     * Description: Gets all policy calls for a specific notice
     *
     * Post data parameters:
     * @param int clientID - ID of the client
     * @param int noticeID - ID of the notice
     * @param int fireID - ID of the fire
     *
     *
     * Post data example:
     * {
     *     "data": {
     *         "noticeID": 6453,
     *         "fireID": 7347,
     *         "clientID": 2
     *     }
     * }
     */
    public function actionApiGetCallsByFire()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID', 'noticeID', 'fireID')))
            return;

        $notieID = $data['noticeID'];
        $fireID = $data['fireID'];
        $clientID = $data['clientID'];

        $criteria = new CDbCriteria;
        $criteria->select = 'date_created, notice_id';
        $criteria->addCondition("fire_id = $fireID");
        $criteria->addCondition("client_id = $clientID");
        $criteria->order = 'notice_id DESC';

        if (ResNotice::model()->exists($criteria))
        {
            $notices = ResNotice::model()->findAll($criteria);

            $allcalls = false;

            // Determine if most recent notice and all calls can be fetched or if calls need to be restricted by notice date.
            // Older notices will show all calls up to the next notice.

            for ($i = 0; $i < count($notices); $i++)
            {
                if ($notices[$i]->notice_id == $notieID)
                {
                    if ($i === 0)
                    {
                        $date = $notices[$i]->date_created;
                        $allcalls = true;
                    }
                    else
                    {
                        $date = $notices[$i - 1]->date_created;
                    }
                }
            }

            // Select all calls ... by date if necessary
            $callcriteria = new CDbCriteria;
            $callcriteria->addCondition("res_fire_id = $fireID");
            $callcriteria->addCondition("client_id = $clientID");
            if (!$allcalls)
                $callcriteria->addCondition("date_called < '$date'");
            $callcriteria->order = 'date_called desc';

            $models = ResCallAttempt::model()->findAll($callcriteria);

            $returnData = array();

            foreach($models as $model)
            {
                if ($model->property)
                {
                    if ($model->publish == 1)
                    {
                        if (!isset($returnData[$model->property_id]))
                            $returnData[$model->property_id] = array();

                        if (!isset($returnData[$model->property_id]) && !isset($returnData[$model->property_id]['calls']))
                            $returnData[$model->property_id]['calls'] = array();

                        // Snapshot Status logic - first see if they exist in the triggered table, otherwise try to figure out when they enrolled
                        $triggeredEntry = ResTriggered::model()->findByAttributes(array('notice_id'=>$notieID, 'property_pid'=>$model->property_id));
                        if($triggeredEntry)
                            $snapshotStatus = $triggeredEntry->response_status;
                        elseif ($model->property->response_enrolled_date && (date_create($model->property->response_enrolled_date) < date_create($date)))
                            $snapshotStatus = 'enrolled';
                        else
                            $snapshotStatus = $model->property->response_status;

                        $memberNumber = '';
                        if ($model->property->member) {
                            $memberNumber = $model->property->member->member_num;
                        }

                        $returnData[$model->property_id]['property_id'] = $model->property_id;
                        $returnData[$model->property_id]['member_name'] = $model->property->member ? $model->property->member->last_name . ', ' . $model->property->member->first_name : '';
                        $returnData[$model->property_id]['address'] = $model->property->address_line_1;
                        $returnData[$model->property_id]['city'] = $model->property->city;
                        $returnData[$model->property_id]['state'] = $model->property->state;
                        $returnData[$model->property_id]['response_status'] = $model->property->response_status;
                        $returnData[$model->property_id]['snapshot_status'] = $snapshotStatus;
                        $returnData[$model->property_id]['member_num'] = $memberNumber;
                        $returnData[$model->property_id]['producer_name'] = ($data['clientID'] == '2') ? trim(explode('(', $model->property->producer)[0]) : '';
                        $returnData[$model->property_id]['signature'] = $model->property->member ? $model->property->member->signed_ola : '';

                        $returnData[$model->property_id]['calls'][] = array(
                            'attempt_number' => $model->attempt_number,
                            'date_called' => $model->date_called,
                            'point_of_contact' => $model->point_of_contact,
                            'point_of_contact_description' => $model->point_of_contact_description,
                            'in_residence' => $model->in_residence,
                            'evacuated' => $model->evacuated,
                            'dashboard_comments' => $model->dashboard_comments
                        );
                    }
                }
            }

            $returnArray['error'] = 0;
            $returnArray['data'] = $returnData;

            return WDSAPI::echoResultsAsJson($returnArray);
        }
        else
        {
            return WDSAPI::echoJsonError('ERROR: there are notices matching this clientID and fireID');
        }
    }

    /**
     * API Method: resCallList/apiGetClientCallAttempts
     * Description: Get all call attempts for WDSFire for a given call_list_id
     *
     * Post data parameters:
     * @param int callListID
     *
     * Post data example:
     * {
     *     "data": {
     *         "callListID": 17
     *     }
     * }
     */
    public function actionApiGetClientCallAttempts()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('callListID')))
            return;

        $models = ResCallAttempt::model()->findAllByAttributes(array(
            'call_list_id' => $data['callListID'],
            'platform' => ResCallAttempt::PLATFORM_WDS_FIRE
        ));

        $returnArray = array(
            'data' => array(),
            'error' => 0
        );

        if ($models)
        {
            foreach ($models as $model)
            {
                $returnArray['data'][] = array(
                    'id' => $model->id,
                    'pid' => $model->property_id,
                    'caller_user_id' => $model->caller_user_id,
                    'caller_user_name' => $model->caller_user->name,
                    'attempt_number' => $model->attempt_number,
                    'date_called' => $model->date_called,
                    'point_of_contact' => $model->point_of_contact,
                    'point_of_contact_description' => $model->point_of_contact_description,
                    'dashboard_comments' => $model->dashboard_comments,
                    'call_list_id' => $model->call_list_id,
                    'contact_type' => $model->contact_type,
                    'prop_res_status' => $model->prop_res_status,
                );
            }
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resCallList/apiGetCallAttempt
     * Description: Get a call list entry by id
     *
     * Post data parameters:
     * @param int id
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 593
     *     }
     * }
     */
    public function actionApiGetCallAttempt()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('id')))
            return;

        $model = ResCallAttempt::model()->findByPk($data['id']);

        if ($model)
        {
            $returnArray = array(
                'error' => 0,
                'data' => array_merge($model->getAttributes(), array('caller_user_name' => $model->caller_user->name))
            );

            return WDSAPI::echoResultsAsJson($returnArray);
        }

        return WDSAPI::echoJsonError('There was an error.', 'No model could be found with this id: ' . $data['id']);
    }

    /**
     * API Method: resCallList/apiCreateClientCallAttempt
     * Description: Create a new call attempt for WDSFire
     *
     * Post data parameters:
     * @param int callListID
     * @param int property_id
     * @param int caller_user_id
     * @param string date_called
     * @param string point_of_contact
     * @param string point_of_contact_description
     * @param string contact_type
     * @param string dashboard_comments
     * @param int call_list_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "res_fire_id": 17,
     *         "property_id": 123456,
     *         "caller_user_id": 5,
     *         "date_called": "2015-05-23",
     *         "point_of_contact": "Dan Rust",
     *         "point_of_contact_description": "Homeowner",
     *         "contact_type": "Successful Contact (Undecided)",
     *         "dashboard_comments": "Some dashboard comments",
     *         "call_list_id": 5
     *     }
     * }
     */
    public function actionApiCreateClientCallAttempt()
    {
        $data = NULL;

        $requiredFields = array('res_fire_id','property_id','caller_user_id','date_called','point_of_contact','point_of_contact_description','contact_type','dashboard_comments','call_list_id');

        if (!WDSAPI::getInputDataArray($data, $requiredFields))
            return;

        $attemptNumber = ResCallAttempt::model()->countByAttributes(array('res_fire_id' => $data['res_fire_id'], 'property_id' => $data['property_id']));
        $attemptNumber++;

        $callAttempt = new ResCallAttempt;
        $callAttempt->res_fire_id = $data['res_fire_id'];
        $callAttempt->property_id = $data['property_id'];
        $callAttempt->caller_user_id = $data['caller_user_id'];
        $callAttempt->attempt_number = $attemptNumber;
        $callAttempt->date_called = $data['date_called'];
        $callAttempt->point_of_contact = $data['point_of_contact'];
        $callAttempt->point_of_contact_description = $data['point_of_contact_description'];
        $callAttempt->dashboard_comments = $data['dashboard_comments'];
        $callAttempt->call_list_id = $data['call_list_id'];
        $callAttempt->contact_type = $data['contact_type'];
        $callAttempt->platform = ResCallAttempt::PLATFORM_WDS_FIRE;

        //snapshot property response status at the time of save
        $property = Property::model()->findByPk($data['property_id']);
        if(isset($property))
        {
            $callAttempt->prop_res_status = $property->response_status;
        }

        try
        {
            if (!$callAttempt->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $callAttempt->getErrors());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        return WDSAPI::echoResultsAsJson(array('data' => null, 'error' => 0));
    }

    /**
     * API Method: resCallList/apiUpdateClientCallAttempt
     * Description: Update an existing call attempt for WDSFire
     *
     * Post data parameters:
     * @param int id
     * @param int callListID
     * @param int property_id
     * @param int caller_user_id
     * @param string date_called
     * @param string point_of_contact
     * @param string point_of_contact_description
     * @param string contact_type
     * @param string dashboard_comments
     * @param int call_list_id
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 378
     *         "res_fire_id": 17,
     *         "property_id": 123456,
     *         "caller_user_id": 5,
     *         "date_called": "2015-05-23",
     *         "point_of_contact": "Dan Rust",
     *         "point_of_contact_description": "Homeowner",
     *         "dashboard_comments": "Some dashboard comments",
     *         "call_list_id": 5,
     *         "contact_type": "Successful Contact (Undecided)",
     *     }
     * }
     */
    public function actionApiUpdateClientCallAttempt()
    {
        $data = NULL;

        $requiredFields = array('id','caller_user_id','date_called','point_of_contact','point_of_contact_description','contact_type','dashboard_comments');

        if (!WDSAPI::getInputDataArray($data, $requiredFields))
            return;

        $callAttempt = ResCallAttempt::model()->findByPk($data['id']);

        if ($callAttempt)
        {
            $callAttempt->caller_user_id = $data['caller_user_id'];
            $callAttempt->date_called = $data['date_called'];
            $callAttempt->point_of_contact = $data['point_of_contact'];
            $callAttempt->point_of_contact_description = $data['point_of_contact_description'];
            $callAttempt->dashboard_comments = $data['dashboard_comments'];
            $callAttempt->contact_type = $data['contact_type'];

            try
            {
                if (!$callAttempt->save())
                    return WDSAPI::echoJsonError('ERROR: There was a database error.', $callAttempt->getErrors());
            }
            catch (CDbException $e)
            {
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
            }

            return WDSAPI::echoResultsAsJson(array('data' => null, 'error' => 0));
        }

        return WDSAPI::echoJsonError('There was an error.', 'No model could be found with this id: ' . $data['id']);
    }

    /**
     * API method: resCallList/apiGetPolicyCallsByDate
     * Description: Gets policyholder calls start for a time range and client
     *
     * Post data parameters:
     * @param string startDate
     * @param string endDate
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "startDate": "2016-03-01",
     *         "endDate": "2016-06-01",
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetPolicyCallsByDate()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('startDate', 'endDate', 'clientID')))
            return;

        $sql = "
            SELECT
                l.res_fire_id,
                l.property_id,
                m.last_name,
                m.first_name,
                p.address_line_1,
                a.attempt_number,
                a.date_called,
                n.Name
            FROM res_call_list l
                INNER JOIN res_call_attempt a ON l.id = a.call_list_id
                INNER JOIN res_fire_name n ON l.res_fire_id = n.Fire_ID
                INNER JOIN properties p ON l.property_id = p.pid
                INNER JOIN members m ON p.member_mid = m.mid
            WHERE a.date_called >= :start_date
                AND a.date_called < :end_date
                AND l.client_id = :client_id
            ORDER BY l.res_fire_id DESC, l.property_id ASC, a.attempt_number
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':start_date', $data['startDate'], PDO::PARAM_STR)
            ->bindParam(':end_date', $data['endDate'], PDO::PARAM_STR)
            ->bindParam('client_id', $data['clientID'], PDO::PARAM_INT)
            ->queryAll();

        WDSAPI::echoResultsAsJson(array('data' => $results, 'error' => 0));
    }

     /**
     * API method: resCallList/apiGetClientCallLog
     * Description: Gets policyholder calls made by the client given the fire and client
     *
     * Post data parameters:
     * @param int fireID
     * @param int clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "fireID": "2543",
     *         "clientID": 1
     *     }
     * }
     */
    public function actionApiGetClientCallLog()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('fireID', 'clientID')))
            return;

        //All call attempts
        $result['attempts'] = $this->getCallAttempts($data['fireID'], $data['clientID']);
        //Property access
        $result['property_access'] = $this->getPropertyAccess($data['fireID'], $data['clientID']);

        WDSAPI::echoResultsAsJson(array('data' => $result, 'error' => 0));

    }


    /*
    *   Description: Helper fucntion to get the call attempts for a given fire and client
    *
    */
    public function getCallAttempts($fireID, $clientID)
    {
        $sql = "
                select
                    a.date_called,
                    m.first_name,
                    m.last_name,
                    p.address_line_1,
                    p.city,
                    p.state,
                    p.response_status as current_response_status,
                    p.response_enrolled_date,
                    u.name as 'Caller Name',
                    a.attempt_number,
                    a.point_of_contact,
                    a.point_of_contact_description,
                    a.dashboard_comments as 'Comments',
                    a.contact_type,
                    p.producer,
                    p.agency_name,
                    p.agency_code,
                    p.policy as 'Policy Number' --,
                    --r.date_updated as 'Date Threatened'
                from
                    res_call_attempt a
                inner join
                    properties p on p.pid = a.property_id

               LEFT OUTER JOIN res_notice r ON (r.notice_id = (
               SELECT MAX(notice_id) FROM res_notice rsn where rsn.fire_id = a.res_fire_id AND rsn.client_id =
               p.client_id AND rsn.publish = 1
               ))
                inner join
                    members m on m.mid = p.member_mid
                inner join
                    [user] u on u.id = a.caller_user_id
                where
                    a.platform = 2
                    and a.res_fire_id = :fireID
                    and p.client_id = :clientID
                order by
                    a.property_id asc, a.date_called asc
            ";

            return Yii::app()->db->createCommand($sql)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
                ->bindParam(':fireID', $fireID, PDO::PARAM_INT)
                ->queryAll();
        }

        public function getPropertyAccess($fireID, $clientID){
            $sql = "
                select
                    m.first_name,
                    m.last_name,
                    p.address_line_1,
                    p.city,
                    p.state,
                    p.response_status as current_response_status,
                    p.response_enrolled_date,
                    a.access_issues,
                    a.address_verified,
                    a.best_contact_number,
                    a.gate_code,
                    a.suppression_resources,
                    a.other_info
                from
                    properties p
                inner join
                    members m on m.mid = p.member_mid
                inner join
                    res_property_access a on a.property_id = p.pid
                where
                    pid in (
                        select
                            property_id from res_call_attempt
                        where
                            platform = 2
                        and
                            res_fire_id = :fireID
                    )

                    and p.client_id = :clientID
                order by
                    p.last_update desc;
                ";

        return Yii::app()->db->createCommand($sql)
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->bindParam(':fireID', $fireID, PDO::PARAM_INT)
            ->queryAll();
    }

    /**
     * API Method: resCallList/apiGetClientCallsNoticeQueryCount
     * Description: Gets count call information for dashboard notice on a given notice and client.
     *
     * Post data parameters:
     * @param string noticeID
     * @param string clientID
     * @param integer enrolled
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": "1007",
     *         "fireID": "17508",
     *         "enrolled": 1,
     *         "compareArray": {
     *             "column1": "text",
     *             "column2": "text"
     *         }
     *     }
     * }
     */
    public function actionApiGetClientCallsNoticeQueryCount()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('clientID','fireID','enrolled')))
            return;

        $sql = "
            DECLARE @fireID INT = :fire_id;
            DECLARE @clientID INT = :client_id;

            SELECT COUNT(DISTINCT c.id)
            FROM res_call_list c
            INNER JOIN properties p on p.pid = c.property_id
            INNER JOIN members m on m.mid = p.member_mid
            -- triggered values
            LEFT OUTER JOIN res_triggered t ON (
                t.property_pid = c.property_id AND t.notice_id = (
                    SELECT MAX(notice_id)
                    FROM res_notice rsn
                    WHERE rsn.fire_id = c.res_fire_id AND rsn.client_id = c.client_id AND rsn.publish = 1
                )
            )
            -- initial threatened date
            LEFT OUTER JOIN (
                SELECT
                    t.property_pid AS pid,
                    MIN(n.date_created) AS date_threatened
                FROM res_triggered t
                INNER JOIN res_notice n ON t.notice_id = n.notice_id
                WHERE n.fire_id = @fireID AND n.client_id = @clientID AND t.threat = 1
                GROUP BY t.property_pid
            ) v ON v.pid = c.property_id
            -- call attempts
            LEFT OUTER JOIN (
                SELECT COUNT(a.id) AS attempts, a.property_id
                FROM res_call_attempt a INNER JOIN res_call_list l ON l.id = a.call_list_id
                WHERE a.res_fire_id = @fireID AND l.client_id = @clientID AND a.platform = 2
                GROUP BY a.property_id
            ) a ON a.property_id = c.property_id
            WHERE c.res_fire_id = @fireID
                AND c.client_id = @clientID";

        $enrolled = filter_var($data['enrolled'], FILTER_VALIDATE_BOOLEAN);

        if ($enrolled === true)
        {
            $sql .= " AND p.response_status = 'enrolled'";
        }
        else
        {
            $sql .= " AND p.response_status != 'enrolled'";
        }

        $params = array();
        $params[':fire_id'] = $data['fireID'];
        $params[':client_id'] = $data['clientID'];

        // Searching

        foreach ($data['compareArray'] as $key => $value)
        {
            if ($key === 'first_name')
            {
                $sql .= " AND m.first_name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'last_name')
            {
                $sql .= " AND m.last_name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'address')
            {
                $sql .= " AND p.address_line_1 LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'threat')
            {
                $sql .= " AND t.threat = :$key";
                $params[":$key"] = $value;
            }
            else if ($key === 'date_threatened')
            {
                $sql .= " AND :1$key >= CONVERT(DATE,v.date_threatened) AND :2$key < CONVERT(DATE,DATEADD(DAY,1,v.date_threatened))";
                $params[":1$key"] = $value;
                $params[":2$key"] = $value;
            }
            else if ($key === 'response_enrolled_date')
            {
                $sql .= " AND :1$key >= CONVERT(DATE,p.response_enrolled_date) AND :2$key < CONVERT(DATE,DATEADD(DAY,1,p.response_enrolled_date))";
                $params[":1$key"] = $value;
                $params[":2$key"] = $value;
            }
        }

        $count = Yii::app()->db->createCommand($sql)->queryScalar($params);

        $returnArray['data'] = $count;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API Method: resCallList/apiGetClientCallsNotice
     * Description: Gets call information for dashboard notice on a given notice and client.
     *
     * Post data parameters:
     * @param string noticeID
     * @param string clientID
     * @param integer enrolled
     * @param integer limit - used for limiting number of results
     * @param integer offset - used for pagination
     * @param array sortArray - associative array of column => SORT_ASC/SORT_DESC for sorting
     * @param array compareArray - associative array of column => text for searching
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": "1007",
     *         "fireID": "17508",
     *         "enrolled": 1,
     *         "limit": 20,
     *         "offset": 60,
     *         "sortArray": {
     *             "first_name": 3
     *         }
     *         "compareArray": {
     *             "column1": "text",
     *             "column2": "text"
     *         }
     *     }
     * }
     */
    public function actionApiGetClientCallsNotice()
    {
        $data = NULL;
        $returnArray = array();

        if (!WDSAPI::getInputDataArray($data, array('clientID','fireID','enrolled')))
            return;

        $sortDirection = function($sort)
        {
            return ($sort == SORT_ASC) ? 'ASC' : 'DESC';
        };

        $sql = "
        DECLARE @fireID INT = :fire_id;
        DECLARE @clientID INT = :client_id;

        SELECT
            c.id,
            c.res_fire_id AS fire_id,
            c.property_id AS pid,
            m.first_name,
            m.last_name,
            p.address_line_1 AS address,
            p.city,
            p.state,
            p.response_status,
            STR(t.distance, 4, 2) AS distance,
            t.threat,
            ISNULL(a.attempts, 0) AS calls,
            v.date_threatened,
            p.response_enrolled_date
        FROM res_call_list c
        INNER JOIN properties p on p.pid = c.property_id
        INNER JOIN members m on m.mid = p.member_mid
        -- triggered values
        LEFT OUTER JOIN res_triggered t ON (
            t.property_pid = c.property_id AND t.notice_id = (
                SELECT MAX(notice_id)
                FROM res_notice rsn
                WHERE rsn.fire_id = c.res_fire_id AND rsn.client_id = c.client_id AND rsn.publish = 1
            )
        )
        -- initial threatened date
        LEFT OUTER JOIN (
            SELECT
                t.property_pid AS pid,
                MIN(n.date_created) AS date_threatened
            FROM res_triggered t
            INNER JOIN res_notice n ON t.notice_id = n.notice_id
            WHERE n.fire_id = @fireID AND n.client_id = @clientID AND t.threat = 1
            GROUP BY t.property_pid
        ) v ON v.pid = c.property_id
        -- call attempts
        LEFT OUTER JOIN (
            SELECT COUNT(a.id) AS attempts, a.property_id
            FROM res_call_attempt a INNER JOIN res_call_list l ON l.id = a.call_list_id
            WHERE a.res_fire_id = @fireID AND l.client_id = @clientID AND a.platform = 2
            GROUP BY a.property_id
        ) a ON a.property_id = c.property_id
        WHERE c.res_fire_id = @fireID
            AND c.client_id = @clientID AND p.policy_status = 'active'";

        $enrolled = filter_var($data['enrolled'], FILTER_VALIDATE_BOOLEAN);

        if ($enrolled === true)
        {
            $sql .= " AND p.response_status = 'enrolled'";
        }
        else
        {
            $sql .= " AND p.response_status != 'enrolled'";
        }

        $params = array();
        $params[':fire_id'] = $data['fireID'];
        $params[':client_id'] = $data['clientID'];

        // Searching

        foreach ($data['compareArray'] as $key => $value)
        {
            if ($key === 'first_name')
            {
                $sql .= " AND m.first_name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'last_name')
            {
                $sql .= " AND m.last_name LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'address')
            {
                $sql .= " AND p.address_line_1 LIKE :$key";
                $params[":$key"] = "%$value%";
            }
            else if ($key === 'threat')
            {
                $sql .= " AND t.threat = :$key";
                $params[":$key"] = $value;
            }
            else if ($key === 'date_threatened')
            {
                $sql .= " AND :1$key >= CONVERT(DATE,v.date_threatened) AND :2$key < CONVERT(DATE,DATEADD(DAY,1,v.date_threatened))";
                $params[":1$key"] = $value;
                $params[":2$key"] = $value;
            }
            else if ($key === 'response_enrolled_date')
            {
                $sql .= " AND :1$key >= CONVERT(DATE,p.response_enrolled_date) AND :2$key < CONVERT(DATE,DATEADD(DAY,1,p.response_enrolled_date))";
                $params[":1$key"] = $value;
                $params[":2$key"] = $value;
            }
        }

        // Sorting

        if (count($data['sortArray']) > 0)
        {
            $sortArray = array();
            foreach ($data['sortArray'] as $key => $sort)
            {
                if ($key === 'first_name')
                    $sortArray[] = 'm.first_name ' . $sortDirection($sort);
                else if ($key === 'last_name')
                    $sortArray[] = 'm.last_name ' . $sortDirection($sort);
                else if ($key === 'distance')
                    $sortArray[] = 't.distance ' . $sortDirection($sort);
                else if ($key === 'threat')
                    $sortArray[] = 't.threat ' . $sortDirection($sort);
                else if ($key === 'date_threatened')
                    $sortArray[] = 'v.date_threatened ' . $sortDirection($sort);
                else if ($key === 'response_enrolled_date')
                    $sortArray[] = 'p.response_enrolled_date ' . $sortDirection($sort);
            }

            $sql .= ' ORDER BY ' . implode(',', $sortArray);
        }

        // Limit - Offset

        $offset  = isset($data['offset']) ? (int)$data['offset'] : -1;
        $limit  = isset($data['limit']) ? (int)$data['limit'] : -1;

        if ($limit > 0 && $offset <= 0)
        {
            $sql .= " OFFSET 0 ROWS FETCH NEXT $limit ROWS ONLY";
        }
        else if ($limit > 0 && $offset > 0)
        {
            $sql .= " OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
        }
        else if ($limit <= 0 && $offset > 0)
        {
            $sql .= " OFFSET $offset ROWS";
        }

        $results = Yii::app()->db->createCommand($sql)->queryAll(true, $params);

        $returnArray['data'] = $results;
        $returnArray['error'] = 0;

        return WDSAPI::echoResultsAsJson($returnArray);
    }
}