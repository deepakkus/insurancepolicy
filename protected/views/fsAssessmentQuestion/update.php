<?php
$this->breadcrumbs=array(
	'Clients'=>array('client/admin'),
	$question->client->id=>array('client/update','id'=>$question->client->id),
	'Update',
);
?>

<h1>Update Client Agent App Question <?php echo $question->id; ?></h1>

<?php 

echo $this->renderPartial('_form', array('question'=>$question,));