<?php

/* @var $this SystemSettingsController */
/* @var $model SystemSettings */

$this->breadcrumbs = array(
	'System Settings' => array('admin'),
	'Update'
);

echo '<h1>Update System Settings</h1>';

Yii::app()->clientScript->registerScript(1, "
    // Add progress bar when submitted
    $('#system-settings-form').submit(function () {
        $('.buttons').empty();
        $('.buttons').html('{$this->widget('bootstrap.widgets.TbProgress', array(
            'percent' => 100,
            'striped' => true,
            'animated' => true,
            'htmlOptions' => array(
                'style' => 'width: 300px;'
            )
        ), true)}');
    });
");

Yii::app()->clientScript->registerCss(1, '
    .help-block {
        display: block;
        color: #737373;
    }
');

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'system-settings-form',
    'type' => 'horizontal',
    'htmlOptions' => array('class' => 'well')
));

echo $form->errorSummary($model);

echo $form->textFieldRow($model, 'max_login_attempts', array(
    'placeholder' => $model->getAttributeLabel('max_login_attempts'),
    'hint' => 'How many login attempts users get until they are locked out of the system.'
));

echo CHtml::tag('div', array('class' => 'marginTop20 marginRight10 clearfix '), CHtml::submitButton('Save', array(
    'class' => 'submit'
)));

$this->endWidget();

unset($form);