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
    	<?php echo CHtml::link('Default','#defaultView',array('class'=>'default-quick-view')); ?>
    </div>
        
	<div class="columnsToShow clearfix">
        <div class="floatLeft paddingRight20">
            <h4>Member</h4>
            <div><input type="checkbox" name="columnsToShow[mid]" value="mid" <?php Helper::checkIfInArray($columnsToShow, 'mid'); ?> /> ID</div>
            <div><input type="checkbox" name="columnsToShow[client_id]" value="client_id" <?php Helper::checkIfInArray($columnsToShow, 'client_id'); ?> /> Client</div>
            <div><input type="checkbox" name="columnsToShow[type_id]" value="type_id" <?php Helper::checkIfInArray($columnsToShow, 'type_id'); ?> /> Type</div>           
            <div><input type="checkbox" name="columnsToShow[member_num]" value="member_num" <?php Helper::checkIfInArray($columnsToShow, 'member_num'); ?> /> Client Member #</div>
            <div><input type="checkbox" name="columnsToShow[first_name]" value="first_name" <?php Helper::checkIfInArray($columnsToShow, 'first_name'); ?> /> First Name</div>
            <div><input type="checkbox" name="columnsToShow[last_name]" value="last_name" <?php Helper::checkIfInArray($columnsToShow, 'last_name'); ?> /> Last Name</div>
            <h4 class="paddingTop10">Contact Info</h4>
            <div><input type="checkbox" name="columnsToShow[home_phone]" value="home_phone" <?php Helper::checkIfInArray($columnsToShow, 'home_phone'); ?> /> Home Phone</div>
            <div><input type="checkbox" name="columnsToShow[work_phone]" value="work_phone" <?php Helper::checkIfInArray($columnsToShow, 'work_phone'); ?> /> Work Phone</div>
            <div><input type="checkbox" name="columnsToShow[cell_phone]" value="cell_phone" <?php Helper::checkIfInArray($columnsToShow, 'cell_phone'); ?> /> Cell Phone</div>
            <div><input type="checkbox" name="columnsToShow[email_1]" value="email_1" <?php Helper::checkIfInArray($columnsToShow, 'email_1'); ?> /> Email</div>
            <div><input type="checkbox" name="columnsToShow[email_2]" value="email_2" <?php Helper::checkIfInArray($columnsToShow, 'email_2'); ?> /> Secondary Email</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>Mailing Address</h4>
            <div><input type="checkbox" name="columnsToShow[mail_address_line_1]" value="mail_address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'mail_address_line_1'); ?> /> Address Line 1</div>
            <div><input type="checkbox" name="columnsToShow[mail_address_line_2]" value="mail_address_line_2" <?php Helper::checkIfInArray($columnsToShow, 'mail_address_line_2'); ?> /> Address Line 2</div>
            <div><input type="checkbox" name="columnsToShow[mail_city]" value="mail_city" <?php Helper::checkIfInArray($columnsToShow, 'mail_city'); ?> /> City</div>
            <div><input type="checkbox" name="columnsToShow[mail_county]" value="mail_county" <?php Helper::checkIfInArray($columnsToShow, 'mail_county'); ?> /> County</div>
            <div><input type="checkbox" name="columnsToShow[mail_state]" value="mail_state" <?php Helper::checkIfInArray($columnsToShow, 'mail_state'); ?> /> State</div>
            <div><input type="checkbox" name="columnsToShow[mail_zip]" value="mail_zip" <?php Helper::checkIfInArray($columnsToShow, 'mail_zip'); ?> /> Zip</div>
            <h4 class="paddingTop10">Spouse</h4>
            <div><input type="checkbox" name="columnsToShow[spouse_member_num]" value="spouse_member_num" <?php Helper::checkIfInArray($columnsToShow, 'spouse_member_num'); ?> /> Client Member #</div>
            <div><input type="checkbox" name="columnsToShow[spouse_first_name]" value="spouse_first_name" <?php Helper::checkIfInArray($columnsToShow, 'spouse_first_name'); ?> /> First Name</div>
            <div><input type="checkbox" name="columnsToShow[spouse_last_name]" value="spouse_last_name" <?php Helper::checkIfInArray($columnsToShow, 'spouse_last_name'); ?> /> Last Name</div>
        </div>
        <div class="floatLeft">
            <h4>Misc.</h4>
            <div><input type="checkbox" name="columnsToShow[fs_carrier_key]" value="fs_carrier_key" <?php Helper::checkIfInArray($columnsToShow, 'fs_carrier_key'); ?> /> FS Carrier Key</div>
            <div><input type="checkbox" name="columnsToShow[is_tester]" value="is_tester" <?php Helper::checkIfInArray($columnsToShow, 'is_tester'); ?> /> Test Member</div>
            <div><input type="checkbox" name="columnsToShow[mem_fireshield_status]" value="mem_fireshield_status" <?php Helper::checkIfInArray($columnsToShow, 'mem_fireshield_status'); ?> /> FireShield Status</div>
            <div><input type="checkbox" name="columnsToShow[mem_fs_status_date]" value="mem_fs_status_date" <?php Helper::checkIfInArray($columnsToShow, 'mem_fs_status_date'); ?> /> FireShield Status Date</div>
            <div><input type="checkbox" name="columnsToShow[status_override]" value="status_override" <?php Helper::checkIfInArray($columnsToShow, 'status_override'); ?> /> Status Override</div>
        </div>
        
        
	</div>

	<div class="paddingTop10">
		<label for="pageSize">Items Per Page:</label>
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
