<ul class="critiques">
    <?php
    foreach ($critiques as $critique) {
        if (!empty($critique['critiquetext'])) { ?>
            <li class="critique <?php if ($critique['addstodiscussion'] == 0) { echo "down"; } else { echo "up"; } ?>" data-id="<?php echo $critique['critiqueid']; ?>">
                <span class="critiquetext <?php if ($critique['overriddenby']) { echo " overridden "; } ?>"><?php echo $critique['critiquetext']; ?></span>
                <?php
                $critauthor = $critique['publicusername'];
                if ($app->getSessionUser($errors)['isadmin'] != 0 || $critique['username'] == $app->getSessionUser($errors)["username"]) {
                    if (!empty($comment['studentname'])) {
                        $critauthor = $critauthor . " (" . $critique['studentname'] . ")";
                    } else {
                        $critauthor = $critauthor . " (" . $critique['username'] . ")";
                    }
                }
                ?>
                <span class="critiqueauthor">
                	-- <?php echo $critauthor; ?>
                </span>
                <span class="critiqueoverride">
                	<?php if ($critique['overriddenby']) { ?>
                		(Negative critique overridden by an instructor)
                	<?php } ?>
                	<?php if ($isadmin && !$critique['overriddenby']) { ?>
                		<a onclick="override(this);" data-id="<?php echo $critique['critiqueid']; ?>">[Override]</a>
                	<?php } ?>
                	<?php if ($isadmin && $critique['overriddenby']) { ?>
                		<a onclick="undoOverride(this);" data-id="<?php echo $critique['critiqueid']; ?>">[Undo override]</a>
                	<?php } ?>
                </span>
            </li>
        <?php 	}
    } ?>
</ul>
