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

// Local variables
$commentingClosed = FALSE;

// Check for admin user
$user = $app->getSessionUser($errors);
$loggedinuserid = $user["userid"];
$isadmin = FALSE;
$text = "";

// Check to see if the user really is logged in and really is an admin
if ($loggedinuserid != NULL) {
	$isadmin = $app->isAdmin($errors, $loggedinuserid);
}

// If the page/thing is being loaded for display
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	// Get the thing id from the URL
	$thingid = $_GET['thingid'];
	
	// Attempt to obtain the thing
	$thing = $app->getThing($thingid, $errors);
	
	// If there were no errors getting the thing, try to get the comments
	if (sizeof($errors) == 0) {
	
		// Attempt to obtain the comments for this thing
		$thing = $app->getThing($thingid, $errors);
		
		// If the thing loaded successfully, load the associated comments
		if (isset($thing)) {
			$comments = $app->getComments($thing['thingid'], $errors);
			$commentingClosed = $app->isCommentingClosed($thing['thingid'], $errors);
		}
	
	} else {
		// Redirect the user to the things page on error
		header("Location: list.php?error=nothing");
		exit();
	}
	
	// Check for url flag indicating that a new comment was created.
	if (isset($_GET["newcomment"]) && $_GET["newcomment"] == "success") {
		$message = "New comment successfully created.";
	}

	// Check for url flag indicating that a new comment was created.
	if (isset($_GET["report"])) {
		$message = "Report submitted.";
		$commentid = $_GET["report"];
	}
}
// If someone is attempting to create a new comment, process their request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Pull the comment text from the <form> POST
	$text = $_POST['comment'];

	// Pull the thing ID from the form
	$thingid = $_POST['thingid'];
	$attachment = $_FILES['attachment'];

	// Get the details of the thing from the database
	$thing = $app->getThing($thingid, $errors);

	// Attempt to create the new comment and capture the result flag
	$result = $app->addComment($text, $thingid, $attachment, $errors);

	// Check to see if the new comment attempt succeeded
	if ($result == TRUE) {

		// Redirect the user to the login page on success
	    header("Location: thing.php?newcomment=success&thingid=" . $thingid);
		exit();

	} else {
		if (isset($thing)) {
			$comments = $app->getComments($thing['thingid'], $errors);
		}
	}

}

