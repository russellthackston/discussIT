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
		$progressReport = $app->getProgressReport($loggedinuserid, NULL, NULL, $loggedinuserregistrationcode, $errors);
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
			<ul id="menuList"> 
			<?php
				//If the user is not logged in, show Home, Register and Login	
				if (!$loggedin) { ?>
					<li> 
						<a id="navHome" href="index.php" class="screenMenu <?php if ($currentPage == "navHome"){echo "navActive";}?>">Home</a>
						<a href="index.php" class="phoneMenu menuHidden">
							<i class="material-icons menuIcons" style="margin-top: -3px; font-size:37px">home</i>
						</a>
					</li>
					
					<li>
						<a id="navRegister" href="register.php" class="screenMenu <?php if ($currentPage == "navRegister"){echo "navActive";}?>">Register</a>
						<a href="register.php" class="phoneMenu menuHidden">
							<i class="fa fa-user-o menuIcons" style="margin-top: 3px; font-size:27px"></i>
							<i class="fa fa-plus menuIcons" style="margin-left: -30px; font-size:10px"></i>
						</a>
					</li>
					
					<li>
						<a id="navLogin" href="login.php" class="screenMenu <?php if ($currentPage == "navLogin"){echo "navActive";}?>">Login</a>
						<a href="login.php" class="phoneMenu menuHidden">
							<i class="material-icons menuIcons" style="margin-top: -1px; font-size:36px">power_settings_new</i>
						</a>
					</li>
			<?php } ?>
			<?php if ($loggedin) { 
					//If the user is logged in, show Topics and Profile
			?>
				<li class="<?php if ($critiquesAlert) { echo " critiquesAlert "; } else if ($commentsAlert) { echo " commentsAlert "; } ?>">
					<a id="navTopics" href="list.php" class="screenMenu <?php if ($currentPage == "navTopics"){echo "navActive";}?>">Topics</a>
					<a href="list.php" class="phoneMenu menuHidden">
						<i class="fa fa-comments-o menuIcons" style="margin-top: -3px; font-size:34px"></i>
					</a>
				</li>
				
				<li> 
					<a id="navCal" href="calendar.php" class="screenMenu <?php if ($currentPage == "navCal"){echo "navActive";}?>">Calendar</a>
					<a href="calendar.php" class="phoneMenu menuHidden">
						<i class="fa fa-calendar menuIcons" style="margin-top: 2px; font-size:27px"></i>
					</a>
				</li>	
						
				<li>
					<a id="navProfile" href="editprofile.php" class="screenMenu <?php if ($currentPage == "navProfile"){echo "navActive";}?>">Profile</a>
					<a href="editprofile.php" class="phoneMenu menuHidden">
						<i class="fa fa-user-o menuIcons" style="margin-top: 2px; font-size:26px"></i>
					</a>


				<?php if ($isadmin) { 
						//If the user is logged in and is an admin, show Admin
				?>
						<li>
							<a id="navAdmin" href="admin.php" class="screenMenu <?php if ($currentPage == "navAdmin"){echo "navActive";}?>">Admin</a>
							<a href="admin.php" class="phoneMenu menuHidden">
								<i class="fa fa-gears menuIcons" style="font-size:30px"></i>
								</a>						
						</li>

				<?php } else { 
						//If the user is logged in and not the admin, Help, Rollcall
				?>
							<li>
								<a id="navHelp" href="fileviewer.php?file=include/help.txt" class="screenMenu <?php if ($currentPage == "navHelp"){echo "navActive";}?>">Help</a>
								<a href="fileviewer.php?file=include/help.txt" class="phoneMenu menuHidden">
									<i class="material-icons menuIcons" style="font-size:32px">help_outline</i>
								</a>						
							</li>
			  		
					  		<li >
					  			<a id="navRollCall" href="#" onclick='return rollcall(this);' class="screenMenu">Roll Call</a>
					  			<a id="present" href="#" onclick='return rollcall(this);' class="phoneMenu menuHidden">
									<i class="fa fa-hand-stop-o menuIcons" style="margin-top: 4px; font-size:26px"></i>
								</a>								
					  		</li>
				<?php }	?>

				
				
				<?php  	

								
					$registrationCodes = $app->getUserRegistrations($loggedinuserid, $errors);
									
					if (count($registrationCodes) >1){
				?>
						<li>
						
							<a id="navSwitch" href="switchregcode.php" class="screenMenu no-barba">Switch</a>
							<a href="switchregcode.php" class="phoneMenu no-barba menuHidden">
								<i class="fa fa-random menuIcons" style="margin-top: 3px; font-size:30px"></i>
							</a>						
						
						</li>
					<?php } ?>  		

				
<!-- 			If the user is logged in, show Logout					 -->
				<li>
					<a href="logout.php" class="screenMenu no-barba">Logout</a>
					<a href="logout.php" class="phoneMenu no-barba menuHidden">
						<i class="material-icons menuIcons" style="margin-top: -1px; font-size:36px">power_settings_new</i>
					</a>						
					
				</li>
				
				
			<?php } ?>
			
						
				<li>
					<a href="#" class="phoneMenu hotdog" onclick="return toggleHotdog();">
						<i class="fa fa-angle-double-left hotdogcontrol" style="font-size:46px"></i>
					</a>
					
				</li>		
	

			</ul>
		</nav>
		<div class="clear"></div>
		<span id="logoContainer" class='<?php if (time() > strtotime("-7day", strtotime("2018-10-31")) && time() < strtotime("2018-11-01")){echo "halloween";}?>'>
			<a href="list.php">
				<span id="discuss">discuss</span><span id="it">IT</span>
			</a>
			<span id="title"><?php if (isset($app->getSessionUser($errors)["description"])){echo $app->getSessionUser($errors)["description"];}?>
		</span>		

</header>
