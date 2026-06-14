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
<title><?php print getres("TITLE_DA_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = GetParam("ProjectPID");
$DAPID = GetParam("PID");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAProject = new ProjectDBAccess();
$project = $DAProject->GetProject($ProjectPID);
if (!$project) {
	die("Something strange. Project is not found\n");
}

$BuildSourceCacheByDataClass = NULL;
if ($NoError) {
	$DABuildSourceCache = new BuildSourceCacheDBAccess();
	$BuildSourceCacheByDataClass = $DABuildSourceCache->GetBuildSourceCache($DAPID, $ProjectPID);
	if (!$BuildSourceCacheByDataClass) {
		?>
		<H3><font color="red">ERROR! Unknown No Corresponding Source</font></H3>
		<?php
		$NoError = false;
	}
}

$ShowSourceLink = $project->Getoption_show_source();
if (!$ShowSourceLink) {
	?>
	<H3><font color="red">ERROR! This project can't display Source</font></H3>
	<?php
	$NoError = false;
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	printPathOnTopForDBAccessClass("DB Access Class: " . $BuildSourceCacheByDataClass->Filename, $ProjectPID, "", "", "", "", "", "", "");
	
	?>
    <pre><?php print htmlspecialchars($BuildSourceCacheByDataClass->SourceCode); ?></pre>
    <?php
	include("source_comment_include.php");
	?>
    <br>
    <br>
    <br>
    <p><a href="./da.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Database Class List</a> / <a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
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
