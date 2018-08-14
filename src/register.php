<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare a set of variables to hold the username, password, question, and answer for the new user
$username = "";
$password = "";
$email = "";
$registrationcode = "";
$studentid = "";

// Declare a list to hold error messages that need to be displayed
$errors = array();

// If someone is attempting to register, process their request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Pull the username, password, question, and answer from the <form> POST
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	$email = trim($_POST['email']);
	$studentid = trim($_POST['studentid']);
	$registrationcode = trim($_POST['registrationcode']);

	// Attempt to register the new user and capture the result flag
	$result = $app->register($username, $password, $email, $registrationcode, $studentid, $errors);

	// Check to see if the register attempt succeeded
	if ($result == TRUE) {

		// Redirect the user to the login page on success
	    header("Location: login.php?register=success");
		exit();

	}

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
		<h2>Register</h2>
		<?php include('include/messages.php'); ?>
		<div>
			<form action="register.php" method="post">
				<input type="text" name="username" id="username" placeholder="Pick a username" value="<?php echo $username; ?>" />
				<br/>
				<input type="password" name="password" id="password" placeholder="Provide a password" value="<?php echo $password; ?>" required="required" />
				<br/>
				<input type="text" name="email" id="email" placeholder="Enter your Georgia Southern email address" size="50" value="<?php echo $email; ?>" required="required" />
				<br/>
				<input type="text" name="studentid" id="studentid" placeholder="Enter your Georgia Southern eagle ID" size="50" value="<?php echo $studentid; ?>" required="required" />
				<br/>
				<input type="text" name="registrationcode" id="registrationcode" placeholder="Enter the registration code provided by your instructor" size="50" value="<?php echo $registrationcode; ?>" required="required" />
				<br/>
				<input type="submit" value="Register" />
			</form>
		</div>
		<a href="login.php">Already a member?</a>
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
