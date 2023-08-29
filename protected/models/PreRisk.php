<?php

/**
 * This is the model class for table "pre_risk".
 *
 * The followings are the available columns in table 'pre_risk':
 * @property integer $id
 * @property integer $property_pid
 * @property double $member_number //old
 * @property string $renewal_date //old
 * @property string $company //old
 * @property string $member_name //old
 * @property string $home_phone //old
 * @property string $work_phone //old
 * @property string $cell_phone //old
 * @property string $street_address //old
 * @property string $city //old
 * @property string $county //old
 * @property string $state //old
 * @property double $zip_code //old
 * @property integer $plus_4 //old
 * @property string $client_email //old
 * @property string $call_attempt_1
 * @property string $time_1
 * @property string $call_attempt_2
 * @property string $time_2
 * @property string $call_attempt_3
 * @property string $time_3
 * @property string $call_attempt_4
 * @property string $time_4
 * @property string $call_attempt_5
 * @property string $time_5
 * @property string $call_attempt_6
 * @property string $time_6
 * @property string $call_attempt_7
 * @property string $time_7
 * @property string $assigned_by
 * @property string $status
 * @property string $wds_callers //old
 * @property string $homeowner_to_be_present
 * @property string $ok_to_do_wo_member_present
 * @property string $authorization_by_affadivit
 * @property string $engine
 * @property string $ha_time
 * @property string $ha_date
 * @property string $contact_date
 * @property string $week_to_schedule
 * @property string $call_list_month
 * @property string $call_list_year
 * @property string $completion_date
 * @property string $call_center_comments
 * @property string $wds_ha_writers
 * @property string $recommended_actions //old
 * @property string $cycle_time_in_days
 * @property string $ha_field_assessor
 * @property string $fire_review
 * @property string $received_date_of_list
 * @property string $assignment_date_start
 * @property string $appointment_information
 * @property integer $follow_up_question_1 //old
 * @property string $question_1_response //old
 * @property integer $follow_up_question_2 //old
 * @property string $question_2_response //old
 * @property integer $follow_up_question_3 //old
 * @property string $question_3_response //old
 * @property integer $follow_up_question_4 //old
 * @property string $question_4_response //old
 * @property string $follow_up_attempt_1
 * @property string $follow_up_time_date_1
 * @property string $follow_up_attempt_2
 * @property string $follow_up_time_date_2
 * @property string $follow_up_attempt_3 //old
 * @property string $follow_up_time_date_3 //old
 * @property string $follow_up_attempt_4 //old
 * @property string $follow_up_time_date_4 //old
 * @property string $follow_up_status
 * @property string $follow_up_month //old
 * @property string $follow_up_year //old
 * @property string $delivery_method
 * @property string $alt_mailing_address
 * @property string $delivery_date
 * @property string $fs_offered
 * @property string $fs_accepted
 * @property string $fs_notes
 * @property string $follow_up_2_question_1 //old
 * @property string $follow_up_2_question_2 //old
 * @property string $follow_up_2_question_3 //old
 * @property string $follow_up_2_question_4 //old
 * @property string $follow_up_2_question_5 //old
 * @property string $follow_up_2_question_6 //old
 * @property string $follow_up_2_question_6a
 * @property string $follow_up_2_question_6b
 * @property string $follow_up_2_question_6c
 * @property string $follow_up_2_question_6d
 * @property string $follow_up_2_question_6e
 * @property string $follow_up_2_question_6f
 * @property integer $follow_up_2_answer_1
 * @property string $follow_up_2_answer_2
 * @property string $follow_up_2_answer_3
 * @property string $follow_up_2_answer_4
 * @property string $follow_up_2_answer_5
 * @property string $follow_up_2_answer_6a
 * @property string $follow_up_2_answer_6b
 * @property string $follow_up_2_answer_6c
 * @property string $follow_up_2_answer_6d
 * @property string $follow_up_2_answer_6e
 * @property string $follow_up_2_answer_6f
 * @property integer $follow_up_2_answer_7
 * @property string $follow_up_2_answer_8
 * @property string $follow_up_2_6a_response
 * @property string $follow_up_2_6b_response
 * @property string $follow_up_2_6c_response
 * @property string $follow_up_2_6d_response
 * @property string $follow_up_2_6e_response
 * @property string $follow_up_2_6f_response
 * @property integer $fs_email_resend
 * @property date $fs_offered_date
 * @property date $point_of_contact
 * @property integer $replace_repair_roof
 * @property integer $clean_roof
 * @property integer $repair_roof_attachment
 * @property integer $screen_openings_vents
 * @property integer $clean_gutters
 * @property integer $clean_enclose_eaves
 * @property integer $replace_windows
 * @property integer $replace_treat_siding
 * @property integer $clean_under_home
 * @property integer $replace_attachment
 * @property integer $clear_veg_0_5
 * @property integer $manage_veg_5_30
 * @property integer $clear_materials_5_30
 * @property integer $manage_veg_30_100
 * @property integer $additional_structure
 */
class PreRisk extends CActiveRecord
{

	/**
	 * @return string the associated database table name
	 */

