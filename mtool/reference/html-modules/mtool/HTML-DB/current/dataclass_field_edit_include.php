<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dataclassfield = new dataclassfieldsData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dataclassfield->ProjectPID = trim(GetParam("ProjectPID"));
$dataclassfield->dataclassPID = trim(GetParam("DataClassPID"));
$dataclassfield->PID = trim(GetParam("DataClassFieldPID"));
$dataclassfield->name = trim(GetParam("name"));
$dataclassfield->datatype = trim(GetParam("datatype"));
$dataclassfield->RefDataClassName = trim(GetParam("RefDataClassName"));
$dataclassfield->RefDataClassFieldName = trim(GetParam("RefDataClassFieldName"));

// for override data type from sync page
$overrideByNewData = trim(GetParam("overrideByNewData"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dataclassfield->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dataclassfield->dataclassPID)) {
	?>
    <H3><font color="red">Data Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dataclassfield->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dataclassfield->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdataclassfields = new dataclassfieldsDBAccess();
			$insertResult = $DAdataclassfields->Insertdataclassfields($dataclassfield);
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
				$dataclassfield->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DATA_CLASS_FIELD"); ?></font></h3>
                <?php
				update_dataclass_LastModifiedDT($dataclassfield->dataclassPID, $dataclassfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dataclassfield->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dataclassfield->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dataclassfield->PID)) {
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
		
	} else if (is_numeric($dataclassfield->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdataclassfields = new dataclassfieldsDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdataclassfields->UpdatedataclassfieldsExcludeFieldListOrder($dataclassfield);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DATA_CLASS_FIELD"); ?></font></h3>
                <?php
				update_dataclass_LastModifiedDT($dataclassfield->dataclassPID, $dataclassfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdataclassfields->Deletedataclassfields($dataclassfield->PID, $dataclassfield->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DATA_CLASS_FIELD"); ?></font></h3>
                <?php
				update_dataclass_LastModifiedDT($dataclassfield->dataclassPID, $dataclassfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dataclassfield = $DAdataclassfields->Getdataclassfields($dataclassfield->PID, $dataclassfield->ProjectPID);
			
			// Override data type from sync page
			if ($overrideByNewData != "") {
				$dataclassfield->datatype = trim(GetParam("datatype"));
				$dataclassfield->RefDataClassName = trim(GetParam("RefDataClassName"));
				$dataclassfield->RefDataClassFieldName = trim(GetParam("RefDataClassFieldName"));
			}
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
	if ($dataclassfield->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DATA_CLASS_FIELD");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DATA_CLASS_FIELD");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dataclassfield != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDataClasses($HeaderCaption, $dataclassfield->ProjectPID, $dataclassfield->dataclassPID, $dataclassfield->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="dataclass_field_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $dataclassfield->name,
			array($LANG_ENGLISH=>"Field Name", $LANG_JAPANESE=>"フィールド名"),
			array($LANG_ENGLISH=>"Please input Field Name of Data Class", $LANG_JAPANESE=>"データクラスのフィールド名を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("datatype", $dataclassfield->datatype,
			array($LANG_ENGLISH=>"Data Type", $LANG_JAPANESE=>"データ種類"),
			array($LANG_ENGLISH=>"Please input Data Type", $LANG_JAPANESE=>"データ種類を入力して下さい(For C#, not for php)"),
			"text", "");
		mtoolCommonFormInput("RefDataClassName", $dataclassfield->RefDataClassName,
			array($LANG_ENGLISH=>"Referencing Data Class Name", $LANG_JAPANESE=>"参照先データクラス名"),
			array($LANG_ENGLISH=>"Please input Referencing Data Class Name[OPTIONAL]", $LANG_JAPANESE=>"参照先データクラス名を入力して下さい[OPTIONAL]"),
			"text", "");
		mtoolCommonFormInput("RefDataClassFieldName", $dataclassfield->RefDataClassFieldName,
			array($LANG_ENGLISH=>"Referencing Field Name", $LANG_JAPANESE=>"参照先フィールド名"),
			array($LANG_ENGLISH=>"Please input Referencing Field Name of Data Class[OPTIONAL]", $LANG_JAPANESE=>"参照先データクラスのフィールド名を入力して下さい[OPTIONAL]"),
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dataclassfield->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dataclassfield->ProjectPID); ?>">
		<input name="DataClassPID" type="hidden" value="<?php print htmlspecialchars($dataclassfield->dataclassPID); ?>">
		<input name="DataClassFieldPID" type="hidden" value="<?php print htmlspecialchars($dataclassfield->PID); ?>">
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
    <p><a href="dataclass_fields.php?ProjectPID=<?php print urlencode($dataclassfield->ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclassfield->dataclassPID); ?>&<?php print makeRandStr(8); ?>">Back to Field List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
