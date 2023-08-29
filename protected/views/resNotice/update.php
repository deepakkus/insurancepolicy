<?php

/* @var $this ResNoticeController */
/* @var $model ResNotice */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Notifications' => array('/resNotice/admin'),
    'Update'
);

?>

<h1>Update Notice <?php echo $model->notice_id; ?> for <?php echo $model->clientName; ?></h1>

<?php  $this->renderPartial('_form', array(
    'model' => $model,
    'fire_id' => $fire_id,
    'client_id' => $client_id
)); ?>