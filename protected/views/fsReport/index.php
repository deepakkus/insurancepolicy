<?php

    $this->breadcrumbs=array(
        'FireShield Analytics',
    );

    //Chartsjs library
    Assets::registerChartsJSPackage();
    
?>

<?php $this->renderPartial('//site/indexAnalyticsNav'); ?>

<h1 class="center">FireShield Analytics</h1>

<div class ="row-fluid">
    <div class ="span4">
        <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Reports Per State</h2>
        <canvas id="reportsByState" width="400" height="400"></canvas>

        <table class ="table">
            <tr>
                <td><strong>State:</strong></td>
                <td>Reports</td>
            </tr>

            <?php foreach($reportsPerState as $key => $value): ?>

            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $value; ?></td>
            </tr>

            <?php endforeach; ?>

        </table>

    </div>
        <div class ="span8">
            <h2 class = "center" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Completed Reports Per Month</h2>
            <p class ="center"><i>(<?php echo date('Y-m', strtotime('-2 years', strtotime(date('Y-m')))) . " to " . date('Y-m'); ?>)</i></p>
            <canvas id="reportsByMonth" height="500"></canvas>

    </div>
</div>

<script>

    // -------------- Reports Per State Bar Chart -------------------------
    var reportsPerState = <?php echo json_encode($reportsPerState); ?>;
    var data = [];
    var g = 25;

    for(state in reportsPerState){
        data.push(
            {
                value: reportsPerState[state],
                color: "rgba(5, " + String(g) + ",175,1)",
                highlight: 'orange',
                label: state
            }
        );
        g += 15;
    }


    //Initialize the bar chart
    var ctx = document.getElementById("reportsByState").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For a pie chart
    var reportsPieChart = new Chart(ctx).Pie(data, {
        animateScale: true,
        responsive: true
    });

    // -------------------------- Monthly Line Chart ---------------------
    var completedReports = <?php echo json_encode($reportsPerMonth); ?>;
    var labels = [];
    var reportsData = [];

    completedReports.forEach(function(row){
        labels.push(row.month);
        reportsData.push(row.completed_reports);
    });

    var data = {
        labels: labels,
        datasets: [
            {
                label: "Completed Reports",
                //fillColor: "steelblue",
                fillColor: "rgba(112,168,25,1)",
                strokeColor: "gray",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: reportsData
            }
        ]
    };

    //Initialize the line chart
    var ctx = document.getElementById("reportsByMonth").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For line chart
    var reportsLineChart = new Chart(ctx).Line(data, {
        animateScale: true,
        bezierCurve : false,
        responsive: true
    });

</script>