<?php

/* @var $this ResPerimetersController */
/* @var $model ResPerimeters */

$this->breadcrumbs = array(
    'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Perimeters' => array('/resPerimeters/admin'),
    'Create Threat'
);

?>

<h2>Update Threat for <?php echo $model->fire_name; ?></h2>

<?php $this->renderPartial('_form_threat', array(
    'model' => $model
)); ?>