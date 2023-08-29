<?php
/* @var $this ResDedicatedController */
/* @var $model ResDedicated */

if ($type === 'dedicated')
{
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Manage Dedicated' => array('/resDedicated/admin'),
        'Update'
    );
    
    $this->renderPartial('_form', array(
        'model'=>$model
    ));
}

if ($type === 'dedicated_hours')
{
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Manage Dedicated' => array('/resDedicated/admin'),
        'Update Hours'
    );
    
    $this->renderPartial('_form_hours', array(
        'model'=>$model
    ));
}

?>