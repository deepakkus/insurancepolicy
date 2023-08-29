<?php
$this->breadcrumbs=array(
	'Properties'=>array('admin'),
	'Create',
);

?>

<h1>Create a New Property for <?php echo $member->first_name . " " . $member->last_name; ?></h1>

<?php echo $this->renderPartial('_form', array('member'=>$member, 'property'=>$property)); ?>
