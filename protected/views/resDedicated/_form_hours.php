<?php

/* @var $this ResDedicatedController */
/* @var $model ResDedicatedHours */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerCss('datepickercss','

    .ui-datepicker-calendar {
	    display: none !important;
	    visibility: hidden !important;
    }
    .ui-datepicker-current {
        display: none;
        visibility: hidden;
    }

');

if ($model->isNewRecord)
    echo '<h1>Fill Out Yearly Dedicated Hours for ' . Client::model()->find("id = $clientid")->name . '</h1>';
else
    echo '<h1>Update Dedicated Hours for ' . $model->client_name . '</h1>';

?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'res-dedicated-hours-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="form-section">
	                <div>
		                <?php echo $form->labelEx($model,'dedicated_hours'); ?>
		                <?php echo $form->textField($model,'dedicated_hours',array('size'=>8,'maxlength'=>10)); ?>
		                <?php echo $form->error($model,'dedicated_hours'); ?>
	                </div>

                    <div>
		                <?php echo $form->labelEx($model,'dedicated_start_date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'dedicated_start_date',
			                'options' => array(
				                'dateFormat' => 'yy-mm-01',
                                'changeMonth' => true,
                                'changeYear' => true,
                                'showButtonPanel' => true,
                                'onClose' => 'js:function(dateText, inst) { 
                                    var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                                    var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                                    $(this).datepicker("setDate", new Date(year, month, 1));
                                }'
			                )
                        )); ?>
		                <?php echo $form->error($model,'dedicated_start_date'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'notes'); ?>
		                <?php echo $form->textArea($model,'notes',array('maxlength'=>300, 'rows' => 6, 'cols' => 50, 'style'=>'width:auto;')); ?>
		                <?php echo $form->error($model,'notes'); ?>
	                </div>
                </div>
            </div>
        </div>
    </div>



    <?php if($model->isNewRecord) echo $form->hiddenField($model,'client_id', array('value'=>$clientid));  ?>
    <?php if(!$model->isNewRecord) echo $form->hiddenField($model,'client_id'); ?>

    <div class="buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('resDedicated/admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->