<?php if (isset($errors) && sizeof($errors) > 0) { ?>
			<div class="error">The following errors were encountered:
	<?php
				foreach ($errors as $error) {
					echo "<br/>";
					echo "&bullet; $error";
				}
	?>
			</div>
<?php } ?>



<?php if (isset($message)) { ?>
			<div class="message"> 
			<?php echo $message; ?>
			</div>
<?php } ?>



