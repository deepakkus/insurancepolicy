<?php
/* @var $this ResDedicatedController */
/* @var $model ResDedicated */

$this->breadcrumbs=array(
	'Dedicated Analytics'
);

Assets::registerChartsJSPackage();

Yii::app()->clientScript->registerCss(1, '

    .table-fires {
        margin-bottom: 25px;
    }

    .table-fires th { 
        border-top: none !important;
    }

');

$this->renderPartial('//site/indexAnalyticsNav'); 

?>

<div class="text-right">
    <a class="btn btn-info" href="<?php echo $this->createUrl('/resDedicated/indexAllClients'); ?>">All Clients</a>
    <!--<a class="btn btn-info" href="<?php //echo $this->createUrl('/clientDedicatedHours/index'); ?>">Shift Ticket Dedicated Analytics</a>-->
</div>
<!--<div class="text-right">
    <p>(Experimental)</p>
</div>-->

<h1 class="center">Dedicated Service Analytics</h1>
<p class="center lead"><b>(<?php echo Client::model()->find('id = ' . $clientid)->name; ?>)</b></p>

<!-- Client Search Form -->
<div class="row-fluid">
    <div class ="span12">
        <div class="form" style="background-color:#FFFFFF; border: 0;">
            <?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	            'id' => 'res-dedicated-anlytics-form'
            )); ?>
            <div>
                <?php echo CHtml::label('Start Date', ''); ?>
                <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'ResDedicatedAnalytics[client_start_date]', 'value' => date('m/d/Y',strtotime($clientstartdate)) )); ?>
            </div>
            <div>
                <?php echo CHtml::label('End Date', ''); ?>
                <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'ResDedicatedAnalytics[client_end_date]', 'value' => date('m/d/Y',strtotime($clientenddate)) )); ?>
            </div>
            <div>
                <?php echo CHtml::label('Clients', ''); ?>
                <?php echo CHtml::dropDownList('ResDedicatedAnalytics[client_id]', '', CHtml::listData($clients, 'id', 'name'), array(
                    'options' => array( $clientid => array('selected'=>true))
                )); ?>
            </div>
            <div class="buttons">
                <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>

<br />
<br />
<br />
<br />

<h2 class="center">General Stats</h2>

<div class="row-fluid">
    <div style="width:1200px; margin: 0 auto;" >
        <canvas id="dedicatedStatsChart" width="400" height="300"></canvas>
        <div class="bar-legend"></div>
    </div>
</div>
<div class="row-fluid" style="padding-top:30px;">
    <div style="width:1200px; margin: 0 auto;" >
        <table class="table table-fires">
            <tr>
                <th>Days Used per Month</th>
                <th>Days Remaining for Year</th>
                <th>Days Allowed for Year</th>
                <th>Dedicated Month</th>
                <th>Dedicated Year</th>
            </tr>
            <?php foreach ($dedicatedHours as $dedicated): ?>
            <tr>
                <td><?php echo round($dedicated['hours_used']/8, 2) ?></td>
                <td><?php echo round($dedicated['hours_remaining']/8, 2) ?></td>
                <td><?php echo round($dedicated['dedicated_year_hours']/8, 2) ?></td>
                <td><?php echo date('Y - M', strtotime($dedicated['dedicated_month'])); ?></td>
                <td><?php echo date('Y', strtotime($dedicated['dedicated_year'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<br />
<br />
<br />
<br />

<h2 class="center">Month Breakdown</h2>
<p class="center"><b><i>(One Dedicated Service Day is 8 Hours.)</i></b></p>

<?php foreach(array_chunk($dedicatedHoursMonthBreakdown, 2, true) as $dedicatedStatsArray): ?>
    <div class="row-fluid">
        <?php foreach($dedicatedStatsArray as $dedicated): ?>
        <div class="span6">
            <h2><small> Month: <?php echo date('F Y', strtotime($dedicated['dedicated_date'])) ?></small></h2>
            <div class="table-responsive">
                <table class="table table-fires">
                    <tr>
                        <th style="padding: 5px;">State</th>
                        <th style="padding: 5px;">Days Used</th>
                        <th style="padding: 5px;">State</th>
                        <th style="padding: 5px;">Days Used</th>
                    </tr>

                    <?php foreach(array_chunk($dedicated['dedicated_states'], 2, true) as $values): ?>
                    <tr>
                        <?php foreach($values as $key => $value): ?>
                        <td class="td-padding"><?php echo $key ?></td>
                        <td class="td-padding"><?php echo round($value/8, 2) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>

                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<script type="text/javascript">

    Date.prototype.monthNames = [
        'January', 'February', 'March','April', 'May', 'June','July', 'August', 'September','October', 'November', 'December'
    ];

    Date.prototype.getMonthName = function() {
        return this.monthNames[this.getMonth()];
    };

    function createDedicatedGraph() {

        var dedicatedData = <?= json_encode($dedicatedHours); ?>;

        var labels = dedicatedData.map(function(data) {
            var date = new Date(data.dedicated_month);
            var UTCDate = new Date(date.getUTCFullYear(),date.getUTCMonth(),date.getUTCDate());
            return UTCDate.getFullYear() + ' - ' + UTCDate.getMonthName();
        });

        var data = {
            labels: labels,
            datasets: [
                {
                    label: 'Days Used per Month',
                    fillColor: 'rgba(255,0,0,0.7)',
                    strokeColor: 'rgba(220,220,220,0.8)',
                    highlightFill: 'rgba(255,0,0,0.9)',
                    highlightStroke: 'rgba(220,220,220,1)',
                    data: dedicatedData.map(function(data) { return Math.round((data.hours_used / 8) * 100) / 100; })
                },
                {
                    label: 'Days Remaining in Year',
                    fillColor: 'rgba(0,0,255,0.7)',
                    strokeColor: 'rgba(151,187,205,0.8)',
                    highlightFill: 'rgba(0,0,255,0.9)',
                    highlightStroke: 'rgba(151,187,205,1)',
                    data: dedicatedData.map(function(data) { return Math.round((data.hours_remaining / 8) * 100) / 100; })
                }
            ]
        };

        var legend_template = '<table>';
        legend_template += '<% for (var i=0; i<datasets.length; i++){ %>';
        legend_template += '<tr><td style="height: 18px; width:30px; background-color:<%=datasets[i].fillColor%>; border:1px solid #CCCCCC;"></td>';
        legend_template += '<td><%if(datasets[i].label){%><%=datasets[i].label%><%}%></td><%}%></tr>';
        legend_template += '</table>';

        var dedicatedChartOptions = {
            legendTemplate: legend_template,
            scaleBeginAtZero : false,
            barShowStroke: true,
            barStrokeWidth: 2,
            barValueSpacing: 5,
            barDatasetSpacing: 1,
            responsive: true
        };

        var ctx = document.getElementById('dedicatedStatsChart').getContext('2d');
        ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;
        var dedicatedBarChart = new Chart(ctx).Bar(data, dedicatedChartOptions);

        document.getElementsByClassName('bar-legend')[0].innerHTML = dedicatedBarChart.generateLegend();
    }
    
    $(function() {
        createDedicatedGraph();
    });

</script>