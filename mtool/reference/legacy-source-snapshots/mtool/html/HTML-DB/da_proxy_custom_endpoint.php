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

$daCustomProxy = new daCustomProxyData();
$daCustomProxy->ProjectPID = trim(GetParam("ProjectPID"));
$daCustomProxy->PID = trim(GetParam("DACustomProxyPID"));
$ReleaseType = trim(GetParam("ReleaseType"));

$NoError = true;

if (!is_numeric($daCustomProxy->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($daCustomProxy->PID)) {
	?>
    <H3><font color="red">Function of Custom Proxy is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

$DAdaCustomProxy = new daCustomProxyDBAccess();
$daCustomProxy = $DAdaCustomProxy->GetdaCustomProxy($daCustomProxy->PID, $daCustomProxy->ProjectPID);
if (!$daCustomProxy) {
	$thisCaption = array($LANG_ENGLISH=>"WARNING! Corresponding Custom Proxy is not exist.", $LANG_JAPANESE=>"WARNING! 該当Custom Proxyが存在しません");
	?>
	<H3><font color="red"><?php print htmlspecialchars($thisCaption[$lang]); ?></font></H3>
	<?php
	$NoError = false;
}

$BuildSourceFuncCache = NULL;
if ($NoError) {
	$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
	$BuildSourceFuncCache = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByCustomProxy($daCustomProxy->ProjectPID, $daCustomProxy->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$CUSTOMPROXYSERVER, $ReleaseType);
	if (!$BuildSourceFuncCache) {
		?>
		<H3><font color="red">ERROR! Unknown No Corresponding Source</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	
	$HeaderCaption = "Endpoint";
	
	printPathOnTopForProxyCustom("Endpoint for Proxy Target [Custom, Multi]", $daCustomProxy->ProjectPID, $daCustomProxy->PID);
	
	$TargetProjectPID = $daCustomProxy->ProjectPID;
	include("endpoint_common_include.php");
}
?>
<br>
<br>
<br>
<p><a href="da_proxy_custom.php?ProjectPID=<?php print urlencode($daCustomProxy->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Custom Proxy List</a></p>

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
