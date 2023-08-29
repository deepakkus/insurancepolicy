<?php
/* @var $this EngCrewManagementController */
/* @var $model EngCrewManagement */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Crew Management'=>array('admin'),
	'Update'
);
?>

<h1>Update Crew Member - <?php echo $model-> first_name .' '. $model-> last_name; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>