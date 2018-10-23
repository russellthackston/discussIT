<?php $index = 1; ?>
<?php foreach($notes as $note) { ?>
	<div class="instructornote" data-order="<?php echo $index++; ?>">
		<button name="note-<?php echo $index++; ?>" onclick="return doCopyToClipboard(this);" data-text="<?php echo htmlentities($note->text); ?>" vaue="Copy">Copy</button>
		<?php echo $note->text; ?>
	</div>
<?php } ?>
