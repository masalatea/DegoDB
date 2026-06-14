<?php
function print_footer_back_link($ProjectPID)
{
	?>
    <p>
    <?php
	if ($ProjectPID != "") {
		?>
        <a href="/test/?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Test Group List for this Project</a> / 
        <?php
	}
	?>
    <a href="/test/?<?php print makeRandStr(8); ?>">Back to Test Group List for all Project</a>
    </p>
    <?php
}
?>
