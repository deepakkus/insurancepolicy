<?php 

/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engine Analytics'
);

$this->renderPartial('//site/indexAnalyticsNav');

Assets::registerChartsJSPackage();

$script = '
    var onHold = ' . json_encode(filter_var($engineReportForm->onhold, FILTER_VALIDATE_BOOLEAN)) . ';
    var responseEngines = ' . json_encode($tallyEngineResponse) . ';
    var dedicatedEngines = ' . json_encode($tallyEngineDedicated) . ';
    var onHoldEngines = ' . json_encode($tallyEngineOnHold) . ';
    var totalEngines = ' . json_encode($tallyEngineTotal) . ';
    var policiesTriggered = ' . json_encode($tallyPoliciesTriggered) . ';
    var dispatchedFires = ' . json_encode($tallyDispatchedFires) . ';

    AnalyticsEnginesChart.init(responseEngines, dedicatedEngines, onHoldEngines, totalEngines, onHold);
    AnalyticPoliciesChart.init(policiesTriggered);
    AnalyticDispatchedFiresChart.init(dispatchedFires);
';

$css = "
    .help-block {
        color: #737373;
    }
    .engine-report-day-form-loading {
        height: 30px;
        width: 30px;
    }
    .engine-report-day-form-loading.active {
        background-image: url('images/loading.gif');
        background-repeat: no-repeat;
        background-size: 20px 20px;
        background-position: center;
        position: absolute;
    }
";

Yii::app()->clientScript->registerScriptFile('/js/engEngines/indexAnalytics.js',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript(1, $script);
Yii::app()->clientScript->registerCss(1, $css);

?>

<div class="clearfix width100">
    <?php $this->widget('bootstrap.widgets.TbButtonGroup', array(
        'size' => 'large',
        'type' => 'info',
        'htmlOptions' => array('class' => 'pull-right'),
        'buttons' => array(
            array(
                'label' => 'Other Engine Analytics',
                'items' => array(
                    array('label' => 'Utilization and Breakdown', 'url' => $this->createUrl('/engEngines/indexAnalyticsBreakdown')),
                    '---',
                    array('label' => 'Crew Member Breakout', 'url' => $this->createUrl('/engEngines/indexAnalyticsCrew')),
                    '---',
                    array('label' => 'Assignment By Client', 'url' => $this->createUrl('/engEngines/indexAnalyticsClient')),
                    '---',
                    array('label' => 'Days Used By Company / Engine', 'url' => $this->createUrl('/engEngines/indexAnalyticsDays')),
                    '---',
                    //array('label' => 'Policyholder Report', 'url' => $this->createUrl('/engEngines/indexAnalyticsPolicyholder'), 'visible'=>in_array(Yii::app()->params['env'], array('dev','local'))),
                )
            ),
        ),
    )); ?>
    <a class="btn btn-large btn-info pull-right marginRight10" href="<?php echo $this->createUrl('/engEngines/indexAnalyticsMap'); ?>">Today's Engines</a>
</div>

<!-- Engine Report View -->

<h1>Engine Report View</h1>

<!-- Engine Report Form -->

<?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'engine-report-form',
    'type' => 'horizontal',
    'enableAjaxValidation' => true,
    //'focus' => array($engineReportForm,'startdate'),
    'htmlOptions' => array(
        'class' => 'well'
    ),
    'clientOptions' => array(
        'validateOnSubmit' => true
    )
)); ?>

<div class="row-fluid">
    <div class="span12">
        <p class="lead">Select attributes for statistic over a period of time.</p>
        <?php echo $form->errorSummary($engineReportForm); ?>
    </div>
</div>

