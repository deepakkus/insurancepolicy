<?php

/* @var $this UserTrackingController */
/* @var $model UserTracking */

if ($this->route === 'userTracking/admin')
{
    $this->breadcrumbs = array(
        'User Tracking' => array('admin'),
        'Manage',
    );
}
else
{
    $this->breadcrumbs = array(
        'User Tracking' => array('admin'),
        'View User' => array('viewUser'),
        'View User Details'
    );
}


Yii::app()->format->dateFormat = 'Y-m-d H:i';

if ($this->route === 'userTracking/admin')
{
    echo '<h1>User Tracking</h1>';
    echo '<div>';
    echo '<a class="btn btn-success marginRight10" href="' . $this->createUrl('/userTrackingPlatform/admin') . '">Tracking Platforms</a>';
    echo '<a class="btn btn-info" href="' . $this->createUrl('/userTracking/viewUser') . '">Tracking By User</a>';
    echo '</div>';
}

echo '<div class="table-responsive">';

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'user-tracking-grid',
    'dataProvider' => $dataProvider,
    'filter' => $model,
    'columns' => array(
        'user_name',
        array(
            'name' => 'client_id',
            'value' => 'isset($data->client->name) ? $data->client->name : ""',
            'filter' => CHtml::activeDropDownList($model, 'client_id', CHtml::listData($model->findAll(array(
                'select' => 'client_id',
                'distinct' => 'true',
                'order' => 'client_id',
                'condition' => 'client_id IS NOT NULL'
            )), 'client_id', 'client_name'), array(
                'prompt' => ''
            ))
        ),
        array(
            'name' => 'date',
            'filter' => CHtml::activeDateField($model,'date'),
            'type' => 'date'
        ),
        'fire_name',
        'route',
        'platform_id' => array(
            'name' => 'platform_id',
            'value' => 'isset($data->platform->platform) ? $data->platform->platform : ""',
            'filter' => CHtml::activeDropDownList($model, 'platform_id', CHtml::listData($model->findAll(array(
                'select' => 'platform_id',
                'distinct' => 'true'
            )), 'platform_id', 'platform_name'), array(
                'prompt' => ''
            ))
        ),
        array(
            'name' => 'ip',
            'filter' => false
        ),
        array(
            'name' => 'data',
            'value' => '(strlen($data->data) > 100) ? substr($data->data, 0, 100) . " ..." : $data->data;',
            'filter' => false
        ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'Action',
            'label' => 'View Data',
            'urlExpression' => '$this->grid->controller->createUrl("/userTracking/view", array("id" => $data->id))',
            'linkHtmlOptions' => array('class' => 'view-data')
        )
    )
));

echo '</div>';

Yii::app()->clientScript->registerScript(1, '

    // Open JUI modal popup
    $(document).on("click", ".view-data", function(e) {
        e.preventDefault();
        $("#modal-container")
            .find("#modal-content")
            .load($(this).attr("href"), function() {
                $("#modal-container").dialog("open");
            });
    });

');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'modal-container',
    'options' => array(
        'title' => 'Action GET / POST data',
        'autoOpen' => false,
        'closeText' => false,
        'modal' => true,
        'buttons' => array(
            'OK' => 'js:function() { $(this).dialog("close"); }'
        ),
        'show' => array(
            'effect' => 'drop',
            'duration' => 300,
            'direction' => 'up'
        ),
        'hide' => array(
            'effect' => 'fadeOut',
            'duration' => 300
        ),
        'width' => 800,
        'height' => 600,
        'resizable' => false,
        'draggable' => true
    )
));

echo CHtml::tag('div', array('id' => 'modal-content'), true);

$this->endWidget('zii.widgets.jui.CJuiDialog');

?>