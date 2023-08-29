<?php

$this->breadcrumbs = array(
	'Client Analytics' => $this->createUrl('/client/index'),
    'Client Stats'
);

//Chartsjs library
Assets::registerChartsJSPackage();

?>

<h1>Stats for <?php echo $client->name; ?></h1>

<div class="row-fluid">
    <div class="span4">
        <h3 class='center'>Basic Usage</h3>
        <table class="table">
            <tr>
                <td><b>Total Users:</b></td>
                <td><?php echo (isset($users['total'])) ? $users['total'] : 0; ?></td>
            </tr>
            <tr>
                <td><b>Admin Users:</b></td>
                <td><?php echo (isset($users['user_admin'])) ? $users['user_admin'] : 0; ?></td>
            </tr>
            <tr>
                <td><b>Manager Users:</b></td>
                <td><?php echo (isset($users['user_manager'])) ? $users['user_manager'] : 0; ?></td>
            </tr>
            <tr>
                <td><b>Risk Users:</b></td>
                <td><?php echo (isset($users['user_risk'])) ? $users['user_risk'] : 0; ?></td>
            </tr>
            <tr>
                <td><b>Disabled Users:</b></td>
                <td><?php echo (isset($users['user_disable'])) ? $users['user_disable'] : 0; ?></td>
            </tr>
        </table>
    </div>

    <div class="span4">
        <h3 class='center'>Response</h3>

        <table class="table">
            <tr>
                <td><b>Program Name:</b></td>
                <td><?php echo $client->response_program_name; ?></td>
            </tr>
            <tr>
                <td><b>States:</b></td>
                <td><?php echo implode(array_map(function($data){return $data->state->abbr; }, $client->clientStates), ", "); ?></td>
            </tr>
        </table>

        <?php if($notices): ?>
        <p><b><u>PTD Notices By Year:</u></b><p>
        <table class="table">
            <?php foreach($notices as $year): ?>
                <tr>
                    <td><b><?php echo $year['year']; ?></b></td>
                    <td><?php echo $year['total']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <canvas id="monthChart" height="150"></canvas>

        <?php else: ?>

        <p>No notices</p>

        <?php endif; ?>

    </div>

    <div class="span4">
        <h3 class='center'>WDS Risk</h3>
        <?php if($risk): ?>
        <table class="table">
            <?php foreach($risk as $type): ?>
            <tr>
                <td><b><?php echo $type['type']; ?>:</b></td>
                <td><?php echo $type['total']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><i>(PTD Transactions)</i><p>
        <?php else: ?>

        <p>No risk scores</p>

        <?php endif; ?>
    </div>

</div>

<script>
    //Data and labels
    var notices = <?= json_encode(array_map(function($data){ return $data['total']; }, $notices)); ?>;
    var noticeLabels = <?= json_encode(array_map(function($data){ return $data['year']; }, $notices)); ?>;

     var data = {
        labels:  noticeLabels,
        datasets: [
            {
                label: "Notices",
                fillColor: "rgba(179,227,250,.8)",
                strokeColor: "#666666",
                pointColor: "rgba(179,227,250,.8)",
                pointStrokeColor: "#ccccccc",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: notices
            }
        ]
    };

    //Formatting for chart
    var chartOptions = {
        responsive: true
    };

    //Initialize the line chart
    var ctx = document.getElementById("monthChart").getContext("2d");
    ctx.canvas.width  = ctx.canvas.parentNode.offsetWidth - 20;
    var monthBarChart = new Chart(ctx).Bar(data, chartOptions);
</script>