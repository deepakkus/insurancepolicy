<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engine Analytics' => array('indexAnalytics'),
    'Days By Company / Engine'
);

Assets::registerChartsJSPackage();

// Sort companies by alphabetical order
//usort($results_company, function($a, $b) { return strnatcasecmp($a['company'], $b['company']); });

$assignments = array_keys(EngScheduling::model()->getEngineAssignments());

?>

<div class="container-fluid marginBottom20">

    <div class ="row-fluid">
        <div class ="span12">

            <div class="form" style="background-color:#FFFFFF; border: 0;">
                <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	                'id'=>'eng-analytics-form'
                )); ?>

                <div class="row">
                    <?php echo CHtml::label('Start Date', ''); ?>
                    <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EngAnalyticsDays[start_date]', 'value' => date('m/d/Y',strtotime($start_date)) )); ?>
                </div>

                <div class="row">
                    <?php echo CHtml::label('End Date', ''); ?>
                    <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EngAnalyticsDays[end_date]', 'value' => date('m/d/Y',strtotime($end_date)) )); ?>
                </div>

                <div class="row buttons">
                    <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
                </div>

                <?php $this->endWidget(); ?>
            </div>

        </div>
    </div>

    <div class="row-fluid marginTop20">
        <div class="span8">

            <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Days Used By Company</h2>
            <canvas id="daysChart" height="450"></canvas>
            <div class="bar-legend"></div>

        </div>
        <div class="span4">

            <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Days Used By Company</h2>
            <table class="table table-striped marginBottom20">
                <tr>
                    <td><strong>Company</strong></td>
                    <td><strong>Fire Days</strong></td>
                    <td><strong>Dedicated Days</strong></td>
                    <td><strong>Total Days</strong></td>
                </tr>

                <?php foreach($results_company as $result): ?>

                <tr>
                    <td><?php echo $result['company']; ?></td>
                    <td><?php echo $result['firedays']; ?></td>
                    <td><?php echo $result['dedicateddays']; ?></td>
                    <td><?php echo $result['firedays'] + $result['dedicateddays']; ?></td>
                </tr>

                <?php endforeach; ?>

                <tr>
                    <td><b>Totals By Company</b></td>
                    <td><b><?php echo array_sum(array_map(function($result) { return $result['firedays']; }, $results_company)); ?></b></td>
                    <td><b><?php echo array_sum(array_map(function($result) { return $result['dedicateddays']; }, $results_company)); ?></b></td>
                    <td><b><?php echo array_sum(array_map(function($result) { return $result['firedays'] + $result['dedicateddays']; }, $results_company)); ?></b></td>
                </tr>
            </table>

        </div>
    </div>

    <div class="row-fluid marginTop20">
        <div class="span12 table-responsive">

            <h2 class="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Days Used By Engine</h2>
            <table class="table table-striped marginBottom20">
                <tr>
                    <td><strong>Engine</strong></td>
                    <?php foreach($assignments as $assignment): ?>
                    <?php echo "<td><b>$assignment</b></td>"; ?>
                    <?php endforeach; ?>
                    <td><b>Total Days</b></td>
                </tr>

                <?php foreach($results_engines as $result): ?>

                <tr>
                    <td><?php echo $result['engine_name'] . (!empty($result['alliance_partner']) ? ' (' . $result['alliance_partner'] . ')' : ''); ?></td>
                    <?php foreach($assignments as $assignment): ?>
                    <td><?php echo $result[$assignment]; ?></td>
                    <?php endforeach; ?>
                    <td><b><?php echo array_sum(array_map(function($assignment) use ($result) { return $result[$assignment]; }, $assignments)); ?></b></td>
                </tr>

                <?php endforeach; ?>

                <tr>
                    <td><b>Totals By Assignment</b></td>
                    <?php foreach($assignments as $assignment): ?>
                    <td><b><?php echo array_sum(array_map(function($result) use ($assignment) { return $result[$assignment]; }, $results_engines)); ?></b></td>
                    <?php endforeach; ?>
                </tr>
            </table>

        </div>
    </div>
</div>

<script type="text/javascript">

    var results = <?php echo json_encode($results_company); ?>;

    var data = {
        labels:  results.map(function(obj) { return obj.company; }),
        datasets: [
            {
                label: 'Fire Days',
                fillColor: 'rgba(255,0,0,0.7)',
                strokeColor: '#666666',
                highlightFill: 'rgba(255,0,0,1.0)',
                highlightStroke: '#666666',
                data: results.map(function(obj) { return obj.firedays; })
            }, {
                label: 'Dedicated Days',
                fillColor: 'rgba(49,163,84,0.8)',
                strokeColor: '#444444',
                highlightFill: 'rgba(49,163,84,1.0)',
                highlightStroke: '#444444',
                data: results.map(function(obj) { return obj.dedicateddays; })
            }
        ]
    };

    var legend_template = '<table>';
    legend_template += '<% for (var i=0; i<datasets.length; i++){ %>';
    legend_template += '<tr><td style="height: 18px; width:30px; background-color:<%=datasets[i].fillColor%>; border:1px solid #CCCCCC;"></td>';
    legend_template += '<td><%if(datasets[i].label){%><%=datasets[i].label%><%}%></td><%}%></tr>';
    legend_template += '</table>';

    var barChartOptions = {
        legendTemplate: legend_template,
        scaleBeginAtZero : false,
        barShowStroke: true,
        barStrokeWidth: 2,
        barValueSpacing: 5,
        barDatasetSpacing: 1,
        responsive: true
    };

    var context = document.getElementById('daysChart').getContext('2d');
    context.canvas.width = context.canvas.parentNode.offsetWidth;
    var barChart = new Chart(context).Bar(data, barChartOptions);

    document.getElementsByClassName('bar-legend')[0].innerHTML = barChart.generateLegend();

</script>