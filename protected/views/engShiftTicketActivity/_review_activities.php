<?php

/* @var $shiftTicket EngShiftTicket */
/* @var $shiftTicketActivities EngShiftTicketActivity[] */

?>

<div class="marginBottom20">
    <span style="font-size: 1.4em;">
        <u>Shift Ticket Activities</u>:
    </span>
    <span style="font-size: 1.2em;">
        Total activity time:  <?php echo strval($shiftTicket->getTotalActivityTime()); ?>
    </span>
</div>

<div class="row-fluid">
    <div class="span12">
        <div>
            <a href="<?php echo $this->createUrl('engShiftTicketActivity/createActivity', array('shift_ticket_id' => $shiftTicket->id)); ?>" class="review-shift-ticket-activity btn btn-success btn-small" style="margin-bottom:10px;">Add Activity</a>
            <span class="alert alert-activities alert-success" style="display: none;">
                <strong>
                    Shift ticket activities have been updated
                </strong>
            </span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Review</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Billable</th>
                    <th>&nbsp;&nbsp;Comments</th>
                </tr>
            </thead>
            <tbody>
            <?php

            //Initialize $activity_comment
            $activity_comment = ''; 
            $billable = '';
            foreach ($shiftTicketActivities as $activity)
            {
                if($activity->eng_shift_ticket_activity_type_id == 4 && !empty($activity->res_ph_visit_id))
                {
                    $pHV = ResPhVisit::model()->findByPk($activity->res_ph_visit_id);
                    $sql = "SELECT r.id,
                            visit_id,
                            action_type_id,
                            qty,
                            name  
                            FROM res_ph_action r 
                            INNER JOIN res_ph_action_type a ON a.id = r.action_type_id
                            WHERE visit_id = ".$pHV->id;
                    $phActions = ResPhAction::model()->findallbysql($sql);
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
                    $activityType = 'Policyholder ('.$pHV->memberLastName.')'.$consume;
                }
                else
                {
                    $activityType = $activity->engShiftTicketActivityType->type;    
                }
                //Check whether value of 'activity comment' column cut off or not
                if(strlen($activity->comment) > 100)
                {
                        $activity_comment = substr($activity->comment, 0, 100) . '...';
                }
                else
                {
                        $activity_comment = $activity->comment;
                }
                $activity_location = '';
                if($activity->tracking_location != '')
                {
                    $activity_location = " | ".$activity->tracking_location;
                }
                if($activity->tracking_location_end != '')
                {
                    $activity_location .= " - ".$activity->tracking_location_end;
                }
                if($activity->billable == 1)
                {
                    $billable = 'Yes';
                }
                else
                {
                        $billable = 'No';
                }
                echo '<tr>
                    <td><a target="_blank" href="' . $this->createUrl('engShiftTicketActivity/reviewActivity', array('id' => $activity->id,'status' => 'view')) . '" class="">View</a></td>
                    <td><a href="' . $this->createUrl('engShiftTicketActivity/reviewActivity', array('id' => $activity->id)) . '" class="review-shift-ticket-activity"><i class="icon-pencil"></i></a></td>
                    <td>' . date('H:i', strtotime($activity->start_time)) . ' - ' . date('H:i', strtotime($activity->end_time)) . '</td>
                    <td>' . $activityType.$activity_location. '</td>
                    <td>' . $billable . '</td>
                    <td>' .$activity_comment . '</td>
                </tr>';
            }

            ?>
            </tbody>
        </table>
    </div>
</div>
