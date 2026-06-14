<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafunc = new dafuncData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dafunc->ProjectPID = trim(GetParam("ProjectPID"));
$dafunc->daPID = trim(GetParam("DAPID"));
$dafunc->PID = trim(GetParam("DAFuncPID"));
$dafunc->name = trim(GetParam("name"));
$dafunc->ActionType = trim(GetParam("ActionType"));
$dafunc->InsertUpdateDeleteTargetTable = trim(GetParam("InsertUpdateDeleteTargetTable"));
if ($dafunc->ActionType == dafuncActionTypeEnum::$UPDATE) {
	$dafunc->InsertUpdateDeleteParamType = trim(GetParam("InsertUpdateDeleteParamTypeForUpdate"));
} else {
	$dafunc->InsertUpdateDeleteParamType = trim(GetParam("InsertUpdateDeleteParamType"));
}
$dafunc->SortOrderColumns = trim(GetParam("SortOrderColumns"));
$dafunc->DataClassBaseNameForSelectAction = trim(GetParam("DataClassBaseNameForSelectAction"));
$dafunc->SelectByDistinct = trim(GetParam("SelectByDistinct"));
$dafunc->memo = trim(GetParam("memo"));
$dafunc->limitParameterType = trim(GetParam("limitParameterType"));
$dafunc->limitFixedParameter = trim(GetParam("limitFixedParameter"));
$dafunc->ORGroupType = trim(GetParam("ORGroupType"));
$dafunc->IsBlobTarget = trim(GetParam("IsBlobTarget"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafunc->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafunc->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_proxy.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$DAProject = new ProjectDBAccess();
$project = NULL;
if ($NoError) {
	$project = $DAProject->GetProject($dafunc->ProjectPID);
	if (!$project) {
		?>
		<h3><font color="red">Error! Unknown Project</font></h3>
		<?php
		$NoError = false;
	}
}

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dafunc->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dafunc->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdafunc = new dafuncDBAccess();
			$insertResult = $DAdafunc->Insertdafunc($dafunc);
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
				$dafunc->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DA_FUNC"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafunc->daPID, $dafunc->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafunc->PID, $dafunc->ProjectPID);
				synchronize_mtool_proxy_if_automatic($dafunc->ProjectPID, $dafunc->daPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dafunc->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dafunc->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dafunc->PID)) {
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
		
	} else if (is_numeric($dafunc->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdafunc = new dafuncDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdafunc->Updatedafunc($dafunc);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafunc->daPID, $dafunc->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafunc->PID, $dafunc->ProjectPID);
				synchronize_mtool_proxy_if_automatic($dafunc->ProjectPID, $dafunc->daPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdafunc->Deletedafunc($dafunc->PID, $dafunc->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DA_FUNC"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafunc->daPID, $dafunc->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafunc->PID, $dafunc->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dafunc = $DAdafunc->Getdafunc($dafunc->PID, $dafunc->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Function PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dafunc->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DA_FUNC");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dafunc != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBAccessClass($HeaderCaption, $dafunc->ProjectPID, $dafunc->daPID, $dafunc->PID, "", "", "", "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
<script>
var ChecklimitFixedParameterAreaVisible = function()
{
	var ActionTypeSelectedValue = $('#ActionType').val();
	
	switch (ActionTypeSelectedValue) {
		case "<?php print dafuncActionTypeEnum::$SELECTSINGLE; ?>":
			break;
		case "<?php print dafuncActionTypeEnum::$SELECTLIST; ?>":
			var limitParameterTypeSelectedValue = $('#limitParameterType').val();
			switch (limitParameterTypeSelectedValue) {
				case "<?php print dafunclimitParameterTypeEnum::$FIXED; ?>":
					return true;
			}
			break;
		case "<?php print dafuncActionTypeEnum::$INSERT; ?>":
		case "<?php print dafuncActionTypeEnum::$UPDATE; ?>":
		case "<?php print dafuncActionTypeEnum::$DELETE; ?>":
			break;
	}
	return false;
}
</script>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_func_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $dafunc->name,
			array($LANG_ENGLISH=>"Suffix of Function Name", $LANG_JAPANESE=>"関数名の接尾語"),
			array($LANG_ENGLISH=>"Please input Suffix of Function Name (Automatically add function type as prefix to function name)", $LANG_JAPANESE=>"関数名の接尾語を入力して下さい(関数名先頭に操作種類が自動的に入ります)"),
			"text", "");
		if (is_numeric($dafunc->PID)) {
			if (trim($dafunc->name) == "") {
				$thisCaption = array($LANG_ENGLISH=>"WARNING! Please input Name.", $LANG_JAPANESE=>"WARNING! 接尾語を入力して下さい");
				?>
				<div class="row">
					<label class="col-md-3 control-label" for="inputtext"></label>
					<div class="col-md-9">
						<font color="red">
						<?php print htmlspecialchars($thisCaption[$lang]); ?>
						</font>
					</div>
				</div>
				<?php
			}
		}
		mtoolCommonFormSelect("ActionType", $dafunc->ActionType,
			array($LANG_ENGLISH=>"Action Type", $LANG_JAPANESE=>"操作種類"),
			array($LANG_ENGLISH=>"Please select Action Type", $LANG_JAPANESE=>"操作種類を選択して下さい"), 
			array(
				array("VALUE"=>dafuncActionTypeEnum::$SELECTSINGLE, "CAPTION"=>GetDAFuncActionTypeCaption(dafuncActionTypeEnum::$SELECTSINGLE)),
				array("VALUE"=>dafuncActionTypeEnum::$SELECTLIST, "CAPTION"=>GetDAFuncActionTypeCaption(dafuncActionTypeEnum::$SELECTLIST)),
				array("VALUE"=>dafuncActionTypeEnum::$INSERT, "CAPTION"=>GetDAFuncActionTypeCaption(dafuncActionTypeEnum::$INSERT)),
				array("VALUE"=>dafuncActionTypeEnum::$UPDATE, "CAPTION"=>GetDAFuncActionTypeCaption(dafuncActionTypeEnum::$UPDATE)),
				array("VALUE"=>dafuncActionTypeEnum::$DELETE, "CAPTION"=>GetDAFuncActionTypeCaption(dafuncActionTypeEnum::$DELETE))
			), array(
				array("VALUE"=>dafuncActionTypeEnum::$SELECTLIST, "SHOW"=>"SortOrderColumnsArea,limitParameterTypeArea,SelectByDistinctArea"),
				array("VALUE"=>dafuncActionTypeEnum::$SELECTSINGLE . "," . dafuncActionTypeEnum::$SELECTLIST, "SHOW"=>"DataClassBaseNameForSelectActionArea,CheckDataClassNameForSelectActionArea,ORGroupTypeArea"),
				array("VALUE"=>dafuncActionTypeEnum::$INSERT . "," . dafuncActionTypeEnum::$DELETE . "," . dafuncActionTypeEnum::$UPDATE, "SHOW"=>"InsertUpdateDeleteTargetTableArea"),
				array("VALUE"=>dafuncActionTypeEnum::$INSERT . "," . dafuncActionTypeEnum::$DELETE, "SHOW"=>"InsertUpdateDeleteParamTypeArea,InsertUpdateDeleteParamTypeWarningArea"),
				array("VALUE"=>dafuncActionTypeEnum::$INSERT . "," . dafuncActionTypeEnum::$UPDATE, "SHOW"=>"IsBlobTargetArea"),
				array("VALUE"=>dafuncActionTypeEnum::$UPDATE, "SHOW"=>"InsertUpdateDeleteParamTypeAreaForUpdate,InsertUpdateDeleteParamTypeWarningArea"),
				array("VALUE"=>"", "SHOW"=>"limitFixedParameterArea", "CUSTOMFUNCTION"=>"ChecklimitFixedParameterAreaVisible")
			), "");
		mtoolCommonFormInput("InsertUpdateDeleteTargetTable", $dafunc->InsertUpdateDeleteTargetTable,
			array($LANG_ENGLISH=>"Target Table Name", $LANG_JAPANESE=>"対象テーブル名"),
			array($LANG_ENGLISH=>"Please input Target Table Name for Insert/Update/Delete Action. (Use [Suffix of Function Name] if blank)", $LANG_JAPANESE=>"対象テーブル名を入力して下さい(Insert/Update/Delete操作向け) 空白の場合は[関数名の接尾語]が使用されます"),
			"text", "InsertUpdateDeleteTargetTableArea");
		mtoolCommonFormSelect("InsertUpdateDeleteParamType", $dafunc->InsertUpdateDeleteParamType,
			array($LANG_ENGLISH=>"Parameter Type", $LANG_JAPANESE=>"パラメータ種類"),
			array($LANG_ENGLISH=>"Please select Parameter Type", $LANG_JAPANESE=>"パラメータ種類を選択して下さい"), 
			array(
				array("VALUE"=>dafuncInsertUpdateDeleteParamTypeEnum::$VAL, "CAPTION"=>GetdafuncInsertUpdateDeleteParamTypeCaption(dafuncInsertUpdateDeleteParamTypeEnum::$VAL)),
				array("VALUE"=>dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT, "CAPTION"=>GetdafuncInsertUpdateDeleteParamTypeCaption(dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT))
			), array(), "InsertUpdateDeleteParamTypeArea");
		mtoolCommonFormSelect("InsertUpdateDeleteParamTypeForUpdate", $dafunc->InsertUpdateDeleteParamType,
			array($LANG_ENGLISH=>"Parameter Type", $LANG_JAPANESE=>"パラメータ種類"),
			array($LANG_ENGLISH=>"Please select Parameter Type", $LANG_JAPANESE=>"パラメータ種類を選択して下さい"), 
			array(
				array("VALUE"=>dafuncInsertUpdateDeleteParamTypeEnum::$VAL, "CAPTION"=>GetdafuncInsertUpdateDeleteParamTypeCaption(dafuncInsertUpdateDeleteParamTypeEnum::$VAL)),
				array("VALUE"=>dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT, "CAPTION"=>GetdafuncInsertUpdateDeleteParamTypeCaption(dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT)),
				array("VALUE"=>dafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE, "CAPTION"=>GetdafuncInsertUpdateDeleteParamTypeCaption(dafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE))
			), array(), "InsertUpdateDeleteParamTypeAreaForUpdate");
		if (is_numeric($dafunc->PID)) {
			if ($dafunc->InsertUpdateDeleteParamType == "") {
				// Not Selected. Show Warning
				$thisCaption = array($LANG_ENGLISH=>"WARNING! Please select Parameter Type.", $LANG_JAPANESE=>"WARNING! パラメータ種類を選択して下さい");
				?>
				<div class="row" id="InsertUpdateDeleteParamTypeWarningArea">
					<label class="col-md-3 control-label" for="inputtext"></label>
					<div class="col-md-9">
						<font color="red">
						<?php print htmlspecialchars($thisCaption[$lang]); ?>
						</font>
					</div>
				</div>
				<?php
			}
		}
		mtoolCommonFormInput("SortOrderColumns", $dafunc->SortOrderColumns,
			array($LANG_ENGLISH=>"Sort Order", $LANG_JAPANESE=>"Sort Order"),
			array($LANG_ENGLISH=>"Please input Sort Order", $LANG_JAPANESE=>"Sort Orderを入力して下さい"),
			"text", "SortOrderColumnsArea");
		mtoolCommonFormInput("DataClassBaseNameForSelectAction", $dafunc->DataClassBaseNameForSelectAction,
			array($LANG_ENGLISH=>"Data Class Name", $LANG_JAPANESE=>"データクラス名"),
			array($LANG_ENGLISH=>"Please input Data Class Name(Not a Source's Class Name. Name's Value is used if Blank)", $LANG_JAPANESE=>"格納先のデータクラス名を入力して下さい(ソースのクラス名ではない. 空の場合は名前が代わりに使われます)"),
			"text", "DataClassBaseNameForSelectActionArea");
		?>
		<div id="CheckDataClassNameForSelectActionArea">
        <?php
		if (is_numeric($dafunc->PID)) {
			$sourceDataClassName = CreateDataClassNameFromDAFunc($dafunc);
			$sourceDataClassBaseName = CreateDataClassBaseNameFromDAFunc($dafunc);
			?>
			<div class="row">
				<label class="col-md-3 control-label" for="inputtext"></label>
				<div class="col-md-9">
					<?php
					if ($lang == $LANG_JAPANESE) {
						?>
						FYI: 現在の設定に基づくソースのクラス名: <?php print $sourceDataClassName; ?>
						<?php
					} else {
						?>
						FYI: Class Name in Source based on current setting: <?php print $sourceDataClassName; ?>
						<?php
					}
					?>
				</div>
			</div>
			<?php
			$DAdataclass = new dataclassDBAccess();
			$Dataclass = $DAdataclass->GetdataclassByName($dafunc->ProjectPID, trim($sourceDataClassBaseName));
			if ($Dataclass != NULL) {
				// OK. Exist.
			} else {
				// Data Class is not exist.
				$thisCaption = array($LANG_ENGLISH=>"WARNING! Corresponding Data Class is not exist.", $LANG_JAPANESE=>"WARNING! 該当データクラス名が存在しません");
				?>
				<div class="row">
					<label class="col-md-3 control-label" for="inputtext"></label>
					<div class="col-md-9">
						<font color="red">
						<?php print htmlspecialchars($thisCaption[$lang]); ?>
						</font>
					</div>
				</div>
				<?php
			}
		}
		?>
        </div>
        <?php
		mtoolCommonFormCheckBoxForBoolean("SelectByDistinct", $dafunc->SelectByDistinct,
			array($LANG_ENGLISH=>"Distinct", $LANG_JAPANESE=>"Distinct"),
			array($LANG_ENGLISH=>"Select by Distinct", $LANG_JAPANESE=>"Distinct属性を付ける"),
			"SelectByDistinctArea", "", true);
		mtoolCommonFormInput("memo", $dafunc->memo,
			array($LANG_ENGLISH=>"memo", $LANG_JAPANESE=>"メモ"),
			array($LANG_ENGLISH=>"", $LANG_JAPANESE=>""),
			"text", "");
		mtoolCommonFormSelect("limitParameterType", $dafunc->limitParameterType,
			array($LANG_ENGLISH=>"Limit's Parameter Type", $LANG_JAPANESE=>"Limit値の種類"),
			array($LANG_ENGLISH=>"Please input Limit's Parameter Type (Default: no limit)", $LANG_JAPANESE=>"Limit値の種類を入力して下さい(デフォルト: Limitなし)"), 
			array(
				array("VALUE"=>"argument", "CAPTION"=>"Argument"),
				array("VALUE"=>"fixed", "CAPTION"=>"Fixed")
			), array(
				array("VALUE"=>"", "SHOW"=>"limitFixedParameterArea", "CUSTOMFUNCTION"=>"ChecklimitFixedParameterAreaVisible")
			), "limitParameterTypeArea");
		mtoolCommonFormInput("limitFixedParameter", $dafunc->limitFixedParameter,
			array($LANG_ENGLISH=>"Limit Parameter", $LANG_JAPANESE=>"Limit値"),
			array($LANG_ENGLISH=>"Please input Limit Parameter", $LANG_JAPANESE=>"Limit値を入力して下さい"),
			"text", "limitFixedParameterArea");
		mtoolCommonFormSelect("ORGroupType", $dafunc->ORGroupType,
			array($LANG_ENGLISH=>"OR Group Type", $LANG_JAPANESE=>"ORグループ種類"),
			array($LANG_ENGLISH=>"Please select OR Group Type", $LANG_JAPANESE=>"ORグループ種類を選択して下さい(ORグループを使用する場合のみ有効)"), 
			array(
				array("VALUE"=>dafuncORGroupTypeEnum::$ORANDOR, "CAPTION"=>GetdafuncORGroupTypeCaption(dafuncORGroupTypeEnum::$ORANDOR) . " [default]"),
				array("VALUE"=>dafuncORGroupTypeEnum::$ANDORAND, "CAPTION"=>GetdafuncORGroupTypeCaption(dafuncORGroupTypeEnum::$ANDORAND))
			), array(
			), "ORGroupTypeArea");
		
		if ($project->IsMySQL()) {
			mtoolCommonFormCheckBoxForBoolean("IsBlobTarget", $dafunc->IsBlobTarget,
				array($LANG_ENGLISH=>"Is Blob update Target?", $LANG_JAPANESE=>"BLOBデータ更新対象"),
				array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
				"IsBlobTargetArea", "", true);
		} else {
			?>
			<input name="IsBlobTarget" type="hidden" value="<?php print htmlspecialchars($dafunc->IsBlobTarget); ?>">
			<?php
		}
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dafunc->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafunc->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafunc->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafunc->PID); ?>">
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
    <p><a href="da_funcs.php?ProjectPID=<?php print urlencode($dafunc->ProjectPID); ?>&DAPID=<?php print urlencode($dafunc->daPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
