<?php

/* @var $this ResPhActionCategoryController */
/* @var $model ResPhActionCategory */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Manage Policyholder Action Types' => array('resPhActionType/manage'),
    'Update Category: ' . $model->category
);

echo '<h2>Update Category: ' . $model->category . '</h2>';

$this->renderPartial('_form', array(
    'model' => $model
));