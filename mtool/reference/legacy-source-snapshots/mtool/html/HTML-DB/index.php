<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_EMAIL_VERIFY_AFTER_LOGIN = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_setting_group.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_sandbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
// include_once("project_sync_dropbox_lib.php");

$ProjectPID = GetParam("ProjectPID");		// Optional for top page. Mandatory for sub pages

$HeaderCaption = "DB Settings for All Project";
if ($ProjectPID != "") {
	
	$DAProject = new ProjectDBAccess();
	$thisProjectObj = $DAProject->GetProject($ProjectPID);
	if ($thisProjectObj) {
		$HeaderCaption = "DB Settings for Project: " . $thisProjectObj->name;
	} else {
		die("Fatal Error. Unknown Project");
	}
}
printPathOnTopForDBAccessClass($HeaderCaption, "", "", "", "", "", "", "", "");

InitializeOutputShortenedStringWithExpansion();

$DAProject = new ProjectDBAccess();
$projectlist = $DAProject->GetProjectbyOwnerOrUserSecurityList($matsuesoft_login_token_id); 

$AnyShowDetailOfProject = false;

$target_projectlist = array();
for($i = 0 ; $i < count($projectlist); $i++) {
	$project = $projectlist[$i];
	
	if ($ProjectPID == "" || $ProjectPID == $project->PID) {
		// OK
	} else {
		// Skip
		continue;
	}
	
	// Security Check
	if (!CheckIfPossibleToAccess($project->PID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLREAD)) {
		continue;
	}
	
	if ($project->Getoption_show_detail()) {
		$AnyShowDetailOfProject = true;
	}
	
	array_push($target_projectlist, $project);
}

