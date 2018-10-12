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

// Generates and echos a ICS file with events for each module in the calendar.
function generateEventCalendar($events, $regcode) {
  // Set Content-Type to text/calendar
  header('Content-Type: text/calendar');
  // Set Content-Disposition to attachment and .ics extension
  // This will save it as the correct file extension and allow for better
  // browser behavior when downloading the file directly
  header('Content-Disposition: attachment; filename="'.$regcode.'.ics"');
  // Print the ICS header
  echo "BEGIN:VCALENDAR\r\n";
  echo "PRODID:-//discussIT//NONSGML 1.0//EN\r\n";
  echo "VERSION: 2.0\r\n";
  echo "METHOD:PUBLISH\r\n";
  echo "X-WR-CALNAME:discussIT calendar for $regcode\r\n";
  // For each calendar event, make some events
  foreach ($events as $event) {
    // Set some helper variables
    $moduleName = $event['thingname'];
    /// Format the event dates (convert SQL timestamp to sorta-ISO 8601)
    $openDate = date('Ymd\THis\Z', strtotime($event['commentsopendateUTC']));
    $commentsCloseDate = date('Ymd\THis\Z', strtotime($event['commentsclosedateUTC']));
    $critiquesCloseDate = date('Ymd\THis\Z', strtotime($event['critiquesclosedateUTC']));
    // Create the module open event
    generateEvent("MODOPEN_$moduleName", "$regcode - $moduleName opens", $regcode, $openDate, $openDate);
    // Create the module comments close event
    generateEvent("COMCLOSE_$moduleName", "$regcode - $moduleName comments close", $regcode, $commentsCloseDate, $commentsCloseDate);
    // Create the module critiques close event
    generateEvent("CRTCLOSE_$moduleName", "$regcode - $moduleName critiques close", $regcode, $critiquesCloseDate, $critiquesCloseDate);
  }
  // Print the ICS footer
  echo "END:VCALENDAR";
}

// Generates an ICS VEVENT object with the given parameters.
function generateEvent($uid,$summary,$location,$start,$end) {
  echo "BEGIN:VEVENT\r\n";
  echo "UID:$uid\r\n";
  echo "SUMMARY:$summary\r\n";
  echo "LOCATION:$location\r\n";
  echo "DTSTAMP:$start\r\n";
  echo "DTSTART:$start\r\n";
  echo "DTEND:$end\r\n";
  echo "END:VEVENT\r\n";
}

// Generate an event calendar given the current regcode and that regcode's events
generateEventCalendar($events, $regcode);
?>
