<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['testdbsetup'])) {
		echo "Test DB Setup`";
	}
	if (isset($_POST['setupdb'])) {
		echo "Setup DB";
	}
}

?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<div id="barba-wrapper">
	<div class="barba-container">
	<main id="wrapper">
		<h2>IT 3234 Class Discussions</h2>
		<p>
			First time setup required.
			Please provide the following information and click the Setup button.
		</p>
		<form action="setup.php" method="post" name="setupdbform">
			<label for="servername">Database server name:</label>
			<input type="text" name="servername" id="servername">
			<br>
			<label for="serverusername">Database username:</label>
			<input type="text" name="serverusername" id="serverusername">
			<br>
			<label for="serverdbname">Database name:</label>
			<input type="text" name="serverdbname" id="serverdbname">
			<br>
			<label for="serverpassword">Database password:</label>
			<input type="password" name="serverpassword" id="serverpassword">
			<br>
			<input type="submit" name="testdbsetup" id="testdbsetup" value="Test Settings">
			<br>
			<input type="submit" name="setupdb" id="setupdb" value="Setup!">
		</form>
	</main>
	</div>
	</div>
</body>
</html>
