<?php

/* @var $model AuthItem */

$this->widget('zii.widgets.CDetailView', array(
    'data' => $model,
    'htmlOptions' => array(
        'class' => 'table'
    ),
    'itemTemplate' => '<tr><th style="width:1%; white-space: nowrap;">{label}</th><td>{value}</td></tr>',
    'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
    'attributes' => array(
        'name',
        'description'
    )
));