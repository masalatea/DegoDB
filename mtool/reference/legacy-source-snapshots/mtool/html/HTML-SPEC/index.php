<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");

$ProjectPID = GetParam("ProjectPID");		// Optional for top page. Mandatory for sub pages

$filterSpecPID = trim(GetParam("filterSpecPID"));
if (is_numeric($filterSpecPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific Spec</i></font></h3>
    <?php
}

InitializeOutputShortenedStringWithExpansion();

$HeaderCaption = "Spec List for all Project";
if ($ProjectPID != "") {
	
	$DAProject = new ProjectDBAccess();
	$thisProjectObj = $DAProject->GetProject($ProjectPID);
	if ($thisProjectObj) {
		$HeaderCaption = "Spec List for Project: " . $thisProjectObj->name;
	} else {
		die("Fatal Error. Unknown Project");
	}
}
printPathOnTopForSpec($HeaderCaption, "", "", "");

$NoError = true;
if ($NoError) {
	
	$DAProject = new ProjectDBAccess();
	$projectlist = $DAProject->GetProjectList();
	
	$DASpec = new SpecDBAccess();
	$SpecList = $DASpec->GetSpecByOwnerOrUserSecurityList($matsuesoft_login_token_id);
	
	if (count($SpecList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Project</th>
			  <th>Spec Name</th>
			  <th></th>
			  <th></th>
			  <th></th>
			  <th></th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($SpecList); $i++) {
			$Spec = $SpecList[$i];
			
			if ($ProjectPID == "" || $ProjectPID == $Spec->ProjectPID) {
				// OK
			} else {
				// Skip
				continue;
			}
			
			// Security Check
			if (!CheckIfPossibleToAccess($Spec->ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$SPECTOOLREAD)) {
				continue;
			}
			
			// filter
			if (is_numeric($filterSpecPID)) {
				if ($filterSpecPID != $Spec->PID) {
					continue;
				}
			}
			
			?>
			<tr>
			  <td><?php 
			  $project = NULL;
			  for($j = 0 ; $j < count($projectlist); $j++) {
				  $thisProject = $projectlist[$j];
				  if ($thisProject->PID == $Spec->ProjectPID) {
					  $project = $thisProject;
					  break;
				  }
			  }
			  if ($project != "") {
				  print $project->name;
			  }
			  ?></td>
			  <td><?php print htmlspecialchars($Spec->name); ?></td>
			  <td><a href="view.php?ProjectPID=<?php print urlencode($Spec->ProjectPID); ?>&SpecPID=<?php print urlencode($Spec->PID); ?>&<?php print makeRandStr(8); ?>">View All Spec</a></td>
			  <td><a href="contents.php?ProjectPID=<?php print urlencode($Spec->ProjectPID); ?>&SpecPID=<?php print urlencode($Spec->PID); ?>&<?php print makeRandStr(8); ?>">View/Edit Content(s)</a></td>
			  <td><a href="spec_edit.php?ProjectPID=<?php print urlencode($Spec->ProjectPID); ?>&SpecPID=<?php print urlencode($Spec->PID); ?>&<?php print makeRandStr(8); ?>">Edit Base Info</a></td>
              <td><?php PrintAddMinutesLinkForSpec($Spec->ProjectPID, $Spec->PID); ?></td>
              <td><?php PrintSearchMinutesLinkForSpec($Spec->ProjectPID, $Spec->PID); ?></td>
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
    <p align="right"><a href="spec_add.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New Spec</a></p>
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
