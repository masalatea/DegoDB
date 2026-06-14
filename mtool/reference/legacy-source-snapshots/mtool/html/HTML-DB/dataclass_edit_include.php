<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dataclass = new dataclassData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dataclass->ProjectPID = trim(GetParam("ProjectPID"));
$dataclass->PID = trim(GetParam("DataClassPID"));
$dataclass->name = trim(GetParam("name"));
$dataclass->StoreBasePath = trim(GetParam("StoreBasePath"));
$dataclass->IsAutoload = trim(GetParam("IsAutoload"));
$dataclass->NormalizeIsAutoloadProperty();
$dataclass->InheritParentDataClassName = trim(GetParam("InheritParentDataClassName"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dataclass->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==

$DAProject = new ProjectDBAccess();
$project = $DAProject->GetProject($dataclass->ProjectPID);
if (!$project) {
	die("Something strange. Project is not found\n");
}
$AllSourceInclude = $project->Getoption_all_source_include();

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dataclass->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dataclass->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdataclass = new dataclassDBAccess();
			$insertResult = $DAdataclass->Insertdataclass($dataclass);
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
				$dataclass->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DATA_CLASS"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dataclass->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dataclass->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dataclass->PID)) {
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
		
	} else if (is_numeric($dataclass->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdataclass = new dataclassDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdataclass->Updatedataclass($dataclass);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DATA_CLASS"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdataclass->Deletedataclass($dataclass->PID, $dataclass->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DATA_CLASS"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dataclass = $DAdataclass->Getdataclass($dataclass->PID, $dataclass->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! Data Class PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dataclass->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DATA_CLASS");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DATA_CLASS");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dataclass != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDataClasses($HeaderCaption, $dataclass->ProjectPID, $dataclass->PID, "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="dataclass_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $dataclass->name,
			array($LANG_ENGLISH=>"Data Class Name", $LANG_JAPANESE=>"データクラス名"),
			array($LANG_ENGLISH=>"Please input Data Class Name", $LANG_JAPANESE=>"データクラス名を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("InheritParentDataClassName", $dataclass->InheritParentDataClassName,
			array($LANG_ENGLISH=>"Inherit Parent Data Class Name", $LANG_JAPANESE=>"継承元データクラス名"),
			array($LANG_ENGLISH=>"Please input Inherit Parent Data Class Name (No inherit if blank)", $LANG_JAPANESE=>"継承元データクラス名を入力して下さい(空白の場合は継承なし)"),
			"text", "");
		mtoolCommonFormInput("StoreBasePath", $dataclass->StoreBasePath,
			array($LANG_ENGLISH=>"Store Base Path", $LANG_JAPANESE=>"Store Base Path"),
			array($LANG_ENGLISH=>"Please input Store Base Path (Use Source Output Setting if Blank)", $LANG_JAPANESE=>"Store Base Pathを入力して下さい(空の場合はソース出力設定が使用されます)"),
			"text", "");
		if ($AllSourceInclude) {
			?>
			<input name="IsAutoload" type="hidden" value="<?php print htmlspecialchars($dataclass->IsAutoload); ?>">
            <?php
		} else {
			mtoolCommonFormCheckBoxForBoolean("IsAutoload", $dataclass->IsAutoload,
				array($LANG_ENGLISH=>"Include in Autoload", $LANG_JAPANESE=>"AutoLoadに含める"),
				array($LANG_ENGLISH=>"Include in Autoload (For PHP only. Not for C#)", $LANG_JAPANESE=>"AutoLoadに含める(PHPのみ有効. C#では無効)"),
				"", "", true);
		}
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dataclass->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dataclass->ProjectPID); ?>">
		<input name="DataClassPID" type="hidden" value="<?php print htmlspecialchars($dataclass->PID); ?>">
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
    <p><a href="dataclasses.php?ProjectPID=<?php print urlencode($dataclass->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Data Class List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
