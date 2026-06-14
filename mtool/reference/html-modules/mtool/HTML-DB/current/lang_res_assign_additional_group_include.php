<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$LanguageResource = new LanguageResourceData();
$LanguageResource->ProjectPID = trim(GetParam("ProjectPID"));
$LanguageResource->PID = trim(GetParam("LanguageResourcePID"));

$BaseLanguageResourceGroupPID = trim(GetParam("BaseLanguageResourceGroupPID"));
$AdditionalResourceGroupPIDList = GetParam("AdditionalResourceGroupPIDList");

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
$DALanguageResourceGroup = new LanguageResourceGroupDBAccess();
$DALanguageResourceAdditionalGroupAssignment = new LanguageResourceAdditionalGroupAssignmentDBAccess();

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	$HeaderCaption = getres("ACTION_ASSIGN_ADDITIONAL_GROUP_TO_LANGUAGE_RESOURCE");
	printPathOnTopForLanguageResource($HeaderCaption, $LanguageResource->ProjectPID, $LanguageResource->PID);
	
	$LanguageResourceAdditionalGroupAssignmentList = $DALanguageResourceAdditionalGroupAssignment->GetLanguageResourceAdditionalGroupAssignmentList($LanguageResource->PID, $LanguageResource->ProjectPID);
	$LanguageResourceGroupList = $DALanguageResourceGroup->GetLanguageResourceGroupList($LanguageResource->ProjectPID);
	
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		
		for($i = 0 ; $i < count($LanguageResourceGroupList); $i++) {
			$LanguageResourceGroup = $LanguageResourceGroupList[$i];
			
			$already_assigned = false;
			for($j = 0 ; $j < count($LanguageResourceAdditionalGroupAssignmentList); $j++) {
				$LanguageResourceAdditionalGroupAssignment = $LanguageResourceAdditionalGroupAssignmentList[$j];
				
				if ($LanguageResourceAdditionalGroupAssignment->LanguageResourceGroupPID == $LanguageResourceGroup->PID) {
					$already_assigned = true;
					break;
				}
			}
			
			$is_assign = false;
			for($j = 0 ; $j < count($AdditionalResourceGroupPIDList); $j++) {
				$AdditionalResourceGroupPID = $AdditionalResourceGroupPIDList[$j];
				
				if ($AdditionalResourceGroupPID == $LanguageResourceGroup->PID) {
					$is_assign = true;
					break;
				}
			}
			
			$update_something_to_this_group = false;
			
			$LanguageResourceAdditionalGroupAssignmentObj = new LanguageResourceAdditionalGroupAssignmentData();
			$LanguageResourceAdditionalGroupAssignmentObj->ProjectPID = $LanguageResource->ProjectPID;
			$LanguageResourceAdditionalGroupAssignmentObj->LanguageResourcePID = $LanguageResource->PID;
			$LanguageResourceAdditionalGroupAssignmentObj->LanguageResourceGroupPID = $LanguageResourceGroup->PID;
			if ($is_assign) {
				// Add if necessary
				if (!$already_assigned) {
					$insertResult = $DALanguageResourceAdditionalGroupAssignment->InsertLanguageResourceAdditionalGroupAssignment($LanguageResourceAdditionalGroupAssignmentObj);
					if($insertResult === FALSE) {
						?>
						<h3><font color="red">Error! Failed to insert</font></h3>
						<?php
						$needToLoad = false;
					} else {
						$updatedSomething = true;
						$update_something_to_this_group = true;
					}
				}
				
			} else {
				// Need to delete if there is
				if ($already_assigned) {
					$deleteResult = $DALanguageResourceAdditionalGroupAssignment->DeleteLanguageResourceAdditionalGroupAssignment($LanguageResourceAdditionalGroupAssignmentObj);
					if($deleteResult === FALSE) {
						?>
						<h3><font color="red">Error! Failed to delete</font></h3>
						<?php
						$needToLoad = false;
					} else {
						$updatedSomething = true;
						$update_something_to_this_group = true;
					}
				}
			}
			if ($update_something_to_this_group) {
	            update_language_resource_LastModifiedDT($LanguageResourceGroup->PID, $LanguageResource->ProjectPID);
			}
		}
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
            <h3><font color="red"><?php print getres("ACTION_ASSIGNED_ADDITIONAL_GROUP_TO_LANGUAGE_RESOURCE"); ?></font></h3>
            <?php
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		$LanguageResource = $DALanguageResource->GetLanguageResource($LanguageResource->PID, $LanguageResource->ProjectPID);
		$LanguageResourceAdditionalGroupAssignmentList = $DALanguageResourceAdditionalGroupAssignment->GetLanguageResourceAdditionalGroupAssignmentList($LanguageResource->PID, $LanguageResource->ProjectPID);
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
		
		<form action="lang_res_assign_additional_group.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormComment(array($LANG_ENGLISH=>"Sort String", $LANG_JAPANESE=>"ソート用文字列"), $LanguageResource->SortGroup, "", "");
		mtoolCommonFormComment(array($LANG_ENGLISH=>"Key Name", $LANG_JAPANESE=>"キー名"), $LanguageResource->KeyName, "", "");
		mtoolCommonFormComment(array($LANG_ENGLISH=>"Japanese", $LANG_JAPANESE=>"日本語"), $LanguageResource->JP, "", "");
		mtoolCommonFormComment(array($LANG_ENGLISH=>"English", $LANG_JAPANESE=>"英語"), $LanguageResource->EN, "", "");
		
		
		$BaseLanguageResourceGroup = $DALanguageResourceGroup->GetLanguageResourceGroup($BaseLanguageResourceGroupPID, $LanguageResource->ProjectPID);
		if ($BaseLanguageResourceGroup) {
			mtoolCommonFormComment(array($LANG_ENGLISH=>"Base Language Resource Group", $LANG_JAPANESE=>"基本言語リソースグループ"), $BaseLanguageResourceGroup->Name, "", "");
		}
		
		for($i = 0 ; $i < count($LanguageResourceGroupList); $i++) {
			$LanguageResourceGroup = $LanguageResourceGroupList[$i];
			
			if ($LanguageResourceGroup->PID == $BaseLanguageResourceGroupPID) {
				// Main Group
				
			} else {
				$assignment_value = "";
				for($j = 0 ; $j < count($LanguageResourceAdditionalGroupAssignmentList); $j++) {
					$LanguageResourceAdditionalGroupAssignment = $LanguageResourceAdditionalGroupAssignmentList[$j];
					
					if ($LanguageResourceAdditionalGroupAssignment->LanguageResourceGroupPID == $LanguageResourceGroup->PID) {
						$assignment_value = $LanguageResourceGroup->PID;
						break;
					}
				}
				mtoolCommonFormCheckBoxForValue("AdditionalResourceGroupPIDList[]", $assignment_value,
					array($LANG_ENGLISH=>"Language Resource Group", $LANG_JAPANESE=>"言語リソースグループ"),
					array($LANG_ENGLISH=>$LanguageResourceGroup->Name, $LANG_JAPANESE=>$LanguageResourceGroup->Name),
					"", "", true, $LanguageResourceGroup->PID);
			}
		}
		
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
		<input name="LanguageResourcePID" type="hidden" value="<?php print htmlspecialchars($LanguageResource->PID); ?>">
		<input name="BaseLanguageResourceGroupPID" type="hidden" value="<?php print htmlspecialchars($BaseLanguageResourceGroupPID); ?>">
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
    <p><a href="lang_res_list.php?ProjectPID=<?php print urlencode($LanguageResource->ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($BaseLanguageResourceGroupPID); ?>&<?php print makeRandStr(8); ?>">Back to Language Resource List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
