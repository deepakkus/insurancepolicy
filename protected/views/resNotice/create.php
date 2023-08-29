<?php

/* @var $this ResNoticeController */
/* @var $model ResNotice */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Notifications' => array('/resNotice/admin'),
    'Create'
);

?>

<h1>Create Notice for <?php echo Client::model()->findByPk($client_id)->name; ?></h1>

<?php $this->renderPartial('_form', array(
    'model' => $model,
    'client_id' => $client_id
)); ?>