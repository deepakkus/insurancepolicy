<?php
/* @var $this AllianceController */
/* @var $model Alliance */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Alliance'=>array('admin'),
	'Create'
);
?>

<h1>Create Alliance Partner</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>