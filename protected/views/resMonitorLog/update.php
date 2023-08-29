<?php

/* @var $this ResMonitorLogController */
/* @var $model ResMonitorLog */

if ($page === 'monitor')
{
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Monitor Log' => array('/resMonitorLog/admin'),
        'Update'
    );
    
    echo '<h1>Update Monitor Log Entry ' . $model->Monitor_ID . '</h1>';
}
else
{
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Smoke checks' => array('/resMonitorLog/smokeCheck'),
        'Update'
    );
    
    echo '<h1>Update Smoke Check</h1>';
}

$this->renderPartial('_form', array(
    'model'=>$model,
    'page' => $page
));

?>