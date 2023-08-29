<?php

/* @var $this EngShiftTicketActivityTypeController */
/* @var $engShiftTicketActivityType EngShiftTicketActivityType */

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Manage Shift Tickets' => array('engShiftTicket/admin'),
    'Manage Shift Tickets Activity Types' => array('engShiftTicketActivityType/admin'),
    'Update'
);

?>

<h1>
    Update Shift Ticket Activity Type <?php echo $engShiftTicketActivityType->type; ?>
</h1>

<?php

echo $this->renderPartial('_form', array(
    'engShiftTicketActivityType' => $engShiftTicketActivityType
));

