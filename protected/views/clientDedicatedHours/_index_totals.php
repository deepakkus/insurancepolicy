<?php

/* @var $this ClientDedicatedHoursController */

/* @var $resultsTotals array */
/* @var $resultsTotalsWithClients array */
/* @var $dedicatedHoursForClient array */
/* @var $dedicatedServiceClients array */

$assignmentTypes = array(
    EngScheduling::ENGINE_ASSIGNMENT_DEDICATED,
    EngScheduling::ENGINE_ASSIGNMENT_RESPONSE,
    EngScheduling::ENGINE_ASSIGNMENT_PRERISK
);

?>

<div class="row-fluid">

    <?php foreach ($assignmentTypes as $assignment): ?>

    <?php

    $totals = array_filter($resultsTotals, function($value) use ($assignment) { return $value['assignment'] === $assignment; });
    $totalsByClient = array_filter($resultsTotalsWithClients, function($value) use ($assignment) { return $value['assignment'] === $assignment; });

    ?>

    <div class="span4">
        <p class="lead"><u><?php echo $assignment; ?></u></p>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Hours Used: </td>
                        <td><?php echo array_sum(array_map(function($data) { return $data['hours']; }, $totals)); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>By State <small>( These can be added. )</small></strong></td>
                    </tr>
                    <?php

                    // Split out hours by state

                    $stateTotals = array();

                    foreach ($totals as $result)
                    {
                        if (isset($stateTotals[$result['state']]))
                        {
                            $stateTotals[$result['state']] += $result['hours'];
                        }
                        else
                        {
                            $stateTotals[$result['state']] = $result['hours'];
                        }
                    }

                    foreach ($stateTotals as $state => $hours)
                    {
                        echo '<tr>
                            <td>' . $state . '</td>
                            <td>' . strval(round($hours, 2)) . '</td>
                        </tr>';
                    }

                    ?>
                    <tr>
                        <td colspan="2"><strong>By Client <small>( Don't add these, many clients can be on one engine. )</small></strong></td>
                    </tr>
                    <?php

                    // Split out hours by client
                    // These shouldn't be added, because many clients can be on one engine

                    $clientTotals = array();

                    foreach ($totalsByClient as $result)
                    {
                        if (isset($clientTotals[$result['client_id']]))
                        {
                            $clientTotals[$result['client_id']] += $result['hours'];
                        }
                        else
                        {
                            $clientTotals[$result['client_id']] = $result['hours'];
                        }
                    }

                    foreach ($clientTotals as $clientID => $hours)
                    {
                        echo '<tr>
                            <td>' . (isset($dedicatedServiceClients[$clientID]) ? $dedicatedServiceClients[$clientID] : 'Client Dedicated Hours Not Entered') . '</td>
                            <td>' . strval(round($hours, 2)) . '</td>
                        </tr>';
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endforeach; ?>

</div>