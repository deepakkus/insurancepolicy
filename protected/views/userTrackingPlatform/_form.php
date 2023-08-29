<?php

/* @var $this UserTrackingPlatformController */
/* @var $model UserTrackingPlatform */
/* @var $form CActiveForm */

?>

<div class="form paddingTop20 paddingBottom20">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id' => 'user-tracking-platform-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div>
		<?php echo $form->labelEx($model,'platform'); ?>
		<?php echo $form->textField($model,'platform'); ?>
		<?php echo $form->error($model,'platform'); ?>
	</div>

    <div class="buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('/userTrackingPlatform/admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->