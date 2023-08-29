<?php

    $this->breadcrumbs=array(
        'Pre Risk Analytics',
    );

    //Chartsjs library
    Assets::registerChartsJSPackage();
    
?>

<?php $this->renderPartial('//site/indexAnalyticsNav'); ?>

<h1 class="center">Pre Risk Analytics</h1>

<div class ="row-fluid">
    <div class ="span4">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Program Take-Up</h2>
        <p class ="center"><i>(Program To Date)</i></p>
        <canvas id="programTakeup" width="400" height="400"></canvas>

        <table class ="table">
            <tr>
                <td><strong>Completed Assessments:</strong></td>
                <td><?php echo $completedTotal; ?></td>
            </tr>
            <tr>
                <td><strong>Offered but Not-Completed:</strong></td>
                <td><?php echo $total -  $completedTotal; ?></td>
            </tr>
            <tr>
                <td><strong>Program Take-Up Rate:</strong></td>
                <td><?php echo Yii::app()->numberFormatter->formatPercentage($completedTotal/$total); ?></td>
            </tr>
        </table>

    </div>
        <div class ="span8">
            <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Completed Reports Per Month</h2>
            <p class ="center"><i>(<?php echo date('Y-m', strtotime('-2 years', strtotime(date('Y-m')))) . " to " . date('Y-m'); ?>)</i></p>
            <canvas id="assessmentsByMonth" height="500"></canvas>
    </div>
</div>

<h1 class ="center">Call Campaign Stats for <?php echo date('F Y'); ?></h1>

<div class ="row-fluid">
    <div class ="span4">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Status Counts</h2>
        <table class ="table">
            <tr>
                <td><strong>Status</strong></td>
                <td><strong>Count</strong></td>
            </tr>

        <?php foreach($callCampaign as $key => $value): ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $value; ?></td>
            </tr>

        <?php endforeach; ?>

        </table>
    </div>
    <div class ="span4">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Status Totals</h2>
        <canvas id="callCampaignBar" width="400" height="400"></canvas>
    </div>
    <div class ="span4">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Status Percentage</h2>
        <canvas id="callCampaignPie" width="400" height="400"></canvas>
    </div>

</div>

<script>

    // -------------------------- Program Take-Up Pie Chart ---------------------

    var data = [
        {
            value: <?php echo $completedTotal; ?>,
            color: "green",
            highlight: "darkgreen",
            label: "Completed"
        },
        {
            value: <?php echo $total -  $completedTotal; ?>,
            color: "orange",
            highlight: "darkorange",
            label: "Offered but Not-Completed"
        }
    ];

    //Initialize the bar chart
    var ctx = document.getElementById("programTakeup").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For a pie chart
    var takeUpPieChart = new Chart(ctx).Pie(data, {
        animateScale: true,
        responsive: true
    });

    // -------------------------- Monthly Line Chart ---------------------

    var completedAssessments = <?php echo json_encode($assessmentsPerMonth); ?>;
    var labels = [];
    var assessments = [];

    completedAssessments.forEach(function(row){
        labels.push(row.month);
        assessments.push(row.completed_assessments);
    });

    var data = {
        labels: labels,
        datasets: [
            {
                label: "My First dataset",
                fillColor: "steelblue",
                strokeColor: "gray",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: assessments
            }
        ]
    };

    //Initialize the line chart
    var ctx = document.getElementById("assessmentsByMonth").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For line chart
    var assessmentsLineChart = new Chart(ctx).Line(data, {
        animateScale: true,
        bezierCurve : false,
        responsive: true
    });

    // ------------------------- Campaign Bar Chart -------------------------
    var callCampaign = <?php echo json_encode($callCampaign); ?>;

    //Used for the chart
    var campaignData = [];
    var campaignLabels = [];

    //load the chart
    for(key in callCampaign){
        campaignData.push(callCampaign[key]);
        campaignLabels.push(key);
    }

    var data = {
        labels: campaignLabels,
        datasets: [
            {
                label: "Status",
                fillColor: "steelblue",
                strokeColor: "gray",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: campaignData
            }
        ]
    };

    //Initialize the bar chart
    var ctx = document.getElementById("callCampaignBar").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For a pie chart
    var campaignBarChart = new Chart(ctx).Bar(data, {
        animateScale: true,
        responsive: true
    });

    // ------------------------- Campaign Pie Chart -------------------------
    var g = 25;   

    //Used for the chart
    var campaignData = [];

    //load the chart
    for(key in callCampaign){
        campaignData.push(
            {
                value : callCampaign[key],
                label : key,
                color: "rgba(5, " + String(g) + ",175,1)"
            } 
        );
        g += 15;
    }

    //Initialize the pie chart
    var ctx = document.getElementById("callCampaignPie").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For a pie chart
    var callCampaignPieChart = new Chart(ctx).Pie(campaignData, {
        animateScale: true,
        responsive: true
    });

</script>