<?php

$this->breadcrumbs=array(
	'Properties' => array('admin'),
	$property->pid => array('view','pid'=>$property->pid),
	'Refresh Risk',
);

echo '<h1>Refresh Property ' . $property->pid . ' Risk</h1>';
echo '<p class="lead">This will override the current risk value.</p>';

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'property-risk-refresh-form',
    'htmlOptions' => array('class' => 'well')
));

echo '<div class="control-group">';
echo $form->labelEx($refreshRiskForm, 'id', array('class' => 'control-label'));
echo '<div class="controls">';
echo Chtml::activeDropDownList($refreshRiskForm, 'id', $refreshRiskForm->getRiskTypesDropdown(), array('prompt' => 'Select a risk type ...'));
echo '</div>';
echo $form->error($refreshRiskForm, 'id');
echo '</div>';

echo CHtml::submitButton('Create', array('class' => 'submit'));
echo CHtml::link('Cancel', array('view','pid'=>$property->pid), array('class' => 'paddingLeft10'));

$this->endWidget();
unset($form);