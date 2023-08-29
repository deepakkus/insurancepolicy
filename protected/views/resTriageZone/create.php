<?php

/* @var $this ResTriageZoneController */
/* @var $model ResTriageZone */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Work Zones' => array('/resTriageZone/admin'),
    'Create'
);

?>

<h1>Create Work Zones</h1>

<?php $this->renderPartial('triage', array(
    'triageZone' => $triageZone
)); ?>