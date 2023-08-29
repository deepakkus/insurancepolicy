<?php

/* @var $this ResPerimetersController */
/* @var $model ResPerimeters */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Perimeters' => array('/resPerimeters/admin'),
    'Create Perimeter'
);

?>

<h2>Upload a Perimeter</h2>

<?php $this->renderPartial('_form', array(
    'model' => $model
)); ?>