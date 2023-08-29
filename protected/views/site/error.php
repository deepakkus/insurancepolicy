<?php
$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	'Error',
);
?>

<h2>Error <?php echo $code; ?></h2>
<h2>Error Type <?php echo $type; ?></h2>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>