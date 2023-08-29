<?php

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Manage Shift Tickets' => array('engShiftTicket/admin'),
    'Manage Shift Tickets Activity Types' => array('engShiftTicketActivityType/admin'),
    'Create'
);

?>

<h1>Create Shift Ticket Activity Type</h1>

<?php
echo $this->renderPartial('_form', array(
    'engShiftTicketActivityType' => $engShiftTicketActivityType
));
?>