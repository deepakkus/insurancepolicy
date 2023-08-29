<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>
<?php 
$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);
?>

<h1>Login</h1>

<p>Please fill out the following form with your login credentials:</p>

<div class="form">
<?php $form = $this->beginWidget('CActiveForm', array(
	'id' => 'login-form',
	'enableClientValidation' => true,
    'focus' => array($model, 'username'),
	'clientOptions' => array(
		'validateOnSubmit' => true
	)
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<div>
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username',array('autocomplete' => 'off', 'autocapitalize'=>'none')); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'password'); ?>
        <?php echo $form->passwordField($model,'password',array('autocomplete' => 'off')); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<div class="buttons">
		<?php echo CHtml::submitButton('Login', array('class'=>'btn-large btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->

<?php
echo CHtml::link('Reset your password',array('user/requestResetPass'));
