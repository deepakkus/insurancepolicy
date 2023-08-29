<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'fs-report-text-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($fsReportText); ?>

	<div>
		<?php echo $form->labelEx($fsReportText,'condition_num'); ?>
		<?php echo $form->dropDownList($fsReportText,'condition_num',array_combine(range(1,15), range(1,15)), array('empty'=>'')); ?>
		<?php echo $form->error($fsReportText,'condition_num'); ?>
	</div>
	
	<div>
		<?php echo $form->labelEx($fsReportText,'response'); ?>
		<?php echo $form->dropDownList($fsReportText,'response',array('Yes' => 'Yes', 'No' => 'No', 'Yes,No' => 'Yes,No'), array('empty'=>'')); ?>
		<?php echo $form->error($fsReportText,'response'); ?>
	</div>
	
		<div>
		<?php echo $form->labelEx($fsReportText,'risk_level'); ?>
		<?php echo $form->dropDownList($fsReportText,'risk_level',array('1' => '1', '2,3' => '2,3', '1,2,3' => '1,2,3'), array('empty'=>'')); ?>
		<?php echo $form->error($fsReportText,'risk_level'); ?>
	</div>
	
	<div>
		<?php echo $form->labelEx($fsReportText,'type'); ?>
		<?php echo $form->dropDownList($fsReportText,'type',$fsReportText->getTypes(), array('empty'=>'')); ?>
		<?php echo $form->error($fsReportText,'type'); ?>
	</div>
	
	<div>
		<?php echo $form->labelEx($fsReportText,'text'); ?>
		<?php echo $form->textArea($fsReportText,'text',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($fsReportText,'text'); ?>
	</div>

	<div class="buttons">
		<?php echo CHtml::submitButton($fsReportText->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->