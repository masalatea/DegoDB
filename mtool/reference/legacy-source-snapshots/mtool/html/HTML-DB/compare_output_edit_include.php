<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$CompareOutput = new CompareOutputData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$CompareOutput->ProjectPID = trim(GetParam("ProjectPID"));
$CompareOutput->PID = trim(GetParam("CompareOutputPID"));
$CompareOutput->DropboxBaseFolderPID = trim(GetParam("DropboxBaseFolderPID"));
$CompareOutput->OutputFilePath = trim(GetParam("OutputFilePath"));
$CompareOutput->OutputFileType = trim(GetParam("OutputFileType"));
$CompareOutput->ComparePath = trim(GetParam("ComparePath"));
$CompareOutput->CompareToolFilePath = trim(GetParam("CompareToolFilePath"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($CompareOutput->ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAProject = new ProjectDBAccess();
$project = NULL;
if ($NoError) {
	$project = $DAProject->GetProject($CompareOutput->ProjectPID);
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
		$CompareOutput->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($CompareOutput->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DACompareOutput = new CompareOutputDBAccess();
			$insertResult = $DACompareOutput->InsertCompareOutput($CompareOutput);
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
				$CompareOutput->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_COMPARE_OUTPUT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $CompareOutput->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($CompareOutput->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $CompareOutput->PID)) {
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
		
	} else if (is_numeric($CompareOutput->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DACompareOutput = new CompareOutputDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DACompareOutput->UpdateCompareOutput($CompareOutput);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_COMPARE_OUTPUT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DACompareOutput->DeleteCompareOutput($CompareOutput);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_COMPARE_OUTPUT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$CompareOutput = $DACompareOutput->GetCompareOutput($CompareOutput->PID, $CompareOutput->ProjectPID);
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
	if ($CompareOutput->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_COMPARE_OUTPUT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_COMPARE_OUTPUT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $CompareOutput != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBTable($HeaderCaption, $CompareOutput->ProjectPID, "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="compare_output_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormSelect("DropboxBaseFolderPID", $CompareOutput->DropboxBaseFolderPID,
			array($LANG_ENGLISH=>"Dropbox Base Folder", $LANG_JAPANESE=>"Dropbox Base Folder設定"),
			array($LANG_ENGLISH=>"Please select Dropbox Base Folder (if Blank, Project Setting is used)", $LANG_JAPANESE=>"Dropbox Base Folder設定を選択して下さい (未選択の場合はProject設定を使用)"), 
			GetDropboxBaseFolderSelectionListForEditAnySettingGroup($matsuesoft_login_token_id)
			, array(), "DropboxBaseFolderArea");
		mtoolCommonFormInput("OutputFilePath", $CompareOutput->OutputFilePath,
			array($LANG_ENGLISH=>"Output File Path", $LANG_JAPANESE=>"出力ファイルのパス"),
			array($LANG_ENGLISH=>"Please input Output File Path (on Storege such as DropBox)", $LANG_JAPANESE=>"出力ファイルのパスを入力して下さい。格納先(DropBox等)のパス"),
			"text", "");
		mtoolCommonFormSelect("OutputFileType", $CompareOutput->OutputFileType,
			array($LANG_ENGLISH=>"Output File Type", $LANG_JAPANESE=>"出力ファイル種類"),
			array($LANG_ENGLISH=>"Please select Output File Type", $LANG_JAPANESE=>"出力ファイル種類を選択して下さい"), 
			array(
				array("VALUE"=>CompareOutputOutputFileTypeEnum::$TEXT,         "CAPTION"=>GetCompareOutputOutputFileTypeCaption(CompareOutputOutputFileTypeEnum::$TEXT)),
				array("VALUE"=>CompareOutputOutputFileTypeEnum::$WINDOWSBATCH, "CAPTION"=>GetCompareOutputOutputFileTypeCaption(CompareOutputOutputFileTypeEnum::$WINDOWSBATCH)),
				array("VALUE"=>CompareOutputOutputFileTypeEnum::$MACCOMMAND,   "CAPTION"=>GetCompareOutputOutputFileTypeCaption(CompareOutputOutputFileTypeEnum::$MACCOMMAND))
			), array(
				array("VALUE"=>CompareOutputOutputFileTypeEnum::$WINDOWSBATCH . "," . CompareOutputOutputFileTypeEnum::$MACCOMMAND, "SHOW"=>"CompareToolFilePathArea")
			), "");
		mtoolCommonFormInput("ComparePath", $CompareOutput->ComparePath,
			array($LANG_ENGLISH=>"Compare Folder Path", $LANG_JAPANESE=>"比較フォルダのパス"),
			array($LANG_ENGLISH=>"Please input Compare Folder Path (on Storege such as DropBox)", $LANG_JAPANESE=>"比較フォルダのパスを入力して下さい。格納先(DropBox等)のパス"),
			"text", "");
		mtoolCommonFormInput("CompareToolFilePath", $CompareOutput->CompareToolFilePath,
			array($LANG_ENGLISH=>"Compare Tool Path", $LANG_JAPANESE=>"比較ツールのパス"),
			array($LANG_ENGLISH=>"Please input Compare Tool Path (on Storege such as DropBox)", $LANG_JAPANESE=>"比較ツールのパスを入力して下さい。格納先(DropBox等)のパス"),
			"text", "CompareToolFilePathArea");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($CompareOutput->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($CompareOutput->ProjectPID); ?>">
		<input name="CompareOutputPID" type="hidden" value="<?php print htmlspecialchars($CompareOutput->PID); ?>">
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
	<p><a href="compare_output.php?ProjectPID=<?php print urlencode($CompareOutput->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Compare Output Setting</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
