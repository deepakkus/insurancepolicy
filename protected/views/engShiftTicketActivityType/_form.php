<?php
/* @var EngShiftTicketActivityTypeController $this  */
/* @var EngShiftTicketActivityType $engShiftTicketActivityType */
/* @var CActiveForm $form  */
?>

<div class="form">

<?php 
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'eng-st-activity-type-form'
));

echo $form->errorSummary($engShiftTicketActivityType); 
?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4">
                <div class ="form-section">
	                <div>
		                <?php echo $form->labelEx($engShiftTicketActivityType,'type'); ?>
		                <?php echo $form->textField($engShiftTicketActivityType,'type',array('size'=>20,'maxlength'=>50)); ?>
		                <?php echo $form->error($engShiftTicketActivityType,'type'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($engShiftTicketActivityType,'description'); ?>
		                <?php echo $form->textArea($engShiftTicketActivityType,'description',array('rows'=>5,'style'=>'width:400px;','maxlength'=>500)); ?>
		                <?php echo $form->error($engShiftTicketActivityType,'description'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($engShiftTicketActivityType,'active'); ?>
		                <?php echo $form->checkBox($engShiftTicketActivityType,'active'); ?>
		                <?php echo $form->error($engShiftTicketActivityType,'active'); ?>
	                </div>
                </div>
            </div>
        </div>  
        <div class="row-fluid">
            <div class="span12">
                <div class ="form-section">
	                <div class="buttons">
		                <?php echo CHtml::submitButton($engShiftTicketActivityType->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                        <span class="paddingLeft10">
                            <?php echo CHtml::link('Cancel', array('admin')); ?>
                        </span>
	                </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div>
