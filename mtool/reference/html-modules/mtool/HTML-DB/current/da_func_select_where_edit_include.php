<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafuncselectwhere = new dafuncselectwhereData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dafuncselectwhere->ProjectPID = trim(GetParam("ProjectPID"));
$dafuncselectwhere->daPID = trim(GetParam("DAPID"));
$dafuncselectwhere->dafuncPID = trim(GetParam("DAFuncPID"));
$dafuncselectwhere->PID = trim(GetParam("PID"));
$dafuncselectwhere->targetTableName = trim(GetParam("targetTableName"));
$dafuncselectwhere->targetTableAliasName = trim(GetParam("targetTableAliasName"));
$dafuncselectwhere->targetTableColumnName = trim(GetParam("targetTableColumnName"));
$dafuncselectwhere->ParameterType = trim(GetParam("ParameterType"));
$dafuncselectwhere->FixedParameter = trim(GetParam("FixedParameter"));
$dafuncselectwhere->AnotherTableName = trim(GetParam("AnotherTableName"));
$dafuncselectwhere->AnotherTableAliasName = trim(GetParam("AnotherTableAliasName"));
$dafuncselectwhere->AnotherFieldName = trim(GetParam("AnotherFieldName"));
switch($dafuncselectwhere->ParameterType) {
	case "argument":
	case "fixed":
		$dafuncselectwhere->JoinType      = trim(GetParam("InnerJoinType"));
		break;
	case "anotherfield":
		$dafuncselectwhere->JoinType      = trim(GetParam("OuterJoinType"));
		break;
}
$dafuncselectwhere->ORGroup = trim(GetParam("ORGroup"));
$dafuncselectwhere->ParameterDataType = trim(GetParam("ParameterDataType"));
$dafuncselectwhere->RelationalOperator = trim(GetParam("RelationalOperator"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafuncselectwhere->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncselectwhere->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncselectwhere->dafuncPID)) {
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
		$dafuncselectwhere->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dafuncselectwhere->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
			$insertResult = $DAdafuncselectwhere->Insertdafuncselectwhere($dafuncselectwhere);
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
				$dafuncselectwhere->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DA_FUNC_SELECT_WHERE"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselectwhere->daPID, $dafuncselectwhere->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselectwhere->dafuncPID, $dafuncselectwhere->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dafuncselectwhere->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dafuncselectwhere->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dafuncselectwhere->PID)) {
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
		
	} else if (is_numeric($dafuncselectwhere->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdafuncselectwhere->Updatedafuncselectwhere($dafuncselectwhere);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC_SELECT_WHERE"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselectwhere->daPID, $dafuncselectwhere->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselectwhere->dafuncPID, $dafuncselectwhere->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdafuncselectwhere->Deletedafuncselectwhere($dafuncselectwhere->PID, $dafuncselectwhere->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DA_FUNC_SELECT_WHERE"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselectwhere->daPID, $dafuncselectwhere->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselectwhere->dafuncPID, $dafuncselectwhere->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dafuncselectwhere = $DAdafuncselectwhere->Getdafuncselectwhere($dafuncselectwhere->PID, $dafuncselectwhere->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Function Select's Where PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dafuncselectwhere->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DA_FUNC_SELECT_WHERE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC_SELECT_WHERE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dafuncselectwhere != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBAccessClass($HeaderCaption, $dafuncselectwhere->ProjectPID, $dafuncselectwhere->daPID, $dafuncselectwhere->dafuncPID, "", $dafuncselectwhere->PID, "", "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
<script>
var CheckJoinTableNameAreaVisible = function()
{
	var ParameterTypeSelectedValue = $('#ParameterType').val();
	
	switch (ParameterTypeSelectedValue) {
		case 'argument':
		case 'fixed':
			var InnerJoinTypeSelectedValue = $('#InnerJoinType').val();
			switch (InnerJoinTypeSelectedValue) {
				case 'inner':
					return true;
			}
			break;
		case 'anotherfield':
			var OuterJoinTypeSelectedValue = $('#OuterJoinType').val();
			switch (OuterJoinTypeSelectedValue) {
				case 'left':
				case 'right':
					return true;
			}
			break;
	}
	return false;
};
</script>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_func_select_where_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormSelect("ParameterType", $dafuncselectwhere->ParameterType,
			array($LANG_ENGLISH=>"Parameter Type", $LANG_JAPANESE=>"パラメータ種類"),
			array($LANG_ENGLISH=>"Please input Parameter Type", $LANG_JAPANESE=>"パラメータ種類を入力して下さい"), 
			array(
				array("VALUE"=>"argument", "CAPTION"=>"Argument"),
				array("VALUE"=>"fixed", "CAPTION"=>"Fixed"),
				array("VALUE"=>"anotherfield", "CAPTION"=>"Another Field")
			), array(
				array("VALUE"=>"argument,fixed", "SHOW"=>"InnerJoinTypeArea,ParameterDataTypeArea"),
				array("VALUE"=>"fixed", "SHOW"=>"FixedParameterArea"),
				array("VALUE"=>"anotherfield", "SHOW"=>"AnotherTableNameArea,AnotherTableAliasNameArea,AnotherFieldNameArea,OuterJoinTypeArea,ExchangeValueArea")
			), "");
		mtoolCommonFormSelect("OuterJoinType", $dafuncselectwhere->JoinType,
			array($LANG_ENGLISH=>"Join Type", $LANG_JAPANESE=>"Join種類"),
			array($LANG_ENGLISH=>"Where Statement", $LANG_JAPANESE=>"Where句"), 
			array(
				array("VALUE"=>dafuncselectwhereJoinTypeEnum::$LEFT, "CAPTION"=>GetdafuncselectwhereJoinTypeCaption(dafuncselectwhereJoinTypeEnum::$LEFT)),
				array("VALUE"=>dafuncselectwhereJoinTypeEnum::$RIGHT, "CAPTION"=>GetdafuncselectwhereJoinTypeCaption(dafuncselectwhereJoinTypeEnum::$RIGHT))
			), array(
			), "OuterJoinTypeArea");
		
		mtoolCommonFormSelect("InnerJoinType", $dafuncselectwhere->JoinType,
			array($LANG_ENGLISH=>"Join Type", $LANG_JAPANESE=>"Join種類"),
			array($LANG_ENGLISH=>"Where Statement", $LANG_JAPANESE=>"Where句"), 
			array(
				array("VALUE"=>dafuncselectwhereJoinTypeEnum::$INNER, "CAPTION"=>GetdafuncselectwhereJoinTypeCaption(dafuncselectwhereJoinTypeEnum::$INNER))
			), array(
			), "InnerJoinTypeArea");
		
		mtoolCommonFormInput("targetTableName", $dafuncselectwhere->targetTableName,
			array($LANG_ENGLISH=>"Target Table Name", $LANG_JAPANESE=>"対象テーブル名"),
			array($LANG_ENGLISH=>"Please input Target Table Name", $LANG_JAPANESE=>"対象テーブル名を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("targetTableAliasName", $dafuncselectwhere->targetTableAliasName,
			array($LANG_ENGLISH=>"Target Table Alias Name", $LANG_JAPANESE=>"対象テーブル別名"),
			array($LANG_ENGLISH=>"Please input Target Table Alias Name", $LANG_JAPANESE=>"対象テーブル別名(Alias)を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("targetTableColumnName", $dafuncselectwhere->targetTableColumnName,
			array($LANG_ENGLISH=>"Target Column Name", $LANG_JAPANESE=>"対象カラム名"),
			array($LANG_ENGLISH=>"Please input Target Column Type", $LANG_JAPANESE=>"対象カラム名を入力して下さい"), 
			"text", "");
		mtoolCommonFormInput("RelationalOperator", $dafuncselectwhere->RelationalOperator,
			array($LANG_ENGLISH=>"Relational Operator", $LANG_JAPANESE=>"関係演算子"),
			array($LANG_ENGLISH=>"Please input Relational Operator(If blank, regard as '=')", $LANG_JAPANESE=>"関係演算子を入力して下さい(空白の場合は '=')"),
			"text", "");
		CheckIfValidMysqlRelationalOperator($dafuncselectwhere->RelationalOperator);
		
		mtoolCommonFormRadioButton("ParameterDataType", $dafuncselectwhere->ParameterDataType,
			array($LANG_ENGLISH=>"Parameter's Data Type", $LANG_JAPANESE=>"データ種類"),
			array($LANG_ENGLISH=>"Please input Parameter's Data Type(Regard as string if blank)", $LANG_JAPANESE=>"データ種類を入力して下さい(空白の場合はstring)"), 
			array(
				array("VALUE"=>dafuncselectwhereParameterDataTypeEnum::$DEFAULT, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncselectwhereParameterDataTypeEnum::$DEFAULT)),
				array("VALUE"=>dafuncselectwhereParameterDataTypeEnum::$RAW, "CAPTION"=>GetParameterDataTypeCaptionCommon(dafuncselectwhereParameterDataTypeEnum::$RAW))
			), array(
			), "ParameterDataTypeArea");
		
		mtoolCommonFormInput("FixedParameter", $dafuncselectwhere->FixedParameter,
			array($LANG_ENGLISH=>"Fixed Parameter", $LANG_JAPANESE=>"固定値"),
			array($LANG_ENGLISH=>"Please input Fixed Parameter", $LANG_JAPANESE=>"固定値を入力して下さい"),
			"text", "FixedParameterArea");
		?>
		<div class="row" id="ExchangeValueArea">
		  <label class="col-md-3 control-label" for="inputtext"></label>
		  <div class="col-md-9">
          	<span id="exchangevaluebutton">Exchange</span>
<script>
$(function(){
    $("#exchangevaluebutton").click(function() {
		var targetTableNameValue = $("#targetTableName").val();
		var targetTableAliasNameValue = $("#targetTableAliasName").val();
		var targetTableColumnNameValue = $("#targetTableColumnName").val();
		var AnotherTableNameValue = $("#AnotherTableName").val();
		var AnotherTableAliasNameValue = $("#AnotherTableAliasName").val();
		var AnotherFieldNameValue = $("#AnotherFieldName").val();
		
		$("#AnotherTableName").val(targetTableNameValue);
		$("#AnotherTableAliasName").val(targetTableAliasNameValue);
		$("#AnotherFieldName").val(targetTableColumnNameValue);
		$("#targetTableName").val(AnotherTableNameValue);
		$("#targetTableAliasName").val(AnotherTableAliasNameValue);
		$("#targetTableColumnName").val(AnotherFieldNameValue);
    })
});
</script>
		  </div>
		</div>
        <?php
		mtoolCommonFormInput("AnotherTableName", $dafuncselectwhere->AnotherTableName,
			array($LANG_ENGLISH=>"Another Table Name", $LANG_JAPANESE=>"参照先テーブル名"),
			array($LANG_ENGLISH=>"Please input Another Table Name", $LANG_JAPANESE=>"参照先テーブル名を入力して下さい"),
			"text", "AnotherTableNameArea");
		mtoolCommonFormInput("AnotherTableAliasName", $dafuncselectwhere->AnotherTableAliasName,
			array($LANG_ENGLISH=>"Another Table Alias Name", $LANG_JAPANESE=>"参照先テーブル別名"),
			array($LANG_ENGLISH=>"Please input Another Table Alias Name", $LANG_JAPANESE=>"参照先テーブル別名(Alias)を入力して下さい"),
			"text", "AnotherTableAliasNameArea");
		mtoolCommonFormInput("AnotherFieldName", $dafuncselectwhere->AnotherFieldName,
			array($LANG_ENGLISH=>"Another Table's Field Name", $LANG_JAPANESE=>"参照先テーブルのカラム名"),
			array($LANG_ENGLISH=>"Please input Another Table's Field Name", $LANG_JAPANESE=>"参照先テーブルのカラム名を入力して下さい"),
			"text", "AnotherFieldNameArea");
		mtoolCommonFormInput("ORGroup", $dafuncselectwhere->ORGroup,
			array($LANG_ENGLISH=>"OR Group", $LANG_JAPANESE=>"ORグループ"),
			array($LANG_ENGLISH=>"Please input OR Group", $LANG_JAPANESE=>"ORグループを入力して下さい"),
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dafuncselectwhere->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafuncselectwhere->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafuncselectwhere->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafuncselectwhere->dafuncPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($dafuncselectwhere->PID); ?>">
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
    <p><a href="da_func_select_where.php?ProjectPID=<?php print urlencode($dafuncselectwhere->ProjectPID); ?>&DAPID=<?php print urlencode($dafuncselectwhere->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncselectwhere->dafuncPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function - Select's Where List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
