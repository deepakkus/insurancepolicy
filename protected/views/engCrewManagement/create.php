<?php
/* @var $this EngCrewManagementController */
/* @var $model EngCrewManagement */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Crew Management'=>array('admin'),
	'Create'
);
?>

<h1>Create Crew Member</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>