<?php

/* @var $this EngSchedulingController */
/* @var $model EngScheduling */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Engine Scheduling'=>array('admin'),
	'View'
);

Yii::app()->clientScript->registerCssFile('/css/engScheduling/view.css');

?>

<?php if (!$print): ?>

    <div class="marginBottom10">
        <?php $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'id'=>'eng-scheduling-view-form'
        )); ?>

        <div>
            <?php $this->widget('bootstrap.widgets.TbDatePicker', array(
                'name' => 'EngSchedulingView[view-date]',
                'value' => date('m/d/Y', strtotime($searchDate)),
                'htmlOptions' => array('style' => 'float: left; margin-right: 10px;')
            )); ?>
        </div>

        <div class="row">
            <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
			<?php echo CHtml::link("<i class = 'icon-print'></i> Print View",$this->createUrl('view',array('print'=>true)),array('class'=>'btn btn-default marginBottom10 pull-right','target'=>'_blank')); ?>
        </div>

        <?php $this->endWidget(); ?>
    </div>

<?php endif; ?>

<!-- Scheduled Engines - Active -->

<?php if ($print): ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Engine Schedule | Print View</title>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/logo.png">
    </head>
    <body>

<?php endif; ?>

<div class="table-responsive">
    <table class="table table-condensed">
        <caption><h2 style="background-color:#222; color:white;"><i>Working - <?php echo date('M d, Y', strtotime($searchDate)); ?></i></h2></caption>
        <tbody>
            <tr>
                <th>Engine Name</th>
                <th>Crew</th>
                <th>Task / Location</th>
                <th>Reason</th>
                <th>Resource Order</th>
                <th>Engine Source/ Assignment</th>
            </tr>
            <?php foreach ($models_active as $model): ?>
            <tr>
                <td>
                    <b><?php echo $model->engine_name . (!empty($model->client_names) ? ' (' . join(' / ',$model->client_names) . ')' : ''); ?></b>
                </td>
                <td>
                    <?php foreach($model->employees as $employee): ?>
                    <?php echo "$employee->crew_first_name $employee->crew_last_name<br />"; ?>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php echo "$model->assignment<br />"; ?>
                    <?php echo "$model->city, $model->state"; ?>
                </td>
                <td>
                    <?php echo $model->comment; ?>
                </td>
                <td>
                    <?php echo $model->resource_order_num; ?>
                </td>
                <td>
                    <?php if ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE): ?>
                    <?php echo '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b> (<i>' . $model->engine->alliance_partner . '</i>)<br />' . $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : ''); ?>
                    <?php else: ?>
                    <?php echo '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b><br />' . $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : ''); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<br />
<hr />
<br />

<div class="table-responsive">
	<h2 style="background-color:#222; color:white;">
		<i>
			Not Working - <?php echo date('M d, Y', strtotime($searchDate)); ?>
		</i>
	</h2>
	<h3>WDS</h3>
    <table class="table table-condensed">
 
        <tbody>
            <tr>
                <th>Engine Name</th>
                <th>Crew</th>
                <th>Task / Location</th>
                <th>Reason</th>
                <th>Engine Source/ Assignment</th>
            </tr>
            <?php foreach ($models_notactive as $model): ?>
            <tr>
                <td>
                    <b><?php echo $model->engine_name . (!empty($model->client_names) ? ' (' . join(' / ',$model->client_names) . ')' : ''); ?></b>
                </td>
                <td>
                    <?php foreach($model->employees as $employee): ?>
                    <?php echo "$employee->crew_first_name $employee->crew_last_name<br />"; ?>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php echo "$model->assignment<br />"; ?>
                    <?php echo "$model->city, $model->state"; ?>
                </td>
                <td>
                    <?php echo $model->comment; ?>
                </td>
                <td>
                    <?php if ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE): ?>
                    <?php echo '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b> (<i>' . $model->engine->alliance_partner . '</i>)<br />' . $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : ''); ?>
                    <?php else: ?>
                    <?php echo '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b><br />' . $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : ''); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<br />
<hr />
<br />

<div class="table-responsive">
	<h3>Alliance</h3>
    <table class="table table-condensed">
        <tbody>
            <tr>
                <th>Engine Name</th>
                <th>Available</th>
                <th>Reason</th>
                <th>Egnine Source</th>
            </tr>
            <?php foreach ($unused_engines as $engine): ?>
            <tr>
                <td>
                    <b><?php echo $engine->engine_name; ?></b>
                </td>
                <td>
                    <?php echo Helper::getBooleanStringFromInt($engine->availible); ?>
                </td>
                <td>
                    <?php echo $engine->reason; ?>
                </td>
                <td>
                    <?php echo $engine->getEngineSource($engine->engine_source); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($print): ?>

    </body>
</html>

<?php endif; ?>