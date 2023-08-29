<?php

$this->breadcrumbs=array(
	'Users'=>array('admin'),
	'Manage',
);


echo '<h1>Manage Users</h1>';

if (in_array('Admin',Yii::app()->user->types))
{
    echo CHtml::link('Add User', array('user/create'), array('class' => 'btn btn-success'));
}

echo '<div class ="table-responsive">';

$this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'user-grid',
	'dataProvider' => $model->search(),
	'filter' => $model,
	'columns' => array(
        array(
            'class' => 'CButtonColumn',
            'template' => '{update}{view}',
            'header' => 'Actions',
            'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
            'htmlOptions' => array('style' => 'text-align: center'),
            'buttons' => array(
                'update' => array(
                    'visible' => 'in_array("Admin", Yii::app()->user->types)'
                ),
                'view' => array(
                    'visible' => 'in_array("Manager", Yii::app()->user->types) && !in_array("Admin", Yii::app()->user->types)'
                )
            )
        ),
		array(
			'name' => 'id',
			'filter' => CHtml::activeNumberField($model, 'id', array('max' => 99999)),
		),
		'name',
		'username',
        'email',
        array(
            'name' => 'client',
            'value' => '(isset($data->client->name)) ? $data->client->name : "";',
            'filter' => CHtml::activeDropDownList($model, 'client_id', CHtml::listData(Client::model()->findAll(), 'id', 'name'), array('empty'=>''))
        ),
        array(
            'name' => 'alliance',
            'value' =>'(isset($data->alliance->name)) ? $data->alliance->name : "";',
            'filter'=> CHtml::activeDropDownList($model, 'alliance_id', CHtml::listData(Alliance::model()->findAll(), 'id', 'name'), array('empty'=>''))
        ),
		array(
            'name' => 'type',
            'value' => '$this->grid->controller->getDropdownsItems($data)',
            'filter' => CHtml::activeDropDownList($model, 'type', $model->getTypes(), array('empty'=>'', 'style'=> 'width:400px')),
            'type' => 'html'
        ),	
	    array (
		    'name' => 'active',
            'type' => 'raw',
            'value' => '$data->activeMask;',
            'filter' => CHtml::activeDropDownList($model, 'active', $model->getActiveFilter(), array('encode'=>false, 'empty'=>''))
        )
    )
));

echo '</div>';
?>

