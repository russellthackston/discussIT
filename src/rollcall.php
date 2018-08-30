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

