<?php

/* @var $this ResFireNameController */
/* @var $model ResFireName */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Create'
);

?>

<h1>Create New Fire</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>