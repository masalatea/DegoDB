<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_compare_output.php");
?>

		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th colspan="2">Path A</th>
			  <th colspan="2">Path B</th>
			  <?php if (isset($forEdit) && $forEdit) { ?>
				  <th></th>
			  <?php } ?>
			</tr>
            </thead>
            <tbody id="sortablebodyarea">
		<?php
		for($i = 0 ; $i < count($CompareOutputAdditionalPathList); $i++) {
			$CompareOutputAdditionalPath = $CompareOutputAdditionalPathList[$i];
			?>
			<tr id="<?php print $CompareOutputAdditionalPath->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
			  <td><?php print htmlspecialchars($CompareOutputAdditionalPath->DropboxBaseFolderAName); ?></td>
			  <td><?php print htmlspecialchars($CompareOutputAdditionalPath->PathA); ?></td>
			  <td><?php print htmlspecialchars($CompareOutputAdditionalPath->DropboxBaseFolderBName); ?></td>
			  <td><?php print htmlspecialchars($CompareOutputAdditionalPath->PathB); ?></td>
              </td>
			  <?php if (isset($forEdit) && $forEdit) { ?>
              	<?php if ($IsMtoolProjectOwner) { ?>
				  <td><a href="compare_output_additional_path_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&CompareOutputPID=<?php print urlencode($CompareOutputAdditionalPath->CompareOutputPID); ?>&CompareOutputAdditionalPathPID=<?php print urlencode($CompareOutputAdditionalPath->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
			    <?php } ?>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>