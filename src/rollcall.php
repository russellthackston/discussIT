<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in user since this page is protected
$app->protectPage($errors);

$loggedInUser = $app->getSessionUser($errors);
$isadmin = $loggedInUser['isadmin'] == 1;

if (!$isadmin) {

	// Submit the roll call 
	$app->submitRollcall($errors);
	
	// If there was an error, send to homepage to display
	if (sizeof($errors) > 0) {
		echo "Error!";
	} else {
		echo "Present!";
	}
	
	exit();

}

$roll = $app->getRollcall($loggedInUser['registrationcode'], $errors);
$present = 0;
$notpresent = 0;
foreach($roll as $r) {
	if ($r['present'] == 1) {
		$present = $present + 1;
	} else {
		$notpresent = $notpresent + 1;
	}
}
$message = $present . " student present and " . $notpresent . " absent.";

?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<?php include 'include/header.php'; ?>
	<div id="barba-wrapper">
	<div class="barba-container">
	<main id="wrapper">
		<?php include('include/messages.php'); ?>
		<table id="rollcall">
			<tr>
				<th>Student ID</th>
				<th>Name</th>
			<?php foreach($roll as $student) { ?>
				<tr class="<?php if ($student['present'] == 0) { echo "notpresent"; } ?>">
					<td><?php echo $student['studentid']; ?></td>
					<td><?php echo $student['studentname']; ?></td>
				</tr>
			<?php } ?>
		</table>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/site.js"></script>
	<script src="js/barba.js"></script>
	<script src="js/mybarba.js"></script>
</body>
</html>
