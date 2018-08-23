<?php

function testconnection($servername, $serverdb, $serverusername, $serverpassword) {
	$testconnect = TRUE;
	try {
		$dbh = new PDO("mysql:host=$servername;dbname=$serverdb", $serverusername, $serverpassword);
	} catch (PDOException $e) {
		$testconnect = FALSE;
	}
	return $testconnect;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$servername = $_POST['servername'];
	$serverdb = $_POST['serverdbname'];
	$serverusername = $_POST['serverusername'];
	$serverpassword = $_POST['serverpassword'];
	$adminusername = $_POST['adminusername'];
	$adminpassword = $_POST['adminpassword'];
	$adminemail = $_POST['adminemail'];
	$regcode = $_POST['regcode'];
	$starttime = $_POST['starttime'];
  $endtime = $_POST['endtime'];

	// Validate user input
	if (empty($servername) || empty($serverdb) || empty($serverusername) || empty($serverpassword) ||
		empty($adminusername) || empty($adminpassword) || empty($adminemail) || empty($regcode) ||
		empty($starttime) || empty($endtime)) {

		$missingdata = TRUE;

	} else {

		if (isset($_POST['testdbsetup'])) {
			$testconnect = testconnection($servername, $serverdb, $serverusername, $serverpassword);
		}
		if (isset($_POST['setupdb'])) {
			$testconnect = testconnection($servername, $serverdb, $serverusername, $serverpassword);
			if ($testconnect) {
				if (file_exists('include/credentials.php')) {
					unlink('include/credentials.php');
				}
				$data = "<?php" . PHP_EOL .
					"" . PHP_EOL .
					"// Declare the credentials to the database" . PHP_EOL .
					"\$servername = '$servername';" . PHP_EOL .
					"\$serverdb = '$serverdb';" . PHP_EOL .
					"\$serverusername = '$serverusername';" . PHP_EOL .
					"\$serverpassword = '$serverpassword';" . PHP_EOL .
					"" . PHP_EOL;
				if (file_put_contents('include/credentials.php', $data) === FALSE) {
					$credwritten = FALSE;
				} else {
					$dbh = NULL;
					try {
						$dbh = new PDO("mysql:host=$servername;dbname=$serverdb", $serverusername, $serverpassword);
					} catch (PDOException $e) {
						print "Error connecting to the database.";
						$this->debug($e);
						die();
					}
					$sql = file_get_contents('discussion.sql');
					$dbh->exec($sql);

					// Create the first registration code
					$sql = "INSERT INTO registrationcodes (registrationcode, starttime, endtime) " .
						"VALUES (:regcode, :starttime, :endtime)";
					$stmt = $dbh->prepare($sql);
					$stmt->bindParam(":regcode", $regcode);
					$stmt->bindParam(":starttime", $starttime);
					$stmt->bindParam(":endtime", $endtime);
					$stmt->execute();

					// Create the admin user
					$userid = bin2hex(random_bytes(16));
					$passwordhash = password_hash($adminpassword, PASSWORD_DEFAULT);
					$sql = "INSERT INTO users (userid, username, passwordhash, email, isadmin, emailvalidated) " .
						"VALUES (:userid, :username, :passwordhash, :email, 1, 1)";
					$stmt = $dbh->prepare($sql);
					$stmt->bindParam(":userid", $userid);
					$stmt->bindParam(":username", $adminusername);
					$stmt->bindParam(":passwordhash", $passwordhash);
					$stmt->bindParam(":email", $adminemail);
					$stmt->execute();

					// Create the admin user registration
					$sql = "INSERT INTO userregistrations (registrationcode, userid) " .
						"VALUES (:regcode, :userid)";
					$stmt = $dbh->prepare($sql);
					$stmt->bindParam(":regcode", $regcode);
					$stmt->bindParam(":userid", $userid);
					$stmt->execute();

					$setupcomplete = TRUE;

				}
			}
		}

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
		<?php if (isset($setupcomplete) && $setupcomplete)  { ?>
			<p>Setup complete. Go to the
				<a href="login.php">login page</a> 
				and test things out.</p>
		<?php } ?>
		<?php if (isset($missingdata) && $missingdata)  { ?>
			<p>Please complete all fields.</p>
		<?php } ?>
		<?php if (isset($testconnect) && $testconnect)  { ?>
			<p>Connection successful.</p>
		<?php } ?>
		<?php if (isset($testconnect) && !$testconnect)  { ?>
			<p>Connection failed.</p>
		<?php } ?>
		<?php if (isset($credwritten) && !$credwritten)  { ?>
			<p>Could not write to credentials file. Permissions error?</p>
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
			<hr>
			<label for="regcode">First registration code:</label>
			<input type="text" name="regcode" id="regcode" value="<?php echo $regcode; ?>">
			<br>
			<label for="starttime">Topic start time:</label>
			<input type="time" name="starttime" id="starttime" value="<?php echo $starttime; ?>">
			<br>
			<label for="endtime">Topic end time:</label>
			<input type="time" name="endtime" id="endtime" value="<?php echo $endtime; ?>">
			<hr>
			<label for="adminusername">Admin user username:</label>
			<input type="text" name="adminusername" id="adminusername" value="<?php echo $adminusername; ?>">
			<br>
			<label for="adminpassword">Admin user password:</label>
			<input type="password" name="adminpassword" id="adminpassword" value="<?php echo $adminpassword; ?>">
			<br>
			<label for="adminemail">Admin user email address:</label>
			<input type="text" name="adminemail" id="adminemail" value="<?php echo $adminemail; ?>">
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
