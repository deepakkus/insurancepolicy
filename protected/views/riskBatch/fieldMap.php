<?php

/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */

$this->breadcrumbs = array(
	'Risk Batch' => array('/riskBatch/admin'),
    'Field Mapping'
);

?>

<h1>Step 1: Field Mapping For CSV</h1>

<div class="form" id ="field-map" style="background-color:#FFFFFF; border: 0;">
    <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	    'id'=>'eng-analytics-form'
    )); ?>

    <div>
        <?php echo CHtml::label('First Name', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[first_name]', '59', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('Last Name', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[last_name]', '60', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('Address', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[address]', '39', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('City', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[city]', '40', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('State', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[state]', '42', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('Zip', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[zip]', '43', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('Client Property ID or Policy Number (optional)', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[client_property_id]', '36', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('Client Member ID or Member Number(optional)', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[client_member_id]', '45', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('WDS PID (optional)', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[property_pid]', '44', $headerFields, array('prompt' => '')); ?>
    </div>

    <p class="marginTop20 marginBottom20">
        <b>If coordinates are mapped, no geocoding will occur.</b>
    </p>

    <div>
        <?php echo CHtml::label('Latitude', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[lat]', '23', $headerFields, array('prompt' => '')); ?>
    </div>

    <div>
        <?php echo CHtml::label('Longitude', ''); ?>
        <?php echo CHtml::dropDownList('FieldMap[long]', '22', $headerFields, array('prompt' => '')); ?>
    </div>

    <div class="buttons" id="button-row">
        <?php echo CHtml::submitButton('Import CSV', array('class'=>'submit')); ?>
    </div>

    <?php $this->endWidget(); ?>
</div>
<script>
    // Add progress bar when submitted
    $('#field-map').submit(function () {
        var submitButton = this.querySelector('input[type="submit"]');
        $('#button-row').empty();
        $('#button-row').html(
            '<div style="width: 300px; margin: 5px 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">' +
                '<div class="bar" style="width: 100%;margin:0;"></div>' +
            '</div>');
    });
</script>


