<?php
/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'risk-batch-file-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div>
		<?php echo $form->labelEx($model,'file_name'); ?>
        <?php echo $form->textField($model,'file_name',array('size'=>50,'maxlength'=>50)) . ' <span><i>(include .csv extension)</i></span>'; ?>
		<?php echo $form->error($model,'file_name'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'client_id'); ?>
		<?php echo $form->dropDownList($model,'client_id', $model->getRiskClients(), array('prompt' => 'WDS')); ?>
		<?php echo $form->error($model,'client_id'); ?>
	</div>

    <div class="buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div>

