<?php

Yii::app()->clientScript->registerScript('disableInputs', "

$('input, select, textarea').prop('disabled', true);
$('input[type=\'submit\']').hide();

", CClientScript::POS_READY);

$this->breadcrumbs=array(
	'Users' => array('admin'),
	$model->id,
);

echo $this->renderPartial('_form', array(
    'model' => $model
));
