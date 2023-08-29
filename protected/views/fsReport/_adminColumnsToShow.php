<div class="column-form">
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	));
	?>

	<h3>Columns To Show</h3>

    <div class="paddingTop10 paddingBottom10">
        Quick Views: 
        <?php echo CHtml::link('Default', '#defaultView', array('class' => 'default-quick-view')); ?>
    </div>

    <div class="columnsToShow clearfix">
        <div class="floatLeft paddingRight20">
            <h4>Report Info</h4>
            <div><input type="checkbox" name="columnsToShow[id]" value="id" <?php Helper::checkIfInArray($columnsToShow, 'id'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[report_guid]" value="report_guid" <?php Helper::checkIfInArray($columnsToShow, 'report_guid'); ?> /> GUID</div>
            <div><input type="checkbox" name="columnsToShow[type]" value="type" <?php Helper::checkIfInArray($columnsToShow, 'type'); ?> /> Type</div>
            <div><input type="checkbox" name="columnsToShow[status]" value="status" <?php Helper::checkIfInArray($columnsToShow, 'status'); ?> /> Status</div>
            <div><input type="checkbox" name="columnsToShow[status_date]" value="status_date" <?php Helper::checkIfInArray($columnsToShow, 'status_date'); ?> /> Status Date</div>
            <div><input type="checkbox" name="columnsToShow[assigned_user_name]" value="assigned_user_name" <?php Helper::checkIfInArray($columnsToShow, 'assigned_user_name'); ?> /> Assigned To</div>
            <div><input type="checkbox" name="columnsToShow[address_line_1]" value="address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'address_line_1'); ?> /> Address Line 1</div>
            <div><input type="checkbox" name="columnsToShow[city]" value="city" <?php Helper::checkIfInArray($columnsToShow, 'city'); ?> /> City</div>
            <div><input type="checkbox" name="columnsToShow[state]" value="state" <?php Helper::checkIfInArray($columnsToShow, 'state'); ?> /> State</div>
            <div><input type="checkbox" name="columnsToShow[submit_date]" value="submit_date" <?php Helper::checkIfInArray($columnsToShow, 'submit_date'); ?> /> Submit Date</div>
            <div><input type="checkbox" name="columnsToShow[due_date]" value="due_date" <?php Helper::checkIfInArray($columnsToShow, 'due_date'); ?> /> Due Date</div>
            <div><input type="checkbox" name="columnsToShow[scheduled_call]" value="scheduled_call" <?php Helper::checkIfInArray($columnsToShow, 'scheduled_call'); ?> /> Scheduled Call</div>
            <div><input type="checkbox" name="columnsToShow[condition_risk]" value="condition_risk" <?php Helper::checkIfInArray($columnsToShow, 'condition_risk'); ?> /> Site Risk</div>
            <div><input type="checkbox" name="columnsToShow[geo_risk]" value="geo_risk" <?php Helper::checkIfInArray($columnsToShow, 'geo_risk'); ?> /> Geo Risk</div>
            <div><input type="checkbox" name="columnsToShow[risk_level]" value="risk_level" <?php Helper::checkIfInArray($columnsToShow, 'risk_level'); ?> /> LOS</div>
            <div><input type="checkbox" name="columnsToShow[completeDate]" value="completeDate" <?php Helper::checkIfInArray($columnsToShow, 'completeDate'); ?> /> Complete Date</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>App User Info</h4>
            <div><input type="checkbox" name="columnsToShow[fs_user_id]" value="fs_user_id" <?php Helper::checkIfInArray($columnsToShow, 'fs_user_id'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[fs_user_email]" value="fs_user_email" <?php Helper::checkIfInArray($columnsToShow, 'fs_user_email'); ?> /> Email</div>
            <div><input type="checkbox" name="columnsToShow[fs_user_type]" value="fs_user_type" <?php Helper::checkIfInArray($columnsToShow, 'fs_user_type'); ?> /> Type</div>
            <div><input type="checkbox" name="columnsToShow[fs_user_name]" value="fs_user_name" <?php Helper::checkIfInArray($columnsToShow, 'fs_user_name'); ?> /> Name</div>
            <div><input type="checkbox" name="columnsToShow[fs_user_client_name]" value="fs_user_client_name" <?php Helper::checkIfInArray($columnsToShow, 'fs_user_client_name'); ?> /> Client</div>
        </div>       
        <div class="floatLeft paddingRight20">
            <h4>Member Info</h4>
            <div><input type="checkbox" name="columnsToShow[member_mid]" value="member_mid" <?php Helper::checkIfInArray($columnsToShow, 'member_mid'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[member_first_name]" value="member_first_name" <?php Helper::checkIfInArray($columnsToShow, 'member_first_name'); ?> /> First Name</div>
            <div><input type="checkbox" name="columnsToShow[member_last_name]" value="member_last_name" <?php Helper::checkIfInArray($columnsToShow, 'member_last_name'); ?> /> Last Name</div>
            <div><input type="checkbox" name="columnsToShow[member_client]" value="member_client" <?php Helper::checkIfInArray($columnsToShow, 'member_client'); ?> /> Client</div>
            <div><input type="checkbox" name="columnsToShow[member_member_num]" value="member_member_num" <?php Helper::checkIfInArray($columnsToShow, 'member_member_num'); ?> /> Client #</div>
            <div><input type="checkbox" name="columnsToShow[member_is_tester]" value="member_is_tester" <?php Helper::checkIfInArray($columnsToShow, 'member_is_tester'); ?> /> Test Member</div>
        </div>        
        <div class="floatLeft">
            <h4>Property Info</h4>
            <div><input type="checkbox" name="columnsToShow[property_pid]" value="property_pid" <?php Helper::checkIfInArray($columnsToShow, 'property_pid'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[property_address_line_1]" value="property_address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'property_address_line_1'); ?> /> Address</div>
            <div><input type="checkbox" name="columnsToShow[property_city]" value="property_city" <?php Helper::checkIfInArray($columnsToShow, 'property_city'); ?> /> City</div>
            <div><input type="checkbox" name="columnsToShow[property_state]" value="property_state" <?php Helper::checkIfInArray($columnsToShow, 'property_state'); ?> /> State</div>
            <div><input type="checkbox" name="columnsToShow[property_policy]" value="property_policy" <?php Helper::checkIfInArray($columnsToShow, 'property_policy'); ?> /> Policy</div>
            <div><input type="checkbox" name="columnsToShow[property_geo_risk]" value="property_geo_risk" <?php Helper::checkIfInArray($columnsToShow, 'property_geo_risk'); ?> /> GeoRisk</div>
            <div><input type="checkbox" name="columnsToShow[property_fsOfferedDate]" value="property_fsOfferedDate" <?php Helper::checkIfInArray($columnsToShow, 'property_fsOfferedDate'); ?> /> Offered Date</div>
        </div>
		<?php if(empty($advSearch['types']) || $advSearch['types'] == 'agent'): ?>
        <div class="floatLeft">
            <h4>Pre Risk Info</h4>
            <div><input type="checkbox" name="columnsToShow[pre_risk_id]" value="pre_risk_id" <?php Helper::checkIfInArray($columnsToShow, 'pre_risk_id'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[pre_risk_ha_date]" value="pre_risk_ha_date" <?php Helper::checkIfInArray($columnsToShow, 'pre_risk_ha_date'); ?> /> HA Date</div>
        </div>
		<div class="floatLeft paddingRight20">
			<h4>Agent Info</h4>
			<div><input type="checkbox" name="columnsToShow[agent_id]" value="agent_id" <?php Helper::checkIfInArray($columnsToShow, 'agent_id'); ?> /> ID</div>
			<div><input type="checkbox" name="columnsToShow[agent_first_name]" value="agent_first_name" <?php Helper::checkIfInArray($columnsToShow, 'agent_first_name'); ?> /> First Name</div>
			<div><input type="checkbox" name="columnsToShow[agent_last_name]" value="agent_last_name" <?php Helper::checkIfInArray($columnsToShow, 'agent_last_name'); ?> /> Last Name</div>
			<div><input type="checkbox" name="columnsToShow[agent_agent_num]" value="agent_agent_num" <?php Helper::checkIfInArray($columnsToShow, 'agent_agent_num'); ?> /> Agent #</div>
			<div><input type="checkbox" name="columnsToShow[client_id]" value="client_id" <?php Helper::checkIfInArray($columnsToShow, 'client_id'); ?> /> Client ID</div>
			<div><input type="checkbox" name="columnsToShow[client_name]" value="client_name" <?php Helper::checkIfInArray($columnsToShow, 'client_name'); ?> /> Client Name</div>
		</div>
		<div class="floatLeft">
            <h4>Agent Property Info</h4>
            <div><input type="checkbox" name="columnsToShow[agent_property_id]" value="agent_property_id" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_id'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[agent_property_address_line_1]" value="agent_property_address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_address_line_1'); ?> /> Address</div>
            <div><input type="checkbox" name="columnsToShow[agent_property_city]" value="agent_property_city" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_city'); ?> /> City</div>
            <div><input type="checkbox" name="columnsToShow[agent_property_state]" value="agent_property_state" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_state'); ?> /> State</div>
            <div><input type="checkbox" name="columnsToShow[agent_property_property_value]" value="agent_property_property_value" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_property_value'); ?> /> Value</div>
            <div><input type="checkbox" name="columnsToShow[agent_property_geo_risk]" value="agent_property_geo_risk" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_geo_risk'); ?> /> GeoRisk</div>
            <div><input type="checkbox" name="columnsToShow[agent_property_work_order_num]" value="agent_property_work_order_num" <?php Helper::checkIfInArray($columnsToShow, 'agent_property_work_order_num'); ?> /> Work Order #</div>
        </div>
		<?php endif; ?>
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
