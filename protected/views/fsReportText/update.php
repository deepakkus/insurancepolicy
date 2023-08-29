<?php
$this->breadcrumbs=array(
	'FS Report Texts'=>array('admin'),
	$fsReportText->id=>array('update','id'=>$fsReportText->id),
	'Update',
);
?>

<h1>Update FS Report Text (id: <?php echo $fsReportText->id; ?>)</h1>

<?php echo $this->renderPartial('_form', array('fsReportText'=>$fsReportText)); ?>