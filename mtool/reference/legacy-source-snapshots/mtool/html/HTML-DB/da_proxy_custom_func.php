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
<title><?php print getres("TITLE_DA_EDIT_PROXY_CUSTOM_FUNC"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = GetParam("ProjectPID");
$daCustomProxyPID = GetParam("daCustomProxyPID");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	
	printPathOnTopForProxyCustom("Proxy Target Function List to be called [Custom, Multi]", $ProjectPID, $daCustomProxyPID);
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	
	$IncludeProxy = CheckIfProjectIncludeProxy($ProjectPID);
	
	$DAdaCustomProxy = new daCustomProxyDBAccess();
	$daCustomProxy = $DAdaCustomProxy->GetdaCustomProxy($daCustomProxyPID, $ProjectPID);
	
	$DAdaCustomProxyFunc = new daCustomProxyFuncDBAccess();
	$DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da = new daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccess();
	$daCustomProxyFuncList = $DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da->GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList($ProjectPID, $daCustomProxyPID);
	
	if (count($daCustomProxyFuncList) > 0) {
		$for_list = true;
		$for_sort = !$for_list;
		include_once("da_proxy_custom_func_table_include.php");
        
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
	<p align="right"><a href="da_proxy_custom_func_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxyPID); ?>&<?php print makeRandStr(8); ?>">Add functions to be called</a></p>
    <p align="right"><a href="da_proxy_custom_func_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxyPID); ?>&<?php print makeRandStr(8); ?>">Change Function's Order</a></p>
    <br>
    <br>
    <br>
    <p><a href="da_proxy_custom.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Proxy Target Setting [Multi, Custom] List</a></p>
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
