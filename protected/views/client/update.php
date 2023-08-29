<?php
$this->breadcrumbs=array(
	'Clients'=>array('admin'),
	$client->id=>array('update','id'=>$client->id),
	'Update',
);
?>

<h1>Update Client <?php echo $client->id; ?></h1>

<?php 

echo $this->renderPartial('_form', array(
    'client' => $client,
    'clientStates' => $clientStates
));
$this->renderPartial('//clientAppQuestionSet/_grid', array('clientAppQuestionSets'=>$clientAppQuestionSets,'client'=>$client));
$this->renderPartial('_assessmentQuestions',array('clientQuestions'=>$clientQuestions,'client'=>$client));

?>