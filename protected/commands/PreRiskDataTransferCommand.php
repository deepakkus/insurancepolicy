<?php
class PreRiskDataTransferCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
        print "\n-----STARTING PR Data Xfer COMMAND--------\n\n";

        //setup output file
        $data_fh = fopen('C:\\wds_usaa_pr_file_xfer\\wds_pr_assessment_data.csv', 'w');
        $conditions_fh = fopen('C:\\wds_usaa_pr_file_xfer\\wds_pr_assessment_conditions.csv', 'w');
        fputcsv($data_fh, array('Assessment ID', 'Member Number', 'Policy', 'Completion Date', 'Member Name', 'Property Address',
            'Property City', 'Property State', 'Property Zip', 'Week to Schedule', 'Call List Year', 'Call List Month', 'Home Phone',
            'Work Phone', 'Cell Phone', 'Email', 'Mailing Address', 'Mailing City', 'Mailing State', 'Mailing Zip', 'Time 1', 'Time 2',
            'Time 3', 'Time 4', 'Status', 'Assigned By', 'HA Date', 'HA Time', 'Homeowner to Be Present', 'Ok To Do w/o Member Present',
            'Authorization by Affidavit', 'Contact Date', 'Engine', 'Delivery Method', 'Appointment Information', 'HA Field Assessor',
            'Fire Review', 'Writer', 'Replace/repair roof', 'Clean roof', 'Replace/repair skylight/roof attachment', 'Screen/openings vents',
            'Clean gutters', 'Clean/enclose eaves', 'Replace windows', 'Replace/treat siding', 'Clean/enclose underside of home', 'Replace/treat attachment(s)',
            'Clear vegetation/materials in 0-5 ft. zone', 'Manage vegetation in 5-30 ft. zone', 'Clear materials in 5-30 ft. zone',
            'Manage vegetation in 30-100 ft. zone', 'Additional structure(s)', 'Legacy Recommended Actions', 'Delivery Date', 'Follow Up Time Date 1',
            'Follow Up Time Date 2', 'Follow Up Status', 'Point of contact', 'Satisfaction with HA', 'Recommend USAA to contiue this program?',
            'Why USAA should discontinue?', 'Taken action recommended in HA?', 'Reasons why actions not completed', 'Condition 1', 'Action 1', 'Condition 2',
            'Action 2', 'Condition 3', 'Action 3', 'Condition 4', 'Action 4', 'Condition 5', 'Action 5', 'Condition 6', 'Action 6', 'Has the HA prepared you to protect your home?',
            'Recommendations for USAA to improve this program'));
        fputcsv($conditions_fh, array('Assessment ID', 'Condition', 'Response', 'Image Names', 'Risk', 'Action', 'Notes', 'Example'));

        //get all PR entries
        $prids = Yii::app()->db->createCommand()
            ->select('id')
            ->from('pre_risk')
            ->queryAll();

        $ass_ids = array();
        $counter = 0;
        foreach($prids as $prid)
        {
            $counter++;
            $prerisk = PreRisk::model()->findByPk($prid['id']);

            $member_num = $prerisk->member_number;
            $policy = 'na';
            $comp_date = 'na';
            if(isset($prerisk->completion_date) && $prerisk->completion_date != '1900-01-01 00:00:00')
                $comp_date = substr($prerisk->completion_date, 0, 10);
            $member_name = '';
            if(!empty($prerisk->member_name))
                $member_name = $prerisk->member_name;
            $property_address = '';
            if(!empty($prerisk->street_address))
                $property_address = $prerisk->street_address;
            $property_city = '';
            if(!empty($prerisk->city))
                $property_city = $prerisk->city;
            $property_state = '';
            if(!empty($prerisk->state))
                $property_state = $prerisk->state;
            $property_zip = '';
            if(!empty($prerisk->zip_code))
                $property_zip = $prerisk->zip_code;
            $week_to_schedule = '';
            if(!empty($prerisk->week_to_schedule))
                $week_to_schedule = $prerisk->week_to_schedule;
            $call_list_year = '';
            if(!empty($prerisk->call_list_year))
                $call_list_year = $prerisk->call_list_year;
            $call_list_month = '';
            if(!empty($prerisk->call_list_month))
                $call_list_month = $prerisk->call_list_month;
            $home_phone = '';
            if(!empty($prerisk->home_phone))
                $home_phone = $prerisk->home_phone;
            $work_phone = '';
            if(!empty($prerisk->work_phone))
                $work_phone = $prerisk->work_phone;
            $cell_phone = '';
            if(!empty($prerisk->cell_phone))
                $cell_phone = $prerisk->cell_phone;
            $email = '';
            if(!empty($prerisk->client_email))
                $email = $prerisk->client_email;
            $mail_address = '';
            if(!empty($prerisk->alt_mailing_address))
                $mail_address = $prerisk->alt_mailing_address;
            $mail_city = '';
            $mail_state = '';
            $mail_zip = '';
            $time_1 = '';
            if(!empty($prerisk->time_1))
                $time_1 = $prerisk->time_1;
            $time_2 = '';
            if(!empty($prerisk->time_2))
                $time_2 = $prerisk->time_2;
            $time_3 = '';
            if(!empty($prerisk->time_3))
                $time_3 = $prerisk->time_3;
            $time_4 = '';
            if(!empty($prerisk->time_4))
                $time_4 = $prerisk->time_4;
            $status = '';
            if(!empty($prerisk->status))
                $status = $prerisk->status;
            $assigned_by = '';
            if(!empty($prerisk->assigned_by))
                $assigned_by = $prerisk->assigned_by;
            $ha_date = '';
            if(!empty($prerisk->ha_date))
                $ha_date = $prerisk->ha_date;
            $ha_time = '';
            if(!empty($prerisk->ha_time))
                $ha_time = $prerisk->ha_time;
            $homeowner_to_be_present = '';
            if(!empty($prerisk->homeowner_to_be_present))
                $homeowner_to_be_present = $prerisk->homeowner_to_be_present;
            $ok_to_do_wo_member_present = '';
            if(!empty($prerisk->ok_to_do_wo_member_present))
                $ok_to_do_wo_member_present = $prerisk->ok_to_do_wo_member_present;
            $authorization_by_affidavit = '';
            if(!empty($prerisk->authorization_by_affadivit))
                $authorization_by_affidavit = $prerisk->authorization_by_affadivit;
            $contact_date = '';
            if(!empty($prerisk->contact_date))
                $contact_date = $prerisk->contact_date;
            $engine = '';
            if(!empty($prerisk->engine))
                $engine = $prerisk->engine;
            $delivery_method = '';
            if(!empty($prerisk->delivery_method))
                $delivery_method = $prerisk->delivery_method;
            $appointment_information = '';
            if(!empty($prerisk->appointment_information))
                $appointment_information = $prerisk->appointment_information;
            $ha_field_assessor = '';
            if(!empty($prerisk->ha_field_assessor))
                $ha_field_assessor = $prerisk->ha_field_assessor;
            $fire_review = '';
            if(!empty($prerisk->fire_review))
                $fire_review = $prerisk->fire_review;
            $writer = '';
            if(!empty($prerisk->wds_ha_writers))
                $writer = $prerisk->wds_ha_writers;
            $replace_repair_roof = '';
            if(!empty($prerisk->replace_repair_roof))
                $replace_repair_roof = $prerisk->replace_repair_roof;
            $clean_roof = '';
            if(!empty($prerisk->clean_roof))
                $clean_roof = $prerisk->clean_roof;
            $replace_repair_skylight_roof_attachment = '';
            if(!empty($prerisk->repair_roof_attachment))
                $replace_repair_skylight_roof_attachment = $prerisk->repair_roof_attachment;
            $screen_openings_vents = '';
            if(!empty($prerisk->screen_openings_vents))
                $screen_openings_vents = $prerisk->screen_openings_vents;
            $clean_gutters = '';
            if(!empty($prerisk->clean_gutters))
                $clean_gutters = $prerisk->clean_gutters;
            $clean_enclose_eaves = '';
            if(!empty($prerisk->clean_enclose_eaves))
                $clean_enclose_eaves = $prerisk->clean_enclose_eaves;
            $replace_windows = '';
            if(!empty($prerisk->replace_windows))
                $replace_windows = $prerisk->replace_windows;
            $replace_treat_siding = '';
            if(!empty($prerisk->replace_treat_siding))
                $replace_treat_siding = $prerisk->replace_treat_siding;
            $clean_enclose_underside_of_home = '';
            if(!empty($prerisk->clean_under_home))
                $clean_enclose_underside_of_home = $prerisk->clean_under_home;
            $replace_treat_attachments = '';
            if(!empty($prerisk->replace_attachment))
                $replace_treat_attachments = $prerisk->replace_attachment;
            $clear_vegetation_materials_in_5_ft_zone = '';
            if(!empty($prerisk->clear_veg_0_5))
                $clear_vegetation_materials_in_5_ft_zone = $prerisk->clear_veg_0_5;
            $manage_vegetation_in_30_ft_zone = '';
            if(!empty($prerisk->clear_veg_0_5))
                $manage_vegetation_in_30_ft_zone = $prerisk->clear_veg_0_5;
            $clear_materials_in_30_ft_zone = '';
            if(!empty($prerisk->clear_materials_5_30))
                $clear_materials_in_30_ft_zone = $prerisk->clear_materials_5_30;
            $manage_vegetation_in_100_ft_zone = '';
            if(!empty($prerisk->manage_veg_30_100))
                $manage_vegetation_in_100_ft_zone = $prerisk->manage_veg_30_100;
            $additional_structures = '';
            if(!empty($prerisk->additional_structure))
                $additional_structures = $prerisk->additional_structure;
            $legacy_recommended_actions = '';
            if(!empty($prerisk->allRecActions))
                $legacy_recommended_actions = $prerisk->allRecActions;
            $delivery_date = '';
            if(!empty($prerisk->delivery_date))
                $delivery_date = $prerisk->delivery_date;
            $follow_up_time_date_1 = '';
            if(!empty($prerisk->follow_up_time_date_1))
                $follow_up_time_date_1 = $prerisk->follow_up_time_date_1;
            $follow_up_time_date_2 = '';
            if(!empty($prerisk->follow_up_time_date_2))
                $follow_up_time_date_2 = $prerisk->follow_up_time_date_2;
            $follow_up_status = '';
            if(!empty($prerisk->follow_up_status))
                $follow_up_status = $prerisk->follow_up_status;
            $point_of_contact = '';
            if(!empty($prerisk->point_of_contact))
                $point_of_contact = $prerisk->point_of_contact;
            $satisfaction_with_ha = '';
            if(!empty($prerisk->follow_up_2_answer_1))
                $satisfaction_with_ha = $prerisk->follow_up_2_answer_1;
            $recommend_usaa_to_contiue_this_program = '';
            if(!empty($prerisk->follow_up_2_answer_2))
                $recommend_usaa_to_contiue_this_program = $prerisk->follow_up_2_answer_2;
            $why_usaa_should_discontinue = '';
            if(!empty($prerisk->follow_up_2_answer_3))
                $why_usaa_should_discontinue = $prerisk->follow_up_2_answer_3;
            $taken_action_recommended_in_ha = '';
            if(!empty($prerisk->follow_up_2_answer_4))
                $taken_action_recommended_in_ha = $prerisk->follow_up_2_answer_4;
            $reasons_why_actions_not_completed = '';
            if(!empty($prerisk->follow_up_2_answer_5))
                $reasons_why_actions_not_completed = $prerisk->follow_up_2_answer_5;
            $condition_1 = '';
            if(!empty($prerisk->follow_up_2_question_6a))
                $condition_1 = $prerisk->follow_up_2_question_6a;
            $action_1 = '';
            if(!empty($prerisk->follow_up_2_answer_6a))
                $action_1 = $prerisk->follow_up_2_answer_6a;
            $condition_2 = '';
            if(!empty($prerisk->follow_up_2_question_6b))
                $condition_2 = $prerisk->follow_up_2_question_6b;
            $action_2 = '';
            if(!empty($prerisk->follow_up_2_answer_6b))
                $action_2 = $prerisk->follow_up_2_answer_6b;
            $condition_3 = '';
            if(!empty($prerisk->follow_up_2_question_6c))
                $condition_3 = $prerisk->follow_up_2_question_6c;
            $action_3 = '';
            if(!empty($prerisk->follow_up_2_answer_6c))
                $action_3 = $prerisk->follow_up_2_answer_6c;
            $condition_4 = '';
            if(!empty($prerisk->follow_up_2_question_6d))
                $condition_4 = $prerisk->follow_up_2_question_6d;
            $action_4 = '';
            if(!empty($prerisk->follow_up_2_answer_6d))
                $action_4 = $prerisk->follow_up_2_answer_6d;
            $condition_5 = '';
            if(!empty($prerisk->follow_up_2_question_6e))
                $condition_5 = $prerisk->follow_up_2_question_6e;
            $action_5 = '';
            if(!empty($prerisk->follow_up_2_6e_response))
                $action_5 = $prerisk->follow_up_2_6e_response;
            $condition_6 = '';
            if(!empty($prerisk->follow_up_2_question_6f))
                $condition_6 = $prerisk->follow_up_2_question_6f;
            $action_6 = '';
            if(!empty($prerisk->follow_up_2_answer_6f))
                $action_6 = $prerisk->follow_up_2_answer_6f;
            $has_the_ha_prepared_you = '';
            if(!empty($prerisk->follow_up_2_answer_7))
                $has_the_ha_prepared_you = $prerisk->follow_up_2_answer_7;
            $recommendations = '';
            if(!empty($prerisk->follow_up_2_answer_8))
                $recommendations = $prerisk->follow_up_2_answer_8;

            if(isset($prerisk->property_pid))
            {
                $property = Property::model()->findByPk($prerisk->property_pid);
                if(isset($property))
                {
                    $member = Member::model()->findByPk($property->member_mid);
                    $member_num = $member->member_num;
                    $policy = $property->policy;
                    $member_name = $member->first_name.' '.$member->last_name;

                    if(!empty($property->address_line_1))
                        $property_address = $property->address_line_1;
                    if(!empty($property->address_line_2))
                        $property_address .= "\n".$property->address_line_2;
                    if(!empty($property->city))
                        $property_city = $property->city;
                    if(!empty($property->state))
                        $property_state = $property->state;
                    if(!empty($property->zip))
                        $property_zip = $property->zip;
                    if(!empty($member->home_phone))
                        $home_phone = $member->home_phone;
                    if(!empty($member->work_phone))
                        $work_phone = $member->work_phone;
                    if(!empty($member->cell_phone))
                        $cell_phone = $member->cell_phone;
                    if(!empty($member->email_1))
                        $email = $member->email_1;
                    if(!empty($member->mail_address_line_1))
                        $mail_address = $member->mail_address_line_1;
                    if(!empty($member->mail_city))
                        $mail_city = $member->mail_city;
                    if(!empty($member->mail_state))
                        $mail_state = $member->mail_state;
                    if(!empty($member->mail_zip))
                        $mail_zip = $member->mail_zip;
                }
            }

            $ass_id = $member_num.'_'.$policy.'_'.$comp_date;
            if(in_array($ass_id, $ass_ids))
            {
                $dupe_counter = 2;
                $new_ass_id = $ass_id.'_'.$dupe_counter;
                while(in_array($new_ass_id, $ass_ids))
                {
                    $dupe_counter++;
                    $new_ass_id = $ass_id.'_'.$dupe_counter;
                }
                $ass_id = $new_ass_id;
            }
            $ass_ids[] = $ass_id;

            fputcsv($data_fh, array($ass_id, $member_num, $policy, $comp_date, $member_name, $property_address, $property_city,
            	$property_state, $property_zip, $week_to_schedule, $call_list_year, $call_list_month, $home_phone, $work_phone,
            	$cell_phone, $email, $mail_address, $mail_city, $mail_state, $mail_zip, $time_1, $time_2, $time_3, $time_4,
            	$status, $assigned_by, $ha_date, $ha_time, $homeowner_to_be_present, $ok_to_do_wo_member_present,
            	$authorization_by_affidavit, $contact_date, $engine, $delivery_method, $appointment_information,
            	$ha_field_assessor, $fire_review, $writer, $replace_repair_roof, $clean_roof, $replace_repair_skylight_roof_attachment,
            	$screen_openings_vents, $clean_gutters, $clean_enclose_eaves, $replace_windows, $replace_treat_siding,
            	$clean_enclose_underside_of_home, $replace_treat_attachments, $clear_vegetation_materials_in_5_ft_zone,
            	$manage_vegetation_in_30_ft_zone, $clear_materials_in_30_ft_zone, $manage_vegetation_in_100_ft_zone, $additional_structures,
            	$legacy_recommended_actions, $delivery_date, $follow_up_time_date_1, $follow_up_time_date_2, $follow_up_status,
            	$point_of_contact, $satisfaction_with_ha, $recommend_usaa_to_contiue_this_program, $why_usaa_should_discontinue,
            	$taken_action_recommended_in_ha, $reasons_why_actions_not_completed, $condition_1, $action_1, $condition_2, $action_2,
            	$condition_3, $action_3, $condition_4, $action_4, $condition_5, $action_5, $condition_6, $action_6, $has_the_ha_prepared_you,
            	$recommendations));

            //conditions, if they exist
            $fs_report = FSReport::model()->findByAttributes(array('pre_risk_id'=>$prerisk->id));
            if(isset($fs_report))
            {
                fputcsv($conditions_fh, array($ass_id, 'Summary', '', '', '', '', $fs_report->summary, ''));

                $conditions = FSCondition::model()->findAllByAttributes(array('fs_report_id'=>$fs_report->id));
                foreach($conditions as $condition)
                {
                    $ass_question_model = FSAssessmentQuestion::model()->findByAttributes(array('client_id'=>13, 'question_num'=>$condition->condition_num));
                    if(isset($ass_question_model))
                        $ass_question = $ass_question_model->question_text;
                    else
                        $ass_question = '';
                    $response = '';
                    if(isset($condition->response))
                    {
                        if($condition->response == 0)
                            $response = 'yes';
                        elseif($condition->response == 1)
                            $response = 'no';
                        elseif($condition->response == 2)
                            $response = 'not sure';
                    }
                    $images = '';
                    if(!empty($condition->submitted_photo_path))
                        $images = $condition->submitted_photo_path;
                    $risk = '';
                    if(!empty($condition->risk_text))
                        $risk = $condition->risk_text;
                    $action = '';
                    if(!empty($condition->recommendation_text))
                        $action = $condition->recommendation_text;
                    $notes = '';
                    if(!empty($condition->notes))
                        $notes = $condition->notes;
                    $example = '';
                    if(!empty($condition->example_text))
                        $example = $condition->example_text;

                    fputcsv($conditions_fh, array($ass_id, $ass_question, $response, $images, $risk, $action, $notes, $example));
                }

                //move files around
                $outgoing_report_path = 'C:\\inetpub\\wwwroot\\pro.wildfire-defense.com\\protected\\fs_reports\\outgoing\\'.$fs_report->report_guid.'\\';
                $incoming_report_path = 'C:\\inetpub\\wwwroot\\pro.wildfire-defense.com\\protected\\fs_reports\\incoming\\'.$fs_report->report_guid.'\\';
                if(!file_exists('C:\\wds_usaa_pr_file_xfer\\'.$ass_id))
	                mkdir('C:\\wds_usaa_pr_file_xfer\\'.$ass_id);
                $output_path = 'C:\\wds_usaa_pr_file_xfer\\'.$ass_id.'\\';
                copy($outgoing_report_path.'report.pdf', $output_path.'report.pdf');
                if(file_exists($incoming_report_path.'images'))
                {
                	if(!file_exists($output_path.'images'))
	                    mkdir($output_path.'images');
                    $img_dir = opendir($incoming_report_path.'images');
                    while(($file = readdir($img_dir)) !== false)
                    {
                        if($file != '.' && $file != '..' && !is_dir($incoming_report_path.'images\\'.$file))
                        {
                            copy($incoming_report_path.'images\\'.$file, $output_path.'images\\'.$file.'.jpg');
                        }
                    }
                }
            }
            print "$counter: $ass_id \n";

        }
        fclose($data_fh);
        fclose($conditions_fh);
        print "\n-----DONE WITH PR Data Xfer COMMAND-------\n";
    }
}
