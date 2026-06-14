<table class="table">
  <thead>
    <tr bgcolor="#ECECEC" class="unsortable">
      <?php if (isset($forSort) && $forSort) { ?>
      <th></th>
      <?php } ?>
      <th>Name</th>
      <th>Filename Format</th>
      <th>Access Log Filename Format</th>
      <th>Error Log Filename Format</th>
      <?php if (!isset($forSort) || !$forSort) { ?>
      <th></th>
      <?php } ?>
    </tr>
  </thead>
  <tbody id="sortablebodyarea">
    <?php
    for ($i = 0 ; $i < count($ApacheHostSettingTemplateList); $i++) {
		$ApacheHostSettingTemplate = $ApacheHostSettingTemplateList[$i];
		
        ?>
    <tr id="<?php print $ApacheHostSettingTemplate->PID; ?>">
      <?php if (isset($forSort) && $forSort) { ?>
      <td><?php print ($i + 1); ?></td>
      <?php } ?>
      <td><?php print htmlspecialchars($ApacheHostSettingTemplate->name); ?></td>
      <td><?php print htmlspecialchars($ApacheHostSettingTemplate->FilenameFormat); ?></td>
      <td><?php print htmlspecialchars($ApacheHostSettingTemplate->AccessLogFilenameFormat); ?></td>
      <td><?php print htmlspecialchars($ApacheHostSettingTemplate->ErrorLogFilenameFormat); ?></td>
      <td><?php if (!isset($forSort) || !$forSort) { ?>
        <a href="apache_host_template_setting_edit.php?PID=<?php print urlencode($ApacheHostSettingTemplate->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
        <?php } ?></td>
    </tr>
    <?php
    }
    ?>
  </tbody>
</table>
