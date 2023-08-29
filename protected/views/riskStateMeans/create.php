<?php

/* @var $this RiskStateMeansController */
/* @var $model RiskStateMeans */

$this->breadcrumbs = array(
	'State Means' => array('/riskStateMeans/admin'),
    'Create'
);

?>

<h1>Create State Mean</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>