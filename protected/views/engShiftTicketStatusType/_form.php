<?php
/* @var $this EngShiftTicketStatusTypeController */
/* @var model $shiftTicketStatusType */
/* @var $form CActiveForm */

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'shift-ticket-review-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

echo $form->textFieldRow($shiftTicketStatusType, 'type', array(
    'size' => 20,
    'maxlength' => 50,
    'placeholder' => 'Status Type'
));

echo $form->numberFieldRow($shiftTicketStatusType, 'order', array(
    'min' => 0,
    'placeholder' => 'Order'
));

echo $form->checkBoxRow($shiftTicketStatusType, 'disabled');

?>

<div class="buttons">
	<?php echo CHtml::submitButton($shiftTicketStatusType->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
    <span class="paddingLeft10">
        <?php echo CHtml::link('Cancel', array('admin')); ?>
    </span>
</div>

<?php

$this->endWidget();
