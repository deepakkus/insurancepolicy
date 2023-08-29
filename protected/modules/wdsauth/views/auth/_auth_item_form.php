<?php

/* @var $authItemForm AuthItemForm */
/* @var $type CAuthItem const */
/* @var $name string (on update) */

$action = $authItemForm->isNewRecord ?
    array($this->action->getId(), 'type' => $type) : array($this->action->getId(), 'type' => $type, 'name' => $name);

$form = $this->beginWidget('WDSActiveForm', array(
    'id' => 'auth-item-form',
    'enableAjaxValidation' => true,
    'action' => $action,
    'htmlOptions' => array(
        'class' => 'well'
    ),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validationUrl' => $action
    )
));

if ($authItemForm->isNewRecord === false)
    echo '<p><strong>Update: <span class="lead">' . $name . '</span></strong></p>';

echo $form->errorSummary($authItemForm);

echo $form->dropDownListRow($authItemForm, 'platform', $authItemForm->getPlatforms(), array(
    'prompt' => 'Select a platform'
));

echo $form->textFieldRow($authItemForm, 'name', array(
    'hint' => 'The name of this auth item.',
    'maxlength' => 64
));

echo $form->textAreaRow($authItemForm, 'description', array(
    'style' => 'width: 40em; height: 10em;',
    'hint' => 'A description of this auth item.'
));

if ($this->module->enableBizRule === true)
{
    echo $form->textAreaRow($authItemForm, 'bizRule', array(
        'style' => 'width: 40em; height: 10em;'
    ));
}

if ($this->module->enableBizRuleData === true)
{
    echo $form->textAreaRow($authItemForm, 'data', array(
        'style' => 'width: 40em; height: 10em;'
    ));
}

echo $form->hiddenField($authItemForm, 'type');

if (!$authItemForm->isNewRecord)
{
    echo $form->hiddenField($authItemForm, 'oldPlatform');
    echo $form->hiddenField($authItemForm, 'oldName');
}

echo CHtml::submitButton($authItemForm->isNewRecord ? 'Create' : 'Update', array('class' => 'submit', 'style' => 'display: block'));

echo $form->generateFormJavscriptValidation();

$this->endWidget();
unset($form);