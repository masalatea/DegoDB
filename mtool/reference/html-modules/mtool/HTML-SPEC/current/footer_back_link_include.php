<?php
function print_footer_back_link($ProjectPID)
{
	?>
    <p>
    <?php
	if ($ProjectPID != "") {
		?>
        <a href="/spec/?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Specification List for this Project</a> / 
        <?php
	}
	?>
    <a href="/spec/?<?php print makeRandStr(8); ?>">Back to Specification List for all Project</a>
    </p>
    <?php
}
?>
