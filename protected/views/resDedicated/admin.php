<?php

/* @var $this ResDedicatedController */
/* @var $model ResDedicated */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Manage Dedicated'
);
Yii::app()->format->dateFormat = 'd-m-Y';
?>

<h1>Dedicated Service Hours</h1>

<?php $this->widget('bootstrap.widgets.TbButtonGroup', array(
    //'size' => 'large',
    'type' => 'success',
    'buttons' => array(
        array(
            'label' => 'Add Yearly Dedicated Hours',
            'items' => array_map(function($client) { 
                return array(
                    'label' => $client->name,
                    'url' => $this->createUrl('/resDedicated/createHours', array('clientid' => $client->id))
                );
            }, Client::model()->findAll(array('order'=>'name ASC','condition'=>'dedicated = 1')))
        )
    )
)); ?>

<div class="table-responsive">
    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array (
        'id' => 'res-dedicated-hours-grid',
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped bordered condensed',
	    'dataProvider' => $hours->search(),
	    'filter' => $hours,
	    'columns' => array(
            array(
                'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}',
                'header' => 'Actions',
                'updateButtonUrl' => '$this->grid->controller->createUrl("/resDedicated/updateHours", array("id" => $data->id))'
            ),
            array(
                'class' => 'CLinkColumn',
                'header' => 'Add Dedicated Service Hours',
                'label' => 'Add Hours',
                'urlExpression' => 'array("/resDedicated/create", "clientid" => $data->client_id, "hoursid" => $data->id)'
            ),
            array(
                'name' => 'dedicated_start_date',
                'value' => 'date("Y-m-d", strtotime($data->dedicated_start_date))',                
                'type' => 'date',
                'filter' => CHtml::activeDateField($hours,'dedicated_start_date'),

            ),
		    'dedicated_hours',
            array(
                'header' => 'Hours Used',
                'value' => 'Yii::app()->db->createCommand("SELECT SUM(CAST(hours_used AS NUMERIC(8,2))) FROM res_dedicated WHERE hours_id = :id")->queryScalar(array(":id" => $data->id))'
            ),
            array(
                'name' => 'client_name',
                'filter' => CHtml::activeDropDownList($hours, 'client_name', CHtml::listData(ResDedicatedHours::model()->findAll(array(
                    'select' => 'client_id',
                    'distinct' => true
                )), 'client_name', 'client_name'),array('prompt' => ''))
            )
	    )
    )); ?>
</div>

<div class ="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'id' => 'res-dedicated-grid',
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped bordered condensed',
	    'dataProvider' => $dedicated->search(),
	    'filter' => $dedicated,
	    'columns' => array(
            array(
                'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}{delete}',
                'header' => 'Actions',
                'updateButtonUrl' => '$this->grid->controller->createUrl("/resDedicated/update", array("id" => $data->dedicated_id))'
            ),
            array(
                'name' => 'client_name',
                'filter' => CHtml::activeDropDownList($dedicated, 'client_name', CHtml::listData(ResDedicated::model()->findAll(array(
                    'select' => 'client_id',
                    'distinct' => true
                )), 'client_name','client_name'),array('prompt'=>''))
            ),
            array(
                'name' => 'dedicated_hours_date',
                'value' => 'date("Y-m-d", strtotime($data->dedicated_hours_date))'
            ),
            array(
                'name' => 'date',
                'value' => 'date("Y-m", strtotime($data->date))',
                'filter' => false
            ),
            array(
                'name' => 'date_updated',
                'value' => 'date("Y-m-d", strtotime($data->date_updated))',
                'filter' => false
            ),
            array('name' => 'AZ', 'filter' => false),
            array('name' => 'CA', 'filter' => false),
            array('name' => 'CO', 'filter' => false),
            array('name' => 'FL', 'filter' => false),
            array('name' => 'GA', 'filter' => false),
            array('name' => 'ID', 'filter' => false),
            array('name' => 'MT', 'filter' => false),
            array('name' => 'NC', 'filter' => false),
            array('name' => 'ND', 'filter' => false),
            array('name' => 'NM', 'filter' => false),
            array('name' => 'NV', 'filter' => false),
            array('name' => 'OK', 'filter' => false),
            array('name' => 'OR', 'filter' => false),
            array('name' => 'SC', 'filter' => false),
            array('name' => 'SD', 'filter' => false),
            array('name' => 'TN', 'filter' => false),
            array('name' => 'TX', 'filter' => false),
            array('name' => 'UT', 'filter' => false),
            array('name' => 'WA', 'filter' => false),
            array('name' => 'WY', 'filter' => false),
            array('name' => 'hours_used', 'filter' => false)
	    )
    )); ?>

</div>