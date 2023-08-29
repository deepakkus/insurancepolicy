<div class="row-fluid">
    <div class="span12">
        <table class="table table-condensed table-striped table-hover" style="table-layout:fixed">
        <caption style="font-weight: bold; font-size: 16px;"><?php echo date('Y-m-d', strtotime($engineReportDayForm->date)); ?></caption>
            <thead>
                <tr style="border-bottom: 1px solid black;">
                    <th>Clients</th>
                    <th>Provider</th>
                    <th>Partner</th>
                    <th>Engine</th>
                    <th>Assignment</th>
                    <th>Fire</th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($results as $result)
                {
                        echo '<tr>';
                        echo '<td>' . implode('<br />', array_map(function($client) { return $client->client_name; }, $result->engineClient))  . '</td>';
                        echo '<td>' . $result->getEngineSource($result->engine_source) . '</td>';
                        echo '<td>' . $result->engine->alliance_partner . '</td>';
                        echo '<td>' . $result->engine_name . '</td>';
                        echo '<td>' . $result->assignment . '</td>';
                        echo '<td>' . $result->fire_name . '</td>';
                        echo '</tr>';
                }

                ?>
            </tbody>
        </table>
    </div>
</div>