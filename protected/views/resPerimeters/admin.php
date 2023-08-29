<?php

/* @var $this ResPerimetersController */
/* @var $model ResPerimeters */

$this->breadcrumbs = array(
    'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Perimeters'
);

Yii::app()->format->datetimeFormat = 'Y-m-d H:i';

?>

<h2>Perimeters | Threats</h2>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'res-perimeters-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}',
            'header' => 'Edit Perimeter',
            'updateButtonUrl' => '$this->grid->controller->createUrl("/resPerimeters/updatePerimeter", array("id" => $data->id))'
        ),
        array(
            'header' => 'Fire ID',
            'name' => 'fire_id'
            ),
        array(
            'name' => 'fire_name',
            'header' => 'Download Perimeter KML',
            'value' => 'CHtml::link($data->fire_name, array("/resPerimeters/downloadPerimeterKML", "id" => $data->id))',
            'type' => 'raw'
        ),
        array(
            'header' => 'Threat Actions',
            'value' => 'empty($data->threat_location_id) ?
                CHtml::link("<img src=\"/images/res_threat.png\" title=\"New Threat\">", array("/resPerimeters/createThreat", "id" => $data->id)) :
                CHtml::link("<i class=\"icon-pencil\"></i>", array("/resPerimeters/updateThreat", "id" => $data->id))',
            'type' => 'raw'
        ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'Download Threat KML',
            'labelExpression' => '$data->fire_name',
            'urlExpression' => 'array("/resPerimeters/downloadThreatKML", "id" => $data->id)',
            'cssClassExpression' => 'empty($data->threat_location_id) ? "no-link" : ""',
        ),
        array(
            'name' => 'date_created',
            'type' => 'datetime',
            'filter' => CHtml::activeDateField($model, 'date_created')
        ),
        array(
            'name' => 'date_updated',
            'type' => 'datetime',
            'filter' => CHtml::activeDateField($model, 'date_updated')
        )
    )
)); ?>
