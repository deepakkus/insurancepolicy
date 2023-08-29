<?php
/* @var $this AppSettingController */
/* @var $model AppSetting */

$this->breadcrumbs=array(
	'App Settings'=>array('admin'),
);

$this->menu=array(
	array('label'=>'App Settings', 'url'=>array('admin')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-form form').submit(function(){
	$('#app-setting-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>App Settings</h1>

<?php 
    echo CHtml::link('Add New Setting', array('appSetting/create'));

    $this->widget('zii.widgets.grid.CGridView', array(
	    'id'=>'app-setting-grid',
	    'dataProvider'=>$model->search(),
	    'filter'=>$model,
	    'columns'=>array(
            array('class'=>'CButtonColumn', 'template'=>'{update}', 'buttons'=>array('update'=>array('url'=>'$this->grid->controller->createUrl("/appSetting/update", array("id"=>$data->id,))',))),
		    'id',
		    'type',
		    'client_ids',
		    'application_context',
            'platform_context',
		    'name',
		    'data_type',
		    'value',
		    'effective_date',
		    'expiration_date',
            'minimum_resolution'
	    ),
    )); 
?>
