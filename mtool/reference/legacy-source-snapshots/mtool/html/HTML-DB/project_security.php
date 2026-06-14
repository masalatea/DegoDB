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
<title><?php print getres("TITLE_PROJECT_SECURITY_VIEW"); ?></title>
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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

InitializeOutputShortenedStringWithExpansion();

$DAProject = new ProjectDBAccess();
$projectlist = $DAProject->GetProjectbyOwnerOrUserSecurityList($matsuesoft_login_token_id); 

if (count($projectlist) > 0) {
	?>
	<h3>Project Security List</h3>
	
	<table class="table">
		<thead>
		<tr bgcolor="#ECECEC">
		  <th>Name</th>
		  <th></th>
		</tr>
		</thead>
		<tbody>
	<?php
	for($i = 0 ; $i < count($projectlist); $i++) {
		$project = $projectlist[$i];
		
		// Security Check
		if (!CheckIfMtoolProjectOwner($project->PID, $matsuesoft_login_token_id)) {
			continue;
		}
		
		$IncludeProxy = CheckIfProjectIncludeProxy($project->PID);
		?>
		<tr>
		  <td><?php print htmlspecialchars($project->name); ?></td>
		  <td><a href="project_security_detail.php?ProjectPID=<?php print urlencode($project->PID); ?>&<?php print makeRandStr(8); ?>">Security</a></td>
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
<br>
<br>
<br>
<p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>

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
