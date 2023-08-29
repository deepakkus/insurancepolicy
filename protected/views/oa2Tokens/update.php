<?php
/* @var $this Oa2TokensController */
/* @var $model Oa2Tokens */

$this->breadcrumbs=array(
	'Oauth2 Users' => array('user/adminOauth'),
	'Oauth2 Tokens' =>  array('admin'),
    'Update',
);

$this->menu=array(
	array('label'=>'List Oa2Tokens', 'url'=>array('index')),
	array('label'=>'Create Oa2Tokens', 'url'=>array('create')),
	array('label'=>'View Oa2Tokens', 'url'=>array('view', 'id'=>$model->oauth_token)),
	array('label'=>'Manage Oa2Tokens', 'url'=>array('admin')),
);
?>

<h1>Update Oa2Tokens <?php echo $model->oauth_token; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>