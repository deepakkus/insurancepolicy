<?php

/* @var $this EngShiftTicketActivityController */
/* @var $activity EngShiftTicketActivity */

$clientScript = Yii::app()->clientScript;
$clientScript->registerCssFile('/css/engShiftTicket/activity.css');
if($activity->billable == 1)
{
    $billable = 'Yes';
}
else
{
      $billable = 'No';
}
$memberLastName = '';
$consume = '';
if($activity->res_ph_visit_id!='')
{
    $pHV = ResPhVisit::model()->findByPk($activity->res_ph_visit_id);
    $memberLastName = ' ('.$pHV->memberLastName.')';
    $phActions = ResPhAction::model()->getVisitActions($pHV->id);
    $consume = "(";
    foreach($phActions as $action)
    {
        if($action->qty!='')
        {
            $consume .= "# of ".$action->actionTypeName.": ".$action->qty.", ";
        }
    }
    $consume = rtrim($consume,", ");
    $consume .= ")";
}

$totalHours = '';
if($activity->billable == 1)
{
    $totalHours = round((strtotime('1970-01-01 ' . $activity->end_time) - strtotime('1970-01-01 ' . $activity->start_time)) / 3600, 3);
}

$clientNames = isset($shiftTicket->engScheduling->engineClient) ? array_map(function($engineClient) { return $engineClient->client_name; }, $shiftTicket->engScheduling->engineClient) : array();
$assignment = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->assignment : '';
$crew_name = isset($shiftTicket->engScheduling) ? implode(', ', $shiftTicket->engScheduling->crew_names) : '';
$crew_type = isset($shiftTicket->engScheduling) ? ' ('.implode(', ', $shiftTicket->engScheduling->crew_types).')' : '';
$fireName = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->fire_name : '';
$engineName = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->engine_name : '';
$location = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->city.', '.$shiftTicket->engScheduling->state : '';
$resourceOrder = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->resource_order_num : '';
$alliance = isset($shiftTicket->engScheduling->engine->alliancepartner) ? $shiftTicket->engScheduling->engine->alliancepartner->name : '';
?>

<script>
$(document).ready(function () {
 $('#print-ticket').click(function (e) {
        window.print();
    });
    });
</script>
<div class="assignment_info">
    <div class="assignment_title">
    <b>Assignment Info</b>
    </div>
    <div class="assignment_items_1">
        <div class="assignment_item1">
        <b>Assignment: </b>
        <?php echo $assignment;?>
        </div>
        <div class="assignment_item1">
        <b>Clients: </b>
        <?php echo join(',', $clientNames);?>
        </div>
        <div class="assignment_item1">
        <b>Fire: </b>
        <?php echo $fireName;?>
        </div>
        <div class="assignment_item1">
        <b>Location: </b>
        <?php echo $location;?>
        </div>
    </div>
    <div class="assignment_items_2">
        <div class="assignment_item1">
        <b>RO#: </b>
        <?php echo $resourceOrder;?>
        </div>
        <div class="assignment_item1">
        <b>Engine: </b>
        <?php echo $engineName.$alliance;?>
        </div>
        <div class="assignment_item1">
        <b>Crew: </b>
        <?php echo $crew_name.$crew_type;?>
        </div>
    </div>
</div>
<div class="time_sect">
<div class="time_header">
<p><b>Time Entries:</b></p>
</div>
<div class="time_table">
    <table>
    <tr>
    <th width="150px;" align="left"><b>Start Time</b></th>
    <th align="left" width="150px;"><b>End Time</b></th>
    <th align="left" width="250px;"><b>Activity</b></th>
    <th align="left" width="250px;"><b>Comment</b></th>
    <th align="left"><b>Billable</b></th>
    <th align="left"><b>Total Hours</b></th>
    </tr>
    <tr>
    <td><?php echo date('H:i', strtotime($activity->start_time));?></td>
    <td><?php echo date('H:i', strtotime($activity->end_time));?></td>
    <td><?php echo $activity->engShiftTicketActivityType->type.$memberLastName.$consume;?></td>
    <td><?php echo $activity->comment;?></td>
    <td><?php echo $billable;?></td>
    <td><?= round((strtotime('1970-01-01 ' . $activity->end_time) - strtotime('1970-01-01 ' . $activity->start_time)) / 3600, 3) ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td><b>Total Billable:</b></td>
        <td><?php echo $totalHours;?></td>
    </tr>
    </table>
</div>
</div>
<div class="shiftticket_sect">
    <div class="header_info"><b>Day Info</b></div>
    <div class="shift_ticket_content">
        <div class="col_left">
            <p><b>Safty Meeting Comments</b></p>
            <div class="col_comment"><?php echo $shiftTicket->safety_meeting_comments;?></div>
        </div>
        <div class="col_right">
            <div class="top_right">
                <div class="right_sect_1">
                    <p><b>Start Mileage</b></p> 
                        <div class="content_1">
                        <?php echo $shiftTicket->start_miles;?>
                        </div>
                </div>
                <div class="right_sect_2">
                    <p><b>Start Location</b></p> 
                        <div class="content_1">
                        <?php echo $shiftTicket->start_location;?>
                        </div>
                </div>
            </div>
            <div class="top_right_2">
                <div class="right_sect_1">
                    <p><b>End Mileage</b></p> 
                        <div class="content_1">
                        <?php echo $shiftTicket->end_miles;?>
                        </div>
                </div>
                <div class="right_sect_2">
                    <p><b>End Location</b></p> 
                        <div class="content_1">
                        <?php echo $shiftTicket->end_location;?>
                        </div>
                </div>
            </div>
        </div>
        <div class="col_left_2">
            <p><b>Equipment Check Comments</b></p>
            <div class="col_comment"><?php echo $shiftTicket->equipment_check;?></div>
        </div>
    </div>
</div>
<div class="print_buttons">
<button id="print-ticket" type="button" class="btn btn-lg btn-primary" >
Print
</button>
</div>
