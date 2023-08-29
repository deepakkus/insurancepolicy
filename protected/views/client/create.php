<?php
$this->breadcrumbs=array(
	'Clients'=>array('admin'),
	'Create',
);

?>

<h1>Create Client</h1>

<?php echo $this->renderPartial('_form', array(
    'client'=>$client,
    'clientStates' => $clientStates
)); ?>