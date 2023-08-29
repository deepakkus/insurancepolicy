<?php

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Manage Shift Tickets' => array('engShiftTicket/admin'),
    'Manage Shift Tickets Status Types' => array('engShiftTicketStatusType/admin'),
    'Create'
);

?>

<h1>Create Shift Ticket Status Type</h1>

<?php
echo $this->renderPartial('_form', array(
    'shiftTicketStatusType' => $shiftTicketStatusType
));
?>