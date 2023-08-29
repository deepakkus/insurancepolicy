<?php
$this->breadcrumbs=array(
	'Clients'=>array('admin'),
	$clientAppQuestionSet->client_id=>array('update','id'=>$clientAppQuestionSet->client_id),
	'Update Client App Question Set',
);
?>

<h1>
    Update Client App Question Set <?php echo $clientAppQuestionSet->id; ?>
</h1>

<?php

echo $this->renderPartial('_form', array('clientAppQuestionSet'=>$clientAppQuestionSet,));
