<?php
$this->breadcrumbs=array(
	'Members'=>array('admin'),
	'Create',
);

?>

<h1>Create a New Client Member</h1>

<?php echo $this->renderPartial('_form', array('member'=>$member)); ?>
