<?php

/* @var $this RiskStateMeansController */
/* @var $model RiskStateMeans */

$this->breadcrumbs = array(
	'State Means' => array('/riskStateMeans/admin'),
    'Update'
);

?>

<h1>Update State Mean for <?php echo $model->stateAbbr; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>