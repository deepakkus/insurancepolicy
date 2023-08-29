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
            <h4>PolicyHolder</h4>
            <div><input type="checkbox" name="columnsToShow[member_first_name]" value="member_first_name" <?php Helper::checkIfInArray($columnsToShow, 'member_first_name'); ?> /> First Name</div>
            <div><input type="checkbox" name="columnsToShow[member_last_name]" value="member_last_name" <?php Helper::checkIfInArray($columnsToShow, 'member_last_name'); ?> /> Last Name</div>
            <div><input type="checkbox" name="columnsToShow[client_id]" value="client_id" <?php Helper::checkIfInArray($columnsToShow, 'client_id'); ?> /> Client</div>
            <div><input type="checkbox" name="columnsToShow[member_member_num]" value="member_member_num" <?php Helper::checkIfInArray($columnsToShow, 'member_member_num'); ?> /> PolicyHolder #</div>
            <div><input type="checkbox" name="columnsToShow[member_home_phone]" value="member_home_phone" <?php Helper::checkIfInArray($columnsToShow, 'member_home_phone'); ?> />Home Phone</div>
            <div><input type="checkbox" name="columnsToShow[member_work_phone]" value="member_work_phone" <?php Helper::checkIfInArray($columnsToShow, 'member_work_phone'); ?> />Work Phone</div>
            <div><input type="checkbox" name="columnsToShow[member_cell_phone]" value="member_cell_phone" <?php Helper::checkIfInArray($columnsToShow, 'member_cell_phone'); ?> />Cell Phone</div>
            <div><input type="checkbox" name="columnsToShow[member_email_1]" value="member_email_1" <?php Helper::checkIfInArray($columnsToShow, 'member_email_1'); ?> />Email</div>
            <h4>Policy</h4>
            <div><input type="checkbox" name="columnsToShow[policy]" value="policy" <?php Helper::checkIfInArray($columnsToShow, 'policy'); ?> /> Policy #</div>
            <div><input type="checkbox" name="columnsToShow[location]" value="location" <?php Helper::checkIfInArray($columnsToShow, 'location'); ?> /> Location #</div>
            <div><input type="checkbox" name="columnsToShow[rated_company]" value="rated_company" <?php Helper::checkIfInArray($columnsToShow, 'rated_company'); ?> /> Rated Company</div>
            <div><input type="checkbox" name="columnsToShow[coverage_a_amt]" value="coverage_a_amt" <?php Helper::checkIfInArray($columnsToShow, 'coverage_a_amt'); ?> /> Coverage A Amt</div>
            <div><input type="checkbox" name="columnsToShow[producer]" value="producer" <?php Helper::checkIfInArray($columnsToShow, 'producer'); ?> /> Producer</div>
            <div><input type="checkbox" name="columnsToShow[agency_code]" value="agency_code" <?php Helper::checkIfInArray($columnsToShow, 'agency_code'); ?> /> Agency Code</div>
            <div><input type="checkbox" name="columnsToShow[agency_name]" value="agency_name" <?php Helper::checkIfInArray($columnsToShow, 'agency_name'); ?> /> Agency Name</div>
            <div><input type="checkbox" name="columnsToShow[lob]" value="lob" <?php Helper::checkIfInArray($columnsToShow, 'lob'); ?> /> LOB</div>
            <div><input type="checkbox" name="columnsToShow[rated_company]" value="rated_company" <?php Helper::checkIfInArray($columnsToShow, 'rated_company'); ?> /> Rated Company</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>Property</h4>
            <div><input type="checkbox" name="columnsToShow[address_line_1]" value="address_line_1" <?php Helper::checkIfInArray($columnsToShow, 'address_line_1'); ?> /> Address Line 1</div>
            <div><input type="checkbox" name="columnsToShow[address_line_2]" value="address_line_2" <?php Helper::checkIfInArray($columnsToShow, 'address_line_2'); ?> /> Address Line 2</div>             
            <div><input type="checkbox" name="columnsToShow[city]" value="city" <?php Helper::checkIfInArray($columnsToShow, 'city'); ?> /> City</div>            
            <div><input type="checkbox" name="columnsToShow[state]" value="state" <?php Helper::checkIfInArray($columnsToShow, 'state'); ?> /> State</div>
            <div><input type="checkbox" name="columnsToShow[county]" value="county" <?php Helper::checkIfInArray($columnsToShow, 'county'); ?> /> County</div> 
            <div><input type="checkbox" name="columnsToShow[zip]" value="zip" <?php Helper::checkIfInArray($columnsToShow, 'zip'); ?> /> Zip</div>  
            <div><input type="checkbox" name="columnsToShow[zip_supp]" value="zip_supp" <?php Helper::checkIfInArray($columnsToShow, 'zip_supp'); ?> /> Zip Supp</div>
            <div><input type="checkbox" name="columnsToShow[lat]" value="lat" <?php Helper::checkIfInArray($columnsToShow, 'Lat'); ?> /> Lat</div>  
            <div><input type="checkbox" name="columnsToShow[long]" value="long" <?php Helper::checkIfInArray($columnsToShow, 'Long'); ?> /> Long</div>
            <div><input type="checkbox" name="columnsToShow[geocode_level]" value="geocode_level" <?php Helper::checkIfInArray($columnsToShow, 'geocode_level'); ?> /> GeoCode Level</div>              
            <div><input type="checkbox" name="columnsToShow[geo_risk]" value="geo_risk" <?php Helper::checkIfInArray($columnsToShow, 'geo_risk'); ?> /> GeoRisk</div>
            <div><input type="checkbox" name="columnsToShow[dwelling_type]" value="dwelling_type" <?php Helper::checkIfInArray($columnsToShow, 'dwelling_type'); ?> /> Dwelling Type</div>
            <div><input type="checkbox" name="columnsToShow[multi_family]" value="multi_family" <?php Helper::checkIfInArray($columnsToShow, 'multi_family'); ?> /> Multi-Family</div>
            <div><input type="checkbox" name="columnsToShow[roof_type]" value="roof_type" <?php Helper::checkIfInArray($columnsToShow, 'roof_type'); ?> /> Roof Type</div>  
            <div><input type="checkbox" name="columnsToShow[comments]" value="comments" <?php Helper::checkIfInArray($columnsToShow, 'comments'); ?> /> Comments</div>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>Program Statuses & Dates</h4>
            <div><input type="checkbox" name="columnsToShow[policy_status]" value="policy_status" <?php Helper::checkIfInArray($columnsToShow, 'policy_status'); ?> /> Policy Status</div>
            <div><input type="checkbox" name="columnsToShow[policy_status_date]" value="policy_status_date" <?php Helper::checkIfInArray($columnsToShow, 'policy_status_date'); ?> /> Policy Status Date</div>
            <div><input type="checkbox" name="columnsToShow[policy_effective]" value="policy_effective" <?php Helper::checkIfInArray($columnsToShow, 'policy_effective'); ?> /> Policy Effective Date</div>
            <div><input type="checkbox" name="columnsToShow[policy_expiration]" value="policy_expiration" <?php Helper::checkIfInArray($columnsToShow, 'policy_expiration'); ?> /> Policy Expiration Date</div>
            <div><input type="checkbox" name="columnsToShow[response_status]" value="response_status" <?php Helper::checkIfInArray($columnsToShow, 'response_status'); ?> /> Response Program Status</div>
            <div><input type="checkbox" name="columnsToShow[res_status_date]" value="res_status_date" <?php Helper::checkIfInArray($columnsToShow, 'res_status_date'); ?> /> Response Status Date</div>
            <div><input type="checkbox" name="columnsToShow[response_enrolled_date]" value="response_enrolled_date" <?php Helper::checkIfInArray($columnsToShow, 'response_enrolled_date'); ?> /> Response Enrolled Date</div>
            <div><input type="checkbox" name="columnsToShow[response_auto_enrolled]" value="response_auto_enrolled" <?php Helper::checkIfInArray($columnsToShow, 'response_auto_enrolled'); ?> /> Response Auto Enrolled</div>
            <div><input type="checkbox" name="columnsToShow[fireshield_status]" value="fireshield_status" <?php Helper::checkIfInArray($columnsToShow, 'fireshield_status'); ?> /> FireShield Program Status</div>
            <div><input type="checkbox" name="columnsToShow[fs_status_date]" value="fs_status_date" <?php Helper::checkIfInArray($columnsToShow, 'fs_status_date'); ?> /> FS Status Date</div>
            <div><input type="checkbox" name="columnsToShow[pre_risk_status]" value="pre_risk_status" <?php Helper::checkIfInArray($columnsToShow, 'pre_risk_status'); ?> /> PreRisk Program Status</div>
            <div><input type="checkbox" name="columnsToShow[pr_status_date]" value="pr_status_date" <?php Helper::checkIfInArray($columnsToShow, 'pr_status_date'); ?> /> PR Status Date</div>            
        </div>
        <div class="floatLeft">
            <h4>App</h4>
            <div><input type="checkbox" name="columnsToShow[app_status]" value="app_status" <?php Helper::checkIfInArray($columnsToShow, 'app_status'); ?> /> App Status</div>
            <div><input type="checkbox" name="columnsToShow[fs_assessments_allowed]" value="fs_assessments_allowed" <?php Helper::checkIfInArray($columnsToShow, 'fs_assessments_allowed'); ?> /> FS Assessments Allowed</div>
            <div><input type="checkbox" name="columnsToShow[agent_id]" value="agent_id" <?php Helper::checkIfInArray($columnsToShow, 'agent_id'); ?> /> Agent ID</div>
            <div><input type="checkbox" name="columnsToShow[question_set_id]" value="question_set_id" <?php Helper::checkIfInArray($columnsToShow, 'question_set_id'); ?> /> Question Set ID</div>
            <div><input type="checkbox" name="columnsToShow[member_fs_carrier_key]" value="member_fs_carrier_key" <?php Helper::checkIfInArray($columnsToShow, 'member_fs_carrier_key'); ?> /> App Registration Code</div>
            <h4>WDS Values</h4>
            <div><input type="checkbox" name="columnsToShow[wds_geocode_level]" value="wds_geocode_level" <?php Helper::checkIfInArray($columnsToShow, 'wds_geocode_level'); ?> /> WDS Geocode Level</div>
            <div><input type="checkbox" name="columnsToShow[wds_geocoder]" value="wds_geocoder" <?php Helper::checkIfInArray($columnsToShow, 'wds_geocoder'); ?> /> WDS Geocoder</div>
            <div><input type="checkbox" name="columnsToShow[wds_match_address]" value="wds_match_address" <?php Helper::checkIfInArray($columnsToShow, 'wds_match_address'); ?> /> WDS Match Address</div>
            <div><input type="checkbox" name="columnsToShow[wds_match_score]" value="wds_match_score" <?php Helper::checkIfInArray($columnsToShow, 'wds_match_score'); ?> /> WDS Match Score</div>
            <div><input type="checkbox" name="columnsToShow[wds_geocode_date]" value="wds_geocode_date" <?php Helper::checkIfInArray($columnsToShow, 'wds_geocode_date'); ?> /> WDS Geocode Date</div>
            <div><input type="checkbox" name="columnsToShow[wds_lat]" value="wds_lat" <?php Helper::checkIfInArray($columnsToShow, 'wds_lat'); ?> /> WDS Lat</div>
            <div><input type="checkbox" name="columnsToShow[wds_long]" value="wds_long" <?php Helper::checkIfInArray($columnsToShow, 'wds_long'); ?> /> WDS Long</div>
            <div><input type="checkbox" name="columnsToShow[wds_risk]" value="wds_risk" <?php Helper::checkIfInArray($columnsToShow, 'wds_risk'); ?> /> WDS Risk</div>
            <div><input type="checkbox" name="columnsToShow[wds_lob]" value="wds_lob" <?php Helper::checkIfInArray($columnsToShow, 'wds_lob'); ?> /> WDS LOB</div>
        </div>
        <div class="floatLeft">
            <h4>Admin</h4>
            <div><input type="checkbox" name="columnsToShow[member_mid]" value="member_mid" <?php Helper::checkIfInArray($columnsToShow, 'member_mid'); ?> /> MID</div>
            <div><input type="checkbox" name="columnsToShow[pid]" value="pid" <?php Helper::checkIfInArray($columnsToShow, 'pid'); ?> /> PID</div>
            <div><input type="checkbox" name="columnsToShow[type_id]" value="type_id" <?php Helper::checkIfInArray($columnsToShow, 'type_id'); ?> /> Property Type</div>
            <div><input type="checkbox" name="columnsToShow[last_update]" value="last_update" <?php Helper::checkIfInArray($columnsToShow, 'last_update'); ?> /> Last Update</div>
            <div><input type="checkbox" name="columnsToShow[flag]" value="flag" <?php Helper::checkIfInArray($columnsToShow, 'flag'); ?> /> Flag</div>
            <div><input type="checkbox" name="columnsToShow[transaction_type]" value="transaction_type" <?php Helper::checkIfInArray($columnsToShow, 'transaction_type'); ?> /> Transaction Type</div>
            <div><input type="checkbox" name="columnsToShow[transaction_effective]" value="transaction_effective" <?php Helper::checkIfInArray($columnsToShow, 'transaction_effective'); ?> /> Transaction Effective Date</div>
            <h4>Property Access</h4>
            <div><input type="checkbox" name="columnsToShow[property_access_gate_code]" value="property_access_gate_code" <?php Helper::checkIfInArray($columnsToShow, 'property_access_gate_code'); ?> /> Gate Code</div>
        </div>
    </div>
	
	<div class="paddingTop10">
		<label for="pageSize">Items Per Page</label>
		<select name="pageSize">
			<option value="10" <?php if ($pageSize == 10) { echo 'selected="selected"'; } ?> >10</option>
			<option value="25" <?php if ($pageSize == 25) { echo 'selected="selected"'; } ?> >25</option>
			<option value="50" <?php if ($pageSize == 50) { echo 'selected="selected"'; } ?> >50</option>
			<option value="100" <?php if ($pageSize == 100) { echo 'selected="selected"'; } ?> >100</option>
			<option value="200" <?php if ($pageSize == 200) { echo 'selected="selected"'; } ?> >200</option>
			<option value="500" <?php if ($pageSize == 500) { echo 'selected="selected"'; } ?> >500</option>
			<option value="1000" <?php if ($pageSize == 1000) { echo 'selected="selected"'; } ?> >1000</option>
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
