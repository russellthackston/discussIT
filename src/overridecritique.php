<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in ADMIN user since this page is protected
$app->protectPage($errors, TRUE);

// Get the request parameters
$critiqueid = $_GET['id'];
$action = $_GET['action'];

if ($action == 'override') {
	// Override the negative critique
	$result = $app->overrideCritique($critiqueid, $errors);
} else if ($action == 'undo') {
	// Undo the override of the negative critique
	$result = $app->undoOverrideCritique($critiqueid, $errors);
}

if ($result) {
	echo "Success";
} else {
	echo "Failure";
}

?>
