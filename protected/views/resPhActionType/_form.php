<?php

/* @var $this ResPhActionTypeController */
/* @var $model ResPhActionType */
/* @var $form CActiveForm */

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'res-ph-action-type-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

echo $form->textFieldRow($model, 'name', array(
    'size' => 60,
    'maxlength' => 100,
    'placeholder' => 'Enter a ' . $model->getAttributeLabel('name')
));

echo $form->textAreaRow($model, 'definition', array(
    'rows' => 10,
    'maxlength' => 255,
    'class' => 'span6',
    'placeholder' => 'Enter a ' . $model->getAttributeLabel('definition'),
    'hint' => 'Limited to 255 characters'
));

echo $form->dropDownListRow($model, 'category_id', CHtml::listData($model->getCategories(), 'id', 'category'), array(
    'prompt' => 'Select a category'
));

echo $form->radioButtonListRow($model, 'action_type', array('Physical' => 'Physical', 'Recon' => 'Recon'));

echo $form->textFieldRow($model, 'units', array(
    'size' => 60,    
    'placeholder' => 'Enter ' . $model->getAttributeLabel('units')
));

echo $form->dropDownListRow($model, 'app_sub_category', $model->getSubCategories(), array(
    'prompt' => 'Select a sub category'
));
echo $form->textFieldRow($model, 'action_item_order', array(
    'size' => 60,    
    'placeholder' => 'Enter ' . $model->getAttributeLabel('action_item_order')
));
echo $form->checkBoxRow($model, 'active', array(
    'hint' => 'If Deactivated,  a user can no longer user this item when filling out a policyholder visit.'
));

echo CHtml::tag('div', array('class' => 'marginTop20'), CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array(
    'class' => 'submit'
)));
echo CHtml::tag('div', array('style' => 'position: relative;bottom: 23px;left: 60px;'), CHtml::link('Cancel', array('resPhActionType/manage')));

 
$this->endWidget();

unset($form);