<div class="row-fluid">
    <div class="span6">
        <?php

        echo $form->dropDownListRow($engineReportForm, 'clientids', $engineReportForm->getActiveWdsfireClients(), array(
            'multiple' => true,
            'size' => '8',
            'hint' => 'Hold down CTRL to select multiple types.'
        ));

        echo $form->dropDownListRow($engineReportForm, 'sources', EngEngines::model()->getEngineSources(), array(
            'multiple' => true,
            'size' => '3',
            'hint' => 'Hold down CTRL to select multiple types.'
        ));

        ?>
    </div>
    <div class="span6">
        <?php

        echo $form->datepickerRow($engineReportForm, 'startdate', array(
            'prepend' => '<i class="icon-calendar"></i>',
            'placeholder' => $engineReportForm->getAttributeLabel('startdate') . ' ...',
            'options' => array(
                'format' => 'yyyy-mm-dd',
                'autoclose' => true,
                'todayHighlight' => true
            ),
            'readOnly' => true,
            'style' => 'cursor:pointer;',
        ));

        echo $form->datepickerRow($engineReportForm, 'enddate', array(
            'prepend' => '<i class="icon-calendar"></i>',
            'placeholder' => $engineReportForm->getAttributeLabel('enddate') . ' ...',
            'options' => array(
                'format' => 'yyyy-mm-dd',
                'autoclose' => true,
                'todayHighlight' => true
            ),
            "readOnly" => true,
            'style' => 'cursor:pointer;',

        ));

        echo $form->checkBoxRow($engineReportForm, 'onhold');

        ?>
    </div>
</div>

<div class="row-fluid">
    <div class="span12">
        <?php echo CHtml::submitButton('Search', array('class' => 'submit','id' => 'search_report')); ?>
    </div>
</div>

<?php

$this->endWidget();
unset($form);

?>

<!-- Graphical statistic over period of time -->

<div class="row-fluid paddingTop20 paddingBottom10" style="overflow-x: auto;">
    <div class="span12">
        <p class="lead">Engines <small>(click on graph to search for day stats)</small></p>
        <?php if (count($tallyEngineTotal)): ?>
        <div id="engines-chart-parent">
            <canvas id="engines-chart" height="400"></canvas>
            <div id="engine-chart-legend"></div>
        </div>
        <?php else: ?>
        <p class="lead text-center"><strong>No Results</strong></p>
        <?php endif; ?>
    </div>
</div>

<div class="row-fluid paddingTop20 paddingBottom10" style="overflow-x: auto;">
    <div class="span12">
        <p class="lead">Policies Triggered (Dispatched)</p>
        <?php if (count($tallyPoliciesTriggered)): ?>
        <div id="policies-chart-parent">
            <canvas id="policies-chart" height="400"></canvas>
            <div id="policies-chart-legend"></div>
        </div>
        <?php else: ?>
        <p class="lead text-center"><strong>No Results</strong></p>
        <?php endif; ?>
    </div>
</div>

<div class="row-fluid paddingTop20 paddingBottom10" style="overflow-x: auto;">
    <div class="span12">
        <p class="lead">Fires (Dispatched)</p>
        <?php if (count($tallyDispatchedFires)): ?>
        <div id="fires-chart-parent">
            <canvas id="fires-chart" height="400"></canvas>
            <div id="fires-chart-legend"></div>
        </div>
        <?php else: ?>
        <p class="lead text-center"><strong>No Results</strong></p>
        <?php endif; ?>
    </div>
</div>

<!-- Tabular statistic over period of time -->

