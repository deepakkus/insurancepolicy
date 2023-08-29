<?php

/* @var $this UserTrackingPlatformController */
/* @var $model UserTrackingPlatform */

$this->breadcrumbs = array(
    'User Tracking' => array('/userTracking/admin'),
    'Platforms' => array('admin'),
    'Create',
);

?>

<h1>Create Platform</h1>

<?php $this->renderPartial('_form', array('model' => $model)); ?>