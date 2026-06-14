<?php
function print_footer_back_link($ProjectPID)
{
	?>
    <p>
    <?php
	if ($ProjectPID != "") {
		?>
        <a href="/chat/?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Topic List for this Project</a> / 
        <?php
	}
	?>
    <a href="/chat/?<?php print makeRandStr(8); ?>">Back to Topic List for all Project</a>
    </p>
    <?php
}
?>
