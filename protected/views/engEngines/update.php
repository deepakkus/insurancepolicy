<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engines'=>array('index'),
	'Manage Engines'=>array('admin'),
	'Update',
);

?>

<h1>Update Engine <?php echo $model->engine_name; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>