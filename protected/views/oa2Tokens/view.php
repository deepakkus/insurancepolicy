<?php
/* @var $this Oa2TokensController */
/* @var $model Oa2Tokens */

$this->breadcrumbs=array(
	'Oauth2 Users' => array('user/adminOauth'),
	'Oauth2 Tokens' =>  array('admin'),
    'View',
);

$this->menu=array(
	array('label'=>'List Oa2Tokens', 'url'=>array('index')),
	array('label'=>'Create Oa2Tokens', 'url'=>array('create')),
	array('label'=>'Update Oa2Tokens', 'url'=>array('update', 'id'=>$model->oauth_token)),
	array('label'=>'Delete Oa2Tokens', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->oauth_token),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Oa2Tokens', 'url'=>array('admin')),
);
?>
<div class = "container-fluid">
    <div class ="row">
<h1>View Oa2Tokens #<?php echo $model->oauth_token; ?></h1>

        <?php $this->widget('zii.widgets.CDetailView', array(
	        'data'=>$model,
            'htmlOptions' => array('style'=>'margin-top:25px', 'class'=>'table'),
	        'attributes'=>array(
		        'oauth_token',
		        'client_id',
		        'expires',
		        'scope',
		        'type',
	        ),
        )); ?>
    </div>
</div>
