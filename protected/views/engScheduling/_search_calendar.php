<?php

/* @var $this EngSchedulingController */
/* @var $model EngScheduling */

$assignmentsChecked = isset($_SESSION['wds_engines_calendar_searchAttr']) ? $_SESSION['wds_engines_calendar_searchAttr']->assignments : null;
$clientsChecked = isset($_SESSION['wds_engines_calendar_searchAttr']) ? $_SESSION['wds_engines_calendar_searchAttr']->clients : null;

?>

<div style="margin: 10px; width: 300px;">

    <div class="clearfix">
        <div class="floatLeft marginRight20">
            <h4>Assignments</h4>
            <div class="compactRadioGroup">
                <?php echo CHtml::checkBoxList('engine-assignments', $assignmentsChecked, $model->getEngineAssignments(), array('class'=>'engine-assignments-checkbox')); ?>
            </div>
        </div>

        <div class="floatLeft">
            <h4>Clients</h4>
            <div class="compactRadioGroup">
                <?php echo CHtml::checkBoxList('engine-clients', $clientsChecked, CHtml::listData($model->getAvailibleFireClients(), 'id', 'name'), array('class'=>'engine-clients-checkbox')); ?>
            </div>
        </div>
    </div>

    <div class="paddingTop10">
            <?php echo CHtml::link('Clear Selections','#',array('class'=>'clear-checked')); ?>
        <div class="marginTop20">
        	<?php echo CHtml::button('Update View', array('name'=>'calendarSearchSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::button('Close', array('id' => 'closeCalendarSearch', 'class' => 'submitButton')); ?>
        </div>
    </div>

</div>