<div class="row-fluid">
    <div class="span12">
        <p class="lead">Tabular Statistics</p>
        <?php if (!count($tallyEngineTotal)): ?>
        <p class="lead text-center"><strong>No Results</strong></p>
        <?php else: ?>

        <!-- The "header" is a different table, so it doesn't scroll -->
        <table class="table table-condensed table-striped table-hover" style="table-layout:fixed">
            <caption style="font-weight: bold; font-size: 16px;">Engine Stats By Day</caption>
            <thead>
                <tr style="border-bottom: 1px solid black;">
                    <th>Date</th>
                    <th># Fires</th>
                    <th># Policyholders</th>
                    <th># Engines</th>
                    <th># Response Engines</th>
                    <th># Dedicated Engines</th>
                </tr>
            </thead>
        </table>
        
        <!-- Data Table -->
        <div style="max-height: 400px; overflow-y: scroll;">
            <table class="table table-condensed table-striped table-hover" style="table-layout:fixed">
                <tbody>
                    <?php

                    $nozeroes = function($data) { return $data === 0 ? '' : $data; };

                    foreach ($tallyEngineTotal as $timestamp => $result)
                    {
                        $totalEngines = $result;
                        $responseEngines = $tallyEngineResponse[$timestamp];
                        $dedicatedEngines = $tallyEngineDedicated[$timestamp];
                        $policiesTriggered = $tallyPoliciesTriggered[$timestamp];
                        $dispatchedFires = $tallyDispatchedFires[$timestamp];

                        echo '<tr>';
                        echo '<td><a data-date="' . $timestamp  . '" href="#" class="date-select">' . date('M d, Y', $timestamp) . '</a></td>';
                        echo '<td>' . $nozeroes($dispatchedFires['count']) . '</td>';
                        echo '<td>' . $nozeroes($policiesTriggered['count']) . '</td>';
                        echo '<td>' . $nozeroes($totalEngines['count']) . '</td>';
                        echo '<td>' . $nozeroes($responseEngines['count']) . '</td>';
                        echo '<td>' . $nozeroes($dedicatedEngines['count']) . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<hr style="height:30px; margin-top:60px; border-top: 1px solid black;" />

<!-- Engine Report Form -->

<?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'engine-report-day-form',
    'type' => 'horizontal',
    'enableAjaxValidation' => true,
    'action' => array('engEngines/indexAnalyticsDayRender'),
    'htmlOptions' => array(
        'class' => 'well'
    ),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validationUrl' => $this->createUrl($this->route),
        'afterValidate' => new CJavaScriptExpression('function(form, data, hasError) { 
            if (!hasError) {
                var loading = $(".engine-report-day-form-loading");
                loading.addClass("active");
                $.ajax({
                    "type": "POST",
                    "url": form.attr("action"),
                    "data": form.serialize(),
                    "success": function (data) {
                        loading.removeClass("active");
                        $("#engine-report-day-form-results").html(data);
                    }
                });
            }
        }')
    )
)); ?>

<?php

echo $form->hiddenField($engineReportDayForm, 'clientids');
echo $form->hiddenField($engineReportDayForm, 'sources');
echo $form->hiddenField($engineReportDayForm, 'onhold');

?>

<div class="row-fluid">
    <div class="span12">
        <p class="lead">Search for detailed stats by day.</p>
        <?php echo $form->errorSummary($engineReportDayForm); ?>
    </div>
</div>

<div class="row-fluid">
    <div class="span6">
        <?php

        echo $form->dropDownListRow($engineReportDayForm, 'date', $engineReportDayForm->getDate($engineReportForm), array(
            'prompt' => 'Select a date',
            'hint' => '<div id="no-fires-found" style="display: none;"><i style="color:red">No fires found</i></div>'
        ));

        echo $form->dropDownListRow($engineReportDayForm, 'fires', array(), array(
            'multiple' => true,
            'size' => '7',
            'hint' => 'Select a date to populate availible fires.<br />Hold down CTRL to select multiple types.'
        ));

        ?>
    </div>
    <div class="span6">
        <?php

        echo $form->dropDownListRow($engineReportDayForm, 'alliance', $engineReportDayForm->getAlliance(), array(
            'multiple' => true,
            'size' => '8',
            'hint' => 'Hold down CTRL to select multiple types.'
        ));

        echo $form->dropDownListRow($engineReportDayForm, 'assignment', $engineReportDayForm->getAssignments(), array(
            'multiple' => true,
            'size' => '4',
            'hint' => 'Hold down CTRL to select multiple types.'
        ));
        
        ?>
    </div>
</div>

<div class="row-fluid">
    <div class="span12">
        <?php echo CHtml::submitButton('Search', array('class' => 'submit')); ?>
        <?php echo CHtml::button('Clear Selects', array('id' => 'clear-selects')); ?>
    </div>
</div>

<?php

$this->endWidget();
unset($form);

?>

<div class="row-fluid paddingTop20">
    <div class="span12">
        <p class="lead">Stats by Day <span class="engine-report-day-form-loading"></span></p>
        <div id="engine-report-day-form-results">
            <p class="lead text-center"><strong>No Results</strong></p>
        </div>
    </div>
</div>

