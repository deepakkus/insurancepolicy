<?php

/* @var $this ApiDocumentationController */
/* @var $model ApiDocumentation */

$this->breadcrumbs = array(
	'Api Documentations'=>array('admin'),
	'Update ' . $model->name,
);

echo '<h1>Update ' . $model->name . '</h1>';

$this->renderPartial('_form', array(
    'model' => $model
));