<?php

/* @var $this UserTrackingPlatformController */
/* @var $model UserTrackingPlatform */

$this->breadcrumbs = array(
    'User Tracking' => array('/userTracking/admin'),
    'Platforms' => array('admin'),
    'Update',
);

?>

<h1>Update Platform <?php echo $model->platform; ?></h1>

<?php $this->renderPartial('_form', array('model' => $model)); ?>