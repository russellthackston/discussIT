<?php

//set currentPage
$currentPage = "navAdmin";

// Import the application classes
require_once('include/classes.php');
require_once('include/histogram.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in admin user since this page is "isadmin" protected
// NOTE: passing optional parameter TRUE which indicates the user must be an admin
$app->protectPage($errors, TRUE);

// List of tabs on this page
$tabs = array("dashboard", "userreports", "rollcall", "reports", "userlist", "studentlist", "attachmenttypes", "notes");
$loggedInUser = $app->getSessionUser($errors);
$regcodes = $app->getRegistrationCodes($errors);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	// Process the impersonate student request
	if (isset($_GET['hijack'])) {
	    $userid = $_GET['hijack'];
	    if ($app->impersonate($userid, $errors) == FALSE) {
		    $message = "No active session";
	    }
	}

	// Process the download progress report request
	if (isset($_GET['downloadprogress'])) {
	    $progressReports = $app->getAllProgressReports($errors);
	
	    $app->outputCSV($progressReports);
	    exit();
	
	}

	// Process the capture snapshot request
	if (isset($_GET['snapshot']) && $_GET['snapshot'] == 'roll') {
	    $app->snapshotRollcall($loggedInUser['registrationcode'], $errors);
	    $message = "Snapshot recorded";
	}
	
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['tab']) && in_array($_POST['tab'], $tabs)) {
        $tab = $_POST['tab'];
    }

	// Process the download attendance request
    if (isset($_POST['tabaction']) && $_POST['tabaction'] == 'downloadattendance') {
        $attendancedate = $_POST['attendancedate'];
        $attendancedata = $app->getAttendanceData($attendancedate, $errors);
        $app->outputCSV($attendancedata);
        exit();
    }

	// Process the add new attachment type request
    if (isset($_POST['attachmenttype']) && $_POST['attachmenttype'] == "add") {

        $name = $_POST['name'];
        $extension = $_POST['extension'];

        $attachmenttypeid = $app->newAttachmentType($name, $extension, $errors);

        if ($attachmenttypeid != NULL) {
            $messages[] = "New attachment type added";
        }

    }

}

// If no tab selection has been set, default to the dashboard
if (!isset($tab) && isset($_GET['tab']) && in_array($_GET['tab'], $tabs)) {
    $tab = $_GET['tab'];
} else {
    $tab = "dashboard";
}


if ($tab == 'dashboard') {
    $histograms = $app->getGradeCharts($errors);
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
}

if ($tab == 'studentlist') {
    $students = $app->getStudents($errors);
}

if ($tab == 'notes') {
    $notes = $app->getNotes($loggedInUser['registrationcode'], $errors);
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
                        <li <?php if ($tab == 'dashboard') { ?>class="active"<?php } ?>><a href="admin.php?tab=dashboard">Dashboard</a></li>
                        <li <?php if ($tab == 'userreports') { ?>class="active"<?php } ?>><a href="admin.php?tab=userreports">User Reports</a></li>
                        <li <?php if ($tab == 'rollcall') { ?>class="active"<?php } ?>><a href="admin.php?tab=rollcall">Roll call</a></li>
                        <li <?php if ($tab == 'reports') { ?>class="active"<?php } ?>><a href="admin.php?tab=reports">Reports</a></li>
                        <li <?php if ($tab == 'userlist') { ?>class="active"<?php } ?>><a href="admin.php?tab=userlist">Users</a></li>
                        <li <?php if ($tab == 'studentlist') { ?>class="active"<?php } ?>><a href="admin.php?tab=studentlist">Students</a></li>
                        <li <?php if ($tab == 'attachmenttypes') { ?>class="active"<?php } ?>><a href="admin.php?tab=attachmenttypes">Attachments</a></li>
                        <li <?php if ($tab == 'notes') { ?>class="active"<?php } ?>><a href="admin.php?tab=notes">Notes</a></li>
                    </ul>
                </div>
                <?php if ($tab == 'dashboard') { ?>
                <div id="dashboard">
					<?php
						renderHistogram($histograms['commentinggradedecimal'], 'Commenting');
						renderHistogram($histograms['critiquinggradedecimal'], 'Critiquing');
						renderHistogram($histograms['commentqualitydecimal'], 'Commenting Quality');
 					?>
                </div>
                <?php } ?>
                <?php if ($tab == 'userreports') { ?>
                <div id="userreports">
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
                </div>
                <?php } ?>
                <?php if ($tab == 'studentlist') { ?>
                <div id="studentlist">
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
                    <label for="regcodefilter">Filter:</label>
                    <select name="regcodefilter" id="regcodefilter" onchange="filterByRegCode()" autocomplete="off">
                        <option value="nofilter">All members</option>
                        <?php foreach ($regcodes as $regcode) { ?>
                            <option value="<?php echo $regcode->registrationcode; ?>"><?php echo $regcode->registrationcode; ?></option>
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
                <?php if ($tab == 'notes') { ?>
                <div id="notes">
        			<?php foreach($notes as $note) { ?>
        				<div class="instructornote">
        					<?php echo $note->text; ?>
        				</div>
        			<?php } ?>
                </div>
                <?php } ?>
            </main>
        </div>
    </div>
    <?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
