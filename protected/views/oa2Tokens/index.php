<?php
/* @var $this Oa2TokensController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Oa2 Tokens',
);

$this->menu=array(
	array('label'=>'Create Oa2Tokens', 'url'=>array('create')),
	array('label'=>'Manage Oa2Tokens', 'url'=>array('admin')),
);
?>

<h1>Oa2 Tokens</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
