<?php
/* @var $this ResMonitorLogController */
/* @var $model ResMonitorLog */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Monitor Log'=>array('resMonitorLog/admin'),
    'Create'
);

?>

<h1>Create Monitoring Log Entry</h1>

<?php $this->renderPartial('_form', array(
    'model'=>$model,
    'page'=>$page
)); ?>