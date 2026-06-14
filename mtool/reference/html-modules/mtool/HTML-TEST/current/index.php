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

$ProjectPID = GetParam("ProjectPID");		// Optional for top page. Mandatory for sub pages
$filterTestGroupPID = trim(GetParam("filterTestGroupPID"));

if (is_numeric($filterTestGroupPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific Test Group</i></font></h3>
    <?php
}

InitializeOutputShortenedStringWithExpansion();

$HeaderCaption = "Test Group List for all Project";
if ($ProjectPID != "") {
	
	$DAProject = new ProjectDBAccess();
	$thisProjectObj = $DAProject->GetProject($ProjectPID);
	if ($thisProjectObj) {
		$HeaderCaption = "Test Group List for Project: " . $thisProjectObj->name;
	} else {
		die("Fatal Error. Unknown Project");
	}
}
printPathOnTopForTest($HeaderCaption, "", "", "", "");

$DAProject = new ProjectDBAccess();
$projectlist = $DAProject->GetProjectList();

$DATestGroup_leftouterjoin_Project = new TestGroup_leftouterjoin_ProjectDBAccess();
$TestGroupList = $DATestGroup_leftouterjoin_Project->GetTestGroupByOwnerOrUserSecurityList($matsuesoft_login_token_id);

if (count($TestGroupList) > 0) {
	?>
	<table class="table">
		<thead>
		<tr bgcolor="#ECECEC">
		  <th>Project</th>
		  <th>Test Group Name</th>
		  <th>Unit Test's Template Base Dir (on DropBox)</th>
		  <th>Unit Test's Working Dir (on DropBox)</th>
		  <th></th>
		  <th></th>
		</tr>
        </thead>
        <tbody>
	<?php
	for($i = 0 ; $i < count($TestGroupList); $i++) {
		$TestGroup = $TestGroupList[$i];
		
		if ($ProjectPID == "" || $ProjectPID == $TestGroup->ProjectPID) {
			// OK
		} else {
			// Skip
			continue;
		}
		
		// Security Check
		if (!CheckIfPossibleToAccess($TestGroup->ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$TESTTOOLREAD)) {
			continue;
		}
		
		// filter
		if (is_numeric($filterTestGroupPID)) {
			if ($filterTestGroupPID != $TestGroup->PID) {
				continue;
			}
		}
		?>
		<tr>
		  <td><?php 
		  $project = NULL;
		  for($j = 0 ; $j < count($projectlist); $j++) {
			  $thisProject = $projectlist[$j];
			  if ($thisProject->PID == $TestGroup->ProjectPID) {
				  $project = $thisProject;
				  break;
			  }
		  }
		  if ($project != "") {
			  print $project->name;
		  }
		  ?></td>
		  <td><?php print htmlspecialchars($TestGroup->name); ?></td>
		  <td><?php print htmlspecialchars($TestGroup->UnitTestTemplateBaseDir); ?></td>
		  <td><?php print htmlspecialchars($TestGroup->UnitTestWorkingDir); ?></td>
		  <td><a href="tests.php?ProjectPID=<?php print urlencode($TestGroup->ProjectPID); ?>&TestGroupPID=<?php print urlencode($TestGroup->PID); ?>&<?php print makeRandStr(8); ?>">View Test(s)</a></td>
		  <td><a href="testgroup_edit.php?ProjectPID=<?php print urlencode($TestGroup->ProjectPID); ?>&TestGroupPID=<?php print urlencode($TestGroup->PID); ?>&<?php print makeRandStr(8); ?>">Edit Group Info</a></td>
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
<p align="right"><a href="testgroup_add.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New Test Group</a></p>

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
