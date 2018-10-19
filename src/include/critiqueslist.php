<ul class="critiques">
    <?php
    foreach ($critiques as $critique) {
        if (!empty($critique['critiquetext'])) { ?>
            <li class="critique <?php if ($critique['addstodiscussion'] == 0) { echo "down"; } else { echo "up"; } ?> <?php if ($critique['isadmin'] == 1) { echo "instructor"; } ?>" data-id="<?php echo $critique['critiqueid']; ?>">
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
                	<?php if ($app->getSessionUser($errors)['isadmin'] != 0) { ?>
                		<input type="button" value="<?php echo ($critique['overriddenby']?'Undo Override':'Override'); ?>" onclick="return override(this);" data-critique-id="<?php echo $critique['critiqueid']; ?>" data-state="<?php echo ($critique['overriddenby']?'overridden':'notoverridden'); ?>"/>
                  	<?php } ?>
                </span>
            </li>
        <?php 	}
    } ?>
</ul>
