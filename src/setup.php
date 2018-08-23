<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$servername = $_POST['servername'];
	$serverdb = $_POST['serverdbname'];
	$serverusername = $_POST['serverusername'];
	$serverpassword = $_POST['serverpassword'];
	if (isset($_POST['testdbsetup'])) {
		$testconnect = TRUE;
		try {
			$dbh = new PDO("mysql:host=$servername;dbname=$serverdb", $serverusername, $serverpassword);
		} catch (PDOException $e) {
			$testconnect = FALSE;
		}
	}
	if (isset($_POST['setupdb'])) {

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
		<?php if (isset($testconnect) && $testconnect)  { ?>
			<p>Connection successful.</p>
		<?php } ?>
		<?php if (isset($testconnect) && !$testconnect)  { ?>
			<p>Connection failed.</p>
		<?php } ?>
		<form action="setup.php" method="post" name="setupdbform">
			<label for="servername">Database server name:</label>
			<input type="text" name="servername" id="servername" value="<?php echo $servername; ?>">
			<br>
			<label for="serverdbname">Database name:</label>
			<input type="text" name="serverdbname" id="serverdbname" value="<?php echo $serverdb; ?>">
			<br>
			<label for="serverusername">Database username:</label>
			<input type="text" name="serverusername" id="serverusername" value="<?php echo $serverusername; ?>">
			<br>
			<label for="serverpassword">Database password:</label>
			<input type="password" name="serverpassword" id="serverpassword" value="<?php echo $serverpassword; ?>">
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
