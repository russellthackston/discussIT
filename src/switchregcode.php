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


//current state
$loggedinuser = $app->getSessionUser($errors);
$loggedinusersessionid = $loggedinuser["usersessionid"];
$loggedinuserid = $loggedinuser["userid"];
$loggedinuserregcode = $loggedinuser["registrationcode"];



// If registration code is passed in URL, use that value
if (isset($_GET['regcode'])) {

	// Get the new registration code
	$regcode = $_GET['regcode'];

//If registration code is not in the url, go get the other value.
} else{

	$registrationCodes = $app->getUserRegistrations($loggedinuserid, $errors);

	foreach ($registrationCodes as $index=>$registrationCode){
		if ($registrationCode == $loggedinuserregcode){
			$newindex = $index +1;
		}
	}
	
	if ($newindex == count($registrationCodes)){
		$newindex = 0;
	}
	
	$regcode = $registrationCodes[$newindex];
}



// Update the reg code
$result = $app->updateSession($loggedinuserid, $loggedinusersessionid, $regcode, $errors);

	


header("Location: list.php?regcode=switch&x=$regcode");
exit();

?>