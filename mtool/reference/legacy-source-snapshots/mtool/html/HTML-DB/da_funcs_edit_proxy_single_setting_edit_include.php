<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = trim(GetParam("ProjectPID"));

// Array Parameter
$dafunc = new dafuncData();
$dafunc->ProjectPID = trim(GetParam("ProjectPID"));
$dafunc->daPID = trim(GetParam("DAPID"));
$dafunc->PID = trim(GetParam("DAFuncPID"));
$dafunc->SingleProxy_AuthType = GetParam("SingleProxy_AuthType");
$dafunc->SingleProxy_SingleGetFuncPID = GetParam("SingleProxy_SingleGetFuncPID");
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$DAdafunc = new dafuncDBAccess();

$DAProjectUser = new ProjectUserDBAccess();

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForProxySingle("Edit Proxy Setting [Single]", $ProjectPID, $dafunc->daPID, $dafunc->PID);
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		if($DAdafunc->UpdateSingleProxySetting($dafunc) === FALSE) {
			// Failed
			?>
			<h3><font color="red">Error! Failed to update. something strange. Please ask administrator if this continues.</font></h3>
			<?php
			$needToLoad = false;
		} else {
			// Success
			if ($mtooldb->affected_rows > 0 ) {
				$updatedSomething = true;
			}
			update_da_LastModifiedDT($dafunc->daPID, $ProjectPID);
		}
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
			<h3><font color="red">Setting was updated</font></h3>
			<?php
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		$dafunc = $DAdafunc->Getdafunc($dafunc->PID, $dafunc->ProjectPID);
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_UPDATE");
	$HeaderCaption = getres("ACTION_UPDATE_PROXY_SINGLE");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_funcs_edit_proxy_single_setting_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		$thisPID = $dafunc->PID;
		$AuthType = $dafunc->SingleProxy_AuthType;
		$SingleGetFuncPID = $dafunc->SingleProxy_SingleGetFuncPID;
		$FormKeyNameForAuthType = "SingleProxy_AuthType";
		$FormKeyNameForSingleGetFuncPID = "SingleProxy_SingleGetFuncPID";
		include_once("proxy_auth_common_include.php");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			</div>
		</div>
		<?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafunc->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafunc->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafunc->PID); ?>">
		<?php
		// == END OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		</form>
		<?php
	}
	?>
	<br>
	<br>
	<br>
	<?php
	// == START OF EDITABLE AREA FOR "Bottom Links" ==
	?>
	<p><a href="da_edit_proxy_single_target.php?ProjectPID=<?php print urlencode($dafunc->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Proxy Target Setting [Single] for each Table</a> / <a href="da_funcs_edit_proxy_single_setting.php?ProjectPID=<?php print urlencode($dafunc->ProjectPID); ?>&DAPID=<?php print urlencode($dafunc->daPID); ?>&<?php print makeRandStr(8); ?>">Setting List for each function</a> / <a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>

	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
