<?php
$this->breadcrumbs=array(
	'Clients'=>array('client/admin'),
	$question->client->id=>array('client/update','id'=>$question->client->id),
	'Create New Agent App Question',
);

?>

<h1>Create Client Agent App Question</h1>

<?php echo $this->renderPartial('_form', array('question'=>$question,)); ?>