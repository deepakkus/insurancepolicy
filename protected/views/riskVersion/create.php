<?php

/* @var $this RiskVersionController */
/* @var $model RiskVersion */

$this->breadcrumbs = array(
    'Risk Versions' => array('/riskVersion/versions'),
    'Create'
);

?>

<h2>Create Risk Version</h2>

<?php $this->renderPartial('_form', array('model' => $model)); ?>