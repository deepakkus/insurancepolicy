<?php
    $this->breadcrumbs=array(
	'Contact Us' => array('admin'),
	'Create',
    );
?>

<h1>Create New 'Contact Us'</h1>

<?php 
    echo $this->renderPartial('_form', array(
        'model' => $model,
        'existingMemberID' => $existingMemberID,
    )); 
?>
