<?php
$this->breadcrumbs=array(
	'Clients'=>array('admin'),
	$clientAppQuestionSet->client_id=>array('update','id'=>$clientAppQuestionSet->client_id),
	'Create Client App Question Set',
);

?>

<h1>Create Client App Question Set</h1>

<?php echo $this->renderPartial('_form', array('clientAppQuestionSet'=>$clientAppQuestionSet,)); ?>