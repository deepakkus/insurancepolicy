<?php 
$this->pageTitle=Yii::app()->name . ' - Reset Password';
?>

<h1>Request Password Reset</h1>

<p><?php echo $message; ?></p>

<?php
if(!isset($error) || $error > 0){ //if initial form (error is null) or if there is an error, show the form
?>
    <div class="form">
        <form name="pass-reset-form" action="<?php echo $this->createUrl('user/resetPass', array('reset_token'=>$reset_token, 'return_url'=>$return_url)); ?>" method="post">

            <div class="row-fluid">
                <label for="username">New Password: </label>
                <input type="password" name="new_pass" />
	        </div>
            <div class="row-fluid">
                <label for="username">Confirm New Password: </label>
                <input type="password" name="new_pass_confirm" />
	        </div>

	        <div class="row-fluid buttons">
		        <?php echo CHtml::submitButton('Reset Password', array('class'=>'btn-large btn-primary')); ?>
	        </div>

        </form>
    </div><!-- form -->
<?php
}//end if
?>
