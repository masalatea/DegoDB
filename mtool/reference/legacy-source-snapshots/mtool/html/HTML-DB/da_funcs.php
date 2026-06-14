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
<title><?php print getres("TITLE_DA_FUNC_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_proxy.php");
include_once("da_func_select_target_fields_update_list_order_lib.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));

$filterdafuncPID = trim(GetParam("filterdafuncPID"));

if (is_numeric($filterdafuncPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific DB Access Function</i></font></h3>
    <?php
}

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Class PID</font></H3>
    <?php
	$NoError = false;
}
$project = NULL;
if ($NoError) {
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	if ($project) {
		// OK
	} else {
		?>
		<h3>ERROR. Unknown Project ID</h3>
		<?php
		$NoError = false;
	}
}

InitializeOutputShortenedStringWithExpansion();

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForDBAccessClass("Function List", $ProjectPID, $DAPID, "", "", "", "", "", "");
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	if (!$project) {
		die("Something strange. Project is not found\n");
	}
	$ShowSourceLink = $project->Getoption_show_source();
	
	$DAdafunc = new dafuncDBAccess();
	$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $DAPID); 
	
	if (count($dafunclist) > 0) {
		$forList = true;
		$forSetProxyTarget = false;
		$forSetProxySetting = false;
		include_once("da_funcs_table_include.php");
		
		for($i = 0 ; $i < count($dafunclist); $i++) {
			$dafunc = $dafunclist[$i];
			
			switch($dafunc->ActionType) {
				case dafuncActionTypeEnum::$SELECTSINGLE:
				case dafuncActionTypeEnum::$SELECTLIST:
				
				adjust_list_order_of_select_target_fields_and_show_message($ProjectPID, $DAPID, $dafunc->PID);
				break;
			}
		}
		synchronize_mtool_proxy_if_automatic($dafunc->ProjectPID, $DAPID);
		
	} else {
		?>
<p>none</p>
		<?php
	}
	?>
	<?php if ($DBWritePermission) { ?>
        <p align="right"><a href="da_func_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Add New DB Access Function</a></p>
        
        <?php if ($project->Getoption_user_can_change_da_func_order()) { ?>
            <p align="right"><a href="da_funcs_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Change Function's Order</a></p>
        <?php } ?>
	<?php } // if DBWritePermission ?>
    
    <br>
    <br>
    <br>
    <p><a href="./da.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Class List</a></p>
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
