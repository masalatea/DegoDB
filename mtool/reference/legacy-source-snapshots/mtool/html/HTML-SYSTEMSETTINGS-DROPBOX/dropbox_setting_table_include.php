<table class="table">
  <thead>
    <tr bgcolor="#ECECEC" class="unsortable">
      <?php if (isset($forSort) && $forSort) { ?>
      <th></th>
      <?php } ?>
      <th>Name</th>
      <th>for Public</th>
      <th></th>
      <?php if (!isset($forSort) || !$forSort) { ?>
      <th></th>
      <?php } ?>
    </tr>
  </thead>
  <tbody id="sortablebodyarea">
    <?php
    for ($i = 0 ; $i < count($DropboxSettingList); $i++) {
		$DropboxSetting = $DropboxSettingList[$i];
		
        ?>
    <tr id="<?php print $DropboxSetting->PID; ?>">
      <?php if (isset($forSort) && $forSort) { ?>
      <td><?php print ($i + 1); ?></td>
      <?php } ?>
      <td><?php print htmlspecialchars($DropboxSetting->name); ?></td>
      <td><?php 
	  if ($DropboxSetting->IsPublic == 1) {
		  print "Yes";
	  } else {
		  print "No";
	  }
	  ?></td>
      <th>
      <a href="dropbox_oauth2_jump.php?DropboxSettingPID=<?php print urlencode($DropboxSetting->PID); ?>&<?php print makeRandStr(8); ?>">Update Access Token</a>
      <a href="update_access_token.php?PID=<?php print urlencode($DropboxSetting->PID); ?>&<?php print makeRandStr(8); ?>"></a></th>
      <td><?php if (!isset($forSort) || !$forSort) { ?>
        <a href="dropbox_setting_edit.php?PID=<?php print urlencode($DropboxSetting->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
        <?php } ?></td>
    </tr>
    <?php
    }
    ?>
  </tbody>
</table>
