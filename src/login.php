<?php
//set currentPage
$currentPage = "navLogin";

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare a set of variables to hold the username and password for the user
$username = "";
$password = "";

// Declare an empty array of error messages
$errors = array();

// If the user is already logged in, send them to the topic list
$loggedinuser = $app->getSessionUser($errors);
if ($loggedinuser != null) {

	// Redirect the user to the topics page
	header("Location: list.php");
	exit();

}

// If someone has clicked their email validation link, then process the request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	if (isset($_GET['id'])) {
		
		$success = $app->processEmailValidation($_GET['id'], $errors);
		if ($success) {
			$message = "Email address validated. You may login.";
		}

	}

}

// If someone is attempting to login, process their request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Pull the username and password from the <form> POST
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);

	// Attempt to login the user and capture the result flag
	$result = $app->login($username, $password, $errors);

	// Check to see if the login attempt succeeded
	if ($result == TRUE) {

		// Redirect the user to the topics page on success
		header("Location: list.php");
		exit();

	}

}

if (isset($_GET['register']) && $_GET['register']== 'success') {
	$message = "Registration successful. Please check your email. A message has been sent to validate your address.";
}

?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<?php include 'include/header.php'; ?>
	<div id="barba-wrapper">
	<div class="barba-container" data-id="navLogin">
	<main id="wrapper">
		<h2>Login</h2>
	
		<?php include('include/messages.php'); ?>
		<div>
			<form method="post" action="login.php">
				
				<input type="text" name="username" id="username" aria-label="Username" title="Username" placeholder="Username" value="<?php echo $username; ?>" required="required" />
				<br/>
	
				<input type="password" name="password" id="password" aria-label="Password" title="Password" placeholder="Password" value="<?php echo $password; ?>" required="required" />
				<br/>
	
				<input type="submit" value="Login" name="login" />
			</form>
		</div>
		<a href="register.php">Need to create an account?</a>
		<br/>
		<a href="reset.php">Forgot your password?</a>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
