
<?php

/* @var $this EngSchedulingController */
/* @var $model EngScheduling */

$this->breadcrumbs=array(
    'Engines'=>array('engEngines/index'),
    'Engine Scheduling'
);
$enginesViewOnly = in_array('Engine View',Yii::app()->user->types) && (!in_array('Engine',Yii::app()->user->types) && !in_array('Engine Manager',Yii::app()->user->types));

$clientScript = Yii::app()->clientScript;
$clientScript->registerScriptFile('/js/engScheduling/admin.js', CClientScript::POS_END);
$clientScript->registerScriptFile('/js/moment.min.js', CClientScript::POS_HEAD);
$clientScript->registerScriptFile('/js/jquery.floatThead.min.js', CClientScript::POS_HEAD);
$clientScript->registerCssFile('/css/engScheduling/admin.css');

?>

<h1>Engine Scheduling</h1>

<div class="row-fluid">
    <div class="span12 marginTop10">
        <?php if (in_array('Engine Manager',Yii::app()->user->types)): ?>
        <a class="btn btn-success btn-engine" href="<?php echo $this->createUrl('/engScheduling/create'); ?>">Add New Engine to Schedule</a>
        <?php endif; ?>
        <a class="btn btn-success btn-engine" href="<?php echo $this->createUrl('/engScheduling/view'); ?>">View Today's Engines</a>
        <a class="btn btn-primary btn-engine" href="<?php echo $this->createUrl('/engResourceOrder/admin'); ?>">Resource Orders</a>
    </div>
</div>

<div class="marginTop10">
    <?php echo CHtml::link("Calendar Search",'#',array('class'=>'calendar-search-button')); ?>
    <div class="search-form" id="calendar-search-form" style="display: none; padding: 10px; width: 400px;">
        <?php $this->renderPartial('_search_calendar', array(
        'model' => $model
    )); ?>
    </div>
</div>

<div class="row-fluid" style="margin-top: 40px; margin-bottom: 40px;">
    <div id="calendar-container" class="table-responsive">
        <?php echo $this->actionCalendarEngineFeed(date(DateTime::ISO8601)); ?>
    </div>
</div>

<div class="marginTop10">
    <?php echo CHtml::link("Today's Engines by Client",'#',array('class'=>'search-button')); ?>
    <div class="search-form" id="grid-search-form" style="display: none; padding: 10px;">
        <?php $this->renderPartial('_search', array(
        'model' => $model          
    )); ?>
    </div>
</div>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
        'id' => 'eng-scheduling-grid',
        'dataProvider' => $dataProvider,
        'filter' => $model,
        'columns' => array(
            array(
                'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}{resource}',
                'header' => 'Actions',
               /* 'deleteConfirmation' => 'All associated shift tickets will be deleted.  Is this ok?',
                'afterDelete' => 'function(link, success, data) {
                    if (success) {
                        loadCalendar("' . $this->createUrl('engScheduling/calendarEngineFeed') . '", "' . date(DateTime::ISO8601) . '");
                    }
                }',*/
                'buttons' => array(
                    'update' => array(
                        'url' => 'array("/engScheduling/update", "id" => $data->id)',
                        'visible' => function() use ($enginesViewOnly) { return !$enginesViewOnly; }
                    ),
                    'resource' => array(
                        'url' => 'array("/engScheduling/resourceOrder", "id" => $data->id)',
                        'label' => 'Resource Order',
                        'imageUrl' => '/images/read_details.png',
                        'visible' => '!empty($data->resourceOrder)'
                    ),
                    'delete' => array(
                        'visible' => 'in_array("Engine Manager", Yii::app()->user->types)'
                    )
                )
            ),
            //'resource_order_num',
            array(
                'name' => 'resource_order_num',
                'value' => '$data->resource_order_num',
                'filter' => CHtml::activeNumberField($model,'resource_order_num',array('numerical', 'integerOnly'=>true))
            ),
            array(
                'name' => 'client_names',
                'value' => 'implode("<br />", $data->client_names);',
                'type' => 'html',
                'filter' => CHtml::activeDropDownList($model,'client_id',CHtml::listData($model->getAvailibleFireClients(), 'id', 'name'),array('prompt'=>''))
            ),
            array(
                'name' => 'engine_name',
                'filter' => CHtml::activeDropDownList($model,'engine_name',CHtml::listData($model->getAvailibleEngines(),'engine_name','engine_name'),array('prompt'=>''))
            ),
            'comment',
            array(
                'name' => 'assignment',
                'filter' => CHtml::activeDropDownList($model,'assignment',$model->getEngineAssignments(),array('prompt'=>''))
            ),
            'fire_name',
            'city',
            'state',
            array(
                'name' => 'start_date',
                'filter' => false
            ),
            array(
                'name' => 'end_date',
                'filter' => false
            )
        )
    )); ?>

</div>

<?php

// Empty Modal for scheduling a new engine.

$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id' => 'schedule-engine-modal',
    'htmlOptions' => array('class' => 'modal-admin')
));

// This div will be dynamically filled with content
echo CHtml::tag('div', array('id'=>'modal-content'), '');
$this->endWidget();

?>