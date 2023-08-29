<?php

/* @var $this EngResourceOrderController */
/* @var $model EngResourceOrder */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Engine Scheduling'=>array('engScheduling/admin'),
    'Resource Orders'=>array('admin'),
    'Update'
);

?>

<h1>Update RO <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>