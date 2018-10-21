<?php
//set currentPage
$currentPage = "navTopics";
	
// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in user since this page is protected
$app->protectPage($errors);

// Get the logged in user
$loggedInUser = $app->getSessionUser($errors);
$loggedinuserregistrationcode = $loggedInUser['registrationcode'];
$isAdmin = $app->isAdmin($errors, $loggedInUser['userid']);

// Declare local variables
$search = "";
$name = "";
$description = "";
$commentsopendate = "";
$commentsclosedate = "";
$critiquesclosedate = "";
$includeingrading = FALSE;

// Attempt to obtain the list of things
$things = $app->getThings($errors);

// Get the start of the next discussion
if (sizeof($things) == 0) {
	$nextthing = $app->getNextThing($errors);
}

// Check for url flag indicating that there was a reg code switch
if (isset($_GET["regcode"]) && $_GET["regcode"] == "switch") {
	$message = "Course switched.";
}

// Check for url flag indicating that there was a "no thing" error.
if (isset($_GET["error"]) && $_GET["error"] == "nothing") {
	$errors[] = "Things not found.";
}

// Check for url flag indicating that a new thing was created.
if (isset($_GET["newthing"]) && $_GET["newthing"] == "success") {
	$message = "Thing successfully created.";
}

// Get the my registration code and the full list
$myCode = $loggedInUser['registrationcode'];
$allCodes = $app->getRegistrationCodes($errors);
$justCodes = RegistrationCode::justCodes($allCodes);

$starthour = date("H");
$startminute = date("i");
$endhour = date("H");
$endminute = date("i");

foreach ($allCodes as $code) {
	if ($code['registrationcode'] == $myCode) {
		$starthour = date("H", strtotime($code['starttime']));
		$startminute = date("i", strtotime($code['starttime']));
		$endhour = date("H", strtotime($code['endtime']));
		$endminute = date("i", strtotime($code['endtime']));
		break;
	}
}

// On initial load, set the default values for date/time fields
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$now = new DateTime();

	$now->setTime($endhour, $endminute);
	$commentsopendate = $now->format("Y-m-d\TH:i");

	$now->setTime($starthour, $startminute);
	$commentsclosedate = $now->format("Y-m-d\TH:i");
	$critiquesclosedate = $now->format("Y-m-d\TH:i");
}

// If someone is attempting to create a new thing, the process the request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (isset($_POST['filter']) && $_POST['filter'] == 'discussions') {

		// Pull the filter text from the <form> POST
		$search = $_POST['search'];

	} else if (isset($_POST['create']) && $_POST['create'] == 'discussion') {

		// Pull the title and thing text from the <form> POST
		$name = $_POST['name'];
		$description = $_POST['description'];
		$regCode = $_POST['code'];
		$attachment = $_FILES['attachment'];
		$commentsopendate = $_POST['commentsopendate'];
		$commentsclosedate = $_POST['commentsclosedate'];
		$critiquesclosedate = $_POST['critiquesclosedate'];
		if (isset($_POST['includeingrading']) && $_POST['includeingrading'] == 'yes') {
			$includeingrading = TRUE;
		}

		// Attempt to create the new thing and capture the result flag
		$result = $app->addThing($name, $description, $regCode, $attachment,
			$commentsopendate, $commentsclosedate, $critiquesclosedate,
			$includeingrading, $errors);

		// Check to see if the new thing attempt succeeded
		if ($result == TRUE) {

			// Redirect the user to the login page on success
		    header("Location: list.php?newthing=success");
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
	<div class="barba-container" data-id="navTopics">
	<main id="wrapper">
		<?php include('include/messages.php'); ?>

		<!--div class="search">
			<form action="list.php" method="post">
				<label for="search">Filter:</label>
				<input type="text" id="search" name="search" value="<?php echo $search; ?>" />
				<input type="submit" name="apply" value="Apply" />
				<input type="hidden" name="filter" value="discussions" />
			</form>
		</div-->
		<ul class="things">
			<?php if (sizeof($things) == 0) { ?>
			<li>No discussions found. Next discussion starts <?php echo $nextthing['commentsopendate']; ?> </li>
			<?php } ?>
			<?php foreach ($things as $thing) { ?>
				<?php
					$excerpt = substr(strip_tags($thing['thingdescription']), 0, 125);
					$excerpt = substr($excerpt, 0, strrpos($excerpt, " "));
					$excerpt = '"' . $excerpt . '..."';
				?>
				<?php $sentiment = $thing['total'] == 0 ? 0 : round(($thing['up'] / $thing['total']) * 100); ?>
				<li class="<?php if (!$thing['hascommented']) { echo "uncommented"; } else { echo "commented"; } ?>" data-thingid="<?php echo $thing['thingid']; ?>">
					<a href="thing.php?thingid=<?php echo $thing['thingid']; ?>" data-thingid="<?php echo $thing['thingid']; ?>"><?php echo $thing['thingname']; ?></a>
					<br>
					<span class="commentdesc"><?php echo $excerpt; ?></span>
					<br>
					<span class="commentcount"><?php echo sizeof($thing['comments']); ?> comments</span>
					<?php if ($sentiment != 0) { ?>
						<span class="sentiment">(<?php echo $sentiment ?>% positive)</span>
					<?php } else { ?>
						<span class="sentiment">No critiques, yet</span>
					<?php } ?>
					<?php if ($isAdmin) { ?>
					<br>
					<span class="note">Comments open: <?php echo $thing['commentsopendate']; ?></span>
					<?php } ?>
					<br>
					<span class="note">Comments close: <?php echo $thing['commentsclosedate']; ?></span>
					<br>
					<span class="note">Critiques close: <?php echo $thing['critiquesclosedate']; ?></span>
					<br>
					<span class="note">
						<?php if (!$thing['includeingrading']) { ?>
		          <b>[Ungraded topic]</b>
						<?php } else { ?>
							<b>[Graded Topic]</b>
						<?php } ?>
						<?php if ($isAdmin) { ?>
		          	<a href="editthing.php?thingid=<?php echo $thing['thingid']; ?>">[Edit]</a>
						<?php } ?>
					</span>

				</li>
			<?php } ?>

		</ul>

		<?php if ($isAdmin) { ?>
		<h3>New Discussion</h3>
		<div class="newthing">
			<form enctype="multipart/form-data" method="post" action="list.php">
				<fieldset>
					<legend class="visuallyhidden">New Discussion Form:</legend>
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
					<select id="code" name="code" autocomplete="off">
						<?php foreach($justCodes as $code) { ?>
						<option value="<?php echo $code; ?>" <?php if ($code == $myCode) { echo "selected='selected'"; } ?> ><?php echo $code; ?></option>
						<?php } ?>
					</select>
					<br/>
					<label for="attachment">Add an image, PDF, etc.</label>
					<br/>
					<input id="attachment" name="attachment" type="file">
					<br/>
					<input type="submit" name="save" value="Start New Discussion" />
					<input type="hidden" name="create" value="discussion" />
				</fieldset>
			</form>
		</div>
		<?php } ?>

	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
