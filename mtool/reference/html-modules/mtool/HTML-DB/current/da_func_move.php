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
<title><?php print getres("TITLE_DA_FUNC_MOVE"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));

$MoveTarget = trim(GetParam("MoveTarget"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAFuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}

$DAdafunc = new dafuncDBAccess();
$dafunc = NULL;

$DAda = new daDBAccess();
$dalist = NULL; 

if ($NoError) {
	$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
	
	if ($dafunc == NULL) {
		?>
		<H3><font color="red">ERROR! Function is not found</font></H3>
		<?php
		$NoError = false;
	}
	
}	
if ($NoError) {
	
	printPathOnTopForDBAccessClass("Move function", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	if ($UPDATE != NULL) {
		if (!is_numeric($MoveTarget)) {
			?>
			<H3><font color="red">ERROR! Move Target is not set</font></H3>
			<?php
			$NoError = false;
		}
		if ($NoError) {
			
			$mtooldb->autocommit(false);
			$mtooldb->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
			
			$DAdafunc = new dafuncDBAccess();
			$DAdafuncinserttargetfields = new dafuncinserttargetfieldsDBAccess();
			$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
			$DAdafuncselectwhereDBAccess = new dafuncselectwhereDBAccess();
			$DAdafuncupdatedeletewhereDBAccess = new dafuncupdatedeletewhereDBAccess();
			$DAdafuncupdatetargetfieldsDBAccess = new dafuncupdatetargetfieldsDBAccess();
			
			update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);		// Original Target
			
			if ($DAdafunc->UpdateDAPIDforMovingFunction($MoveTarget, $ProjectPID, $DAFuncPID) &&
				$DAdafuncinserttargetfields->UpdateDAPIDforMovingFunction($MoveTarget, $ProjectPID, $DAFuncPID) &&
				$DAdafuncselecttargetfields->UpdateDAPIDforMovingFunction($MoveTarget, $ProjectPID, $DAFuncPID) &&
				$DAdafuncselectwhereDBAccess->UpdateDAPIDforMovingFunction($MoveTarget, $ProjectPID, $DAFuncPID) &&
				$DAdafuncupdatedeletewhereDBAccess->UpdateDAPIDforMovingFunction($MoveTarget, $ProjectPID, $DAFuncPID) &&
				$DAdafuncupdatetargetfieldsDBAccess->UpdateDAPIDforMovingFunction($MoveTarget, $ProjectPID, $DAFuncPID))
			{
				update_da_LastModifiedDT($DAPID, $ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);	// Destination Target
				
				$mtooldb->commit();
				?>
                <h3><font color="red">Moved function</font></h3>
                
                <p>Cation! Store Data Class Target is not updated. You need to update target Data Class if necessary (especially when you are going to combine table and store combined fields into new "inherited" data class).</p>
                <?php
				
			} else {
				$mtooldb->rollback();
				?>
                <h3><font color="red">Failed to Move function</font></h3>
                <?php
			}
			$mtooldb->autocommit(true);
		}
		
	} else {
		
		$dalist = $DAda->GetdaList($ProjectPID); 
		
		?>
		<h3>Move Function: <?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); ?></h3>
		<form action="da_func_move.php" method="post">
		<?php
		
		$move_target_list = array();
		for($i = 0 ; $i < count($dalist); $i++) {
			$thisda = $dalist[$i];
			array_push($move_target_list,
					array("VALUE"=>$thisda->PID, "CAPTION"=>$thisda->name)
				);
		}
		mtoolCommonFormSelect("MoveTarget", $DAFuncPID,
			array($LANG_ENGLISH=>"Move Target", $LANG_JAPANESE=>"移動先"),
			array($LANG_ENGLISH=>"Please select Move Target", $LANG_JAPANESE=>"移動先を選択して下さい"),
			$move_target_list, array(), "");
		?>
		
		<div class="row">
		  <label class="col-md-3 control-label" for="inputtext"></label>
		  <div class="col-md-9"><input name="UPDATE" type="submit" value="Move"></div>
		</div>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($DAPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($DAFuncPID); ?>">
		</form>
    <?php
	}
	?>
    <br>
    <br>
    <br>
    <p><a href="da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
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
