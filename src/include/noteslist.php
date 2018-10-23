<?php $index = 1; ?>
<?php foreach($notes as $note) { ?>
	<div class="instructornote" data-order="<?php echo $index++; ?>">
		<button name="addnote-<?php echo $index++; ?>" onclick="return doCopyToClipboard(this);" data-text="<?php echo htmlentities($note->text); ?>" vaue="Copy">Copy</button>
		<button name="delnote-<?php echo $index++; ?>" onclick="return deleteNote(this);" data-noteid="<?php echo $note->id; ?>" vaue="Delete">Delete</button>
		<pre><?php echo htmlentities($note->text); ?></pre>
	</div>
<?php } ?>
