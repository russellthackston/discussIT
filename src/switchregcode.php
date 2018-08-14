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


// Switch reg code
if (isset($_GET['regcode'])) {

	// Get the new registration code
	$regcode = $_GET['regcode'];

	$loggedinuser = $app->getSessionUser($errors);
	$loggedinusersessionid = $loggedinuser["usersessionid"];
	
	// Update the reg code
	$result = $app->updateSession($loggedinusersessionid, $regcode, $errors);

}

header("Location: list.php?regcode=switch");
exit();

?>