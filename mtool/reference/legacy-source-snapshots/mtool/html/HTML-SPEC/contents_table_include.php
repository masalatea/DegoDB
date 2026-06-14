<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");
?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th>Depth</th>
			  <th colspan="7">Title</th>
			  <?php if (!isset($forSort) || !$forSort) { ?>
			  <th></th>
              <th></th>
              <th></th>
			  <?php } ?>
			</tr>
          </thead>
            <tbody id="functionlistbodyarea">
		<?php

		for($i = 0 ; $i < count($SpecContentList); $i++) {
			$SpecContent = $SpecContentList[$i];
			
			// filter
			if (is_numeric($filterSpecContentPID)) {
				if ($filterSpecContentPID != $SpecContent->PID) {
					continue;
				}
			}
			?>
			<tr id="<?php print $SpecContent->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
 			  <td><font size="-1"><?php print htmlspecialchars($SpecContent->GetDepthCaption()); ?></font></td>
             <?php
			  $indent = $SpecContent->Depth - 1;		// Depth 0と1はIndentなし
			  if ($indent < 0 ) {
				  $indent = 0;
			  }
			  $restCount = $CONTENT_DEPTH_MAX - $indent;
			  if ($indent > 0 ) {
				  $INDENT_WIDTH = 10;
				  ?>
				  <td colspan="<?php print $indent; ?>" width="<?php print $INDENT_WIDTH * $indent; ?>"></td>
                  <?php
			  }
			  ?>
			  <td colspan="<?php print $restCount; ?>"><?php print htmlspecialchars($SpecContent->Title); ?></td>
			  <?php if (!isset($forSort) || !$forSort) { ?>
			  <td><a href="content_edit.php?ProjectPID=<?php print urlencode($SpecContent->ProjectPID); ?>&SpecPID=<?php print urlencode($SpecContent->SpecPID); ?>&ContentPID=<?php print urlencode($SpecContent->PID); ?>&<?php print makeRandStr(8); ?>">Edit Content Info</a></td>
              <td><?php PrintAddMinutesLinkForSpecContent($SpecContent->ProjectPID, $SpecContent->SpecPID, $SpecContent->PID); ?></td>
              <td><?php PrintSearchMinutesLinkForSpecContent($SpecContent->ProjectPID, $SpecContent->SpecPID, $SpecContent->PID); ?></td>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
