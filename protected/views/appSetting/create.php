<?php
/* @var $this AppSettingController */
/* @var $model AppSetting */

$this->breadcrumbs=array(
	'App Settings'=>array('admin'),
	'Create',
);

?>

<h1>Create AppSetting</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>