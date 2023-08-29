<?php

/* @var $this ResFireNameController */
/* @var $model ResFireName */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Update'
);

?>

<h1>Update <?php echo $model->Name; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>