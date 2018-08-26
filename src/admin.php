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

// Attempt to obtain the list of users
$users = $app->getUsers($errors);

// Get a list of registration codes
$regcodes = $app->getRegistrationCodes($errors);

if (isset($_GET['downloadprogress'])) {
    $progressReports = $app->getAllProgressReports($errors);

    $app->outputCSV($progressReports['progress']);

    exit();

}

if (isset($_GET['hijack'])) {
    $userid = $_GET['hijack'];
    $app->impersonate($userid, $errors);
}

// If someone is adding a new attachment type
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_POST['attachmenttype'] == "add") {

        $name = $_POST['name'];;
        $extension = $_POST['extension'];;

        $attachmenttypeid = $app->newAttachmentType($name, $extension, $errors);

        if ($attachmenttypeid != NULL) {
            $messages[] = "New attachment type added";
        }

    }

}

// Attempt to obtain the list of users
$attachmentTypes = $app->getAttachmentTypes($errors);

$reports = $app->getReports($errors);

?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
    <?php include 'include/header.php'; ?>
    <div id="barba-wrapper">
        <div class="barba-container">
            <main id="wrapper">
                <h2>Admin Functions</h2>
                <?php include 'include/messages.php'; ?>
                <h3>User List</h3>
                <label for="regcodefilter">Filter:</label>
                <select name="regcodefilter" id="regcodefilter" onchange="filterByRegCode()">
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
                <a href="admin.php?downloadprogress" class="no-barba">Download progress report</a>
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
                        <input type="submit" name="addattachmenttype" value="Add type" />
                    </form>
                </div>
                <h3>User Reports</h3>
                <ul class="reports">
                    <?php foreach($reports as $report) { ?>
                        <?php $comment = $app->getComment($report['userreportcommentid'], $errors); ?>
                        <li><a href="thing.php?thingid=<?php echo $comment['commentthingid']; ?>#<?php echo $comment['commentid']; ?>"><?php echo $report['userreportid']; ?></a> -- <?php echo $report['userreportreasoncodeid']; ?> -- <?php echo $report['moreinfo']; ?><br>Comment: <?php echo $comment['commenttext']; ?><br>Posted by: <?php echo $comment['username']; ?></li>
                    <?php } ?>
                    <?php if (sizeof($reports) == 0) { ?>
                        <li>No user report found</li>
                    <?php } ?>
                </ul>
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
