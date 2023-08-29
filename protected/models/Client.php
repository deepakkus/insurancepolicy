<?php

/**
 * This is the model class for table "client".
 *
 * The followings are the available columns in table 'client':
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $welcome_screen_url
 * @property string $report_type //'uw', 'edu'
 * @property string $report_logo_url
 * @property string $report_los_structure  //should be json array of form example [{type:"geo", label:"Low Geographic Threat", pts:25}{type:"site", label:"Low Structure Threat", start:0, end:25}, {type:"los", label:"Low Threat", start:0, end:25}, {type:"los", label:"Medium Threat", start:26, end:50}, etc...]
 * @property string $report_stamp_1
 * @property string $report_stamp_2
 * @property string $report_options
 * @property integer $photos_question_num
 * @property float $fra_report_threshold
 * @property float $risk_multiplier
 * @property bool $no_scoring
 * @property bool $response_program_name
 * @property string $response_disclaimer
 * @property string $wds_fire
 * @property string $wds_risk
 * @property string $wds_pro
 * @property string $policyholder_label
 * @property string $enrolled_label
 * @property string $not_enrolled_label
 * @property integer $logo_id
 * @property string $mapbox_layer_id
 * @property integer $analytics
 * @property string $map_enrolled_color
 * @property string $map_not_enrolled_color
 * @property integer $noteworthy_distance
 * @property integer $call_list
 * @property integer $client_call_list
 * @property integer $wds_education
 * @property integer $dedicated
 * @property integer $unmatched
 * @property integer $active
 * @property string $app_contexts
 * @property string $fs_default_download_types
 * @property string $fs_default_email_download_types
 * @property integer $enrollment
 * @property integer $parent_client_id
 * @property integer $api
 * @property integer $property_access_days
 * @property string $business_entity
 * 
 * The followings are the available model relations:
 * @property FsAssessmentQuestion[] $fsAssessmentQuestions
 * @property ClientDedicated[] $clientDedicated
 */
