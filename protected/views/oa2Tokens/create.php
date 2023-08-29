<?php
/* @var $this Oa2TokensController */
/* @var $model Oa2Tokens */

$this->breadcrumbs=array(
	'Oa2 Tokens'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Oa2Tokens', 'url'=>array('index')),
	array('label'=>'Manage Oa2Tokens', 'url'=>array('admin')),
);
?>

<h1>Create Oa2Tokens</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>