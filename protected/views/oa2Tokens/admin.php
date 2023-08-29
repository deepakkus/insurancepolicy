<?php
/* @var $this Oa2TokensController */
/* @var $model Oa2Tokens */

$this->breadcrumbs=array(
	'Oauth2 Users' => array('user/adminOauth'),
	'Oauth2 Tokens'
);

$this->menu=array(
	array('label'=>'List Oa2Tokens', 'url'=>array('index')),
	array('label'=>'Create Oa2Tokens', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#oa2-tokens-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Oa2 Tokens</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'oa2-tokens-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'oauth_token',
		array(
            'name' => 'client_id',
            'filter' => CHtml::activeDropDownList($model,'client_id',CHtml::listData($model->findAll(array(
                'select' => 'client_id',
                'distinct' => true
            )),'client_id','client_id'), array('prompt'=>''))
        ),
        array(
            'name'=>'expires',
            'value'=>function($data) { return ($data->expires < date('Y-m-d H:i') && !empty($data->expires)) ? "<b style='color:red'>$data->expires</b>" : $data->expires; },
            'type'=>'html',
            'filter' => CHtml::activeDateField($model,'expires'),
        ),
	    array(
            'name' => 'scope',
            'filter' => CHtml::activeDropDownList($model,'scope',CHtml::listData($model->findAll(array(
                'select' => 'scope',
                'distinct' => true
            )),'scope','scope'), array('prompt'=>''))
        ),
		array(
            'name' => 'type',
            'filter' => CHtml::activeDropDownList($model,'type',CHtml::listData($model->findAll(array(
                'select' => 'type',
                'distinct' => true
            )),'type','type'), array('prompt'=>''))
        ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
