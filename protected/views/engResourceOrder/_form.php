<?php

/* @var $this EngResourceOrder Controller */
/* @var $model EngResourceOrder */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerCss('resource-order-form-css',<<<CSS
.bootstrap-timepicker-widget input[type='text'] {
    width: 50px;
}
CSS
);

?>

<div class="form">

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'eng-resource-order-form',
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="marginTop20 marginBottom20">

        <?php echo $form->datepickerRow($model, 'form_ordered_date', array(
            'options' =>array(
                'autoclose' => true,
                'todayHighlight' => true,
            ),
            'style' => 'cursor:pointer',
            'readonly'=>true
        )); ?>

        <?php echo $form->timepickerRow($model,'form_ordered_time', array(
            'class' => 'input-small',
            'options' => array(
                'showMeridian' => false,
                'defaultTime' => false,
                
            ),
            
        )); ?>

	</div>

	<div class="buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div>