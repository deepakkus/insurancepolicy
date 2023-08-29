<?php

/* @var $this ResPhActionCategoryController */
/* @var $model ResPhActionCategory */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Manage Policyholder Action Types' => array('resPhActionType/manage'),
    'Create Category'
);

echo '<h2>Create Category</h2>';

$this->renderPartial('_form', array(
    'model' => $model
));