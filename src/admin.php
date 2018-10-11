<?php

//set currentPage
$currentPage = "navAdmin";

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

$tabs = array("userreports", "rollcall", "reports", "userlist", "studentlist", "attachmenttypes");
$loggedInUser = $app->getSessionUser($errors);
$regcodes = $app->getRegistrationCodes($errors);

if (isset($_GET['hijack'])) {
    $userid = $_GET['hijack'];
    if ($app->impersonate($userid, $errors) == FALSE) {
	    $message = "No active session";
    }
}

if (isset($_GET['downloadprogress'])) {
    $progressReports = $app->getAllProgressReports($errors);

    $app->outputCSV($progressReports);
    exit();

}

// If someone is adding a new attachment type
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['tab']) && in_array($_POST['tab'], $tabs)) {
        $tab = $_POST['tab'];
    }

    if (isset($_POST['tabaction']) && $_POST['tabaction'] == 'downloadattendance') {
        $attendancedate = $_POST['attendancedate'];
        $attendancedata = $app->getAttendanceData($attendancedate, $errors);
        $app->outputCSV($attendancedata);
        exit();
    }

    if (isset($_POST['attachmenttype']) && $_POST['attachmenttype'] == "add") {

        $name = $_POST['name'];
        $extension = $_POST['extension'];

        $attachmenttypeid = $app->newAttachmentType($name, $extension, $errors);

        if ($attachmenttypeid != NULL) {
            $messages[] = "New attachment type added";
        }

    }

}

if (isset($_GET['snapshot']) && $_GET['snapshot'] == 'roll') {
    $app->snapshotRollcall($loggedInUser['registrationcode'], $errors);
    $message = "Snapshot recorded";
}

if (!isset($tab) && isset($_GET['tab']) && in_array($_GET['tab'], $tabs)) {
    $tab = $_GET['tab'];
} else {
    $tab = "userreports";
}


if ($tab == 'userlist') {
    $users = $app->getUsers($errors);
}

if ($tab == 'attachmenttypes') {
     $attachmentTypes = $app->getAttachmentTypes($errors);
}

if ($tab == 'userreports') {
    $reports = $app->getReports($errors);
}

if ($tab == 'reports') {
    $attendancedates = $app->getAttendanceDates($errors);
    $histogram = $app->getGradeHistograms($errors);
}

if ($tab == 'studentlist') {
    $students = $app->getStudents($errors);
}

if ($tab == 'rollcall') {

    $roll = $app->getRollcall($loggedInUser['registrationcode'], $errors);
    $present = 0;
    $notpresent = 0;
    foreach($roll as $r) {
        if ($r['present'] == 1) {
            $present = $present + 1;
        } else {
            $notpresent = $notpresent + 1;
        }
    }
    $rollmessage = $present . " student present and " . $notpresent . " absent.";
}

