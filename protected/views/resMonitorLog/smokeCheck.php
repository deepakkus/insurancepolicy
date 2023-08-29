<?php

/* @var $this ResMonitorLogController */
/* @var $model ResMonitorLog */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Smokechecks'
);

Yii::app()->format->booleanFormat = array('', '&#10004;');
Yii::app()->clientScript->registerScriptFile('/js/resMonitorLog/smokecheck.js');
Yii::app()->clientScript->registerCSS('monitorLogCss','
    .smokecheck a {
        color: #FF0000
    }
');

?>

<h1>Smoke Checks</h1>

<a class="column-toggle paddingRight20" href="#">Columns</a>

<?php

echo $this->renderPartial('_adminExtraColumnsSmoke', array('columnsToShow' => $columnsToShow));

$columnsArray = array(
    array(
        'class' => 'bootstrap.widgets.TbButtonColumn',
        'template' => '{update}',
        'header' => 'Actions',
        'updateButtonUrl' => '$this->grid->controller->createUrl("/resMonitorLog/update", array("id"=>$data->Monitor_ID, "page"=>"smokecheck"))'
    ),
	array(
        'name' => 'Dispatcher',
        'filter'=>CHtml::activeDropDownList($model,'Dispatcher',CHtml::listData($model->findAll(array('select'=>'Dispatcher','distinct'=>true)),'Dispatcher','Dispatcher'),array('prompt'=>''))
    ),
	array(
        'name' => 'monitored_date_stamp',
        'header' => 'Monitored Date',
        'filter' => CHtml::activeDateField($model,'monitored_date')
    ),
	array(
        'header' => 'Time',
        'name' => 'monitored_time_stamp',
        'filter' => false
    ),
    array(
        'name' => 'fire_name',
        'value' => '$this->grid->controller->getFireNameUpdated($data, "smokecheck")',
        'type' => 'html'
    ),
    array(
        'name' => 'fire_city',
        'visible' => in_array('Fire_City', $columnsToShow) ? true : false
    ),
    array(
        'name' => 'fire_state',
        'visible' => in_array('Fire_State', $columnsToShow) ? true : false
    ),
    array(
        'name' => 'fire_size',
        'value' => 'is_numeric($data->fire_size) ? ($data->fire_size == -1 ? "unknown" : $data->fire_size . " acres") : $data->fire_size',
        'visible' => in_array('Fire_Size', $columnsToShow) ? true : false,
        'filter' => false
    ),
    array(
        'name' => 'fire_containment',
        'header' => 'Containment',
        'value' => '$this->grid->controller->getFireContainment($data)',
        'type' => 'html',
        'htmlOptions' => array('class' => 'grid-column-width-100'),
        'visible' => in_array('Fire_Containment', $columnsToShow) ? true : false,
        'filter' => false
    ),
    array(
        'header' => 'Fuels',
        'value' => '$this->grid->controller->getMonitorFuels($data)',
        'visible' => in_array('Fire_Fuels', $columnsToShow) ? true : false
    ),
    array(
        'name' => 'client_triggered',
        'header' => 'Triggered',
        'value' => 'implode("<br />",$data->client_triggered);',
        'type' => 'html',
        'htmlOptions' => array('class' => 'grid-column-width-100'),
        'filter' => CHtml::activeDropDownList($model,'client_id',CHtml::listData($model->getAvailibleFireClients(), 'id', 'name'),array('prompt' => ''))
    ),
    array(
        'name' => 'client_noteworthy',
        'header' => 'Noteworthy',
        'value' => 'implode("<br />",$data->client_noteworthy);',
        'type' => 'html',
    ),
    array(
        'name' => 'Comments',
        'filter' => false,
        'htmlOptions' => array('class' => 'grid-column-width-200'),
    ),
	/*
    array(
        'header' => 'DO Review',
        'value' => '$data->Smoke_Check_DO_Review',
        'type' => 'boolean'
    ),
	*/
    //array(
    //    'class' => 'CLinkColumn',
    //    'header' => 'Mapping',
    //    'label' => 'View Map',
    //    'urlExpression' => '$data->Perimeter_ID ? $this->grid->controller->createUrl("/resMonitorLog/viewMonitoredFire", array("id" => $data->Monitor_ID, "page" => "monitor")) : ""',
    //    'cssClassExpression' => '$data->Perimeter_ID ? "" : "no-link"',
    //    'linkHtmlOptions' => array('target' => '_blank')
    //)
);

$model->smoke_check_only = 1;

?>

<div class="marginTop20">
    <a class="btn btn-success" href="<?php echo $this->createUrl('/resMonitorLog/admin'); ?>">Monitor Log</a>
</div>

<div class ="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	    'id' => 'res-smokecheck-grid',
        'type' => 'striped bordered condensed',
        'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
	    'dataProvider' => $model->search(),
	    'filter' => $model,
	    'columns' => $columnsArray
    )); ?>

</div>