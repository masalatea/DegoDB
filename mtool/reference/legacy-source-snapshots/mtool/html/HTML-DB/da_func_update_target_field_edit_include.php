<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafuncupdatetargetfield = new dafuncupdatetargetfieldsData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dafuncupdatetargetfield->ProjectPID = trim(GetParam("ProjectPID"));
$dafuncupdatetargetfield->daPID = trim(GetParam("DAPID"));
$dafuncupdatetargetfield->dafuncPID = trim(GetParam("DAFuncPID"));
$dafuncupdatetargetfield->PID = trim(GetParam("PID"));
$dafuncupdatetargetfield->targetTableColumnName = trim(GetParam("targetTableColumnName"));
$dafuncupdatetargetfield->ParameterType = trim(GetParam("ParameterType"));
$dafuncupdatetargetfield->FixedParameter = trim(GetParam("FixedParameter"));
$dafuncupdatetargetfield->ParameterDataType = trim(GetParam("ParameterDataType"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafuncupdatetargetfield->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncupdatetargetfield->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncupdatetargetfield->dafuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("da_func_insert_or_update_target_fields_update_list_order_lib.php");

include_once("da_func_common_for_blob.php");
CheckDAFuncStatus($dafuncupdatetargetfield->dafuncPID, $dafuncupdatetargetfield->ProjectPID);

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dafuncupdatetargetfield->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dafuncupdatetargetfield->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
			$insertResult = $DAdafuncupdatetargetfields->Insertdafuncupdatetargetfields($dafuncupdatetargetfield);
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
				$dafuncupdatetargetfield->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DA_FUNC_UPDATE_TARGET_FIELDS"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncupdatetargetfield->daPID, $dafuncupdatetargetfield->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncupdatetargetfield->dafuncPID, $dafuncupdatetargetfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dafuncupdatetargetfield->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dafuncupdatetargetfield->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dafuncupdatetargetfield->PID)) {
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
		
	} else if (is_numeric($dafuncupdatetargetfield->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdafuncupdatetargetfields->Updatedafuncupdatetargetfields($dafuncupdatetargetfield);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC_UPDATE_TARGET_FIELDS"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncupdatetargetfield->daPID, $dafuncupdatetargetfield->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncupdatetargetfield->dafuncPID, $dafuncupdatetargetfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdafuncupdatetargetfields->Deletedafuncupdatetargetfields($dafuncupdatetargetfield->PID, $dafuncupdatetargetfield->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DA_FUNC_UPDATE_TARGET_FIELDS"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncupdatetargetfield->daPID, $dafuncupdatetargetfield->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncupdatetargetfield->dafuncPID, $dafuncupdatetargetfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dafuncupdatetargetfield = $DAdafuncupdatetargetfields->Getdafuncupdatetargetfields($dafuncupdatetargetfield->PID, $dafuncupdatetargetfield->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Function Update/Delete Target Field PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dafuncupdatetargetfield->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DA_FUNC_UPDATE_TARGET_FIELDS");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC_UPDATE_TARGET_FIELDS");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dafuncupdatetargetfield != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBAccessClass($HeaderCaption, $dafuncupdatetargetfield->ProjectPID, $dafuncupdatetargetfield->daPID, $dafuncupdatetargetfield->dafuncPID, "", "", "", "", $dafuncupdatetargetfield->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_func_update_target_field_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("targetTableColumnName", $dafuncupdatetargetfield->targetTableColumnName,
			array($LANG_ENGLISH=>"Target Column Name", $LANG_JAPANESE=>"対象カラム名"),
			array($LANG_ENGLISH=>"Please input Target Column Type", $LANG_JAPANESE=>"対象カラム名を入力して下さい"), 
			"text", "");
		mtoolCommonFormSelect("ParameterType", $dafuncupdatetargetfield->ParameterType,
			array($LANG_ENGLISH=>"Parameter Type", $LANG_JAPANESE=>"パラメータ種類"),
			array($LANG_ENGLISH=>"Please select Parameter Type", $LANG_JAPANESE=>"パラメータ種類を選択して下さい"), 
			array(
				array("VALUE"=>"argument", "CAPTION"=>"Argument"),
				array("VALUE"=>"fixed", "CAPTION"=>"Fixed")
			), array(
				array("VALUE"=>"fixed", "SHOW"=>"FixedParameterArea")
			), "");
		$ParameterDataTypeSelections = array(
				array("VALUE"=>dafuncupdatetargetfieldsParameterDataTypeEnum::$DEFAULT, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncupdatetargetfieldsParameterDataTypeEnum::$DEFAULT)),
				array("VALUE"=>dafuncupdatetargetfieldsParameterDataTypeEnum::$RAW, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncupdatetargetfieldsParameterDataTypeEnum::$RAW))
			);
		if ($IsBlobTarget) {
			array_push($ParameterDataTypeSelections,
				array("VALUE"=>dafuncupdatetargetfieldsParameterDataTypeEnum::$FILE, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncupdatetargetfieldsParameterDataTypeEnum::$FILE))
				);
		}
		mtoolCommonFormRadioButton("ParameterDataType", $dafuncupdatetargetfield->ParameterDataType,
			array($LANG_ENGLISH=>"Parameter's Data Type", $LANG_JAPANESE=>"データ種類"),
			array($LANG_ENGLISH=>"Please input Parameter's Data Type(Regard as string if blank)", $LANG_JAPANESE=>"データ種類を入力して下さい(空白の場合はstring)"), 
			$ParameterDataTypeSelections,
			array(
			), "ParameterDataTypeArea");
		mtoolCommonFormInput("FixedParameter", $dafuncupdatetargetfield->FixedParameter,
			array($LANG_ENGLISH=>"Fixed Parameter", $LANG_JAPANESE=>"固定値"),
			array($LANG_ENGLISH=>"Please input Fixed Parameter", $LANG_JAPANESE=>"固定値を入力して下さい"),
			"text", "FixedParameterArea");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dafuncupdatetargetfield->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatetargetfield->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatetargetfield->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatetargetfield->dafuncPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatetargetfield->PID); ?>">
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
	adjust_list_order_of_insert_or_update_target_fields_and_show_message($dafuncupdatetargetfield->ProjectPID, $dafuncupdatetargetfield->daPID, $dafuncupdatetargetfield->dafuncPID);
	?>
    <p><a href="da_func_update_target_fields.php?ProjectPID=<?php print urlencode($dafuncupdatetargetfield->ProjectPID); ?>&DAPID=<?php print urlencode($dafuncupdatetargetfield->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncupdatetargetfield->dafuncPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function's Update/Delete Target Field List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
