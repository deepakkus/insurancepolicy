<?php
/* @var $this AllianceController */
/* @var $model Alliance */
/* @var $form CActiveForm */
?>

<div class="form paddingTop20 paddingBottom20">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'alliance-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4">
                <div class ="form-section">

	                <div>
		                <?php echo $form->labelEx($model,'name'); ?>
		                <?php echo $form->textField($model,'name',array('size'=>40,'maxlength'=>40)); ?>
		                <?php echo $form->error($model,'name'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'contact_first'); ?>
		                <?php echo $form->textField($model,'contact_first',array('size'=>20,'maxlength'=>20)); ?>
		                <?php echo $form->error($model,'contact_first'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'contact_last'); ?>
		                <?php echo $form->textField($model,'contact_last',array('size'=>20,'maxlength'=>20)); ?>
		                <?php echo $form->error($model,'contact_last'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'phone'); ?>
		                <?php echo $form->telField($model,'phone',array('size'=>20,'maxlength'=>20,'placeholder'=>'xxx-xxx-xxxx','pattern'=>'\d{3}-\d{3}-\d{4}')); ?>
		                <?php echo $form->error($model,'phone'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'phone_alt'); ?>
		                <?php echo $form->telField($model,'phone_alt',array('size'=>20,'maxlength'=>20,'placeholder'=>'xxx-xxx-xxxx','pattern'=>'\d{3}-\d{3}-\d{4}')); ?>
		                <?php echo $form->error($model,'phone_alt'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'email'); ?>
		                <?php echo $form->emailField($model,'email',array('size'=>50,'maxlength'=>50,'placeholder'=>'user@mydomain.com')); ?>
		                <?php echo $form->error($model,'email'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'preseason_agreement'); ?>
		                <?php echo $form->textField($model,'preseason_agreement',array('size'=>10,'maxlength'=>10)); ?>
		                <?php echo $form->error($model,'preseason_agreement'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'email_reminder'); ?>
		                <?php echo $form->checkBox($model,'email_reminder'); ?>
		                <?php echo $form->error($model,'email_reminder'); ?>
	                </div>

                    <div>
		                <?php echo $form->labelEx($model,'active'); ?>
		                <?php echo $form->checkBox($model,'active'); ?>
		                <?php echo $form->error($model,'active'); ?>
	                </div>

	                <div class="buttons">
		                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                        <span class="paddingLeft10">
                            <?php echo CHtml::link('Cancel', array('admin')); ?>
                        </span>
	                </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->