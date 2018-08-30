<?php
$regs = array();
if ($user != null) {
	$regs = $app->getUserRegistrations($user['userid'], $errors);
}
?>

<footer id="sitefooter">
	<p class="footer">
		Copyright &copy; <?php echo date("Y"); ?> Russell Thackston, Laura Thackston
		<br>
		<?php if (isset($loggedinusername) && $loggedinusername != null) { ?>
		You are logged in as <?php echo $loggedinusername; ?>.
		<?php } ?>
		<br>
		<?php if (isset($loggedinusername) && $loggedinusername != null) { ?>
			<?php if (!$isadmin) { ?>
			<input type="submit" name="rollcall" value="Roll call" id="rollcall" onclick="rollcall(this);" />
			<?php } ?>
			<br>
			<span id="rollcallresult"></span>
			<?php if (sizeof($regs) > 1) { ?>
				<br>
				<label for="switchregcode">Switch Course</label>
				<select id="switchregcode" name="switchregcode" onchange="switchregcode()">
					<?php foreach($regs as $code) { ?>
					<option value="<?php echo $code; ?>" <?php if ($code == $loggedinuserregistrationcode) { echo "selected='selected'"; } ?> ><?php echo $code; ?></option>
					<?php } ?>
				</select>
			<?php } ?>
		<?php } ?>
	</p>
	<?php
	if ($_COOKIE['debug'] == "true") {
		if (isset($debugMessages)) {
			print("<pre>");
			foreach ($debugMessages as $msg) {
				var_dump($msg);
			}
			print("</pre>");
		}
	}
	?>
</footer>
