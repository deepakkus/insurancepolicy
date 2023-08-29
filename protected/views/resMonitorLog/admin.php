
<?php

/* @var $this ResMonitorLogController */
/* @var $model ResMonitorLog */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Monitor Log'
);
Yii::app()->format->dateFormat = 'm/d/Y';
Yii::app()->format->booleanFormat = array('', '&#10004;');
Yii::app()->clientScript->registerScriptFile('/js/resMonitorLog/admin.js');
Yii::app()->clientScript->registerScript('refreshLogGrid','
    setInterval(function() {
        $.fn.yiiGridView.update("res-monitor-log-grid");
    }, 60000 * 5);
');
Yii::app()->clientScript->registerCSS('monitorLogCss', '
    .smokecheck a {
        color: #FF0000
    }
');

?>

<h1>Monitor Log</h1>

<a class="column-toggle paddingRight20" href="#">Columns</a>

<?php

echo $this->renderPartial('_adminExtraColumns', array('columnsToShow' => $columnsToShow));

$columnsArray = array(
    array(
        'class' => 'bootstrap.widgets.TbButtonColumn',
        'template' => '{update}{delete}',
        'header' => 'Actions',
        'updateButtonUrl' => '$this->grid->controller->createUrl("/resMonitorLog/update", array("id"=>$data->Monitor_ID, "page"=>"monitor"))',
    ),
	array(
        'name'=>'Dispatcher',
        'filter'=>CHtml::activeDropDownList($model,'Dispatcher',CHtml::listData($model->findAll(array('select'=>'Dispatcher','distinct'=>true)),'Dispatcher','Dispatcher'),array('prompt'=>'')),
        'visible' => in_array('Dispatcher', $columnsToShow) ? true : false
    ),
	array(
        'name' => 'monitored_date_stamp',
        'header' => 'Monitored Date',
        'type' => 'date',
        'filter' => CHtml::activeDateField($model,'monitored_date'),
        'visible' => in_array('monitored_date_stamp', $columnsToShow) ? true : false
    ),
	array(
        'header' => 'Time',
        'name' => 'monitored_time_stamp',
        'filter' => false,
        'visible' => in_array('monitored_time_stamp', $columnsToShow) ? true : false

    ),
    array(
        'header' => 'Fire ID',
        'name' => 'fire_id'
    ),
    array(
        'name' => 'fire_name',
        'value' => '$this->grid->controller->getFireNameUpdated($data, "monitor")',
        'type' => 'html',
        'visible' => in_array('Fire_Name', $columnsToShow) ? true : false
    ),
    array(
        'name' => 'fire_alternate_name',
        'visible' => in_array('fire_alternate_name', $columnsToShow) ? true : false
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
        'visible' => in_array('Fire_Size', $columnsToShow) ? true : false
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
        //'name' => 'closest',
        'header' => 'Closest Policyholder',
        'value' => function($data){
            $closest = array();
            foreach ($data->resMonitorTriggered as $resTriggered) {
                if ($resTriggered->client_id != 999 && $resTriggered->closest != '25+') {
                    $closest[] = $resTriggered->closest;
                }
            }

            return (!empty($closest)) ? min($closest) . ' miles' : '25+ miles';
        },
        'type' => 'html',
        'htmlOptions' => array('style'=>'max-width:125px;'),
        'visible' => in_array('closest', $columnsToShow) ? true : false

    ),
    array(
        'name' => 'client_triggered',
        'header' => 'Triggered',
        'value' => 'implode("<br />", $data->client_triggered);',
        'type' => 'html',
        'htmlOptions' => array('style'=>'min-width:125px;'),
        'filter' => CHtml::activeDropDownList($model, 'client_id', CHtml::listData($model->getAvailibleFireClients(), 'id', 'name'), array('prompt' => '')),
        'visible' => in_array('client_triggered', $columnsToShow) ? true : false
    ),
    array(
        'name' => 'client_noteworthy',
        'header' => 'Noteworthy',
        'value' => 'implode("<br />", $data->client_noteworthy);',
        'type' => 'html',
        'filter' => false,
        'visible' => in_array('client_noteworthy', $columnsToShow) ? true : false

    ),
    array(
        'name' => 'Comments',
        'filter' => false,
        'htmlOptions' => array('class' => 'grid-column-width-200'),
        'visible' => in_array('Comments', $columnsToShow) ? true : false
    ),
    array(
        'header' => 'Fire Smokecheck',
        'value' => '(isset($data->resFireObs)) ? $data->resFireObs->resFireName->Smoke_Check : ""',
        'type' => 'boolean',
        'visible' => in_array('resFireObs', $columnsToShow) ? true : false
    ),
    array(
        'header' => 'Entry Smokecheck',
        'value' => '$data->Smoke_Check',
        'type' => 'boolean',
        'filter' => CHtml::activeDropDownList($model,'Smoke_Check',array(true => '&#10004;'),array('encode'=>false,'prompt' => '')),
        'visible' => in_array('Smoke_Check', $columnsToShow) ? true : false
    )
);

?>

<div class="row-fluid marginTop20">
    <div class ="span12">
        <a class="btn btn-success" href="<?php echo $this->createUrl('/resMonitorLog/monitorFire'); ?>">New Fire</a>
        <a class="btn btn-success" href="<?php echo $this->createUrl('/resFireObs/admin'); ?>">Update Fire</a>
        <a class="btn btn-success" href="<?php echo $this->createUrl('/resMonitorLog/smokeCheck'); ?>">View Smokechecks</a>
    </div>
</div>

<?php echo $this->renderPartial('_adminDownload'); ?>

<?php echo $this->renderPartial('_adminHelp'); ?>

<div class ="table-responsive">

<?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	'id' => 'res-monitor-log-grid',
    'type' => 'striped bordered condensed',
    'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>$columnsArray
)); ?>

</div>
