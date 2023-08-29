<?php
$this->pageTitle = 'WDSPro App - Request Password Reset';
?>

<h2>WDSPro App Password Reset Request Form</h2>

<p><?php echo $message; ?></p>

<?php
if(!isset($error) || $error > 0){ //if initial form (error is null) or if there is an error, show the form
?>
<div class="form">
    <form name="pass-reset-form" action="<?php echo $this->createUrl('fsUser/requestResetApp2UserPW'); ?>" method="post">


        <div class="row-fluid">
            <label for="username">Username: </label>
            <input type="text" name="username" />
        </div>

        <div class="row-fluid buttons">
            <?php echo CHtml::submitButton('Request Reset', array('class'=>'btn-large btn-primary')); ?>
        </div>

    </form>
</div><!-- form -->
<?php
}//end if
?>
