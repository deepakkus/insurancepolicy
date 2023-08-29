<?php

/* @var $this RiskVersionController */
/* @var $model RiskVersion */

$this->breadcrumbs = array(
    'Risk Versions' => array('/riskVersion/versions'),
    'Update'
);

?>

<h2>Update Risk Version <?php echo $model->version; ?></h2>

<?php $this->renderPartial('_form', array('model' => $model)); ?>