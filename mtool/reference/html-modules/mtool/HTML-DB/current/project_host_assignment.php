<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_OWNER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_PROJECT_HOST_ASSIGNMENT_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = trim(GetParam("ProjectPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	
	printPathOnTopForProjectHostAssignment("Project's Host Assignment List", $ProjectPID);
	
	$DAProjectHostSetting = new ProjectHostSettingDBAccess();
	$ProjectHostSettingList = $DAProjectHostSetting->GetProjectHostSettingByProjectList($ProjectPID);
	
	if (count($ProjectHostSettingList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Apache Setting</th>
			  <th>Host</th>
			  <th>Virtual Host</th>
			  <th>Template</th>
              <?php if ($DBWritePermission) { ?>
              <th></th>
              <?php } // if DBWritePermission ?>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		
		$TotalColumnCount = 0;
		
		for($i = 0 ; $i < count($ProjectHostSettingList); $i++) {
			$ProjectHostSetting = $ProjectHostSettingList[$i];
			
			?>
			<tr>
			  <td><?php print htmlspecialchars($ProjectHostSetting->ApacheSettingname); ?></td>
			  <td><?php print htmlspecialchars($ProjectHostSetting->ServerLocalServerName); ?></td>
			  <td><?php print htmlspecialchars($ProjectHostSetting->ApacheHostSettingVirtualHostName); ?></td>
			  <td><?php print htmlspecialchars($ProjectHostSetting->ApacheHostSettingTemplatename); ?></td>
              <?php if ($DBWritePermission) { ?>
			  <td><a href="project_host_assignment_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ProjectHostSettingPID=<?php print urlencode($ProjectHostSetting->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
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
    <p align="right"><a href="project_host_assignment_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Update New Host Assignment</a></p>
	<br>
	<br>
	<br>
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
    <?php

}
?>
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
