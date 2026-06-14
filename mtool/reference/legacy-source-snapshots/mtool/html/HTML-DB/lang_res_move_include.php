<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$LanguageResource = new LanguageResourceData();
$LanguageResource->ProjectPID = trim(GetParam("ProjectPID"));
$LanguageResource->PID = trim(GetParam("PID"));
$LanguageResource->SortGroup = trim(GetParam("SortGroup"));
$LanguageResource->KeyName = trim(GetParam("KeyName"));
$LanguageResource->LanguageResourceGroupPID = trim(GetParam("LanguageResourceGroupPID"));
$LanguageResource->JP = trim(GetParam("JP"));
$LanguageResource->EN = trim(GetParam("EN"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($LanguageResource->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$DALanguageResource = new LanguageResourceDBAccess();

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	$HeaderCaption = getres("ACTION_MOVE_LANGUAGE_RESOURCE");
	printPathOnTopForLanguageResource($HeaderCaption, $LanguageResource->ProjectPID, $LanguageResource->PID);
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		
		$OldLanguageResource = $DALanguageResource->GetLanguageResource($LanguageResource->PID, $LanguageResource->ProjectPID);
		$updateResult = $DALanguageResource->UpdateLanguageGroup($LanguageResource);
		if($updateResult === FALSE) {
			// Failed
			?>
			<h3><font color="red">Error! Failed to update</font></h3>
			<?php
			$needToLoad = false;
		} else {
			// Success
			$updatedSomething = true;
		}
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
            <h3><font color="red"><?php print getres("ACTION_MOVED_LANGUAGE_RESOURCE"); ?></font></h3>
            <?php
            if ($OldLanguageResource) {
                update_language_resource_LastModifiedDT($OldLanguageResource->LanguageResourceGroupPID, $LanguageResource->ProjectPID);
            }
            update_language_resource_LastModifiedDT($LanguageResource->LanguageResourceGroupPID, $LanguageResource->ProjectPID);
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		$LanguageResource = $DALanguageResource->GetLanguageResource($LanguageResource->PID, $LanguageResource->ProjectPID);
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_MOVE");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="lang_res_move.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormComment(array($LANG_ENGLISH=>"Sort String", $LANG_JAPANESE=>"ソート用文字列"), $LanguageResource->SortGroup, "", "");
		mtoolCommonFormComment(array($LANG_ENGLISH=>"Key Name", $LANG_JAPANESE=>"キー名"), $LanguageResource->KeyName, "", "");
		mtoolCommonFormComment(array($LANG_ENGLISH=>"Japanese", $LANG_JAPANESE=>"日本語"), $LanguageResource->JP, "", "");
		mtoolCommonFormComment(array($LANG_ENGLISH=>"English", $LANG_JAPANESE=>"英語"), $LanguageResource->EN, "", "");
		
		include_once("lang_res_select_resource_group_lib.php");
		
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($LanguageResource->ProjectPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($LanguageResource->PID); ?>">
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
    <p><a href="lang_res_list.php?ProjectPID=<?php print urlencode($LanguageResource->ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($LanguageResource->LanguageResourceGroupPID); ?>&<?php print makeRandStr(8); ?>">Back to Language Resource List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