?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
    <?php include 'include/header.php'; ?>
    <div id="barba-wrapper">
        <div class="barba-container" data-id="navAdmin">
            <main id="wrapper">
                <h2>Admin Functions</h2>
                <?php include 'include/messages.php'; ?>
                <div id="tabs">
                    <ul>
                        <li <?php if ($tab == 'userreports') { ?>class="active"<?php } ?>><a href="admin.php?tab=userreports">User Reports</a></li>
                        <li <?php if ($tab == 'rollcall') { ?>class="active"<?php } ?>><a href="admin.php?tab=rollcall">Roll call</a></li>
                        <li <?php if ($tab == 'reports') { ?>class="active"<?php } ?>><a href="admin.php?tab=reports">Reports</a></li>
                        <li <?php if ($tab == 'userlist') { ?>class="active"<?php } ?>><a href="admin.php?tab=userlist">User List</a></li>
                        <li <?php if ($tab == 'studentlist') { ?>class="active"<?php } ?>><a href="admin.php?tab=studentlist">Student List</a></li>
                        <li <?php if ($tab == 'attachmenttypes') { ?>class="active"<?php } ?>><a href="admin.php?tab=attachmenttypes">Attachment Types</a></li>
                    </ul>
                </div>
                <?php if ($tab == 'userreports') { ?>
                <div id="userreports">
                    <h3>User Reports</h3>
                    <ul class="reports">
                        <?php foreach($reports as $report) { ?>
                            <?php $comment = $app->getComment($report['userreportcommentid'], $errors); ?>
                            <li><a href="thing.php?thingid=<?php echo $comment['commentthingid']; ?>#comment-<?php echo $comment['commentid']; ?>"><?php echo $report['userreportid']; ?></a> -- <?php echo $report['userreportreasoncodeid']; ?> -- <?php echo $report['moreinfo']; ?><br>Comment: <?php echo $comment['commenttext']; ?><br>Posted by: <?php echo $comment['username']; ?></li>
                        <?php } ?>
                        <?php if (sizeof($reports) == 0) { ?>
                            <li>No user report found</li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if ($tab == 'rollcall') { ?>
                <div id="rollcall">
                	<div><?php echo $rollmessage; ?></div>
                    <a href="admin.php?tab=rollcall&snapshot=roll">[Take Snapshot]</a>
            		<table id="rollcalltable">
            			<tr>
            				<th>Name</th>
            			<?php foreach($roll as $student) { ?>
            				<tr class="<?php if ($student['present'] == 0) { echo "notpresent"; } ?>">
            					<td><?php echo $student['studentname']; ?></td>
            				</tr>
            			<?php } ?>
            		</table>
                </div>
                <?php } ?>
                <?php if ($tab == 'reports') { ?>
                <div id="reports">
                    <div>
                        <a href="admin.php?tab=reports&downloadprogress" class="no-barba">Download progress report</a>
                    </div>
                    <div>
                        <form action="admin.php" method="post" name="attendanceform">
                        <select id="attendancedate" name="attendancedate" autocomplete="off">
        					<?php foreach($attendancedates as $date) { ?>
        					<option value="<?php echo $date['rolltaken']; ?>"><?php echo $date['rolltaken']; ?></option>
        					<?php } ?>
        				</select>
                        <input type="hidden" name="tab" value="reports">
                        <input type="hidden" name="tabaction" value="downloadattendance">
                        <input type="submit" name="attendance" value="Download attendance" id="attendance">
                    </form>
                    </div>

					<span class="chart">
						<?php
							$chartheight = max(
								$histogram['commentinggradedecimal']['A'],
								$histogram['commentinggradedecimal']['B'],
								$histogram['commentinggradedecimal']['C'],
								$histogram['commentinggradedecimal']['D'],
								$histogram['commentinggradedecimal']['F']);
						?>
	                    <span class="histogram">
						<?php foreach($histogram['commentinggradedecimal'] as $grade=>$count) { ?>
							<?php $height = strval(($count / $chartheight) * 100); ?>
							<span style="height: <?php echo $height; ?>%" class="histobar" data-label="<?php echo $grade; ?>" data-count="<?php echo $count; ?>">
								<span class="visuallyhidden">
									<?php echo $grade; ?>:<?php echo $count['commentinggradedecimal']; ?>
								</span>
							</span>
						<?php } ?>
	                    </span>
						<h4>Commenting</h4>
					</span>

					<span class="chart">
						<?php
							$chartheight = max(
								$histogram['critiquinggradedecimal']['A'],
								$histogram['critiquinggradedecimal']['B'],
								$histogram['critiquinggradedecimal']['C'],
								$histogram['critiquinggradedecimal']['D'],
								$histogram['critiquinggradedecimal']['F']);
						?>
	                    <span class="histogram">
						<?php foreach($histogram['critiquinggradedecimal'] as $grade=>$count) { ?>
							<?php $height = strval(($count / $chartheight) * 100); ?>
							<span style="height: <?php echo $height; ?>%" class="histobar" data-label="<?php echo $grade; ?>" data-count="<?php echo $count; ?>">
								<span class="visuallyhidden">
									<?php echo $grade; ?>:<?php echo $count['critiquinggradedecimal']; ?>
								</span>
							</span>
						<?php } ?>
	                    </span>
						<h4>Critiquing</h4>
					</span>

					<span class="chart">
						<?php
							$chartheight = max(
								$histogram['commentqualitydecimal']['A'],
								$histogram['commentqualitydecimal']['B'],
								$histogram['commentqualitydecimal']['C'],
								$histogram['commentqualitydecimal']['D'],
								$histogram['commentqualitydecimal']['F']);
						?>
	                    <span class="histogram">
						<?php foreach($histogram['commentqualitydecimal'] as $grade=>$count) { ?>
							<?php $height = strval(($count / $chartheight) * 100); ?>
							<span style="height: <?php echo $height; ?>%" class="histobar" data-label="<?php echo $grade; ?>" data-count="<?php echo $count; ?>">
								<span class="visuallyhidden">
									<?php echo $grade; ?>:<?php echo $count['commentqualitydecimal']; ?>
								</span>
							</span>
						<?php } ?>
	                    </span>
						<h4>Commenting Quality</h4>
					</span>

                </div>
                <?php } ?>
                <?php if ($tab == 'studentlist') { ?>
                <div id="studentlist">
                    <h3>Student List</h3>
                    <table class="students">
                        <tr>
                            <th>Delete</th>
                            <th>Name</th>
                            <th>Registered?</th>
                            <th>Student ID</th>
                            <th>Registrations</th>
                        </tr>
                        <tr id="newstudent">
                            <td>&nbsp;</td>
                            <td><input name="newstudentname" id="newstudentname"></td>
                            <td>--</td>
                            <td><input name="newstudentid" id="newstudentid"></td>
                            <td>
                                <input name="newregcode" id="newregcode">
                                <input type="button" value="Add" onclick="addStudent();">
                            </td>
                        </tr>
                        <?php foreach($students as $student) { ?>
                            <tr class="student" id="student-row-<?php echo $student['studentid']; ?>">
                                <td>
                                    <input type="button" id="student-del-button-<?php echo $student['studentid']; ?>" data-studentid="<?php echo $student['studentid']; ?>" value="Delete" onclick="deleteStudent(this);">
                                </td>
                                <td>
                                    <span onclick="showEditStudent(this);" id="student-name-<?php echo $student['studentid']; ?>" data-studentid="<?php echo $student['studentid']; ?>">
                                        <?php echo $student['studentname']; ?>
                                    </span>
                                    <input style="display: none" type="text" name="studentname" id="student-name-textfield-<?php echo $student['studentid']; ?>" value="<?php echo $student['studentname']; ?>">
                                    <input style="display: none" type="button" id="student-edit-button-<?php echo $student['studentid']; ?>" data-studentid="<?php echo $student['studentid']; ?>" value="Save" onclick="saveStudent(this)">
                                </td>
                                <td>
                                    <?php if($student['regcount'] > 0) { echo "Yes"; } else { echo "No"; } ?>
                                </td>
                                <td>
                                    <?php echo $student['studentid']; ?>
                                </td>
                                <td>
                                    <?php echo $student['regcodes']; ?>
                                </td>
                            </li>
                        <?php } ?>
                    </table>
                </div>
                <?php } ?>
                <?php if ($tab == 'userlist') { ?>
                <div id="userlist">
                    <h3>User List</h3>
                    <label for="regcodefilter">Filter:</label>
                    <select name="regcodefilter" id="regcodefilter" onchange="filterByRegCode()" autocomplete="off">
                        <option value="nofilter">All members</option>
                        <?php foreach ($regcodes as $regcode) { ?>
                            <option value="<?php echo $regcode['registrationcode']; ?>"><?php echo $regcode['registrationcode']; ?></option>
                        <?php } ?>
                    </select>

                    <ul class="users">
                        <?php
                        foreach($users as $user) {
                            if ($user['studentname'] == NULL) {
                                $user['studentname'] = $user['username'];
                            }
                            ?>
                            <li class="user">
                                <a href="editprofile.php?userid=<?php echo $user['userid']; ?>" data-regcodes="<?php echo $user['regcodes']; ?>"><?php echo $user['studentname']; ?></a>
                                <a href="admin.php?hijack=<?php echo $user['userid']; ?>" class="commentdesc no-barba">[Impersonate]</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if ($tab == 'attachmenttypes') { ?>
                <div id="attachmenttypes">
                    <h3>Valid Attachment Types</h3>
                    <ul class="attachmenttypes">
                        <?php foreach($attachmentTypes as $attachmentType) { ?>
                            <li><?php echo $attachmentType['name']; ?> [<?php echo $attachmentType['extension']; ?>]</li>
                        <?php } ?>
                        <?php if (sizeof($attachmentTypes) == 0) { ?>
                            <li>No attachment types found in the database</li>
                        <?php } ?>
                    </ul>

                    <h4>Add Attachment Type</h4>
                    <div class="newattachmenttype">
                        <form enctype="multipart/form-data" method="post" action="admin.php">
                            <input id="name" name="name" type="text" placeholder="Name" required="required">
                            <br/>
                            <input id="extension" name="extension" type="text" placeholder="Extension" required="required">
                            <br/>
                            <input type="hidden" name="attachmenttype" value="add" />
                            <input type="hidden" name="tab" value="attachmenttypes" />
                            <input type="submit" name="addattachmenttype" value="Add type" />
                        </form>
                    </div>
                </div>
                <?php } ?>
            </main>
        </div>
    </div>
    <?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
