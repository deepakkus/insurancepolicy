<?php

/* @var $shiftTicket EngShiftTicket */

$history = EngShiftTicket::getShiftTicketHistory($shiftTicket->id);

// Getting list of all users

$userIDs = array_map(function($entry) { return $entry->user_id; }, $history);
$userIDs = array_filter(array_unique($userIDs));

$users = $userIDs ? CHtml::listData(User::model()->findAll(array(
    'select' => array('id', 'name'),
    'condition' => 'id IN (' . implode(',', $userIDs) . ')'
)), 'id', 'name') : array();

// Getting list of all statuses

$statusList = CHtml::listData(EngShiftTicketStatusType::model()->findAll(array(
    'select' => array('id', 'type'),
    'order' => '[order] ASC'
)), 'id', 'type');

?>

<h4>Shift Ticket History</h4>
<div style="max-height: 250px; overflow-y: auto;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Completed Statuses</th>
                <th>Date</th>
            </tr>
        </thead>    
        <tbody>
            <?php

            foreach ($history as $historyDate => $entry)
            {
                $completedStatuses = array();

                foreach ($entry->statuses as $status)
                {
                    $completed = filter_var($status->completed, FILTER_VALIDATE_BOOLEAN);

                    if ($completed === true && isset($statusList[$status->status_type_id]))
                    {
                        $completedStatuses[] = $statusList[$status->status_type_id];
                    }
                }

                echo '<tr>
                    <td>' . (isset($users[$entry->user_id]) ? $users[$entry->user_id] : '') . '</td>
                    <td>' . implode('<br />', $completedStatuses) . '</td>
                    <td>' . date('Y-m-d H:i', $historyDate)  . '</td>
                </tr>';
            }

            ?>
        </tbody>
    </table>
</div>