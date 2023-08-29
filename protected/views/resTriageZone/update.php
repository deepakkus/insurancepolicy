<?php

/* @var $this ResTriageZoneController */
/* @var $model ResTriageZone */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Work Zones' => array('/resTriageZone/admin'),
    'Update'
);

?>

<h1>Update Work Zones</h1>

<?php $this->renderPartial('triage', array(
    'triageZone' => $triageZone,
    'resTriageZoneAreas' => $resTriageZoneAreas
)); ?>