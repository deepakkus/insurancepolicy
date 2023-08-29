<?php

class FSAssessmentQuestionController extends Controller
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
                    WDSAPI::SCOPE_FIRESHIELD => array(
                        'apiGetQuestionSet',
                        'apiGetQuestionSet2'
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
                    'update',
                    'create'
                ),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array(
                    'apiGetQuestionSet',
                    'apiGetQuestionSet2'
                ),
				'users'=>array('*')
            ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionApiGetQuestionSet2()
    {
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('loginToken')))
            return;

        $fsUser = FSUser::model()->find("login_token = '".$data['loginToken']."'");
        if (!isset($fsUser))
        {
            return WDSAPI::echoJsonError('ERROR: App User Not Found.', 'Could not find app user based on provided loginToken.', 1);
        }
        else
        {
            if(isset($fsUser->member->client_id))
                $client_id = $fsUser->member->client_id;
            elseif(isset($fsUser->agent->client_id))
                $client_id = $fsUser->agent->client_id;
            else
                return WDSAPI::echoJsonError('ERROR: App User Client Not Found.', 'Could not find a client for the app user that was found based on the loginToken.', 1);
        }

        //$default_client_set = ClientAppQuestionSet::model()->findByAttributes(array('is_default'=>1, 'client_id'=>$client_id));
        //$set_id = $default_client_set->id;
        $set_id = null;
        if(isset($data['setID']))
        {
            if(empty($data['setID'])) //if blank set id passed in, then use default set
            {
                $defaultSet = ClientAppQuestionSet::model()->findByAttributes(array('client_id'=>$client_id, 'is_default'=>1, 'active'=>1));
                $set_id = $defaultSet->id;
            }
            else
                $set_id = $data['setID'];
        }

        $criteria = new CDbCriteria();
        $criteria->condition = "active = 1 AND type = 'field' AND client_id = ".$client_id;
        if(isset($set_id))
          $criteria->condition .= " AND set_id = ".$set_id;
        $criteria->order = 'order_by';
        $questions = FSAssessmentQuestion::model()->findAll($criteria);

        if (!isset($questions) || count($questions) == 0)
        {
            return WDSAPI::echoJsonError("ERROR: questions not found for the given client and/or set_id.");
        }


        $sets = array();
        foreach($questions as $question)
        {
            $clientQuestionSet = ClientAppQuestionSet::model()->findByPk($question->set_id);
            if(!array_key_exists($question->set_id, $sets))//if not already in sets array then need to add it
                $sets[$question->set_id] = array("ID"=>$clientQuestionSet->id, "Name"=>$clientQuestionSet->name, "Sections"=>array());

            if(!array_key_exists($question->section_type, $sets[$question->set_id]["Sections"])) //if not already in sections array then need to add it
                $sets[$question->set_id]["Sections"][$question->section_type] = array('Title'=>$question->getSectionTitle(), 'Order'=>$question->section_type, 'Questions'=>array());

            $sets[$question->set_id]["Sections"][$question->section_type]['Questions'][] = array(
                    'ID'=>$question->id,
                    'Number'=>$question->question_num,
                    'SetID'=>$question->set_id,
                    'Order'=>intval($question->order_by),
                    'Label'=>$question->label,
                    'Title'=>$question->title,
                    'Description'=>utf8_encode($question->description),
                    'QuestionText'=>utf8_encode($question->question_text),
                    'HelpURI' => $question->help_uri,
                    'HelpText' => $question->overlay_image_help_text,
                    'PhotoText' => $question->photo_text,
                    'RequiredPhotos' => $question->number_of_required_photos,
                    'AllowNotes' => $question->allow_notes,
                    'ChoicesType' => $question->choices_type,
                    'Choices' => $question->getChoicesArray(),
            );
        }

        //remove section keys needed for sorting above
        $return_sets = array();
        foreach($sets as $set)
        {
            $return_sections = array();
            foreach($set["Sections"] as $section)
                $return_sections[] = $section;
            $set["Sections"] = array_values($return_sections);
            $return_sets[] = $set;
        }
        $debug = var_export($sets,true);
		$returnArray = array();
        $returnArray['error'] = 0; // Success
        if(isset($set_id))
            $returnArray['data'] = array('Sections'=>$return_sets[0]['Sections']);
        else
            $returnArray['data'] = array('Sets'=>$return_sets);
        WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * Gets the assessment question set for a client.
     * URL Rewrite Rule in IIS: api/fireshield/v2/getQuestionSet
     */
    public function actionApiGetQuestionSet()
	{
        $data = NULL;

        if (!WDSAPI::getInputDataArray($data, array('client')))
            return;

        $client = Client::model()->findByAttributes(array('code' => $data['client']));

        if (!isset($client))
        {
            return WDSAPI::echoJsonError("ERROR: failed to find the client.");
        }

        $questions = Client::model()->getQuestions($client->id);

        if (!isset($questions) || count($questions) == 0)
        {
            return WDSAPI::echoJsonError("ERROR: question set was not found for the given client.");
        }

		$returnArray = array();
        $returnArray['error'] = 0; // Success
        $returnArray['data'] = array('questions'=>$questions);
        WDSAPI::echoResultsAsJson($returnArray);
	}

		    /**
	 * Creates a new Client.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($client_id)
	{
        $question = new FSAssessmentQuestion;

        if(isset($_POST['FSAssessmentQuestion']))
        {
            $question->attributes = $_POST['FSAssessmentQuestion'];
            $error = false;
            if(!empty($_FILES['FSAssessmentQuestion']['tmp_name']['example_image']))
            {
                $uploaded_file = CUploadedFile::getInstance($model, 'example_image');
                $example_image = new File();
                $example_image->name = $uploaded_file->name;
                $example_image->type = $uploaded_file->type;
                $fp = fopen($file->tempName, 'r');
                $content = fread($fp, filesize($file->tempName));
                fclose($fp);
                $example_image->data = $content;
                if($example_image->save())
                {
                    $question->example_image_file_id = $example_image->id;
                }
                else
                {
                    $error = true;
                    Yii::app()->user->setFlash('error', 'There was an Error saving the example image.');
                }
            }

			if(!$error && $question->save())
			{
				Yii::app()->user->setFlash('success', "Question Created Successfully!");
				$this->redirect(array('client/update', 'id'=>$question->client_id));
            }
        }
		else {
			$question->client_id = $client_id;
		}

        $this->render('create',array(
            'question' => $question,
        ));
	}

    /**
	 * Deletes a FSAssessmentQuestion.
	 * If deletion is successful, the browser will be redirected to the 'client/update' page.
	 * @param integer $id the ID of the Client to be deleted
	 */
	public function actionDelete($id)
	{
        if (Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $question = $this->loadModel($id);
			$client_id = $question->client_id;
			$question->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('client/update', 'id'=>$client_id));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

    /**
	 * Updates a FSAssessmentQuestion.
	 */
	public function actionUpdate($id)
	{
        $question = $this->loadModel($id);

        if (isset($_POST['FSAssessmentQuestion']))
        {
            $question->attributes = $_POST['FSAssessmentQuestion'];
            $error = false;
            if(!empty($_FILES['example_image']['name']))
            {
                $uploaded_file = CUploadedFile::getInstanceByName('example_image');
                if(isset($question->example_image_file_id))
                    $file_save_result = File::model()->saveFile($uploaded_file, $question->example_image_file_id);
                else
                    $file_save_result = File::model()->saveFile($uploaded_file);
                if($file_save_result === false)
                {
                    $error = true;
                    Yii::app()->user->setFlash('error', 'There was an Error saving the example image.');
                }
                else
                {
                    $question->example_image_file_id = $file_save_result;
                }
            }
			if (!$error && $question->save())
			{
				Yii::app()->user->setFlash('success', "Question Updated Successfully!");
				$this->redirect(array('client/update', 'id'=>$question->client_id));
			}
        }

        $this->render('update',array(
            'question' => $question,
        ));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	private function loadModel($id)
	{
        $question = FSAssessmentQuestion::model()->findByPk($id);
        if ($question === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $question;
	}
}