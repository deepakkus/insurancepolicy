<?php
/* @var $this ResNoticeController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Response Analytics',
);

//Chartsjs library
Assets::registerChartsJSPackage();

$this->renderPartial('//site/indexAnalyticsNav');

echo CHtml::beginForm($this->createUrl($this->route));

?>

<p>Start Date</p>

<?php

$this->widget('zii.widgets.jui.CJuiDatePicker', array(
	'name' => 'startdate',
	'options' => array(
		'showAnim' => 'fold',
		'dateFormat' => 'yy-mm-dd',
        'onSelect' => new CJavaScriptExpression('function(selectedDate) {
            var minDate = new Date(selectedDate);
            minDate.setDate(minDate.getDate() + 2);
            $("#enddate").datepicker("option", "minDate", minDate);
        }')
	),
	'value' => $startDate,
        'htmlOptions' => array(
        'readonly' => 'readonly',
        'style' => 'cursor: pointer;'
    )
));

?>

<p>End Date</p>

<?php

$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name' => 'enddate',
	'options' => array(
		'showAnim' => 'fold',
		'dateFormat' => 'yy-mm-dd',
        'onSelect' => new CJavaScriptExpression('function(selectedDate) {
            var maxDate = new Date(selectedDate);
            $("#startdate").datepicker("option", "maxDate", maxDate);
        }')
	),
	'value' => $endDate,
    'htmlOptions' => array(
        'readonly' => 'readonly',
        'style' => 'cursor:pointer;',
    )
));

?>

<div class="row-fluid">
    <div class="span12">
    <?php echo CHtml::submitButton('Search', array('class' => 'submit')); ?>
    </div>
</div>

<?php

echo CHtml::endForm();

?>

<h1 class ="center">Response Summary</h1>
<p class ="center"><i>(<?php echo $startDate . " to " . $endDate; ?>)</i></p>

<div class ="row-fluid">
    <div class ="span8">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Program Fires By Month</h2>
        <canvas id="monthChart" height="450"></canvas>
        <div class="line-legend"></div>
    </div>
    <div class ="span4">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Month Breakdown</h2>
        <table class = "table table-striped">
            <tr>
                <td><strong>Month</strong></td>
                <td><strong>Dispatched Fires</strong></td>
                <td><strong>Not-Dispatched Fires</strong></td>
                <td><strong>All Program Fires</strong></td>
            </tr>

            <?php foreach($monthlyTotals as $month): ?>

            <tr>
                <td><?php echo $month['date']; ?></td>
                <td><?php echo $month['dispatched_fires']; ?></td>
                <td><?php echo $month['fires'] - $month['dispatched_fires']; ?></td>
                <td><?php echo $month['fires']; ?></td>
            </tr>

            <?php endforeach; ?>
        </table>
    </div>

</div>

<div class ="row-fluid">
    <div class ="span2">
        <p class ="center">
            <span style="font-family: 'Arial Black', Gadget, sans-serif;color:#003D99;font-size:34px;"><?php echo ResNotice::countProgramFires($startDate, $endDate, 1); ?></span> Dispatched Fires
        </p>
    </div>
    <div class ="span2">
        <p class ="center">
             <span style="font-family: 'Arial Black', Gadget, sans-serif;color:#0052CC;font-size:34px;"><?php echo (ResNotice::countProgramFires($startDate, $endDate, null) - ResNotice::countProgramFires($startDate, $endDate, 1)); ?></span> Not-Dispatched Fires
        </p>
    </div>
    <div class ="span2">
        <p class ="center">
            <span style="font-family: 'Arial Black', Gadget, sans-serif;color:#0066FF;font-size:34px;"><?php echo ResNotice::countProgramFires($startDate, $endDate, null); ?></span> Total Program Fires
        </p>
    </div>
    <div class ="span2">
        <p class ="center">
            <span style="font-family: 'Arial Black', Gadget, sans-serif;color:#3385FF;font-size:34px;"><?php echo ResFireName::countFiresByDate($startDate, $endDate); ?></span> Monitored Fires
        </p>
    </div>
</div>

<h1 class ="center" style="padding-top:50px;">Response Current</h1>

<div class ="row-fluid">

    <div class="span4 table-responsive">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Current Dispatched Fires</h2>
        <table class ="table">
            <tr>
                <td><strong>Client</strong></td>
                <td><strong>Fire</strong></td>
                <td><strong>City</strong></td>
                <td><strong>State</strong></td>
            </tr>

        <?php foreach($clients as $client): ?>
                            
            <?php $fires = ResNotice::getAllCurrentFires($client->id); ?>
            <?php foreach($fires['dispatched'] as $fire): ?>

                <tr>
                    <td><?php echo $client->name; ?></td>
                    <td><?php echo $fire->fire->Name; ?></td>
                    <td><?php echo $fire->fire->City; ?></td>
                    <td><?php echo $fire->fire->State; ?></td>
                </tr>

            <?php endforeach; ?>
        <?php endforeach; ?>
        </table>
    </div>

    <div class ="span4 table-responsive">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Current Not-Dispatched Fires</h2>
        <table class ="table">
            <tr>
                <td><strong>Client</strong></td>
                <td><strong>Fire</strong></td>
                <td><strong>City</strong></td>
                <td><strong>State</strong></td>
            </tr>

        <?php foreach($clients as $client): ?>
                            
            <?php $fires = ResNotice::getAllCurrentFires($client->id); ?>
            <?php foreach($fires['not_dispatched'] as $fire): ?>

                <tr>
                    <td><?php echo $client->name; ?></td>
                    <td><?php echo $fire->fire->Name; ?></td>
                    <td><?php echo $fire->fire->City; ?></td>
                    <td><?php echo $fire->fire->State; ?></td>
                </tr>

            <?php endforeach; ?>
        <?php endforeach; ?>

        </table>
    </div>

    <div class ="span4 table-responsive">
            <h2 class ="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Fires By State (past year)</h3>
            <table class ="table">
                <tr>
                    <td><strong>State</strong></td>
                    <td><strong>Dispatched Fires</strong></td>
                    <td><strong>Not-Dispatched Fires</strong></td>
                    <td><strong>Total Fires</strong></td>
                    <td><strong>Percentage</strong></td>
                </tr>

                <?php foreach($stateTally as $key=>$value): ?>
                <tr>
                    <td><?= $key; ?></td>
                    <td><?= $value['dispatched_fires']; ?></td>
                    <td><?= $value['fires'] - $value['dispatched_fires']; ?></td>
                    <td><?= $value['fires']; ?></td>
                    <td><?= round($value['fires'] / $totalStates['fires'] * 100); ?>&#37;</td>
                </tr>
                <?php endforeach; ?>

            </table>
            </div>
</div>
<div class ="row-fluid">
    <h2 class ="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Fires By State (past year)</h3>
    <table class ="table">
        <tr>
            <td><strong>State</strong></td>
            <td><strong>Dispatched Fires</strong></td>
            <td><strong>Not-Dispatched Fires</strong></td>
            <td><strong>Total Fires</strong></td>
            <td><strong>Percentage</strong></td>
        </tr>

        <?php foreach($stateTally as $key=>$value): ?>
        <tr>
            <td><?= $key; ?></td>
            <td><?= $value['dispatched_fires']; ?></td>
            <td><?= $value['fires'] - $value['dispatched_fires']; ?></td>
            <td><?= $value['fires']; ?></td>
            <td><?= round($value['fires'] / $totalStates['fires'] * 100); ?>&#37;</td>
        </tr>
        <?php endforeach; ?>

    </table>
</div>

<h2 class ="center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222; margin-top:50px;">Client Statistics (YTD)</h2>

<div class ="row-fluid">
    <div class ="span12 table-responsive">
        <table class="table table-striped">
            <tr>
                <td><strong>Client</strong></td>
                <td><strong>Notices (Not Dispatched)</strong></td>
                <td><strong>Notices (Dispatched)</strong></td>
                <td><strong>Notices(Total)</strong></td>
                <td><strong>Fires (Not Dispatched)</strong></td>
                <td><strong>Fires (Dispatched)</strong></td>
                <td><strong>Fires (Total)</strong></td>
                <td><strong>Triggered</strong></td>
                <td><strong>Threatened</strong></td>
                <td></td>
            </tr>

            <?php foreach($clients as $client): ?>
                <tr>
                    <td><?php echo $client->name; ?></td>
                    <td><?php echo ResNotice::countNotices($ytdStart, $ytdEnd, $client->id, 2) + ResNotice::countNotices($ytdStart, $ytdEnd, $client->id, 3); ?></td>
                    <td><?php echo ResNotice::countNotices($ytdStart, $ytdEnd, $client->id, 1); ?></td>
                    <td><?php echo ResNotice::countNotices($ytdStart, $ytdEnd, $client->id); ?></td>
                    <td><?php echo ResNotice::countProgramFires($ytdStart, $ytdEnd, null, $client->id) - ResNotice::countProgramFires($ytdStart, $ytdEnd, 1, $client->id); ?></td>
                    <td><?php echo ResNotice::countProgramFires($ytdStart, $ytdEnd, 1, $client->id); ?></td>
                    <td><?php echo ResNotice::countProgramFires($ytdStart, $ytdEnd, null, $client->id); ?></td>
                    <td><?php echo ResTriggered::countTriggeredPolicyholders($client->id, $ytdStart, $ytdEnd); ?></td>
                    <td><?php echo ResTriggered::countTriggeredPolicyholders($client->id, $ytdStart, $ytdEnd, 1); ?></td>
                    <td><?php echo CHtml::link("View $client->name", $this->createUrl('/resNotice/viewNotice', array('clientid'=>$client->id)), array('target'=>'_blank')); ?></td>
                </tr>
            <?php endforeach; ?>

        </table>
    </div>
</div>

<script>
    //raw data
    var firesByMonth = <?= json_encode($monthlyTotals); ?>;

    //chartsjs friendly formats (break into individual arrays)
    var monthTotalData = [];
    var monthDispatchedData = [];
    var monthNotDispatchedData = [];
    var monthLabels = [];

    //Fill Arrays
    firesByMonth.forEach(function(row) { 
        monthLabels.push(row.date);
        monthTotalData.push(row.fires);
        monthDispatchedData.push(row.dispatched_fires);
        monthNotDispatchedData.push(row.fires - row.dispatched_fires);
    });

    var data = {
        labels:  monthLabels,
        datasets: [
            {
                label: "All Program Fires",
                fillColor: "rgba(179,227,250,.8)",
                strokeColor: "#666666",
                pointColor: "rgba(179,227,250,.8)",
                pointStrokeColor: "#ccccccc",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: monthTotalData
            },
            {
                label: "Not-Dispatched Program Fires",
                fillColor: "rgba(1,131,191,.8)",
                strokeColor: "#444444",
                pointColor: "rgba(1,131,191,.8)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: monthNotDispatchedData
            },
            {
                label: "Dispatched Program Fires",
                fillColor: "rgba(1,90,132,.8)",
                strokeColor: "#222222",
                pointColor: "rgba(1,90,132,.8)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: monthDispatchedData
            }
        ]
    };

    var legend_template = '<table>';
    legend_template += '<% for (var i=0; i<datasets.length; i++){ %>';
    legend_template += '<tr><td style="height: 18px; width:30px; background-color:<%=datasets[i].fillColor%>; border:1px solid #CCCCCC;"></td>';
    legend_template += '<td><%if(datasets[i].label){%><%=datasets[i].label%><%}%></td><%}%></tr>';
    legend_template += '</table>';

    //Formatting for chart
    var lineChartOptions = {
        legendTemplate: legend_template,
        bezierCurve : true,
        responsive: true
    };

    //Initialize the line chart
    var ctx = document.getElementById("monthChart").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth - 20;
    var monthLineChart = new Chart(ctx).Line(data, lineChartOptions);

    //legend
    document.getElementsByClassName('line-legend')[0].innerHTML = monthLineChart.generateLegend();

</script>
