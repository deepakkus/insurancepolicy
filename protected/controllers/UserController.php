<?php

class UserController extends Controller
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
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetUsersByClient',
                        'apiGetUserByID',
                        'apiGetIsUsernameUnique',
                        'apiCreateUser',
                        'apiUpdateUser',
                        'apiLogin',
                        'apiChangePass',
                        'apiResetPass',
                        'apiRequestResetPass',
                        'apiGetClientsDropdownByClientId',
						'apiGetCountryLists'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiLogin',
                        'apiChangePass',
                        'apiRequestResetPass',
                        'apiResetPass'
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
                    'create',
                    'update',
                    'delete',
                    'adminOauth',
                    'createOauth',
                    'updateOauth',
                    'getGeneratedClientSecret',
                    'changeStatus'
                ),
                'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types)',
            ),
            array('allow',
                'actions'=>array(
                    'manageClientUsers',
                    'createClientUser',
                    'updateClientUser'
                ),
                'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types) || in_array("Dash User Admin",$user->types)',
            ),
            array('allow',
                'actions'=>array(
                    'view',
                    'admin'
                ),
                'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types) || in_array("Manager",$user->types)',
            ),
            array('allow',
                'actions'=>array(
                    'adminEngineUsers',
                    'createEngineUser',
                    'updateEngineUser'
                ),
                'users'=>array('@'),
                'expression' => 'in_array("Admin",$user->types) || in_array("Engine Manager",$user->types)',
            ),
            array('allow',
                'actions'=>array(
                    'apiGetUsersByClient',
                    'apiGetUserByID',
                    'apiGetIsUsernameUnique',
                    'apiCreateUser',
                    'apiUpdateUser',
                    'apiLogin',
                    'apiChangePass',
                    'apiResetPass',
                    'apiRequestResetPass',
                    'requestResetPass',
                    'resetPass',
                    'apiGetClientsDropdownByClientId',
					'apiGetCountryLists'
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

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new User('search');
        $model->unsetAttributes();
        if (isset($_GET['User']))
        {
            $model->attributes=$_GET['User'];
        }
        $this->render('admin',array(
            'model' => $model,
        ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $model=$this->loadModel($id);
        $this->render('view',array(
            'model'=>$model,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model=new User;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['User'])) {
            $userAttr = $_POST['User'];
            $apipass = $userAttr['password'];
            $userAttr['salt'] = $model->generateSalt();
            $userAttr['password'] = $model->hashPassword($userAttr['password'], $userAttr['salt']);
            $userAttr['type'] = implode(",", $userAttr['type']);
            $model->attributes = $userAttr;

            //Geo Locations
            if(isset($userAttr['user_geo']))
            {
                foreach($userAttr['user_geo'] as $state)
                {
                    $userGeo=new UserGeo();
                    $userGeo->user_id = $model->id;
                    $userGeo->geo_location = $state;
                    $userGeo->save();
                }
            }

            //User Clients
            if(isset($userAttr['user_clients']))
            {
                foreach($userAttr['user_clients'] as $clientID)
                {
                    $userClient = new UserClient();
                    $userClient->user_id = $model->id;
                    $userClient->client_id = $clientID;
                    $userClient->save();
                }
            }

            if ($model->save())
            {
                /*
                Call wdsCreatePritiniUser API from component page WDSAPIG2
                */
                $LastInsertId = Yii::app()->db->getLastInsertId();
                $provectus = new WDSAPIG2();
                $guid = $provectus->wdsCreatePritiniUser($model, $apipass, $LastInsertId);

                //User Roles (have to do this after model save so there is an id to work with)
                if(isset($_POST['user_roles']))
                {
                    //loop through each availible role
                    $allRoles = array_keys(Yii::app()->authManager->getRoles());
                    foreach($allRoles as $role)
                    {
                        //if in selected array of roles and not already assigned to the role, then assign it
                        if(in_array($role, $_POST['user_roles']) && !Yii::app()->authManager->isAssigned($role, $model->id))
                        {
                            Yii::app()->authManager->assign($role, $model->id);
                        }
                        //if already assigned to the role but not in selected array then revoke it
                        else if(!in_array($role, $_POST['user_roles']) && Yii::app()->authManager->isAssigned($role, $model->id))
                        {
                            Yii::app()->authManager->revoke($role, $model->id);
                        }
                    }
                }

                Yii::app()->user->setFlash('success', "User created!");
                $this->redirect(array('admin'));
            }
            else
                Yii::app()->user->setFlash('error', "Error creating user!");

        }

        $model->active = 1;

        $this->render('create',array(
                'model'=>$model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model=$this->loadModel($id);

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['User'])) {
            $userAttr = $_POST['User'];
            $apipass = $userAttr['password'];
            if ($userAttr['password'] !== '')
                $userAttr['password'] = $model->hashPassword($userAttr['password'], $model->salt);
            else
                $userAttr['password'] = $model->password;
			
			$country_id = $userAttr['country_id'];
			if ($country_id != '')
			{
						$country = CountryCode::model()->find(array('condition'=>"id = ".$country_id));
						$model ->MFACountryCode = $country['country_code'];
						$model->save();
			}

            $userAttr['type'] = implode(",", $userAttr['type']);
            $model->attributes = $userAttr;

            //Geo Locations
            if(isset($userAttr['user_geo']))
            {
                UserGeo::model()->deleteAllByAttributes(array('user_id' => $model->id));
                foreach($userAttr['user_geo'] as $state)
                {
                    $userGeo=new UserGeo();
                    $userGeo->user_id = $model->id;
                    $userGeo->geo_location = $state;
                    $userGeo->save();
                }
            }

            //User Clients
            if(isset($userAttr['user_clients']))
            {
                UserClient::model()->deleteAllByAttributes(array('user_id' => $model->id));
                foreach($userAttr['user_clients'] as $clientID)
                {
                    $userClient = new UserClient();
                    $userClient->user_id = $model->id;
                    $userClient->client_id = $clientID;
                    if(!$userClient->save())
                        Yii::app()->user->setFlash('error', "Error saving User Clients! Details: ".var_export($userClient->getErrors(),true));
                }
            }

            //User Roles
            if(isset($_POST['user_roles']))
            {
                //loop through each availible role
                $allRoles = array_keys(Yii::app()->authManager->getRoles());
                foreach($allRoles as $role)
                {
                    //if in selected array of roles and not already assigned to the role, then assign it
                    if(in_array($role, $_POST['user_roles']) && !Yii::app()->authManager->isAssigned($role, $model->id))
                    {
                        Yii::app()->authManager->assign($role, $model->id);
                    }
                    //if already assigned to the role but not in selected array then revoke it
                    else if(!in_array($role, $_POST['user_roles']) && Yii::app()->authManager->isAssigned($role, $model->id))
                    {
                        Yii::app()->authManager->revoke($role, $model->id);
                    }
                }
            }

            if ($model->save()) {

                /*
                Call wdsUpatePristiniUser API from component page WDSAPIG2
                */
                $provectus = new WDSAPIG2();
                $result = $provectus->wdsUpatePristiniUser($model , $apipass);

                Yii::app()->user->setFlash('success', "User updated!");
                $this->redirect(array('admin'));
            }
            else
                Yii::app()->user->setFlash('error', "Error updating user!");

        }

        $model->password = '';
        $this->render('update',array(
                'model'=>$model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Manages all oauth models.
     */
    public function actionAdminOauth()
    {
        $model = new User('searchOauth');
        $model->unsetAttributes();
        if (isset($_GET['User']))
            $model->attributes=$_GET['User'];

        $this->render('admin_oauth',array(
            'model' => $model,
        ));
    }

    /**
     * Creates a new oauth2 user.
     */
    public function actionCreateOauth()
    {
        $model = new User;
        $model->scenario = 'oauth';

        if (isset($_POST['User']))
        {
            $model->attributes = $_POST['User'];
            $model->type = $model->type === null ? array() : $model->type;
            $model->type = implode(',', $model->type);
            
            if ($model->validate())
            {
                $model->active = 1;
    
                if ($model->save(false))
                {
                    Yii::app()->user->setFlash('success', 'Oauth user, ' . $model->username . ', created successfully.');
                    $this->redirect(array('user/adminOauth'));
                }
            }
        }

        $this->render('create',array(
            'model' => $model
        ));
    }

    /**
     * Updates an existing oauth2 user
     * @param integer id
     */
    public function actionUpdateOauth($id)
    {
        $model = $this->loadModel($id);
        $model->scenario = 'oauth';
        $model->wasActive = $model->active;

        if (isset($_POST['User']))
        {
            $model->type = null;
            $model->attributes = $_POST['User'];
            $model->type = $model->type === null ? array() : $model->type;
            $model->type = implode(',', $model->type);

            if ($model->save())
            {
                Yii::app()->user->setFlash('success', 'Oauth user, ' . $model->username . ', updated successfully.');
                $this->redirect(array('user/adminOauth'));
            }
        }

        $this->render('update',array(
            'model' => $model
        ));
    }

    public function actionGetGeneratedClientSecret()
    {
        echo User::model()->generateOauthClientSecret();
    }

    /*
    * Manage client users
    * Return users that are subset of (dashboard client users)
    */
    public function actionManageClientUsers()
    {
        $model = new User('searchClientUsers');
        $model->unsetAttributes();
        if (isset($_GET['User']))
            $model->attributes=$_GET['User'];

        $pageSize = 25;
        if(isset($_GET['pageSize']))
        {
            $_SESSION['wds_prop_pageSize'] = $_GET['pageSize'];
            $_COOKIE['wds_prop_pageSize'] = $_GET['pageSize'];
            $pageSize = $_GET['pageSize'];
        }
        elseif(isset($_SESSION['wds_prop_pageSize']))
            $pageSize = $_SESSION['wds_prop_pageSize'];
        elseif(isset($_COOKIE['wds_prop_pageSize']))
            $pageSize = $_COOKIE['wds_prop_pageSize'];

        $this->render('manage_client_users',array(
            'model' => $model,
            'pageSize' => $pageSize
        ));
    }

    /*
     * Manage engine users
     *  Return engine users grid
     *  @param $id - int the id of the last created user (if user is returning to grid from a create)
     */
    public function actionAdminEngineUsers($id = null)
    {
        $pageSize = 25;
        $model = new User('searchEngineUsers');
        $model->unsetAttributes();
        if (isset($_GET['User']))
        {
            $model->attributes=$_GET['User'];
        }

        $this->render('admin_engine_users',array(
            'model' => $model,
            'pageSize' => $pageSize,
            'id' => $id
        ));
    }

    /**
     * Creates a Engine User
     * If creation is successful, the browser will be redirected to the admin page.
     */
    public function actionCreateEngineUser()
    {
        $model = new User;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        //Form was submitted, so load and save
        if (isset($_POST['User'])) {
            $userAttr = $_POST['User'];
            $apipass = $userAttr['password'];
            $userAttr['salt'] = $model->generateSalt();
            $userAttr['password'] = $model->hashPassword($userAttr['password'], $userAttr['salt']);
            $userAttr['type'] = implode(",", $userAttr['type']);
            $model->attributes = $userAttr;

            if ($model->save())
            {

                /*
                Call wdsCreatePritiniUser API from component page WDSAPIG2
                */
                $LastInsertId = Yii::app()->db->getLastInsertId();
                $provectus = new WDSAPIG2();
                $guid = $provectus->wdsCreatePritiniUser($model, $apipass, $LastInsertId);

                Yii::app()->user->setFlash('success', "Engine user created!");
                $this->redirect(array('adminEngineUsers', 'id'=>$model->id));
            }
            else
            {
                Yii::app()->user->setFlash('error', "Error creating engine user!");
            }

        }

        //Default values
        $model->active = 1;
        $model->type = "Engine User";

        //Render form
        $this->render('create_engine_user',array(
            'model'=>$model,
        ));
    }

    /**
     * Updates an engine user.
     * If update is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdateEngineUser($id)
    {
        $model = $this->loadModel($id);

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['User'])) {
            $userAttr = $_POST['User'];
            $apipass = $userAttr['password'];
            if ($userAttr['password'] !== '')
            {
                $userAttr['password'] = $model->hashPassword($userAttr['password'], $model->salt);
            }
            else
            {
                $userAttr['password'] = $model->password;
            }

            $userAttr['type'] = implode(",", $userAttr['type']);
            $model->attributes = $userAttr;

            if ($model->save()) {

                /*
                Call wdsUpatePristiniUser API from component page WDSAPIG2
                */
                $provectus = new WDSAPIG2();
                $result = $provectus->wdsUpatePristiniUser($model , $apipass);

                Yii::app()->user->setFlash('success', "User updated!");
                $this->redirect(array('adminEngineUsers'));
            }
            else
                Yii::app()->user->setFlash('error', "Error updating user!");

        }

        $model->password = '';
        $this->render('update_engine_user',array(
                'model'=>$model,
        ));

    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model=User::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * API Method: user/apiChangePass
     * Description: Change password for the given username
     *
     * Post data parameters:
     * @param string username
     * @param string password
     * @param string new_password
     *
     * Post data example:
     * {
     *     "data": {
     *         "username" : "jdoe",
     *         "password" : "jdoe has the best password ever",
     *         "new_password": "jdoe has the new best password ever"
     *     }
     * }
     */
    public function actionApiChangePass()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('username', 'password', 'new_password')))
            return;

        // Check username and current password pair.
        $user = User::model()->findByAttributes(array('username'=>$data['username']));
        if($user === NULL)
        {
            return WDSAPI::echoJsonError('ERROR: Username does not exist.', 'Username does not exist.', 1);
        }
        else //found username
        {
            if(!$user->validatePassword($data['password']))
            {
                return WDSAPI::echoJsonError('ERROR: current password was incorrect.', 'Current password was incorrect.', 2);
            }
            else if($data['new_password'] == $data['password'])
            {
                return WDSAPI::echoJsonError('ERROR: cannot have same as previous password', 'Current password and New password are the same, the new password must be different.', 3);
            }
            else //current username and password correct, so update pw
            {
                if($user->checkPassComplexity($data['new_password']))
                {
                    $user->password = $user->hashPassword($data['new_password'], $user->salt);
                    $user->pw_exp = date('Y-m-d', strtotime('+ 90 days'));
                    if($user->save())
                    {
                        $return_array = array();
                        $return_array['error'] = 0; // success
                        return WDSAPI::echoResultsAsJson($return_array);
                    }
                    else
                    {
                        return WDSAPI::echoJsonError('ERROR: could not update password.', 'Error updating password', 4);
                    }
                }
                else
                {
                    return WDSAPI::echoJsonError('ERROR: password did not meet complexity requirments.', 'Error updating password. New Password must have 8-20 characters, with at least 1 uppercase and lowercase letter, 1 number, and 1 symbol', 5);
                }
            }
        }
    }

    /**
     * API Method: user/apiGetUsersByClient
     * Description: Looks up all users for a given client.
     * By default, no oauth users are returned.  However, if the oauth flag is given,
     * only oauth users will be returned.
     *
     * Post data parameters:
     * @param integer clientID
     * @param integer oauth (optional)
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 1
     *     }
     * }
     *
     * @return array
     * {
     *     "data": [
     *         {
     *             "id": "688",
     *             "name": "Ricky Bobby2",
     *             "username": "rbobby2",
     *             "email": "meiben@wildfire-defense.com",
     *             "types": [
     *                 "Dash Client"
     *             ],
     *             "client_id": "3",
     *             "client_name": "Liberty Mutual",
     *             "active": "1"
     *         }, {
     *             "id": "687",
     *             "name": "Ricky Bobby",
     *             "username": "rbobby",
     *             "email": "meiben@wildfire-defense.com",
     *             "types": [
     *                 "Dash Client",
     *                 "Dash Enrollment",
     *                 "Dash Analytics"
     *             ],
     *             "client_id": "3",
     *             "client_name": "Liberty Mutual",
     *             "active": "1"
     *         }
     *     ],
     *     "error": 0
     * }
     */
    public function actionApiGetUsersByClient()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        $criteria = new CDbCriteria;
        $criteria->addCondition('t.client_id = :clientID');
        $criteria->order = 't.id DESC';
        $criteria->params[':clientID'] = $data['clientID'];
        $criteria->select = array('t.id','t.name','t.username','t.email','t.type','t.client_id','t.client_secret','t.redirect_uri','t.scope','t.active','t.removed');
        $criteria->with = array('client' => array(
            'select' => array('client.id, client.name')
        ));

        if (isset($data['oauth']) && filter_var($data['oauth'], FILTER_VALIDATE_BOOLEAN) === true)
        {
            $criteria->addCondition('client_secret IS NOT NULL'); // Only display oauth users
        }
        else
        {
            $criteria->addCondition('client_secret IS NULL'); // Omit all oauth users
        }

        $users = User::model()->findAll($criteria);

        $return_array = array();
        $return_array['error'] = 0;
        $return_array['data'] = array();

        foreach ($users as $user)
        {
            $return_array['data'][] = array(
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'types' => $user->getSelectedTypes(),
                'client_id' => $user->client_id,
                'client_name' => ($user->client) ? $user->client->name : null,
                'client_secret' => $user->client_secret,
                'redirect_uri' => $user->redirect_uri,
                'scope' => $user->scope,
                'active' => $user->active,
                'removed' => $user->removed
            );
        }

        return WDSAPI::echoResultsAsJson($return_array);
    }

    /**
     * API Method: user/apiGetUserByID
     * Description: Looks up a user by id and returns it.
     * If the oauth flag is given, user will only be returned if they are an oauth user
     *
     * Post data parameters:
     * @param integer id
     * @param integer clientID
     * @param integer oauth (optional)
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 685,
     *         "clientID": 3
     *     }
     * }
     *
     * @return array
     * {
     *     "data": {
     *         "id": "685",
     *         "name": "Test Test",
     *         "email": "libertyBothTest@email.com",
     *         "username": "libertyBothTest",
     *         "types": [
     *             "Dash Client",
     *             "Dash LM All"
     *         ],
     *         "active": "0",
     *         "timezone": "America/Denver",
     *         "user_geo": [
     *             "TX",
     *             "VT"
     *         ]
     *     },
     *     "error": 0
     * }
     */
    public function actionapiGetUserByID()
    {
       $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('id','clientID')))
            return;

        $criteria = new CDbCriteria;
        $criteria->addCondition('t.id = :id');
        $criteria->params[':id'] = $data['id'];
        $criteria->select = array('t.id','t.name','t.email','t.username','t.type','t.client_id','t.active','t.timezone','t.client_secret','t.redirect_uri','t.scope', 't.MFACountryCode', 'MFAPhoneNumber', 't.MFAEmail', 't.country_id');
        $criteria->with = array(
            'user_geo' => array(
                'select' => array('user_geo.geo_location')
            )
        );

        $criteria->with = array('user_clients','user_clients.client');

        if (isset($data['oauth']) && filter_var($data['oauth'], FILTER_VALIDATE_BOOLEAN) === true)
        {
            $criteria->addCondition('client_secret IS NOT NULL'); // Only display oauth users
        }
        else
        {
            $criteria->addCondition('client_secret IS NULL'); // Omit all oauth users
        }

        $user = User::model()->find($criteria);
        $clientsArray = array(); $selectedclientsArray = array();
        if(!empty($user->user_clients))
        {
            foreach($user->user_clients as $clients)
            {
                $selectedclientsArray[$clients->client->id] = $clients->client->name;
            }
        }
        if(isset($data['clientID']))
            $clients = Client::model()->findAll((array('condition' => 'parent_client_id = '.$data['clientID']. ' OR id = '.$data['clientID'], 'select' => array('id','name'))));
        if(!empty($clients))
            {
                foreach($clients as $client)
                {
                    $clientsArray[$client->id] = $client->name;
                }
            }
		if(isset($data['clientID']))
			$clientMFA = Client::model()->find((array('condition' =>'id = '.$data['clientID'])));
		if(!empty($clientMFA))
        {
			$MFAActiveClient = $clientMFA['MFAActiveClient'];
		}


        if (!$user)
            return WDSAPI::echoJsonError('ERROR: There was an error finding this user', 'Does the user id and clientID searched for exist?');

        if ($user->wds_staff == 1)
            return WDSAPI::echoJsonError('ERROR: WDS staff are not allowed to edit their profile through WDSfire!', 'Does the user id and clientID searched for exist?');

        if ($user->client_id != $data['clientID'])
            return WDSAPI::echoJsonError('ERROR: This user is not allowed on this WDSfire!', 'No WDS Staff users are allowed to edit themselves on WDSfire.');

        $user_geo = array();
        if ($user->user_geo)
            $user_geo = array_map(function($model) { return $model->geo_location; }, $user->user_geo);

        $return_array = array(
            'data' => array(
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'types' => $user->getSelectedTypes(),
                'active' => $user->active,
                'timezone' => $user->timezone,
                'user_geo' => $user_geo,
                'client_secret' => $user->client_secret,
                'redirect_uri' => $user->redirect_uri,
                'scope' => $user->scope,
                'clients_dropdown' => $clientsArray,
                'selectedclientsArray' => $selectedclientsArray,
				'MFACountryCode' => $user->MFACountryCode,
				'MFAPhoneNumber' => $user->MFAPhoneNumber,
				'MFAEmail' => $user->MFAEmail,
				'MFAActiveClient' => $MFAActiveClient,
				'country_id' => $user->country_id
            ),
            'error' => 0
        );

        return WDSAPI::echoResultsAsJson($return_array);
    }

    /**
     * API Method: user/apiGetIsUsernameUnique
     * Description: Receives a list of clients dropdown

     * Post data parameters:
     * @param integer clientID
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": "1007",
     *     }
     * }
     *
     * @return array
     * {
     *     "clients_dropdown": array(),
     *     "error": 0
     * }
     */
    public function actionApiGetClientsDropdownByClientId()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        $clientsArray = array();
        if(isset($data['clientID']))
            $clients = Client::model()->findAll((array('condition' => 'parent_client_id = '.$data['clientID']. ' OR id = '.$data['clientID'], 'select' => array('id','name'))));
        if(!empty($clients))
            {
                foreach($clients as $client)
                {
                    $clientsArray[$client->id] = $client->name;
                }
            }

        $return_array = array(
            'clients_dropdown' => $clientsArray,
            'error' => 0
        );

        return WDSAPI::echoResultsAsJson($return_array);
    }

	/**
     * API Method: user/apiGetCountryLists
     * Description: Receives a list of country dropdown
     * @return array
     * {
     *     "country_dropdown": array(),
     *     "error": 0
     * }
     */
    public function actionApiGetCountryLists()
    {
        $data = NULL;

        $countryArray = array();
        $countryLists = CountryCode::model()->findAll(array("condition"=>"sms_enabled =  1", "order" => "country_name"));
        if(!empty($countryLists))
            {
                foreach($countryLists as $country)
                {
                    $countryArray[$country->id]= $country->country_code.' '.$country->country_name;
                }
            }
        $return_array = array(
            'country_dropdown' => $countryArray,
            'error' => 0
        );
        return WDSAPI::echoResultsAsJson($return_array);
    }

    /**
     * API Method: user/apiGetIsUsernameUnique
     * Description: Receives a username and returns false it's unique or
     * true if it already exists
     *
     * Post data parameters:
     * @param string username
     *
     * Post data example:
     * {
     *     "data": {
     *         "username": "jdoe@wildfire-defense.com",
     *     }
     * }
     *
     * @return array
     * {
     *     "data": false,
     *     "error": 0
     * }
     */
    public function actionApiGetIsUsernameUnique()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('username')))
            return;

        $return_array = array();
        $return_array['error'] = 0;

        if (User::model()->exists('username = :username', array(':username' => $data['username'])))
        {
            $return_array['data'] = false;
        }
        else
        {
            $return_array['data'] = true;
        }

        return WDSAPI::echoResultsAsJson($return_array);
    }

    /**
     * API Method: user/apiCreateUser
     * Description: Creates new user.
     *
     * Post data parameters:
     * @param string name
     * @param string username
     * @param string password
     * @param string email
     * @param array types
     * @param integer active
     * @param integer clientID
     * @param string timezone
     * @param array userGeo
     *
     * Post data example:
     * {
     *     "data": {
     *         "name": "name",
     *         "username": "myusername",
     *         "password": "mypassword",
     *         "email" : "myemail",
     *         "active" : 1,
     *         "clientID": 1,
     *         "types": [
     *             "type1",
     *             "type2"
     *         ],
     *         "timezone": "America/Denver",
     *         "userGeo": [
     *             "AK",
     *             "MT",
     *             "CA"
     *         ]
     *     }
     * }
     *
     * @return array
     * {
     *     "data": null,
     *     "error": 0
     * }
     */
    public function actionApiCreateUser()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('name','username','password','email','types','active','clientID','timezone','userGeo','clients_dropdown')))
            return;

        $user = new User();
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->salt = $user->generateSalt();
        $user->password = $user->hashPassword($data['password'], $user->salt);
        $user->email = $data['email'];
        $user->type = implode(',', $data['types']);
        $user->active = $data['active'];
        $user->client_id = $data['clientID'];
        $user->timezone = $data['timezone'];
        $user->pw_exp = date('Y-m-d H:i', strtotime('+3 month'));

        try
        {
            if (!$user->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', var_export($user->getErrors(), true));
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        // UserGeo changes - this could be an empty string if no options were selected.
        if (is_array($data['userGeo']))
        {
            foreach ($data['userGeo'] as $state)
            {
                $userGeo = new UserGeo();
                $userGeo->user_id = $user->id;
                $userGeo->geo_location = $state;
                $userGeo->save();
            }
        }

        // User Clients - this could be an empty string if no options were selected.
        if (is_array($data['clients_dropdown']))
        {
            foreach ($data['clients_dropdown'] as $clientID)
            {
                $userClient = new UserClient();
                $userClient->user_id = $user->id;
                $userClient->client_id = $clientID;
                $userClient->save();
            }
        }

        return WDSAPI::echoResultsAsJson(array(
            'data' => null,
            'error' => 0
        ));
    }

    /**
     * API Method: user/apiUpdateUser
     * Description: Updates user
     *
     * Post data parameters:
     * @param integer id
     * @param string name
     * @param string username
     * @param string email
     * @param array types
     * @param integer active
     * @param string timezone
     * @param array userGeo
     *
     * Post data example:
     * {
     *     "data": {
     *         "id": 123
     *         "name": "name",
     *         "username": "myusername",
     *         "email" : "myemail",
     *         "active": 1,
     *         "types": [
     *             "type1",
     *             "type2"
     *         ],
     *         "timezone": "America/Denver",
     *         "userGeo": [
     *             "AK",
     *             "MT",
     *             "CA"
     *         ]
     *     }
     * }
     *
     * @return array
     * {
     *     "data": null,
     *     "error": 0
     * }
     */
    public function actionApiUpdateUser()
    {
       $data = NULL;

         if (!WDSAPI::getInputDataArray($data, array('id','name','username','email','types','active','timezone', 'userGeo','clients_dropdown', 'MFAPhoneNumber','MFAEmail', 'country_id')))
            return;

        $user = User::model()->findByPk($data['id']);
		$country = CountryCode::model()->find(array('condition'=>"id = ".$data['country_id']));

        if (!$user)
        {
            return WDSAPI::echoJsonError('ERROR: Could not find a user with the given id');
        }
        else
        {
            $dashUserTypes = array(
                'Dash Client',
                'Dash Email Group Non-Dispatch',
                'Dash Email Group Dispatch',
                'Dash Email Group Noteworthy',
                'Dash Enrollment',
                'Dash Caller',
                'Dash Analytics',
                'Dash Post Incident Summary',
                'Dash User Manager',
                'Dash User Assignment',
                'Dash LM All',
                'Dash Risk'
            );

            $adminTypes = explode(',', $user->type);
            $apiTypes = $data['types'];

            // Adding new types in
            foreach ($apiTypes as $apiType)
                if (!in_array($apiType, $adminTypes))
                    $adminTypes[] = $apiType;

            // Removing dash types no longer needed
            // Cross checking with Dash User Types to not accidentally remove any WDSAdmin user types
            foreach ($adminTypes as $index => $adminType)
                if (!in_array($adminType, $apiTypes) && in_array($adminType, $dashUserTypes))
                    unset($adminTypes[$index]);

            $user->name = $data['name'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->type = implode(',', $adminTypes);
            $user->active = $data['active'];
            $user->timezone = $data['timezone'];
			$user->MFACountryCode = $country['country_code'];
            $user->MFAPhoneNumber = $data['MFAPhoneNumber'];
            $user->MFAEmail = $data['MFAEmail'];
			$user->country_id = $data['country_id'];
        }

        try
        {
            if (!$user->save())
                return WDSAPI::echoJsonError('ERROR: There was a database error.', $user->getError());
        }
        catch (CDbException $e)
        {
            return WDSAPI::echoJsonError('ERROR: There was a database error.', $e->errorInfo[2]);
        }

        $geoUser = ($data['userGeo'] === '') ? array() : $data['userGeo'];
        $geoDb = Yii::app()->db->createCommand('SELECT geo_location FROM user_geo WHERE user_id = :user_id')->queryAll(true, array(':user_id' => $user->id));

        // Check to see if use geo perimissions have changed.
        sort($geoUser);
        sort($geoDb);
        if ($geoUser != $geoDb)
        {
            // User geo permissions have changed!!  Write new user geos.
            if (!empty($geoDb))
                UserGeo::model()->deleteAllByAttributes(array('user_id' => $user->id));

            foreach ($geoUser as $state)
            {
                $userGeo = new UserGeo();
                $userGeo->user_id = $user->id;
                $userGeo->geo_location = $state;
                $userGeo->save();
            }
        }

        UserClient::model()->deleteAllByAttributes(array('user_id' => $user->id));

        // User Clients - this could be an empty string if no options were selected.
        if (is_array($data['clients_dropdown']))
        {
            foreach ($data['clients_dropdown'] as $clientID)
            {
                $userClient = new UserClient();
                $userClient->user_id = $user->id;
                $userClient->client_id = $clientID;
                $userClient->save();
            }
        }

        return WDSAPI::echoResultsAsJson(array(
            'data' => null,
            'error' => 0
        ));
    }

    /**
     * API Method: user/apiLogin
     * Description: Receives user credentials and returns login information.
     *
     * Post data parameters:
     * @param string username
     * @param string password
     * @param string auto_login_token (optional)
     * @param int engine_user (optional) 0 or 1
     * @param int dash_user (optional) 0 or 1
     * @param int cid (optional) Client ID
     * @param int crew_id (optional)
     *
     * Post data example:
     * {
     *     "data": {
     *         "username": "myusername",
     *         "password": "mypassword",
     *         "auto_login_token" : "myautologintoken"
     *     }
     * }
     */
    public function actionApiLogin()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('username', 'password')))
            return;

        $auto_login_token = (isset($data['auto_login_token'])) ? $data['auto_login_token'] :  '-9999';

        // Check email and password pair.
        $user = User::model()->findByAttributes(array('username' => $data['username']));

        if ($user === NULL)
        {
            return WDSAPI::echoJsonError('ERROR: username and/or password were incorrect.', 'Username and/or password were incorrect.', 1);
        }
        elseif($user->active == 0)
        {
            return WDSAPI::echoJsonError('ERROR: this user is deactivated.', 'Your account has been deactivated. Please contact your user manager to regain access to the WDSfire dashboard.', 1);
        }
        else //found username
        {
            $user->loginAttempt();

            if($user->checkMaxLoginAttempts())
            {
                return WDSAPI::echoJsonError('ERROR: Too Many Login Attempts', 'Too many login attempts. Your account has been locked for 30 minutes.', 3);
            }
            elseif(!$user->validatePassword($data['password']) && !$user->validateAutoLoginToken($auto_login_token))
            {
                return WDSAPI::echoJsonError('ERROR: username and/or password were incorrect.', 'Username and/or password were incorrect.', 1);
            }
            elseif($user->checkPWExp())
            {
                $user->resetAttempts(); // Don't increment attempt on wrong password
                return WDSAPI::echoJsonError('ERROR: usernames password is expired', 'Password Expired', 2);
            }
            elseif(isset($data['dash_user']) && !$user->checkPublicUserType('dash'))
            {
                return WDSAPI::echoJsonError('ERROR: Usertype is not authorized', 'Usertype is not authorized', 6);
            }
            elseif(isset($data['engine_user']) && !$user->checkPublicUserType('engine'))
            {
                return WDSAPI::echoJsonError('ERROR: Usertype is not authorized', 'Usertype is not authorized', 6);
            }
            elseif($user->checkUserExp())
            {
                return WDSAPI::echoJsonError("ERROR: User acount has expired.",
                                                "We're sorry, your trial use of the WDSfire Basic Dashboard has expired.
                                                Please contact WDS to discuss continued service options"
                                                , 5);
            }
            else
            {
                $user->resetAttempts(); //reset the login attempts on successfull login

                //If autologin than mask the client id that the user is trying to log in as
                if(isset($data['auto_login_token']) && isset($data['cid']))
                {
                    $user->client_id = $data['cid'];
                }

                //If autologin than mask the crew id that the user is trying to log in as
                if(isset($data['auto_login_token']) && isset($data['crew_id']))
                {
                    $crewMember = EngCrewManagement::model()->findByPk($data['crew_id']);
                }

                //setup user_clients details to pass back
                $userClientsArray = array();
                $userClients = UserClient::model()->with('client')->findAllByAttributes(array('user_id' => $user->id));
                foreach($userClients as $userClient)
                {
                    $userClientsArray[] = array('name'=>$userClient->client->name,'logoid'=>$userClient->client->logo_id);
                }

                $return_array = array();
                $return_array['error'] = 0; // success
                
                
                if (isset($data['dash_user']))
                {

                    if($user->UserGUID==NULL || $user->UserGUID=='{00000000-0000-0000-0000-000000000000}')
                    {
                        $apipass = $data['password'];
                        $LastInsertId = $user->id;
                        $provectus = new WDSAPIG2();
                        $guid = $provectus->wdsCreatePritiniUser($user, $apipass, $LastInsertId);
                    }
                    //define an array $mfaArray and passing parameters
                    $mfaArray = array(
                        'UserGUID'=>$user->UserGUID,
                        'MFACountryCode'=> (isset($data['MFACountryCode'])) ? $data['MFACountryCode'] :  '',
                        'MFAPhoneNumber'=> (isset($data['MFAPhoneNumber'])) ? $data['MFAPhoneNumber'] :  '',
                        'MFAEmail'=> (isset($data['MFAEmail'])) ? $data['MFAEmail'] :  '',
                        'MFACodeValue'=>(isset($data['MFACodeValue'])) ? $data['MFACodeValue'] :  '',
                        'MFAMethodDefault'=>(isset($data['MFAMethodDefault'])) ? $data['MFAMethodDefault'] :  $user['MFAMethodDefault'],
                        'resendCode'=>(isset($data['resendCode'])) ? $data['resendCode'] :  false,
                        'noCodeReceived' => (isset($data['noCodeReceived'])) ? $data['noCodeReceived'] :  false,
                        'userModel' => $user
                    );
                    if(!isset($data['auto_login_token'])){  
                    // call getmfaDetails() with $mfaArray array()
                        $mfaReturnArray = $this->getmfaDetails($mfaArray);

                        //Fetch MFAtermscondition from SystemSettings 
                        $SystemSettings=SystemSettings::model()->find();  
                        $MFAtermscondition = $SystemSettings['MFAtermscondition'];

                        $mfaReturnArray = array(
                            'messageScreen' => $mfaReturnArray['messageScreen'],
                            'messageDetails' => $mfaReturnArray['messageDetails'],
                            'MFAMethodDefault' => $user->MFAMethodDefault,
                            'MFAStatus' => $mfaReturnArray['MFAStatus'],
                            'MFAActiveUser'=>$mfaReturnArray['MFAActiveUser'],
                            'MFAtermscondition' => $MFAtermscondition
                            );
                        
                    }else{
                        $mfaReturnArray = array(
                            'messageScreen' => "",
                            'messageDetails' => "",         
                            'MFAStatus' => 0,
                            'MFAActiveUser'=> 0,
                            'MFAtermscondition' => ""
                        );
                    }
                    $return_array['data'] = array(
                        'userGUID' => $user->UserGUID,
                        'name'=>$user->name,
                        'username'=>$user->username,
                        'email'=>$user->email,
                        'types'=>$user->getSelectedTypes(), //array of types since user can be multiple
                        'client_id'=>$user->client_id,
                        'wds_staff'=>$user->wds_staff,
                        'timezone'=>$user->timezone,
                        'client_name'=>($user->client) ? $user->client->name : null,
                        'response_program_name'=>($user->client) ? $user->client->response_program_name : null,
                        'response_disclaimer'=>($user->client) ? $user->client->response_disclaimer : null,
                        'policyholder_label'=>($user->client && !empty($user->client->policyholder_label)) ? $user->client->policyholder_label : 'policyholder',
                        'enrolled_label'=>($user->client && !empty($user->client->enrolled_label)) ? $user->client->enrolled_label : 'enrolled',
                        'not_enrolled_label'=>($user->client && !empty($user->client->not_enrolled_label)) ? $user->client->not_enrolled_label : 'not enrolled',
                        'map_enrolled_color'=>($user->client && !empty($user->client->map_enrolled_color)) ? $user->client->map_enrolled_color : '#3399ff',
                        'map_not_enrolled_color'=>($user->client && !empty($user->client->map_not_enrolled_color)) ? $user->client->map_not_enrolled_color : '#00ccff',
                        'analytics'=>($user->client && !empty($user->client->analytics) && $auto_login_token == '-9999') ? $user->client->analytics : null,
                        'logo_id'=>($user->client && !empty($user->client->logo_id)) ? $user->client->logo_id : null,
                        'wds_fire'=>($user->client) ? $user->client->wds_fire : null,
                        'wds_risk'=>($user->client) ? $user->client->wds_risk : null,
                        'wds_pro'=>($user->client) ? $user->client->wds_pro : null,
                        'call_list'=>($user->client) ? $user->client->call_list : null,
                        'client_call_list'=>($user->client) ? $user->client->client_call_list : null,
                        'dedicated'=>($user->client) ? $user->client->dedicated : null,
                        'unmatched'=>($user->client) ? $user->client->unmatched: null,
                        'enrollment'=>($user->client) ? $user->client->enrollment: null,
                        'api'=>($user->client) ? $user->client->api: null,
                        'wds_education'=>($user->client) ? $user->client->wds_education : null,
                        'user_clients' => $userClientsArray,
                        //stuff for yii 2.0 Dash 3 useridentity
                        'id'=>$user->id,
                        'authKey'=>md5($user->email.$user->id),
                        'messageScreen' => $mfaReturnArray['messageScreen'],
                        'messageDetails' => $mfaReturnArray['messageDetails'],
                        'MFAMethodDefault' => $user->MFAMethodDefault,
                        'MFAStatus' => $mfaReturnArray['MFAStatus'],
                        'MFAActiveUser'=>$mfaReturnArray['MFAActiveUser'],
                        'MFAtermscondition' => $mfaReturnArray['MFAtermscondition']
                    );
                }
                elseif (isset($data['engine_user']))
                {

                    if($user->UserGUID==NULL || $user->UserGUID=='{00000000-0000-0000-0000-000000000000}')
                    {
                        $apipass = $data['password'];
                        $LastInsertId = $user->id;
                        $provectus = new WDSAPIG2();
                        $guid = $provectus->wdsCreatePritiniUser($user , $apipass, $LastInsertId);
                    }
                    //define an array $mfaArray and passing parameters
                    $mfaArray = array(
                        'UserGUID'=>$user->UserGUID,
                        'MFACountryCode'=> (isset($data['MFACountryCode'])) ? $data['MFACountryCode'] :  '',
                        'MFAPhoneNumber'=> (isset($data['MFAPhoneNumber'])) ? $data['MFAPhoneNumber'] :  '',
                        'MFAEmail'=> (isset($data['MFAEmail'])) ? $data['MFAEmail'] :  '',
                        'MFACodeValue'=>(isset($data['MFACodeValue'])) ? $data['MFACodeValue'] :  '',
                        'MFAMethodDefault'=>(isset($data['MFAMethodDefault'])) ? $data['MFAMethodDefault'] :  $user['MFAMethodDefault'],
                        'resendCode'=>(isset($data['resendCode'])) ? $data['resendCode'] :  false,
                        'noCodeReceived' => (isset($data['noCodeReceived'])) ? $data['noCodeReceived'] :  false,
						'MFAChangeDetails' => (isset($data['MFAChangeDetails'])) ? $data['MFAChangeDetails'] :  NULL,
                        'userModel' => $user
                    );
                    if(!isset($data['auto_login_token'])){  
                    // call getmfaDetails() with $mfaArray array()
                        $mfaReturnArray = $this->getmfaDetails($mfaArray);
						//Fetch MFAtermscondition from SystemSettings 
                        $SystemSettings=SystemSettings::model()->find();  
                        $MFAtermscondition = $SystemSettings['MFAtermscondition'];

                        $mfaReturnArray = array(
                            'messageScreen' => $mfaReturnArray['messageScreen'],
                            'messageDetails' => $mfaReturnArray['messageDetails'],
                            'MFAMethodDefault' => $user->MFAMethodDefault,
                            'MFAStatus' => $mfaReturnArray['MFAStatus'],
                            'MFAActiveUser'=>$mfaReturnArray['MFAActiveUser'],
                            'MFAtermscondition' => $MFAtermscondition
                            );
                    }else{
                        $mfaReturnArray = array(
                            'messageScreen' => "",
                            'messageDetails' => "",         
                            'MFAStatus' => 0,
                            'MFAActiveUser'=> 0,
							'MFAtermscondition' => ""
                        );
                    }

                    $return_array['data'] = array(
                        'userGUID' => $user->UserGUID,
                        'name' => isset($crewMember, $crewMember->user) ? $crewMember->user->name : $user->name,
                        'crew_id' => isset($crewMember, $crewMember->user) ? $crewMember->id : (isset($user->crew) ? $user->crew->id : null),
                        'username' => isset($crewMember, $crewMember->user) ?  $crewMember->user->username : $user->username,
                        'email' => isset($crewMember, $crewMember->user) ?  $crewMember->user->email : $user->email,
                        'types' => isset($crewMember, $crewMember->user) ?  $crewMember->user->getSelectedTypes() : $user->getSelectedTypes(),
                        'affiliation' => isset($crewMember, $crewMember->user) ?
                            (isset($crewMember->user->alliance) ? 'alliance' : 'wds') :
                            ($user->wds_staff ? 'wds' : 'alliance'),
                        'alliance_id' => isset($crewMember, $crewMember->user) ? $crewMember->user->alliance_id : $user->alliance_id,
                        'alliance_name' => isset($crewMember, $crewMember->user) ?
                            (isset($crewMember->user->alliance) ? $crewMember->user->alliance->name : null) :
                            (isset($user->alliance) ? $user->alliance->name : null),
                        // yii 2.0 Dash 3 useridentitygit
                        'id' => isset($crewMember, $crewMember->user) ?  $crewMember->user->id : $user->id,
                        'authKey' => isset($crewMember, $crewMember->user) ? md5($crewMember->user->email.$crewMember->user->id) : md5($user->email.$user->id),
                        'messageScreen' => $mfaReturnArray['messageScreen'],
                        'messageDetails' => $mfaReturnArray['messageDetails'],
                        'MFAMethodDefault' => $user->MFAMethodDefault,
                        'MFAStatus' => $mfaReturnArray['MFAStatus'],
                        'MFAActiveUser'=>$mfaReturnArray['MFAActiveUser'],
                        'MFAtermscondition' => $mfaReturnArray['MFAtermscondition']
                    );
                }

                return WDSAPI::echoResultsAsJson($return_array);
            }
        }
    }
    /**
     * Create a function getmfaDetails and pass $mfaData() for fetching MFA details according to conditions.
     * return $return_array 
     */
    public function getmfaDetails($mfaData = array())
    {
        $clientID = Yii::app()->params['defaultClient'];
        if($mfaData['userModel']['client_id'] != NULL)
        {
            $clientID = $mfaData['userModel']['client_id']; 
        }
        //client Message        
        $clientUser = Client::model()->findByAttributes(array('id'=>$clientID));
        $activeClient =  $clientUser['MFAActiveClient'];
        $MFAMessage1 = $clientUser['MFAMessage1'];
        $MFAMessage2 = $clientUser['MFAMessage2'];
        $MFAMessage3 = $clientUser['MFAMessage3'];

        //MFAActiveUser = 0 OR MFAActiveClient = 0 then direct login without MFA conditions
        if(($mfaData['userModel']['MFAActiveUser'] == 0) || ($activeClient == 0))
        {
            $return_array = array(
                'messageScreen' => "",
                'messageDetails' => "",         
                'MFAStatus' => 0,
                'MFAActiveUser'=> 0
            );
        }
        //MFAActiveUser = 1 AND MFAActiveClient = 1 then checking MFA conditions
        if(($mfaData['userModel']['MFAActiveUser'] == 1) && ($activeClient == 1))
        {
            if($mfaData['MFACodeValue'] != NULL)
            {
                //api call wdsApiVerifyMFAURL, send $UserGUID and $code
                $mfa = new WDSAPIG2MFA();
                $result = $mfa->wdsApiVerifyMFAURL($mfaData['UserGUID'], $mfaData['MFACodeValue']);
                if($result)
                {
                    return $return_array = array(
                        'messageScreen' => "",
                        'messageDetails' => "",         
                        'MFAStatus' => 1,
                        'MFAActiveUser'=>1
                    );
                }
                else
                {
                    $return_array = array(
                        'messageScreen' => 3,
                        'messageDetails' => $MFAMessage3,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=>1
                    );
                }
            }
            elseif(($mfaData['userModel']['MFAPhoneNumber'] != NULL) && ($mfaData['userModel']['MFAEmail'] != NULL) && ($mfaData['noCodeReceived'] != 1) && ($mfaData['MFAChangeDetails'] == NULL))
            {
                    //api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
                    $mfa = new WDSAPIG2MFA();
                    $result = $mfa->wdsApiSendMFAURL($mfaData['UserGUID'], $mfaData['MFAMethodDefault']);
                    if($result)
                    {
                        $return_array = array(
                            'messageScreen' => 2,
                            'messageDetails' => $MFAMessage2,           
                            'MFAStatus' => 1,
                            'MFAActiveUser'=>1
                        );
                    }
                    else
                    {
                        $return_array = array(
                            'messageScreen' => 3,
                            'messageDetails' => $MFAMessage3,           
                            'MFAStatus' => 1,
                            'MFAActiveUser'=>1
                        );
                    }
            }
            elseif(($mfaData['MFAPhoneNumber'] != NULL) && ($mfaData['MFAEmail'] != NULL))
            {
                $countryCode = $mfaData['MFACountryCode'];
                $phoneNo = $mfaData['MFAPhoneNumber'];
                $email = $mfaData['MFAEmail'];
                $UserGUID = $mfaData['UserGUID'];
                //update details into User table
                $connection=Yii::app()->db;
                $sql = "UPDATE [user] SET MFAPhoneNumber = '$phoneNo', MFACountryCode = '$countryCode', MFAEmail = '$email'
                WHERE UserGUID = '$UserGUID' ";
                $command = $connection->createCommand($sql);
                $command->execute();
                //api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
                $mfa = new WDSAPIG2MFA();
                $result = $mfa->wdsApiSendMFAURL($mfaData['UserGUID'], $mfaData['MFAMethodDefault']);
                
                if($result)
                {
                    $return_array = array(
                        'messageScreen' => 2,
                        'messageDetails' => $MFAMessage2,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=>1
                    );
                }   
                else
                {
                    $connection=Yii::app()->db;
                    $sql = "UPDATE [user] SET MFACountryCode = NULL, MFAPhoneNumber = NULL , MFAEmail = NULL
                    WHERE UserGUID = '$UserGUID' ";
                    $command = $connection->createCommand($sql);
                    $command->execute();
                    $return_array = array(
                        'messageScreen' => 1,
                        'messageDetails' => $MFAMessage1,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=> 1
                    );
                }
            }
			elseif($mfaData['MFAChangeDetails'] != NULL)
			{
				if($mfaData['MFAChangeDetails'] == 'for_mfa_ph')
				{
					
					//changes mfa details with existing case
					if($mfaData['MFAPhoneNumber'] != NULL)
					{
						$countryId = $mfaData['MFACountryCode'];
						$phoneNo = $mfaData['MFAPhoneNumber'];
						$UserGUID = $mfaData['UserGUID'];

						$countrycodequery = CountryCode::model()->findByAttributes(array('id'=>$countryId));
						$countryCode = $countrycodequery['country_code'];

						//update details into User table
						$connection=Yii::app()->db;
						$sql = "UPDATE [user] SET MFAPhoneNumber = '$phoneNo', MFACountryCode = '$countryCode', country_id = '$countryId' 
						WHERE UserGUID = '$UserGUID' ";
						$command = $connection->createCommand($sql);
						$command->execute();
						//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
						$mfa = new WDSAPIG2MFA();
						$result = $mfa->wdsApiSendMFAURL($mfaData['UserGUID'], $mfaData['MFAMethodDefault']);

						if($result->Success)
						{
							$return_array = array(
								'messageScreen' => 2,
								'messageDetails' => $MFAMessage2,           
								'MFAStatus' => 1,
								'MFAActiveUser'=>1,
								'TwilioOutageMessage' => "",
								'APIOutageMessage' => ""
							);
						}
						elseif($result->Reason == 'twilio')
						{
							$return_array = array(
								'messageScreen' => 4,
								'messageDetails' => "",         
								'MFAStatus' => 1,
								'MFAActiveUser'=> 1,
								'TwilioOutageMessage' => $TwilioOutageMessage,
								'APIOutageMessage' => $APIOutageMessage
							);
						}
						elseif($result->Reason == '404')
						{
							$return_array = array(
								'messageScreen' => 5,
								'messageDetails' => "",         
								'MFAStatus' => 1,
								'MFAActiveUser'=> 1,
								'TwilioOutageMessage' => $TwilioOutageMessage,
								'APIOutageMessage' => $APIOutageMessage
							);
						}
						else
						{
							$return_array = array(
								'messageScreen' => 1,
								'messageDetails' => $MFAMessage2,           
								'MFAStatus' => 1,
								'MFAActiveUser'=>1,
								'TwilioOutageMessage' => "",
								'APIOutageMessage' => ""
							);
						}
						if(!($result->Success))
						{
							$oldPhNo = $mfaData['userModel']['MFAPhoneNumber'] ;
							$oldCountryCode = $mfaData['userModel']['MFACountryCode'];
							
							$connection=Yii::app()->db;
							$sql = "UPDATE [user] SET MFACountryCode = '$oldCountryCode', MFAPhoneNumber = '$oldPhNo'
							WHERE UserGUID = '$UserGUID' ";
							$command = $connection->createCommand($sql);
							$command->execute();
						}
	
					}
				}
				elseif($mfaData['MFAChangeDetails'] == 'for_mfa_email')
				{
					//changes mfa details with existing case
					if($mfaData['MFAEmail'] != NULL)
					{						
						$newEmail = $mfaData['MFAEmail'];
						$UserGUID = $mfaData['UserGUID'];

						//update details into User table
						$connection=Yii::app()->db;
						$sql = "UPDATE [user] SET MFAEmail = '$newEmail' WHERE UserGUID = '$UserGUID' ";
						$command = $connection->createCommand($sql);
						$command->execute();
						//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
						$mfa = new WDSAPIG2MFA();
						$result = $mfa->wdsApiSendMFAURL($mfaData['UserGUID'], $mfaData['MFAMethodDefault']);

						if($result->Success)
						{
							$return_array = array(
								'messageScreen' => 2,
								'messageDetails' => $MFAMessage2,           
								'MFAStatus' => 1,
								'MFAActiveUser'=>1,
								'TwilioOutageMessage' => "",
								'APIOutageMessage' => ""
							);
						}
						elseif($result->Reason == 'twilio')
						{
							$return_array = array(
								'messageScreen' => 4,
								'messageDetails' => "",         
								'MFAStatus' => 1,
								'MFAActiveUser'=> 1,
								'TwilioOutageMessage' => $TwilioOutageMessage,
								'APIOutageMessage' => $APIOutageMessage
							);
						}
						elseif($result->Reason == '404')
						{
							$return_array = array(
								'messageScreen' => 5,
								'messageDetails' => "",         
								'MFAStatus' => 1,
								'MFAActiveUser'=> 1,
								'TwilioOutageMessage' => $TwilioOutageMessage,
								'APIOutageMessage' => $APIOutageMessage
							);
						}
						else
						{
							$return_array = array(
								'messageScreen' => 1,
								'messageDetails' => $MFAMessage2,           
								'MFAStatus' => 1,
								'MFAActiveUser'=>1,
								'TwilioOutageMessage' => "",
								'APIOutageMessage' => ""
							);
						}
						if(!($result->Success))
						{
							$oldEmail = $mfaData['userModel']['MFAEmail'];
							
							$connection=Yii::app()->db;
							$sql = "UPDATE [user] SET MFAEmail = '$oldEmail' WHERE UserGUID = '$UserGUID' ";
							$command = $connection->createCommand($sql);
							$command->execute();
						}
					}
				}
				elseif($mfaData['MFAChangeDetails'] == 'for_mfa_ph_onetime')
				{
					$countryId = $mfaData['MFACountryCode'];
					$MFAPhoneNumber = $mfaData['MFAPhoneNumber'];
					$UserGUID = $mfaData['UserGUID'];
					//fetch country code
					$countrycodequery = CountryCode::model()->findByAttributes(array('id'=>$countryId));
					$MFACountryCode = $countrycodequery['country_code'];
		
					//api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
						$mfa = new WDSAPIG2MFA();
						$result = $mfa->wdsApiSendMFAURL($mfaData['UserGUID'], $mfaData['MFAMethodDefault'], $MFACountryCode, $MFAPhoneNumber);

						if($result->Success)
						{
							$return_array = array(
								'messageScreen' => 2,
								'messageDetails' => $MFAMessage2,           
								'MFAStatus' => 1,
								'MFAActiveUser'=>1,
								'TwilioOutageMessage' => "",
								'APIOutageMessage' => ""
							);
						}
						elseif($result->Reason == 'twilio')
						{
							$return_array = array(
								'messageScreen' => 4,
								'messageDetails' => "",         
								'MFAStatus' => 1,
								'MFAActiveUser'=> 1,
								'TwilioOutageMessage' => $TwilioOutageMessage,
								'APIOutageMessage' => $APIOutageMessage
							);
						}
						elseif($result->Reason == '404')
						{
							$return_array = array(
								'messageScreen' => 5,
								'messageDetails' => "",         
								'MFAStatus' => 1,
								'MFAActiveUser'=> 1,
								'TwilioOutageMessage' => $TwilioOutageMessage,
								'APIOutageMessage' => $APIOutageMessage
							);
						}
						else
						{
							$return_array = array(
							'messageScreen' => 1,
							'messageDetails' => $MFAMessage1,           
							'MFAStatus' => 1,
							'MFAActiveUser'=> 1,
							'TwilioOutageMessage' => "",
							'APIOutageMessage' => ""
						);
					}
				}
			}
            elseif($mfaData['resendCode'] == 1)
            {
                //api call wdsApiSendMFAURL, send $UserGUID and $MFAMethodDefault
                $mfa = new WDSAPIG2MFA();
                $result = $mfa->wdsApiSendMFAURL($mfaData['UserGUID'], $mfaData['MFAMethodDefault']);
                if($result)
                {
                    $return_array = array(
                        'messageScreen' => 2,
                        'messageDetails' => $MFAMessage2,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=>1
                    );
                }
                else
                {
                    $return_array = array(
                        'messageScreen' => 3,
                        'messageDetails' => $MFAMessage3,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=>1
                    );
                }
            }
            elseif($mfaData['noCodeReceived'] == 1)
            {
                $return_array = array(
                        'messageScreen' => 3,
                        'messageDetails' => $MFAMessage3,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=>1
                    );
            }
            else
            {
                if($mfaData['userModel']['MFAPhoneNumber'] == NULL && $mfaData['userModel']['MFAActiveUser'] == 1)
                {
                    $return_array = array(
                        'messageScreen' => 1,
                        'messageDetails' => $MFAMessage1,           
                        'MFAStatus' => 1,
                        'MFAActiveUser'=> 1
                    );
                }
                else
                {
                    $return_array = array(
                        'messageScreen' => "",
                        'messageDetails' => "",         
                        'MFAStatus' => 0,
                        'MFAActiveUser'=> 0
                    );
                }
            }
        }
        return $return_array;
    }
    /**
     * API Method: user/apiRequestResetPass
     * Description: Looks up a given user name and sends an email to them with a reset link
     *              reset link is WDS Admin user/resetPassword page with a query param 'reset_token'
     *              which is set equal to a concat of 5 radom letters and the current timestamp.
     *              Optional param can be passed in for which login page to return to once pw is reset.
     *
     * Post data parameters:
     * @param string username - username to request reset
     * @param string return_url - Optional, defaults to WDS Admin login. url they need to return to after a succefuly reset (i.e. some dashboard login page)
     *
     * Post data example:
     * {
     *     "data": {
     *         "username": "someone",
     *         "return_url": "http://dev.wildfire-defense.com",
     *         "wds_fire": 1
     *     }
     * }
     */
    public function actionApiRequestResetPass()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('username')))
            return;

        $result = User::model()->requestResetPass($data);
        return WDSAPI::echoResultsAsJson($result);
    }

    /**
     * API Method: user/apiResetPass
     * Description: Looks up a given user name and validates the reset token for it
     *              and if valid then sets the password to the given new password
     *
     * Post data parameters:
     * @param string reset_token - token from query param of reset link
     * @param string return_url - where to go on success, from query param of reset link, need to pass it along in the return
     * @param string new_password - password to set if reset token is valid
     *
     * Post data example:
     * {
     *     "data": {
     *         "username": "someone",
     *         "reset_token": "12345678910",
     *         "new_password": "tempPass321",
     *         "return_url": "https://dev.wildfire-defense.com/"
     *     }
     * }
     */
    public function actionApiResetPass()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('reset_token', 'return_url', 'new_password')))
            return;

        $result = User::model()->resetPass($data);
        return WDSAPI::echoResultsAsJson($result);
    }

    /**
     * Displays a the request reset password form.
     */
    public function actionRequestResetPass($return_url = null)
    {
        if($return_url === null && isset($_SERVER['HTTP_REFERER']))
            $return_url = $_SERVER['HTTP_REFERER'];

        $error = null;
        $message = 'Please enter your username and an email will be sent to your address on file with reset instructions:';
        if(isset($_POST['username']))
        {
            $data = array('username'=>$_POST['username'], 'return_url'=>$return_url);
            $result = User::model()->requestResetPass($data);
            $error = $result['error'];
            if($error === 0) //success
            {
                $message = '<span style="color:green;font-weight:bold;">Thank You! Please check your Email tied to this account for reset instructions. You can close this window.</span>';
            }
            else //error > 0
            {
                $message = '<span style="color:red">'.$result['errorMessage'].' (ERROR CODE: '.$error.')</span>';
            }
        }
        $this->render('request_reset_pass', array('error'=>$error, 'message'=>$message, 'return_url'=>$return_url));
    }

    /**
     * Displays a the reset password form.
     */
    public function actionResetPass($reset_token, $return_url)
    {
        if(!Yii::app()->user->isGuest && (in_array('Analytics', Yii::app()->user->types)))
        {
            $this->redirect('index.php?r=site/index');            
        }
        else
        {
            $error = null;
            $message = 'Please enter your username and an email will be sent to your address on file with reset instructions:';
            if(isset($_POST['new_pass']))
            {
                if(isset($_POST['new_pass_confirm']) && $_POST['new_pass'] === $_POST['new_pass_confirm'])
                {
                    $data = array('reset_token'=>$reset_token, 'return_url'=>$return_url, 'new_password'=>$_POST['new_pass']);
                    $result = User::model()->resetPass($data);
                    $error = $result['error'];
                    if($error === 0) //success
                    {
                        //check affiliation and redirect url accordingly
                        $user = User::model()->find("reset_token ='"  .$reset_token."'");
                        if($user -> wds_staff == null && $user ->alliance_id == null && $user ->client_id != null)
                        {
                            $return_url = Yii::app()->params['wdsfireBaseUrl'];
                        }
                        $this->redirect($return_url);
                    }
                    else //error > 0
                    {
                        $message = '<span style="color:red">'.$result['errorMessage'].' (ERROR CODE: '.$error.')</span>';
                    }
                }
                else
                {
                    $error = 1;
                    $message = '<span style="color:red">Error: Passwords do not match.</span>';
                }
            }
            $this->render('reset_pass', array('reset_token'=>$reset_token, 'return_url'=>$return_url, 'message'=>$message, 'error'=>$error));
        }
    }

    /**
     * Creates a Client User.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreateClientUser()
    {
        $model=new User;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['User'])) {
            $userAttr = $_POST['User'];
            $apipass = $userAttr['password'];
            $userAttr['salt'] = $model->generateSalt();
            $userAttr['password'] = $model->hashPassword($userAttr['password'], $userAttr['salt']);
            $userAttr['type'] = implode(",", $userAttr['type']);
            $model->attributes = $userAttr;

            //Geo Locations
            if(isset($userAttr['user_geo']))
            {
                foreach($userAttr['user_geo'] as $state)
                {
                    $userGeo=new UserGeo();
                    $userGeo->user_id = $model->id;
                    $userGeo->geo_location = $state;
                    $userGeo->save();
                }
            }

            //User Clients
            if(isset($userAttr['user_clients']))
            {
                foreach($userAttr['user_clients'] as $clientID)
                {
                    $userClient = new UserClient();
                    $userClient->user_id = $model->id;
                    $userClient->client_id = $clientID;
                    $userClient->save();
                }
            }

            if ($model->save())
            {

                /*
                Call wdsCreatePritiniUser API from component page WDSAPIG2
                */
                $LastInsertId = Yii::app()->db->getLastInsertId();
                $provectus = new WDSAPIG2();
                $guid = $provectus->wdsCreatePritiniUser($model, $apipass, $LastInsertId);

                Yii::app()->user->setFlash('success', "Client User created!");
                $this->redirect(array('manageClientUsers'));
            }
            else
                Yii::app()->user->setFlash('error', "Error creating client user!");

        }

        $model->active = 1;

        $this->render('create_client_user',array(
                'model'=>$model,
        ));
    }

    /**
     * Updates a client user.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdateClientUser($id)
    {
        $model=$this->loadModel($id);
        // checking for client  users
        if($model->client_id > 0 && empty($model->api_mode))
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form')
            {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }

            if (isset($_POST['User'])) {
                $userAttr = $_POST['User'];
                $apipass = $userAttr['password'];
                if ($userAttr['password'] !== '')
                    $userAttr['password'] = $model->hashPassword($userAttr['password'], $model->salt);
                else
                    $userAttr['password'] = $model->password;

                $userAttr['type'] = implode(",", $userAttr['type']);
                $model->attributes = $userAttr;

                //Geo Locations
                if(isset($userAttr['user_geo']))
                {
                    UserGeo::model()->deleteAllByAttributes(array('user_id' => $model->id));
                    foreach($userAttr['user_geo'] as $state)
                    {
                        $userGeo=new UserGeo();
                        $userGeo->user_id = $model->id;
                        $userGeo->geo_location = $state;
                        $userGeo->save();
                    }
                }

                //User Clients
                if(isset($userAttr['user_clients']))
                {
                    UserClient::model()->deleteAllByAttributes(array('user_id' => $model->id));
                    foreach($userAttr['user_clients'] as $clientID)
                    {
                        $userClient = new UserClient();
                        $userClient->user_id = $model->id;
                        $userClient->client_id = $clientID;
                        if(!$userClient->save())
                            Yii::app()->user->setFlash('error', "Error saving User Clients! Details: ".var_export($userClient->getErrors(),true));
                    }
                }

                if ($model->save()) {

                    /*
                    Call wdsUpatePristiniUser API from component page WDSAPIG2
                    */
                    $provectus = new WDSAPIG2();
                    $result = $provectus->wdsUpatePristiniUser($model , $apipass);

                    Yii::app()->user->setFlash('success', "User updated!");
                    $this->redirect(array('manageClientUsers'));
                }
                else
                    Yii::app()->user->setFlash('error', "Error updating user!");

            }

            $model->password = '';
            $this->render('update_client_user',array(
                    'model'=>$model,
            ));
        }
        else
        {
            Yii::app()->user->setFlash('error', "Not allowed to update this user!");
            $this->redirect(array('manageClientUsers'));
        }
    }

     /**
     * Change Status for an array of client users entries.
     * @param json data with assigned client user ID.
     *      Example:
     *  {
     *      "data": {
     *          "statusType": 1,
     *          "clientUserIDs": [1, 2, 3]
     *      }
     *  }
     */
    public function actionChangeStatus()
    {
        $data = null;

        if (!WDSAPI::getInputDataArray($data, array('statusType', 'clientUserIDs')))
            return;

        $clientUserIDs = $data['clientUserIDs'];
        $statusType = $data['statusType'];

        if (!is_array($clientUserIDs))
            return WDSAPI::echoJsonError("ERROR: clientUserIDs is not an array!");

        if (count($clientUserIDs) <= 0)
            return WDSAPI::echoJsonError("ERROR: no clientUserIDs were provided!");

        $criteria = new CDbCriteria();
        $criteria->addInCondition('id', $clientUserIDs);

        $models = User::model()->findAll($criteria);

        if ($models)
        {
            foreach($models as $model)
            {
                $model->active = $statusType;
                $model->save();
                if($model->UserGUID)
                {
                    /*
                    Call wdsUpatePristiniUser API from component page WDSAPIG2
                    */
                    $provectus = new WDSAPIG2();
                    $result = $provectus->wdsUpatePristiniUser($model, $apipass = '');
                }
            }
        }

        $returnArray['error'] = 0; // success

        WDSAPI::echoResultsAsJson($returnArray);
    }
    /*
    * param: $data
    * return user type
    */
     public function getDropdownsItems($data)
    {
        $html = '';
        $usertype = explode(',', $data-> type);
        $html .= '<ul>';
        foreach($usertype as $utype)
        {

            $html .= '<li>';
            $html .= $utype;
            $html .= '</li>';
        }
        $html .= '</ul>';
       return $html;
    }
    
}
