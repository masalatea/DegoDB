<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafuncupdatedeletewhere = new dafuncupdatedeletewhereData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dafuncupdatedeletewhere->ProjectPID = trim(GetParam("ProjectPID"));
$dafuncupdatedeletewhere->daPID = trim(GetParam("DAPID"));
$dafuncupdatedeletewhere->dafuncPID = trim(GetParam("DAFuncPID"));
$dafuncupdatedeletewhere->PID = trim(GetParam("PID"));
$dafuncupdatedeletewhere->targetTableColumnName = trim(GetParam("targetTableColumnName"));
$dafuncupdatedeletewhere->ParameterType = trim(GetParam("ParameterType"));
$dafuncupdatedeletewhere->FixedParameter = trim(GetParam("FixedParameter"));
$dafuncupdatedeletewhere->ParameterDataType = trim(GetParam("ParameterDataType"));
$dafuncupdatedeletewhere->RelationalOperator = trim(GetParam("RelationalOperator"));
$dafuncupdatedeletewhere->ORGroup = trim(GetParam("ORGroup"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafuncupdatedeletewhere->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncupdatedeletewhere->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncupdatedeletewhere->dafuncPID)) {
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
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dafuncupdatedeletewhere->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dafuncupdatedeletewhere->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdafuncupdatedeletewhere = new dafuncupdatedeletewhereDBAccess();
			$insertResult = $DAdafuncupdatedeletewhere->Insertdafuncupdatedeletewhere($dafuncupdatedeletewhere);
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
				$dafuncupdatedeletewhere->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DA_FUNC_UPDATE_DELETE_WHERE"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncupdatedeletewhere->daPID, $dafuncupdatedeletewhere->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncupdatedeletewhere->dafuncPID, $dafuncupdatedeletewhere->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dafuncupdatedeletewhere->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dafuncupdatedeletewhere->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dafuncupdatedeletewhere->PID)) {
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
		
	} else if (is_numeric($dafuncupdatedeletewhere->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdafuncupdatedeletewhere = new dafuncupdatedeletewhereDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdafuncupdatedeletewhere->Updatedafuncupdatedeletewhere($dafuncupdatedeletewhere);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC_UPDATE_DELETE_WHERE"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncupdatedeletewhere->daPID, $dafuncupdatedeletewhere->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncupdatedeletewhere->dafuncPID, $dafuncupdatedeletewhere->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdafuncupdatedeletewhere->Deletedafuncupdatedeletewhere($dafuncupdatedeletewhere->PID, $dafuncupdatedeletewhere->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DA_FUNC_UPDATE_DELETE_WHERE"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncupdatedeletewhere->daPID, $dafuncupdatedeletewhere->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncupdatedeletewhere->dafuncPID, $dafuncupdatedeletewhere->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dafuncupdatedeletewhere = $DAdafuncupdatedeletewhere->Getdafuncupdatedeletewhere($dafuncupdatedeletewhere->PID, $dafuncupdatedeletewhere->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Function Update/Delete's Where PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dafuncupdatedeletewhere->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DA_FUNC_UPDATE_DELETE_WHERE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC_UPDATE_DELETE_WHERE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dafuncupdatedeletewhere != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBAccessClass($HeaderCaption, $dafuncupdatedeletewhere->ProjectPID, $dafuncupdatedeletewhere->daPID, $dafuncupdatedeletewhere->dafuncPID, "", "", "", $dafuncupdatedeletewhere->PID, "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_func_update_delete_where_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("RelationalOperator", $dafuncupdatedeletewhere->RelationalOperator,
			array($LANG_ENGLISH=>"Relational Operator", $LANG_JAPANESE=>"関係演算子"),
			array($LANG_ENGLISH=>"Please input Relational Operator(If blank, regard as '=')", $LANG_JAPANESE=>"関係演算子を入力して下さい(空白の場合は '=')"),
			"text", "");
		CheckIfValidMysqlRelationalOperator($dafuncupdatedeletewhere->RelationalOperator);
		
		mtoolCommonFormInput("targetTableColumnName", $dafuncupdatedeletewhere->targetTableColumnName,
			array($LANG_ENGLISH=>"Target Column Name", $LANG_JAPANESE=>"対象カラム名"),
			array($LANG_ENGLISH=>"Please input Target Column Type", $LANG_JAPANESE=>"対象カラム名を入力して下さい"), 
			"text", "");
		mtoolCommonFormSelect("ParameterType", $dafuncupdatedeletewhere->ParameterType,
			array($LANG_ENGLISH=>"Parameter Type", $LANG_JAPANESE=>"パラメータ種類"),
			array($LANG_ENGLISH=>"Please select Parameter Type", $LANG_JAPANESE=>"パラメータ種類を選択して下さい"), 
			array(
				array("VALUE"=>"argument", "CAPTION"=>"Argument"),
				array("VALUE"=>"fixed", "CAPTION"=>"Fixed")
			), array(
				array("VALUE"=>"fixed", "SHOW"=>"FixedParameterArea")
			), "");
		mtoolCommonFormRadioButton("ParameterDataType", $dafuncupdatedeletewhere->ParameterDataType,
			array($LANG_ENGLISH=>"Parameter's Data Type", $LANG_JAPANESE=>"データ種類"),
			array($LANG_ENGLISH=>"Please input Parameter's Data Type(Regard as string if blank)", $LANG_JAPANESE=>"データ種類を入力して下さい(空白の場合はstring)"), 
			array(
				array("VALUE"=>dafuncupdatedeletewhereParameterDataTypeEnum::$DEFAULT, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncupdatedeletewhereParameterDataTypeEnum::$DEFAULT)),
				array("VALUE"=>dafuncupdatedeletewhereParameterDataTypeEnum::$RAW, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncupdatedeletewhereParameterDataTypeEnum::$RAW))
			), array(
			), "ParameterDataTypeArea");
		mtoolCommonFormInput("FixedParameter", $dafuncupdatedeletewhere->FixedParameter,
			array($LANG_ENGLISH=>"Fixed Parameter", $LANG_JAPANESE=>"固定値"),
			array($LANG_ENGLISH=>"Please input Fixed Parameter", $LANG_JAPANESE=>"固定値を入力して下さい"),
			"text", "FixedParameterArea");
		mtoolCommonFormInput("ORGroup", $dafuncupdatedeletewhere->ORGroup,
			array($LANG_ENGLISH=>"OR Group", $LANG_JAPANESE=>"ORグループ"),
			array($LANG_ENGLISH=>"Please input OR Group", $LANG_JAPANESE=>"ORグループを入力して下さい"), 
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dafuncupdatedeletewhere->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatedeletewhere->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatedeletewhere->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatedeletewhere->dafuncPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($dafuncupdatedeletewhere->PID); ?>">
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
    <p><a href="da_func_update_delete_where.php?ProjectPID=<?php print urlencode($dafuncupdatedeletewhere->ProjectPID); ?>&DAPID=<?php print urlencode($dafuncupdatedeletewhere->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncupdatedeletewhere->dafuncPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function - Update/Delete's Where List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
