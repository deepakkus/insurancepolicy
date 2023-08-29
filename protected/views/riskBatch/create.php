<?php

/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */

$this->breadcrumbs = array(
	'Risk Batch'=>array('/riskBatch/admin'),
    'Create'
);

?>

<h1>Load new batch file for risk processing (CSV)</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>
