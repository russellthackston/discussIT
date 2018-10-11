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

// Get the logged in user
$loggedInUser = $app->getSessionUser($errors);
$loggedinuserregistrationcode = $loggedInUser['registrationcode'];
$isAdmin = $app->isAdmin($errors, $loggedInUser['userid']);

// Declare local variables
$thingid = "";
$search = "";
$name = "";
$description = "";
$commentsopendate = "";
$commentsclosedate = "";
$critiquesclosedate = "";
$includeingrading = FALSE;

// Get the my registration code and the full list
$myCode = $loggedInUser['registrationcode'];
$allCodes = $app->getRegistrationCodes($errors);
$justCodes = array_column($allCodes, 'registrationcode');

// When loading the page, get the thing ID from the request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Get the thing ID
	$thingid = $_GET['thingid'];
	$thing = $app->getThing($thingid, $errors);

	// If there were no errors getting the thing, try to get the comments
	if (sizeof($errors) == 0) {

		$name = $thing['thingname'];
		$description = $thing['thingdescription'];
		$regCode = $thing['thingregistrationcode'];
		//$attachment = $_FILES['attachment'];
		$commentsopendate = $thing['commentsopendate'];
		$commentsclosedate = $thing['commentsclosedate'];
		$critiquesclosedate = $thing['critiquesclosedate'];
		if (isset($thing['includeingrading']) && $thing['includeingrading'] == '1') {
            $includeingrading = TRUE;
        }

	} else {
		$errors[] = "Could not load thing";
	}

}

// If someone is attempting to create a new thing, the process the request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (isset($_POST['update']) && $_POST['update'] == 'discussion') {

		// Pull the title and thing text from the <form> POST
		$thingid = $_POST['thingid'];
		$name = $_POST['name'];
		$description = $_POST['description'];
		$regCode = $_POST['code'];
		//$attachment = $_FILES['attachment'];
		$commentsopendate = $_POST['commentsopendate'];
		$commentsclosedate = $_POST['commentsclosedate'];
		$critiquesclosedate = $_POST['critiquesclosedate'];
		if (isset($_POST['includeingrading']) && $_POST['includeingrading'] == 'yes') {
            $includeingrading = TRUE;
        } else {
	        $includeingrading = FALSE;
        }


		// Attempt to create the new thing and capture the result flag
		$result = $app->updateThing($thingid, $name, $description, $regCode,
			$attachment, $commentsopendate, $commentsclosedate, $critiquesclosedate,
			$includeingrading, $errors);

		// Check to see if the new thing attempt succeeded
		if ($result == TRUE) {

			// Redirect the user to the list page on success
		  header("Location: thing.php?thingid=$thingid&updatething=success");
			exit();

		}

	}

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
		<h2>Edit Topic</h2>

		<?php include('include/messages.php'); ?>
    <div class="editthing">
			<form enctype="multipart/form-data" method="post" action="editthing.php">
				<fieldset>
					<legend class="visuallyhidden">Edit Discussion Form:</legend>
					<label for="name" class="visuallyhidden">Discussion Title</label>
					<input type="text" name="name" id="name" placeholder="Enter a discussion title" value="<?php echo $name; ?>"  required="required"/>
					<br/>

					<label for="description" class="visuallyhidden">Discussion Description</label>
					<textarea name="description" id="description" rows="10" placeholder="Enter a description" required="required"><?php echo $description; ?></textarea>
					<br/>

					<label for="commentsopendate">Comments Open Date:</label>
					<br/>
					<input type="datetime-local" name="commentsopendate" id="commentsopendate" value="<?php echo $commentsopendate; ?>" required="required" />
					<br/>

					<label for="commentsclosedate">Comments Close Date:</label>
					<br/>
					<input type="datetime-local" name="commentsclosedate" id="commentsclosedate" value="<?php echo $commentsclosedate; ?>" required="required" />
					<br/>

					<label for="critiquesclosedate">Critiques Close Date:</label>
					<br/>
					<input type="datetime-local" name="critiquesclosedate" id="critiquesclosedate" value="<?php echo $critiquesclosedate; ?>" required="required" />
					<br/>

					<label for="includeingrading">Include in grade calculation:</label>
					<br/>
					<input type="checkbox" name="includeingrading" id="includeingrading" value="yes" <?php if ($includeingrading) { ?>checked<?php } ?> />
					<br/>

					<label for="code">Course:</label>
					<br/>
					<select id="code" name="code">
						<?php foreach($justCodes as $code) { ?>
						<option value="<?php echo $code; ?>" <?php if ($code == $myCode) { echo "selected='selected'"; } ?> ><?php echo $code; ?></option>
						<?php } ?>
					</select>
					<br/>
					<input type="submit" name="save" value="Update Discussion" />
					<input type="hidden" name="update" value="discussion" />
					<input type="hidden" name="thingid" value="<?php echo $thingid; ?>" />
				</fieldset>
			</form>
		</div>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
