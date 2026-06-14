<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_compare_output.php");
?>

		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th>Dropbox Base Folder</th>
			  <th>Output File Path (on Dropbox)</th>
			  <th>Output File Type</th>
			  <th>Compare Path (on Dropbox)</th>
			  <th>Compare Tool File Path</th>
			  <th></th>
			  <?php if (isset($forEdit) && $forEdit) { ?>
				  <th></th>
			  <?php } ?>
			</tr>
            </thead>
            <tbody id="sortablebodyarea">
		<?php
		for($i = 0 ; $i < count($CompareOutputList); $i++) {
			$CompareOutput = $CompareOutputList[$i];
			?>
			<tr id="<?php print $CompareOutput->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
			  <td><?php
				$DropboxBaseFolder = initialize_compare_output($ProjectPID, $CompareOutput->DropboxBaseFolderPID, $matsuesoft_login_token_id);
				if ($DropboxBaseFolder) {
					print htmlspecialchars($DropboxBaseFolder->Name);
				}
			   ?>
			  </td>
			  <td><?php print htmlspecialchars(get_dropbox_folder_path_for_compare_output($DropboxBaseFolder, $CompareOutput->OutputFilePath)); ?></td>
			  <td><?php print htmlspecialchars(GetCompareOutputOutputFileTypeCaption($CompareOutput->OutputFileType)); ?></td>
			  <td><?php print htmlspecialchars(get_dropbox_folder_path_for_compare_output($DropboxBaseFolder, $CompareOutput->ComparePath)); ?></td>
			  <td><?php print htmlspecialchars($CompareOutput->CompareToolFilePath); ?>
              </td>
			  <td><a href="compare_output_additional_path.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&CompareOutputPID=<?php print urlencode($CompareOutput->PID); ?>&<?php print makeRandStr(8); ?>">Additional Path Setting</a></td>
			  <?php if (isset($forEdit) && $forEdit) { ?>
              	<?php if ($IsMtoolProjectOwner) { ?>
				  <td><a href="compare_output_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&CompareOutputPID=<?php print urlencode($CompareOutput->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
			    <?php } ?>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>