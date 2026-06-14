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
<title><?php print getres("TITLE_DA_FUNC_PROXY_SERVER_ENDPOINT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("endpoint_lib_include.php");

$dafunc = new dafuncData();
$dafunc->ProjectPID = trim(GetParam("ProjectPID"));
$dafunc->daPID = trim(GetParam("DAPID"));
$dafunc->PID = trim(GetParam("DAFuncPID"));
$ReleaseType = trim(GetParam("ReleaseType"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafunc->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafunc->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafunc->PID)) {
	?>
    <H3><font color="red">Function of DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

$DAdafunc = new dafuncDBAccess();
$dafunc = $DAdafunc->Getdafunc($dafunc->PID, $dafunc->ProjectPID);
if (!$dafunc) {
	$thisCaption = array($LANG_ENGLISH=>"WARNING! Corresponding function is not exist.", $LANG_JAPANESE=>"WARNING! 該当関数が存在しません");
	?>
	<H3><font color="red"><?php print htmlspecialchars($thisCaption[$lang]); ?></font></H3>
	<?php
	$NoError = false;
}

$BuildSourceFuncCache = NULL;
if ($NoError) {
	$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
	$BuildSourceFuncCache = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByDAFunc($dafunc->ProjectPID, $dafunc->daPID, $dafunc->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$PROXYSERVER, $ReleaseType);
	if (!$BuildSourceFuncCache) {
		?>
		<H3><font color="red">ERROR! Unknown No Corresponding Source</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	$HeaderCaption = "Endpoint";
	
	printPathOnTopForDBAccessClass($HeaderCaption, $dafunc->ProjectPID, $dafunc->daPID, $dafunc->PID, "", "", "", "", "");
	
	$TargetProjectPID = $dafunc->ProjectPID;
	include("endpoint_common_include.php");
}
?>
<br>
<br>
<br>
<p><a href="da_funcs.php?ProjectPID=<?php print urlencode($dafunc->ProjectPID); ?>&DAPID=<?php print urlencode($dafunc->daPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>

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
