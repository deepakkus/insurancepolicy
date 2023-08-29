<?php
/* @var $this ClientDedicatedHoursController */
/* @var $model ClientDedicatedHours */

$this->breadcrumbs=array(
    'Client Dedicated Hours' => array('admin'),
    'Create',
);

?>

<h1>Create</h1>

<?php $this->renderPartial('_form', array(
    'model' => $model
)); ?>