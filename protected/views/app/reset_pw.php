<?php
$this->pageTitle = 'WDSPro App - Reset User Password';
?>

<h2>WDSPro App - Reset User Password</h2>

<p>
    <?php echo $message; ?>
</p>

<?php
if(!isset($error) || $error > 0){ //if initial form (error is null) or if there is an error, show the form
?>
<div class="form">
    <form name="pass-reset-form" action="<?php echo $this->createUrl('app/resetUserPW', array('reset_token'=>$reset_token)); ?>" method="post">

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
</div>
<!-- form -->
<?php
}//end if
?>
