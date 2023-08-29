<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engines'=>array('index'),
	'Manage Engines'=>array('admin'),
	'Create',
);

$this->menu=array(
	array('label'=>'List EngEngines', 'url'=>array('index')),
	array('label'=>'Manage EngEngines', 'url'=>array('admin')),
);
?>

<h1>Create Engine</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>