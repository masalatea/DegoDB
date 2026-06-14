<table class="table">
  <thead>
    <tr bgcolor="#ECECEC" class="unsortable">
      <?php if (isset($forSort) && $forSort) { ?>
      <th></th>
      <?php } ?>
      <th>Project Group Type</th>
      <th>Project Group Name Prefix</th>
      <th>Setting Group PID</th>
      <th>Main Server PID</th>
      <th>Server PID</th>
      <th>Dropbox Setting PID</th>
      <th>Apache Host Setting Template PID</th>
      <th>Dropbox Base Dir</th>
      <th>Local Base Dir</th>
      <th>Proxy Base URL</th>
      <th>Uploader URL Suffix</th>
      <th>DB Manager URL Suffix</th>
      <th>Access Control Allow Headers for Proxy Access</th>
      <th>Access Control Allow Headers for Proxy Access</th>
      <?php if (!isset($forSort) || !$forSort) { ?>
      <th></th>
      <?php } ?>
    </tr>
  </thead>
  <tbody id="sortablebodyarea">
    <?php
    for ($i = 0 ; $i < count($ProjectGroupTemplateList); $i++) {
		$ProjectGroupTemplate = $ProjectGroupTemplateList[$i];
		
        ?>
    <tr id="<?php print $ProjectGroupTemplate->PID; ?>">
      <?php if (isset($forSort) && $forSort) { ?>
      <td><?php print ($i + 1); ?></td>
      <?php } ?>
      <td><?php print htmlspecialchars(GetProjectGroupTemplateProjectGroupTypeCaption($ProjectGroupTemplate->ProjectGroupType)); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->ProjectGroupNamePrefix); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->SettingGroupPID); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->MainServerPID); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->ServerPID); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->DropboxSettingPID); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->ApacheHostSettingTemplatePID); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->DropboxBaseDir); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->LocalBaseDir); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->ProxyBaseURL); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->UploaderURLSuffix); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->DBManagerURLSuffix); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->proxy_header_of_access_control_allow_origin); ?></td>
      <td><?php print htmlspecialchars($ProjectGroupTemplate->proxy_header_of_access_control_allow_headers); ?></td>
      <td><?php if (!isset($forSort) || !$forSort) { ?>
        <a href="projectgroup_template_edit.php?PID=<?php print urlencode($ProjectGroupTemplate->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
        <?php } ?></td>
    </tr>
    <?php
    }
    ?>
  </tbody>
</table>