    public function tableName()
	{
		return 'pre_risk';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('follow_up_2_answer_1, follow_up_2_answer_7, property_pid, fs_email_resend, replace_repair_roof, clean_roof, repair_roof_attachment, screen_openings_vents, clean_gutters, clean_enclose_eaves, replace_windows, replace_treat_siding, clean_under_home, replace_attachment, clear_veg_0_5, manage_veg_5_30, clear_materials_5_30, manage_veg_30_100, additional_structure', 'numerical', 'integerOnly'=>true),
			array('call_attempt_1, wds_ha_writers, ha_field_assessor, follow_up_2_answer_6b, follow_up_2_answer_6c, follow_up_2_answer_6d, follow_up_2_answer_6e, follow_up_2_answer_6f', 'length', 'max'=>20),
			array('ha_time, follow_up_2_6a_response, follow_up_2_6b_response, follow_up_2_6c_response, follow_up_2_6d_response, follow_up_2_6e_response, follow_up_2_6f_response', 'length', 'max'=>15),
			array('time_1, time_2, time_3, time_4, time_6, time_7, follow_up_time_date_1, follow_up_time_date_2, follow_up_status', 'length', 'max'=>35),
			array('call_attempt_2, call_attempt_3, call_attempt_4, call_attempt_5, time_5, call_attempt_6, call_attempt_7, assigned_by, homeowner_to_be_present, ok_to_do_wo_member_present, authorization_by_affadivit, engine, call_list_year, cycle_time_in_days, fire_review, follow_up_attempt_1, follow_up_attempt_2, alt_mailing_address, fs_notes, follow_up_2_answer_3, follow_up_2_answer_6a, follow_up_2_answer_8, follow_up_2_question_6a, follow_up_2_question_6b, follow_up_2_question_6c, follow_up_2_question_6d, follow_up_2_question_6e, follow_up_2_question_6f', 'length', 'max'=>255),
			array('status', 'length', 'max'=>60),
			array('call_list_month', 'length', 'max'=>25),
            array('point_of_contact', 'length', 'max'=>20),
			array('fs_offered, follow_up_2_answer_2, follow_up_2_answer_4', 'length', 'max'=>10),
			array('delivery_method', 'length', 'max'=>75),
			array('fs_accepted', 'length', 'max'=>25),
			array('follow_up_2_answer_5', 'length', 'max'=>200),
			array('ha_date, contact_date, week_to_schedule, completion_date, call_center_comments, recommended_actions, received_date_of_list, assignment_date_start, appointment_information, question_1_response, question_2_response, question_3_response, question_4_response, delivery_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, property_pid, call_attempt_1, time_1, call_attempt_2, time_2, call_attempt_3, time_3, call_attempt_4, time_4, call_attempt_5, time_5, call_attempt_6, time_6, call_attempt_7, time_7, assigned_by, status, homeowner_to_be_present, ok_to_do_wo_member_present, authorization_by_affadivit, engine, ha_time, ha_date, contact_date, week_to_schedule, call_list_month, call_list_year, completion_date, call_center_comments, wds_ha_writers, recommended_actions, cycle_time_in_days, ha_field_assessor, fire_review, received_date_of_list, assignment_date_start, appointment_information, follow_up_attempt_1, follow_up_time_date_1, follow_up_attempt_2, follow_up_time_date_2, follow_up_status, delivery_method, alt_mailing_address, delivery_date, fs_offered, fs_accepted, fs_notes, follow_up_2_answer_1, follow_up_2_answer_2, follow_up_2_answer_3, follow_up_2_answer_4, follow_up_2_answer_5, follow_up_2_answer_6a, follow_up_2_answer_6b, follow_up_2_answer_6c, follow_up_2_answer_6d, follow_up_2_answer_6e, follow_up_2_answer_6f, follow_up_2_answer_7, follow_up_2_answer_8, follow_up_2_question_6a, follow_up_2_question_6b, follow_up_2_question_6c, follow_up_2_question_6d, follow_up_2_question_6e, follow_up_2_question_6f, follow_up_2_6a_response, follow_up_2_6b_response, follow_up_2_6c_response, follow_up_2_6d_response, follow_up_2_6e_response, follow_up_2_6f_response, fs_email_resend, fs_offered_date,
				  member_member_num, member_client, member_email_1, member_email_2, member_salutation, member_first_name, member_middle_name, member_last_name, member_home_phone, member_work_phone, member_cell_phone, member_mail_address_line_1, member_mail_address_line_2, member_mail_city, member_mail_state, member_mail_zip, member_mail_zip_supp,
				  property_rated_company, property_address_line_1, property_address_line_2, property_city, property_state, property_county, property_zip, property_zip_supp, property_policy', //relational attributes
				  'safe', 'on'=>'search'),
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
			'property' => array(self::BELONGS_TO, 'Property', 'property_pid'),
			'member' => array(self::BELONGS_TO, 'Member', '', 'on' => 'property.member_mid = member.mid'),
            'fs_reports' => array(self::HAS_MANY, 'FSReport', 'pre_risk_id'),
		);
	}

	//virtual attributes from related tables
	public $member_member_num;
    public $member_client;
	public $property_rated_company;
	public $member_email_1;
	public $member_email_2;
    public $member_salutation;
	public $member_first_name;
	public $member_middle_name;
	public $member_last_name;
	public $member_home_phone;
	public $member_work_phone;
	public $member_cell_phone;
	public $member_mail_address_line_1;
	public $member_mail_address_line_2;
	public $member_mail_city;
	public $member_mail_state;
	public $member_mail_zip;
	public $member_mail_zip_supp;
	public $property_address_line_1;
	public $property_address_line_2;
	public $property_city;
	public $property_state;
	public $property_county;
	public $property_zip;
	public $property_zip_supp;
    public $property_policy;
    public $property_geo_risk;

	public function getAllRecActions()
	{
		$allRecActions = array();
		$recActionsArray = $this->getRecActions();
		foreach(array_keys($recActionsArray) as $recAction)
		{
			if(isset($this->$recAction) && ($this->$recAction == 'YES' || $this->$recAction == 1 || $this->$recAction == '1'))
				$allRecActions[] = $recActionsArray[$recAction];
		}
		return implode(",<br>", $allRecActions);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'property_pid' => 'Property ID',
			'member_member_num' => 'Member Num',
            'member_client' => 'Client',
            'member_salutation' => 'Salutation',
			'member_first_name' => 'First Name',
			'member_middle_name' => 'Middle Name',
			'member_last_name' => 'Last Name',
			'member_home_phone' => 'Home Phone',
			'member_work_phone' => 'Work Phone',
			'member_cell_phone' => 'Cell Phone',
			'member_mail_address_line_1' => 'Mail Address Ln 1',
			'member_mail_address_line_2' => 'Mail Address Ln 2',
			'member_mail_city' => 'Mail City',
			'member_mail_state' => 'Mail State',
			'member_mail_zip' => 'Mail Zip',
			'member_mail_zip_supp' => 'Mail Zip Supp',
			'member_email_1' => 'Email 1',
			'member_email_2' => 'Email 2',
			'property_rated_company' => 'Company',
			'property_address_line_1' => 'Address Ln 1',
			'property_address_line_2' => 'Address Ln 2',
			'property_city' => 'City',
			'property_state' => 'State',
			'property_zip' => 'Zip',
			'property_zip_supp' => 'Zip Supp',
			'property_county' => 'County',
            'property_policy' => 'Policy #',
            'property_geo_risk' => 'Geo Risk',
			'call_attempt_1' => 'Call Attempt 1',
			'time_1' => 'Time 1',
			'call_attempt_2' => 'Call Attempt 2',
			'time_2' => 'Time 2',
			'call_attempt_3' => 'Call Attempt 3',
			'time_3' => 'Time 3',
			'call_attempt_4' => 'Call Attempt 4',
			'time_4' => 'Time 4',
			'call_attempt_5' => 'Call Attempt 5',
			'time_5' => 'Time 5',
			'call_attempt_6' => 'Call Attempt 6',
			'time_6' => 'Time 6',
			'call_attempt_7' => 'Call Attempt 7',
			'time_7' => 'Time 7',
			'assigned_by' => 'Assigned By',
			'status' => 'Status',
			'wds_callers' => 'WDS Callers',
			'homeowner_to_be_present' => 'Homeowner To Be Present',
			'ok_to_do_wo_member_present' => 'Ok To Do w/o Member Present',
			'authorization_by_affadivit' => 'Authorization By Affadivit',
			'engine' => 'Engine',
			'ha_time' => 'HA Time',
			'ha_date' => 'HA Date',
			'contact_date' => 'Contact Date',
			'week_to_schedule' => 'Week To Schedule',
			'call_list_month' => 'Call List Month',
			'call_list_year' => 'Call List Year',
			'completion_date' => 'Completion Date',
			'call_center_comments' => 'Call Center Comments',
			'wds_ha_writers' => 'Writer',
			'recommended_actions' => 'Legacy Recommended Actions',
			'cycle_time_in_days' => 'Cycle Time (In Days)',
			'ha_field_assessor' => 'HA Field Assessor',
			'fire_review' => 'Fire Review',
			'received_date_of_list' => 'Received Date Of List',
			'assignment_date_start' => 'Assignment Date Start',
			'appointment_information' => 'Appointment Information',
			'follow_up_question_1' => 'Follow Up Question 1',
			'question_1_response' => 'Question 1 Response',
			'follow_up_question_2' => 'Follow Up Question 2',
			'question_2_response' => 'Question 2 Response',
			'follow_up_question_3' => 'Follow Up Question 3',
			'question_3_response' => 'Question 3 Response',
			'follow_up_question_4' => 'Follow Up Question 4',
			'question_4_response' => 'Question 4 Response',
			'follow_up_attempt_1' => 'Follow Up Attempt 1',
			'follow_up_time_date_1' => 'Follow Up Time Date 1',
			'follow_up_attempt_2' => 'Follow Up Attempt 2',
			'follow_up_time_date_2' => 'Follow Up Time Date 2',
			'follow_up_attempt_3' => 'Follow Up Attempt 3',
			'follow_up_time_date_3' => 'Follow Up Time Date 3',
			'follow_up_attempt_4' => 'Follow Up Attempt 4',
			'follow_up_time_date_4' => 'Follow Up Time Date 4',
			'follow_up_status' => 'Follow Up Status',
			'follow_up_month' => 'Follow Up Month',
			'follow_up_year' => 'Follow Up Year',
			'delivery_method' => 'Delivery Method',
			'alt_mailing_address' => 'Alt. Mail/Email Address',
			'delivery_date' => 'Delivery Date',
			'fs_offered' => 'FS Offered',
			'fs_accepted' => 'FS Accepted (Verbally)',
			'fs_notes' => 'FS Notes',
			'follow_up_2_question_1' => 'Follow Up 2 Question 1',
			'follow_up_2_question_2' => 'Follow Up 2 Question 2',
			'follow_up_2_question_3' => 'Follow Up 2 Question 3',
			'follow_up_2_question_4' => 'Follow Up 2 Question 4',
			'follow_up_2_question_5' => 'Follow Up 2 Question 5',
			'follow_up_2_question_6' => 'Follow Up 2 Question 6',
			'follow_up_2_question_6a' => 'A. Condition 1',
			'follow_up_2_question_6b' => 'B. Condition 2',
			'follow_up_2_question_6c' => 'C. Condition 3',
			'follow_up_2_question_6d' => 'D. Condition 4',
			'follow_up_2_question_6e' => 'E. Condition 5',
			'follow_up_2_question_6f' => 'F. Condition 6',
			'follow_up_2_answer_1' => '1. Satisfaction with HA',
			'follow_up_2_answer_2' => '2. Recommend USAA to contiue this program?',
			'follow_up_2_answer_3' => '3. Why USAA should discontinue?',
			'follow_up_2_answer_4' => '4. Taken action recommended in HA?',
			'follow_up_2_answer_5' => '5. Reasons why actions not completed',
			'follow_up_2_answer_6a' => 'Follow Up 2 Answer 6a',
			'follow_up_2_answer_6b' => 'Follow Up 2 Answer 6b',
			'follow_up_2_answer_6c' => 'Follow Up 2 Answer 6c',
			'follow_up_2_answer_6d' => 'Follow Up 2 Answer 6d',
			'follow_up_2_answer_6e' => 'Follow Up 2 Answer 6e',
			'follow_up_2_answer_6f' => 'Follow Up 2 Answer 6f',
			'follow_up_2_answer_7' => '7. Has the HA prepared you to protect your home?',
			'follow_up_2_answer_8' => '8. Recommendations for USAA to improve this program',
			'follow_up_2_6a_response' => 'Follow Up 2 6a Response',
			'follow_up_2_6b_response' => 'Follow Up 2 6b Response',
			'follow_up_2_6c_response' => 'Follow Up 2 6c Response',
			'follow_up_2_6d_response' => 'Follow Up 2 6d Response',
			'follow_up_2_6e_response' => 'Follow Up 2 6e Response',
			'follow_up_2_6f_response' => 'Follow Up 2 6f Response',
            'fs_email_resend' => 'FS Email Resend',
            'fs_offered_date' => 'FS Offered Date',
            'appointmentWithSalutation'=>'Appointment Info',
            'point_of_contact'=>'Point of contact',
			'replace_repair_roof' => 'Replace/repair roof',
			'clean_roof' => 'Clean roof',
			'repair_roof_attachment' => 'Replace/repair skylight/roof attachment',
			'screen_openings_vents' => 'Screen/openings vents',
			'clean_gutters' => 'Clean gutters',
			'clean_enclose_eaves' => 'Clean/enclose eaves',
			'replace_windows' => 'Replace windows',
			'replace_treat_siding' => 'Replace/treat siding',
			'clean_under_home' => 'Clean/enclose underside of home',
			'replace_attachment' => 'Replace/treat attachment(s)',
			'clear_veg_0_5' => 'Clear vegetation/materials in 0-5 ft. zone',
			'manage_veg_5_30' => 'Manage vegetation in 5-30 ft. zone',
			'clear_materials_5_30' => 'Clear materials in 5-30 ft. zone',
			'manage_veg_30_100' => 'Manage vegetation in 30-100 ft. zone',
			'additional_structure' => 'Additional structure(s)',
			'allRecActions' => 'Recommended Actions',
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
	public function search($advSearch = NULL, $pageSize = NULL, $sort = NULL)
	{
		$criteria=new CDbCriteria;

		$criteria->with = array('property', 'member');

        // Advanced search for HA completion date
		if(isset($advSearch['completionDate1']) && isset($advSearch['completionDate2']) && $advSearch['completionDate1'] != '' && $advSearch['completionDate2'] != '')
        {
            $criteria->addBetweenCondition('completion_date', $advSearch['completionDate1'], $advSearch['completionDate2'].' 23:59:59');
        }

        if(isset($advSearch['followUpDate1']) && isset($advSearch['followUpDate2']))
        {
            $criteria->addBetweenCondition('follow_up_time_date_1', $advSearch['followUpDate1'], $advSearch['followUpDate2'].' 23:59:59');
        }

        if (isset($advSearch['haDateBegin']) && isset($advSearch['haDateEnd']))
        {
            $criteria->addBetweenCondition('ha_date', $advSearch['haDateBegin'], $advSearch['haDateEnd'] . ' 23:59:59');
        }

		$criteria->compare('id',$this->id, true);
        $criteria->compare('property_pid',$this->property_pid);

		$criteria->compare('member.member_num', $this->member_member_num, false);
        $criteria->compare('member.client', $this->member_client, true);
        $criteria->compare('member.salutation', $this->member_salutation, true);
		$criteria->compare('member.first_name', $this->member_first_name, true);
		$criteria->compare('member.middle_name', $this->member_middle_name, true);
		$criteria->compare('member.last_name', $this->member_last_name, true);
		$criteria->compare('member.email_1', $this->member_email_1, true);
		$criteria->compare('member.email_2', $this->member_email_2, true);
		$criteria->compare('member.home_phone', $this->member_home_phone, true);
		$criteria->compare('member.work_phone', $this->member_work_phone, true);
		$criteria->compare('member.cell_phone', $this->member_cell_phone, true);
		$criteria->compare('member.mail_address_line_1', $this->member_mail_address_line_1, true);
		$criteria->compare('member.mail_address_line_2', $this->member_mail_address_line_2, true);
		$criteria->compare('member.mail_city', $this->member_mail_city, true);
		$criteria->compare('member.mail_state', $this->member_mail_state, true);
		$criteria->compare('member.mail_zip', $this->member_mail_zip, true);
		$criteria->compare('member.mail_zip_supp', $this->member_mail_zip_supp, true);
		$criteria->compare('property.rated_company', $this->property_rated_company, true);
		$criteria->compare('property.address_line_1', $this->property_address_line_1, true);
		$criteria->compare('property.address_line_2', $this->property_address_line_2, true);
		$criteria->compare('property.city', $this->property_city, true);
		$criteria->compare('property.state', $this->property_state, true);
		$criteria->compare('property.county', $this->property_county, true);
		$criteria->compare('property.zip', $this->property_zip, true);
		$criteria->compare('property.zip_supp', $this->property_zip_supp, true);
        $criteria->compare('property.policy', $this->property_policy, true);
        $criteria->compare('property.geo_risk', $this->property_geo_risk, false);

		$criteria->compare('call_attempt_1',$this->call_attempt_1,true);
		$criteria->compare('time_1',$this->time_1,true);
		$criteria->compare('call_attempt_2',$this->call_attempt_2,true);
		$criteria->compare('time_2',$this->time_2,true);
		$criteria->compare('call_attempt_3',$this->call_attempt_3,true);
		$criteria->compare('time_3',$this->time_3,true);
		$criteria->compare('call_attempt_4',$this->call_attempt_4,true);
		$criteria->compare('time_4',$this->time_4,true);
		$criteria->compare('call_attempt_5',$this->call_attempt_5,true);
		$criteria->compare('time_5',$this->time_5,true);
		$criteria->compare('call_attempt_6',$this->call_attempt_6,true);
		$criteria->compare('time_6',$this->time_6,true);
		$criteria->compare('call_attempt_7',$this->call_attempt_7,true);
		$criteria->compare('time_7',$this->time_7,true);
		$criteria->compare('assigned_by',$this->assigned_by,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('homeowner_to_be_present',$this->homeowner_to_be_present,true);
		$criteria->compare('ok_to_do_wo_member_present',$this->ok_to_do_wo_member_present,true);
		$criteria->compare('authorization_by_affadivit',$this->authorization_by_affadivit,true);
		$criteria->compare('engine',$this->engine,true);
		$criteria->compare('ha_time',$this->ha_time,true);
		$criteria->compare('ha_date',$this->ha_date,true);
		$criteria->compare('contact_date',$this->contact_date,true);
		$criteria->compare('week_to_schedule',$this->week_to_schedule,true);
		$criteria->compare('call_list_month',$this->call_list_month,true);
		$criteria->compare('call_list_year',$this->call_list_year,true);
		$criteria->compare('completion_date',$this->completion_date,true);
		$criteria->compare('call_center_comments',$this->call_center_comments,true);
		$criteria->compare('wds_ha_writers',$this->wds_ha_writers,true);
		$criteria->compare('recommended_actions',$this->recommended_actions,true);
		$criteria->compare('cycle_time_in_days',$this->cycle_time_in_days,true);
		$criteria->compare('ha_field_assessor',$this->ha_field_assessor,true);
		$criteria->compare('fire_review',$this->fire_review,true);
		$criteria->compare('received_date_of_list',$this->received_date_of_list,true);
		$criteria->compare('assignment_date_start',$this->assignment_date_start,true);
		$criteria->compare('appointment_information',$this->appointment_information,true);
		$criteria->compare('follow_up_attempt_1',$this->follow_up_attempt_1,true);
		$criteria->compare('follow_up_time_date_1',$this->follow_up_time_date_1,true);
		$criteria->compare('follow_up_attempt_2',$this->follow_up_attempt_2,true);
		$criteria->compare('follow_up_time_date_2',$this->follow_up_time_date_2,true);
		$criteria->compare('follow_up_status',$this->follow_up_status,true);
		$criteria->compare('delivery_method',$this->delivery_method,true);
		$criteria->compare('alt_mailing_address',$this->alt_mailing_address,true);
		$criteria->compare('delivery_date',$this->delivery_date,true);
		$criteria->compare('fs_offered',$this->fs_offered,true);
		$criteria->compare('fs_accepted',$this->fs_accepted,true);
		$criteria->compare('fs_notes',$this->fs_notes,true);
		$criteria->compare('follow_up_2_answer_1',$this->follow_up_2_answer_1);
		$criteria->compare('follow_up_2_answer_2',$this->follow_up_2_answer_2,true);
		$criteria->compare('follow_up_2_answer_3',$this->follow_up_2_answer_3,true);
		$criteria->compare('follow_up_2_answer_4',$this->follow_up_2_answer_4,true);
		$criteria->compare('follow_up_2_answer_5',$this->follow_up_2_answer_5,true);
		$criteria->compare('follow_up_2_answer_6a',$this->follow_up_2_answer_6a,true);
		$criteria->compare('follow_up_2_answer_6b',$this->follow_up_2_answer_6b,true);
		$criteria->compare('follow_up_2_answer_6c',$this->follow_up_2_answer_6c,true);
		$criteria->compare('follow_up_2_answer_6d',$this->follow_up_2_answer_6d,true);
		$criteria->compare('follow_up_2_answer_6e',$this->follow_up_2_answer_6e,true);
		$criteria->compare('follow_up_2_answer_6f',$this->follow_up_2_answer_6f,true);
		$criteria->compare('follow_up_2_answer_7',$this->follow_up_2_answer_7);
		$criteria->compare('follow_up_2_answer_8',$this->follow_up_2_answer_8,true);
        $criteria->compare('follow_up_2_question_6a',$this->follow_up_2_question_6a,true);
		$criteria->compare('follow_up_2_question_6b',$this->follow_up_2_question_6b,true);
		$criteria->compare('follow_up_2_question_6c',$this->follow_up_2_question_6c,true);
		$criteria->compare('follow_up_2_question_6d',$this->follow_up_2_question_6d,true);
		$criteria->compare('follow_up_2_question_6e',$this->follow_up_2_question_6e,true);
		$criteria->compare('follow_up_2_question_6f',$this->follow_up_2_question_6f,true);
		$criteria->compare('follow_up_2_6a_response',$this->follow_up_2_6a_response,true);
		$criteria->compare('follow_up_2_6b_response',$this->follow_up_2_6b_response,true);
		$criteria->compare('follow_up_2_6c_response',$this->follow_up_2_6c_response,true);
		$criteria->compare('follow_up_2_6d_response',$this->follow_up_2_6d_response,true);
		$criteria->compare('follow_up_2_6e_response',$this->follow_up_2_6e_response,true);
		$criteria->compare('follow_up_2_6f_response',$this->follow_up_2_6f_response,true);
        $criteria->compare('fs_email_resend',$this->fs_email_resend,true);
        $criteria->compare('fs_offered_date',$this->fs_offered_date,true);
        $criteria->compare('point_of_contact',$this->point_of_contact,true);
		$criteria->compare('replace_repair_roof',$this->replace_repair_roof);
		$criteria->compare('clean_roof',$this->clean_roof);
		$criteria->compare('repair_roof_attachment',$this->repair_roof_attachment);
		$criteria->compare('screen_openings_vents',$this->screen_openings_vents);
		$criteria->compare('clean_gutters',$this->clean_gutters);
		$criteria->compare('clean_enclose_eaves',$this->clean_enclose_eaves);
		$criteria->compare('replace_windows',$this->replace_windows);
		$criteria->compare('replace_treat_siding',$this->replace_treat_siding);
		$criteria->compare('clean_under_home',$this->clean_under_home);
		$criteria->compare('replace_attachment',$this->replace_attachment);
		$criteria->compare('clear_veg_0_5',$this->clear_veg_0_5);
		$criteria->compare('manage_veg_5_30',$this->manage_veg_5_30);
		$criteria->compare('clear_materials_5_30',$this->clear_materials_5_30);
		$criteria->compare('manage_veg_30_100',$this->manage_veg_30_100);
		$criteria->compare('additional_structure',$this->additional_structure);

		$condition = '1=1 ';
		if(isset($advSearch['statuses']))
		{
			$in = '(';
			foreach($advSearch['statuses'] as $status)
				$in .= "'".$status."',";
			$in = trim($in, ',').')';
			$condition .= 'AND status IN'.$in;
		}
        $criteria->addCondition($condition);

		if(!isset($sort))
			$sort = 'id';
		$sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

		$dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
			),
			'criteria'=>$criteria,
			//'pagination'=>array('pageSize'=>$pageSize),
		));

		$dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
				'attributes'=>array(
					'member_member_num'=>array(
						'asc'=>'member.member_num',
						'desc'=>'member.member_num DESC',
					),
                    'member_client'=>array(
                        'asc'=>'member.client',
                        'desc'=>'member.client DESC',
                    ),
                    'member_salutation'=>array(
						'asc'=>'member.salutation',
						'desc'=>'member.salutation DESC',
					),
					'member_first_name'=>array(
						'asc'=>'member.first_name ASC',
						'desc'=>'member.first_name DESC',
					),
					'member_middle_name'=>array(
						'asc'=>'member.middle_name ASC',
						'desc'=>'member.middle_name DESC',
					),
					'member_last_name'=>array(
						'asc'=>'member.last_name ASC',
						'desc'=>'member.last_name DESC',
					),
					'member_email_1'=>array(
						'asc'=>'member.email_1',
						'desc'=>'member.email_1 DESC',
					),
					'member_email_2'=>array(
						'asc'=>'member.email_2',
						'desc'=>'member.email_2 DESC',
					),
					'member_home_phone'=>array(
						'asc'=>'member.home_phone',
						'desc'=>'member.home_phone DESC',
					),
					'member_work_phone'=>array(
						'asc'=>'member.work_phone',
						'desc'=>'member.work_phone DESC',
					),
					'member_cell_phone'=>array(
						'asc'=>'member.cell_phone',
						'desc'=>'member.cell_phone DESC',
					),
					'member_mail_address_line_1'=>array(
						'asc'=>'member.mail_address_line_1',
						'desc'=>'member.mail_address_line_1 DESC',
					),
					'member_mail_address_line_2'=>array(
						'asc'=>'member.mail_address_line_2',
						'desc'=>'member.mail_address_line_2 DESC',
					),
					'member_mail_city'=>array(
						'asc'=>'member.mail_city',
						'desc'=>'member.mail_city DESC',
					),
					'member_mail_state'=>array(
						'asc'=>'member.mail_state',
						'desc'=>'member.mail_state DESC',
					),
					'member_mail_zip'=>array(
						'asc'=>'member.mail_zip',
						'desc'=>'member.mail_zip DESC',
					),
					'member_mail_city'=>array(
						'asc'=>'member.mail_zip_supp',
						'desc'=>'member.mail_zip_supp DESC',
					),
					'property_rated_company'=>array(
						'asc'=>'property.rated_company',
						'desc'=>'property.rated_company DESC',
					),
					'property_address_line_1'=>array(
						'asc'=>'property.address_line_1',
						'desc'=>'property.address_line_1 DESC',
					),
					'property_address_line_2'=>array(
						'asc'=>'property.address_line_2',
						'desc'=>'property.address_line_2 DESC',
					),
					'property_city'=>array(
						'asc'=>'property.city',
						'desc'=>'property.city DESC',
					),
					'property_state'=>array(
						'asc'=>'property.state',
						'desc'=>'property.state DESC',
					),
					'property_county'=>array(
						'asc'=>'property.county',
						'desc'=>'property.county DESC',
					),
					'property_zip'=>array(
						'asc'=>'property.zip',
						'desc'=>'property.zip DESC',
					),
					'property_zip_supp'=>array(
						'asc'=>'property.zip_supp',
						'desc'=>'property.zip_supp DESC',
					),
                    'property_policy'=>array(
						'asc'=>'property.policy',
						'desc'=>'property.policy DESC',
					),
                    'property_geo_risk'=>array(
						'asc'=>'property.geo_risk',
						'desc'=>'property.geo_risk DESC',
					),
					'*',
				),
			),
			'criteria'=>$criteria,
		));

		if($pageSize == NULL)
		{
			$dataProvider->pagination = false;
		}
		else
		{
			$dataProvider->pagination->pageSize = $pageSize;
			$dataProvider->pagination->validateCurrentPage = false;
		}

		return $dataProvider;
	}

	//creates a report of the current gridview (all pages)
    public function makeDownloadableReport($columnsToShow, $advSearch, $sort)
    {
        $myFile = Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_PRReport.csv';
		$fh = fopen($myFile, 'w') or die("can't open file");

        //headerrow
        $tempLine = '';
        foreach($columnsToShow as $column)
        {
            $tempLine .= $column.',';
        }
		fwrite($fh, rtrim($tempLine, ',')."\n");

		//loop through all pages in dataprovider so report contains all data rows
		if(empty($sort))
			$sort = 'id';
		$pageSize = 100;
		$dataProvider = $this->search($advSearch, $pageSize);
		$dataRows = $dataProvider->getData(true);
		$pagination = $dataProvider->pagination;
        while ($pagination->currentPage < $pagination->pageCount)
		{
			$dataRows = $dataProvider->getData(true);
			foreach($dataRows as $data)
			{
				$tempLine = '';
				foreach($columnsToShow as $columnToShow)
				{
					if(strpos($columnToShow, 'property_') !== FALSE)
					{
						$prop_attr = str_replace('property_', '', $columnToShow);
						$tempLine .= '"'.str_replace('"', '""', (isset($data->property->$prop_attr) ? $data->property->$prop_attr : "")).'",';
					}
					elseif(strpos($columnToShow, 'member_') !== FALSE && $columnToShow != 'ok_to_do_wo_member_present')
					{
						if($columnToShow == 'member_member_num')
							$mem_attr = 'member_num';
						else
							$mem_attr = str_replace('member_', '', $columnToShow);

						$tempLine .= '"'.str_replace('"', '""', (isset($data->member->$mem_attr) ? $data->member->$mem_attr : "")).'",';
					}
					elseif(in_array($columnToShow, array_keys($this->getRecActions())))
					{
						if(isset($data->$columnToShow) && $data->$columnToShow == 1)
							$tempLine .= 'YES,';
						else
							$tempLine .= 'NO,';
					}
					elseif($columnToShow == 'recommended_actions')
					{
						$tempLine .= str_replace('<br>', "\r\n", '"'.$data->allRecActions.'",');
					}
					else
						$tempLine .= '"'.str_replace('"', '""', (isset($data->$columnToShow) ? $data->$columnToShow : "")).'",';

				}
				fwrite($fh, $tempLine."\n");
			}
			$pagination->currentPage++;
		}
		fclose($fh);
    }

	//Combines all of question 6 sub questions and aswers into 1 field.
    public function getFollowUpAnswer6Combined()
    {
        $mashedQuestions = "";
        if($this->follow_up_2_question_6a)
            $mashedQuestions = $this->follow_up_2_question_6a . ": " . $this->follow_up_2_6a_response;

        if($this->follow_up_2_question_6b)
            $mashedQuestions .= ", " . $this->follow_up_2_question_6b . ": " . $this->follow_up_2_6b_response;

        if($this->follow_up_2_question_6c)
            $mashedQuestions .= ", " . $this->follow_up_2_question_6c . ": " . $this->follow_up_2_6c_response;

        if($this->follow_up_2_question_6d)
            $mashedQuestions .= ", " . $this->follow_up_2_question_6d . ": " . $this->follow_up_2_6d_response;

        if($this->follow_up_2_question_6e)
            $mashedQuestions .= ", " . $this->follow_up_2_question_6e . ": " . $this->follow_up_2_6e_response;

        if($this->follow_up_2_question_6f)
            $mashedQuestions .= ", " . $this->follow_up_2_question_6f . ": " . $this->follow_up_2_6f_response;

        return $mashedQuestions;

    }

    public function getMailingCityStateZip()
    {
        if(!empty($this->property))
            return $this->property->member->mail_city . ", " . $this->property->member->mail_state. " " . $this->property->member->mail_zip;
        else
            return "";

    }

    //Used for the calendar export, which gets imported into google. Won't need this once we get the internal calendar working.
    public function getAppointmentWithSalutation()
    {
        if(!empty($this->appointment_information) && !empty($this->property))
        {
           $appointment = "Salutation: " . $this->property->member->salutation . " " . $this->appointment_information;
        }
        else
            $appointment = "";
        return $appointment;

    }

    /**
     * Writes an .ics calendar import file based on the selected rows in the grid.
     * @param type $advSearch
     * @param type $sort
     */
    public function writeSchedulesICS($advSearch, $sort)
    {
        $eventData = array();
        $pageSize = 10000;
        $dataProvider = $this->search($advSearch, $pageSize, $sort);
		$dataRows = $dataProvider->getData(true);

        foreach($dataRows as $data)
		{
            // The HA Date and Time must be provided.
            if (empty($data->ha_date) || empty($data->ha_time))
            {
                return 'Unable to export calendar. Found member without a HA Date or HA Time set with ID: ' . $data->id;
            }

            $today = new DateTime();
            $today->setTime(0, 0, 0); // Remove the time part.
            $haDate = new DateTime($data->ha_date);

            // Business requirement: skip past appointments.
            if ($haDate < $today)
            {
                continue;
            }

            if (!is_numeric(trim($data->ha_time)))
            {
                // If the time is text (as in "see notes"), just default it to 1:00am.
                $startTime = '01';
            }
            else
            {
                $startTime = $data->ha_time;
            }

            $startDate = str_replace('-', '', $data->ha_date) . 'T' . $startTime . '00';

            if (!is_numeric(trim($data->ha_time)))
            {
                $endTime = '02';
            }
            else
            {
                $endTime = $data->ha_time + 100; // Add an hour
            }

            // Make sure morning times before 10:00 have a leading zero prepended. Fixes bug WA-7!
            if ($endTime < 1000)
            {
                $endTime = '0' . $endTime;
            }

            $endDate = str_replace('-', '', $data->ha_date) . 'T' . $endTime . '00';

            $memberName = trim($data->property->member->first_name);

            if (!empty($data->property->member->middle_name))
                $memberName .= ' ' . trim($data->property->member->middle_name);

            $memberName .= ' ' . trim($data->property->member->last_name);

            $summary = $data->property->city . ', ' . $memberName . ', Home: ' . $data->property->member->home_phone . ', Cell: ' . $data->property->member->cell_phone;
            $location = $data->property->address_line_1.' '.$data->property->address_line_2. ', ' . $data->property->city . ', ' . $data->property->zip;
            $description = '';

            if (!empty($data->id))
            {
                $description .= 'ID = ' . $data->id . '\n';
            }

            if (!empty($data->homeowner_to_be_present))
            {
                $description .= 'Present = ' . $data->homeowner_to_be_present . '\n';
            }

            if (!empty($data->ok_to_do_wo_member_present))
            {
                $description .= 'OK w/o = ' . $data->ok_to_do_wo_member_present . '\n';
            }

            if (!empty($data->property->member->salutation))
            {
                $description .= 'Salutation = ' . $data->property->member->salutation . '\n';
            }

            $description .= $data->appointment_information;

            $eventData[] = array(
                'summary' => $summary,
                'location' => $location,
                'description' => $description,
                'startDate' => $startDate,
                'endDate' => $endDate,
            );
        }

        header("Content-type: text/calendar");
        header("Content-Disposition: inline; filename=schedules.ics");

        echo "BEGIN:VCALENDAR\n";
        echo "VERSION:2.0\n";
        echo "PRODID:-//IceWarp//IceWarp Server 11.0.0.0 x64//EN\n";
        echo "BEGIN:VTIMEZONE\n";
        echo "TZID:America/Boise\n";
        echo "BEGIN:STANDARD\n";
        echo "TZOFFSETFROM:-0600\n";
        echo "TZOFFSETTO:-0700\n";
        echo "TZNAME:MST\n";
        echo "DTSTART:19701101T020000\n";
        echo "RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=11;BYDAY=1SU\n";
        echo "END:STANDARD\n";
        echo "BEGIN:DAYLIGHT\n";
        echo "TZOFFSETFROM:-0700\n";
        echo "TZOFFSETTO:-0600\n";
        echo "TZNAME:MDT\n";
        echo "DTSTART:19700308T020000\n";
        echo "RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=3;BYDAY=2SU\n";
        echo "END:DAYLIGHT\n";
        echo "END:VTIMEZONE\n";

        foreach ($eventData as $event)
        {
            $uid = uniqid();
            $createdDate = '20140314T161550Z';

            echo "BEGIN:VEVENT\n";
            echo "SUMMARY:" . $event['summary'] . "\n";
            echo "LOCATION:" . $event['location'] . "\n";
            echo "DESCRIPTION:" . $event['description'] . "\n";
            echo "UID:$uid\n";
            echo "DTSTART;TZID=America/Boise:" . $event['startDate'] ."\n";
            echo "DTEND;TZID=America/Boise:" . $event['endDate'] . "\n";
            echo "CREATED:$createdDate\n";
            echo "LAST-MODIFIED:$createdDate\n";
            echo "DTSTAMP:$createdDate\n";
            echo "PRIORITY:0\n";
            echo "SEQUENCE:0\n";
            echo "CLASS:PUBLIC\n";
            echo "TRANSP:OPAQUE\n";
            echo "X-MICROSOFT-CDO-BUSYSTATUS:busy\n";
            echo "END:VEVENT\n";
        }

        echo "END:VCALENDAR\n";
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PreRisk the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	protected function afterFind()
	{
        // Convert values to better display values.
		$format = 'Y-m-d';
		if($this->completion_date != null)
	        $this->completion_date = date_format(new DateTime($this->completion_date), $format);
		if($this->ha_date != null)
			$this->ha_date = date_format(new DateTime($this->ha_date), $format);
		if($this->contact_date != null)
			$this->contact_date = date_format(new DateTime($this->contact_date), $format);
		if($this->delivery_date != null)
			$this->delivery_date = date_format(new DateTime($this->delivery_date), $format);
		if($this->week_to_schedule != null)
			$this->week_to_schedule = date_format(new DateTime($this->week_to_schedule), $format);
		if($this->assignment_date_start != null)
			$this->assignment_date_start = date_format(new DateTime($this->assignment_date_start), $format);
		if($this->received_date_of_list != null)
			$this->received_date_of_list = date_format(new DateTime($this->received_date_of_list), $format);
		if($this->follow_up_time_date_1 != null)
			$this->follow_up_time_date_1 = date_format(new DateTime($this->follow_up_time_date_1), $format);
		if($this->follow_up_time_date_2 != null)
			$this->follow_up_time_date_2 = date_format(new DateTime($this->follow_up_time_date_2), $format);
		if($this->time_1 != null)
			$this->time_1 = date_format(new DateTime($this->time_1), $format);
		if($this->time_2 != null)
			$this->time_2 = date_format(new DateTime($this->time_2), $format);
		if($this->time_3 != null)
			$this->time_3 = date_format(new DateTime($this->time_3), $format);
		if($this->time_4 != null)
			$this->time_4 = date_format(new DateTime($this->time_4), $format);
		if($this->time_5 != null)
			$this->time_5 = date_format(new DateTime($this->time_5), $format);
		if($this->time_6 != null)
			$this->time_6 = date_format(new DateTime($this->time_6), $format);
		if($this->time_7 != null)
			$this->time_7 = date_format(new DateTime($this->time_7), $format);

		parent::afterFind();
	}

	public function getRecActions()
	{
		return array(
			'replace_repair_roof' => 'Replace/repair roof',
			'clean_roof' => 'Clean roof',
			'repair_roof_attachment' => 'Replace/repair skylight/roof attachment',
			'screen_openings_vents' => 'Screen/openings vents',
			'clean_gutters' => 'Clean gutters',
			'clean_enclose_eaves' => 'Clean/enclose eaves',
			'replace_windows' => 'Replace windows',
			'replace_treat_siding' => 'Replace/treat siding',
			'clean_under_home' => 'Clean/enclose underside of home',
			'replace_attachment' => 'Replace/treat attachment(s)',
			'clear_veg_0_5' => 'Clear vegetation/materials in 0-5 ft. zone',
			'manage_veg_5_30' => 'Manage vegetation in 5-30 ft. zone',
			'clear_materials_5_30' => 'Clear materials in 5-30 ft. zone',
			'manage_veg_30_100' => 'Manage vegetation in 30-100 ft. zone',
			'additional_structure' => 'Additional structure(s)',
		);
	}

	protected function beforeSave()
	{
		if($this->delivery_date == '')
			$this->delivery_date = null;
		if($this->completion_date == '')
			$this->completion_date = null;
		if($this->ha_date == '')
			$this->ha_date = null;
		if($this->contact_date == '')
			$this->contact_date = null;
		if($this->assignment_date_start == '')
			$this->assignment_date_start = null;
		if($this->received_date_of_list == '')
			$this->received_date_of_list = null;
		if($this->follow_up_time_date_1 == '')
			$this->follow_up_time_date_1 = null;
		if($this->follow_up_time_date_2 == '')
			$this->follow_up_time_date_2 = null;
		if($this->time_1 == '')
			$this->time_1 = null;
		if($this->time_2 == '')
			$this->time_2 = null;
		if($this->time_3 == '')
			$this->time_3 = null;
		if($this->time_4 == '')
			$this->time_4 = null;
		if($this->time_5 == '')
			$this->time_5 = null;
		if($this->time_6 == '')
			$this->time_6 = null;
		if($this->time_7 == '')
			$this->time_7 = null;

        // Automatically set the fs_offered_date (first time only) if the user changes fs_offered to yes.
        if (!$this->isNewRecord && $this->fs_offered_date == null)
		{
            $currentPreRisk = PreRisk::model()->findByPk($this->id);

            if ($currentPreRisk->fs_offered !== 'YES' && $this->fs_offered === 'YES')
                $this->fs_offered_date = date('Y-m-d H:i:s');
        }

		return parent::beforeSave();
	}

	protected function afterSave()
	{
		if($this->status == 'COMPLETED - Delivered to Member' && isset($this->property))
		{
			$this->property->pre_risk_status = 'enrolled';
            $this->property->pr_status_date = date('Y-m-d H:i:s');
            if (isset($this->property->geog))
            {
                $this->property->geog = Yii::app()->db->createCommand('select geog.STAsText() from properties where pid = :pid')
                    ->bindValue(':pid', $this->property->pid)
                    ->queryScalar();
            }
			$this->property->save();
		}
        else if($this->status == 'TO BE SCHEDULED' && isset($this->property) && $this->property->pre_risk_status == 'not enrolled')
        {
            $this->property->pre_risk_status = 'offered';
            $this->property->pr_status_date = date('Y-m-d H:i:s');
            if (isset($this->property->geog))
            {
                $this->property->geog = Yii::app()->db->createCommand('select geog.STAsText() from properties where pid = :pid')
                    ->bindValue(':pid', $this->property->pid)
                    ->queryScalar();
            }
            $this->property->save();
        }
		return parent::afterSave();
	}

	/**
	 * @return array of wds callers with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsCallers($caller)
	{
		$wdsCallers = array(
			'' => '',
            'Matt M.' => 'Matt M.',
			'Alexis W.' => 'Alexis W.',
			'Allison G.' => 'Allison G.',
            'Amber G.' => 'Amber G.',
			'Andy C.' => 'Andy C.',
			'Angie C.' => 'Angie C.',
			'Brad K.' => 'Brad K.',
			'Cade B.' => 'Cade B.',
			'Carson M.' => 'Carson M.',
			'Charly M.' => 'Charly M.',
			'Chris R.' => 'Chris R.',
            'Collin B.'=>'Collin B.',
			'Graham H.' => 'Graham H.',
			'James C.' => 'James C.',
			'Jay U.' => 'Jay U.',
			'Jessica Z.' => 'Jessica Z.',
			'Jill P.' => 'Jill P.',
			'Josh A.' => 'Josh A.',
			'Kelly T.' => 'Kelly T.',
			'Kelsey T.' => 'Kelsey T.',
			'Lynn C.' => 'Lynn C.',
			'Monica I.' => 'Monica I.',
			'Olivia C.' => 'Olivia C.',
            'Ryan C.'=>'Ryan C.',
			'Scott R.' => 'Scott R.',
			'Todd S.' => 'Todd S.',
			'Zack M.' => 'Zack M.',
		);

                 //Check for older data - an employee that no longer works for WDS
                if (in_array($caller, $wdsCallers)) {
                    return $wdsCallers;
                }
                else
                {
                    $wdsCallers[$caller] = $caller;
                    return $wdsCallers;
                }
	}

	/**
	 * @return array of wds HA writers with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsHAWriters($writer)
	{
		$haWriters = array(
			'' => '',
			'Alexis W.' => 'Alexis W.',
			'Allison G.' => 'Allison G.',
            'Amber G.' => 'Amber G.',
			'Amy S.' => 'Amy S.',
			'Andy C.' => 'Andy C.',
			'Angie C.' => 'Angie C.',
			'Cade B.' => 'Cade B.',
			'Carson M.' => 'Carson M.',
            'Collin B.' => 'Collin B.',
			'Gayle T.' => 'Gayle T.',
			'Graham H.' => 'Graham H.',
			'Heather D.' => 'Heather D.',
			'James C.' => 'James C.',
			'Jay U.' => 'Jay U.',
            'Jennifer M.' => 'Jennifer M.',
			'Jill P.' => 'Jill P.',
			'Josh A.' => 'Josh A.',
			'Katie T.' => 'Katie T.',
			'Monica I.' => 'Monica I.',
			'Olivia C.' => 'Olivia C.',
			'Ryan C.' => 'Ryan C.',
			'Ryan S.' => 'Ryan S.',
			'Scott R.' => 'Scott R.',
			'Sophia R.' => 'Sophia R.',
			'Steve G.' => 'Steve G.',
			'Todd S.' => 'Todd S.',
			'Zach M.' => 'Zach M.',
		);

                 //Check for older data - an employee that no longer works for WDS
                if (in_array($writer, $haWriters)) {
                    return $haWriters;
                }
                else
                {
                    $haWriters[$writer] = $writer;
                    return $haWriters;
                }
	}

	/**
	 * @return array of wds statuses with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsStatuses()
	{
		return array(
//			'' => '',
			'CANCELLED - SNOW' => 'CANCELLED - SNOW',
			'COMPLETED - Delivered to Member' => 'COMPLETED - Delivered to Member',
			'Contacted 4 Times' => 'Contacted 4 Times',
			'DECEASED' => 'DECEASED',
			'Declined (Do Not Contact)' => 'Declined (Do Not Contact)',
			'Declined (USAA - Approval Denied)' => 'Declined (USAA - Approval Denied)',
			'Declined (USAA - Approval Pending)' => 'Declined (USAA - Approval Pending)',
			'Declined (USAA - Approved)' => 'Declined (USAA - Approved)',
            'Declined - Fireshield Enrolled' => 'Declined - Fireshield Enrolled',
			'FINAL CONTACT' => 'FINAL CONTACT',
			'More Info Required' => 'More Info Required',
			'NEED MORE INFO - Letter Mailed' => 'NEED MORE INFO - Letter Mailed',
			'NO LONGER INSURED - PER USAA' => 'NO LONGER INSURED - PER USAA',
			'Postponed' => 'Postponed',
			'Postponed - Fire' => 'Postponed - Fire',
			'Postponed - Previously (Contacted 4 Times)' => 'Postponed - Previously (Contacted 4 Times)',
			'Postponed - Previously (Declined)' => 'Postponed - Previously (Declined)',
			'Postponed - Previously (More Info Required)' => 'Postponed - Previously (More Info Required)',
			'Postponed - Previously (Previously Canceled - Snow)' => 'Postponed - Previously (Previously Canceled - Snow)',
			'Postponed - Previously (Scheduled)' => 'Postponed - Previously (Scheduled)',
			'Scheduled' => 'Scheduled',
			'Scheduled - Previously (Contacted 4 Times)' => 'Scheduled - Previously (Contacted 4 Times)',
			'Scheduled - Previously (Declined USAA Approval Pending)' => 'Scheduled - Previously (Declined USAA Approval Pending)',
			'Scheduled - Previously (More Info Required)' => 'Scheduled - Previously (More Info Required)',
			'Scheduled - Previously (Postponed - Fire)' => 'Scheduled - Previously (Postponed - Fire)',
			'Scheduled - Previously (Postponed)' => 'Scheduled - Previously (Postponed)',
			'Scheduled - Previously (Previously Canceled - Snow)' => 'Scheduled - Previously (Previously Canceled - Snow)',
			'T.B.S. - Letter Mailed' => 'T.B.S. - Letter Mailed',
			'TBS - Info Received' => 'TBS - Info Received',
			'TO BE SCHEDULED' => 'TO BE SCHEDULED',
		);
	}

	/**
	 * @return array of wds follow up statuses with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsFollowUpStatuses()
	{
		return array(
			'' => '',
			'TO BE SURVEYED' => 'TO BE SURVEYED',
			'COMPLETED - SURVEY' => 'COMPLETED - SURVEY',
			'DECLINED - SURVEY' => 'DECLINED - SURVEY',
			'CONTACTED 2 TIMES - SURVEY' => 'CONTACTED 2 TIMES - SURVEY',
			'MEMBER INFO INCORRECT' => 'MEMBER INFO INCORRECT',
		);
	}

	/**
	 * @return array of wds HA times with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsHATimes()
	{
		return array(
			'' => '',
			'0830' => '0830',
			'0900' => '0900',
            '0930'=>'0930',
			'1000' => '1000',
            '1030' => '1030',
			'1100' => '1100',
			'1130' => '1130',
			'1200' => '1200',
			'1230' => '1230',
			'1300' => '1300',
			'1330' => '1330',
			'1400' => '1400',
            '1430' => '1430',
			'1500' => '1500',
            '1530' => '1530',
            '1600' => '1600',
            '1630' => '1630',
            '1700' => '1700',
            '1730' => '1730'
		);
	}

	/**
	 * @return array of wds Follow Up ?s with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsFollowUpQuestions()
	{
		return array(
			'' => '',
			'1' => 'Have you taken any action since receiving the assessment? - If so what?',
			'2' => 'Do you believe the assessment better prepared you to protect your home from wildfire damage?',
			'3' => 'Would you recommend USAA continue this program?',
			'4' => 'Do you have any recommendations on how to improve the program? - If so, what?',
		);
	}

	/**
	 * @return array of wds Fire Reviewers with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsFireReviewers($reviewer)
	{
        // Look up users by "PR Fire Reviewer" type.
        $userCriteria = new CDbCriteria();
        $userCriteria->addSearchCondition('type', 'PR Fire Reviewer');
        $fireReviewerUsers = User::model()->findAll($userCriteria);

        $fireReviewers = array('' => '');
        $lastName = '';  //initialize $lastName
        // Add only their last names to the list.
        foreach ($fireReviewerUsers as $user)
        {
            $names = explode(" ", $user->name);
            if(count($names) > 1)
            {
                $lastName = $names[1];
            }
            $fireReviewers[$lastName] = $lastName;
        }
        //Check for older data - an employee that no longer works for WDS
        if (!in_array($reviewer, $fireReviewers))
        {
            $fireReviewers[$reviewer] = $reviewer;
        }

        asort($fireReviewers);

        return $fireReviewers;
	}

	/**
	 * @return array of wds Field Assessors with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsFieldAssessors($fieldAssessor)
	{
		$assessors = array(
			'' => '',
			'A. Coats' => 'A. Coats',
            'A. Elkins' => 'A. Elkins',
            "A. O'Dea"=>"A. O'Dea",
			'A. Ornelas' => 'A. Ornelas',
            'A. Garcia' => 'A. Garcia',
			'B. Fisk' => 'B. Fisk',
			'B. Haysom' => 'B. Haysom',
            'B. Hillious' => 'B. Hillious',
			'B. James' => 'B. James',
			'B. Martin' => 'B. Martin',
			'B. Schmidt' => 'B. Schmidt',
			'C. Bachman' => 'C. Bachman',
			'C. Brozka' => 'C. Brozka',
			'C. Riddle' => 'C. Riddle',
            'C. Weaver' => 'C. Weaver',
			'D. Aaron' => 'D. Aaron',
            'D. Ross' => 'D. Ross',
			'E. Kozloski' => 'E. Kozloski',
			'F. Arrellano' => 'F. Arrellano',
			'G. Jackson' => 'G. Jackson',
            'J.D. James' => 'J.D. James',
			'J. Lee' => 'J. Lee',
			'J. Leintz' => 'J. Leintz',
			'J. Margason' => 'J. Margason',
			'J. Pancerz' => 'J. Pancerz',
			'J. Rheinbolt' => 'J. Rheinbolt',
            'J. Vasey' => 'J. Vasey',
			'J. Wills' => 'J. Wills',
			'K. Grossnickle' => 'K. Grossnickle',
			'K. Rafferty' => 'K. Rafferty',
            'M. Duffy' =>'M. Duffy',
            'M. Harper' => 'M. Harper',
            'M. Hayes' => 'M. Hayes',
			'M. Willis' => 'M. Willis',
			'N. Beacham' => 'N. Beacham',
			'N. Lauria' => 'N. Lauria',
            'N. Tuntland' => 'N. Tuntland',
			'P. Barry' => 'P. Barry',
			'P. Flynn' => 'P. Flynn',
			'R. Arias' => 'R. Arias',
			'R. Cox' => 'R. Cox',
			'R. Fillmore' => 'R. Fillmore',
			'R. Laird' => 'R. Laird',
            'R. Martin'=>'R. Martin',
            'S. Gilson' => 'S. Gilson',
            'S. Roden' => 'S. Roden',
			'T. Mathiesen' => 'T. Mathiesen',
			'T. Stahl' => 'T. Stahl',
			'T. Thornsberry' => 'T. Thornsberry',
			'Z. McHugh' => 'Z. McHugh',
		);

            //Check for older data - an employee that no longer works for WDS
            if (in_array($fieldAssessor, $assessors)) {
                return $assessors;
            }
            else
            {
                $assessors[$fieldAssessor] = $fieldAssessor;
                return $assessors;
            }
	}

	/**
	 * @return array of wds Engines with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	 */
	public function wdsEngines()
	{
		return array(
			'' => '',
			'AZ-WDS-1' => 'AZ-WDS-1',
			'AZ-WDS-2' => 'AZ-WDS-2',
			'AZ-WDS-3' => 'AZ-WDS-3',
			'AZ-WDS-4' => 'AZ-WDS-4',
			'CA-WDS-1' => 'CA-WDS-1',
			'CA-WDS-2' => 'CA-WDS-2',
			'CA-WDS-3' => 'CA-WDS-3',
			'CA-WDS-4' => 'CA-WDS-4',
			'CO-C2C-1' => 'CO-C2C-1',
			'CO-WDS-1' => 'CO-WDS-1',
			'CO-WDS-2' => 'CO-WDS-2',
			'CO-WDS-3' => 'CO-WDS-3',
			'CO-WDS-4' => 'CO-WDS-4',
			'FS-1' => 'FS-1',
			'FS-2' => 'FS-2',
			'FS-3' => 'FS-3',
			'FS-4' => 'FS-4',
			'GFP-1' => 'GFP-1',
            'ID-WDS-1' => 'ID-WDS-1',
            'MT-WDS-1'=>'MT-WDS-1',
            'NM-WDS-1' => 'NM-WDS-1',
            'NM-WDS-2' => 'NM-WDS-2',
			'NV-WDS-1' => 'NV-WDS-1',
			'NV-WDS-2' => 'NV-WDS-2',
			'NV-WDS-3' => 'NV-WDS-3',
			'NV-WDS-4' => 'NV-WDS-4',
            'OR-WDS-1' => 'OR-WDS-1',
            'OR-WDS-2' => 'OR-WDS-2',
			'TFR-1' => 'TFR-1',
			'TFR-2' => 'TFR-2',
			'TX-WDS-1' => 'TX-WDS-1',
			'TX-WDS-2' => 'TX-WDS-2',
			'TX-WDS-3' => 'TX-WDS-3',
			'TX-WDS-4' => 'TX-WDS-4',
            'UT-WDS-1' => 'UT-WDS-1',
            'UT-WDS-2' => 'UT-WDS-2',
            'WA-WDS-1'=>'WA-WDS-1',
            'WA-WDS-2'=>'WA-WDS-2',
            'WY-WDS-1'=>'WY-WDS-1',
            'Dedicated_Engine' => 'Dedicated_Engine',
		);
	}

	/**
	 * @return array of states that pre risk uses with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	*/
	public function states()
	{
		return array(
			'' => '',
			'AZ' => 'AZ',
			'CA' => 'CA',
			'CO' => 'CO',
            'ID' => 'ID',
            'MT' => 'MT',
			'NM' => 'NM',
			'NV' => 'NV',
            'OR' => 'OR',
			'TX' => 'TX',
            'UT' => 'UT',
            'WA' => 'WA',
            'WY' => 'WY'
		);
	}

	/**
	 * @return array of months with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	*/
	public function months()
	{
		return array(
			'' => '',
			'JANUARY' => 'JANUARY',
			'FEBRUARY' => 'FEBRUARY',
			'MARCH' => 'MARCH',
			'APRIL' => 'APRIL',
			'MAY' => 'MAY',
			'JUNE' => 'JUNE',
			'JULY' => 'JULY',
			'AUGUST' => 'AUGUST',
			'SEPTEMBER' => 'SEPTEMBER',
			'OCTOBER' => 'OCTOBER',
			'NOVEMBER' => 'NOVEMBER',
			'DECEMBER' => 'DECEMBER',
		);
	}

	/**
	 * @return array of years with both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	*/
	public function years()
	{
		return array(
			'' => '',
			'2009' => '2009',
			'2010' => '2010',
			'2011' => '2011',
			'2012' => '2012',
			'2013' => '2013',
			'2014' => '2014',
			'2015' => '2015',
		);
	}

    /**
     * @return array of drop-down options for fs_accepted.
     */
    public function getFSAcceptedOptions()
    {
        return array(
            '' => '',
            'NO' => 'NO',
            'Walkthrough' => 'Walkthrough',
            'Landline Callback' => 'Landline Callback',
            'Member Callback' => 'Member Callback',
            'Email Resend' => 'Email Resend',
            'No Action' => 'No Action',
        );
    }

	/**
	 * @return array of yes/no (optional maybe) both key and value the name of caller (good for yii CActiveDropDowns and other widgets)
	*/
	public function yesNo($include_maybe = false)
	{
		$return_array = array(
			'' => '',
			'YES' => 'YES',
			'NO' => 'NO',
		);

		if($include_maybe)
			$return_array['MAYBE'] = 'MAYBE';

		return $return_array;
	}

    public function yesNoBoolean()
    {
        return array(
            0 => 'NO',
            1 => 'YES',
        );
    }

        /**
	 * @return array of yes/no/notsure
         */
	public function yesNoNotSure()
	{
		$return_array = array(
			'' => '',
			'YES' => 'YES',
			'NO' => 'NO',
			'NOT SURE' => 'NOT SURE',
		);

		return $return_array;
	}

        public function deliveryMethod()
	{
		$return_array = array(
			'' => '',
			'Email' => 'Email',
			'Mail' => 'Mail',
			'Both' => 'Both',
		);

		return $return_array;
	}

         public function scaleRating()
	{
		$return_array = array(
			''=>'',
			'1' => '1 - Not Satisfied',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5- Extremely Satisfied'
		);

		return $return_array;
	}

        public function scaleRatingPrepare()
	{
		$return_array = array(
			''=>'',
			'1' => '1 - Not at all',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5 - To a great extent'
		);

		return $return_array;
	}

    public function pointOfContact()
	{
		$return_array = array(
			''=>'',
			'Member' => 'Member',
			'Spouse' => 'Spouse',
			'Other' => 'Other'
		);

		return $return_array;
	}

    public function conditions()
	{
		$return_array = array(
			''=>'',
			'Firewise' => 'Firewise',
			'Replace/repair roof' => 'Replace/repair roof',
            'Clean roof' => 'Clean roof',
            'Replace/repair skylight/roof attachment' => 'Replace/repair skylight/roof attachment',
			'Screen/openings vents' => 'Screen/openings vents',
			'Clean gutters' => 'Clean gutters',
			'Clean/enclose eaves' => 'Clean/enclose eaves',
			'Screen openings/vents' => 'Screen openings/vents',
			'Replace windows' => 'Replace windows',
			'Replace/treat siding' => 'Replace/treat siding',
			'Clean/enclose underside of home' => 'Clean/enclose underside of home',
			'Replace/treat attachment(s)' => 'Replace/treat attachment(s)',
			'Clear vegetation/materials in 0-5 ft. zone' => "Clear vegetation/materials in 0-5 ft. zone",
			'Manage vegetation in 5-30 ft. zone'=>'Manage vegetation in 5-30 ft. zone',
			'Clear materials in 5-30 ft. zone'=>'Clear materials in 5-30 ft. zone',
			'Additional structure(s)'=>'Additional structure(s)',
			'Manage vegetation in 30-100 ft. zone'=>'Manage vegetation in 30-100 ft. zone',
			'Additional structure(s)'=>'Additional structure(s)',
		);

		return $return_array;
	}

	public function actionsNotComplete()
	{
		$return_array = array(
			''=>'',
			'I have not had enough time to complete the work' => 'I have not had enough time to complete the work',
			"I didn't think that action was applicable" => "I didn't think that action was applicable",
			"I didn't agree with the recommended actions" => "I didn't agree with the recommended actions",
			"I didn't understand the recommended actions" => "I didn't understand the recommended actions",
			'I could not afford the recommended actions' => 'I could not afford the recommended actions',
			'Other' => 'Other',
		);

		return $return_array;
	}

    public static function countScheduledAssessments($dateStart, $dateEnd)
    {
        $criteria = new CDbCriteria;
        $criteria->addCondition("ha_date >='" . $dateStart . "'");
        $criteria->addCondition("ha_date <'" . $dateEnd . "'");

        $total = PreRisk::model()->count($criteria);

        return $total;
    }

    /**
     * Counts all completed assessments for the date range
     * @param string $dateStart 
     * @param string $dateEnd 
     * @param integer $format 
     * @return integer
     */
    public static function countCompletedAssessments($dateStart = null, $dateEnd = null, $format = null)
    {
        if ($dateStart)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition("completion_date >='" . $dateStart . "'");
            if ($dateEnd)
                $criteria->addCondition("completion_date <'" . $dateEnd . "'");
            $total = PreRisk::model()->count($criteria);
        }
        else
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition("status ='COMPLETED - Delivered to Member'");
            $total = PreRisk::model()->count($criteria);
        }

        return ($format) ? Yii::app()->format->number($total) : $total;
    }

    public static function countCompletedAssessmentsPerMonth($startDate, $endDate)
    {
        //Final result
        $returnArray = array();

        //Reassign to preserve the originals
        $date1 = date('Y-m-d', strtotime($startDate));
        $date2 = date('Y-m-d', strtotime('+1 months', strtotime($date1)));

        //makes selections for each month
        while($date2 < $endDate)
        {
            $criteria = new CDbCriteria;
            $criteria->addCondition("completion_date >= '$date1'");
            $criteria->addCondition("completion_date < '$date2'");

            //Add totals per month into return array
            $monthEntry = array(
                'month'=> date('M', strtotime($date1)),
                'completed_assessments' => PreRisk::model()->count($criteria)
            );

            //Add totals per month into return array
            $returnArray[] = $monthEntry;

            //incriment dates to the next month
            $date1 = $date2;
            $date2 = date('Y-m-d', strtotime('+1 months', strtotime($date1)));
        }

        return $returnArray;
    }

    //Retrieves all pre risk entries for the date range (used to calculate stats for the calls)
    public static function countCallCampaignStatus($callMonth, $callYear)
    {
        $returnArray = array();

        $criteria = new CDbCriteria;
        $criteria->addCondition("call_list_month = '$callMonth'");
        $criteria->addCondition("call_list_year = '$callYear'");
        $models = PreRisk::model()->findAll($criteria);

        foreach($models as $model)
        {
            if(isset($returnArray[$model->status]))
                $returnArray[$model->status] += 1;
            else
                $returnArray[$model->status] = 1;
        }

        return $returnArray;

    }

}
