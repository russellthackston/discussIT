<?php function renderHistogram($chartdata, $title) { ?>
	<span class="chart">
		<?php
			$chartheight = max(
				$chartdata['A'],
				$chartdata['B'],
				$chartdata['C'],
				$chartdata['D'],
				$chartdata['F']);
		?>
	    <span class="histogram">
		<?php foreach($chartdata as $grade=>$count) { ?>
			<?php $height = strval(($count / $chartheight) * 100); ?>
			<span style="height: <?php echo $height; ?>%" class="histobar" data-label="<?php echo $grade; ?>" data-count="<?php echo $count; ?>">
				<span class="visuallyhidden">
					There are <?php echo $count; ?> <?php echo $grade; ?>'s.
				</span>
			</span>
		<?php } ?>
	    </span>
		<h4><?php echo $title; ?></h4>
	</span>
<?php } ?>