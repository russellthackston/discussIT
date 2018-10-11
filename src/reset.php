<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

$errors = array();
$messages = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Grab or initialize the input values
	$usernameOrEmail = $_POST['usernameOrEmail'];

	// Request a password reset email message
	$app->passwordReset($usernameOrEmail, $errors);
	
	$message = "An email has been sent to the specified account, if it exists. Please check your spam folder.";

}

?>

<!doctype html>

<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<?php include 'include/header.php'; ?>
	<div id="barba-wrapper">
	<div class="barba-container">
	<main id="wrapper">
		<h2>Reset Password</h2>
		<?php include('include/messages.php'); ?>
		<form method="post" action="reset.php">
			<input type="text" name="usernameOrEmail" id="usernameOrEmail" placeholder="Enter your username or email address" required="required" size="40" />
			<input type="submit" value="Submit" />
		</form>
		<a href="register.php">Need to create an account?</a>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
