<?php

/* @var $this ResThreatController */
/* @var $model ResThreat */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Perimeters' => array('/resPerimeters/admin'),
    'Create Threat'
);

?>

<h1>Upload a Threat</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>