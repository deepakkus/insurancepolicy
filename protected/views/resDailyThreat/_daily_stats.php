<?php

/* @var $models ResDaily[] */

?>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <td><strong>Client</strong></td>
                <td><strong>Fires Monitored</strong></td>
                <td><strong>Fires Triggering</strong></td>
                <td><strong>Fires Responding</strong></td>
                <td><strong>Total Exposure</strong></td>
                <td><strong>Policyholders Triggered</strong></td>
                <td><strong>Response Enrolled (YTD)</strong></td>
            </tr>
        </thead>
        <tbody>
            <?php

            $formatter = Yii::app()->format;

            $formatter->numberFormat = array('decimals' => 0, 'decimalSeparator' => '.', 'thousandSeparator' => ',');

            foreach ($models as $model)
            {
                echo '<tr>';
                echo '<td>' . $model->client->name . '</td>';
                echo '<td>' . $model->monitored . '</td>';
                echo '<td>' . $model->fires_triggered . '</td>';
                echo '<td>' . $model->fires_responding . '</td>';
                echo '<td>' . $formatter->formatNumber($model->exposure) . '</td>';
                echo '<td>' . $formatter->formatNumber($model->policy_triggered) . '</td>';
                echo '<td>' . $formatter->formatNumber($model->response_enrolled) . '</td>';
                echo '</tr>';
            }

            ?>
        </tbody>
    </table>
</div>
