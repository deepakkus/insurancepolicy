<?php
/* @var $this ResDedicatedAgencyController */
/* @var $model ResDedicatedAgency */
/* @var $form CActiveForm */
?>

<div class="form" style="border: none; background-color: inherit;">

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'res-dedicated-agency-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">

	            <div>
		            <?php echo $form->labelEx($model,'name'); ?>
		            <?php echo $form->textField($model,'name',array('size'=>50,'maxlength'=>100)); ?>
		            <?php echo $form->error($model,'name'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'address'); ?>
		            <?php echo $form->textField($model,'address',array('size'=>50,'maxlength'=>100)); ?>
		            <?php echo $form->error($model,'address'); ?>
	            </div>

                <div>
	                <div class="clearfix">
		                <?php echo $form->labelEx($model,'city'); ?>
		                <?php echo $form->textField($model,'city',array('size'=>50,'maxlength'=>50)); ?>
		                <?php echo $form->error($model,'city'); ?>
	                </div>

	                <div class="clearfix">
		                <?php echo $form->labelEx($model,'state'); ?>
                        <?php echo $form->dropDownList($model,'state', Helper::getStates(), array('style'=>'width:100px;', 'prompt'=>'')); ?>
		                <?php echo $form->error($model,'state'); ?>
	                </div>
                </div>

	            <div>
		            <?php echo $form->labelEx($model,'contact_name'); ?>
		            <?php echo $form->textField($model,'contact_name',array('size'=>50,'maxlength'=>100)); ?>
		            <?php echo $form->error($model,'contact_name'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'contact_phone_1'); ?>
                    <?php echo $form->telField($model,'contact_phone_1',array('size'=>50,'maxlength'=>50,'placeholder'=>'xxx-xxx-xxxx ext xx...','pattern'=>'\d{3}-\d{3}-\d{4}(\sext\s\d+)?')); ?>
		            <?php echo $form->error($model,'contact_phone_1'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'contact_phone_2'); ?>
                    <?php echo $form->telField($model,'contact_phone_2',array('size'=>50,'maxlength'=>50,'placeholder'=>'xxx-xxx-xxxx ext xx...','pattern'=>'\d{3}-\d{3}-\d{4}(\sext\s\d+)?')); ?>
		            <?php echo $form->error($model,'contact_phone_2'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'email'); ?>
                    <?php echo $form->emailField($model,'email',array('size'=>50,'maxlength'=>50,'placeholder'=>'user@mydomain.com')); ?>
		            <?php echo $form->error($model,'email'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'wds_contact'); ?>
		            <?php echo $form->textField($model,'wds_contact',array('size'=>50,'maxlength'=>50)); ?>
		            <?php echo $form->error($model,'wds_contact'); ?>
	            </div>

	            <div>
                    <?php echo $form->datepickerRow($model, 'last_contact_date', array(
                        'options' =>array(
                            'autoclose' => true,
                            'todayHighlight' => true,
                            'format' => 'yyyy-mm-dd',
                        ),
                        'value' => isset($model->last_contact_date) ? date('Y-m-d', strtotime($model->last_contact_date)) : '',
                        'placeholder' => 'Date of contact ...',
                    )); ?>
	            </div>

            </div>
            <div class="span6">

	            <div>
		            <?php echo $form->labelEx($model,'comment'); ?>
                    <?php echo $form->textArea($model,'comment',array('maxlength'=>2000, 'rows' => 8, 'cols' => 70, 'style'=>'width:auto;')); ?>
		            <?php echo $form->error($model,'comment'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'lat'); ?>
		            <?php echo $form->textField($model,'lat',array('size'=>50,'maxlength'=>50)); ?>
		            <?php echo $form->error($model,'lat'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'lon'); ?>
		            <?php echo $form->textField($model,'lon',array('size'=>50,'maxlength'=>50)); ?>
		            <?php echo $form->error($model,'lon'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'client_id'); ?>
                    <?php echo $form->dropDownList($model, 'client_id', CHtml::listData(Client::model()->findAll(array(
                        'select' => 'id, name',
                        'order' => 'name ASC'
                    )), 'id', 'name'), array('empty'=>'')); ?>
		            <?php echo $form->error($model,'client_id'); ?>
	            </div>

            </div>
        </div>
        <div class="row-fluid buttons">
		    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
            <span class="paddingLeft10">
                <?php echo CHtml::link('Cancel', array('admin')); ?>
            </span>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->