$hasCommented = FALSE;
$comments = $app->getComments($thingid, $errors);
foreach ($comments as $comment) {
	if ($comment['commentuserid'] == $loggedinuserid) {
		$hasCommented = TRUE;
		break;
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
		<?php include('include/messages.php'); ?>
		
		<div class="thingcontainer">
			<p style="display: none;">Thing loaded</p>
			<p class="thingtitle"><?php echo $thing['thingname']; ?></p>
			<p class="thingdescription"><?php echo nl2br($thing['thingdescription']); ?></p>
			<p class="thingtagline"><?php echo $thing['username']; ?> on <?php echo $thing['thingcreated']; ?></p>
			<?php if ($thing['filename'] != NULL) { ?>
				<p class="thingattachment"><a href="attachments/<?php echo $thing['thingattachmentid'] . '-' . $thing['filename']; ?>" class="no-barba"><?php echo $thing['filename']; ?></a></p>
			<?php } ?>
		</div>
		<ul class="comments" data-size="<?php echo sizeof($comments); ?>">
			<?php 
				$index = 0;
				foreach ($comments as $comment) { 
					$index++;
			?>
			<li data-index="<?php echo $index; ?>" data-contents="comment">
				<div class="comment">
					<?php echo nl2br(htmlentities($comment['commenttext'])); ?>
				</div>
				<div class="commentdetails <?php if ($comment['anon'] != TRUE) { echo "mine"; } ?>">
					<span class="author">
						-- <?php echo $comment['username']; ?> on <?php echo $comment['commentposted']; ?>
					</span>
					<span class="report">
						<?php if (isset($commentid)) { ?>
						Reported
						<?php } else { ?>
						<a href="report.php?commentid=<?php echo $comment['commentid']; ?>&thingid=<?php echo $thingid; ?>">Report</a>
						<?php } ?>
					</span>
				</div>
				<?php if ($comment['filename'] != NULL) { ?>
					<p class="commentattachment">
						<a href="attachments/<?php echo $comment['attachmentid'] . '-' . $comment['filename']; ?>" class="no-barba"><?php echo $comment['filename']; ?></a>
					</p>
				<?php } ?>
	
				<?php if (!$comment['voted'] && !$isadmin) { ?>
					
					<div class="votingform" id="votingid-<?php echo $comment['commentid']; ?>" data-commentid="<?php echo $comment['commentid']; ?>">
						<input type="button" name="upvote" onclick="up(this);" value="Contributes" class="up" data-commentid="<?php echo $comment['commentid']; ?>">
						<input type="button" name="showdown" onclick="showdown(this);" value="Does not contribute" class="down" data-commentid="<?php echo $comment['commentid']; ?>">
					</div>
	
					<div class="downvoteform" id="downvotediv-<?php echo $comment['commentid']; ?>" style="display: none;">
						<textarea class="downvotetext" name="downvotetext-<?php echo $comment['commentid']; ?>" rows="4" placeholder="Briefly explain why this does not contribue to the discussion. Required." id="downvotetext-<?php echo $comment['commentid']; ?>"></textarea><br>
						<input type="button" name="downvote" onclick="down(this);" value="Save" class="down" data-commentid="<?php echo $comment['commentid']; ?>">
					</div>
	
					<div class="processing" id="voteprocessing-<?php echo $comment['commentid']; ?>" style="display: none;">Sending critique to server. Please wait...</div>
	
					<div class="processing" id="voteprocessed-<?php echo $comment['commentid']; ?>" style="display: none;">Your critique has been saved.</div>
	
				<?php } ?>
	
				<div id="votes-<?php echo $comment['commentid']; ?>" class="voting <?php if ($comment['up'] > 0 && $comment['up'] == sizeof($comment['critiques'])) { echo "perfect"; } ?> <?php if ($comment['up'] == 0) { echo "unrated"; } ?>" <?php if (!$isadmin && !$comment['voted']) { echo " style='display: none;'"; } ?>>
					<?php 
						if (sizeof($comment['critiques']) > 0) {
							echo $comment['up'] . " out of " . sizeof($comment['critiques']) . " users thought this comment contributed to the discussion.";
						} else {
							echo "No critiques";
						}
					?>
				</div>
				
				<div class="critiques" id="critiques-<?php echo $comment['commentid']; ?>" <?php if (!$isadmin && !$comment['voted']) { echo " style='display: none;'"; } ?>>
					<ul class="critiques">
					<?php 
						$critiques = $comment['critiques'];
						foreach ($critiques as $critique) { 
							if (!empty($critique['critiquetext'])) { ?>
								<li class="critique <?php if ($critique['addstodiscussion'] == 0) { echo "down"; } else { echo "up"; } ?>">
									<span class="critiquetext"><?php echo $critique['critiquetext']; ?></span>
									<span class="critiqueauthor">-- <?php echo $critique['username']; ?></span>
								</li>
								
					<?php 	} 
						} ?>	
					</ul>
				</div>
	
			</li>
			<?php } ?>
		</ul>
		<?php if (!$commentingClosed && !$hasCommented && !$isadmin) { ?>
			<div class="newcomment">
				<form enctype="multipart/form-data" method="post" action="thing.php" name="newcommentform" id="newcommentform">
					<fieldset>
						<legend class="visuallyhidden">New Comment Form:</legend>
						<label for="comment"class="visuallyhidden">Comment</label>
						<textarea name="comment" id="comment" rows="6" placeholder="Add a comment" required="required"><?php echo $text; ?></textarea>
						<br/>
						
						<label for="attachment">Add an image, PDF, etc.</label>
						<input id="attachment" name="attachment" type="file">
						<br/>
						
						<input type="hidden" name="thingid" value="<?php echo $thingid; ?>" />
						<input type="submit" name="start" value="Add comment" id="submitcomment"/>
					</fieldset>
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
	<script>
		setupCommentForm();
		activateNewCommentForm();
	</script>
</body>
</html>
