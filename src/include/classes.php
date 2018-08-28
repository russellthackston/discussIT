<?php

/** Global variable or variable from include/require **/
/** @var Type $debugMessages */
/** @var Type $servername */
/** @var Type $serverdb */
/** @var Type $serverusername */
/** @var Type $serverpassword */

class Application {

    private $codeversion = 2;

    public function setup() {

        // Check to see if the client has a cookie called "debug" with a value of "true"
        // If it does, turn on error reporting
        if ($_COOKIE['debug'] == "true") {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        // check for first-time setup of database
        if (!$this->databaseReady()) {
            header('Location: setup.php');
            exit();
        }

        $this->checkForDatabaseUpdates();
    }

    function databaseReady() {

        $result = FALSE;

        if (!file_exists('include/credentials.php')) {
            return FALSE;
        }

        // Connect to the database
        $dbh = $this->getConnection();

        // Construct a SQL statement to perform the insert operation
        $sql = "SHOW TABLES LIKE 'users'";


        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $result = TRUE;
        }

        $dbh = NULL;

        return $result;

    }

    function checkForDatabaseUpdates() {

        // Declare an errors array
        $errors = [];

        // Connect to the database
        $dbh = $this->getConnection();

        // Construct a SQL statement to perform the insert operation
        $sql = "SELECT dbversion FROM dbversion LIMIT 1";


        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $dbversion = $stmt->fetch(PDO::FETCH_ASSOC)['dbversion'];

        // Compare to the code version
        if ($dbversion < $this->codeversion) {
            $ver = $this->codeversion;
            $this->auditlog("checkForDatabaseUpdates", "Database needs updating from $dbversion to $ver");
            $this->updateDatabase($dbh, $dbversion, $this->codeversion);
        }

        $dbh = NULL;

    }

    // Updates the database structure to match this code base using the SQL files found in the 'sql' folder.
    function updateDatabase($dbh, $dbversion, $codeversion) {
        for ($i = $dbversion; $i < $codeversion; $i++) {
            $num = $i + 1;
            $sqlfilename = "sql/update-$num.sql";
            if (file_exists($sqlfilename)) {
                $sql = file_get_contents($sqlfilename);
                $dbh->exec($sql);
            }
        }

        $sql = "UPDATE dbversion SET dbversion = $codeversion";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
    }

    // Writes a message to the debug message array for printing in the footer.
    public function debug($message) {
        global $debugMessages;
        $debugMessages[] = $message;
    }

    // Sets the website in maintenance mode (mode) or production mode (false)
    public function setMaintenanceMode($mode) {
        if ($mode == TRUE) {

        } else if ($mode == FALSE) {

        }
    }

    // Returns true or false depending on the current state of the maintenance mode flag in the database
    function isMaintenanceMode() {
        return FALSE;
    }

    // Creates a database connection
    protected function getConnection() {

        // Import the database credentials
        require('credentials.php');

        // Create the connection
        try {
            $dbh = new PDO("mysql:host=$servername;dbname=$serverdb", $serverusername, $serverpassword);
        } catch (PDOException $e) {
            print "Error connecting to the database.";
            $this->debug($e);
            die();
        }

        // Return the newly created connection
        return $dbh;
    }

