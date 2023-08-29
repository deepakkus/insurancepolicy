<div id="shiftTicketGridColumnForm" class="column-form">
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	));
    ?>

	<h3>Columns To Show</h3>
    
    <div class="clearfix">
        <div class="floatLeft paddingRight20">
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[id]" value="id" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'id'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('id') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[date]" value="date" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'date'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('date') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[completedStatuses]" value="completedStatuses" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'completedStatuses'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('completedStatuses') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[submitted_by_user_id]" value="submitted_by_user_id" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'submitted_by_user_id'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('submitted_by_user_id') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[user_id]" value="user_id" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'user_id'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('user_id') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[fire_name]" value="fire_name" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'fire_name'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('fire_name') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[eng_schedule_clients]" value="eng_schedule_clients" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'eng_schedule_clients'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('eng_schedule_clients') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[eng_schedule_assignment]" value="eng_schedule_assignment" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'eng_schedule_assignment'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('eng_schedule_assignment') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[eng_engine_name]" value="eng_engine_name" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'eng_engine_name'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('eng_engine_name') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[eng_schedule_ro]" value="eng_schedule_ro" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'eng_schedule_ro'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('eng_schedule_ro') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[eng_schedule_crew]" value="eng_schedule_crew" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'eng_schedule_crew'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('eng_schedule_crew') ?></div>            
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[activities]" value="activities" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'activities'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('activities') ?></div>
            
            <div><?php echo EngShiftTicket::model()->getAttributeLabel('totalActivityTime');echo EngShiftTicketActivityType::model()->geActivityTimeHTMLList($shiftTicketGridSubColumnsToShow); ?>
            <input type="hidden" name="shiftTicketGridColumnsToShow[totalActivityTime]" value="totalActivityTime" >
            </div>
            
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[start_location]" value="start_location" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'start_location'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('start_location') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[end_location]" value="end_location" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'end_location'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('end_location') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[totalMiles]" value="totalMiles" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'totalMiles'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('totalMiles') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[start_miles]" value="start_miles" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'start_miles'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('start_miles') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[end_miles]" value="end_miles" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'end_miles'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('end_miles') ?></div>
            <div><input type="checkbox" name="shiftTicketGridColumnsToShow[safety_meeting_comments]" value="safety_meeting_comments" <?php Helper::checkIfInArray($shiftTicketGridColumnsToShow, 'safety_meeting_comments'); ?> /> <?php echo EngShiftTicket::model()->getAttributeLabel('safety_meeting_comments') ?></div>
        </div> 
    </div>
	
	<div class="paddingTop10">
		<label for="pageSize">Items Per Page</label>
		<select name="shiftTicketGridPageSize">
			<option value="10" <?php if ($shiftTicketGridPageSize == 10) { echo 'selected="selected"'; } ?> >10</option>
			<option value="25" <?php if ($shiftTicketGridPageSize == 25) { echo 'selected="selected"'; } ?> >25</option>
			<option value="50" <?php if ($shiftTicketGridPageSize == 50) { echo 'selected="selected"'; } ?> >50</option>
			<option value="100" <?php if ($shiftTicketGridPageSize == 100) { echo 'selected="selected"'; } ?> >100</option>
			<option value="200" <?php if ($shiftTicketGridPageSize == 200) { echo 'selected="selected"'; } ?> >200</option>
			<option value="500" <?php if ($shiftTicketGridPageSize == 500) { echo 'selected="selected"'; } ?> >500</option>
			<option value="1000" <?php if ($shiftTicketGridPageSize == 1000) { echo 'selected="selected"'; } ?> >1000</option>
		</select>
	</div>
    
    <div class="clearfix width100 paddingTop20">
        <div class="floatRight">
        	<?php echo CHtml::submitButton('Update Grid', array('id'=>'shiftTicketGridColumnsSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'shiftTicketGridCloseColumnsToShow', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>

	<?php $this->endWidget(); ?>

</div><!-- column-form -->
