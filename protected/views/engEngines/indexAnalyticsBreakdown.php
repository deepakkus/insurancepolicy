<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engine Analytics' => array('indexAnalytics'),
    'Utilization and Breakdown'
);

Assets::registerChartsJSPackage();
Assets::registerMapboxPackage();

Yii::app()->clientScript->registerCssFile('/css/engEngines/index.css');
Yii::app()->clientScript->registerScriptFile('/js/us-states.js',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/engEngines/indexAnalyticsBreakdown.js',CClientScript::POS_HEAD);

?>


<h1 class="center" id="analytics">Engines Analytics</h1>
<p class="center">
    <i>
        (<?php echo date('Y-m-d',strtotime($analytics_start_date)) . ' to ' . date('Y-m-d',strtotime($analytics_end_date)); ?>)
    </i>
</p>

<div class="form marginBottom10 paddingTop10 paddingBottom10" style="float: inherit;">
    <h3 class="center">Select Start and End Dates</h3>

    <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	    'id'=>'eng-analytics-form'
    )); ?>

    <div class="row center">
        <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EnginesAnalytics[reporting_start_date]', 'value' => date('m/d/Y',strtotime($analytics_start_date)) )); ?>
        <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EnginesAnalytics[reporting_end_date]', 'value' => date('m/d/Y',strtotime($analytics_end_date)) )); ?>
    </div>

    <div class="row buttons actionButton center">
        <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
    </div>

    <?php $this->endWidget(); ?>
</div>

