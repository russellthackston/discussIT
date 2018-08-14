<?php

// Import the application classes
require_once('include/classes.php');

// Declare an empty array of error messages
$errors = array();

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Check for logged in user since this page is protected
$app->protectPage($errors);

// Get a list of registration codes
$allCodes = $app->getRegistrationCodes($errors);

// Declare a set of variables to hold the details for the user
$userid = "";
$username = "";
$isadminFlag = FALSE;
$registrationCode = "";

$loggedinuser = $app->getSessionUser($errors);
$loggedinuserid = $loggedinuser["userid"];
$loggedinusersessionid = $loggedinuser["usersessionid"];
$loggedinusername = $loggedinuser["username"];
$loggedinuserisadmin = $loggedinuser["isadmin"];
$loggedinuserregistrationcode = $loggedinuser['registrationcode'];

// If someone is accessing this page for the first time, try and grab the userid from the GET request
// then pull the user's details from the database
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	// Get the userid
	if (!isset($_GET['userid'])) {

		$userid = $loggedinuserid;

	} else {

		$userid = $_GET['userid'];
		
	}
	
	// Attempt to obtain the user information.
	$user = $app->getUser($userid, $errors);
	
	if ($user != NULL){
		$username = $user['username'];
		$isadminFlag = ($user['isadmin'] == "1");
		$password = "";
		$regs = $app->getUserRegistrations($userid, $errors);
	}

// If someone is attempting to edit their profile, process the request	
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	if (isset($_POST['editprofile'])) {
		
		// Get the form values 
		$userid   = $_POST['userid'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		if (isset($_POST['isadmin']) && $_POST['isadmin'] == "isadmin") {
			$isadminFlag = TRUE;
		} else {
			$isadminFlag = FALSE;
		}
		if (isset($_POST['registrationcode'])) {
			$app->auditlog("editprofile.php", "Updating reg code to " . $_POST['registrationcode']);
			$registrationCode = $_POST['registrationcode'];
		} else {
			$user = $app->getUser($userid, $errors);
			$registrationCode = $user['registrationcode'];
		}
	
		// Attempt to update the user information.
		$result = $app->updateUser($userid, $username, $password, $isadminFlag, $errors);
		
		// Display message upon success.
		if ($result == TRUE){
	
			$result = $app->updateSession($loggedinusersessionid, $registrationCode, $errors);
			
			if ($result) {
				$message = "User successfully updated.";
				$loggedinuserregistrationcode = $registrationCode;
			}
			
		}

	} else if (isset($_POST['addregcode'])) {

		$userid = $_POST['userid'];
		$registrationCode = $_POST['regcode'];

		// Attempt to update the user information.
		$result = $app->addUserRegistration($userid, $registrationCode, $errors);

		if ($result) {

			$message = "Course registration added.";

		}

	}
		

}

$user = $app->getUser($userid, $errors);
$regs = $app->getUserRegistrations($userid, $errors);

// Load the progress report for the user being edited
$progressReport = $app->getProgressReport($userid, $loggedinuserregistrationcode, $errors);
if ($progressReport['numberofcommentsmade'] && $progressReport['numberofcommentsmade'] != 0 && $progressReport['numberoftopics'] != 0) {
	$commentinggrade = round(100 * $progressReport['numberofcommentsmade'] / $progressReport['numberoftopics']) . "%";
} else {
	$commentinggrade = "No data";
}
if ($progressReport['numberofcritiquesreceived'] && $progressReport['numberofcritiquesreceived'] != 0) {
	$commentQuality = round(100 * $progressReport['up'] / $progressReport['numberofcritiquesreceived']) . "%";
} else {
	$commentQuality = "No data";
}
if ($progressReport['numberofcritiquesexpected'] && $progressReport['numberofcritiquesexpected'] != 0) {
	$critiqueQuality = round(100 * $progressReport['critiques'] / $progressReport['numberofcritiquesexpected']) . "%";
} else {
	$critiqueQuality = "No data";
}

?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<?php include 'include/header.php'; ?>
	<div id="barba-wrapper">
	<div class="barba-container">
	<main id="wrapper">
		<?php if (isset($progressReport) && sizeof($progressReport) > 0) { ?>
		<h2>Stats for <?php echo $username; ?></h2>
		<div class="progress">
			<div>Topics (current &amp; past): <?php echo $progressReport['numberoftopics']; ?></div>
			<div>Comments made: <?php echo $progressReport['numberofcommentsmade']; ?></div>
			<br>
			<div>Positive critiques received: <?php echo $progressReport['up']; ?></div>
			<div>Negative critiques received: <?php echo $progressReport['down']; ?></div>
			<div>Total number of critiques received: <?php echo $progressReport['numberofcritiquesreceived']; ?></div>
			<br>
			<div>Critiques given: <?php echo $progressReport['critiques']; ?></div>
			<div>Critiques expected: <?php echo $progressReport['numberofcritiquesexpected']; ?></div>
		</div>
		<hr>
		<h2>Grades for <?php echo $username; ?></h2>
		<div class="progress">
			<div>Commenting grade: <?php echo $commentinggrade; ?></div>
			<div>Critiquing grade: <?php echo $critiqueQuality; ?></div>
			<div>Comment quality: <?php echo $commentQuality; ?></div>
		</div>
		<hr>
		<?php } ?>
		<h3>Edit Profile</h3>
		<div class="profile">
			<?php include 'include/messages.php'; ?>	
			<form action="editprofile.php" method="post">
				<fieldset>
					<legend class="visuallyhidden">Edit Profile Form:</legend>
					
					<input type="hidden" name="userid" value="<?php echo $userid; ?>" />
					
					<label for="username">User Name</label>
					<input type="text" name="username" id="username" placeholder="Pick a username" required="required" value="<?php echo $username ; ?>" />
					<br/>
					
					<label for="password">Password (optional)</label>
					<input type="password" name="password" id="password" placeholder="Enter a password" value="<?php echo $password; ?>" /> 
					<br/>
					
					<label for="registrationcode">Current Course</label>
					<select id="registrationcode" name="registrationcode">
						<?php foreach($regs as $code) { ?>
						<option value="<?php echo $code; ?>" <?php if ($code == $loggedinuserregistrationcode) { echo "selected='selected'"; } ?> ><?php echo $code; ?></option>
						<?php } ?>
					</select>			
					<br/>
					
					<?php if ($loggedinuserid != $userid) { ?>
					<label for="isadmin">Grant admin rights</label>
					<input type="checkbox" name="isadmin" id="isadmin" <?php echo ($isadminFlag ? "checked=checked" : ""); ?> value="isadmin" />
					<?php } ?>
					<br/>
							
					<input type="hidden" name="editprofile" value="editprofile">
					<input type="submit" value="Update profile" />
				</fieldset>
			</form>
		</div>
		<h3>Register for Another Course</h3>
		<div class="register">
			<form action="editprofile.php" method="post">
				<fieldset>
					<input type="hidden" name="userid" value="<?php echo $userid; ?>" />
					
					<label for="regcode" class="visuallyhidden">Enter a Registration Code:</label>
					<input type="text" name="regcode" id="regcode" size="30" maxlength="255" placeholder="Enter a new registration code" required="required" value="<?php echo $registrationCode ; ?>" />
					<br/>
					
					<input type="hidden" name="addregcode" value="addregcode">
					<input type="submit" value="Add code" />
				</fieldset>
			</form>
		</div>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/site.js"></script>
	<script src="js/barba.js"></script>
	<script src="js/mybarba.js"></script>
</body>
</html>
