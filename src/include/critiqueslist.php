<?php
	
?>

<ul class="critiques">
    <?php
    foreach ($critiques as $critique) {
        if (!empty($critique['critiquetext'])) { ?>
            <li class="critique <?php if ($critique['addstodiscussion'] == 0) { echo "down"; } else { echo "up"; } ?>">
                <span class="critiquetext"><?php echo $critique['critiquetext']; ?></span>
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
                <span class="critiqueauthor">-- <?php echo $critauthor; ?></span>
            </li>
        <?php 	}
    } ?>
</ul>
