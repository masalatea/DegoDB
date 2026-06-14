<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$LanguageResourceGroup = new LanguageResourceGroupData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$LanguageResourceGroup->ProjectPID = trim(GetParam("ProjectPID"));
$LanguageResourceGroup->PID = trim(GetParam("PID"));
$LanguageResourceGroup->Name = trim(GetParam("Name"));
$LanguageResourceGroup->FunctionNamePrefix = trim(GetParam("FunctionNamePrefix"));
$LanguageResourceGroup->FunctionNameSuffix = trim(GetParam("FunctionNameSuffix"));
$LanguageResourceGroup->FilenameSuffix = trim(GetParam("FilenameSuffix"));
$LanguageResourceGroup->FilenameSuffixForPHP = trim(GetParam("FilenameSuffixForPHP"));
$LanguageResourceGroup->FilenameForXcode = trim(GetParam("FilenameForXcode"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($LanguageResourceGroup->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");

function UpdateLanguageResourceGroupSourceOutput()
{
	global $mtooldb;
	global $LanguageResourceGroup;
	
	// Array
	$TargetProjectSourceOutputPIDList = GetParam("TargetProjectSourceOutputPIDList");
	
	$DAProjectSourceOutputData = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutputData->GetProjectSourceOutputList($LanguageResourceGroup->ProjectPID);
	
	$DALanguageResourceGroupProjectSourceOutput = new LanguageResourceGroupProjectSourceOutputDBAccess();
	$LanguageResourceGroupProjectSourceOutputList = $DALanguageResourceGroupProjectSourceOutput->GetLanguageResourceGroupProjectSourceOutputForTheGroupList($LanguageResourceGroup->PID, $LanguageResourceGroup->ProjectPID);
	
	for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$j];
		
		$IsChecked = false;
		if (is_array($TargetProjectSourceOutputPIDList)) {
			for ($k = 0 ; $k < count($TargetProjectSourceOutputPIDList) ; $k++) {
				$TargetProjectSourceOutputPID = $TargetProjectSourceOutputPIDList[$k];
				
				if ($TargetProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					$IsChecked = true;
					
					// print "Checked: " . $ProjectSourceOutput->SourceOutputDir . "<br>\n";
					break;
				}
			}
		}
		
		if ($IsChecked) {
			// Checked for this Source Output
			$NeedToInsert = true;
			for ( $k = 0 ; $k < count($LanguageResourceGroupProjectSourceOutputList) ; $k++) {
				$LanguageResourceGroupProjectSourceOutput = $LanguageResourceGroupProjectSourceOutputList[$k];
				
				if ($LanguageResourceGroupProjectSourceOutput->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					$NeedToInsert = false;
					break;
				}
			}
			if ($NeedToInsert) {
				$thisTargetObj = new LanguageResourceGroupProjectSourceOutputData();
				$thisTargetObj->ProjectPID = $LanguageResourceGroup->ProjectPID;
				$thisTargetObj->LanguageResourceGroupPID = $LanguageResourceGroup->PID;
				$thisTargetObj->ProjectSourceOutputPID = $ProjectSourceOutput->PID;
				
				if ($DALanguageResourceGroupProjectSourceOutput->InsertLanguageResourceGroupProjectSourceOutput($thisTargetObj)) {
					// Success
					if (mysqli_affected_rows($mtooldb) > 0) {
						?>
						<h3><font color="red">Added Output Target</font> </h3>
						<?php
					}
					
				} else {
					?>
					<h3><font color="red">Error! Failed to add Output Target</font></h3>
					<?php
				}
			}
		} else {
			// Not Checked for this Source Output
			$NeedToDelete = false;
			for ( $k = 0 ; $k < count($LanguageResourceGroupProjectSourceOutputList) ; $k++) {
				$LanguageResourceGroupProjectSourceOutput = $LanguageResourceGroupProjectSourceOutputList[$k];
				
				if ($LanguageResourceGroupProjectSourceOutput->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					if ($DALanguageResourceGroupProjectSourceOutput->DeleteLanguageResourceGroupProjectSourceOutput($LanguageResourceGroupProjectSourceOutput)) {
						// Success
						if (mysqli_affected_rows($mtooldb) > 0) {
							?>
							<h3><font color="red">Deleted Output Target</font> </h3>
							<?php
						}
						
					} else {
						?>
						<h3><font color="red">Error! Failed to delete Output Target</font></h3>
						<?php
					}
					break;
				}
			}
		}
	}
}

function UpdateLanguageResourceGroupLang()
{
	global $mtooldb;
	global $LanguageResourceGroup;
	
	if (!is_numeric($LanguageResourceGroup->PID)) {
		die("Error! Unknown Resource Group... Aborted");
	}
	
	// Array
	$LanguageResourceGroupLangPIDList = GetParam("LanguageResourceGroupLangPIDList");
	
	$DALanguageResourceLang = new LanguageResourceLangDBAccess();
	$DALanguageResourceGroupLang = new LanguageResourceGroupLangDBAccess();
	
	$LanguageResourceLangList = $DALanguageResourceLang->GetLanguageResourceLangList();
	$LanguageResourceGroupLangList = $DALanguageResourceGroupLang->GetLanguageResourceGroupLangList($LanguageResourceGroup->ProjectPID, $LanguageResourceGroup->PID);
	
	for($j = 0 ; $j < count($LanguageResourceLangList) ; $j++) {
		$LanguageResourceLang = $LanguageResourceLangList[$j];
		
		$isAlreadySelected = CheckIfAlreadySelectedLanguage($LanguageResourceGroupLangList, $LanguageResourceLang);
		
		$isSelected = false;
		if (is_array($LanguageResourceGroupLangPIDList)) {
			for ($k = 0 ; $k < count($LanguageResourceGroupLangPIDList) ; $k++) {
				$LanguageResourceGroupLangPID = $LanguageResourceGroupLangPIDList[$k];

				if ($LanguageResourceGroupLangPID == $LanguageResourceLang->PID) {
					$isSelected = true;
					break;
				}
			}
		}
		
		if ($isAlreadySelected != $isSelected) {
			$LanguageResourceGroupLangObj = new LanguageResourceGroupLangData();
			$LanguageResourceGroupLangObj->ProjectPID = $LanguageResourceGroup->ProjectPID;
			$LanguageResourceGroupLangObj->LanguageResourceGroupPID = $LanguageResourceGroup->PID;
			$LanguageResourceGroupLangObj->LanguageResourceLangPID = $LanguageResourceLang->PID;
			if ($isSelected) {
				// Need to Add
				$DALanguageResourceGroupLang->InsertLanguageResourceGroupLang($LanguageResourceGroupLangObj);
			} else {
				// Need to Delete
				$DALanguageResourceGroupLang->DeleteLanguageResourceGroupLang($LanguageResourceGroupLangObj);
			}
		}
	}
	
}
function CheckIfAlreadySelectedLanguage($LanguageResourceGroupLangList, $LanguageResourceLang)
{
	global $LanguageResourceGroupLang;
	
	$isSelected = false;
	for($k = 0 ; $k < count($LanguageResourceGroupLangList) ; $k++) {
		$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$k];

		if ($LanguageResourceLang->PID == $LanguageResourceGroupLang->LanguageResourceLangPID) {
			$isSelected = true;
			break;
		}
	}
	return $isSelected;
}
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$LanguageResourceGroup->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($LanguageResourceGroup->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DALanguageResourceGroup = new LanguageResourceGroupDBAccess();
			$insertResult = $DALanguageResourceGroup->InsertLanguageResourceGroup($LanguageResourceGroup);
			// == END OF EDITABLE AREA FOR "Insert Data" ==
			if($insertResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Insert Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to insert</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Failed" ==
			} else {
				// Success
				$LanguageResourceGroup->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_LANGUAGE_RESOURCE_GROUP"); ?></font></h3>
                <?php
				UpdateLanguageResourceGroupSourceOutput();
				UpdateLanguageResourceGroupLang();
				update_language_resource_LastModifiedDT($LanguageResourceGroup->PID, $LanguageResourceGroup->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $LanguageResourceGroup->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($LanguageResourceGroup->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $LanguageResourceGroup->PID)) {
					// Success
					$insertToken = "";
				} else {
					// Failed
					?>
					<h3><font color="red">Internal Error! Failed to complete Insert</font></h3>
					<?php
				}
			}
		}
		
	} else if (is_numeric($LanguageResourceGroup->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DALanguageResourceGroup = new LanguageResourceGroupDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DALanguageResourceGroup->UpdateLanguageResourceGroup($LanguageResourceGroup);
			// == END OF EDITABLE AREA FOR "Update Data" ==
			if($updateResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Update Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Failed" ==
				$needToLoad = false;
				
			} else {
				// Success
				// == START OF EDITABLE AREA FOR "Update Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_UPDATED_LANGUAGE_RESOURCE_GROUP"); ?></font></h3>
                <?php
				UpdateLanguageResourceGroupSourceOutput();
				UpdateLanguageResourceGroupLang();
				update_language_resource_LastModifiedDT($LanguageResourceGroup->PID, $LanguageResourceGroup->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DALanguageResourceGroup->DeleteLanguageResourceGroup($LanguageResourceGroup);
			// == END OF EDITABLE AREA FOR "Delete Data" ==
			if($deleteResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Delete Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to delete</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Failed" ==
				$needToLoad = false;
				
			} else {
				// Success
				// == START OF EDITABLE AREA FOR "Delete Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_DELETED_LANGUAGE_RESOURCE_GROUP"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$LanguageResourceGroup = $DALanguageResourceGroup->GetLanguageResourceGroup($LanguageResourceGroup->PID, $LanguageResourceGroup->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! LanguageResourceGroup PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($LanguageResourceGroup->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_LANGUAGE_RESOURCE_GROUP");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_LANGUAGE_RESOURCE_GROUP");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $LanguageResourceGroup != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForLanguageResource($HeaderCaption, $LanguageResourceGroup->ProjectPID, $LanguageResourceGroup->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="lang_res_group_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("Name", $LanguageResourceGroup->Name,
			array($LANG_ENGLISH=>"Name", $LANG_JAPANESE=>"名前"),
			array($LANG_ENGLISH=>"Please input Name", $LANG_JAPANESE=>"名前を入力して下さい。"),
			"text", "");
		mtoolCommonFormInput("FunctionNamePrefix", $LanguageResourceGroup->FunctionNamePrefix,
			array($LANG_ENGLISH=>"Function Name's Prefix", $LANG_JAPANESE=>"関数名接頭辞"),
			array($LANG_ENGLISH=>"Please input Function Name's Prefix (Only for PHP)", $LANG_JAPANESE=>"関数名接頭辞を入力して下さい。(PHPのみ)"),
			"text", "");
		mtoolCommonFormInput("FunctionNameSuffix", $LanguageResourceGroup->FunctionNameSuffix,
			array($LANG_ENGLISH=>"Function Name's Suffix", $LANG_JAPANESE=>"関数名接尾語"),
			array($LANG_ENGLISH=>"Please input Function Name's Suffix (Only for PHP)", $LANG_JAPANESE=>"関数名接尾語を入力して下さい。(PHPのみ)"),
			"text", "");
		mtoolCommonFormInput("FilenameSuffixForPHP", $LanguageResourceGroup->FilenameSuffixForPHP,
			array($LANG_ENGLISH=>"File Name's Suffix for PHP", $LANG_JAPANESE=>"PHPファイル名(接尾語)"),
			array($LANG_ENGLISH=>"Please input File Name's Suffix For PHP", $LANG_JAPANESE=>"PHPファイル名(接尾語)を入力して下さい。"),
			"text", "");
		mtoolCommonFormCommentByList(array(), array(
			"PHP: \"lang_lib\" + Filename Suffix + \".php\""
		), "", "");
		mtoolCommonFormInput("FilenameSuffix", $LanguageResourceGroup->FilenameSuffix,
			array($LANG_ENGLISH=>"File Name's Suffix", $LANG_JAPANESE=>"ファイル名(接尾語)"),
			array($LANG_ENGLISH=>"Please input File Name's Suffix (For C#/Android)", $LANG_JAPANESE=>"ファイル名(接尾語)を入力して下さい。(C#/Android向け)"),
			"text", "");
		mtoolCommonFormCommentByList(array(), array(
			"C#: \"Resources\" + Filename Suffix + \".resx\" / \"Resources\" + Filename Suffix + \".ja-JP.resx\"",
			"Android: \"res/values/strings_generated\" + Filename Suffix + \".xml\" / \"res/values-ja/strings_generated\" + Filename Suffix + \".xml\""
		), "", "");
		mtoolCommonFormInput("FilenameForXcode", $LanguageResourceGroup->FilenameForXcode,
			array($LANG_ENGLISH=>"File Name for Xcode", $LANG_JAPANESE=>"ファイル名(Xcode向け)"),
			array($LANG_ENGLISH=>"Please input File Name for Xcode", $LANG_JAPANESE=>"Xcode向けファイル名を入力して下さい。(Xcode向け)"),
			"text", "");
		mtoolCommonFormCommentByList(array(), array(
			"Xcode: \"Base.lproj/\" + Filename + \".strings\" / \"ja.lproj/\" + Filename + \".strings\""
		), "", "");
		?>
       <div class="clearfix"></div>
        <div class="row">
            <label class="col-md-3 control-label" for="inputtext">Target Language</label>
            <div class="col-md-9">
                <?php
				  	$DALanguageResourceLang = new LanguageResourceLangDBAccess();
					$DALanguageResourceGroupLang = new LanguageResourceGroupLangDBAccess();
					
					$LanguageResourceLangList = $DALanguageResourceLang->GetLanguageResourceLangList();
					$LanguageResourceGroupLangList = array();
					if (is_numeric($LanguageResourceGroup->PID)) {
						$LanguageResourceGroupLangList = $DALanguageResourceGroupLang->GetLanguageResourceGroupLangList($LanguageResourceGroup->ProjectPID, $LanguageResourceGroup->PID);
					}
					
					for($j = 0 ; $j < count($LanguageResourceLangList) ; $j++) {
						$LanguageResourceLang = $LanguageResourceLangList[$j];
						
						$isSelected = CheckIfAlreadySelectedLanguage($LanguageResourceGroupLangList, $LanguageResourceLang);
						?>
						<span class="checkbox"><label>
						<input name="LanguageResourceGroupLangPIDList[]" type="checkbox" value="<?php print $LanguageResourceLang->PID; ?>"<?php if ($isSelected) { print " checked"; } ?> /> <?php print htmlspecialchars($LanguageResourceLang->Caption); ?>
						</label>
						</span>
						<?php
					}
                ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <label class="col-md-3 control-label" for="inputtext">Source Output Target</label>
            <div class="col-md-9">
                <?php
				  	$DALanguageResourceGroupProjectSourceOutput = new LanguageResourceGroupProjectSourceOutputDBAccess();
					
					$LanguageResourceGroupProjectSourceOutputList = array();
					if (is_numeric($LanguageResourceGroup->PID)) {
						$LanguageResourceGroupProjectSourceOutputList = $DALanguageResourceGroupProjectSourceOutput->GetLanguageResourceGroupProjectSourceOutputForTheGroupList($LanguageResourceGroup->PID, $LanguageResourceGroup->ProjectPID);
					}
		
					$DAProjectSourceOutputData = new ProjectSourceOutputDBAccess();
					$ProjectSourceOutputList = $DAProjectSourceOutputData->GetProjectSourceOutputList($LanguageResourceGroup->ProjectPID);
					for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
						$ProjectSourceOutput = $ProjectSourceOutputList[$j];
						
						if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE) {
							
							$isSelected = false;
							for($k = 0 ; $k < count($LanguageResourceGroupProjectSourceOutputList) ; $k++) {
								$LanguageResourceGroupProjectSourceOutput = $LanguageResourceGroupProjectSourceOutputList[$k];
								
								if ($LanguageResourceGroupProjectSourceOutput->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
									$isSelected = true;
									break;
								}
							}
							?>
		                	<span class="checkbox"><label>
			                <input name="TargetProjectSourceOutputPIDList[]" type="checkbox" value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($isSelected) { print " checked"; } ?> /> <?php print htmlspecialchars(MakeDropboxFolderByProjectAndProjectSourceOutput($LanguageResourceGroup->ProjectPID, $ProjectSourceOutput, $ProjectSourceOutput->SourceOutputDir)); ?>
							</label>
							</span>
							<?php
						}
					}
                ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($LanguageResourceGroup->PID != "") {
				?>
				<p align="right">
				<input name="DELETE" type="submit" value="<?php print htmlspecialchars(getres("ACTION_DELETE")); ?>" onClick="return confirm('<?php print htmlspecialchars(getres("ACTION_DELETE_CONFIRM")); ?>');">
				</p>
				<?php
			}
			?>
			</div>
		</div>
		<?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($LanguageResourceGroup->ProjectPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($LanguageResourceGroup->PID); ?>">
		<?php
		// == END OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="insertToken" type="hidden" value="<?php print htmlspecialchars($insertToken); ?>">
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
    <p><a href="lang_res.php?ProjectPID=<?php print urlencode($LanguageResourceGroup->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Language Resource Group List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
