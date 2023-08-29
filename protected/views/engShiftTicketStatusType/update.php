<?php

/* @var $this EngShiftTicketStatusTypeController */
/* @var $shiftTicketStatusType EngShiftTicketStatusType */

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Manage Shift Tickets' => array('engShiftTicket/admin'),
    'Manage Shift Tickets Status Types' => array('engShiftTicketStatusType/admin'),
    'Update'
);

?>

<h1>
    Update Shift Ticket Status Type <?php echo $shiftTicketStatusType->type; ?>
</h1>

<?php

echo $this->renderPartial('_form', array(
    'shiftTicketStatusType' => $shiftTicketStatusType
));

