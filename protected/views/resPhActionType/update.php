<?php
/* @var $this ResPhActionTypeController */
/* @var $model ResPhActionType */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Manage Policyholder Action Types' => array('resPhActionType/manage'),
    'Update Type: ' . $model->name
);

echo '<h2>Update Type: ' . $model->name . '</h2>';

$this->renderPartial('_form', array(
    'model' => $model
));