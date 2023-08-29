<?php

/* @var $this ResPhActionCategoryController */
/* @var $model ResPhActionCategory */
/* @var $form CActiveForm */

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'res-ph-action-category-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    ),
));

echo $form->textFieldRow($model, 'category', array(
    'size' => 60,
    'maxlength' => 100,
    'placeholder' => 'Enter a ' . $model->getAttributeLabel('category')
));

echo CHtml::tag('div', array('class' => 'marginTop20'), CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array(
    'class' => 'submit'
)));

$this->endWidget();

unset($form);