<?php

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in admin user since this page is "isadmin" protected
// NOTE: passing optional parameter TRUE which indicates the user must be an admin
$app->protectPage($errors, TRUE);

// If someone is adding a new attachment type
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $this->auditlog("editStudent.php", "Request to edit");
    $this->auditlog("editStudent.php", $_POST);

    // Parse the JSON
    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody);
    $this->auditlog("editStudent.php", $entityBody);

    // Get the individual attributes
    $studentid = $data->studentid;
    $studentname = $data->studentname;

    // Add the critique to the database
    $result = $app->updateStudent($studentid, $studentname, $errors);

    if ($result) {
    	$response = $studentname
    	echo json_encode($response);
    }

}
