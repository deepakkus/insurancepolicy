<?php

/* @var $this EngShiftTicketController */
/* @var $engines array */
/* @var $date string */

$backDate = date('Y-m-d', strtotime($date . ' -1 day'));
$forwardDate = date('Y-m-d', strtotime($date . ' +1 day'));

$shiftTicketStatuses = CHtml::listData(EngShiftTicketStatusType::getAllActiveStatuses(), 'id', 'type');

// Getting all unique user ids in shift ticket / histories
$userIDs = array();
foreach ($shiftTickets as $shiftTicket)
{
    $completedByUserIDs = array_map(function($data) { return isset($data['completed_by_user_id']) ? $data['completed_by_user_id'] : null; }, $shiftTicket['statuses']);
    $userIDs = array_merge($userIDs, $completedByUserIDs);
}

$userIDs = array_filter(array_unique($userIDs));

$users = array();

if ($userIDs)
{
    // Getting array of users.  Index of user id, value of user name
    $users = $userIDs ? CHtml::listData(User::model()->findAll(array(
        'select' => array('id', 'name'),
        'condition' => 'id IN (' . implode(',', $userIDs) . ')'
    )), 'id', 'name') : array();
}

?>

<div class="table-responsive" id="shift-tickets">
    <table class="shift-ticket-table table">
        <thead>
            <tr>
                <th>
                    <a style="margin-bottom: 20px;" class="arrow-left pull-left" href="<?php echo $this->createUrl('engShiftTicket/shiftTicketTable'); ?>" id="back" data-date="<?php echo $backDate; ?>"></a>

					<div class="shift-ticket-table-loading clearfix" style="height: 30px; width: 30px;"></div>
                </th>
                <th colspan="5">
                    <div>
                        <h2 id="current-date" class="text-center" data-date="<?php echo $date; ?>"><?php echo date('l, F jS', strtotime($date)); ?></h2>
                    </div>
                </th>
                <th>
                    <a style="margin-bottom: 20px;" class="arrow-right pull-right" href="<?php echo $this->createUrl('engShiftTicket/shiftTicketTable'); ?>" id="forward" data-date="<?php echo $forwardDate; ?>"></a>
                </th>
            </tr>
            <tr>
                <th>ENGINE</th>
                <th>REVIEW</th>
                <?php

                foreach ($shiftTicketStatuses as $status)
                {
                    echo '<th class="text-center">' . strtoupper($status) . '</th>';
                }

                ?>
            </tr>
        </thead>
        <tbody>

            <?php

            foreach ($shiftTickets as $shiftTicket)
            {
                echo '<tr>';

                // engine details column
                echo "<td>
                   <strong>{$shiftTicket['engine_name']}</strong><br />
                   <strong>{$shiftTicket['fire_name']}</strong><br />
                   {$shiftTicket['clients']}<br />
                   {$shiftTicket['employees']}
                </td>";

                $submittedStatus = array_filter($shiftTicket['statuses'], function($v) { return $v['type'] === 'Submitted'; });

                // If the shift ticket has not been submitted, let the user know and move on to the next engine
                if (count($shiftTicket['statuses']) === 0)
                {
                    echo '<td class="alert-danger text-center">Not Created Yet</td>';
                    for ($i = 0; $i < count($shiftTicketStatuses); $i++)
                    {
                        echo '<td class="alert-danger text-center"></td>';
                    }
                    echo '<tr />';
                    continue;
                }
                // Shift ticket has been created, but it not submitted
                else if (isset($submittedStatus[0]) && $submittedStatus[0]['completed'] !== '1')
                {
                    echo '<td class="alert-danger text-center">Not Submitted</td>';
                    for ($i = 0; $i < count($shiftTicketStatuses); $i++)
                    {
                        echo '<td class="alert-danger text-center"></td>';
                    }
                    echo '<tr />';
                    continue;
                }
                else
                {
                    echo '<td class="alert-success text-center">
                        <a href="' . $this->createUrl('engShiftTicket/review', array('id' => $shiftTicket['eng_shift_ticket_id'])) . '">
                            Review
                        </a>
                        &nbsp;
                        <a href="' . $this->createUrl('engShiftTicket/viewShiftTicketPDF', array('ids' => json_encode(array($shiftTicket['eng_shift_ticket_id'])))) . '" target="_blank">
                            <i class="icon-eye-open"></i>
                        </a>
                    </td>';
                }

                foreach ($shiftTicket['statuses'] as $status)
                {
                    $completed = filter_var($status['completed'], FILTER_VALIDATE_BOOLEAN);

                    if ($completed === true && isset($users[$status['completed_by_user_id']]))
                    {
                        echo '<td class="alert-success text-center">
                            &#10004;<br />' . $users[$status['completed_by_user_id']] . '
                        </td>';
                    }
                    else
                    {
                        echo '<td class="alert-danger text-center">&#x2716;</td>';
                    }
                }

                echo '</tr>';
            }

            ?>

        </tbody>
    </table>
</div>