if (count($target_projectlist) > 0) {
	?>
	<table class="table">
		<thead>
		<tr bgcolor="#ECECEC">
		  <th>Name</th>
          <?php if ($AnyShowDetailOfProject) { ?>
			  <th>Storage Type</th>
          <?php } ?>
		  <th>DB Table</th>
		  <th>Data Class</th>
		  <th>DB Access Class</th>
		  <th></th>
		  <th></th>
		  <th></th>
		  <th></th>
		  <th></th>
		</tr>
		</thead>
		<tbody>
	<?php
	
	for($i = 0 ; $i < count($target_projectlist); $i++) {
		$project = $target_projectlist[$i];
		
		$ShowDetailOfProject = $project->Getoption_show_detail();
		
		$DBWritePermission = CheckIfPossibleToAccess($project->PID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);		
		$IsMtoolProjectOwner = CheckIfMtoolProjectOwner($project->PID, $matsuesoft_login_token_id);
		
		$IncludeProxy = CheckIfProjectIncludeProxy($project->PID);
		$IncludeHtml = CheckIfProjectIncludeHtml($project->PID);
		$IncludeLanguageResource = CheckIfProjectIncludeLanguageResource($project->PID);
		$show_source_output_setting = ($project->option_show_source_output_setting == 1);
		$show_language_resource = ($project->option_show_language_resource == 1);
		$show_compare_output = ($project->option_IsCompareOutputTarget == 1);
		
		$ShowProxyLink = $project->Getoption_show_proxy_link();
		
		?>
		<tr>
		  <td><?php print htmlspecialchars($project->name); ?></td>
          <?php if ($AnyShowDetailOfProject) { ?>
			  <td><?php
				if ($ShowDetailOfProject) {
					print htmlspecialchars($project->StorageType);
				}
			  ?></td>
          <?php } ?>
		  <td><a href="dbtables.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">View DB Table(s)</a>
			  <?php
			  if ($DBWritePermission) {
				  if ($project->DBConnectionDBServerType != "") {
				  ?>
				  <br>
				  [<a href="dbtables_import.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Import from DB (Table and Column)</a>]
				  <?php
				  }
				  if ($project->DBManagerURL != ""){
				  ?>
				  <br>
                  [<a href="<?php print $project->DBManagerURL; ?>">DB Manager</a>] <?php OutputShortenedStringWithExpansionWithMessage("ID: \"" . $project->DBUserUser . "\" Password: \"" . $project->DBUserPassword . "\"", 0, false, "[Show ID/Password]", "[Hide]", ""); ?>
				  <?php
				  }
			  } // if DBWritePermission
			  ?>
          </td>
		  <td><a href="dataclasses.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">View Data Class(es)</a>
		  <?php if ($DBWritePermission) { ?>
		  <br>
		  [<a href="dataclasses_sync.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Sync with DB Table (Class and Field)</a>]
          <?php } // if DBWritePermission ?>
		</td>
		  <td><a href="da.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Database Access Class(es)</a>
		  <?php if ($DBWritePermission) { ?>
		  <br>
		  [<a href="da_sync.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Sync with Data Class (Class itself only)</a>]
          <?php } // if DBWritePermission ?>
		  </td>
		  <?php if ($DBWritePermission) { ?>
              <td>
			  <?php if ($IncludeProxy && $ShowProxyLink) { ?>
                  <a href="da_edit_proxy_single_target.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Proxy Target Setting [Single]</a><br>
                  <a href="da_proxy_custom.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Proxy Target Setting [Multi, Custom]</a>
              <?php } ?>
              </td>
              <td>
			  <?php if ($IncludeHtml) { ?>
	              <a href="htmls.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Html List</a>
              <?php } ?>
              </td>
              <td>
			  <?php if ($show_language_resource && $IncludeLanguageResource) { ?>
	              <a href="lang_res.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Language Resource</a>
              <?php } ?>
              </td>
              <td><a href="build_project.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Build</a><br>
                  [<a href="build_project_for_each.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Build for Each</a>]
                  
				  <?php if ($show_compare_output) { ?>
	    		  [<a href="compare_output_do.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Compare Output</a>]
	              <?php } ?>
                  </td>
              <td>
              <?php if ($IsMtoolProjectOwner) { ?>
              [<a href="project_edit.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Edit Project Info</a>]
              <?php } ?>
              <?php if ($show_source_output_setting) { ?>
              [<a href="project_source_output.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Source Output Setting</a>]
              <?php } ?>
			  <?php if ($IsMtoolProjectOwner) { ?>
    		  [<a href="project_security_user_edit.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Edit User</a>]
              [<a href="project_security_detail.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">User Security</a>]
              [<a href="project_host_assignment.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Host Assignment</a>]
              <?php } ?>
			  <?php if ($show_compare_output) { ?>
    		  [<a href="compare_output.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Compare Output Setting</a>]
              <?php } ?>
              </td>
		  <?php } // if DBWritePermission ?>
		</tr>
		<?php
	}
	?>
		</tbody>
	</table>
	
	<?php
} else {
	?>
<p>none</p>
	<?php
}
?>
<?php if (check_if_user_is_user_of_any_mtool_setting_group($matsuesoft_login_token_id)) { ?>
	<p align="right"><a href="project_edit.php?<?php print makeRandStr(8); ?>">Add New Project [Only for user of any Setting Group]</a></p>
<?php } ?>

<?php
if (check_if_project_group_is_created_for_this_mtool_user($matsuesoft_login_token_id, ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX) &&
	check_if_project_group_is_created_for_this_mtool_user($matsuesoft_login_token_id, ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER))
{
	// OK
} else {


	if (false) {		// for the time being


	?>
	<p align="right"><a href="create_project_group.php?<?php print makeRandStr(8); ?>">Create Sandbox and Default Project</a></p>
	<?php

	}

}
?>


<?php if (CheckIfAnyMtoolProjectOwner($matsuesoft_login_token_id)) { ?>
	<p align="right"><a href="project_security.php?<?php print makeRandStr(8); ?>">Project Security Setting [Project Admin Only]</a></p>
<?php } ?>
<br>
<br>
<br>
<br>
<p align="right"><font size="-1"><a href="update_history.php">Update History</a></font></p>
    
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content

