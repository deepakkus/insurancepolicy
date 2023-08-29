<?php

/* @var $this ResEvacZoneController */
/* @var $model ResNotice */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Evac Zones' => array('/resEvacZone/admin'),
    'Update'
);

?>

<h1>Update Evac Zones</h1>

<?php 
$this->renderPartial('_form', array(
    'notice' => $notice,
    'mapEvacZones' => $mapEvacZones,
)); 
?>