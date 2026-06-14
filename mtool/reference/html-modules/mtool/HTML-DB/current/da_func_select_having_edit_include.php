<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafuncselecthaving = new dafuncselecthavingData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dafuncselecthaving->ProjectPID = trim(GetParam("ProjectPID"));
$dafuncselecthaving->daPID = trim(GetParam("DAPID"));
$dafuncselecthaving->dafuncPID = trim(GetParam("DAFuncPID"));
$dafuncselecthaving->PID = trim(GetParam("PID"));
$dafuncselecthaving->LeftTargetPrefix = trim(GetParam("LeftTargetPrefix"));
$dafuncselecthaving->LeftTargetFieldPID = trim(GetParam("LeftTargetFieldPID"));
$dafuncselecthaving->LeftTargetSuffix = trim(GetParam("LeftTargetSuffix"));
$dafuncselecthaving->RelationalOperator = trim(GetParam("RelationalOperator"));
$dafuncselecthaving->RightTargetPrefix = trim(GetParam("RightTargetPrefix"));
$dafuncselecthaving->RightParameterType = trim(GetParam("RightParameterType"));
$dafuncselecthaving->RightParameterDataType = trim(GetParam("RightParameterDataType"));
$dafuncselecthaving->RightFixedParameter = trim(GetParam("RightFixedParameter"));
$dafuncselecthaving->RightTargetFieldPID = trim(GetParam("RightTargetFieldPID"));
$dafuncselecthaving->RightTargetSuffix = trim(GetParam("RightTargetSuffix"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafuncselecthaving->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncselecthaving->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncselecthaving->dafuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_db_check.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dafuncselecthaving->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dafuncselecthaving->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdafuncselecthaving = new dafuncselecthavingDBAccess();
			$insertResult = $DAdafuncselecthaving->Insertdafuncselecthaving($dafuncselecthaving);
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
				$dafuncselecthaving->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DA_FUNC_SELECT_HAVING"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselecthaving->daPID, $dafuncselecthaving->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselecthaving->dafuncPID, $dafuncselecthaving->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dafuncselecthaving->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dafuncselecthaving->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dafuncselecthaving->PID)) {
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
		
	} else if (is_numeric($dafuncselecthaving->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdafuncselecthaving = new dafuncselecthavingDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdafuncselecthaving->Updatedafuncselecthaving($dafuncselecthaving);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC_SELECT_HAVING"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselecthaving->daPID, $dafuncselecthaving->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselecthaving->dafuncPID, $dafuncselecthaving->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdafuncselecthaving->Deletedafuncselecthaving($dafuncselecthaving->PID, $dafuncselecthaving->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DA_FUNC_SELECT_HAVING"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselecthaving->daPID, $dafuncselecthaving->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselecthaving->dafuncPID, $dafuncselecthaving->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dafuncselecthaving = $DAdafuncselecthaving->Getdafuncselecthaving($dafuncselecthaving->PID, $dafuncselecthaving->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Function Select's Having PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dafuncselecthaving->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DA_FUNC_SELECT_HAVING");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC_SELECT_HAVING");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dafuncselecthaving != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBAccessClass($HeaderCaption, $dafuncselecthaving->ProjectPID, $dafuncselecthaving->daPID, $dafuncselecthaving->dafuncPID, "", $dafuncselecthaving->PID, "", "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_func_select_having_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
		$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($dafuncselecthaving->ProjectPID, $dafuncselecthaving->daPID, $dafuncselecthaving->dafuncPID); 
		$TargetFieldSelectionList = array();
		if ($dafuncselecttargetfieldlist != NULL) {
			for($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
				$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
				
				$thisCaption = GetReferencingFieldColumnIfThereisFromFieldData($dafuncselecttargetfield);
				array_push($TargetFieldSelectionList,
					array("VALUE"=>$dafuncselecttargetfield->PID, "CAPTION"=>$thisCaption));
			}
		}
		
		mtoolCommonFormInput("LeftTargetPrefix", $dafuncselecthaving->LeftTargetPrefix,
			array($LANG_ENGLISH=>"Prefix for Left Target", $LANG_JAPANESE=>"左辺の接頭辞"),
			array($LANG_ENGLISH=>"Please input Prefix for Left Target", $LANG_JAPANESE=>"左辺の接頭辞を入力して下さい"), 
			"text", "");
		mtoolCommonFormSelect("LeftTargetFieldPID", $dafuncselecthaving->LeftTargetFieldPID,
			array($LANG_ENGLISH=>"Left Target Field", $LANG_JAPANESE=>"左辺の対象フィールド"),
			array($LANG_ENGLISH=>"Please input Left Target Field", $LANG_JAPANESE=>"左辺の対象フィールドを入力して下さい"), 
			$TargetFieldSelectionList, array(), "");
		mtoolCommonFormInput("LeftTargetSuffix", $dafuncselecthaving->LeftTargetSuffix,
			array($LANG_ENGLISH=>"Suffix for Left Target", $LANG_JAPANESE=>"左辺の接尾辞"),
			array($LANG_ENGLISH=>"Please input Suffix for Left Target", $LANG_JAPANESE=>"左辺の接尾辞を入力して下さい"), 
			"text", "");
		mtoolCommonFormInput("RelationalOperator", $dafuncselecthaving->RelationalOperator,
			array($LANG_ENGLISH=>"Relational Operator", $LANG_JAPANESE=>"関係演算子"),
			array($LANG_ENGLISH=>"Please input Relational Operator(If blank, regard as '=')", $LANG_JAPANESE=>"関係演算子を入力して下さい(空白の場合は '=')"),
			"text", "");
		CheckIfValidMysqlRelationalOperator($dafuncselecthaving->RelationalOperator);
		
		mtoolCommonFormInput("RightTargetPrefix", $dafuncselecthaving->RightTargetPrefix,
			array($LANG_ENGLISH=>"Prefix for Right Target", $LANG_JAPANESE=>"右辺の接頭辞"),
			array($LANG_ENGLISH=>"Please input Prefix for Right Target", $LANG_JAPANESE=>"右辺の接頭辞を入力して下さい"), 
			"text", "");
		mtoolCommonFormSelect("RightParameterType", $dafuncselecthaving->RightParameterType,
			array($LANG_ENGLISH=>"Right Parameter Type", $LANG_JAPANESE=>"右辺パラメータ種類"),
			array($LANG_ENGLISH=>"Please input Right Parameter Type", $LANG_JAPANESE=>"右辺パラメータ種類を入力して下さい"), 
			array(
				array("VALUE"=>dafuncselecthavingRightParameterTypeEnum::$ARGUMENT, "CAPTION"=>GetdafuncselecthavingRightParameterTypeCaption(dafuncselecthavingRightParameterTypeEnum::$ARGUMENT)),
				array("VALUE"=>dafuncselecthavingRightParameterTypeEnum::$FIXED, "CAPTION"=>GetdafuncselecthavingRightParameterTypeCaption(dafuncselecthavingRightParameterTypeEnum::$FIXED)),
				array("VALUE"=>dafuncselecthavingRightParameterTypeEnum::$FIELD, "CAPTION"=>GetdafuncselecthavingRightParameterTypeCaption(dafuncselecthavingRightParameterTypeEnum::$FIELD))
			), array(
				array("VALUE"=>dafuncselecthavingRightParameterTypeEnum::$ARGUMENT . "," . dafuncselecthavingRightParameterTypeEnum::$FIXED, "SHOW"=>"RightParameterDataTypeArea"),
				array("VALUE"=>dafuncselecthavingRightParameterTypeEnum::$FIXED, "SHOW"=>"RightFixedParameterArea"),
				array("VALUE"=>dafuncselecthavingRightParameterTypeEnum::$FIELD, "SHOW"=>"RightTargetFieldPIDArea")
			), "");
		mtoolCommonFormRadioButton("RightParameterDataType", $dafuncselecthaving->RightParameterDataType,
			array($LANG_ENGLISH=>"Right Parameter's Data Type", $LANG_JAPANESE=>"右辺データ種類"),
			array($LANG_ENGLISH=>"Please input Right Parameter's Data Type(Regard as string if blank)", $LANG_JAPANESE=>"右辺データ種類を入力して下さい(空白の場合はstring)"), 
			array(
				array("VALUE"=>dafuncselecthavingRightParameterDataTypeEnum::$DEFAULT, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncselecthavingRightParameterDataTypeEnum::$DEFAULT)),
				array("VALUE"=>dafuncselecthavingRightParameterDataTypeEnum::$RAW, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncselecthavingRightParameterDataTypeEnum::$RAW))
			), array(
			), "RightParameterDataTypeArea");
		mtoolCommonFormInput("RightFixedParameter", $dafuncselecthaving->RightFixedParameter,
			array($LANG_ENGLISH=>"Right Fixed Parameter", $LANG_JAPANESE=>"右辺の固定値"),
			array($LANG_ENGLISH=>"Please input Right Fixed Parameter", $LANG_JAPANESE=>"右辺の固定値を入力して下さい"),
			"text", "RightFixedParameterArea");
		mtoolCommonFormSelect("RightTargetFieldPID", $dafuncselecthaving->RightTargetFieldPID,
			array($LANG_ENGLISH=>"Right Target Field", $LANG_JAPANESE=>"右辺の対象フィールド"),
			array($LANG_ENGLISH=>"Please input Right Target Field", $LANG_JAPANESE=>"右辺の対象フィールドを入力して下さい"), 
			$TargetFieldSelectionList, array(), "RightTargetFieldPIDArea");
		mtoolCommonFormInput("RightTargetSuffix", $dafuncselecthaving->RightTargetSuffix,
			array($LANG_ENGLISH=>"Suffix for Right Target", $LANG_JAPANESE=>"右辺の接尾辞"),
			array($LANG_ENGLISH=>"Please input Suffix for Right Target", $LANG_JAPANESE=>"右辺の接尾辞を入力して下さい"), 
			"text", "");			
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dafuncselecthaving->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafuncselecthaving->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafuncselecthaving->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafuncselecthaving->dafuncPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($dafuncselecthaving->PID); ?>">
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
    <p><a href="da_func_select_having.php?ProjectPID=<?php print urlencode($dafuncselecthaving->ProjectPID); ?>&DAPID=<?php print urlencode($dafuncselecthaving->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncselecthaving->dafuncPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function - Select's Having List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
