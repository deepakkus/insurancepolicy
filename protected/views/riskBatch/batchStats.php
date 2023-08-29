<?php

/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */

$this->breadcrumbs=array(
	'Risk Batch'=>array('/riskBatch/admin'),
	'Risk Batch Stats',
);

//Chartsjs library
Assets::registerChartsJSPackage();

?>

<h1>Risk Batch Stats</h1>

<div class="row">

    <div class="span6">

        <canvas id="batchScoreStats" width="400" height="400"></canvas>

        <h2 class="center">Score Distribution</h2>

        <table class="table">

            <?php foreach($results as $row): ?>

            <tr>
                <td><?php echo $row['std_dev_text']; ?></td>
                <td><?php echo $row['num']; ?></td>
            </tr>

            <?php endforeach; ?>

        </table>

    </div>

    <div class ="span6">

        <canvas id="batchStateStats" width="400" height="400"></canvas>

        <h2 class="center">Home Distribution</h2>

        <table class="table">

            <?php foreach($states as $row): ?>

            <tr>
                <td>
                    <?php echo $row['state']; ?>
                </td>
                <td>
                    <?php echo $row['num']; ?>
                </td>
            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>

<script>

    /*-----------------------------Scores---------------------------------*/

    //raw data
    var batchStats = <?= json_encode($results); ?>;
    var data = [];

    batchStats.forEach(function(row) {
        var color = null;
        if(row.std_dev_score == 0){
            color = 'darkgreen';
        }
        else if (row.std_dev_score == 1){
            color = 'green';
        }
        else if (row.std_dev_score == 2){
            color = 'yellow';
        }
        else if (row.std_dev_score == 3){
            color = 'red';
        }
        else if (row.std_dev_score == 4){
            color = 'firebrick';
        }

        data.push({
            value: parseInt(row.num),
            color:  color,
            label: row.std_dev_text
        });
    });

    //Initialize the line chart
    var ctx = document.getElementById("batchScoreStats").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    // For line chart
    var batchScoreChart = new Chart(ctx).Pie(data, {
        animateScale: true,
        responsive: true
    });

    /*-----------------------------States---------------------------------*/

    //raw data
    var batchStateStats = <?= json_encode($states); ?>;
    var stateData = [];
    var g = 25;

    batchStateStats.forEach(function(row) {
        stateData.push({
            value: parseInt(row.num),
            color: "rgba(5, " + String(g) + ",175,1)",
            label: row.state
        });

        g += 15;
    });

    //Initialize the line chart
    var ctx = document.getElementById("batchStateStats").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth;

    //For line chart
    var batchStateChart = new Chart(ctx).Pie(stateData, {
        animateScale: true,
        responsive: true
    });

</script>