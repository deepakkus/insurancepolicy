<?php
$this->breadcrumbs = array(
    'Contact Us' => array('admin'),
    'Manage',
);

Yii::app()->clientScript->registerScript('search', "
    $('.search-button').click(function(){
            $('.search-form').toggle();
            return false;
    });
    $('.search-form form').submit(function(){
            $.fn.yiiGridView.update('contactUs-grid', {
                    data: $(this).serialize()
            });
            return false;
    });
");
?>

<h1>Manage Contact Us</h1>

<?php 

echo CHtml::link('Add New Contact Us', array('fsContactUs/create'));

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'contactUs-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
                array(
                    'class' => 'CButtonColumn', 
                    'template' => '{update}{delete}',
                    'buttons' => array(
                        'update' => array('url' => '$this->grid->controller->createUrl("/fsContactUs/update", array("id"=>$data->id,))',)
                    ),
		),
		
                array (
                    'name' => 'email',
                    'filter' => CHtml::activeTextField($model, 'email', array('size'=>5)),
                ),
                array (
                    'name' => 'from',
                    'filter' => CHtml::activeTextField($model, 'from', array('size'=>5)),
                ),
                array (
                    'name' => 'status',
                    'filter' => CHtml::activeTextField($model, 'status', array('size'=>5)),
                ),
                array (
                    'name' => 'provider',
                    'filter' => CHtml::activeTextField($model, 'provider', array('size'=>5)),
                ),
                array (
                    'name' => 'timestamp',
                    'value' => '(isset($data->timestamp)) ? date("Y-m-d H:m", strtotime($data->timestamp)) : "";',
                    'filter' => CHtml::activeTextField($model, 'timestamp', array('size'=>5)),
                ),
	),
)); ?>
