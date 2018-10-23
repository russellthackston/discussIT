<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in user since this page is protected
$app->protectPage($errors, TRUE);

// Parse the JSON
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody);

if ($data->action == "add") {

	// Get the individual attributes
	$notetext = $data->text;
	$noteorder = $data->order;
	
	// Add the critique to the database
	$result = $app->addNote($notetext, $noteorder, $errors);
	
}

if ($data->action == "delete") {

	// Get the individual attributes
	$noteid = $data->id;

	// Add the critique to the database
	$result = $app->deleteNote($noteid, $errors);

}

if ($result) {
	$notes = $app->getNotes($app->getSessionUser($errors)['registrationcode'], $errors);
	require('include/noteslist.php');
}

?>
