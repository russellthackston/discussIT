<?php 
	if (isset($app)) {
		$version = $app->getVersion();
	} else {
		$version = 1;
	}
?>
<head>
	<meta charset="utf-8">
	<title>DiscussIT : <?php if (isset($app->getSessionUser($errors)["description"])){echo $app->getSessionUser($errors)["description"];}?></title>
	<meta name="description" content="DiscussIT - A discussion site for <?php if (isset($app->getSessionUser($errors)["description"])){echo $app->getSessionUser($errors)["description"];}?> class">
	<meta name="author" content="Russell Thackston">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="css/style.css?v=<?php echo $version; ?>">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Mali|Cormorant+Unicase|Covered+By+Your+Grace|Nixie+One|Nosifer" >
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
