<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectUserSecurityDetail = new ProjectUserData();
$ProjectUserSecurityDetail->ProjectPID = trim(GetParam("ProjectPID"));
$ProjectUserSecurityDetail->username = trim(GetParam("username"));
$ProjectUserSecurityDetail->dbtoolRead = trim(GetParam("dbtoolRead"));
$ProjectUserSecurityDetail->dbtoolWrite = trim(GetParam("dbtoolWrite"));
$ProjectUserSecurityDetail->htmlRead = trim(GetParam("htmlRead"));
$ProjectUserSecurityDetail->htmlWrite = trim(GetParam("htmlWrite"));
$ProjectUserSecurityDetail->testtoolRead = trim(GetParam("testtoolRead"));
$ProjectUserSecurityDetail->testtoolWrite = trim(GetParam("testtoolWrite"));
$ProjectUserSecurityDetail->spectoolRead = trim(GetParam("spectoolRead"));
$ProjectUserSecurityDetail->spectoolWrite = trim(GetParam("spectoolWrite"));
$ProjectUserSecurityDetail->ReqRead = trim(GetParam("ReqRead"));
$ProjectUserSecurityDetail->ReqWrite = trim(GetParam("ReqWrite"));
$ProjectUserSecurityDetail->ChatRead = trim(GetParam("ChatRead"));
$ProjectUserSecurityDetail->ChatWrite = trim(GetParam("ChatWrite"));
$ProjectUserSecurityDetail->MinutesRead = trim(GetParam("MinutesRead"));
$ProjectUserSecurityDetail->MinutesWrite = trim(GetParam("MinutesWrite"));
$ProjectUserSecurityDetail->UploadRead = trim(GetParam("UploadRead"));
$ProjectUserSecurityDetail->UploadWrite = trim(GetParam("UploadWrite"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($ProjectUserSecurityDetail->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if ($ProjectUserSecurityDetail->username == "") {
	?>
    <H3><font color="red">Username is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
$DAProjectUser = new ProjectUserDBAccess();
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		if($DAProjectUser->UpdateProjectUserDetail($ProjectUserSecurityDetail) === FALSE) {
			// Failed
			?>
			<h3><font color="red">Error! Failed to update</font></h3>
			<?php
			$needToLoad = false;
			
		} else {
			// Success
			if ($mtooldb->affected_rows > 0 ) {
				$updatedSomething = true;
			}
		}
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
			<h3><font color="red"><?php print getres("ACTION_UPDATED_SECURITY_USER_DETAIL"); ?></font></h3>
			<?php
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		$ProjectUserSecurityDetail = $DAProjectUser->GetProjectOwnerOrUser($ProjectUserSecurityDetail->ProjectPID, $ProjectUserSecurityDetail->username);
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_UPDATE");
	$HeaderCaption = getres("ACTION_UPDATE_SECURITY_USER_DETAIL");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
<script>
$(function(){
	
	var CheckBoxSelections = [
		"ChatRead",
		"ChatWrite",
		"ReqRead",
		"ReqWrite",
		"spectoolRead",
		"spectoolWrite",
		"dbtoolRead",
		"dbtoolWrite",
		"htmlRead",
		"htmlWrite",
		"testtoolRead",
		"testtoolWrite",
		"MinutesRead",
		"MinutesWrite",
		"UploadRead",
		"UploadWrite"
	];
	function update_all_tick_checkbox()
	{
		var is_any_checked = false;
		var is_all_checked = true;
		for(var i = 0 ; i < CheckBoxSelections.length ; i++ ) {
			if ($("#" + CheckBoxSelections[i]).prop("checked")) {
				is_any_checked = true;
			} else {
				is_all_checked = false;
			}
		}
		if (is_any_checked) {
			if (is_all_checked) {
				$("#ALLORNOT").prop("indeterminate", false);
				$("#ALLORNOT").prop("checked", true);
			} else {
				$("#ALLORNOT").prop("checked", false);
				$("#ALLORNOT").prop("indeterminate", true);
			}
		} else {
			$("#ALLORNOT").prop("checked", false);
			$("#ALLORNOT").prop("indeterminate", false);
		}
	}
	update_all_tick_checkbox();
	
	function set_check_all(setvalue)
	{
		for(var i = 0 ; i < CheckBoxSelections.length ; i++ ) {
			$("#" + CheckBoxSelections[i]).prop("checked", setvalue);
		}
	}
	$(".ALLORNOT").change(function(){
		if ($(this).is(':checked')) {
			set_check_all(true);
		} else {
			set_check_all(false);
		}
	});
	$("#ChatRead").change(function(){
		update_all_tick_checkbox();
	});
	$("#ChatWrite").change(function(){
		update_all_tick_checkbox();
	});
	$('#ReqRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#ReqWrite').change(function(){
		update_all_tick_checkbox();
	});
	$('#spectoolRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#spectoolWrite').change(function(){
		update_all_tick_checkbox();
	});
	$('#dbtoolRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#dbtoolWrite').change(function(){
		update_all_tick_checkbox();
	});
	$('#htmlRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#htmlWrite').change(function(){
		update_all_tick_checkbox();
	});
	$('#testtoolRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#testtoolWrite').change(function(){
		update_all_tick_checkbox();
	});
	$('#MinutesRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#MinutesWrite').change(function(){
		update_all_tick_checkbox();
	});
	$('#UploadRead').change(function(){
		update_all_tick_checkbox();
	});
	$('#UploadWrite').change(function(){
		update_all_tick_checkbox();
	});
});

</script>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="project_security_detail_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		?>
		<div class="row">
		  <label class="col-md-3 control-label" for="inputtext"></label>
		  <div class="col-md-9">
            <span class="checkbox">
				<label><input type="checkbox" class="ALLORNOT" name="ALLORNOT" id="ALLORNOT" value="1">All Checked</label>
            </span>
		  </div>
		</div>
		<?php
		mtoolCommonFormCheckBoxForBoolean("ChatRead", $ProjectUserSecurityDetail->ChatRead,
			array($LANG_ENGLISH=>"Chat", $LANG_JAPANESE=>"Chat"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("ChatWrite", $ProjectUserSecurityDetail->ChatWrite,
			array($LANG_ENGLISH=>"Chat", $LANG_JAPANESE=>"Chat"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("ReqRead", $ProjectUserSecurityDetail->ReqRead,
			array($LANG_ENGLISH=>"Requirement Tool", $LANG_JAPANESE=>"Requirement Tool"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("ReqWrite", $ProjectUserSecurityDetail->ReqWrite,
			array($LANG_ENGLISH=>"Requirement Tool", $LANG_JAPANESE=>"Requirement Tool"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("spectoolRead", $ProjectUserSecurityDetail->spectoolRead,
			array($LANG_ENGLISH=>"Spec Tool", $LANG_JAPANESE=>"Spec Tool"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("spectoolWrite", $ProjectUserSecurityDetail->spectoolWrite,
			array($LANG_ENGLISH=>"Spec Tool", $LANG_JAPANESE=>"Spec Tool"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("dbtoolRead", $ProjectUserSecurityDetail->dbtoolRead,
			array($LANG_ENGLISH=>"DB Tool", $LANG_JAPANESE=>"DB Tool"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("dbtoolWrite", $ProjectUserSecurityDetail->dbtoolWrite,
			array($LANG_ENGLISH=>"DB Tool", $LANG_JAPANESE=>"DB Tool"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("htmlRead", $ProjectUserSecurityDetail->htmlRead,
			array($LANG_ENGLISH=>"Html", $LANG_JAPANESE=>"Html"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("htmlWrite", $ProjectUserSecurityDetail->htmlWrite,
			array($LANG_ENGLISH=>"Html", $LANG_JAPANESE=>"Html"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("testtoolRead", $ProjectUserSecurityDetail->testtoolRead,
			array($LANG_ENGLISH=>"Test Tool", $LANG_JAPANESE=>"Test Tool"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("testtoolWrite", $ProjectUserSecurityDetail->testtoolWrite,
			array($LANG_ENGLISH=>"Test Tool", $LANG_JAPANESE=>"Test Tool"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("MinutesRead", $ProjectUserSecurityDetail->MinutesRead,
			array($LANG_ENGLISH=>"Minutes Tool", $LANG_JAPANESE=>"Minutes Tool"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("MinutesWrite", $ProjectUserSecurityDetail->MinutesWrite,
			array($LANG_ENGLISH=>"Minutes Tool", $LANG_JAPANESE=>"Minutes Tool"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("UploadRead", $ProjectUserSecurityDetail->UploadRead,
			array($LANG_ENGLISH=>"Upload Tool", $LANG_JAPANESE=>"Upload Tool"),
			array($LANG_ENGLISH=>"Read", $LANG_JAPANESE=>"Read"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("UploadWrite", $ProjectUserSecurityDetail->UploadWrite,
			array($LANG_ENGLISH=>"Upload Tool", $LANG_JAPANESE=>"Upload Tool"),
			array($LANG_ENGLISH=>"Write", $LANG_JAPANESE=>"Write"),
			"", "", true);
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectUserSecurityDetail->ProjectPID); ?>">
		<input name="username" type="hidden" value="<?php print htmlspecialchars($ProjectUserSecurityDetail->username); ?>">
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
	<p><a href="project_security_detail.php?ProjectPID=<?php print urlencode($ProjectUserSecurityDetail->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Project User List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
