<?php
/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */
/* @var $form CActiveForm */

$this->breadcrumbs = array(
	'Risk Batch' => array('/riskBatch/admin'),
    'Run PIF'
);
?>

<h1>Load new batch run for risk processing (PIF)</h1>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'risk-batch-pif-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div>
		<?php echo $form->labelEx($model,'file_name'); ?>
		<?php echo '<span class="marginRight10"></span>' . $form->textField($model,'file_name',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'file_name'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'client_id'); ?>
		<?php echo $form->dropDownList($model,'client_id', $model->getRiskClients(), array('prompt' => 'WDS')); ?>
		<?php echo $form->error($model,'client_id'); ?>
	</div>

    <div class="buttons">
		<?php echo CHtml::submitButton('Run Risk', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div>

