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
$DAPID      = GetParam("DAPID");
$DAFuncPID  = GetParam("DAFuncPID");
$ReleaseType = trim(GetParam("ReleaseType"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$BuildSourceFuncCache = NULL;
if ($NoError) {
	$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
	$BuildSourceFuncCache = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByDAFunc($ProjectPID, $DAPID, $DAFuncPID, BuildSourceFuncCacheBuildTargetTypeEnum::$DA, $ReleaseType);
	if (!$BuildSourceFuncCache) {
		?>
		<H3><font color="red">ERROR! Unknown No Corresponding Source</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	printPathOnTopForDBAccessClass("DB Access Class's Function Name: " . $BuildSourceFuncCache->FunctionName, $ProjectPID, "", "", "", "", "", "", "");
	
	?>
    <H3>Sample Code</H3>
    <pre>&lt;?php
<?php
if (trim($BuildSourceFuncCache->AutoloadFilename) != "") {
	print "include_once(\"" . htmlspecialchars($BuildSourceFuncCache->AutoloadFilename) . "\");\n";
}
if (trim($BuildSourceFuncCache->ExampleCodeForCreatingObject) != "") {
	print htmlspecialchars($BuildSourceFuncCache->ExampleCodeForCreatingObject) . "\n";
}
?>
$DA<?php print htmlspecialchars($BuildSourceFuncCache->DAName) ?> = new <?php print htmlspecialchars($BuildSourceFuncCache->DAClassName) ?>();
$DA<?php print htmlspecialchars($BuildSourceFuncCache->DAName) ?>-&gt;<?php print htmlspecialchars($BuildSourceFuncCache->FunctionName) ?>(<?php print htmlspecialchars($BuildSourceFuncCache->ParameterListString) ?>);
?&gt;</pre>
    
    <h3>Source</h3>
    <pre><?php print htmlspecialchars($BuildSourceFuncCache->SourceCode); ?></pre>
    <?php
	include("source_comment_include.php");
	?>
    <br>
    <br>
    <br>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a> / <a href="./da.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Database Class List</a> / <a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
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
