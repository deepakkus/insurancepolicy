<?php

/* @var $this ResPerimetersController */
/* @var $model ResPerimeters */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Perimeters' => array('/resPerimeters/admin'),
    'Update Perimeter'
);

?>

<h2>Edit Perimeter <?php echo $model->resFireName->Name; ?></h2>
<p style="color:red;display:inline;"> WARNING</p>: Uploading will overwrite the current perimeter. Do not use to update perimeters!
<?php $this->renderPartial('_form', array(
    'model' => $model
)); ?>