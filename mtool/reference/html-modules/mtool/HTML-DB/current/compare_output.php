<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_USER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_PROJECT_SOURCE_COMPARE_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");

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
	$IsMtoolProjectOwner = CheckIfMtoolProjectOwner($ProjectPID, $matsuesoft_login_token_id);
	
	printPathOnTopForSourceCompare("Project's Compare Output Setting", $ProjectPID);
	
	$DACompareOutput = new CompareOutputDBAccess();
	$CompareOutputList = $DACompareOutput->GetCompareOutputList($ProjectPID); 
	
	if (count($CompareOutputList) > 0) {
		$forSort = false;
		$forEdit = true;
		include("compare_output_table_include.php");
	} else {
		?>
    <p>none</p>
		<?php
	}
	
	if ($IsMtoolProjectOwner) {
		?>
		<p align="right"><a href="compare_output_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New Compare Output Setting</a></p>
		<?php
	}
	?>
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
