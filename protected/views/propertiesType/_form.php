<?php

/* @var $this PropertiesTypeController */
/* @var $model PropertiesType */
/* @var $form CActiveForm */

?>

<div class="form paddingTop20 paddingBottom20">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'properties-type-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div>
		<?php echo $form->labelEx($model,'type'); ?>
		<?php echo $form->textField($model,'type',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($model,'type'); ?>
	</div>

    <div class="buttons marginTop20">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div>