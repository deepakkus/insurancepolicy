<?php
/* @var $this AllianceController */
/* @var $model Alliance */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Alliance'=>array('admin'),
	'Update'
);
?>

<h1>Update Alliance Partner <?php echo $model->name; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>