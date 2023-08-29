<?php

$data = json_decode($model->data, true);

if ($data) 
{
    // Format any json data within the $data object
    array_walk_recursive($data, function(&$value, $key) {
        if ($key === 'data') {
            $jsondata = preg_replace('/\s+/', '', $value);
            $jsondata = json_decode($jsondata, true);
            if (json_last_error()) {
                return;
            }
            $value = $jsondata;
        }
    });
}

$data = json_encode($data, JSON_PRETTY_PRINT);

Yii::app()->format->dateFormat = 'Y-m-d H:i';

$this->widget('zii.widgets.CDetailView', array(
    'data' => $model,
    'htmlOptions' => array(
        'class' => 'table table-striped table-hover table-condensed',
        'style' => 'width: 70%;'
    ),
    'itemTemplate' => '<tr><th style="width:1%; white-space: nowrap;">{label}</th><td>{value}</td></tr>',
    'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
    'attributes' => array(
        'user_name',
        'client_name',
        'date:date',
        'route',
        'platform_name',
    )
));

echo '<pre>' . stripslashes($data) . '</pre>';