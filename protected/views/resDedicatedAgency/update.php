<?php

/* @var $this ResDedicatedAgencyController */
/* @var $model ResDedicatedAgency */

$this->breadcrumbs=array(
    'Engines'=>array('/engEngines/index'),
	'Manage Agency Visits' => array('/resDedicatedAgency/admin'),
    'Update'
);

?>

<h1>Update Agency Visit</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>