class Client extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'client';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, code', 'required'),
            array('noteworthy_distance', 'numerical', 'integerOnly' => true, 'max' => 50, 'tooBig' => '{attribute} must be 50 miles or less'),
			array('code, report_type, map_enrolled_color, map_not_enrolled_color', 'length', 'max' => 25),
			array('code, report_type', 'length', 'max' => 25),
            array('name', 'length', 'max' => 40),
            array('business_entity', 'length', 'max' => 55),
            array('property_access_days', 'numerical', 'integerOnly'=>true, 'max'=>366, 'min'=>1),
            array('property_access_days', 'length', 'max'=>3),
            array('response_program_name',  'length', 'max' => 100),
            array('response_disclaimer',  'length', 'max' => 700),
            array('policyholder_label, enrolled_label, not_enrolled_label, mapbox_layer_id, analytics',  'length', 'max' => 50),
			array('photos_question_num, no_scoring, wds_fire, wds_risk, wds_pro, logo_id, call_list, client_call_list, wds_education, dedicated, unmatched, enrollment, active, parent_client_id, api', 'numerical', 'integerOnly' => true),
            array('fra_report_threshold, risk_multiplier', 'numerical'),
            array('welcome_screen_url, report_stamp_1, report_stamp_2, report_logo_url', 'length', 'max' => 256),
            array('app_contexts', 'length', 'max' => 500),
			array('report_los_structure, report_options', 'length', 'max' => 2000),
            array('fs_default_download_types, fs_default_email_download_types', 'length', 'max' => 10),
			// The following rule is used by search().
			array('id, name, code, welcome_screen_url, report_logo_url, report_stamp_1, report_stamp_2, report_los_structure, photos_question_num, report_type, fra_report_threshold, risk_multiplier, response_program_name, wds_fire, wds_risk, wds_pro, logo_id, call_list, wds_education, active,app_contexts, fs_default_download_types, fs_default_email_download_types, parent_client_id, api', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'clientStates' => array(self::HAS_MANY, 'Clientstates', 'client_id'),
			'fsAssessmentQuestions' => array(self::HAS_MANY, 'FsAssessmentQuestion', 'client_id'),
			'agents' => array(self::HAS_MANY, 'Agent', 'client_id'),
            'users' => array(self::HAS_MANY, 'User', 'client_id'),
            'resNotice' => array(self::HAS_MANY, 'ResNotice', 'client_id'),
            'file' => array(self::BELONGS_TO, 'File', 'logo_id'),
            'parentClient' => array(self::BELONGS_TO, 'Client', 'parent_client_id'),
            'childClients' => array(self::HAS_MANY, 'Client', 'parent_client_id'),
            'clientDedicated' => array(self::HAS_MANY, 'ClientDedicated', 'client_id'),
		);
	}
    
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
            'code' => 'Code',
            'welcome_screen_url' => 'Welcome Screen URL',
			'report_type' => "Default Report Type",
			'report_logo_url' => 'Report Logo URL',
			'report_stamp_1' => 'Report Stamp 1',
			'report_stamp_2' => 'Report Stamp 2',
			'report_los_structure' => 'Report LOS Structure',
            'report_options' => 'Report Options',
			'photos_question_num' => 'Additional Photos Question #',
            'fra_report_threshold' => 'FRA Report Threshold',
            'risk_multiplier' => 'Risk Multiplier', 
			'no_scoring' => 'Do Not Show Scoring',
            'response_program_name' => 'Response Program Name',
            'response_disclaimer' => 'Response Disclaimer',
            'policyholder_label'=>'Policyholder Label',
            'enrolled_label' => 'Enrolled Label',
            'not_enrolled_label' => 'Not Enrolled Label',
            'wds_fire' => 'WDS Fire (Response)',
            'wds_risk' => 'WDS Risk (Risk rating model)',
            'wds_pro' => 'WDS Pro (App)',
            'logo_id' => 'Logo ID',
            'mapbox_layer_id' => 'Mapbox Layer ID',
            'analytics' => 'Google Analytics ID (Pilot: UA-39673641-15)',
            'map_enrolled_color' => 'Map Enrolled Color',
            'map_not_enrolled_color' => 'Map Not Enrolled Color',
            'noteworthy_distance' => 'Noteworthy Distance',
            'call_list' => 'Call List',
            'client_call_list' => 'Client Call List',
            'wds_education' => 'WDS Education',
            'dedicated' => 'Dedicated',
            'unmatched' => 'Unmatched',
            'enrollment' => 'Enrollment',
            'active'=>'Active',
            'app_contexts'=>'Application Contexts',
            'fs_default_download_types' => 'Default App Download Type(s)',
            'fs_default_email_download_types' => 'Default Email Download Type(s)',
            'parent_client_id' => 'Parent Client',
            'api' => 'API',
            'property_access_days' => 'Property Access Days',
            'business_entity' => 'Business Entity'
		);
	}   

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('code',$this->code,true);
        $criteria->compare('welcome_screen_url',$this->welcome_screen_url,true);
		$criteria->compare('report_type', $this->report_type, true);
		$criteria->compare('report_logo_url', $this->report_logo_url, true);
		$criteria->compare('report_stamp_1', $this->report_stamp_1, true);
		$criteria->compare('report_stamp_2', $this->report_stamp_2, true);
        $criteria->compare('report_options', $this->report_options, true);
		$criteria->compare('report_los_structure', $this->report_los_structure, true);
		$criteria->compare('photos_question_num', $this->photos_question_num);
        $criteria->compare('fra_report_threshold', $this->fra_report_threshold);
        $criteria->compare('risk_multiplier', $this->risk_multiplier);
		$criteria->compare('no_scoring', $this->no_scoring);
        $criteria->compare('response_program_name', $this->response_program_name);
        $criteria->compare('response_disclaimer', $this->response_disclaimer);
        $criteria->compare('policyholder_label', $this->policyholder_label);
        $criteria->compare('enrolled_label', $this->enrolled_label);
        $criteria->compare('not_enrolled_label', $this->not_enrolled_label);
        $criteria->compare('wds_fire', $this->wds_fire);
        $criteria->compare('wds_risk', $this->wds_risk);
        $criteria->compare('wds_pro', $this->wds_pro);
        $criteria->compare('mapbox_layer_id', $this->mapbox_layer_id);
        $criteria->compare('analytics', $this->analytics);
        $criteria->compare('call_list',$this->call_list);
        $criteria->compare('client_call_list',$this->client_call_list);
        $criteria->compare('wds_education',$this->wds_education);
        $criteria->compare('active',$this->active);
        $criteria->compare('app_contexts',$this->app_contexts);
        $criteria->compare('fs_default_download_types',$this->fs_default_download_types);
        $criteria->compare('fs_default_email_download_types',$this->fs_default_email_download_types);
        $criteria->compare('parent_client_id', $this->parent_client_id);
       
        return new WDSCActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=> 20
            ),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Client the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    /**
     * Virtual attribute that returns an array of state names
     * the response client operates in
     * @return mixed
     */
    public function getStateNames()
    {
        $stateNames = array();
        
        foreach ($this->clientStates as $clientState)
            $stateNames[] = $clientState->stateName;
        
        return $stateNames;
    }
    
    /**
     * Gets the question set for a client.
     * @param int $id id of the client
     * @return array
     */
    public function getQuestions($id)
    {
        $questions = array();
        $default_client_set = ClientAppQuestionSet::model()->findByAttributes(array('is_default'=>1, 'client_id'=>$id));
        $criteria = new CDbCriteria();
        $criteria->condition = "active = 1 AND set_id = ".$default_client_set->id." AND type != 'internal' AND client_id = ".$id;
        $criteria->order = 'order_by';
        $data = FSAssessmentQuestion::model()->findAll($criteria);
        
        foreach($data as $question)
        {
            $questions[] = array(
                'question_num' => $question->question_num,
                
                'order_by' => $question->order_by,
                'label' => $question->label,
                'section_type' => $question->section_type,
                'title' => utf8_encode($question->title),
                'question_text' => utf8_encode($question->question_text),
                'overlay_portrait_image_name' => $question->overlay_portrait_image_name,
                'overlay_landscape_image_name' => $question->overlay_landscape_image_name,
                'overlay_image_help_text' => $question->overlay_image_help_text,
                'overlay_image_should_stretch' => $question->overlay_image_should_stretch,
                'number_of_required_photos' => $question->number_of_required_photos,
                'requires_landscape_photo' => $question->requires_landscape_photo,
                'help_uri' => $question->help_uri,
                'launch_camera_on_response_action' => $question->launch_camera_on_response_action,
                'enforce_required_photos_on_response_action' => $question->enforce_required_photos_on_response_action,
                'allow_notes' => $question->allow_notes,
            );
        }
        
        return $questions;
    }

    /**
     * Helper function that Returns an array including all client ids with their name and children as sub arrays.
     * used in User Form for selecting user_clients
     * @return array 
     */
    public function getFullParentChildClientArray()
    {
        $returnArray = array();
        $clients = Client::model()->findAll();
        foreach($clients as $client)
        {
            $returnArray[$client->id] = array('name'=>$client->name, 'children'=>$client->getChildClientIDs());
        }
        return $returnArray;
    }

    /**
     * Helper function that Returns a key,value array of all children (key=>client_id, value=>client_name).
     * @return array
     */
    public function getChildClientIDs()
    {
        $returnArray = array();
        $childClients = Client::model()->findAllByAttributes(array('parent_client_id' => $this->id));
        foreach($childClients as $childClient)
        {
            $returnArray[$childClient->id] = $childClient->name;
        }
        return $returnArray;
    }
    
    public function getMaxAssessmentQuestionNum($id)
    {
        $criteria = new CDbCriteria;
        $criteria->select='MAX(question_num) AS question_num';
        $criteria->condition = "client_id = ".$id;
        $row = FSAssessmentQuestion::model()->find($criteria);
       
        if(!isset($row) || empty($row->question_num)) //no max, so no questions so return 0
            return 0;
        else
            return $row->question_num; 
    }
    
    public function beforeSave()
    {
        if(empty($this->fra_report_threshold))
            $this->fra_report_threshold = 1;
        if(empty($this->risk_multiplier))
            $this->risk_multiplier = 1;
        
        //var_dump($_POST);
        //var_dump($_FILES);
           
        $this->saveLogo('create_logo_id','logo_id');
        
        return parent::beforeSave();
    }
    
    //-----------------------------------------------------Virtual Attributes -------------------------------------------------------------
    #region Virtual Attributes

    /**
     * @return array question set ids and names (good for drop down use)
     */
    public function getAppQuestionSets()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.id, t.name';
        $criteria->condition = 't.client_id = :client_id';
        $criteria->params = array(':client_id' => $this->id);
        $question_sets = ClientAppQuestionSet::model()->findAll($criteria);
        $question_sets_array = array();

        foreach ($question_sets as $question_set)
        {
            $question_sets_array[$question_set->id] = $question_set->name;
        }

        return $question_sets_array;
    }

    /**
     * @return array client ids and names
     */
    public static function getClientNames()
	{
        $criteria = new CDbCriteria();
        $criteria->select = 't.id, t.name';
        $clients = Client::model()->findAll($criteria);
        $clients_array = array();
        
        foreach ($clients as $client)
        {
            $clients_array[$client->id] = $client->name;            
        }

        return $clients_array;
	}
	
	public function getMaxPts($type)
	{
		$los_struct = json_decode($this->report_los_structure);
		if(!isset($los_struct))
			return 'ERROR Parsing the los structure json for the client';
		
		$max_pts = 0;
		foreach($los_struct as $entry)
		{
            if(empty($entry->end_value))
                return 'ERROR in LOS structure for the client, must have type, label, start_value, and end_value for all entries';
			if($entry->end_value > $max_pts && $type == $entry->type)
			{
				$max_pts = $entry->end_value;
			}
		}
		return $max_pts;
	}
    
    /**
     * Virtual attribute for Client Name - retreives the client name for the notice
     */
    
    public function getServiceMask($value)
    {
        if($value)
            return '&#x2713;'; //not sure this is necessary
        else
            return '&#x2716;';
    }

    //gets app contexts and returns them in assoc array format (good for use in dropdown lists)
    public function getApplicationContexts()
    {
        if(!empty($this->app_contexts))
        {
            $app_contexts = json_decode($this->app_contexts);
            foreach($app_contexts as $context)
            {
                if(!empty($context->name))
                    $return_array[$context->name] = $context->name;
            }
            return $return_array;
        }
        else
            return array();
    }
    
    public function getLogoName()
    {
		if(!empty($this->file)){
			return $this->logo_id ? $this->file->name : 'No File';
		}else{
			return 'No File';
		}
    }
    
    //decodes the json object as an assoc array, swaps out template variables (key=>value array that is passed in) and returns it. returns false on error
    public function getReportOptions($template_vars = array())
    {
        $report_options = json_decode($this->report_options, true);
        if(isset($report_options))
        {
            $return_array = array();
            foreach($report_options as $report_option => $option_value)
            {
                $value = $option_value;
                foreach($template_vars as $template_var => $actual_value)
                {
                    $value = str_replace("~$template_var~", $actual_value, $value);     
                }
                $return_array[$report_option] = $value;
            }
            return $return_array;
        }
        else //return false if it is not a proper json structure
            return false;
    }
    
    #endregion
	
    //----------------------------------------------------- General Functions -------------------------------------------------------------
    #region General Functions
    
	public function losHTMLTable($fsReport)
	{
		$los_struct = json_decode($this->report_los_structure);
		if(!isset($los_struct))
			return 'ERROR Parsing the los structure json for the client';
		
		$html = '<style>
				table.los-table {
					border-collapse:collapse;
					border: 2px solid black;
					width: 700px;
					color: black;
				}
				table.los-table th {
					border: 2px solid black;
				}
				table.los-table td {
				
				}
				</style>';
		$html .= '<table class="los-table">';
		$html .= '<tr><th colspan="6" style="background:#1F497D;color:white;">Vulnerability Score Summary</th></tr>';
		$html .= '<tr><th colspan="2">Geo Hazard Risk ('.$this->getMaxPts('geo').' possible)</th>';
		$html .= '<th colspan="2">Site Risk ('.$this->getMaxPts('site').' possible)</th>';
		$html .= '<th colspan="2">Total Wildfire Risk ('.$this->getMaxPts('los').' possible)</th>';
		
		$geo_data = array();
		$site_data = array();
		$los_data = array();
		foreach($los_struct as $entry)
		{
            if(isset($entry->type, $entry->start_value, $entry->end_value, $entry->label))
                $temp_range = '<td>'.$entry->label.'</td><td style="border-right: 2px solid black;">'.$entry->start_value.'-'.$entry->end_value.' pts</td>';
            else
                $temp_range = '<td>ERROR: Client LOS Entry Requires</td><td style="border-right: 2px solid black;">type, label, start_value, end_value</td>';
            
            if($entry->type == 'geo')
				$geo_data[] = $temp_range;
			elseif($entry->type == 'site')
				$site_data[] = $temp_range;
			elseif($entry->type == 'los')
				$los_data[] = $temp_range;
		}
		
		$row_count = max(count($geo_data), count($site_data), count($los_data));
		for ($i = 0; $i < $row_count; $i++) 
		{
			$html .= '<tr>';
			if(isset($geo_data[$i]))
				$html .= $geo_data[$i];
			else
				$html .= '<td></td><td style="border-right: 2px solid black;"></td>';
			if(isset($site_data[$i]))
				$html .= $site_data[$i];
			else
				$html .= '<td></td><td style="border-right: 2px solid black;"></td>';
			if(isset($los_data[$i]))
				$html .= $los_data[$i];
			else
				$html .= '<td></td><td style="border-right: 2px solid black;"></td>';
			$html .= '</tr>';
		}
			
		//totals row
		$html .= '<tr style="border-top: 1px solid black;">';
		//geo
		$geo_risk = 'n/a';
		if(!empty($fsReport->agent_property->geo_risk))
			$geo_risk = $fsReport->agent_property->geo_risk;
		if(!empty($fsReport->geo_risk) && $fsReport->geo_risk > 0)
			$geo_risk = $fsReport->geo_risk;
		$html .= '<td style="border-right:2px solid black;text-align: center" colspan="2"><strong>'.$geo_risk.'</strong></td>';
		//site
		$html .= '<td style="border-right:2px solid black;text-align: center" colspan="2"><strong>'.$fsReport->condition_risk.'</strong></td>';
		//los
		$html .= '<td style="border-right:2px solid black; text-align: center" colspan="2"><strong>'.$fsReport->risk_level.'</strong></td></tr>';

		$html .= '</table>';
		return $html;
	}
	
	public function losPDFHTMLTable($fsReport)
	{   
		$los_struct = json_decode($this->report_los_structure);
		if(!isset($los_struct))
			return 'ERROR Parsing the los structure json for the client';
		
		$html = '<style>
				table.los-table {
					border-collapse:collapse;
					border: 1px solid black;
					width: 200px;
					color: black;
					font-size: 28px;
				}
				</style>';
		$html .= '<table class="los-table"><tbody>';
		
		if(empty($fsReport->no_scoring) || $fsReport->no_scoring == 0)
		{
            //get the data for each type of risk range to be used in below table building
			$geo_data = array();
			$site_data = array();
			$los_data = array();
			foreach($los_struct as $entry)
			{
				if(isset($entry->type, $entry->start_value, $entry->end_value, $entry->label))
					$temp_range = '<tr><td style="text-align:left;" width="70%">'.$entry->label.'</td><td style="border-right: 1px solid black;" width="30%">'.$entry->start_value.'-'.$entry->end_value.' pts</td></tr>';
				else
					$temp_range = '<tr><td>ERROR: Client LOS Entry Requires</td><td style="border-right: 2px solid black;">type, label, start_value, end_value</td></tr>';

				if($entry->type == 'geo')
					$geo_data[] = $temp_range;
				elseif($entry->type == 'site')
					$site_data[] = $temp_range;
				elseif($entry->type == 'los')
					$los_data[] = $temp_range;
			}
            
            //GEO table rows
            if(isset($fsReport->show_geo_risk) && $fsReport->show_geo_risk == 1)
            {
                //header row
                $html .= '<tr><td colspan="2" style="background-color:#1F497D;color:white;text-align:center;font-weight:bold;border-right:1px solid black;">Geo-Based Risk</td></tr>';
                
                //geo total row
                $geo_risk = 'n/a'; //geo default, look up in related property or report itself below if it exists
                if(!empty($fsReport->agent_property->geo_risk))
                    $geo_risk = $fsReport->agent_property->geo_risk;
                if(!empty($fsReport->geo_risk) && $fsReport->geo_risk > 0)
                    $geo_risk = $fsReport->geo_risk;
                
                $html .= '<tr><td style="border:1px solid black;text-align: center;font-weight:bold;font-size:100px;" colspan="2">'.$geo_risk.'</td></tr>';
                
                //spacer row
                $html .= '<tr><td></td><td></td></tr>';
                
                //each range that was gathered above for geo risk levels gets a row
                foreach($geo_data as $data) 
                    $html .= $data;
                
                //spacer row
                $html .= '<tr><td></td><td></td></tr>';
            }
            
            //SITE table rows
            if(isset($fsReport->show_site_risk) && $fsReport->show_site_risk == 1)
            {
                //header row
                $html .= '<tr><td colspan="2" style="background-color:#1F497D;color:white;text-align:center;font-weight:bold;">Site-Based Risk</td></tr>';
                
                //total row
                $html .= '<tr><td style="border:1px solid black;text-align: center;font-weight:bold;font-size:100px;" colspan="2">'.$fsReport->condition_risk.'</td></tr>';
                
                //spacer row
                $html .= '<tr><td></td><td></td></tr>';
                
                //each range that was gathered above for site risk levels gets a row
                foreach($site_data as $data) 
                    $html .= $data;
                
                //spacer row
                $html .= '<tr><td></td><td></td></tr>';
            }
            
            //LOS (Total Risk) table rows
            if(isset($fsReport->show_los_risk) && $fsReport->show_los_risk == 1)
            {
                //header row
                $html .= '<tr><td colspan="2" style="background-color:#1F497D;color:white;text-align:center;font-weight:bold;">Total Wildfire Risk</td></tr>';
                
                //los total row
                $html .= '<tr><td style="border:1px solid black;border-right:1px solid black;text-align: center;font-weight:bold;font-size:100px;" colspan="2">'.$fsReport->risk_level.'</td></tr>';
                
                //spacer row
                $html .= '<tr><td></td><td></td></tr>';
                
                //each range that was gathered above for los risk levels gets a row
                foreach($los_data as $data) 
                    $html .= $data;   
                
                //spacer row
                $html .= '<tr><td></td><td></td></tr>';
            }
		}
			
		$html .= '</tbody></table>';
		
		return $html;
	}
    
    /**
     * Used by the before save to store attachments
     * @param string $propertyNameFile - variable from the form (read as $_FILES)
     * @param string $propertyName - model variable to assign as file_id (read as $_FILES)
     */
    private function saveLogo($propertyNameFile, $propertyName)
    {
        $uploaded_file = CUploadedFile::getInstanceByName($propertyNameFile);

        if ($uploaded_file)
        {
            $image = new ImageResize($uploaded_file->getTempName());

            $image->resizeToHeight(100);
            $image_thumb_temp = dirname($uploaded_file->getTempName()) . DIRECTORY_SEPARATOR . 'thumb_' . $uploaded_file->getName();
            $image->save($image_thumb_temp, IMAGETYPE_PNG);
                
            // Assign images to new objects for save
            $image_thumb = new stdClass();
            $image_thumb->tempName = $image_thumb_temp;
            $image_thumb->name = 'thumb_' . $uploaded_file->getName();
            $image_thumb->type = $uploaded_file->getType();
            
            if (isset($this->$propertyName)) { // if there already exists a file, replace it
                File::model()->saveImageWithThumbnail($uploaded_file, $image_thumb, $this->$propertyName);
            }
            
            else { // new file
                $this->$propertyName = File::model()->saveImageWithThumbnail($uploaded_file, $image_thumb);
            }
            
            // Clean up temp files
            if (isset($image_thumb_temp)) { unlink($image_thumb_temp); }
        }
    }
	
    #endregion
}
