<?php

$this->breadcrumbs = array(
	'Fire Shield Users' => array('admin'),
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

$columnArray = array(
	array(
		'class' => 'CButtonColumn',
		'template' => '{update}',
        'visible' => in_array("Admin", Yii::app()->user->types),
		'buttons' => array(
			'update' => array('url' => '$this->grid->controller->createUrl("/fsUser/update", array("id"=>$data->id))')
		),
	),
	array(
		'name' => 'id',
		'htmlOptions' => array('width'=>'2'),
		'filter' => CHtml::activeNumberField($model, 'id', array('min'=>0,'max'=> 999999)),
	),
	array (
		'name' => 'email',
		'filter' => CHtml::activeTextField($model, 'email', array('size'=>5)),
	),
	array (
		'name' => 'first_name',
		'filter' => CHtml::activeTextField($model, 'first_name', array('size'=>5)),
	),
	array (
		'name' => 'last_name',
		'filter' => CHtml::activeTextField($model, 'last_name', array('size'=>5)),
	),
	array (
		'name' => 'login_token',
		'filter' => CHtml::activeTextField($model, 'login_token', array('size'=>5)),
	),
	array (
		'name' => 'vendor_id',
		'filter' => CHtml::activeTextField($model, 'vendor_id', array('size'=>5)),
	),
    array (
        'name' => 'platform',
        'filter' => CHtml::activeTextField($model, 'platform', array('size'=>10)),
    ),
	array (
		'name' => 'user_created_date',
		'filter' => CHtml::activeTextField($model, 'user_created_date', array('size'=>5)),
    ),
);

if($type == 'fs')
{
	echo '<h1>Manage Fire Shield Users</h1>';

    if (in_array("Admin", Yii::app()->user->types))
    {
        echo CHtml::link('Add New Fire Shield User', array('fsUser/create'));
    }

	$columnArray[] = array (
            'name' => 'member_mid',
            'filter' => CHtml::activeTextField($model, 'member_mid', array('size'=>5)),
        );

	$columnArray[] = array (
            'name' => 'user_created_date',
            'filter' => CHtml::activeTextField($model, 'user_created_date', array('size'=>5)),
        );

    $columnArray[] = array (
            'class' => 'CLinkColumn',
            'labelExpression' => '$data->member_mid > 0 ? "View Member" : ""',
            'urlExpression' => 'Yii::app()->createUrl("member/update", array("mid" => $data->member_mid))',
            'header' => 'Associated Member',
        );
}
elseif($type == 'agent')
{
	echo '<h1>Manage Agent App Users</h1>';
	echo CHtml::link('Add New Agent App User', array('fsUser/create'));
	$columnArray[] = array (
            'name' => 'agent_id',
            'filter' => CHtml::activeNumberField($model, 'agent_id', array('min'=>0,'max'=> 999999))
        );

    //$columnArray[] = array(
    //           'name'=>'agent_client_name',
    //           'value'=>'(isset($data->agent->client->name)) ? $data->agent->client->name : "";',
    //       );

    $columnArray[] = array (
            'class' => 'CLinkColumn',
            'labelExpression' => '$data->agent_id > 0 ? "View Agent" : ""',
            'urlExpression' => 'Yii::app()->createUrl("agent/update", array("id" => $data->agent_id))',
            'header' => 'Associated Agent',
        );
}

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'fsUser-grid',
	'dataProvider'=>$model->search($type),
	'filter'=>$model,
	'columns'=> $columnArray,
)); ?>
