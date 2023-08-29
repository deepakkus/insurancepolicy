<?php

/* @var $this ClientDedicatedHoursController */
/* @var $dedicatedForm EngineReportDayForm */
/* @var $dedicatedPools array */
/* @var $dedicatedHoursForClient array */

// All unique states (already ordered from db)

$uniqueStates = array_unique(array_map(function($data) { return $data['state']; }, $dedicatedHoursForClient));

// Getting array of each month datetime in date range choosen

$interval = DateInterval::createFromDateString('1 month');

$startDate = new DateTime($dedicatedForm->startDate);
$endDate = new DateTime($dedicatedForm->endDate);

$periods = new DatePeriod($startDate, $interval, $endDate);

$monthDates = array();
foreach ($periods as $date)
    $monthDates[] = $date;

?>

<div class="row-fluid" style="padding-top:30px;">
    <div style="margin: 0 auto;" >
        <table class="table">
            <thead>
                <tr>
                    <td><strong>Hours Used per Month</strong></td>
                    <td><strong>Hours Remaining for Year</strong></td>
                    <td><strong>Hours Allowed for Year</strong></td>
                    <td><strong>Dedicated Month</strong></td>
                    <td><strong>Dedicated Year</strong></td>
                </tr>
            </thead>
            <tobdy>
                <?php

                foreach ($monthDates as $date)
                {
                    // Getting reference to current dedicated hours pool
                    $currentDedicatedPool = array('dedicated_hours' => 0, 'dedicated_hours_remaining' => 0);
                    foreach ($dedicatedPools as $dedicatedPool)
                    {
                        if ($date >= $dedicatedPool['dedicated_start_date'] && $date < $dedicatedPool['dedicated_end_date'])
                        {
                            $currentDedicatedPool = $dedicatedPool;
                        }
                    }

                    // Dedicated hours used for this month
                    $hoursUsed = array_sum(array_map(function($data) use ($date) { if ($data['date'] == $date) { return $data['hours']; } }, $dedicatedHoursForClient));

                    // Adjust hours remaining
                    if ($currentDedicatedPool['dedicated_hours'] !== 0)
                    {
                        $currentDedicatedPool['dedicated_hours_remaining'] -= $hoursUsed;
                    }

                    // Echo table row
                    echo '<tr>
                        <td>' . $hoursUsed . '</td>
                        <td>' . $currentDedicatedPool['dedicated_hours_remaining'] . '</td>
                        <td>' . $currentDedicatedPool['dedicated_hours'] . '</td>
                        <td>' . $date->format('F') . '</td>
                        <td>' . $date->format('Y') . '</td>
                    </tr>';
                }

                ?>
            </tbody>
        </table>
    </div>
</div>

<p class="lead"><u>Dedicated service hour usage for selected client</u></p>

<?php foreach(array_chunk($monthDates, 3, true) as $monthDate): ?>
<div class="row-fluid">
    <?php foreach ($monthDate as $date): ?>
        <div class="span4">
            <h2><small> Month: <?php echo $date->format('F Y'); ?></small></h2>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <td><b>State</b></td>
                        <td><b>Hours Used</b></td>
                    </tr>
                    <?php

                    // Echoing table row data for each state this month

                    foreach ($uniqueStates as $state)
                    {
                        $hours = 0;
                        foreach ($dedicatedHoursForClient as $dedicatedHours)
                        {
                            if ($dedicatedHours['date'] == $date &&
                                $dedicatedHours['state'] === $state)
                            {
                                $hours = $dedicatedHours['hours'];
                                break;
                            }
                        }

                        echo '<tr>
                            <td>' . $state . '</td>
                            <td>' . strval($hours) . '</td>
                        </tr>';
                    }
                    
                    ?>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>