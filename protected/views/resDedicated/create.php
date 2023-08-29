<?php
/* @var $this ResDedicatedController */
/* @var $model ResDedicated */

if ($type === 'dedicated')
{
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Manage Dedicated' => array('/resDedicated/admin'),
        'Create'
    );
    
    $this->renderPartial('_form', array(
		'model' => $model,
        'clientid' => $clientid,
        'hoursid' => $hoursid,
        'months' => $months
    ));
}

if ($type === 'dedicated_hours')
{
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Manage Dedicated' => array('/resDedicated/admin'),
        'Create Hours'
    );
    
    $this->renderPartial('_form_hours', array(
        'model'=>$model,
        'clientid' => $clientid,
    ));
}

?>