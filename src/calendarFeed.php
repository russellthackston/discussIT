<?php

// Generate an iCal/ICS file on the fly and send it to the user.

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// If this is not a GET request, return HTTP 405 Method Not Allowed and end the script early
if( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
  http_response_code(405);
  echo '405 Method Not Allowed';
  exit();
}

// If regcode is not set, return HTTP 400 Bad Request and end the script early
if( !isset($_GET['regcode']) ) {
  http_response_code(400);
  echo '400 Bad Request';
  exit();
}

// Get the regcode from the GET params
$regcode = $_GET['regcode'];

// Get the event list with the given regcode
$events = $app->getCalendar($errors, $regcode);

// If the events are empty, then the regcode is likely invalid
// Return HTTP 404 Not Found and end the script early
if(count($events) == 0) {
  http_response_code(404);
  echo '404 Not Found';
  exit();
}

// If all that passed, we should be able to generate an ICS file

// Set Content-Type to text/calendar
header('Content-Type: text/calendar');


// Print the ICS header
echo "BEGIN:VCALENDAR\r\n";
echo "PRODID:-//discussIT//NONSGML 1.0//EN\r\n";
echo "VERSION: 2.0\r\n";
echo "METHOD:PUBLISH\r\n";
echo "X-WR-CALNAME:discussIT calendar for $regcode\r\n";
// Iterate over each calendar event. We'll be creating VEVENTs for each module for the opening, comment closing, and critique closing.
foreach ($events as $event) {
  // Set some helper variables
  $moduleName = $event['thingname'];
  /// Format the event dates (convert SQL timestamp to ISO 8601)
  $opendate = date('Ymd\THis', strtotime($event['commentsopendate']));
  $commentsclosedate = date('Ymd\THis', strtotime($event['commentsclosedate']));
  $critiquesclosedate = date('Ymd\THis', strtotime($event['critiquesclosedate']));
  // Create the module open event
  echo "BEGIN:VEVENT\r\n";
  echo "UID:COMMENTSOPEN_$moduleName\r\n";
  echo "SUMMARY:$moduleName opens\r\n";
  echo "LOCATION:$regcode\r\n";
  echo "DTSTAMP:$opendate\r\n";
  echo "DTSTART:$opendate\r\n";
  echo "DTEND:$opendate\r\n";
  echo "END:VEVENT\r\n";
  // Create the module comment closing event
  echo "BEGIN:VEVENT\r\n";
  echo "UID:COMMENTSCLOSE_$moduleName\r\n";
  echo "SUMMARY:$moduleName comments close\r\n";
  echo "LOCATION:$regcode\r\n";
  echo "DTSTAMP:$commentsclosedate\r\n";
  echo "DTSTART:$commentsclosedate\r\n";
  echo "DTEND:$commentsclosedate\r\n";
  echo "END:VEVENT\r\n";
  // Create the module critique closing event
  echo "BEGIN:VEVENT\r\n";
  echo "UID:CRITIQUESCLOSE_$moduleName\r\n";
  echo "SUMMARY:$moduleName critiques close\r\n";
  echo "LOCATION:$regcode\r\n";
  echo "DTSTAMP:$critiquesclosedate\r\n";
  echo "DTSTART:$critiquesclosedate\r\n";
  echo "DTEND:$critiquesclosedate\r\n";
  echo "END:VEVENT\r\n";
}
// Print the ICS footer
echo "END:VCALENDAR";
?>
