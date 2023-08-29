<?php

/* @var $this PropertiesTypeController */
/* @var $model PropertiesType */

$this->breadcrumbs=array(
	'Properties' => array('property/admin'),
    'Properties Types'=>array('admin'),
	'Create'
);

?>

<h1>Create Property Type</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>