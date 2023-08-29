<?php

/* @var $this ResEvacZoneController */
/* @var $model ResNotice */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Evac Zones' => array('/resEvacZone/admin'),
    'Create'
);

?>

<h1>Create Evac Zones</h1>

<?php 

$this->renderPartial('_form', array(
    'notice' => $notice
)); 

?>