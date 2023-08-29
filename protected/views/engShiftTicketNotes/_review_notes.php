<?php

/* @var $shiftTicket EngShiftTicket */
/* @var $shiftTicketNotes EngShiftTicketNotes[] */

$script = '

    $(document).on("click", ".review-shift-ticket-note-delete", function() {

        if (!confirm("Are you sure you want to delete this?")) {
            return false;
        }

        $.get(this.href, function(data) {
            if (data.success === true) {
                $.get("' . $this->createUrl('engShiftTicketNotes/notes', array('shift_ticket_id' => $shiftTicket->id)) . '", function(data) {
                    $("#review-notes-container").html(data);
                    $(".alert-notes").fadeIn().delay(2000).fadeOut();
                });
            } else {
                alert("Something went wrong!");
                console.log("Shift ticket note deletion error: " + data.error);
            }
        }, "json");

        return false;
    });

';

// Note this script will only register when rendered the first time.  Every ajax load after won't load this ... that's by design 

Yii::app()->clientScript->registerScript('shift-ticket-notes-script', $script);

?> 

<div class="marginBottom20">
    <span style="font-size: 1.4em;">
        <u>Shift Ticket Notes</u>:
    </span>
</div>

<div class="row-fluid">
    <div class="span12">
        <div>
            <a href="<?php echo $this->createUrl('engShiftTicketNotes/create', array('shift_ticket_id' => $shiftTicket->id)); ?>" class="review-shift-ticket-note btn btn-success btn-small" style="margin-bottom:10px;">Add Note</a>
            <span class="alert alert-notes alert-success" style="display: none;">
                <strong>
                    Shift ticket notes have been updated
                </strong>
            </span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Actions</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>&nbsp;&nbsp;Note</th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($shiftTicketNotes as $note)
                {
                    echo '<tr>
                        <td>
                            <a href="' . $this->createUrl('engShiftTicketNotes/update', array('id' => $note->id)) . '" class="review-shift-ticket-note"><i class="icon-pencil"></i></a>
                            <a href="' . $this->createUrl('engShiftTicketNotes/delete', array('id' => $note->id)) . '" class="review-shift-ticket-note-delete"><i class="icon-trash"></i></a>
                        </td>
                        <td>' . (isset($note->user->name) ? $note->user->name : '') . '</td>
                        <td>' . date('Y-m-d H:i', strtotime($note->date_updated)) . '</td>
                        <td>' . $note->notes . '</td>
                    </tr>';
                }

                ?>
            </tbody>
        </table>
    </div>
</div>
