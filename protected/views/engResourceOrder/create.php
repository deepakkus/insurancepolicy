<?php

/* @var $this EngResourceOrderController */
/* @var $model EngResourceOrder */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Engine Scheduling'=>array('engScheduling/admin'),
    'Resource Orders'=>array('admin'),
    'Create'
);

?>

<h1>Create New RO</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>