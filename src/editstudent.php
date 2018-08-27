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

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody);
    $studentid = $data->studentid;
    $studentname = $data->studentname;
    $result = $app->updateStudent($studentid, $studentname, $errors);
    if ($result) {
    	$response = $studentname;
    	echo json_encode($response);
    } else {
        echo json_encode('Error');
    }

}

?>
