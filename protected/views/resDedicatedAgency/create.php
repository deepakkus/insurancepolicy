<?php

/* @var $this ResDedicatedAgencyController */
/* @var $model ResDedicatedAgency */

$this->breadcrumbs=array(
    'Engines'=>array('/engEngines/index'),
	'Manage Agency Visits' => array('/resDedicatedAgency/admin'),
    'Create'
);

?>

<h1>Create Agency Visit</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>