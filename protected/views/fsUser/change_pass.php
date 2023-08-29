<div class="passwordContainer">
	<h2>Change Password</h2>
	<?php 
	if(isset($message))
		echo '<div class="medium dark bold" style="color:red">'.$message.'</div>';
	?>
	<div class="medium dark bold">Enter your current WDSpro account login and password and new password.</div>
	<form method="post" data-ajax="false">
		<input class="inputField" type="email" name="FSUser[email]" placeholder="Email Address" /><br/>
		<input class="inputField" type="password" name="FSUser[password]" placeholder="Current Password" /><br/>
		<input class="inputField" type="password" name="new_pass" placeholder="New Password" /><br/>
		<input class="inputField" type="password" name="new_pass_confirm" placeholder="Confirm Password" /><br/>
		<?php //if the OAuth access token is not in the url that the form gets resubmitted to already, then we need to add it in as a post param
		if(!isset($_GET['access_token']))
			echo '<input type="hidden" name="access_token" value="'.$access_token.'" />';
		?>
		<input id="change_pass_submit" name="submit" type="submit" value="Submit" />
	</form>
</div>
