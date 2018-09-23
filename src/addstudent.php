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

// If someone is adding a new student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody);
    $studentid = $data->studentid;
    $studentname = $data->studentname;
    $registrationcode = $data->regcode;
    $result = $app->addStudent($studentid, $studentname, $registrationcode, $errors);
    if ($result) {
    	echo json_encode(array('studentname' => $studentname, 'studentid' => $studentid, 'regcode' => $registrationcode, 'isreg' => 'Unknown'));
    } else {
        echo json_encode(array('studentname' => 'Error'));
    }

}

?>
