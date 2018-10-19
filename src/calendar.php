<?php
	
//set currentPage
$currentPage = "navCal";	

function endsWith($haystack, $needle)
{
    $length = strlen($needle);

    return $length === 0 || 
    (substr($haystack, -$length) === $needle);
}

// Import the application classes
require_once('include/classes.php');

// Create an instance of the Application class
$app = new Application();
$app->setup();

// Declare an empty array of error messages
$errors = array();

// Check for logged in user since this page is protected
$app->protectPage($errors);

// Declare local variables
$commentsopendate = "";
$commentsclosedate = "";
$critiquesclosedate = "";

// Attempt to obtain the list of things
$things = $app->getCalendar($errors);

if (sizeof($things) > 0) {
	
	$events = array();
	$firstdate = time(PHP_INT_MAX);
	$lastdate = time(0);
	foreach ($things as $thing) {
	
		// process the comments open date
		$commentsopendate = strtotime($thing['commentsopendate']);
		if ($commentsopendate < $firstdate) {
			$firstdate = $commentsopendate;
		}
		if ($commentsopendate > $lastdate) {
			$lastdate = $commentsopendate;
		}
		$key = date("Y-m-d", $commentsopendate);
		$dayevents = "";
		if (array_key_exists($key, $events)) {
			$dayevents = $events[$key]['text'];
		}
		$dayevents .= $thing['thingname'] . " opens<hr>";
		$events[$key]['text'] = $dayevents;
	
		// process the comments close date
		$commentsclosedate = strtotime($thing['commentsclosedate']);
		if ($commentsclosedate < $firstdate) {
			$firstdate = $commentsclosedate;
		}
		if ($commentsclosedate > $lastdate) {
			$lastdate = $commentsclosedate;
		}
		$key = date("Y-m-d", $commentsclosedate);
		$dayevents = "";
		if (array_key_exists($key, $events)) {
			$dayevents = $events[$key]['text'];
		}
		$dayevents .= $thing['thingname'] . " comments close<hr>";
		$events[$key]['text'] = $dayevents;
	
		// process the critiques close date
		$critiquesclosedate = strtotime($thing['critiquesclosedate']);
		if ($critiquesclosedate < $firstdate) {
			$firstdate = $critiquesclosedate;
		}
		if ($critiquesclosedate > $lastdate) {
			$lastdate = $critiquesclosedate;
		}
		$key = date("Y-m-d", $critiquesclosedate);
		$dayevents = "";
		if (array_key_exists($key, $events)) {
			$dayevents = $events[$key]['text'];
		}
		$dayevents .= $thing['thingname'] . " critiques close<hr>";
		$events[$key]['text'] = $dayevents;
	
	}
	
	// Clear out trailing <hr> tags
	foreach ($events as &$event) {
		if (endsWith($event['text'], "<hr>")) {
			$event['text'] = substr($event['text'], 0, strlen($event['text']) - 4);
		}
	}
	
	$firstdateshort = date("Y-m", $firstdate);
	$lastdateshort = date("Y-m", $lastdate);
	
	$calendarHtml = "";
	do {
		
		// Move to next month
		if ($firstdateshort != $lastdateshort) {
			// build the calendar html
			$calendarHtml .= "<h2>";
			$calendarHtml .= date("F", $firstdate);
			$calendarHtml .= " ";
			$calendarHtml .= date("Y", $firstdate);
			$calendarHtml .= "</h2>";
			$calendarHtml .= $app->build_html_calendar(date("Y", $firstdate), date("m", $firstdate), $events);
			$firstdate = strtotime("+1 month", $firstdate);
			$firstdateshort = date("Y-m", $firstdate);
		} else {
			break;
		}
	} while ($firstdateshort != $lastdateshort);
	
	// Catch the last month
	$calendarHtml .= "<h2>";
	$calendarHtml .= date("F", $firstdate);
	$calendarHtml .= " ";
	$calendarHtml .= date("Y", $firstdate);
	$calendarHtml .= "</h2>";
	$calendarHtml .= $app->build_html_calendar(date("Y", $firstdate), date("m", $firstdate), $events);

}


?>

<!doctype html>
<html lang="en">
<?php include 'include/head.php'; ?>
<body>
	<?php include 'include/header.php'; ?>
	<div id="barba-wrapper">
	<div class="barba-container" data-id="navCal">
	<main id="wrapper">
		<h2>
			Calendar
			<a href="calendarFeed.php?regcode=<?php echo $loggedinuserregistrationcode; ?>&v=<?php echo $app->getVersion(); ?>" class="note no-barba download">
				<i class="fa fa-download menuIcons" style="margin-top: 2px; font-size:27px"></i>
			</a>
			<a href="fileviewer.php?file=include/ical.txt" class="ical">What is this?</a>
		</h2>
		<?php include('include/messages.php'); ?>
		<?php if (sizeof($things) > 0) { ?>
		<table class="agenda">
			<tr>
				<th>Topic</th>
				<th>Opens</th>
				<th>Comments Due</th>
				<th>Critiques Due</th>
			</tr>
			<?php foreach ($things as $thing) { ?>
			<tr class="<?php if ($thing['notopen']) { echo "notopen"; } else if ($thing['critiquesclosed']) { echo "critiquesclosed"; }  else if ($thing['commentsclosed']) { echo "commentsclosed"; } else { echo "open"; } ?>">
				<td><?php echo $thing['thingname']; ?></td>
				<td><?php echo date('m/d g:ia', strtotime($thing['commentsopendate'])); ?></td>
				<td><?php echo date('m/d g:ia', strtotime($thing['commentsclosedate'])); ?></td>
				<td><?php echo date('m/d g:ia', strtotime($thing['critiquesclosedate'])); ?></td>
			</tr>
			<?php } ?>
		</table>
		<?php echo $calendarHtml; ?>
		<?php } else { ?>
		<h3>No events found.</h3>
		<?php } ?>
	</main>
	</div>
	</div>
	<?php include 'include/footer.php'; ?>
	<?php $app->includeJavascript(array('jquery-3.3.1.min','site','barba','mybarba')); ?>
</body>
</html>
