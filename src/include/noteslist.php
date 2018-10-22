<?php $index = 0; ?>
<?php foreach($notes as $note) { ?>
	<div class="instructornote" data-order="<?php echo $index++; ?>">
		<a name="note-<?php echo $index++; ?>" onclick="return doCopyToClipboard(this);" data-text="<?php echo htmlentities($note->text); ?>">[Copy]</a>
		<?php echo $note->text; ?>
	</div>
<?php } ?>
