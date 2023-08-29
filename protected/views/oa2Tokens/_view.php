<?php
/* @var $this Oa2TokensController */
/* @var $data Oa2Tokens */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('oauth_token')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->oauth_token), array('view', 'id'=>$data->oauth_token)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('client_id')); ?>:</b>
	<?php echo CHtml::encode($data->client_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('expires')); ?>:</b>
	<?php echo CHtml::encode($data->expires); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('scope')); ?>:</b>
	<?php echo CHtml::encode($data->scope); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('type')); ?>:</b>
	<?php echo CHtml::encode($data->type); ?>
	<br />


</div>