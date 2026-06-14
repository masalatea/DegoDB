<?php
function print_DB_boolean_string($value)
{
	if ($value == 1) {
		print "Yes";
	} else {
		print "No";
	}
}
?>

<table class="table">
  <thead>
    <tr bgcolor="#ECECEC" class="unsortable">
      <?php if (isset($forSort) && $forSort) { ?>
      <th></th>
      <?php } ?>
      <th>Username</th>
      <th>Project Group Type</th>
      <th>Name</th>
      <th>Unique Dir Name</th>
      <th>Unique DB Name</th>
      <th>Unique DB User Name</th>
      <th>Create Setting Group?</th>
      <th>Create Setting Group User?</th>
      <th>Create Server?</th>
      <th>Create Main Server?</th>
      <th>Create DB Connection?</th>
      <th>Create DB User?</th>
      <th>Create DB User Client Host?</th>
      <th>Create DB User Client Host (from Main Server)?</th>
      <th>Create Dropbox Base Folder?</th>
      <th>Create Apache Setting?</th>
      <th>Create Apache Host Setting?</th>
      <th>Create Build Apache Host Setting?</th>
      <th>Create Upload Server?</th>
      <th>Create Upload Server Path?</th>
      <th>Create Upload Server Path For Beta?</th>
      <th>Create Upload Group?</th>
      <th>Create Upload Group Assigned Server Path?</th>
      <th>Create Upload Group Assigned Server Path For Beta?</th>
      <th>Create Upload Group Assigned User?</th>
      <th>Create Project?</th>
      <th>Create Project Source Output For DA?</th>
      <th>Create Project Source Output For Proxy Server?</th>
      <th>Create Project Source Output For DA For Beta?</th>
      <th>Create Project Source Output For Proxy Server For Beta?</th>
      <th>Create Database?</th>
      <?php if (!isset($forSort) || !$forSort) { ?>
      <th></th>
      <?php } ?>
    </tr>
  </thead>
  <tbody id="sortablebodyarea">
    <?php
    for ($i = 0 ; $i < count($ProjectGroupList); $i++) {
		$ProjectGroup = $ProjectGroupList[$i];
		
        ?>
    <tr id="<?php print $ProjectGroup->PID; ?>">
      <?php if (isset($forSort) && $forSort) { ?>
      <td><?php print ($i + 1); ?></td>
      <?php } ?>
      <td><?php print htmlspecialchars($ProjectGroup->username); ?></td>
      <td><?php print htmlspecialchars(GetProjectGroupProjectGroupTypeCaption($ProjectGroup->ProjectGroupType)); ?></td>
      <td><?php print htmlspecialchars($ProjectGroup->Name); ?></td>
      <td><?php print htmlspecialchars($ProjectGroup->UniqueDirName); ?></td>
      <td><?php print htmlspecialchars($ProjectGroup->UniqueDBName); ?></td>
      <td><?php print htmlspecialchars($ProjectGroup->UniqueDBUserName); ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateSettingGroup));
				  if ($ProjectGroup->CreateSettingGroup == 1) {
					  if ($ProjectGroup->SettingGroupName != "") {
						  print "(PID: " . $ProjectGroup->SettingGroupPID . ")";
						  ?>
						  <br />
						  [<a href="/settings/setting_group_edit.php?SettingGroupPID=<?php print urlencode($ProjectGroup->SettingGroupPID); ?>&<?php print makeRandStr(8); ?>">Edit</a>]
						  [<a href="/settings/setting_group_user_edit.php?SettingGroupPID=<?php print urlencode($ProjectGroup->SettingGroupPID); ?>&<?php print makeRandStr(8); ?>">User</a>]
						  <?php
					  } else {
						  // $ProjectGroup->SettingGroupNameが空文字列の場合もここに来るが、まあ、とりあえず良しとする
						  ?>
                          [Not Exist (deleted?)]
                          <?php
					  }
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateSettingGroupUser));
				  if ($ProjectGroup->CreateSettingGroupUser == 1) {
					  print "(PID: " . $ProjectGroup->SettingGroupUserPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateServer));
				  if ($ProjectGroup->CreateServer == 1) {
					  print "(PID: " . $ProjectGroup->ServerPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateMainServer));
				  if ($ProjectGroup->CreateMainServer == 1) {
					  print "(PID: " . $ProjectGroup->MainServerPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateDBConnection));
				  if ($ProjectGroup->CreateDBConnection == 1) {
					  print "(PID: " . $ProjectGroup->DBConnectionPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateDBUser));
				  if ($ProjectGroup->CreateDBUser == 1) {
					  print "(PID: " . $ProjectGroup->DBUserPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateDBUserClientHost));
				  if ($ProjectGroup->CreateDBUserClientHost == 1) {
					  print "(PID: " . $ProjectGroup->DBUserClientHostPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateDBUserClientHostFromMain));
				  if ($ProjectGroup->CreateDBUserClientHostFromMain == 1) {
					  print "(PID: " . $ProjectGroup->DBUserClientHostPIDFromMain . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateDropboxBaseFolder));
				  if ($ProjectGroup->CreateDropboxBaseFolder == 1) {
					  print "(PID: " . $ProjectGroup->DropboxBaseFolderPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateApacheSetting));
				  if ($ProjectGroup->CreateApacheSetting == 1) {
					  print "(PID: " . $ProjectGroup->ApacheSettingPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateApacheHostSetting));
				  if ($ProjectGroup->CreateApacheHostSetting == 1) {
					  print "(PID: " . $ProjectGroup->ApacheHostSettingPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateBuildApacheHostSetting)); ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadServer));
				  if ($ProjectGroup->CreateUploadServer == 1) {
					  print "(PID: " . $ProjectGroup->UploadServerPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadServerPath));
				  if ($ProjectGroup->CreateUploadServerPath == 1) {
					  print "(PID: " . $ProjectGroup->UploadServerPathPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadServerPathForBeta));
				  if ($ProjectGroup->CreateUploadServerPathForBeta == 1) {
					  print "(PID: " . $ProjectGroup->UploadServerPathPIDForBeta . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadGroup));
				  if ($ProjectGroup->CreateUploadGroup == 1) {
					  print "(PID: " . $ProjectGroup->UploadGroupPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadGroupAssignedServerPath));
				  if ($ProjectGroup->CreateUploadGroupAssignedServerPath == 1) {
					  print "(PID: " . $ProjectGroup->UploadGroupAssignedServerPathPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadGroupAssignedServerPathForBeta));
				  if ($ProjectGroup->CreateUploadGroupAssignedServerPathForBeta == 1) {
					  print "(PID: " . $ProjectGroup->UploadGroupAssignedServerPathPIDForBeta . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateUploadGroupAssignedUser));
				  if ($ProjectGroup->CreateUploadGroupAssignedUser == 1) {
					  print "(PID: " . $ProjectGroup->UploadGroupAssignedUserPID . ")";
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateProject));
				  if ($ProjectGroup->CreateProject == 1) {
					  if ($ProjectGroup->Projectname != "") {
						  print "(PID: " . $ProjectGroup->ProjectPID . ")";
						  ?>
						  <br />
                          [<a href="/db/project_edit.php?ProjectPID=<?php print urlencode($ProjectGroup->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Edit Project Info</a>]
                          [<a href="/db/project_source_output.php?ProjectPID=<?php print urlencode($ProjectGroup->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Source Output Setting</a>]
                          [<a href="/db/project_security_user_edit.php?ProjectPID=<?php print urlencode($ProjectGroup->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Edit User</a>]
                          [<a href="/db/project_security_detail.php?ProjectPID=<?php print urlencode($ProjectGroup->ProjectPID); ?>&<?php print makeRandStr(8); ?>">User Security</a>]
                          [<a href="/db/project_host_assignment.php?ProjectPID=<?php print urlencode($ProjectGroup->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Host Assignment</a>]
						  <?php
					  } else {
						  // $ProjectGroup->SettingGroupNameが空文字列の場合もここに来るが、まあ、とりあえず良しとする
						  ?>
                          [Not Exist (deleted?)]
                          <?php
					  }
				  }
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateProjectSourceOutputForDA));
	  			 output_project_source_output_html_tag($ProjectGroup->ProjectPID, $ProjectGroup->CreateProjectSourceOutputForDA, $ProjectGroup->DAProjectSourceOutputPID);
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateProjectSourceOutputForProxyServer));
	             output_project_source_output_html_tag($ProjectGroup->ProjectPID, $ProjectGroup->CreateProjectSourceOutputForProxyServer, $ProjectGroup->ProxyServerProjectSourceOutputPID);
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateProjectSourceOutputForDAForBeta));
	             output_project_source_output_html_tag($ProjectGroup->ProjectPID, $ProjectGroup->CreateProjectSourceOutputForDAForBeta, $ProjectGroup->DAProjectSourceOutputPIDForBeta);
	   ?></td>
      <td><?php print htmlspecialchars(print_DB_boolean_string($ProjectGroup->CreateProjectSourceOutputForProxyServerForBeta));
	             output_project_source_output_html_tag($ProjectGroup->ProjectPID, $ProjectGroup->CreateProjectSourceOutputForProxyServerForBeta, $ProjectGroup->ProxyServerProjectSourceOutputPIDForBeta);
	   ?></td>
      <td><?php print htmlspecialchars($ProjectGroup->CreateDatabase); ?></td>
      <td><?php if (!isset($forSort) || !$forSort) { ?>
        <a href="projectgroup_edit.php?PID=<?php print urlencode($ProjectGroup->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
        <?php } ?></td>
    </tr>
    <?php
    }
    ?>
  </tbody>
</table>

<?php
function output_project_source_output_html_tag($ProjectPID, $CreateFlag, $ProjectSourceOutputPID)
{
	  if ($CreateFlag == 1) {
		  print "(PID: " . $ProjectSourceOutputPID . ")";
		  ?>
          <br />
          [<a href="/db/project_source_output.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">View</a>]
          [<a href="/db/project_source_output_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ProjectSourceOutputPID=<?php print urlencode($ProjectSourceOutputPID); ?>&<?php print makeRandStr(8); ?>">Edit</a>]
          <?php
	  } else {
		  ?>
		  [Not Exist (deleted?)]
		  <?php
	  }
}
?>