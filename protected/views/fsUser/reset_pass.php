<div class="container">	
	<h2>Reset Password</h2>
	<?php 
	if(isset($message))
	{
		if(strpos($message, "Success") === false)
			echo '<div class="medium dark bold" style="color:red">'.$message.'</div>';
		else
			echo '<div class="medium dark bold" style="color:green">'.$message.'</div>';
	}
	if(!isset($message) || strpos($message, "Error"))
	{
		echo '<div class="medium dark bold">Enter the Email address you created your WDSpro account with and an email will be sent to you with further instructions.</div>';
		echo '<form method="post">';
		echo '<input class="inputField" type="email" name="email" placeholder="Email Address" /><br/>';
		echo '<input id="reset_pass_submit" class="blueButton" type="submit" value="Submit" />';
		echo '</form>';
	}
	?>
</div>