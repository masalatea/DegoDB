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
<title><?php print getres("TITLE_DA_FUNC_EDIT_PROXY_SINGLE_TARGET"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));

$UPDATE = trim(GetParam("UPDATE"));

// Array
$IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID = GetParam("IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID");

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
	
	printPathOnTopForProxySingle("Set Proxy Target [Single]", $ProjectPID, $DAPID, "");
	
	$DAdafunc = new dafuncDBAccess();
	$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $DAPID);
	
	if ($UPDATE != "") {
		
		$DAProjectSourceOutputData = new ProjectSourceOutputDBAccess();
		$ProjectSourceOutputList = $DAProjectSourceOutputData->GetProjectSourceOutputList($ProjectPID);
		
		$DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
		
		for($i = 0 ; $i < count($dafunclist); $i++) {
			$dafunc = $dafunclist[$i];
			
			$dafuncSimpleProxyList = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxySourceOutputTargetList($ProjectPID, $DAPID, $dafunc->PID);
			
			for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
				$ProjectSourceOutput = $ProjectSourceOutputList[$j];
				
				$IsChecked = false;
				if (is_array($IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID)) {
					for ($k = 0 ; $k < count($IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID) ; $k++) {
						$tmp = $IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID[$k];
						if (preg_match("/^(\d+)\-(\d+)$/", $tmp, $matches)) {
							$DAFuncPID = $matches[1];
							$ProjectSourceOutputPID = $matches[2];
							
							if ($DAFuncPID == $dafunc->PID &&
								$ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
								$IsChecked = true;
								
								// print "Checked: " . $ProjectSourceOutput->SourceOutputDir . "<br>\n";
								break;
							}
						}
					}
				}
				
				if ($IsChecked) {
					// Checked for this Source Output
					$NeedToInsert = true;
					for ( $k = 0 ; $k < count($dafuncSimpleProxyList) ; $k++) {
						$dafuncSimpleProxy = $dafuncSimpleProxyList[$k];
						
						if ($dafuncSimpleProxy->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
							$NeedToInsert = false;
							break;
						}
					}
					if ($NeedToInsert) {
						$thisTargetObj = new dafuncSimpleProxySourceOutputTargetData();
						$thisTargetObj->ProjectPID = $ProjectPID;
						$thisTargetObj->daPID = $DAPID;
						$thisTargetObj->dafuncPID = $dafunc->PID;
						$thisTargetObj->ProjectSourceOutputPID = $ProjectSourceOutput->PID;				
						
						if ($DAdafuncSimpleProxySourceOutputTarget->InsertdafuncSimpleProxySourceOutputTarget($thisTargetObj)) {
							// Success
							if (mysqli_affected_rows($mtooldb) > 0) {
								?>
								<h3><font color="red">Added: <?php print $dafunc->name; ?> (<?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); ?>)</font> </h3>
								<?php
							}
							
						} else {
							?>
							<h3><font color="red">Error! Failed to update: <?php print $dafunc->name; ?> (<?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); ?>)</font></h3>
							<?php
							update_da_LastModifiedDT($DAPID, $ProjectPID);
						}
					}
				} else {
					// Not Checked for this Source Output
					$NeedToDelete = false;
					for ( $k = 0 ; $k < count($dafuncSimpleProxyList) ; $k++) {
						$dafuncSimpleProxy = $dafuncSimpleProxyList[$k];
						
						if ($dafuncSimpleProxy->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
							if ($DAdafuncSimpleProxySourceOutputTarget->DeletedafuncSimpleProxySourceOutputTarget($dafuncSimpleProxy)) {
								// Success
								if (mysqli_affected_rows($mtooldb) > 0) {
									?>
									<h3><font color="red">Deleted: <?php print $dafunc->name; ?> (<?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); ?>)</font> </h3>
									<?php
								}
								update_da_LastModifiedDT($DAPID, $ProjectPID);
								
							} else {
								?>
								<h3><font color="red">Error! Failed to update: <?php print $dafunc->name; ?> (<?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); ?>)</font></h3>
								<?php
							}
							break;
						}
					}
				}
			}
		}
		// Initialize Again
		$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $DAPID); 
	}
	
	if (count($dafunclist) > 0) {
		?>
        <form action="da_funcs_edit_proxy_single_target.php" method="post">
        <?php
		$forList = false;
		$forSetProxyTarget = true;
		$forSetProxySetting = false;
		include_once("da_funcs_table_include.php");
		?>
        <input name="ProjectPID" type="hidden" value="<?Php print htmlspecialchars($ProjectPID); ?>">
        <input name="DAPID" type="hidden" value="<?Php print htmlspecialchars($DAPID); ?>">
        </form>
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
	<p><a href="da_edit_proxy_single_target.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Proxy Target Setting [Single]</a></p>
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
