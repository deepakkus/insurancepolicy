<?php

/* @var $this ResDailyThreatController */
/* @var $model ResDailyThreat */

$this->breadcrumbs = array(
    'Response'  =>  array('/resNotice/landing'),
    'Regional Conditions'
);

?>

<h1>Regional Danger Ratings</h1>

<div>
    <a class="btn btn-success" href="<?php echo $this->createUrl('/resDailyThreat/create'); ?>">Create New Daily Threat</a>
</div>

<div class ="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'id' => 'res-daily-threat-grid',
        'type' => 'striped bordered condensed',
        'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
        'dataProvider' => $model->search(),
        //'filter' => $model,
        'columns' => array(
            array('class' => 'CButtonColumn',
                'template' => '{update}',
                'header' => 'Edit Entry',
                'updateButtonUrl'  =>  '$this->grid->controller->createUrl("/resDailyThreat/update", array("id" => $data->threat_id,"type" => "update"))'
            ),
            array(
                'name' => 'date_updated',
                'value' => function($data) { return date('m-d-Y H:i',strtotime($data->date_updated)); }
            ),
            array(
                'class' => 'CLinkColumn',
                'header' => 'View Stats',
                'label' => 'View Stats',
                'urlExpression' => '$this->grid->controller->createUrl("/resDailyThreat/viewDailyStats", array("id" => $data->threat_id))',
                'linkHtmlOptions' => array('class' => 'view-data')
            ),
            array(
                'name' => 'eastern',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->eastern)); }
            ),
            array(
                'name' => 'southern',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->southern)); }
            ),
            array(
                'name' => 'southwest',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->southwest)); }
            ),
            array(
                'name' => 'great_basin',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->great_basin)); }
            ),
            array(
                'name' => 'california_south',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->california_south)); }
            ),
            array(
                'name' => 'california_north',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->california_north)); }
            ),
            array(
                'name' => 'rocky_mountains',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->rocky_mountains)); }
            ),
            array(
                'name' => 'northern_rockies',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->northern_rockies)); }
            ),
            array(
                'name' => 'northwest',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->northwest)); }
            ),
            array(
                'name' => 'alaska',
                'type' => 'raw',
                'value' => function($data) { return ($data->getColorRating($data->alaska)); }
            )
        )
    )); ?>

</div>

<?php

Yii::app()->clientScript->registerScript(1, '

    // Open JUI modal popup
    $(document).on("click", ".view-data", function() {
        $("#modal-container")
            .find("#modal-content")
            .load($(this).attr("href"), function() {
                $("#modal-container").dialog("open");
            });
        return false;
    });

');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id' => 'modal-container',
    'options' => array(
        'title' => 'Daily Stats',
        'autoOpen' => false,
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