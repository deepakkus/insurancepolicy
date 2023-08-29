<?php
/* @var $this Oa2TokensController */
/* @var $model Oa2Tokens */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'oa2-tokens-form', 
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>true,
)); ?>

    <div class="container-fluid">

        <div class="row-fluid">

	        <p class="note">Fields with <span class="required">*</span> are required.</p>

	        <?php echo $form->errorSummary($model); ?>

	        <div class="row">
		        <?php echo $form->labelEx($model,'oauth_token'); ?>
		        <?php echo $form->textField($model,'oauth_token',array('size'=>40,'maxlength'=>40)); ?>
		        <?php echo $form->error($model,'oauth_token'); ?>
	        </div>

	        <div class="row">
		        <?php echo $form->labelEx($model,'client_id'); ?>
		        <?php echo $form->textField($model,'client_id',array('size'=>20,'maxlength'=>20)); ?>
		        <?php echo $form->error($model,'client_id'); ?>
	        </div>

            <div class="row">
                <?php echo $form->labelEx($model,'expires'); ?>
                <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'expires',
                        'options' => array(
                            'dateFormat' => 'yy-mm-dd'
                        ),
                        'htmlOptions' => array(
                            'size' => '10',
                            'maxlength' => '10'
                        )
                )); ?>
                <?php echo $form->error($model,'expires'); ?>
            </div>

	        <div class="row">
		        <?php echo $form->labelEx($model,'scope'); ?>
		        <?php echo $form->textField($model,'scope',array('size'=>60,'maxlength'=>200)); ?>
		        <?php echo $form->error($model,'scope'); ?>
	        </div>

	        <div class="row">
		        <?php echo $form->labelEx($model,'type'); ?>
		        <?php echo $form->textField($model,'type',array('size'=>50,'maxlength'=>50)); ?>
		        <?php echo $form->error($model,'type'); ?>
	        </div>

	        <div class="row buttons">
		        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                <span class="paddingLeft10">
                    <?php echo CHtml::link('Cancel', array('oa2Tokens/admin')); ?>
                </span>
	        </div>

        </div>

    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->