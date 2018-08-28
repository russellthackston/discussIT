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

// Parse the JSON
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody);

// Get the individual attributes
$commentid = $data->commentid;
$text = $data->text;
$addstodiscussion = ($data->vote == "up");

// Add the critique to the database
$result = $app->addCritique($text, $commentid, $addstodiscussion, $errors);

if ($result) {
	$rcritiques = $app->getCritiques($commentid, $errors);
	<?php require('include/critiqueslist.php'); ?>
}

?>
