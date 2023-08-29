<?php

/* @var $this ResFireObsController */
/* @var $model ResFireObs */

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Fire Details'
);

Yii::app()->format->datetimeFormat = 'm/d - H:i';

?>

<h1>Manage Fire Details</h1>

<div class ="table-responsive">

    <?php

    //var_dump($model->search()->getData()); die();

    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'id' => 'res-fire-obs-grid',
        'type' => 'striped bordered',
        'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
        'dataProvider' => $model->search(),
        'filter' => $model,
        'columns' => array(
            array(
                'class' => 'CButtonColumn',
                'template' => '{create}{update}{create_log}',
                'header' => 'Actions',
                'headerHtmlOptions' => array('class'=>'grid-column-width-100'),
                'buttons' => array(
                    'create' => array(
                        'url' => '$this->grid->controller->createUrl("/resFireObs/create", array("fireid" => $data->Fire_ID))',
                        'label' => 'Create Details',
                        'imageUrl' => '/images/create.png'
                    ),
                    'update' => array(
                        'url' => '$this->grid->controller->createUrl("/resFireObs/update", array("id" => $data->Obs_ID, "fireid" => $data->Fire_ID))',
                        'label' => 'Update'
                    ),
                    'create_log' => array(
                        'url' => '$this->grid->controller->createUrl("/resMonitorLog/create", array("obs_id" => $data->Obs_ID, "page" => "monitor"))',
                        'label' => 'Add Log Entry',
                        'imageUrl' => '/images/res_log2.png'
                    )
                ),
            ),
            array(
            'header' => 'Fire ID',
            'name' => 'Fire_ID'
            ),
            array(
                'name' => 'fire_name',
                'value' => function($data) { return $data->resFireName ? $data->resFireName->Name : 'No Fire Info'; }
            ),
            array(
                'name' => 'fire_state',
                'value' => function($data) { return $data->resFireName ? $data->resFireName->State : 'No Fire Info'; }
            ),
            array(
                'name' => 'Size',
                'type' => 'html'
            ),
            array(
                'name' => 'Containment',
                'value' => function($data) { return ($data->Containment == 100) ? "<b style='color:green'>$data->Containment</b>" : $data->Containment; },
                'type' => 'html'
            ),
            array(
                'name' => 'Supression',
                'filter' => false,
                'htmlOptions' => array('class' => 'grid-column-width-200'),
            ),
            array(
                'name' => 'Temp',
                'header' => 'Temperature',
                'type' => 'raw',
                'value' => function($data) { return ($data->Temp) ? "{$data->Temp}&#176;F" : ''; },
                'filter' => false
            ),
            array(
                'name' => 'Humidity',
                'value' => function($data) { return ($data->Humidity) ? "{$data->Humidity}%" : ''; },
                'filter' => false
            ),
            array(
                'name' => 'Rating',
                'filter' => CHtml::activeDropDownList($model, 'Rating', $model->getWeatherRatings(), array('prompt' => '','style'=>'min-width:100px'))
            ),
            array(
                'name' => 'Red_Flags',
                'value' => function($data) { return $data->Red_Flags ? CHtml::image('/images/red-flag.png','red flag') : ''; },
                'type' => 'raw',
                'filter' => false
            ),
            array(
                'name' => 'date_created',
                'type' => 'datetime',
                'filter' => false
            ),
            array(
                'name' => 'date_updated',
                'type' => 'datetime',
                'filter' => false
            )
        )
    )); ?>

</div>