<div class="container-fluid">

    <?php $this->widget('bootstrap.widgets.TbButtonGroup', array(
        'toggle' => 'radio',
        'type' => 'success',
        'htmlOptions' => array('class' => 'marginBottom10'),
        'buttons' => array(
            array(
                'label' => '# Days',
                'url' => '#',
                'htmlOptions' => array('class' => 'active', 'id' => 'days')
            ),
            array(
                'label' => '# Engines',
                'url' => '#',
                'htmlOptions' => array('id' => 'engines')
            )
        )
    )); ?>

    <div class="row-fluid">
        <div class="span8">
            <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Engines by month</h2>
            <canvas id="monthChart" height="450"></canvas>
            <div class="bar-legend"></div>
        </div>
        <div class="span4">
            <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Month Breakdown</h2>
            <p>
                <b>Note:</b>
                This tally is not intended to be totaled.  The same engine may work multiple assignments in one month.  Each engine count is out of the total pool, but another count may include the same engine if it worked both assignments that month.
            </p>
            <table class="table table-striped">
                <tr>
                    <td>
                        <strong>Month</strong>
                    </td>
                    <td>
                        <strong>Assignment</strong>
                    </td>
                    <td>
                        <strong># Engines</strong>
                    </td>
                    <td>
                        <strong># Days</strong>
                    </td>
                </tr>
                <?php
                for ($i = 0; $i < count($month_results_array); $i++)
                {
                    $results = $month_results_array[$i];
                    $rowspan = false;
                    if ($results)
                    {
                        foreach($results as $result)
                        {
                            echo '<tr>';
                            if (!$rowspan)
                            {
                                echo '<td rowspan="' . count($results) . '" class="vertalign">' . date('M',strtotime($result['month'])) . '</td>';
                                $rowspan = true;
                            }
                            echo '<td>' . $result['assignment'] . '</td>';
                            echo '<td>' . $result['enginecount'] . '</td>';
                            echo '<td>' . $result['daycount'] . '</td>';
                            echo '</tr>';
                        }
                    }
                    else
                    {
                        echo '<tr>';
                        echo '<td></td>';
                        echo '<td>None</td>';
                        echo '<td>0</td>';
                        echo '<td>0</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">

    var dataEngines = <?php echo json_encode($month_results_array); ?>;

    var labels = [];
    var enginesResponse =  { engines: [], days: [] };
    var enginesDedicated = { engines: [], days: [] };
    var enginesPreRisk =   { engines: [], days: [] };

    // Filling out engine data arrays with data from database method
    dataEngines.forEach(function(datas, index, array) {
        var month = false;
        if (!datas.length) {
            labels.push('');
            enginesResponse.engines.push(0);
            enginesResponse.days.push(0);
            enginesDedicated.engines.push(0);
            enginesDedicated.days.push(0);
            enginesPreRisk.engines.push(0);
            enginesPreRisk.days.push(0);
        }
        else {
            datas.forEach(function(data, index, array) {

                if (!month) {
                    var date = new Date(data.month);
                    var UTCDate = new Date(date.getUTCFullYear(),date.getUTCMonth(),date.getUTCDate());
                    labels.push(UTCDate.getMonthName());
                    month = true;
                }

                if (data.assignment === 'Response') {
                    enginesResponse.engines.push(data.enginecount);
                    enginesResponse.days.push(data.daycount);
                }
                else if (data.assignment === 'Dedicated Service') {
                    enginesDedicated.engines.push(data.enginecount);
                    enginesDedicated.days.push(data.daycount);
                }
                else if (data.assignment === 'Pre Risk') {
                    enginesPreRisk.engines.push(data.enginecount);
                    enginesPreRisk.days.push(data.daycount);
                }
            });

            if (enginesResponse.engines.length !== index + 1) {
                enginesResponse.engines.push(0);
                enginesResponse.days.push(0);
            }
            if (enginesDedicated.engines.length !== index + 1) {
                enginesDedicated.engines.push(0);
                enginesDedicated.days.push(0);
            }
            if (enginesPreRisk.engines.length !== index + 1) {
                enginesPreRisk.engines.push(0);
                enginesPreRisk.days.push(0);
            }
        }
    });

    var dataDayCount = {
        labels: labels,
        datasets: [
            {
                label: 'Response',
                fillColor: 'rgba(255,0,0,0.7)',
                strokeColor: '#666666',
                highlightFill: 'rgba(255,0,0,1.0)',
                highlightStroke: '#666666',
                data: enginesResponse.days
            },
            {
                label: 'Dedicated',
                fillColor: 'rgba(49,163,84,0.8)',
                strokeColor: '#444444',
                highlightFill: 'rgba(49,163,84,1.0)',
                highlightStroke: '#444444',
                data: enginesDedicated.days
            } ,
            {
                label: 'Pre Risk',
                fillColor: 'rgba(44,127,184,0.8)',
                strokeColor: '#222222',
                highlightFill: 'rgba(44,127,184,1.0)',
                highlightStroke: '#222222',
                data: enginesPreRisk.days
            }
        ]
    };

    var dataEngineCount = {
        labels: labels,
        datasets: [
            {
                label: 'Response',
                fillColor: 'rgba(255,0,0,0.7)',
                strokeColor: '#666666',
                highlightFill: 'rgba(255,0,0,1.0)',
                highlightStroke: '#666666',
                data: enginesResponse.engines
            },
            {
                label: 'Dedicated',
                fillColor: 'rgba(49,163,84,0.8)',
                strokeColor: '#444444',
                highlightFill: 'rgba(49,163,84,1.0)',
                highlightStroke: '#444444',
                data: enginesDedicated.engines
            } ,
            {
                label: 'Pre Risk',
                fillColor: 'rgba(44,127,184,0.8)',
                strokeColor: '#222222',
                highlightFill: 'rgba(44,127,184,1.0)',
                highlightStroke: '#222222',
                data: enginesPreRisk.engines
            }
        ]
    };

    var legend_template = '<table>';
    legend_template += '<% for (var i=0; i<datasets.length; i++){ %>';
    legend_template += '<tr><td style="height: 18px; width:30px; background-color:<%=datasets[i].fillColor%>; border:1px solid #CCCCCC;"></td>';
    legend_template += '<td><%if(datasets[i].label){%><%=datasets[i].label%><%}%></td><%}%></tr>';
    legend_template += '</table>';

    //Formatting for chart
    var barChartOptions = {
        legendTemplate: legend_template,
        scaleBeginAtZero : false,
        barShowStroke: true,
        barStrokeWidth: 2,
        barValueSpacing: 5,
        barDatasetSpacing: 1,
        responsive: true
    };

    // Methods for updating the chart and legend
    var updateLegend = function(updated_chart) {
        document.getElementsByClassName('bar-legend')[0].innerHTML = updated_chart.generateLegend();
    };

    var refreshGraph = function(type) {
        updated_chart.clear();
        updated_chart.destroy();
        if (type === 'engines') { updated_chart = bar_chart.Bar(dataEngineCount, barChartOptions) }
        if (type === 'days')    { updated_chart = bar_chart.Bar(dataDayCount, barChartOptions) }
        updateLegend(updated_chart);
    };

    //Initialize the bar chart
    var context = document.getElementById('monthChart').getContext('2d');
    context.canvas.width = context.canvas.parentNode.offsetWidth;
    var bar_chart = new Chart(context);

    var updated_chart = bar_chart.Bar(dataDayCount, barChartOptions);

    //legend
    updateLegend(updated_chart);

    // Event Listeners
    document.getElementById('engines').addEventListener('click', function(e) {
        e.preventDefault();
        refreshGraph('engines');
    });

    document.getElementById('days').addEventListener('click', function(e) {
        e.preventDefault();
        refreshGraph('days');
    });

</script>

<div class="row-fluid" style="margin-bottom:50px;">
    <?php /*$this->widget('bootstrap.widgets.TbExtendedGridView',  // THE FILTERING IS NOT STICKING ON FILTER
    array (                                                    // THE 'BY CLIENT' HAS THE SAME GRID ANYWAYS AND THIS ISSUE IS FIXED THERE
    'id'=>'eng-scheduling-analytics-grid',
    'cssFile' => '../../css/wdsExtendedGridView.css',
    'type' => 'striped hover condensed',
    'dataProvider' => $dataProvider,
    'filter' => $model,
    'columns' => array(
    array(
    'name' => 'state',
    'filter' => CHtml::activeDropDownList($model,'state',CHtml::listData($model->getAvailibleStates(),'state','state'),array('prompt'=>' '))
    ),
    array(
    'name' => 'assignment',
    'filter' => CHtml::activeDropDownList($model,'assignment',CHtml::listData($model->getAvailibleAssignments(),'assignment','assignment'),array('prompt'=>' ')),
    'footer'=>'Totals'
    ),
    array(
    'name' => 'enginecount',
    'class' => 'bootstrap.widgets.TbTotalSumColumn'
    ),
    array(
    'name' => 'daycount',
    'class' => 'bootstrap.widgets.TbTotalSumColumn'
    ),
    ),
    'enableSorting' => true,
    'emptyText' => 'No Statuses have been created.'
    )
    );*/ ?>
</div>

<div class="row-fluid">
    <div class="span4">
        <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Total Engine Utilization</h2>
        <canvas id="total-utilization" height="400"></canvas>
        <table class="table">
            <tr>
                <td>
                    <strong>Adjusted Total Utilization</strong>
                </td>
                <td>
                    <?php echo Yii::app()->numberFormatter->formatPercentage($utilization['Adjusted Total Utilized']); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Adjusted Non Utilization</strong>
                </td>
                <td>
                    <?php echo Yii::app()->numberFormatter->formatPercentage($utilization['Adjusted Non Utilized']); ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="span4">
        <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">WDS Engine Utilization</h2>
        <canvas id="wds-utilization" height="400"></canvas>
        <table class="table">
            <tr>
                <td>
                    <strong>Adjusted Total Utilization</strong>
                </td>
                <td>
                    <?php echo Yii::app()->numberFormatter->formatPercentage($utilization_wds['Adjusted Total Utilized']); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Adjusted Non Utilization</strong>
                </td>
                <td>
                    <?php echo Yii::app()->numberFormatter->formatPercentage($utilization_wds['Adjusted Non Utilized']); ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="span4">
        <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Alliance Engine Utilization</h2>
        <canvas id="alliance-utilization" height="400"></canvas>
        <table class="table">
            <tr>
                <td>
                    <strong>Adjusted Total Utilization</strong>
                </td>
                <td>
                    <?php echo Yii::app()->numberFormatter->formatPercentage($utilization_alliance['Adjusted Total Utilized']); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Adjusted Non Utilization</strong>
                </td>
                <td>
                    <?php echo Yii::app()->numberFormatter->formatPercentage($utilization_alliance['Adjusted Non Utilized']); ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row-fluid" style="margin-bottom:50px;">
    <div class="span12 center">
        <p>
            Adjusted Total Utilization is composed of
            <i>
                <b>Dedicated Service, Pre Risk, Reponse, On Hold</b>
            </i>
        </p>
        <p>
            Adjusted Non Utilization is composed of
            <i>
                <b>Staged, Out of Service, In Storage</b>
            </i>
        </p>
    </div>
</div>

<div class="row-fluid" style="margin-bottom:200px;">
    <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222; width: 80%; margin: 0 auto;">Engines Analytics Map</h2>
    <div id="map-wrapper">
        <div id="map"></div>
    </div>
</div>

<?php

// Shaping Table Analytics into Map Friendly format

/*
Output Ex:
[{
"enginecount": "1",
"assignment": "Dedicated Service",
"state": "CA",
"daycount": "9"
}, {
"enginecount": "1",
"assignment": "Dedicated Service",
"state": "CO",
"daycount": "14"
}, {
"enginecount": "1",
"assignment": "On Hold",
"state": "CO",
"daycount": "5"
}]
 */

$map_data_array = array_map(function($data) {
    return array(
        'state' => $data->state,
        'assignment' => $data->assignment,
        'enginecount' => $data->enginecount,
        'daycount' => $data->daycount
    );
}, $dataProvider->getData());

$_unique_states = array_unique(array_map(function($data) { return $data['state']; }, $map_data_array));

$results_by_state = array();
foreach($_unique_states as $state) {
    $results_by_state[$state] = array();
    foreach($map_data_array as $data) {
        if ($data['state'] === $state) {
            $results_by_state[$state][] = $data;
        }
    }
}
?>

<script type="text/javascript">

    // ------------------------------------------------------------------------
    // MAP

    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;

    var map = new L.mapbox.Map('map').setView([40, -95], 4.5);

    map.removeControl(map.zoomControl)
    map.removeControl(map.attributionControl)

    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
    if (map.tap) map.tap.disable();

    var popup = new L.Popup({ autoPan: false });

    var analyticsData = <?php echo json_encode($results_by_state); ?>;

    // Adding queried data into Feature Collection and gather information for legend scaling

    var daycounts = [];
    var enginecounts = [];

    for (var i = 0; i < statesData.features.length; i++) {
        Object.keys(analyticsData).forEach(function(key) {
            if (key == statesData.features[i].properties.abbr) {
                var array = analyticsData[key];
                statesData.features[i].properties.daycount = array.map(function(data) { return parseInt(data['daycount']); }).sum();
                statesData.features[i].properties.enginecount = array.map(function(data) { return parseInt(data['enginecount']); }).sum();
                statesData.features[i].properties.breakdown = array;
                daycounts.push(statesData.features[i].properties.daycount);
                enginecounts.push(statesData.features[i].properties.enginecount);
            }
        });
    }

    // Building Dynamic Scale
    var maxdaycountscale = [0];
    var maxdaycount = daycounts.max();
    var colors = ['#fee391', '#fec44f', '#fe9929', '#ec7014', '#cc4c02', '#8c2d04'];
    for (var i = 0; i < 5; i++) {
        maxdaycountscale.push(Math.round((maxdaycountscale[maxdaycountscale.length - 1] + maxdaycount / 6) * 100) / 100);
    }

    // statesData comes from the 'us-states.js' script included above
    var statesLayer = new L.geoJson(statesData, {
        style: AnalyticsMap.getStyle,
        onEachFeature: AnalyticsMap.onEachFeature
    }).addTo(map);

    var closeTooltip;

    map.legendControl.addLegend(AnalyticsMap.getLegendHTML());

    // ------------------------------------------------------------------------
    // CHARTS - TOTAL UTILIZATION

    // Formatting for chart
    var pieChartOptions = {
        responsive: true,
        animateScale: true
    };

    var utilization = <?php echo json_encode($utilization); ?>;

    var data = [
        {
            value: Math.round(utilization['Dedicated Service'] * 100),
            color: 'rgba(49,163,84,0.8)',
            highlight: 'rgba(49,163,84,1.0)',
            label: 'Dedicated Service'
        }, {
            value: Math.round(utilization['Pre Risk'] * 100),
            color: 'rgba(44,127,184,0.8)',
            highlight: 'rgba(44,127,184,1.0)',
            label: 'Pre Risk'
        }, {
            value: Math.round(utilization['Response'] * 100),
            color: 'rgba(228,26,28,0.8)',
            highlight: 'rgba(228,26,28,1.0)',
            label: 'Response'
        }, {
            value: Math.round(utilization['On Hold'] * 100),
            color: 'rgba(127,205,187,0.8)',
            highlight: 'rgba(127,205,187,1.0)',
            label: 'On Hold'
        }, {
            value: Math.round(utilization['Staged'] * 100),
            color: 'rgba(237,248,177,0.8)',
            highlight: 'rgba(237,248,177,1.0)',
            label: 'Staged'
        }, {
            value: Math.round(utilization['In Storage'] * 100),
            color: 'rgba(50,203,204,0.8)',
            highlight: 'rgba(50,203,204,0.85)',
            label: 'In Storage'
        }, {
            value: Math.round(utilization['Out of Service'] * 100),
            color: 'rgba(51,51,51,0.8)',
            highlight: 'rgba(51,51,51,0.85)',
            label: 'Out of Service'
        }, {
            value: Math.round(utilization['Non Utilized'] * 100),
            color: 'rgba(51,51,51,0.8)',
            highlight: 'rgba(51,51,51,0.85)',
            label: 'Non Utilized'
        }
    ];

    //Initialize the bar chart
    var context = document.getElementById('total-utilization').getContext('2d');
    context.canvas.width  = context.canvas.parentNode.offsetWidth;

    // For a pie chart
    var totalUtilizationChart = new Chart(context).Pie(data, pieChartOptions);

    // ------------------------------------------------------------------------
    // CHARTS - WDS UTILIZATION

    var utilizationWDS = <?php echo json_encode($utilization_wds); ?>;

    var dataWDS = [
        {
            value: Math.round(utilizationWDS['Dedicated Service'] * 100),
            color: "rgba(49,163,84,0.8)",
            highlight: "rgba(49,163,84,1.0)",
            label: "Dedicated Service"
        }, {
            value: Math.round(utilizationWDS['Pre Risk'] * 100),
            color: "rgba(44,127,184,0.8)",
            highlight: "rgba(44,127,184,1.0)",
            label: "Pre Risk"
        }, {
            value: Math.round(utilizationWDS['Response'] * 100),
            color: "rgba(228,26,28,0.8)",
            highlight: "rgba(228,26,28,1.0)",
            label: "Response"
        }, {
            value: Math.round(utilizationWDS['On Hold'] * 100),
            color: "rgba(127,205,187,0.8)",
            highlight: "rgba(127,205,187,1.0)",
            label: "On Hold"
        }, {
            value: Math.round(utilizationWDS['Staged'] * 100),
            color: "rgba(237,248,177,0.8)",
            highlight: "rgba(237,248,177,1.0)",
            label: "Staged"
        }, {
            value: Math.round(utilizationWDS['In Storage'] * 100),
            color: 'rgba(50,203,204,0.8)',
            highlight: 'rgba(50,203,204,0.85)',
            label: 'In Storage'
        }, {
            value: Math.round(utilizationWDS['Out of Service'] * 100),
            color: "rgba(51,51,51,0.8)",
            highlight: "rgba(51,51,51,0.85)",
            label: "Out of Service"
        }, {
            value: Math.round(utilizationWDS['Non Utilized'] * 100),
            color: "rgba(51,51,51,0.8)",
            highlight: "rgba(51,51,51,0.85)",
            label: "Non Utilized"
        }
    ];

    //Initialize the bar chart
    var contextWDS = document.getElementById('wds-utilization').getContext('2d');
    contextWDS.canvas.width  = contextWDS.canvas.parentNode.offsetWidth;

    // For a pie chart
    var wdsUtilizationChart = new Chart(contextWDS).Pie(dataWDS, pieChartOptions);

    // ------------------------------------------------------------------------
    // CHARTS - ALLIANCE UTILIZATION

    var utilizationAlliance = <?php echo json_encode($utilization_alliance); ?>;

    var dataAlliance = [
        {
            value: Math.round(utilizationAlliance['Dedicated Service'] * 100),
            color: "rgba(49,163,84,0.8)",
            highlight: "rgba(49,163,84,1.0)",
            label: "Dedicated Service"
        }, {
            value: Math.round(utilizationAlliance['Pre Risk'] * 100),
            color: "rgba(44,127,184,0.8)",
            highlight: "rgba(44,127,184,1.0)",
            label: "Pre Risk"
        }, {
            value: Math.round(utilizationAlliance['Response'] * 100),
            color: "rgba(228,26,28,0.8)",
            highlight: "rgba(228,26,28,1.0)",
            label: "Response"
        }, {
            value: Math.round(utilizationAlliance['On Hold'] * 100),
            color: "rgba(127,205,187,0.8)",
            highlight: "rgba(127,205,187,1.0)",
            label: "On Hold"
        }, {
            value: Math.round(utilizationAlliance['Staged'] * 100),
            color: "rgba(237,248,177,0.8)",
            highlight: "rgba(237,248,177,1.0)",
            label: "Staged"
        }, {
            value: Math.round(utilizationAlliance['In Storage'] * 100),
            color: 'rgba(50,203,204,0.8)',
            highlight: 'rgba(50,203,204,0.85)',
            label: 'In Storage'
        }, {
            value: Math.round(utilizationAlliance['Out of Service'] * 100),
            color: "rgba(51,51,51,0.8)",
            highlight: "rgba(51,51,51,0.85)",
            label: "Out of Service"
        }, {
            value: Math.round(utilizationAlliance['Non Utilized'] * 100),
            color: "rgba(51,51,51,0.8)",
            highlight: "rgba(51,51,51,0.85)",
            label: "Non Utilized"
        }
    ];

    //Initialize the bar chart
    var contextAlliance = document.getElementById('alliance-utilization').getContext('2d');
    contextAlliance.canvas.width  = contextAlliance.canvas.parentNode.offsetWidth;

    // For a pie chart
    var allianceUtilizationChart = new Chart(contextAlliance).Pie(dataAlliance, pieChartOptions);

</script>
