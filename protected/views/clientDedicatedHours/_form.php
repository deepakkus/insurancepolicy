<?php

/* @var $this ClientDedicatedHoursController */
/* @var $model ClientDedicatedHours */
/* @var $form CActiveForm */

$clients = Client::model()->findAll(array('select' => 'id,name', 'order' => 'name ASC'));

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'client-dedicated-hours-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

echo $form->textFieldRow($model, 'name', array(
    'placeholder' => $model->getAttributeLabel('name'),
    'maxlength' => 100
));

echo $form->textFieldRow($model, 'dedicated_hours', array(
    'placeholder' => $model->getAttributeLabel('dedicated_hours'),
));

echo $form->datepickerRow($model, 'dedicated_start_date', array(
    'append' => '<i class="icon-calendar"></i>',
    'value' => $model->isNewRecord ? null : date('Y-m-d', strtotime($model->dedicated_start_date)),
    'placeholder' => $model->getAttributeLabel('dedicated_start_date'),
    'options' => array(
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'todayHighlight' => true
    )
));

echo $form->textAreaRow($model, 'notes', array(
    'rows' => 6
));

echo $form->dropDownListRow($model, 'clientIDs', CHtml::listData($clients, 'id', 'name'), array(
    'multiple' => true,
    'size' => '12',
    'hint' => 'Hold down CTRL to select multiple clients'
));

echo CHtml::tag('div', array('class' => 'marginTop20'), CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array(
    'class' => 'submit'
)));

$this->endWidget();

unset($form);
