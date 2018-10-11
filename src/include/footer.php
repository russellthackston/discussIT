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
		Background courtesy of 1001FreeDownloads.com
		<br>
		<br>
		<?php if (isset($app->getSessionUser($errors)["username"]) && $app->getSessionUser($errors)["username"] != null) { ?>
		You are logged in as <?php echo $app->getSessionUser($errors)["username"]; ?>.
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
