<div class="container">
	<h2>Schedule Call</h2>
	<div class="medium dark bold">Choose a preferred date and time for a Wildfire Defense Systems specialist to call and discuss your Assessment</div>
	<form method="post"  data-ajax="false">
		<label for="call_date">Date</label>
		<input name="call_date" id="call_date" type="text" data-role="datebox" min="<?php echo $min_date; ?>" data-options='{"mode":"calbox", "blackDays":[0,6], "useFocus": true}' />
		<label for="call_time">Time</label>
		<?php
		echo '<input name="call_time" id="mode6" type="text" data-role="datebox" data-options=\'{"mode":"timeflipbox", "useFocus": true, "minHour":"'.$min_hour.'", "maxHour":"'.$max_hour.'"}\' />';
		?>
		<?php //if the OAuth access token is not in the url that the form gets resubmitted to already, then we need to add it in as a post param
		if(!isset($_GET['access_token']))
			echo '<input type="hidden" name="access_token" value="'.$access_token.'" />';
		
		echo '<input type="hidden" name="time_zone" value="'.$time_zone.'" />';
		echo '<input type="hidden" name="login_token" value="'.$login_token.'" />';
		echo '<input type="hidden" name="report_guid" value="'.$report_guid.'" />';
		?>
		<input id="schedule_call_submit" name="submit" type="submit" value="Submit" />
	</form>
</div>