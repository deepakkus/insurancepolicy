<?php

/* @var $this ResDailyThreatController */
/* @var $model ResDailyThreat */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Regional Conditions' => array('/resDailyThreat/admin'),
    'Create Regional Conditions'
);

?>

<h1>Create Daily Threat</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>