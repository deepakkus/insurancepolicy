<?php

$this->breadcrumbs = array(
	'Clients' => array('admin'),
	'Manage',
);

Yii::app()->clientScript->registerScript('search', "
	$('.search-button').click(function(){
			$('.search-form').toggle();
			return false;
	});
	$('.search-form form').submit(function(){
			$.fn.yiiGridView.update('fsUser-grid', {
					data: $(this).serialize()
			});
			return false;
	});
");

echo '<h1>Manage Clients</h1>';
echo CHtml::link('Add New Client', array('client/create'));

$columnArray = array(
	array(
		'class' => 'CButtonColumn', 
		'template' => '{update}',
		'buttons' => array(
			'update' => array('url' => '$this->grid->controller->createUrl("/client/update", array("id"=>$data->id,))',)
		),
		'htmlOptions' => array('style' => 'text-align: center'),
		'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
	),
	array(
		'name' => 'id',
		'htmlOptions' => array('width'=>'2'),
		'filter' => CHtml::activeNumberField($clients, 'id', array('min'=>0,'max'=> 999999)),
	),
	array (
		'name' => 'name',
		'filter' => CHtml::activeTextField($clients, 'name', array('size'=>5)),
	),
    array(
        'name' => 'parent_client_id',
        'value' => '(isset($data->parentClient->name)) ? $data->parentClient->name : "";',
        'filter' => CHtml::activeDropDownList($clients, 'parent_client_id', CHtml::listData(Client::model()->findAll(), 'id', 'name'), array('empty'=>''))
    ),
	array (
		'name' => 'code',
		'filter' => CHtml::activeTextField($clients, 'code', array('size'=>5)),
	),	
    array (
		'name' => 'wds_fire',
        'type'=>'raw',
		'value' =>function($data) { 
                    return $data->getServiceMask($data->wds_fire);
                    },
        'filter' => CHtml::activeDropDownList($clients, 'wds_fire', array('0' => '&#x2716;', '1' => '&#x2713;'), array('encode' => false,'prompt' => ''))

    ),
    array (
		'name' => 'wds_risk',
        'type'=>'raw',
		'value' =>function($data) { 
                    return $data->getServiceMask($data->wds_risk);
                    },
         'filter' => CHtml::activeDropDownList($clients, 'wds_risk', array('0' => '&#x2716;', '1' => '&#x2713;'), array('encode' => false,'prompt' => ''))

    ),
    array (
		'name' => 'wds_pro',
        'type'=>'raw',
		'value' =>function($data) { 
                    return $data->getServiceMask($data->wds_pro);
                    },
        'filter' => CHtml::activeDropDownList($clients, 'wds_pro', array('0' => '&#x2716;', '1' => '&#x2713;'), array('encode' => false,'prompt' => ''))

    ),
    array (
		'name' => 'wds_education',
        'type'=>'raw',
		'value' =>function($data) { 
                    return $data->getServiceMask($data->wds_education);
                    },
         'filter' => CHtml::activeDropDownList($clients, 'wds_education', array('0' => '&#x2716;', '1' => '&#x2713;'), array('encode' => false,'prompt' => ''))

    ),
		
);

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'fsUser-grid',
	'dataProvider'=>$clients->search(),
	'filter'=>$clients,
	'columns'=> $columnArray,
)); ?>
 