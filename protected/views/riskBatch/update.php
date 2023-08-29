<?php

/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */

$this->breadcrumbs = array(
	'Risk Batch'=>array('/riskBatch/admin'),
    'Update'
);

?>

<h1>Update Batch Information</h1>


<?php $this->renderPartial('_form', array('model'=>$model)); ?>
