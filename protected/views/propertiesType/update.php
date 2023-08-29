<?php

/* @var $this PropertiesTypeController */
/* @var $model PropertiesType */

$this->breadcrumbs=array(
	'Properties' => array('property/admin'),
    'Properties Types'=>array('admin'),
	'Update'
);

?>

<h1>Update Property Type <?php echo $model->type; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>