    public function auditlog($context, $message, $priority = 0, $userid = NULL){

        // Declare an errors array
        $errors = [];

        // Connect to the database
        $dbh = $this->getConnection();

        // If a user is logged in, get their userid
        if ($userid == NULL) {

            $user = $this->getSessionUser($errors, TRUE);
            if ($user != NULL) {
                $userid = $user["userid"];
            }

        }

        $ipaddress = $_SERVER["REMOTE_ADDR"];

        if (is_array($message)){
            $message = implode( ",", $message);
        }

        // Construct a SQL statement to perform the insert operation
        $sql = "INSERT INTO auditlog (context, message, logdate, ipaddress, userid) " .
        "VALUES (:context, :message, NOW(), :ipaddress, :userid)";


        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":context", $context);
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":ipaddress", $ipaddress);
        $stmt->bindParam(":userid", $userid);
        $stmt->execute();
        $dbh = NULL;

    }

    // Returns a list of registration codes from the database
    public function getRegistrationCodes(&$errors) {
        // Connect to the database
        $dbh = $this->getConnection();

        // Declare the list of codes;
        $codes = NULL;

        // Query the database
        $sql = "SELECT registrationcode, starttime, endtime FROM registrationcodes";
        $stmt = $dbh->prepare($sql);
        $result = $stmt->execute();
        if ($result === FALSE) {

            $errors[] = "Error loading registration codes";
            $this->debug($stmt->errorInfo());

        } else {

            $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        }

        // Return the codes
        return $codes;
    }

    protected function validateUsername($username, &$errors) {
        if (empty($username)) {
            $errors[] = "Missing username";
        } else if (strlen(trim($username)) < 3) {
            $errors[] = "Username must be at least 3 characters";
        } else if (strpos($username, "@")) {
            $errors[] = "Username may not contain an '@' sign";
        }
    }

    protected function validatePassword($password, &$errors) {
        if (empty($password)) {
            $errors[] = "Missing password";
        } else if (strlen(trim($password)) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
    }

    protected function validateEmail($email, &$errors) {
        if (empty($email)) {
            $errors[] = "Missing email";
        } else if (substr(strtolower(trim($email)), -20) != "@georgiasouthern.edu"
        && substr(strtolower(trim($email)), -13) != "@thackston.me") {
            // Verify it's a Georgia Southern email address
            $errors[] = "Not a Georgia Southern email address";
        }
    }

    protected function validateStudentID($studentid, &$errors) {
        if (empty($studentid)) {
            $errors[] = "Missing student ID";
        } else if (substr($studentid, 0 , 1) != "9") {
            $errors[] = "Student ID should begin with a '9'";
        } else if (strlen(trim($studentid)) < 9) {
            $errors[] = "Student ID must be at least 9 characters";
        }
    }

    protected function validateCommentText($commenttext, &$errors) {
        if (empty($commenttext)) {
            $errors[] = "Missing comment text";
        } else if (strlen(trim($commenttext)) < 50) {
            $errors[] = "Comment text is too short (50 characters min).";
        } else if (strlen(trim($commenttext)) > 1000) {
            $errors[] = "Comment text is too long (1000 characters max).";
        }
    }

    // Send an email to validate the address
    protected function sendValidationEmail($userid, $email, &$errors) {

        // Connect to the database
        $dbh = $this->getConnection();

        $this->auditlog("sendValidationEmail", "Sending message to $email");

        $validationid = bin2hex(random_bytes(16));

        // Construct a SQL statement to perform the insert operation
        $sql = "INSERT INTO emailvalidation (emailvalidationid, userid, email, emailsent) " .
        "VALUES (:emailvalidationid, :userid, :email, NOW())";


        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":emailvalidationid", $validationid);
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":email", $email);
        $result = $stmt->execute();
        if ($result === FALSE) {
            $errors[] = "An unexpected error occurred sending the validation email";
            $this->debug($stmt->errorInfo());
            $this->auditlog("register error", $stmt->errorInfo());
        } else {

            $this->auditlog("sendValidationEmail", "Sending message to $email");

            // Send reset email
            $pageLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $pageLink = preg_replace("~(\w+)\.php~", "login.php", $pageLink);
            $to      = $email;
            $subject = 'Confirm your email address';
            $message = "A request has been made to create an account at https://russellthackston.me for this email address. ".
            "If you did not make this request, please ignore this message. No other action is necessary. ".
            "To confirm this address, please click the following link: $pageLink?id=$validationid";
            $headers = 'From: webmaster@russellthackston.me' . "\r\n" .
            'Reply-To: webmaster@russellthackston.me' . "\r\n";

            mail($to, $subject, $message, $headers);

            $this->auditlog("sendValidationEmail", "Message sent to $email");

        }

        // Close the connection
        $dbh = NULL;

    }

    // Send an email to validate the address
    public function processEmailValidation($validationid, &$errors) {

        $success = FALSE;

        // Connect to the database
        $dbh = $this->getConnection();

        $this->auditlog("processEmailValidation", "Received: $validationid");

        // Construct a SQL statement to perform the insert operation
        $sql = "SELECT userid FROM emailvalidation WHERE emailvalidationid = :emailvalidationid";


        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":emailvalidationid", $validationid);
        $result = $stmt->execute();

        if ($result === FALSE) {

            $errors[] = "An unexpected error occurred processing your email validation request";
            $this->debug($stmt->errorInfo());
            $this->auditlog("processEmailValidation error", $stmt->errorInfo());

        } else {

            if ($stmt->rowCount() != 1) {

                $errors[] = "That does not appear to be a valid request";
                $this->debug($stmt->errorInfo());
                $this->auditlog("processEmailValidation", "Invalid request: $validationid");


            } else {

                $userid = $stmt->fetch(PDO::FETCH_ASSOC)['userid'];

                // Construct a SQL statement to perform the insert operation
                $sql = "DELETE FROM emailvalidation WHERE emailvalidationid = :emailvalidationid";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":emailvalidationid", $validationid);
                $result = $stmt->execute();

                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred processing your email validation request";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("processEmailValidation error", $stmt->errorInfo());

                } else if ($stmt->rowCount() == 1) {

                    $this->auditlog("processEmailValidation", "Email address validated: $validationid");

                    // Construct a SQL statement to perform the insert operation
                    $sql = "UPDATE users SET emailvalidated = 1 WHERE userid = :userid";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":userid", $userid);
                    $result = $stmt->execute();

                    $success = TRUE;

                } else {

                    $errors[] = "That does not appear to be a valid request";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("processEmailValidation", "Invalid request: $validationid");

                }

            }

        }


        // Close the connection
        $dbh = NULL;

        return $success;

    }

    // Registers a new user
    public function register($username, $password, $email, $registrationcode, $studentid, &$errors) {

        $this->auditlog("register", "attempt: $username, $email, $studentid, $registrationcode");

        // Validate the user input
        $this->validateUsername($username, $errors);
        $this->validatePassword($password, $errors);
        $this->validateEmail($email, $errors);
        $this->validateStudentID($studentid, $errors);
        if (empty($registrationcode)) {
            $errors[] = "Missing registration code";
        }

        // Only try to insert the data into the database if there are no validation errors
        if (sizeof($errors) == 0) {

            // Connect to the database
            $dbh = $this->getConnection();

            // Check the registration codes table for the code provided
            $goodcode = FALSE;
            $sql = "SELECT COUNT(*) AS codecount FROM registrationcodes WHERE LOWER(registrationcode) = LOWER(:code)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':code', $registrationcode);
            $result = $stmt->execute();
            if ($result) {
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row["codecount"] == 1) {
                        $goodcode = TRUE;
                    }
                }
            } else {
                $this->debug($stmt->errorInfo());
            }

            // If the code is bad, then return error
            if (!$goodcode) {
                $errors[] = "Bad registration code";
                $this->auditlog("register", "bad registration code: $registrationcode");

            } else {

                // Hash the user's password
                $passwordhash = password_hash($password, PASSWORD_DEFAULT);

                // Create a new user ID
                $userid = bin2hex(random_bytes(16));

                // Construct a SQL statement to perform the insert operation
                $sql = "INSERT INTO users (userid, username, passwordhash, email, studentid) " .
                "VALUES (:userid, :username, :passwordhash, :email, :studentid)";

                // Run the SQL insert and capture the result code
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':userid', $userid);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':passwordhash', $passwordhash);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':studentid', $studentid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $arr = $stmt->errorInfo();
                    $this->debug($stmt->errorInfo());

                    // Check for duplicate userid/username/email
                    if ($arr[1] == 1062) {
                        if (substr($arr[2], -7, 6) == "userid") {
                            $errors[] = "An unexpected registration error occurred. Please try again in a few minutes.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("register error", $stmt->errorInfo());

                        } else if (substr($arr[2], -9, 8) == "username") {
                            $errors[] = "That username is not available.";
                            $this->auditlog("register", "duplicate username: $username");
                        } else if (substr($arr[2], -6, 5) == "email") {
                            $errors[] = "That email has already been registered.";
                            $this->auditlog("register", "duplicate email: $email");
                        } else {
                            $errors[] = "An unexpected error occurred.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("register error", $stmt->errorInfo());
                        }
                    } else {
                        $errors[] = "An unexpected error occurred.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("register error", $stmt->errorInfo());
                    }
                } else {
                    // Construct a SQL statement to perform the insert operation
                    $sql = "INSERT INTO userregistrations (userid, registrationcode) " .
                    "VALUES (:userid, :registrationcode)";

                    // Run the SQL insert and capture the result code
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(':userid', $userid);
                    $stmt->bindParam(':registrationcode', $registrationcode);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $arr = $stmt->errorInfo();
                        $this->debug($stmt->errorInfo());

                        if ($arr[1] == 1062) {
                            $errors[] = "User already registered for course.";
                            $this->auditlog("register", "duplicate course registration: $userid, $registrationcode");
                        }

                    } else {

                        $this->auditlog("register", "success: $userid, $username, $email");
                        $this->sendValidationEmail($userid, $email, $errors);

                    }

                }

            }

            // Close the connection
            $dbh = NULL;

        } else {
            $this->auditlog("register validation error", $errors);
        }

        // Return TRUE if there are no errors, otherwise return FALSE
        if (sizeof($errors) == 0){
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // Registers a new user
    public function addUserRegistration($userid, $registrationcode, &$errors) {

        $this->auditlog("addRegistrationCode", "attempt: $userid, $registrationcode");

        // Validate the user input
        if (empty($registrationcode)) {
            $errors[] = "Missing registration code";
        }

        // Only try to insert the data into the database if there are no validation errors
        if (sizeof($errors) == 0) {

            // Connect to the database
            $dbh = $this->getConnection();

            // Check the registration codes table for the code provided
            $goodcode = FALSE;
            $sql = "SELECT COUNT(*) AS codecount FROM registrationcodes WHERE LOWER(registrationcode) = LOWER(:code)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':code', $registrationcode);
            $result = $stmt->execute();
            if ($result) {
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row["codecount"] == 1) {
                        $goodcode = TRUE;
                    }
                }
            } else {
                $this->debug($stmt->errorInfo());
            }

            // If the code is bad, then return error
            if (!$goodcode) {
                $errors[] = "Bad registration code";
                $this->auditlog("addRegistrationCode", "bad registration code: $registrationcode");

            } else {

                // Construct a SQL statement to perform the insert operation
                $sql = "INSERT INTO userregistrations (userid, registrationcode) " .
                "VALUES (:userid, :registrationcode)";

                // Run the SQL insert and capture the result code
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':userid', $userid);
                $stmt->bindParam(':registrationcode', $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $arr = $stmt->errorInfo();
                    $this->debug($stmt->errorInfo());

                    // Check for duplicate userid/username/email
                    if ($arr[1] == 1062) {
                        if (strpos($arr[2], "Duplicate entry") !== FALSE && strpos($arr[2], "for key 'PRIMARY'")) {
                            $errors[] = "Already registered.";
                            $this->auditlog("addRegistrationCode", "already registered for $registrationcode");
                        }
                    } else {
                        $errors[] = "An unexpected error occurred.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("addRegistrationCode error", $stmt->errorInfo());
                    }

                }

            }

            // Close the connection
            $dbh = NULL;

        } else {
            $this->auditlog("addRegistrationCode validation error", $errors);
        }

        // Return TRUE if there are no errors, otherwise return FALSE
        if (sizeof($errors) == 0){
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // Validates a provided username or email address and sends a password reset email
    public function passwordReset($usernameOrEmail, &$errors) {

        // Check for a valid username/email
        if (empty($usernameOrEmail)) {
            $errors[] = "Missing username/email";
            $this->auditlog("session", "missing username");
        }

        // Only proceed if there are no validation errors
        if (sizeof($errors) == 0) {

            // Connect to the database
            $dbh = $this->getConnection();

            // Construct a SQL statement to perform the insert operation
            $sql = "SELECT email, userid FROM users WHERE username = :username OR email = :email";


            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(":username", $usernameOrEmail);
            $stmt->bindParam(":email", $usernameOrEmail);
            $result = $stmt->execute();

            // If the query did not run successfully, add an error message to the list
            if ($result === FALSE) {

                $this->auditlog("passwordReset error", $stmt->errorInfo());
                $errors[] = "An unexpected error occurred saving your request to the database.";
                $this->debug($stmt->errorInfo());

            } else {

                if ($stmt->rowCount() == 1) {

                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $passwordresetid = bin2hex(random_bytes(16));
                    $userid = $row['userid'];
                    $email = $row['email'];

                    // Construct a SQL statement to perform the insert operation
                    $sql = "INSERT INTO passwordreset (passwordresetid, userid, email, expires) " .
                    "VALUES (:passwordresetid, :userid, :email, DATE_ADD(NOW(), INTERVAL 1 HOUR))";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":passwordresetid", $passwordresetid);
                    $stmt->bindParam(":userid", $userid);
                    $stmt->bindParam(":email", $email);
                    $result = $stmt->execute();

                    $this->auditlog("passwordReset", "Sending message to $email");

                    // Send reset email
                    $pageLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $pageLink = preg_replace("~(\w+)\.php~", "password.php", $pageLink);
                    $to      = $email;
                    $subject = 'Password reset';
                    $message = "A password reset request for this account has been submitted at https://russellthackston.me. ".
                        "If you did not make this request, please ignore this message. No other action is necessary. ".
                        "To reset your password, please click the following link: $pageLink?id=$passwordresetid";
                        $headers = 'From: webmaster@russellthackston.me' . "\r\n" .
                        'Reply-To: webmaster@russellthackston.me' . "\r\n";

                        mail($to, $subject, $message, $headers);

                        $this->auditlog("passwordReset", "Message sent to $email");


                    } else {

                        $this->auditlog("passwordReset", "Bad request for $usernameOrEmail");

                    }

                }

                // Close the connection
                $dbh = NULL;

            }

        }

        // Removes the specified password reset entry in the database, as well as any expired ones
        // Does not retrun errors, as the user should not be informed of these problems
        protected function clearPasswordResetRecords($passwordresetid) {

            $dbh = $this->getConnection();

            // Construct a SQL statement to perform the insert operation
            $sql = "DELETE FROM passwordreset WHERE passwordresetid = :passwordresetid OR expires < NOW()";


            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(":passwordresetid", $passwordresetid);
            $stmt->execute();

            // Close the connection
            $dbh = NULL;

        }

        // Validates a provided username or email address and sends a password reset email
        public function updatePassword($password, $passwordresetid, &$errors) {

            $this->auditlog("updatePassword", "request received: $passwordresetid");

            // Check for a valid username/email
            $this->validatePassword($password, $errors);
            if (empty($passwordresetid)) {
                $errors[] = "Missing passwordrequestid";
            }

            // Only proceed if there are no validation errors
            if (sizeof($errors) == 0) {

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to perform the insert operation
                $sql = "SELECT userid FROM passwordreset WHERE passwordresetid = :passwordresetid AND expires > NOW()";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":passwordresetid", $passwordresetid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred updating your password.";
                    $this->auditlog("updatePassword", $stmt->errorInfo());
                    $this->debug($stmt->errorInfo());

                } else if ($stmt->rowCount() == 1) {

                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $userid = $row['userid'];
                    $this->updateUserPassword($userid, $password, $errors);
                    $this->clearPasswordResetRecords($passwordresetid);

                } else {

                    $this->auditlog("updatePassword", "Bad request id: $passwordresetid");

                }

            }

        }

        public function getUserRegistrations($userid, &$errors) {

            // Assume an empty list of regs
            $regs = array();

            // Connect to the database
            $dbh = $this->getConnection();


            $sql = "SELECT registrationcode FROM userregistrations WHERE userid = :userid";


            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':userid', $userid);
            $result = $stmt->execute();

            // If the query did not run successfully, add an error message to the list
            if ($result === FALSE) {

                $errors[] = "An unexpected error occurred getting the regs list.";
                $this->debug($stmt->errorInfo());
                $this->auditlog("getUserRegistrations error", $stmt->errorInfo());


            } else {


                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $regs = array_column($rows, 'registrationcode');
                $this->auditlog("getUserRegistrations", "success");

            }

            // Close the connection
            $dbh = NULL;

            // Return the list of users
            return $regs;
        }

        // Creates a new session in the database for the specified user
        public function newSession($userid, &$errors, $registrationcode = NULL) {

            // Check for a valid userid
            if (empty($userid)) {
                $errors[] = "Missing userid";
                $this->auditlog("session", "missing userid");
            }

            // Only try to query the data into the database if there are no validation errors
            if (sizeof($errors) == 0) {

                if ($registrationcode == NULL) {
                    $regs = $this->getUserRegistrations($userid, $errors);
                    $reg = $regs[0];
                    $this->auditlog("session", "logging in user with first reg code $reg");
                    $registrationcode = $regs[0];
                }

                // Create a new session ID
                $sessionid = bin2hex(random_bytes(25));

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to perform the insert operation
                $sql = "INSERT INTO usersessions (usersessionid, userid, expires, registrationcode) " .
                "VALUES (:sessionid, :userid, DATE_ADD(NOW(), INTERVAL 7 DAY), :registrationcode)";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":sessionid", $sessionid);
                $stmt->bindParam(":userid", $userid);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("new session error", $stmt->errorInfo());
                    return NULL;

                } else {

                    // Store the session ID as a cookie in the browser
                    setcookie('sessionid', $sessionid, time()+60*60*24*30);
                    $this->auditlog("session", "new session id: $sessionid for user = $userid");

                    // Return the session ID
                    return $sessionid;

                }

            }

        }

        // Creates a new session in the database for the specified user
        public function updateSession($userid, $usersessionid, $registrationcode, &$errors) {

            // Check for a valid userid
            if (empty($usersessionid)) {
                $errors[] = "Missing usersessionid";
                $this->auditlog("update session", "missing usersessionid");
            }

            // Only try to query the data into the database if there are no validation errors
            if (sizeof($errors) == 0) {

                if ($registrationcode == NULL) {
                    $this->auditlog("update session", "updating session with first registration code");
                    $regs = $this->getUserRegistrations($userid, $errors);
                    $registrationcode = $regs[0]['registrationcode'];
                } else {
                    $this->auditlog("update session", "updating session with reg code = $registrationcode");
                }

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to perform the insert operation
                $sql = "UPDATE usersessions SET registrationcode = :registrationcode WHERE usersessionid = :usersessionid";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $stmt->bindParam(":usersessionid", $usersessionid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("update session error", $stmt->errorInfo());
                    return FALSE;

                }

            }

            return TRUE;

        }

        // Retrieves an existing session from the database for the specified user
        public function getSessionUser(&$errors, $suppressLog=FALSE) {

            // Get the session id cookie from the browser
            $sessionid = NULL;
            $user = NULL;

            // Check for a valid session ID
            if (isset($_COOKIE['sessionid'])) {

                $sessionid = $_COOKIE['sessionid'];

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to perform the insert operation
                $sql = "SELECT usersessionid, usersessions.userid, email, username, usersessions.registrationcode, isadmin " .
                "FROM usersessions " .
                "LEFT JOIN users on usersessions.userid = users.userid " .
                "WHERE usersessionid = :sessionid AND expires > now()";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":sessionid", $sessionid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred";
                    $this->debug($stmt->errorInfo());

                    // In order to prevent recursive calling of audit log function
                    if (!$suppressLog){
                        $this->auditlog("session error", $stmt->errorInfo());
                    }

                } else {

                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                }

                // Close the connection
                $dbh = NULL;

            }

            return $user;

        }

        // Retrieves the most recent session from the database for the specified user
        public function impersonate($userid, &$errors) {

            // Array of sessions
            $sessionid = null;

            // Connect to the database
            $dbh = $this->getConnection();

            // Construct a SQL statement to perform the insert operation
            $sql = "SELECT usersessionid " .
            "FROM usersessions " .
            "WHERE userid = :userid AND expires > now() " .
            "ORDER BY expires " .
            "LIMIT 1";


            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(":userid", $userid);
            $result = $stmt->execute();

            // If the query did not run successfully, add an error message to the list
            if ($result === FALSE) {

                $errors[] = "An unexpected error occurred";
                $this->debug($stmt->errorInfo());

            } else {

                if ($stmt->rowCount() > 0) {

                    $sessionid = $stmt->fetch(PDO::FETCH_ASSOC)['usersessionid'];

                    // Hijack the session
                    if ($sessionid != null) {

                        setcookie('impersonate', '1', time()+60*60*24*30);
                        setcookie('sessionid', $sessionid, time()+60*60*24*30);
                        $this->auditlog("session", "session hijacked: $sessionid for user = $userid");
                        header("Location: list.php");
                        exit();

                    }

                }

            }

            // Close the connection
            $dbh = NULL;

        }

        // Retrieves an existing session from the database for the specified user
        public function isAdmin(&$errors, $userid) {

            $isAdmin = FALSE;

            // Check for a valid user ID
            if (empty($userid)) {
                $errors[] = "Missing userid";
            } else {

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to perform the insert operation
                $sql = "SELECT isadmin FROM users WHERE userid = :userid";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":userid", $userid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("isadmin error", $stmt->errorInfo());

                } else {

                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $isAdmin = ($row['isadmin'] == "1");

                }

                // Close the connection
                $dbh = NULL;

            }
            return $isAdmin;
        }

        // Logs in an existing user and will return the $errors array listing any errors encountered
        public function login($username, $password, &$errors) {

            $this->debug("Login attempted");
            $this->auditlog("login", "attempt: $username, password length = ".strlen($password));


            // Validate the user input
            $this->validateUsername($username, $errors);
            $this->validatePassword($password, $errors);

            // Only try to query the data into the database if there are no validation errors
            if (sizeof($errors) == 0) {

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to perform the insert operation
                $sql = "SELECT userid, passwordhash, emailvalidated FROM users " .
                "WHERE username = :username";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":username", $username);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("login error", $stmt->errorInfo());


                    // If the query did not return any rows, add an error message for bad username/password
                } else if ($stmt->rowCount() == 0) {

                    $errors[] = "Bad username/password combination";
                    $this->auditlog("login", "bad username: $username");


                    // If the query ran successfully and we got back a row, then the login succeeded
                } else {

                    // Get the row from the result
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Check the password
                    if (!password_verify($password, $row['passwordhash'])) {

                        $errors[] = "Bad username/password combination";
                        $this->auditlog("login", "bad password: password length = ".strlen($password));

                    } else if ($row['emailvalidated'] == 0) {

                        $errors[] = "Login error. Email not validated. Please check your inbox and/or spam folder.";

                    } else {

                        // Create a new session for this user ID in the database
                        $userid = $row['userid'];
                        $this->newSession($userid, $errors);
                        $this->auditlog("login", "success: $username, $userid");

                    }

                }

                // Close the connection
                $dbh = NULL;

            } else {
                $this->auditlog("login validation error", $errors);
            }

            // Return TRUE if there are no errors, otherwise return FALSE
            if (sizeof($errors) == 0){
                return TRUE;
            } else {
                return FALSE;
            }
        }

        // Logs out the current user based on session ID
        public function logout() {

            $sessionid = $_COOKIE['sessionid'];
            $impersonate = FALSE;
            if (isset($_COOKIE['impersonate']) && $_COOKIE['impersonate'] == '1') {
                $impersonate = TRUE;
            }

            // Only try to query the data into the database if there are no validation errors
            if (!empty($sessionid)) {

                if (!$impersonate) {

                    // Connect to the database
                    $dbh = $this->getConnection();

                    // Construct a SQL statement to perform the insert operation
                    $sql = "DELETE FROM usersessions WHERE usersessionid = :sessionid OR expires < now()";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":sessionid", $sessionid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $this->debug($stmt->errorInfo());
                        $this->auditlog("logout error", $stmt->errorInfo());


                        // If the query ran successfully, then the logout succeeded
                    } else {

                        // Clear the session ID cookie
                        setcookie('sessionid', '', time()-3600);
                        $this->auditlog("logout", "successful: $sessionid");

                    }

                    // Close the connection
                    $dbh = NULL;

                } else {

                    // Clear the cookie but do not actually delete the user's session, since we're not really the user
                    setcookie('sessionid', '', time()-3600);
                    setcookie('impersonate', '', time()-3600);
                    $this->auditlog("logout", "(impersonate) successful: $sessionid");

                }

            }

        }

        // Checks for logged in user and redirects to login if not found with "page=protected" indicator in URL.
        public function protectPage(&$errors, $isAdmin = FALSE) {

            // Get the user ID from the session record
            $user = $this->getSessionUser($errors);

            if ($user == NULL) {
                // Redirect the user to the login page
                $this->auditlog("protect page", "no user");
                header("Location: login.php?page=protected");
                exit();
            }

            // Get the user's ID
            $userid = $user["userid"];

            // If there is no user ID in the session, then the user is not logged in
            if(empty($userid)) {

                // Redirect the user to the login page
                $this->auditlog("protect page", "no userid");
                header("Location: login.php?page=protected");
                exit();

            } else if ($isAdmin)  {

                // Get the isAdmin flag from the database
                $isAdminDB = $this->isAdmin($errors, $userid);

                if (!$isAdminDB) {

                    // Redirect the user to the home page
                    $this->auditlog("protect page", "not admin");
                    header("Location: index.php?page=protectedAdmin");
                    exit();

                }

            }

        }

        public function getNextThing(&$errors) {

            // Assume an empty list of things
            $thing = NULL;

            // Get the logged in user
            $loggedinuser = $this->getSessionUser($errors);
            $registrationcode = $loggedinuser["registrationcode"];

            // Connect to the database
            $dbh = $this->getConnection();


            $sql = "SELECT thingid " .
            "FROM things LEFT JOIN users ON things.thinguserid = users.userid " .
            "WHERE thingregistrationcode = :registrationcode1 " .
            "AND commentsopendate IN (" .
                "SELECT MAX(commentsopendate) FROM things LEFT JOIN users ON things.thinguserid = users.userid " .
                "WHERE thingregistrationcode = :registrationcode2)";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode1", $registrationcode);
                $stmt->bindParam(":registrationcode2", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getNextThing error", $stmt->errorInfo());

                    // If the query ran successfully, then get the list of things
                } else {


                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row) {
                        $thingid = $row['thingid'];
                        $thing = $this->getThing($thingid, $errors);
                    }

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of things
                return $thing;

            }

            // Get a list of all things from the database and will return the $errors array listing any errors encountered
            public function getCalendar(&$errors) {

                // Assume an empty list of things
                $things = array();

                // Get the logged in user
                $loggedinuser = $this->getSessionUser($errors);
                $registrationcode = $loggedinuser["registrationcode"];

                // Connect to the database
                $dbh = $this->getConnection();


                $sql = "SELECT thingname, commentsopendate, commentsclosedate, critiquesclosedate, " .
                "(commentsopendate > now()) as notopen, " .
                "(commentsclosedate < now()) as commentsclosed, " .
                "(critiquesclosedate < now()) as critiquesclosed " .
                "FROM things " .
                "WHERE thingregistrationcode = :registrationcode " .
                "ORDER BY things.commentsopendate ASC";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getCalendar error", $stmt->errorInfo());

                    // If the query ran successfully, then get the list of things
                } else {


                    $things = $stmt->fetchAll(PDO::FETCH_ASSOC);

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of things
                return $things;

            }

            // Get a list of things from the database and will return the $errors array listing any errors encountered
            public function getThings(&$errors) {

                // Assume an empty list of things
                $things = array();

                // Get the logged in user
                $loggedinuser = $this->getSessionUser($errors);
                $loggedinuserid = $loggedinuser["userid"];
                $registrationcode = $loggedinuser["registrationcode"];
                $isadmin = FALSE;

                // Check to see if the user really is logged in and really is an admin
                if ($loggedinuserid != NULL) {
                    $isadmin = $this->isAdmin($errors, $loggedinuserid);
                }

                // Connect to the database
                $dbh = $this->getConnection();


                $sql = "SELECT thingid, thingname, thingdescription, " .
                "convert_tz(things.thingcreated,@@session.time_zone,'America/New_York') as thingcreated, " .
                "thinguserid, thingattachmentid, thingregistrationcode, commentsopendate, " .
                "commentsclosedate, critiquesclosedate, includeingrading " .
                "FROM things LEFT JOIN users ON things.thinguserid = users.userid " .
                "WHERE thingregistrationcode = :registrationcode ";
                if (!$isadmin) {
                    $sql = $sql . "AND commentsopendate < convert_tz(now(), @@session.time_zone, 'America/New_York') ";
                }
                $sql = $sql . "ORDER BY things.commentsopendate ASC";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getthings error", $stmt->errorInfo());

                    // If the query ran successfully, then get the list of things
                } else {


                    $things = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Get the comments for each discussion
                    foreach($things as &$thing) {
                        $comments = $this->getComments($thing['thingid'], $errors);
                        $thing['comments'] = $comments;

                        // Sentiment analysis variables
                        $thing['up'] = 0;
                        $thing['total'] = 0;

                        // Set a flag to indicate if the logged in user has commented on this discussion
                        $thing['hascommented'] = FALSE;
                        foreach ($comments as $comment) {
                            if ($comment['commentuserid'] == $loggedinuserid) {
                                $thing['hascommented'] = TRUE;
                            }

                            // While we're here, grab all the critiques for this comment and get a sentiment analysis
                            $thing['up'] = $thing['up'] + $comment['up'];
                            $thing['total'] = $thing['total'] + $comment['up'] + $comment['down'];

                        }
                    }

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of things
                return $things;

            }

            // Get a single thing from the database and will return the $errors array listing any errors encountered
            public function getThing($thingid, &$errors) {

                // Assume no thing exists for this thing id
                $thing = NULL;

                // Check for a valid thing ID
                if (empty($thingid)){
                    $errors[] = "Missing thing ID";
                }

                if (sizeof($errors) == 0){

                    // Connect to the database
                    $dbh = $this->getConnection();


                    $sql = "SELECT things.thingid, things.thingname, things.thingdescription, " .
                    "convert_tz(things.thingcreated,@@session.time_zone,'America/New_York') as thingcreated, " .
                    "things.thinguserid, things.thingattachmentid, things.thingregistrationcode, username, " .
                    "filename, commentsopendate, commentsclosedate, critiquesclosedate, includeingrading " .
                    "FROM things LEFT JOIN users ON things.thinguserid = users.userid " .
                    "LEFT JOIN attachments ON things.thingattachmentid = attachments.attachmentid " .
                    "WHERE thingid = :thingid";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":thingid", $thingid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("getthing error", $stmt->errorInfo());

                        // If no row returned then the thing does not exist in the database.
                    } else if ($stmt->rowCount() == 0) {

                        $errors[] = "Thing not found";
                        $this->auditlog("getThing", "bad thing id: $thingid");

                        // If the query ran successfully and row was returned, then get the details of the thing
                    } else {

                        // Get the thing
                        $thing = $stmt->fetch(PDO::FETCH_ASSOC);

                    }

                    // Close the connection
                    $dbh = NULL;

                } else {
                    $this->auditlog("getThing validation error", $errors);
                }

                // Return the thing
                return $thing;

            }

            // Get a list of comments from the database
            public function getComments($thingid, &$errors) {

                // Assume an empty list of comments
                $comments = array();

                // Figure out who's currently logged in
                $loggedinuser = $this->getSessionUser($errors);
                $loggedinusername = $loggedinuser["username"];

                // Check for a valid thing ID
                if (empty($thingid)) {

                    // Add an appropriate error message to the list
                    $errors[] = "Missing thing ID";
                    $this->auditlog("getComments validation error", $errors);

                } else {

                    // Connect to the database
                    $dbh = $this->getConnection();


                    $sql = "SELECT commentid, commenttext, commentuserid, " .
                    "convert_tz(comments.commentposted,@@session.time_zone,'America/New_York') as commentposted, " .
                    "username, attachmentid, filename, studentname, commentthingid " .
                    "FROM comments " .
                    "LEFT JOIN users ON comments.commentuserid = users.userid " .
                    "LEFT JOIN attachments ON comments.commentattachmentid = attachments.attachmentid " .
                    "LEFT JOIN students ON students.studentid = users.studentid " .
                    "WHERE commentthingid = :thingid ORDER BY commentposted ASC";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":thingid", $thingid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred loading the comments.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("getcomments error", $stmt->errorInfo());

                        // If the query ran successfully, then get the list of comments
                    } else {


                        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Load the critiques for the comments
                        foreach($comments as &$comment) {
                            if ($comment['username'] == $loggedinusername) {
                                $comment['mine'] = TRUE;
                            } else {
                                $comment['mine'] = FALSE;
                            }
                            $commentid = $comment['commentid'];
                            $comment['critiques'] = $this->getCritiques($commentid, $errors);

                            // Count up/down votes and check to see if this user has voted on the comment
                            $comment['voted'] = FALSE;
                            $comment['up'] = 0;
                            $comment['down'] = 0;
                            foreach($comment['critiques'] as &$critique) {
                                if ($critique['addstodiscussion'] == 1) {
                                    $comment['up'] = $comment['up'] + 1;
                                } else {
                                    $comment['down'] = $comment['down'] + 1;
                                }
                                if ($critique['critiqueuserid'] == $loggedinuser['userid']) {
                                    $comment['voted'] = TRUE;
                                }
                            }
                        }

                        // Add a public (anonymous) username
                        $counter = 1;
                        foreach($comments as &$comment) {

                            $comment['publicusername'] = "Commenter #" . $counter;
                            $counter += 1;

                        }

                        // TODO: Make an admin flag to turn on/off this operation
                        if ($loggedinuser['isadmin'] != 1) {

                            $counter = 1;
                            foreach($comments as &$comment) {

                                $comment['username'] = "Commenter #" . $counter;

                                $counter += 1;

                            }

                        }

                    }

                    // Close the connection
                    $dbh = NULL;

                }

                // Return the list of comments
                return $comments;

            }

            // Get a list of comments from the database
            public function getComment($commentid, &$errors) {

                // Assume an empty list of comments
                $comment = NULL;

                // Check for a valid thing ID
                if (empty($commentid)) {

                    // Add an appropriate error message to the list
                    $errors[] = "Missing comment ID";
                    $this->auditlog("getComment validation error", $errors);

                } else {

                    // Connect to the database
                    $dbh = $this->getConnection();


                    $sql = "SELECT commentid, commenttext, commentuserid, " .
                    "convert_tz(comments.commentposted,@@session.time_zone,'America/New_York') as commentposted, " .
                    "username, attachmentid, filename, commentthingid " .
                    "FROM comments " .
                    "LEFT JOIN users ON comments.commentuserid = users.userid " .
                    "LEFT JOIN attachments ON comments.commentattachmentid = attachments.attachmentid " .
                    "WHERE commentid = :commentid";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":commentid", $commentid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred loading the comment.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("getComment error", $stmt->errorInfo());

                        // If the query ran successfully, then get the list of comments
                    } else {

                        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
                        $loggedinuser = $this->getSessionUser($errors);
                        $loggedinusername = $loggedinuser["username"];
                        if ($comment['username'] == $loggedinusername) {
                            $comment['mine'] = TRUE;
                        } else {
                            $comment['mine'] = FALSE;
                        }

                    }

                    // Close the connection
                    $dbh = NULL;

                }

                // Return the list of comments
                return $comment;

            }

            // Get a list of comments from the database
            public function getCritiques($commentid, &$errors) {

                // Assume an empty list of comments
                $critiques = array();

                // Figure out who's currently logged in
                $loggedinuser = $this->getSessionUser($errors);
                $loggedinusername = $loggedinuser["username"];

                // Check for a valid thing ID
                if (empty($commentid)) {

                    // Add an appropriate error message to the list
                    $errors[] = "Missing comment ID";
                    $this->auditlog("getCritiques validation error", $errors);

                } else {

                    // Connect to the database
                    $dbh = $this->getConnection();


                    $sql = "SELECT critiqueid, critiquetext, " .
                    "convert_tz(critiques.critiqueposted,@@session.time_zone,'America/New_York') as critiqueposted, " .
                    "username, addstodiscussion, critiqueuserid, studentname " .
                    "FROM critiques " .
                    "LEFT JOIN users ON critiques.critiqueuserid = users.userid " .
                    "LEFT JOIN students ON students.studentid = users.studentid " .
                    "WHERE critiquecommentid = :commentid ORDER BY critiqueposted ASC";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":commentid", $commentid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred loading the critiques.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("getCritiques error", $stmt->errorInfo());

                        // If the query ran successfully, then get the list of comments
                    } else {

                        // TODO: Enforce authorization rules on data
                        $critiques = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $counter = 1;
                        foreach($critiques as &$critique) {

                            if ($critique['username'] == $loggedinusername) {
                                $critique['mine'] = TRUE;
                            } else {
                                $critique['mine'] = FALSE;
                            }
                            $critique['publicusername'] = "Critiquer #" . $counter;
                            $counter++;

                        }

                    }

                    // Close the connection
                    $dbh = NULL;

                }

                // Return the list of comments
                return $critiques;

            }

            // Handles the saving of uploaded attachments and the creation of a corresponding record in the attachments table.
            public function saveAttachment($dbh, $attachment, &$errors) {

                $attachmentid = NULL;

                // Check for an attachment
                if (isset($attachment) && isset($attachment['name']) && !empty($attachment['name'])) {

                    // Get the list of valid attachment types and file extensions
                    $attachmenttypes = $this->getAttachmentTypes($errors);

                    // Construct an array containing only the 'extension' keys
                    $extensions = array_column($attachmenttypes, 'extension');

                    // Get the uploaded filename
                    $filename = $attachment['name'];

                    // Extract the uploaded file's extension
                    $dot = strrpos($filename, ".");

                    // Make sure the file has an extension and the last character of the name is not a "."
                    if ($dot !== FALSE && $dot != strlen($filename)) {

                        // Check to see if the uploaded file has an allowed file extension
                        $extension = strtolower(substr($filename, $dot + 1));
                        if (!in_array($extension, $extensions)) {

                            // Not a valid file extension
                            $errors[] = "File does not have a valid file extension";
                            $this->auditlog("saveAttachment", "invalid file extension: $filename");

                        }

                    } else {

                        // No file extension -- Disallow
                        $errors[] = "File does not have a valid file extension";
                        $this->auditlog("saveAttachment", "no file extension: $filename");

                    }

                    // Only attempt to add the attachment to the database if the file extension was good
                    if (sizeof($errors) == 0) {

                        // Create a new ID
                        $attachmentid = bin2hex(random_bytes(16));

                        // Construct a SQL statement to perform the insert operation
                        $sql = "INSERT INTO attachments (attachmentid, filename) VALUES (:attachmentid, :filename)";

                        // Run the SQL insert and capture the result code
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(":attachmentid", $attachmentid);
                        $stmt->bindParam(":filename", $filename);
                        $result = $stmt->execute();

                        // If the query did not run successfully, add an error message to the list
                        if ($result === FALSE) {

                            $errors[] = "An unexpected error occurred storing the attachment.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("saveAttachment error", $stmt->errorInfo());

                        } else {

                            // Move the file from temp folder to html attachments folder
                            $newName = getcwd() . '/attachments/' . $attachmentid . '-' . $attachment['name'];
                            move_uploaded_file($attachment['tmp_name'], $newName);
                            $attachmentname = $attachment["name"];
                            $this->auditlog("saveAttachment", "success: $attachmentname");

                        }

                    }

                }

                return $attachmentid;

            }

            public function validateThing($name, $description, $commentsopendate, $commentsclosedate, $critiquesclosedate, &$errors) {

                if (empty($name)) {
                    $errors[] = "Missing title";
                }
                if (empty($description)) {
                    $errors[] = "Missing description";
                }
                if (empty($commentsopendate)) {
                    $errors[] = "Missing comments open date";
                }
                if (empty($commentsclosedate)) {
                    $errors[] = "Missing comments close date";
                }
                if (empty($critiquesclosedate)) {
                    $errors[] = "Missing critiques close date";
                }

            }

            // Adds a new thing to the database
            public function addThing($name, $description, $registrationcode, $attachment,
            $commentsopendate, $commentsclosedate, $critiquesclosedate,
            $includeingrading, &$errors) {

                // Get the user id from the session
                $user = $this->getSessionUser($errors);
                $userid = $user["userid"];

                // Validate the user input
                if (empty($userid)) {
                    $errors[] = "Missing user ID. Not logged in?";
                }
                $this->validateThing($name, $description, $commentsopendate, $commentsclosedate, $critiquesclosedate, $errors);

                // Only try to insert the data into the database if there are no validation errors
                if (sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();
                    $attachmentid = $this->saveAttachment($dbh, $attachment, $errors);

                    // Only try to insert the data into the database if the attachment successfully saved
                    if (sizeof($errors) == 0) {

                        // Create a new ID
                        $thingid = bin2hex(random_bytes(16));

                        // Add a record to the things table
                        // Construct a SQL statement to perform the insert operation
                        $sql = "INSERT INTO things " .
                        "(thingid, thingname, thingdescription, thingcreated, thinguserid, thingattachmentid, thingregistrationcode, " .
                        "commentsopendate, commentsclosedate, critiquesclosedate, includeingrading) " .
                        "VALUES (:thingid, :name, :thingdescription, now(), :userid, :attachmentid, :registrationcode, " .
                        ":commentsopendate, :commentsclosedate, :critiquesclosedate, :includeingrading)";

                        // Run the SQL insert and capture the result code
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(":thingid", $thingid);
                        $stmt->bindParam(":name", $name);
                        $stmt->bindParam(":thingdescription", $description);
                        $stmt->bindParam(":userid", $userid);
                        $stmt->bindParam(":attachmentid", $attachmentid);
                        $stmt->bindParam(":registrationcode", $registrationcode);
                        $stmt->bindParam(":commentsopendate", $commentsopendate);
                        $stmt->bindParam(":commentsclosedate", $commentsclosedate);
                        $stmt->bindParam(":critiquesclosedate", $critiquesclosedate);
                        $stmt->bindParam(":includeingrading", $includeingrading);
                        $result = $stmt->execute();

                        // If the query did not run successfully, add an error message to the list
                        if ($result === FALSE) {

                            $errors[] = "An unexpected error occurred adding the thing to the database.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("addthing error", $stmt->errorInfo());

                        } else {

                            $this->auditlog("addthing", "success: $name, id = $thingid");

                        }

                    }

                    // Close the connection
                    $dbh = NULL;

                } else {
                    $this->auditlog("addthing validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }

            // Updates an existing thing in the database. Does not support changes to attachments.
            public function updateThing($thingid, $name, $description, $registrationcode, $attachment, $commentsopendate, $commentsclosedate, $critiquesclosedate, $includeingrading, &$errors) {

                // Get the user id from the session
                $user = $this->getSessionUser($errors);
                $userid = $user["userid"];

                // Validate the user input
                if (empty($userid)) {
                    $errors[] = "Missing user ID. Not logged in?";
                }
                if (empty($thingid)) {
                    $errors[] = "Missing thing ID.";
                }
                $this->validateThing($name, $description, $commentsopendate, $commentsclosedate, $critiquesclosedate, $errors);

                // Only try to insert the data into the database if there are no validation errors
                if (sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();
                    //$attachmentid = $this->saveAttachment($dbh, $attachment, $errors);

                    // Only try to update the data in the database if the attachment successfully saved
                    if (sizeof($errors) == 0) {

                        // Update the record in the things table
                        // Construct a SQL statement to perform the insert operation
                        $sql = "UPDATE things " .
                        "SET thingname = :name, " .
                        "thingdescription = :thingdescription, " .
                        "thinguserid = :userid, " .
                        //"thingattachmentid = :attachmentid, " .
                        "thingregistrationcode = :registrationcode, " .
                        "commentsopendate = :commentsopendate, " .
                        "commentsclosedate = :commentsclosedate, " .
                        "critiquesclosedate = :critiquesclosedate, " .
                        "includeingrading = :includeingrading " .
                        "WHERE thingid = :thingid";

                        // Run the SQL insert and capture the result code
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(":name", $name);
                        $stmt->bindParam(":thingdescription", $description);
                        $stmt->bindParam(":userid", $userid);
                        //$stmt->bindParam(":attachmentid", $attachmentid);
                        $stmt->bindParam(":registrationcode", $registrationcode);
                        $stmt->bindParam(":commentsopendate", $commentsopendate);
                        $stmt->bindParam(":commentsclosedate", $commentsclosedate);
                        $stmt->bindParam(":critiquesclosedate", $critiquesclosedate);
                        $stmt->bindParam(":includeingrading", $includeingrading);
                        $stmt->bindParam(":thingid", $thingid);
                        $result = $stmt->execute();

                        // If the query did not run successfully, add an error message to the list
                        if ($result === FALSE) {

                            $errors[] = "An unexpected error occurred updating the thing in the database.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("updateThing error", $stmt->errorInfo());

                        } else {

                            $this->auditlog("updateThing", "success: $name, id = $thingid");

                        }

                    }

                    // Close the connection
                    $dbh = NULL;

                } else {
                    $this->auditlog("updateThing validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }

            // Check to see if the comment period has closed for a discussion
            public function isThingFieldInPast($thingid, $field, &$errors) {

                // Get the associated thing and closure date/times
                $thing = $this->getThing($thingid, $errors);
                // Expected format: "2018-08-19 15:30:00"
                $close = DateTime::createFromFormat('Y-m-d H:i:s', $thing[$field], new DateTimeZone('America/New_York'));

                // Get local date/time
                $localtime = new DateTime("now", new DateTimeZone('America/New_York') );
                if ($localtime > $close) {
                    return TRUE;
                }
                return FALSE;

            }

            // Check to see if the comment period has closed for a discussion
            public function isCommentingClosed($thingid, &$errors) {
                return $this->isThingFieldInPast($thingid, 'commentsclosedate', $errors);
            }

            // Check to see if the comment period has closed for a discussion
            public function isCritiquingClosed($thingid, &$errors) {
                return $this->isThingFieldInPast($thingid, 'critiquesclosedate', $errors);
            }

            // Adds a new comment to the database
            public function addComment($text, $thingid, $attachment, &$errors) {

                // Get the user id from the session
                $user = $this->getSessionUser($errors);
                $userid = $user["userid"];

                // Get the associated thing and closure date/times
                $thing = $this->getThing($thingid, $errors);
                $closed = $this->isCommentingClosed($thingid, $errors);
                if ($closed) {
                    $errors[] = "Sorry. The period to comment has closed.";
                }

                // Validate the user input
                if (empty($userid)) {
                    $errors[] = "Missing user ID. Not logged in?";
                }
                if (empty($thingid)) {
                    $errors[] = "Missing thing ID";
                }
                $this->validateCommentText($text, $errors);

                // Only try to insert the data into the database if there are no validation errors
                if (sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();

                    $attachmentid = $this->saveAttachment($dbh, $attachment, $errors);

                    // Only try to insert the data into the database if the attachment successfully saved
                    if (sizeof($errors) == 0) {

                        // Create a new ID
                        $commentid = bin2hex(random_bytes(16));

                        // Add a record to the Comments table
                        // Construct a SQL statement to perform the insert operation
                        $sql = "INSERT INTO comments " .
                        "(commentid, commenttext, commentposted, commentuserid, commentthingid, commentattachmentid) " .
                        "VALUES (:commentid, :text, now(), :userid, :thingid, :attachmentid)";

                        // Run the SQL insert and capture the result code
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(":commentid", $commentid);
                        $stmt->bindParam(":text", $text);
                        $stmt->bindParam(":userid", $userid);
                        $stmt->bindParam(":thingid", $thingid);
                        $stmt->bindParam(":attachmentid", $attachmentid);
                        $result = $stmt->execute();

                        // If the query did not run successfully, add an error message to the list
                        if ($result === FALSE) {
                            $errors[] = "An unexpected error occurred saving the comment to the database.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("addcomment error", $stmt->errorInfo());
                        } else {
                            $this->auditlog("addcomment", "success: $commentid");
                            $this->addCritique("", $commentid, TRUE, $errors);
                        }

                    }

                    // Close the connection
                    $dbh = NULL;

                } else {
                    $this->auditlog("addcomment validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }

            public function addCritique($text, $commentid, $addstodiscussion, &$errors) {

                $this->auditlog("addCritique", "invoked: $text; $commentid; $addstodiscussion");

                // Get the user id from the session
                $user = $this->getSessionUser($errors);
                $userid = $user["userid"];

                // Validate the user input
                if (empty($userid)) {
                    $errors[] = "Missing user ID. Not logged in?";
                }
                if (empty($commentid)) {
                    $errors[] = "Missing comment ID";
                }
                if (!$addstodiscussion && empty($text)) {
                    $errors[] = "Missing critique text for negative critique";
                }

                // Only try to insert the data into the database if there are no validation errors
                if (sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();

                    // Create a new ID
                    $critiqueid = bin2hex(random_bytes(16));

                    // Add a record to the Comments table
                    // Construct a SQL statement to perform the insert operation
                    $sql = "INSERT INTO critiques " .
                    "(critiqueid, critiqueuserid, critiquecommentid, critiqueposted, addstodiscussion, critiquetext) " .
                    "VALUES (:critiqueid, :critiqueuserid, :critiquecommentid, now(), :addstodiscussion, :critiquetext)";

                    // Run the SQL insert and capture the result code
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":critiqueid", $critiqueid);
                    $stmt->bindParam(":critiqueuserid", $userid);
                    $stmt->bindParam(":critiquecommentid", $commentid);
                    $stmt->bindParam(":addstodiscussion", $addstodiscussion);
                    $stmt->bindParam(":critiquetext", $text);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {
                        $errors[] = "An unexpected error occurred saving the critique to the database.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("addCritique error", $stmt->errorInfo());
                    } else {
                        $this->auditlog("addCritique", "success: $critiqueid");
                    }

                    // Close the connection
                    $dbh = NULL;

                } else {
                    $this->auditlog("addCritique validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }

            }

            // Get a list of users from the database and will return the $errors array listing any errors encountered
            public function getUsers(&$errors) {

                // Assume an empty list of users
                $users = array();

                // Connect to the database
                $dbh = $this->getConnection();


                //$sql = "SELECT userid, username, email, isadmin FROM users ORDER BY username";
                $sql = "SELECT users.userid, username, email, isadmin, GROUP_CONCAT(registrationcode) AS regcodes, students.studentname " .
                "FROM users LEFT JOIN userregistrations ON users.userid = userregistrations.userid " .
                "LEFT JOIN students ON users.studentid = students.studentid 	" .
                "GROUP BY students.studentname";


                $stmt = $dbh->prepare($sql);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the user list.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getusers error", $stmt->errorInfo());


                } else {


                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->auditlog("getusers", "success");

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of users
                return $users;

            }

            // Gets a single user from database and will return the $errors array listing any errors encountered
            public function getUser($userid, &$errors) {

                // Assume no user exists for this user id
                $user = NULL;

                // Validate the user input
                if (empty($userid)) {
                    $errors[] = "Missing userid";
                }

                if(sizeof($errors)== 0) {

                    // Get the user id from the session
                    $user = $this->getSessionUser($errors);
                    $loggedinuserid = $user["userid"];
                    $isadmin = FALSE;

                    // Check to see if the user really is logged in and really is an admin
                    if ($loggedinuserid != NULL) {
                        $isadmin = $this->isAdmin($errors, $loggedinuserid);
                    }

                    // Stop people from viewing someone else's profile
                    if (!$isadmin && $loggedinuserid != $userid) {

                        $errors[] = "Cannot view other user";
                        $this->auditlog("getuser", "attempt to view other user: $loggedinuserid");

                    } else {

                        // Connect to the database
                        $dbh = $this->getConnection();


                        $sql = "SELECT userid, username, email, isadmin FROM users " .
                        "WHERE userid = :userid";


                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(":userid", $userid);
                        $result = $stmt->execute();

                        // If the query did not run successfully, add an error message to the list
                        if ($result === FALSE) {

                            $errors[] = "An unexpected error occurred retrieving the specified user.";
                            $this->debug($stmt->errorInfo());
                            $this->auditlog("getuser error", $stmt->errorInfo());

                            // If the query did not return any rows, add an error message for invalid user id
                        } else if ($stmt->rowCount() == 0) {

                            $errors[] = "Bad userid";
                            $this->auditlog("getuser", "bad userid: $userid");

                            // If the query ran successfully and we got back a row, then the request succeeded
                        } else {

                            // Get the row from the result
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        }

                        // Close the connection
                        $dbh = NULL;
                    }
                } else {
                    $this->auditlog("getuser validation error", $errors);
                }

                // Return user if there are no errors, otherwise return NULL
                return $user;
            }

            // Updates a single user in the database and will return the $errors array listing any errors encountered
            public function updateUser($userid, $username, $password, $isadminDB, &$errors) {

                // Assume no user exists for this user id
                $user = NULL;

                // Validate the user input
                if (empty($userid)) {

                    $errors[] = "Missing userid";

                }

                if(sizeof($errors) == 0) {

                    // Get the user id from the session
                    $user = $this->getSessionUser($errors);
                    $loggedinuserid = $user["userid"];
                    $isadmin = FALSE;

                    // Check to see if the user really is logged in and really is an admin
                    if ($loggedinuserid != NULL) {
                        $isadmin = $this->isAdmin($errors, $loggedinuserid);
                    }

                    // Stop people from editing someone else's profile
                    if (!$isadmin && $loggedinuserid != $userid) {

                        $errors[] = "Cannot edit other user";
                        $this->auditlog("getuser", "attempt to update other user: $loggedinuserid");

                    } else {

                        // Validate the user input
                        $this->validateUsername($username, $errors);
                        if (!empty($password)) {
                            $this->validatePassword($password, $errors);
                        }

                        // Only try to update the data into the database if there are no validation errors
                        if (sizeof($errors) == 0) {

                            // Connect to the database
                            $dbh = $this->getConnection();

                            // Hash the user's password
                            $passwordhash = password_hash($password, PASSWORD_DEFAULT);


                            $sql = 	"UPDATE users SET username=:username  " .
                            ($loggedinuserid != $userid ? ", isadmin=:isAdmin " : "") .
                            (!empty($password) ? ", passwordhash=:passwordhash" : "") .
                            " WHERE userid = :userid";


                            $stmt = $dbh->prepare($sql);
                            $stmt->bindParam(":username", $username);
                            $adminFlag = ($isadminDB ? "1" : "0");
                            if ($loggedinuserid != $userid) {
                                $stmt->bindParam(":isAdmin", $adminFlag);
                            }
                            if (!empty($password)) {
                                $stmt->bindParam(":passwordhash", $passwordhash);
                            }
                            $stmt->bindParam(":userid", $userid);
                            $result = $stmt->execute();

                            // If the query did not run successfully, add an error message to the list
                            if ($result === FALSE) {
                                $errors[] = "An unexpected error occurred saving the user profile. ";
                                $this->debug($stmt->errorInfo());
                                $this->auditlog("updateUser error", $stmt->errorInfo());
                            } else {
                                $this->auditlog("updateUser", "success");
                            }

                            // Close the connection
                            $dbh = NULL;
                        } else {
                            $this->auditlog("updateUser validation error", $errors);
                        }
                    }
                } else {
                    $this->auditlog("updateUser validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }


            // Updates a single user in the database and will return the $errors array listing any errors encountered
            public function updateUserPassword($userid, $password, &$errors) {

                // Validate the user input
                if (empty($userid)) {
                    $errors[] = "Missing userid";
                }
                $this->validatePassword($password, $errors);

                if(sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();

                    // Hash the user's password
                    $passwordhash = password_hash($password, PASSWORD_DEFAULT);


                    $sql = "UPDATE users SET passwordhash=:passwordhash " .
                    "WHERE userid = :userid";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":passwordhash", $passwordhash);
                    $stmt->bindParam(":userid", $userid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {
                        $errors[] = "An unexpected error occurred supdating the password.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("updateUserPassword error", $stmt->errorInfo());
                    } else {
                        $this->auditlog("updateUserPassword", "success");
                    }

                    // Close the connection
                    $dbh = NULL;

                } else {

                    $this->auditlog("updateUserPassword validation error", $errors);

                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }


            function getFile($name){
                return file_get_contents($name);
            }

            // Get a list of users from the database and will return the $errors array listing any errors encountered
            public function getAttachmentTypes(&$errors) {

                // Assume an empty list of topics
                $types = array();

                // Connect to the database
                $dbh = $this->getConnection();


                $sql = "SELECT attachmenttypeid, name, extension FROM attachmenttypes ORDER BY name";


                $stmt = $dbh->prepare($sql);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the attachment types list.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getattachmenttypes error", $stmt->errorInfo());


                } else {


                    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->auditlog("getattachmenttypes", "success");

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of users
                return $types;

            }

            // Creates a new session in the database for the specified user
            public function newAttachmentType($name, $extension, &$errors) {

                $attachmenttypeid = NULL;

                // Check for a valid name
                if (empty($name)) {
                    $errors[] = "Missing name";
                }
                // Check for a valid extension
                if (empty($extension)) {
                    $errors[] = "Missing extension";
                }

                // Only try to query the data into the database if there are no validation errors
                if (sizeof($errors) == 0) {

                    // Create a new session ID
                    $attachmenttypeid = bin2hex(random_bytes(25));

                    // Connect to the database
                    $dbh = $this->getConnection();

                    // Construct a SQL statement to perform the insert operation
                    $sql = "INSERT INTO attachmenttypes (attachmenttypeid, name, extension) " .
                    "VALUES (:attachmenttypeid, :name, :extension)";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":attachmenttypeid", $attachmenttypeid);
                    $stmt->bindParam(":name", $name);
                    $stmt->bindParam(":extension", strtolower($extension));
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("newAttachmentType error", $stmt->errorInfo());
                        return NULL;

                    }

                } else {

                    $this->auditlog("newAttachmentType error", $errors);
                    return NULL;

                }

                return $attachmenttypeid;
            }

            public function getProgressReport($userid, $registrationcode, &$errors) {

                /*
                numberoftopics
                numberofgradedtopics
                numberofungradedtopics

                numberofcommentsmade
                numberofgradedcommentsmade
                numberofungradedcommentsmade

                numberofcritiquesreceived
                numberofungradedcritiquesreceived
                numberofgradedcritiquesreceived
                numberofpositivecritiquesreceived
                numberofnegativecritiquesreceived
                ungradedupvotes
                ungradeddownvotes
                gradedupvotes
                gradeddownvotes


                numberofcritiquesgiven
                numberofgradedcritiquesgiven
                numberofungradedcritiquesgiven

                numberofcritiquesexpected
                numberofgradedcritiquesexpected
                numberofungradedcritiquesexpected

                numberofuncommentedtopics
                numberofuncritiquedcomments

                commentinggrade (numberofgradedcommentsmade/numberofgradedtopics)
                critiquinggrade (numberofgradedcritiquesgiven/numberofgradedcritiquesexpected)
                commentquality (gradedupvotes/numberofgradedcritiquesreceived)
                */

                $progressReport = array("userid"=>$userid, "registrationcode"=>$registrationcode);

                if (empty($userid)) {
                    $errors[] = "Missing user ID";
                }
                if (empty($registrationcode)) {
                    $errors[] = "Missing registration code";
                }

                // Connect to the database
                $dbh = $this->getConnection();

                // Get number of topics for the current registration code
                $sql = "SELECT includeingrading, COUNT(*) AS numberoftopics FROM things " .
                "WHERE LOWER(thingregistrationcode) = LOWER(:registrationcode) " .
                "AND commentsopendate < convert_tz(now(), @@session.time_zone, 'America/New_York') " .
                "GROUP BY includeingrading";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (number of topics)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());


                } else {


                    $progressReport['numberofgradedtopics'] = 0;
                    $progressReport['numberofungradedtopics'] = 0;
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        if ($row['includeingrading']) {
                            $progressReport['numberofgradedtopics'] = $row['numberoftopics'];
                        } else {
                            $progressReport['numberofungradedtopics'] = $row['numberoftopics'];
                        }
                    }
                    $progressReport['numberoftopics'] =
                    $progressReport['numberofgradedtopics'] +
                    $progressReport['numberofungradedtopics'];

                }

                // Get the number of comments made by this user under this registration code
                $sql = "SELECT includeingrading, COUNT(*) AS numberofcommentsmade FROM comments " .
                "LEFT JOIN things ON comments.commentthingid = things.thingid " .
                "WHERE commentuserid = :userid " .
                "AND LOWER(things.thingregistrationcode) = LOWER(:regcode) " .
                "GROUP BY includeingrading";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":userid", $userid);
                $stmt->bindParam(":regcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (number of comments made)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());


                } else {


                    $progressReport['numberofgradedcommentsmade'] = 0;
                    $progressReport['numberofungradedcommentsmade'] = 0;
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        if ($row['includeingrading']) {
                            $progressReport['numberofgradedcommentsmade'] = $row['numberofcommentsmade'];
                        } else {
                            $progressReport['numberofungradedcommentsmade'] = $row['numberofcommentsmade'];
                        }
                    }
                    $progressReport['numberofcommentsmade'] =
                    $progressReport['numberofgradedcommentsmade'] +
                    $progressReport['numberofungradedcommentsmade'];

                }

                // Get the number of critiques received on this user's comments under this registration code
                $sql = "SELECT includeingrading, addstodiscussion, COUNT(addstodiscussion) AS numcritiques " .
                "FROM critiques " .
                "LEFT JOIN comments ON comments.commentid = critiques.critiquecommentid " .
                "LEFT JOIN things ON comments.commentthingid = things.thingid  " .
                "WHERE commentuserid = :commentuserid AND LOWER(things.thingregistrationcode) = LOWER(:regcode)  " .
                "GROUP BY includeingrading, addstodiscussion";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":commentuserid", $userid);
                $stmt->bindParam(":regcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (analytics)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());


                } else {

                    $progressReport['gradedupvotes'] = 0;
                    $progressReport['gradeddownvotes'] = 0;
                    $progressReport['ungradedupvotes'] = 0;
                    $progressReport['ungradeddownvotes'] = 0;
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $progressReport['up'] = 0;
                    $progressReport['down'] = 0;
                    foreach ($rows as $row) {
                        if ($row['includeingrading']) {
                            if ($row['addstodiscussion']) {
                                $progressReport['gradedupvotes'] = $row['numcritiques'];
                            } else {
                                $progressReport['gradeddownvotes'] = $row['numcritiques'];
                            }
                        } else {
                            if ($row['addstodiscussion']) {
                                $progressReport['ungradedupvotes'] = $row['numcritiques'];
                            } else {
                                $progressReport['ungradeddownvotes'] = $row['numcritiques'];
                            }
                        }
                    }
                    $progressReport['numberofgradedcritiquesreceived'] =
                    $progressReport['gradedupvotes'] +
                    $progressReport['gradeddownvotes'];
                    $progressReport['numberofungradedcritiquesreceived'] =
                    $progressReport['ungradedupvotes'] +
                    $progressReport['ungradeddownvotes'];
                    $progressReport['numberofcritiquesreceived'] =
                    $progressReport['numberofgradedcritiquesreceived'] +
                    $progressReport['numberofungradedcritiquesreceived'];
                    $progressReport['numberofpositivecritiquesreceived'] =
                    $progressReport['gradedupvotes'] +
                    $progressReport['ungradedupvotes'];
                    $progressReport['numberofnegativecritiquesreceived'] =
                    $progressReport['gradeddownvotes'] +
                    $progressReport['ungradeddownvotes'];
                }

                // Get the number of critiques made by this user for this registration code
                $sql = "SELECT includeingrading, count(*) AS critiquecount " .
                "FROM critiques " .
                "LEFT JOIN comments ON comments.commentid = critiques.critiquecommentid " .
                "LEFT JOIN things ON things.thingid = comments.commentthingid " .
                "WHERE critiqueuserid = :critiqueuserid " .
                "AND LOWER(things.thingregistrationcode) = LOWER(:regcode) " .
                "GROUP BY includeingrading";

                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":critiqueuserid", $userid);
                $stmt->bindParam(":regcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (number of critiques given)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());

                } else {

                    $progressReport['numberofgradedcritiquesgiven'] = 0;
                    $progressReport['numberofungradedcritiquesgiven'] = 0;
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        if ($row['includeingrading']) {
                            $progressReport['numberofgradedcritiquesgiven'] = $row['critiquecount'];
                        } else {
                            $progressReport['numberofungradedcritiquesgiven'] = $row['critiquecount'];
                        }
                    }
                    $progressReport['numberofcritiquesgiven'] =
                    $progressReport['numberofgradedcritiquesgiven'] +
                    $progressReport['numberofungradedcritiquesgiven'];
                }

                // Get the total number of comments made (and therefore critiques expected) for this registration code
                $sql = "SELECT includeingrading, count(*) AS numberofcommentsmade FROM comments " .
                "LEFT JOIN things ON things.thingid = comments.commentthingid " .
                "WHERE LOWER(thingregistrationcode) = LOWER(:registrationcode) " .
                "GROUP BY includeingrading";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (number of comments made)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());


                } else {

                    $progressReport['numberofgradedcritiquesexpected'] = 0;
                    $progressReport['numberofungradedcritiquesexpected'] = 0;
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        if ($row['includeingrading']) {
                            $progressReport['numberofgradedcritiquesexpected'] = $row['numberofcommentsmade'];
                        } else {
                            $progressReport['numberofungradedcritiquesexpected'] = $row['numberofcommentsmade'];
                        }
                    }
                    $progressReport['numberofcritiquesexpected'] =
                    $progressReport['numberofgradedcritiquesexpected'] +
                    $progressReport['numberofungradedcritiquesexpected'];

                }

                // Number of uncommented topics
                $sql = "SELECT COUNT(*) AS numberoftopics FROM things " .
                "WHERE thingregistrationcode = :registrationcode " .
                "AND commentsopendate < convert_tz(now(), @@session.time_zone, 'America/New_York') " .
                "AND commentsclosedate > convert_tz(now(), @@session.time_zone, 'America/New_York') " .
                "AND thingid NOT IN (SELECT commentthingid FROM comments WHERE commentuserid = :userid)";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $stmt->bindParam(":userid", $userid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (number of uncommented topics)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());


                } else {


                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $progressReport['numberofuncommentedtopics'] = $row['numberoftopics'];

                }

                // Number of uncritiqued comments
                $sql = "SELECT COUNT(*) AS numberofcomments FROM comments " .
                "LEFT JOIN things ON comments.commentthingid = things.thingid " .
                "WHERE thingregistrationcode = :registrationcode " .
                "AND commentsopendate < convert_tz(now(), @@session.time_zone, 'America/New_York') " .
                "AND critiquesclosedate > convert_tz(now(), @@session.time_zone, 'America/New_York') " .
                "AND commentid NOT IN (SELECT critiquecommentid FROM critiques WHERE critiqueuserid = :userid)";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":registrationcode", $registrationcode);
                $stmt->bindParam(":userid", $userid);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the progress report (number of uncritiqued comments)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getProgressReport error", $stmt->errorInfo());


                } else {

                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $progressReport['numberofuncritiquedcomments'] = $row['numberofcomments'];

                }

                // Close the connection
                $dbh = NULL;

                // Calculate grades
                $progressReport['commentinggrade'] = 0;
                $progressReport['critiquinggrade'] = 0;
                $progressReport['commentquality'] = 0;

                if ($progressReport['numberofgradedtopics'] != 0) {
                    $progressReport['commentinggrade'] = round(100 * $progressReport['numberofgradedcommentsmade'] / $progressReport['numberofgradedtopics']) . "%";
                } else {
                    $progressReport['commentinggrade'] = "No graded topics";
                }
                if ($progressReport['numberofgradedcritiquesexpected'] != 0) {
                    $progressReport['critiquinggrade'] = round(100 * $progressReport['numberofgradedcritiquesgiven'] / $progressReport['numberofgradedcritiquesexpected']) . "%";
                } else {
                    $progressReport['critiquinggrade'] = "No graded critiques received";
                }
                if ($progressReport['numberofgradedcritiquesreceived'] != 0) {
                    $progressReport['commentquality'] = round(100 * $progressReport['gradedupvotes'] / $progressReport['numberofgradedcritiquesreceived']) . "%";
                } else {
                    $progressReport['commentquality'] = "No graded critiques received";
                }

                return $progressReport;
            }

            public function getAllProgressReports(&$errors) {
                $progressReport = array();

                // Connect to the database
                $dbh = $this->getConnection();
                $sql = "SELECT userregistrations.registrationcode, users.userid " .
                    "FROM users LEFT JOIN userregistrations ON users.userid = userregistrations.userid " .
                    "GROUP BY registrationcode, userid";
                $stmt = $dbh->prepare($sql);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {
                    $errors[] = "An unexpected error occurred getting the progress report (user list)";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getAllProgressReports error", $stmt->errorInfo());
                } else {
                    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                foreach ($students as $student) {
                    $studentReport = $this->getProgressReport($student['userid'], $student['registrationcode'], $errors);
                    $progressReport[] = $studentReport;
                }

                $labels = array();
                foreach ($studentReport as $key => $value) {
                    $labels[$key] = $key;
                }
                array_splice($progressReport, 0, 0, array($labels));

                return $progressReport;
            }

            public function outputCSV($data) {

                $outstream = fopen("php://temp/", 'r+');
                function __outputCSV(&$vals, $key, $filehandler) {
                    fputcsv($filehandler, $vals, ',', '"');
                }
                array_walk($data, '__outputCSV', $outstream);
                $length = fstat($outstream)["size"];

                header('Content-Description: File Transfer');
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename=progress.csv');
                header('Content-Transfer-Encoding: text');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . $length);

                // Read what we have written.
                rewind($outstream);

                $fp = fopen("php://output", 'w');
                fwrite($fp, stream_get_contents($outstream));
                fclose($fp);

            }

            public function getReportCodes(&$errors) {

                // Assume an empty list of topics
                $codes = array();

                // Connect to the database
                $dbh = $this->getConnection();


                $sql = "SELECT reportcodeid, reportcodename, moreinfoneeded FROM reportcodes ORDER BY reportcodeid";


                $stmt = $dbh->prepare($sql);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the attachment types list.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getReportCodes error", $stmt->errorInfo());


                } else {


                    $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->auditlog("getReportCodes", "success");

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of users
                return $codes;
            }

            public function getReports(&$errors) {

                // Assume an empty list of topics
                $reports = array();

                // Connect to the database
                $dbh = $this->getConnection();


                $sql = "SELECT * FROM userreports";


                $stmt = $dbh->prepare($sql);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the attachment types list.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getReports error", $stmt->errorInfo());


                } else {


                    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->auditlog("getReports", "success");

                }

                // Close the connection
                $dbh = NULL;

                // Return the list of users
                return $reports;
            }

            public function submitReport($commentid, $reasonid, $moreinfo, &$errors) {

                $reportid = NULL;

                // Declare an errors array
                $errors = [];

                // Connect to the database
                $dbh = $this->getConnection();

                // If a user is logged in, get their userid
                $userid = NULL;
                $user = $this->getSessionUser($errors, TRUE);
                if ($user != NULL) {
                    $userid = $user["userid"];
                    $reportid = bin2hex(random_bytes(16));

                    // Construct a SQL statement to perform the insert operation
                    $sql = "INSERT INTO userreports (userreportid, userreportcommentid, userreportreasoncodeid, userreportuserid, reportsubmitted, moreinfo) " .
                    "VALUES (:userreportid, :userreportcommentid, :userreportreasoncodeid, :userreportuserid, NOW(), :moreinfo)";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":userreportid", $reportid);
                    $stmt->bindParam(":userreportcommentid", $commentid);
                    $stmt->bindParam(":userreportreasoncodeid", $reasonid);
                    $stmt->bindParam(":userreportuserid", $userid);
                    $stmt->bindParam(":moreinfo", $moreinfo);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred submitting your report.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("submitReport error", $stmt->errorInfo());

                        // If the query ran successfully, then email the admin
                    } else {
                        $reportid = $dbh->lastInsertId();

                        $comment = $this->getComment($commentid, $errors);
                        $commentText = $comment['commenttext'];

                        // Send email to admin
                        // TODO: Removed hardcode email
                        $to      = 'rthackston@georgiasouthern.edu';
                        $subject = 'New user report';
                        $message = "A user has reported a comment.\n\n".
                        "Report ID: $reportid\n".
                        "CommentID: $commentid\n".
                        "Comment Text: $commentText\n".
                        "Reason ID: $reasonid\n".
                        "More Info: $moreinfo\n";
                        $headers = 'From: webmaster@russellthackston.me' . "\r\n" .
                        'Reply-To: webmaster@russellthackston.me' . "\r\n";

                        mail($to, $subject, $message, $headers);
                    }
                }

                $dbh = NULL;

                return $reportid;
            }

            public function submitRollcall(&$errors) {

                // Declare an errors array
                $errors = [];

                // Connect to the database
                $dbh = $this->getConnection();

                // If a user is logged in, get their userid
                $userid = NULL;
                $user = $this->getSessionUser($errors, TRUE);
                if ($user != NULL) {
                    $userid = $user["userid"];

                    // Construct a SQL statement to perform the insert operation
                    $sql = "INSERT INTO rollcall (userid, callsubmitted) " .
                    "VALUES (:userid, NOW()) ON DUPLICATE KEY UPDATE callsubmitted = NOW()";


                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":userid", $userid);
                    $result = $stmt->execute();

                    // If the query did not run successfully, add an error message to the list
                    if ($result === FALSE) {

                        $errors[] = "An unexpected error occurred submitting to the roll.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("submitRollcall error", $stmt->errorInfo());

                    }

                }

                $dbh = NULL;

            }

            public function getRollcall($registrationcode, &$errors) {

                // Declare an errors array
                $errors = [];

                // Connect to the database
                $dbh = $this->getConnection();

                // Construct a SQL statement to get the roll
                $sql = "SELECT users.userid, users.studentid AS studentid, students.studentname AS studentname, rollcall.callsubmitted > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AS present " .
                "FROM users  " .
                "LEFT JOIN rollcall ON rollcall.userid = users.userid  " .
                "LEFT JOIN students ON users.studentid = students.studentid  " .
                "LEFT JOIN userregistrations ON users.userid = userregistrations.userid " .
                "WHERE userregistrations.registrationcode = :regcode " .
                "AND users.isadmin <> 1 " .
                "AND students.studentid > 100000000 " .
                "GROUP BY users.userid " .
                "ORDER BY studentname ASC";


                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(":regcode", $registrationcode);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {

                    $errors[] = "An unexpected error occurred getting the roll.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getRollcall error", $stmt->errorInfo());

                } else {

                    $roll = $stmt->fetchAll(PDO::FETCH_ASSOC);

                }

                $dbh = NULL;

                return $roll;

            }

            public function build_html_calendar($year, $month, $events = null) {

                // CSS classes
                $css_cal = 'calendar';
                $css_cal_row = 'calendar-row';
                $css_cal_day_head = 'calendar-day-head';
                $css_cal_day = 'calendar-day';
                $css_cal_day_number = 'day-number';
                $css_cal_day_blank = 'calendar-day-np';
                $css_cal_day_event = 'calendar-day-event';
                $css_cal_event = 'calendar-event';

                $today = date('Y-m-d', mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time())));

                // Table headings
                $headings = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];

                // Start: draw table
                $calendar =
                "<table cellpadding='0' cellspacing='0' class='{$css_cal}'>" .
                "<tr class='{$css_cal_row} {$css_cal_day_head}'>" .
                "<th class='{$css_cal_day_head}'>" .
                implode("</th><th class='{$css_cal_day_head}'>", $headings) .
                "</th>" .
                "</tr>";

                // Days and weeks
                $running_day = date('N', mktime(0, 0, 0, $month, 1, $year));
                $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));

                // Row for week one
                $calendar .= "<tr class='{$css_cal_row}'>";

                // Print "blank" days until the first of the current week
                for ($x = 1; $x < $running_day; $x++) {
                    $calendar .= "<td class='{$css_cal_day_blank}'> </td>";
                }

                // Keep going with days...
                for ($day = 1; $day <= $days_in_month; $day++) {

                    // Check if there is an event on the current date
                    $cur_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                    $draw_event = false;
                    if (isset($events) && isset($events[$cur_date])) {
                        $draw_event = true;
                    }

                    // Check for today's date to add class
                    $istoday = "";
                    if ($cur_date == $today) {
                        $istoday = " today ";
                    }

                    // Day cell
                    $calendar .= $draw_event ?
                    "<td class='{$css_cal_day} {$css_cal_day_event} {$istoday}'>" :
                    "<td class='{$css_cal_day} {$istoday}'>";

                    // Add the day number
                    $calendar .= "<div class='{$css_cal_day_number}'>" . $day . "</div>";

                    // Insert an event for this day
                    if ($draw_event) {
                        $calendar .=
                        "<div class='{$css_cal_event}'>" .
                        $events[$cur_date]['text'] .
                        "</div>";
                    }

                    // Close day cell
                    $calendar .= "</td>";

                    // New row
                    if ($running_day == 7) {
                        $calendar .= "</tr>";
                        if (($day + 1) <= $days_in_month) {
                            $calendar .= "<tr class='{$css_cal_row}'>";
                        }
                        $running_day = 1;
                    }

                    // Increment the running day
                    else {
                        $running_day++;
                    }

                } // for $day

                // Finish the rest of the days in the week
                if ($running_day != 1) {
                    for ($x = $running_day; $x <= 7; $x++) {
                        $calendar .= "<td class='{$css_cal_day_blank}'> </td>";
                    }
                }

                // Final row
                $calendar .= "</tr>";

                // End the table
                $calendar .= '</table>';

                // All done, return result
                return $calendar;
            }

            // Get a list of students from the database and will return the $errors array listing any errors encountered
            public function getStudents(&$errors) {

                // Assume an empty list of users
                $students = array();

                // Connect to the database
                $dbh = $this->getConnection();
                $sql = "SELECT students.studentid, studentname, COUNT(u.userid) AS regcount, GROUP_CONCAT(registrationcode) AS regcodes " .
                    "FROM students " .
                    "LEFT JOIN users u ON u.studentid = students.studentid " .
                    "LEFT JOIN userregistrations ON userregistrations.userid = u.userid " .
                    "GROUP BY studentname, studentid";
                $stmt = $dbh->prepare($sql);
                $result = $stmt->execute();

                // If the query did not run successfully, add an error message to the list
                if ($result === FALSE) {
                    $errors[] = "An unexpected error occurred getting the student list.";
                    $this->debug($stmt->errorInfo());
                    $this->auditlog("getStudents error", $stmt->errorInfo());
                } else {
                    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->auditlog("getStudents", "success");
                }

                // Close the connection
                $dbh = NULL;

                // Return the list of users
                return $students;

            }

            // Gets a single student from database and will return the $errors array listing any errors encountered
            public function getStudent($studentid, &$errors) {

                // Assume no user exists for this user id
                $student = NULL;

                // Validate the user input
                if (empty($studentid)) {
                    $errors[] = "Missing student ID";
                }

                if(sizeof($errors)== 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();
                    $sql = "SELECT * FROM students WHERE studentid = :studentid";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":studentid", $studentid);
                    $result = $stmt->execute();
                    if ($result === FALSE) {
                        $errors[] = "An unexpected error occurred retrieving the specified student.";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("getStudent error", $stmt->errorInfo());
                    } else if ($stmt->rowCount() == 0) {
                        $errors[] = "Bad student ID";
                        $this->auditlog("getStudent", "bad studentid: $studentid");
                    } else {
                        $student = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    $dbh = NULL;
                }
                return $student;
            }

            // Updates a single user in the database and will return the $errors array listing any errors encountered
            public function updateStudent($studentid, $studentname, &$errors) {

                $user = NULL;

                if (empty($studentid)) {
                    $errors[] = "Missing userid";
                }
                if (empty($studentname)) {
                    $errors[] = "Missing student name";
                }

                if(sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();
                    $sql = 	"UPDATE students SET studentname=:studentname  " .
                    " WHERE studentid = :studentid";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":studentname", $studentname);
                    $stmt->bindParam(":studentid", $studentid);
                    $result = $stmt->execute();
                    if ($result === FALSE) {
                        $errors[] = "An unexpected error occurred saving the student record. ";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("updateStudent error", $stmt->errorInfo());
                    } else {
                        $this->auditlog("updateStudent", "success");
                    }
                    $dbh = NULL;
                } else {
                    $this->auditlog("updateStudent validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }

            // Updates a single user in the database and will return the $errors array listing any errors encountered
            public function addStudent($studentid, $studentname, &$errors) {

                $user = NULL;

                if (empty($studentid)) {
                    $errors[] = "Missing userid";
                }
                if (empty($studentname)) {
                    $errors[] = "Missing student name";
                }

                if(sizeof($errors) == 0) {

                    // Connect to the database
                    $dbh = $this->getConnection();
                    $sql = 	"INSERT INTO students (studentid, studentname) VALUES (:studentid, :studentname)";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(":studentid", $studentid);
                    $stmt->bindParam(":studentname", $studentname);
                    $result = $stmt->execute();
                    if ($result === FALSE) {
                        $errors[] = "An unexpected error occurred creating the student record. ";
                        $this->debug($stmt->errorInfo());
                        $this->auditlog("addStudent error", $stmt->errorInfo());
                    } else {
                        $this->auditlog("addStudent", "success");
                    }
                    $dbh = NULL;
                } else {
                    $this->auditlog("addStudent validation error", $errors);
                }

                // Return TRUE if there are no errors, otherwise return FALSE
                if (sizeof($errors) == 0){
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        }

        ?>
