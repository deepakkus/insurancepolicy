<?php

/* @var $this ApiDocumentationController */
/* @var $model ApiDocumentation */

$this->breadcrumbs=array(
	'Api Documentations'=>array('admin'),
	'Create',
);

echo '<h1>Create ApiDocumentation</h1>';

$this->renderPartial('_form', array(
    'model' => $model
));