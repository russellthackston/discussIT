<?php

// Assume the user is not logged in and not an admin
$isadmin = FALSE;
$loggedin = FALSE;
$commentsAlert = FALSE;
$critiquesAlert = FALSE;

// Check for user session
$user = $app->getSessionUser($errors);
if ($user != NULL) {
	 
	$loggedinuserid = $user["userid"];
	$loggedinusername = $user["username"];
	$loggedinuserregistrationcode = $user['registrationcode'];

	// Check to see if the user really is logged in and really is an admin
	if ($loggedinuserid != NULL) {
		$loggedin = TRUE;
		$isadmin = $app->isAdmin($errors, $loggedinuserid);
		$progressReport = $app->getProgressReport($loggedinuserid, $loggedinuserregistrationcode, $errors);
		if ($progressReport != NULL) {
			if ($progressReport['numberofuncommentedtopics'] > 0) {
				$commentsAlert = TRUE;
			}
			if ($progressReport['numberofuncritiquedcomments'] > 0) {
				$critiquesAlert = TRUE;
			}
		}
	}

} else {
	
	$loggedinuserid = NULL;

}


?>
	<header>
		<nav aria-labelledby="mainmenulabel">
			<h2 id="mainmenulabel" class="visuallyhidden">Main Menu</h2>
			<ul>
			<?php if (!$loggedin) { ?>
				<li><a href="index.php">Home</a></li>
				<li><a href="register.php">Register</a></li>
				<li><a href="login.php">Login</a></li>
			<?php } ?>
			<?php if ($loggedin) { ?>
				<li class="<?php if ($critiquesAlert) { echo " critiquesAlert "; } else if ($commentsAlert) { echo " commentsAlert "; } ?>">
					<a href="list.php">Topics</a>
				</li>
				<li><a href="editprofile.php">Profile</a></li>
				<?php if ($isadmin) { ?>
					<li><a href="admin.php">Admin</a></li>
				<?php } else { ?>
					<li><a href="fileviewer.php?file=include/help.txt">Help</a></li>
				<?php } ?>
				<li><a href="logout.php" class="no-barba">Logout</a></li>
			<?php } ?>
			</ul>
		</nav>
		<div class="clear"></div>
	</header>
