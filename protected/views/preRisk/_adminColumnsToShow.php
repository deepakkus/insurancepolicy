<div class="column-form">
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'post',
	));
	?>

	<h3>Columns To Show</h3>

    <div class="paddingTop10 paddingBottom10">
        Quick Views:
        <?php echo CHtml::link('Default','#defaultView',array('class'=>'default-quick-view')); ?>
        <?php echo CHtml::link('Bi-Monthly','#biMonthlyView',array('class'=>'bi-monthly-view marginLeft10')); ?>
        <?php echo CHtml::link('Follow-up','#followUpView',array('class'=>'follow-up-view marginLeft10')); ?>
        <?php echo CHtml::link('EOM Reports','#eomReportsView',array('class'=>'eom-reports-view marginLeft10')); ?>
        <?php echo CHtml::link('EOM Calls','#eomCallsView',array('class'=>'eom-calls-view marginLeft10')); ?>
        <?php echo CHtml::link('EOM Data','#eomDataView',array('class'=>'eom-data-view marginLeft10')); ?>
        <?php echo CHtml::link('Delivery','#deliveryView',array('class'=>'delivery-view marginLeft10')); ?>
        <?php echo CHtml::link('Mailing','#mailingView',array('class'=>'mailing-view marginLeft10')); ?>
    </div>

    <div class="columnsToShow clearfix">
        <div class="floatLeft paddingRight20">
            <h4>General</h4>
            <div><input type="checkbox" name="columnsToShow[id]" value="id" <?php Helper::checkIfInArray($columnsToShow, 'id'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[member_member_num]" value="member_member_num" <?php Helper::checkIfInArray($columnsToShow, 'member_member_num'); ?> /> Member Number</div>
            <div><input type="checkbox" name="columnsToShow[member_client]" value="member_client" <?php Helper::checkIfInArray($columnsToShow, 'member_client'); ?> /> Member Client</div>
            <div><input type="checkbox" name="columnsToShow[member_salutation]" value="member_salutation" <?php Helper::checkIfInArray($columnsToShow, 'member_salutation'); ?> /> Member Salutation</div>
            <div><input type="checkbox" name="columnsToShow[member_first_name]" value="member_first_name" <?php Helper::checkIfInArray($columnsToShow, 'member_first_name'); ?> /> Member First Name</div>
			<div><input type="checkbox" name="columnsToShow[member_middle_name]" value="member_middle_name" <?php Helper::checkIfInArray($columnsToShow, 'member_middle_name'); ?> /> Member Middle Name</div>
            <div><input type="checkbox" name="columnsToShow[member_last_name]" value="member_last_name" <?php Helper::checkIfInArray($columnsToShow, 'member_last_name'); ?> /> Member Last Name</div>
            <div><input type="checkbox" name="columnsToShow[status]" value="status" <?php Helper::checkIfInArray($columnsToShow, 'status'); ?> /> Status</div>
            <div><input type="checkbox" name="columnsToShow[property_pid]" value="property_pid" <?php Helper::checkIfInArray($columnsToShow, 'property_pid'); ?> /> Property ID</div>
            <div><input type="checkbox" name="columnsToShow[fs_email_resend]" value="fs_email_resend" <?php Helper::checkIfInArray($columnsToShow, 'fs_email_resend'); ?> /> FS Email Resend</div>
            <div><input type="checkbox" name="columnsToShow[fs_offered_date]" value="fs_offered_date" <?php Helper::checkIfInArray($columnsToShow, 'fs_offered_date'); ?> /> FS Offered Date</div>
            <div><input type="checkbox" name="columnsToShow[property_geo_risk]" value="property_geo_risk" <?php Helper::checkIfInArray($columnsToShow, 'property_geo_risk'); ?> /> Geo Risk</div>
            <div><input type="checkbox" name="columnsToShow[property_policy]" value="property_policy" <?php Helper::checkIfInArray($columnsToShow, 'property_policy'); ?> /> Policy Number</div>
            <div><input type="checkbox" name="columnsToShow[property_rated_company]" value="property_rated_company" <?php Helper::checkIfInArray($columnsToShow, 'property_rated_company'); ?> /> Rated Company</div>   
            <h4 class="paddingTop10">Contact</h4>
            <div><input type="checkbox" name="columnsToShow[member_home_phone]" value="member_home_phone" <?php Helper::checkIfInArray($columnsToShow, 'member_home_phone'); ?> /> Home Phone</div>
            <div><input type="checkbox" name="columnsToShow[member_work_phone]" value="member_work_phone" <?php Helper::checkIfInArray($columnsToShow, 'member_work_phone'); ?> /> Work Phone</div>
            <div><input type="checkbox" name="columnsToShow[member_cell_phone]" value="member_cell_phone" <?php Helper::checkIfInArray($columnsToShow, 'member_cell_phone'); ?> /> Cell Phone</div>
            <div><input type="checkbox" name="columnsToShow[property_address_line_1]" value="property_address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'property_address_line_1'); ?> /> Property Address Line 1</div>
            <div><input type="checkbox" name="columnsToShow[property_address_line_2]" value="property_address_line_2" <?php Helper::checkIfInArray($columnsToShow, 'property_address_line_2'); ?> /> Property Address Line 2</div>
            <div><input type="checkbox" name="columnsToShow[property_city]" value="property_city" <?php Helper::checkIfInArray($columnsToShow, 'property_city'); ?> /> Property City</div>
            <div><input type="checkbox" name="columnsToShow[property_state]" value="property_state" <?php Helper::checkIfInArray($columnsToShow, 'property_state'); ?> /> Property State</div>
            <div><input type="checkbox" name="columnsToShow[property_zip]" value="property_zip" <?php Helper::checkIfInArray($columnsToShow, 'property_zip'); ?> /> Property Zip</div>
            <div><input type="checkbox" name="columnsToShow[property_county]" value="property_county" <?php Helper::checkIfInArray($columnsToShow, 'property_county'); ?> /> Property County</div>
            <div><input type="checkbox" name="columnsToShow[member_email_1]" value="member_email_1" <?php Helper::checkIfInArray($columnsToShow, 'member_email_1'); ?> /> Email</div>
            <div><input type="checkbox" name="columnsToShow[member_email_2]" value="member_email_2" <?php Helper::checkIfInArray($columnsToShow, 'member_email_2'); ?> /> Alt. Email</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>HA Info</h4>
            <div><input type="checkbox" name="columnsToShow[engine]" value="engine" <?php Helper::checkIfInArray($columnsToShow, 'engine'); ?> /> Engine</div>
            <div><input type="checkbox" name="columnsToShow[ha_time]" value="ha_time" <?php Helper::checkIfInArray($columnsToShow, 'ha_time'); ?> /> HA Time</div>
            <div><input type="checkbox" name="columnsToShow[ha_date]" value="ha_date" <?php Helper::checkIfInArray($columnsToShow, 'ha_date'); ?> /> HA Date</div>
            <div><input type="checkbox" name="columnsToShow[contact_date]" value="contact_date" <?php Helper::checkIfInArray($columnsToShow, 'contact_date'); ?> /> Contact Date</div>
            <div><input type="checkbox" name="columnsToShow[call_list_month]" value="call_list_month" <?php Helper::checkIfInArray($columnsToShow, 'call_list_month'); ?> /> Call List Month</div>
            <div><input type="checkbox" name="columnsToShow[call_list_year]" value="call_list_year" <?php Helper::checkIfInArray($columnsToShow, 'call_list_year'); ?> /> Call List Year</div>
            <div><input type="checkbox" name="columnsToShow[week_to_schedule]" value="week_to_schedule" <?php Helper::checkIfInArray($columnsToShow, 'week_to_schedule'); ?> /> Week To Schedule</div>
            <div><input type="checkbox" name="columnsToShow[completion_date]" value="completion_date" <?php Helper::checkIfInArray($columnsToShow, 'completion_date'); ?> /> Completion Date</div>
            <div><input type="checkbox" name="columnsToShow[wds_ha_writers]" value="wds_ha_writers" <?php Helper::checkIfInArray($columnsToShow, 'wds_ha_writers'); ?> /> Writer</div>
            <div><input type="checkbox" name="columnsToShow[ha_field_assessor]" value="ha_field_assessor" <?php Helper::checkIfInArray($columnsToShow, 'ha_field_assessor'); ?> /> Assessor</div>
            <div><input type="checkbox" name="columnsToShow[fire_review]" value="fire_review" <?php Helper::checkIfInArray($columnsToShow, 'fire_review'); ?> /> Fire Review</div>
            <div><input type="checkbox" name="columnsToShow[received_date_of_list]" value="received_date_of_list" <?php Helper::checkIfInArray($columnsToShow, 'received_date_of_list'); ?> /> List Received Date</div>
            <div><input type="checkbox" name="columnsToShow[assignment_date_start]" value="assignment_date_start" <?php Helper::checkIfInArray($columnsToShow, 'assignment_date_start'); ?> /> Assignment Date</div>
            <div><input type="checkbox" name="columnsToShow[appointmentWithSalutation]" value="appointmentWithSalutation" <?php Helper::checkIfInArray($columnsToShow, 'appointmentWithSalutation'); ?> /> Appointment Info</div>
            <div><input type="checkbox" name="columnsToShow[homeowner_to_be_present]" value="homeowner_to_be_present" <?php Helper::checkIfInArray($columnsToShow, 'homeowner_to_be_present'); ?> /> Homeowner Present</div>
            <div><input type="checkbox" name="columnsToShow[ok_to_do_wo_member_present]" value="ok_to_do_wo_member_present" <?php Helper::checkIfInArray($columnsToShow, 'ok_to_do_wo_member_present'); ?> /> OK w/o</div>
            <div><input type="checkbox" name="columnsToShow[delivery_date]" value="delivery_date" <?php Helper::checkIfInArray($columnsToShow, 'delivery_date'); ?> /> Delivery Date</div>
            <div><input type="checkbox" name="columnsToShow[recommended_actions]" value="recommended_actions" <?php Helper::checkIfInArray($columnsToShow, 'recommended_actions'); ?> /> Recommended Actions</div>
            <div><input type="checkbox" name="columnsToShow[assigned_by]" value="assigned_by" <?php Helper::checkIfInArray($columnsToShow, 'assigned_by'); ?> /> Assigned By</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>FireShield</h4>
            <div><input type="checkbox" name="columnsToShow[fs_offered]" value="fs_offered" <?php Helper::checkIfInArray($columnsToShow, 'fs_offered'); ?> /> Offered</div>
            <div><input type="checkbox" name="columnsToShow[fs_accepted]" value="fs_accepted" <?php Helper::checkIfInArray($columnsToShow, 'fs_accepted'); ?> /> Accepted (verbally)</div>
            <div><input type="checkbox" name="columnsToShow[fs_notes]" value="fs_notes" <?php Helper::checkIfInArray($columnsToShow, 'fs_notes'); ?> /> Notes</div>
            <h4 class="paddingTop10">Follow Up</h4>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_1]" value="follow_up_2_answer_1" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_1'); ?> /> Question 1</div>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_2]" value="follow_up_2_answer_2" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_2'); ?> /> Question 2</div>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_3]" value="follow_up_2_answer_3" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_3'); ?> /> Question 3</div>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_4]" value="follow_up_2_answer_4" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_4'); ?> /> Question 4</div>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_5]" value="follow_up_2_answer_5" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_5'); ?> /> Question 5</div>
            <div><input type="checkbox" name="columnsToShow[followUpAnswer6Combined]" value="followUpAnswer6Combined" <?php Helper::checkIfInArray($columnsToShow, "followUpAnswer6Combined"); ?> /> Question 6</div>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_7]" value="follow_up_2_answer_7" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_7'); ?> /> Question 7</div>
            <div><input type="checkbox" name="columnsToShow[follow_up_2_answer_8]" value="follow_up_2_answer_8" <?php Helper::checkIfInArray($columnsToShow, 'follow_up_2_answer_8'); ?> /> Question 8</div>
            <div><input type="checkbox" name="columnsToShow[point_of_contact]" value="point_of_contact" <?php Helper::checkIfInArray($columnsToShow, 'point_of_contact'); ?> /> Point of contact</div>
            <h4 class="paddingTop10">Call Attempts</h4>
            <div><input type="checkbox" name="columnsToShow[call_attempt_1]" value="call_attempt_1" <?php Helper::checkIfInArray($columnsToShow, 'call_attempt_1'); ?> /> Attempt 1</div>
            <div><input type="checkbox" name="columnsToShow[call_attempt_2]" value="call_attempt_2" <?php Helper::checkIfInArray($columnsToShow, 'call_attempt_2'); ?> /> Attempt 2</div>
            <div><input type="checkbox" name="columnsToShow[call_attempt_3]" value="call_attempt_3" <?php Helper::checkIfInArray($columnsToShow, 'call_attempt_3'); ?> /> Attempt 3</div>
            <div><input type="checkbox" name="columnsToShow[call_attempt_4]" value="call_attempt_4" <?php Helper::checkIfInArray($columnsToShow, 'call_attempt_4'); ?> /> Attempt 4</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>Mail Reporting</h4>
            <div><input type="checkbox" name="columnsToShow[delivery_method]" value="delivery_method" <?php Helper::checkIfInArray($columnsToShow, 'delivery_method'); ?> /> Delivery Method</div>
            <div><input type="checkbox" name="columnsToShow[member_mail_address_line_1]" value="member_mail_address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'member_mail_address_line_1'); ?> /> Mail Address</div>
            <div><input type="checkbox" name="columnsToShow[member_mail_address_line_2]" value="member_mail_address_line_2" <?php Helper::checkIfInArray($columnsToShow, 'member_mail_address_line_2'); ?> /> Mail Address 2</div>
            <div><input type="checkbox" name="columnsToShow[member_mail_city]" value="member_mail_city" <?php Helper::checkIfInArray($columnsToShow, 'member_mail_city'); ?> /> Mail City</div>
            <div><input type="checkbox" name="columnsToShow[member_mail_state]" value="member_mail_state" <?php Helper::checkIfInArray($columnsToShow, 'member_mail_state'); ?> /> Mail State</div>
            <div><input type="checkbox" name="columnsToShow[member_mail_zip]" value="member_mail_zip" <?php Helper::checkIfInArray($columnsToShow, 'member_mail_zip'); ?> /> Mail Zip</div>
            <div><input type="checkbox" name="columnsToShow[call_center_comments]" value="call_center_comments" <?php Helper::checkIfInArray($columnsToShow, 'call_center_comments'); ?> /> Call Center Comments</div>
        </div>
		<div class="floatLeft paddingRight20">
            <h4>Recommended Actions</h4>
			<?php
			foreach(array_keys(PreRisk::model()->getRecActions()) as $rec_action)
			{
				$checked = '';
				if(in_array($rec_action, $columnsToShow))
					$checked = 'checked="checked"';
				echo '<div><input type="checkbox" name="columnsToShow['.$rec_action.']" value="'.$rec_action.'" '.$checked.' />'.PreRisk::model()->getAttributeLabel($rec_action).'</div>';
			}
			?>
        </div>
    </div>
    
	<div class="paddingTop10">
		<label for="pageSize">Items Per Page</label>
		<select name="pageSize">
			<option value="10" <?php if($pageSize == 10){echo 'selected="selected"';} ?> >10</option>
			<option value="25" <?php if($pageSize == 25){echo 'selected="selected"';} ?> >25</option>
			<option value="50" <?php if($pageSize == 50){echo 'selected="selected"';} ?> >50</option>
			<option value="100" <?php if($pageSize == 100){echo 'selected="selected"';} ?> >100</option>
			<option value="200" <?php if($pageSize == 200){echo 'selected="selected"';} ?> >200</option>
			<option value="500" <?php if($pageSize == 500){echo 'selected="selected"';} ?> >500</option>
			<option value="1000" <?php if($pageSize == 1000){echo 'selected="selected"';} ?> >1000</option>
		</select>
	</div>    
    
    <div class="clearfix width100 paddingTop20">
        <div class="floatLeft" style="padding-top: 3px">
            <?php echo CHtml::link('Clear Selections','#',array('class'=>'clear-checked')); ?>
        </div>
        <div class="floatRight">
            <?php echo CHtml::submitButton('Update View', array('name'=>'columnsSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeColumnsToShow', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>
    
	<?php $this->endWidget(); ?>

</div><!-- column-form -->
