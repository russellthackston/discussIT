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

if (isset($_GET['commentid'])) {

	$commentid = $_GET['commentid'];
	$thingid = $_GET['thingid'];

} else if (isset($_POST['commentid'])) {

	$commentid = $_POST['commentid'];
	$thingid = $_POST['thingid'];

}

$moreinfo = "";
$comment = $app->getComment($commentid, $errors);
$codes = $app->getReportCodes($errors);
$reportid = NULL;
$reasonid = -1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	if (isset($_POST['reasonid']) && $_POST['reasonid'] != "") {
		$reasonid = $_POST['reasonid'];
		
		// Check for more info needed flag
		$moreInfoNeeded = FALSE;
		foreach($codes as $code) {
			if ($code['reportcodeid'] == $reasonid && $code['moreinfoneeded'] == "1") {
				$moreInfoNeeded = TRUE;
			}
		}
		if ($moreInfoNeeded && (!isset($_POST['moreinfo-' . $reasonid]) || $_POST['moreinfo-' . $reasonid] == "")) {
			$errors[] = "Please provide a brief description of the issue";
		} else {
			$moreinfo = $_POST['moreinfo-' . $reasonid];
			$reportid = $app->submitReport($commentid, $reasonid, $moreinfo, $errors);
			if ($reportid != NULL) {
				header("Location: thing.php?thingid=$thingid&report=$commentid");
			}
		}
	} else {
		$errors[] = "Please select a reason for your report";
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
		<h2>Report comment</h2>
		<?php include 'include/messages.php'; ?>
		<div>
			<strong>Comment:</strong>
			<br>
			<?php echo $comment['commenttext']; ?>
		</div>
		<?php if ($reportid == NULL) { ?>
		<div>
			<form action="report.php" method="post">
				<input type="hidden" name="commentid" id="commentid" value="<?php echo $commentid; ?>">
				<input type="hidden" name="thingid" id="thingid" value="<?php echo $thingid; ?>">
				<?php foreach($codes as $code) { ?>
					<input type="radio" name="reasonid" value="<?php echo $code['reportcodeid']; ?>" data-moreinfoneeded="<?php echo $code['moreinfoneeded']; ?>" <?php if ($reasonid == $code['reportcodeid']) { echo " checked='checked' "; } ?>><?php echo $code['reportcodename']; ?></option>
					<?php if ($code['moreinfoneeded'] == 1) { ?>
						<input type="text" placeholder="Describe the issue" maxlength="255" <?php if ($reasonid != $code['reportcodeid']) { echo ' disabled="disabled" '; } ?>name="moreinfo-<?php echo $code['reportcodeid']; ?>" id="moreinfoneeded-<?php echo $code['reportcodeid']; ?>">
					<?php } ?>
					<br/>
				<?php } ?>
				<input type="submit" value="Submit report" />
			</form>
		</div>
		<?php } ?>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/site.js"></script>
	<script src="js/barba.js"></script>
	<script src="js/mybarba.js"></script>
	<script>setupMoreInfo();</script>
</body>
</html>
