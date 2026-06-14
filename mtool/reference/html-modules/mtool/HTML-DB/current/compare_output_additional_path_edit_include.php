<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$CompareOutputAdditionalPath = new CompareOutputAdditionalPathData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$CompareOutputAdditionalPath->PID = trim(GetParam("CompareOutputAdditionalPathPID"));
$CompareOutputAdditionalPath->ProjectPID = trim(GetParam("ProjectPID"));
$CompareOutputAdditionalPath->CompareOutputPID = trim(GetParam("CompareOutputPID"));
$CompareOutputAdditionalPath->PathA_DropboxBaseFolderPID = trim(GetParam("PathA_DropboxBaseFolderPID"));
$CompareOutputAdditionalPath->PathA = trim(GetParam("PathA"));
$CompareOutputAdditionalPath->PathB_DropboxBaseFolderPID = trim(GetParam("PathB_DropboxBaseFolderPID"));
$CompareOutputAdditionalPath->PathB = trim(GetParam("PathB"));
$CompareOutputAdditionalPath->IsSameFilenameOnly = trim(GetParam("IsSameFilenameOnly"));
if (!is_numeric($CompareOutputAdditionalPath->IsSameFilenameOnly)) {
	$CompareOutputAdditionalPath->IsSameFilenameOnly = 0;
}
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($CompareOutputAdditionalPath->ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($CompareOutputAdditionalPath->CompareOutputPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Compare Output PID</font></H3>
    <?php
	$NoError = false;
}

$DAProject = new ProjectDBAccess();
$project = NULL;
if ($NoError) {
	$project = $DAProject->GetProject($CompareOutputAdditionalPath->ProjectPID);
	if ($project == NULL) {
		?>
		<H3><font color="red">Unknown Project</font></H3>
		<?php
		$NoError = false;
	}
}

// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_base_folder_edit.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_compare_output.php");

InitializeOutputShortenedStringWithExpansion();

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$CompareOutputAdditionalPath->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($CompareOutputAdditionalPath->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DACompareOutputAdditionalPath = new CompareOutputAdditionalPathDBAccess();
			$insertResult = $DACompareOutputAdditionalPath->InsertCompareOutputAdditionalPath($CompareOutputAdditionalPath);
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
				$CompareOutputAdditionalPath->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_COMPARE_OUTPUT_ADDITIONAL_PATH"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $CompareOutputAdditionalPath->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($CompareOutputAdditionalPath->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $CompareOutputAdditionalPath->PID)) {
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
		
	} else if (is_numeric($CompareOutputAdditionalPath->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DACompareOutputAdditionalPath = new CompareOutputAdditionalPathDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DACompareOutputAdditionalPath->UpdateCompareOutputAdditionalPath($CompareOutputAdditionalPath);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_COMPARE_OUTPUT_ADDITIONAL_PATH"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DACompareOutputAdditionalPath->DeleteCompareOutputAdditionalPath($CompareOutputAdditionalPath);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_COMPARE_OUTPUT_ADDITIONAL_PATH"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$CompareOutputAdditionalPath = $DACompareOutputAdditionalPath->GetCompareOutputAdditionalPath($CompareOutputAdditionalPath->PID, $CompareOutputAdditionalPath->CompareOutputPID, $CompareOutputAdditionalPath->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! Project PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($CompareOutputAdditionalPath->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_COMPARE_OUTPUT_ADDITIONAL_PATH");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_COMPARE_OUTPUT_ADDITIONAL_PATH");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $CompareOutputAdditionalPath != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBTable($HeaderCaption, $CompareOutputAdditionalPath->ProjectPID, "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="compare_output_additional_path_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormSelect("PathA_DropboxBaseFolderPID", $CompareOutputAdditionalPath->PathA_DropboxBaseFolderPID,
			array($LANG_ENGLISH=>"Dropbox Base Folder for Path A", $LANG_JAPANESE=>"Path A 向け Dropbox Base Folder設定"),
			array($LANG_ENGLISH=>"Please select Dropbox Base Folder for Path A (if Blank, default is used)", $LANG_JAPANESE=>"Path A 向け Dropbox Base Folder設定を選択して下さい (未選択の場合はデフォルト設定を使用)"), 
			GetDropboxBaseFolderSelectionListForEditAnySettingGroup($matsuesoft_login_token_id)
			, array(), "");
		mtoolCommonFormInput("PathA", $CompareOutputAdditionalPath->PathA,
			array($LANG_ENGLISH=>"Path A (tmp output)", $LANG_JAPANESE=>"パスA (tmp output)"),
			array($LANG_ENGLISH=>"Please input Path A (tmp output) (on Storege such as DropBox)", $LANG_JAPANESE=>"パスA(tmp output)を入力して下さい。格納先(DropBox等)のパス"),
			"text", "");
		mtoolCommonFormSelect("PathB_DropboxBaseFolderPID", $CompareOutputAdditionalPath->PathB_DropboxBaseFolderPID,
			array($LANG_ENGLISH=>"Dropbox Base Folder for Path B", $LANG_JAPANESE=>"Path B 向け Dropbox Base Folder設定"),
			array($LANG_ENGLISH=>"Please select Dropbox Base Folder for Path B (if Blank, default is used)", $LANG_JAPANESE=>"Path B 向け Dropbox Base Folder設定を選択して下さい (未選択の場合はデフォルト設定を使用)"), 
			GetDropboxBaseFolderSelectionListForEditAnySettingGroup($matsuesoft_login_token_id)
			, array(), "");
		mtoolCommonFormInput("PathB", $CompareOutputAdditionalPath->PathB,
			array($LANG_ENGLISH=>"Path B", $LANG_JAPANESE=>"パスB"),
			array($LANG_ENGLISH=>"Please input Path B (on Storege such as DropBox)", $LANG_JAPANESE=>"パスBを入力して下さい。格納先(DropBox等)のパス"),
			"text", "");
		mtoolCommonFormCheckBoxForValue("IsSameFilenameOnly", $CompareOutputAdditionalPath->IsSameFilenameOnly,
			array($LANG_ENGLISH=>"[OPTION] Compare Same Filename Only (Ignore one side file)", $LANG_JAPANESE=>"[オプション] 同一ファイル名のみ比較(片側ファイルを処理しない)"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($CompareOutputAdditionalPath->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($CompareOutputAdditionalPath->ProjectPID); ?>">
		<input name="CompareOutputPID" type="hidden" value="<?php print htmlspecialchars($CompareOutputAdditionalPath->CompareOutputPID); ?>">
		<input name="CompareOutputAdditionalPathPID" type="hidden" value="<?php print htmlspecialchars($CompareOutputAdditionalPath->PID); ?>">
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
	<p><a href="compare_output_additional_path.php?ProjectPID=<?php print urlencode($CompareOutputAdditionalPath->ProjectPID); ?>&CompareOutputPID=<?php print urlencode($CompareOutputAdditionalPath->CompareOutputPID); ?>&<?php print makeRandStr(8); ?>">Back to Compare Output Setting</a> / <a href="compare_output.php?ProjectPID=<?php print urlencode($CompareOutputAdditionalPath->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Compare Output Setting</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
