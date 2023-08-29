<?php
/* @var $this ResPhActionTypeController */
/* @var $model ResPhActionType */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Manage Policyholder Action Types' => array('resPhActionType/manage'),
    'Create Type'
);

echo '<h2>Create Type</h2>';

$this->renderPartial('_form', array(
    'model' => $model
));
