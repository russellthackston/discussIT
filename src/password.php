<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

$errors = array();
$messages = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	$passwordrequestid = $_GET['id'];

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Grab or initialize the input values
	$password = $_POST['password'];
	$passwordrequestid = $_POST['passwordrequestid'];

	// Request a password reset email message
	$app->updatePassword($password, $passwordrequestid, $errors);
	
	if (sizeof($errors) == 0) {
		$message = "Password updated";
	}
	
}

?>

<!doctype html>

<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<script src="js/site.js"></script>
	<?php include 'include/header.php'; ?>
	<div id="barba-wrapper">
	<div class="barba-container">
	<main id="wrapper">
		<h2>Reset Password</h2>
		<?php include('include/messages.php'); ?>
		<form method="post" action="password.php">
			New password:
			<input type="password" name="password" id="password" required="required" size="40" />
			<input type="submit" value="Submit" />
			<input type="hidden" name="passwordrequestid" id="passwordrequestid" value="<?php echo $passwordrequestid; ?>" />
		</form>
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
