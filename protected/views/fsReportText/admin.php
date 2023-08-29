<?php

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('engine-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

$this->breadcrumbs=array(
	'FS Report Texts'=>array('admin'),
	'Manage',
);
?>

<h1>Manage FS Report Text</h1>


<?php
echo CHtml::link('Add New Report Text', array('fsReportText/create'));

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'fsReport-grid',
	'dataProvider'=>$fsReportTexts->search(),
	'filter'=>$fsReportTexts,
	'columns'=>array(
		array(
			'class'=>'CButtonColumn', 'template'=>'{update}',
			//'buttons'=>array('update'=>array('url'=>'$this->grid->controller->createUrl("/fsReportText/update", array("id"=>$data->id,))',)),
		),
		array('name'=>'condition_num', 'value'=>'$data->condition_num', 'filter'=> CHtml::activeDropDownList($fsReportTexts, 'condition_num', array_combine(range(1,15), range(1,15)), array('empty'=>''))), 
		array('name'=>'response', 'value'=>'$data->response', 'filter'=> CHtml::activeDropDownList($fsReportTexts, 'response', array('Yes'=>'Yes', 'No'=>'No'), array('empty'=>''))), 
		array('name'=>'risk_level', 'value'=>'$data->risk_level', 'filter'=>CHtml::activeDropDownList($fsReportTexts, 'risk_level', array('1'=>'1', '2'=>'2', '3'=>'3'), array('empty'=>''))),
		array('name'=>'type', 'value'=>'$data->type', 'filter'=> CHtml::activeDropDownList($fsReportTexts, 'type', FSReportText::model()->getTypes(), array('empty'=>''))), 
		array('name'=>'text', 'value'=>'substr($data->text, 0, 100)."...";'), 
	),
)); 
?>