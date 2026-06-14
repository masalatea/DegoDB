<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafuncselecttargetfield = new dafuncselecttargetfieldsData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==

$dafuncselecttargetfield->ProjectPID = trim(GetParam("ProjectPID"));
$dafuncselecttargetfield->daPID = trim(GetParam("DAPID"));
$dafuncselecttargetfield->dafuncPID = trim(GetParam("DAFuncPID"));
$dafuncselecttargetfield->PID = trim(GetParam("PID"));
$dafuncselecttargetfield->targetTableName = trim(GetParam("targetTableName"));
$dafuncselecttargetfield->targetTableAliasName = trim(GetParam("targetTableAliasName"));
$dafuncselecttargetfield->targetTableColumnName = trim(GetParam("targetTableColumnName"));
$dafuncselecttargetfield->targetTableColumnPrefix = trim(GetParam("targetTableColumnPrefix"));
$dafuncselecttargetfield->targetTableColumnSuffix = trim(GetParam("targetTableColumnSuffix"));
$dafuncselecttargetfield->storeClassFieldName = trim(GetParam("storeClassFieldName"));
$dafuncselecttargetfield->GroupByTarget = trim(GetParam("GroupByTarget"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dafuncselecttargetfield->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncselecttargetfield->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafuncselecttargetfield->dafuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("da_func_select_target_fields_update_list_order_lib.php");
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dafuncselecttargetfield->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dafuncselecttargetfield->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
			$insertResult = $DAdafuncselecttargetfields->Insertdafuncselecttargetfields($dafuncselecttargetfield);
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
				$dafuncselecttargetfield->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DA_FUNC_SELECT_TARGET_FIELDS"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselecttargetfield->daPID, $dafuncselecttargetfield->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselecttargetfield->dafuncPID, $dafuncselecttargetfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dafuncselecttargetfield->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dafuncselecttargetfield->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dafuncselecttargetfield->PID)) {
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
		
	} else if (is_numeric($dafuncselecttargetfield->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdafuncselecttargetfields->Updatedafuncselecttargetfields($dafuncselecttargetfield);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC_SELECT_TARGET_FIELDS"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselecttargetfield->daPID, $dafuncselecttargetfield->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselecttargetfield->dafuncPID, $dafuncselecttargetfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdafuncselecttargetfields->Deletedafuncselecttargetfields($dafuncselecttargetfield->PID, $dafuncselecttargetfield->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DA_FUNC_SELECT_TARGET_FIELDS"); ?></font></h3>
                <?php
				update_da_LastModifiedDT($dafuncselecttargetfield->daPID, $dafuncselecttargetfield->ProjectPID);
				update_custom_proxy_LastModifiedDT_by_dbfunc($dafuncselecttargetfield->dafuncPID, $dafuncselecttargetfield->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dafuncselecttargetfield = $DAdafuncselecttargetfields->Getdafuncselecttargetfields($dafuncselecttargetfield->PID, $dafuncselecttargetfield->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Function Select Target Field PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dafuncselecttargetfield->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DA_FUNC_SELECT_TARGET_FIELDS");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC_SELECT_TARGET_FIELDS");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dafuncselecttargetfield != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBAccessClass($HeaderCaption, $dafuncselecttargetfield->ProjectPID, $dafuncselecttargetfield->daPID, $dafuncselecttargetfield->dafuncPID, $dafuncselecttargetfield->PID, "", "", "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_func_select_target_field_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("targetTableName", $dafuncselecttargetfield->targetTableName,
			array($LANG_ENGLISH=>"Target Table Name", $LANG_JAPANESE=>"対象テーブル名"),
			array($LANG_ENGLISH=>"Please input Target Table Name", $LANG_JAPANESE=>"対象テーブル名を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("targetTableAliasName", $dafuncselecttargetfield->targetTableAliasName,
			array($LANG_ENGLISH=>"Alias Table Name", $LANG_JAPANESE=>"テーブル別名(Alias)"),
			array($LANG_ENGLISH=>"Please input Alias Table Name [OPTIONAL]", $LANG_JAPANESE=>"テーブル別名(Alias)を入力して下さい [OPTIONAL]"),
			"text", "");
		mtoolCommonFormInput("targetTableColumnPrefix", $dafuncselecttargetfield->targetTableColumnPrefix,
			array($LANG_ENGLISH=>"Prefix for Column Name", $LANG_JAPANESE=>"カラム名の接頭辞"),
			array($LANG_ENGLISH=>"Please input Prefix for Column Type", $LANG_JAPANESE=>"カラム名の接頭辞を入力して下さい"), 
			"text", "");
		mtoolCommonFormInput("targetTableColumnName", $dafuncselecttargetfield->targetTableColumnName,
			array($LANG_ENGLISH=>"Target Column Name", $LANG_JAPANESE=>"対象カラム名"),
			array($LANG_ENGLISH=>"Please input Target Column Type", $LANG_JAPANESE=>"対象カラム名を入力して下さい"), 
			"text", "");
		mtoolCommonFormInput("targetTableColumnSuffix", $dafuncselecttargetfield->targetTableColumnSuffix,
			array($LANG_ENGLISH=>"Suffix for Column Name", $LANG_JAPANESE=>"カラム名の接尾辞"),
			array($LANG_ENGLISH=>"Please input Suffix for Column Type", $LANG_JAPANESE=>"カラム名の接尾辞を入力して下さい"), 
			"text", "");
		mtoolCommonFormInput("storeClassFieldName", $dafuncselecttargetfield->storeClassFieldName,
			array($LANG_ENGLISH=>"Store Class Field Name", $LANG_JAPANESE=>"クラス 格納フィールド名"),
			array($LANG_ENGLISH=>"Please input Store Class Field Name", $LANG_JAPANESE=>"クラスの格納フィールド名を入力して下さい"),
			"text", "");
		mtoolCommonFormCheckBoxForBoolean("GroupByTarget", $dafuncselecttargetfield->GroupByTarget,
			array($LANG_ENGLISH=>"Group-By Target", $LANG_JAPANESE=>"Group By対象"),
			array($LANG_ENGLISH=>"Include this field into Group-By Target (If target, check for all non-Aggregate functions)", $LANG_JAPANESE=>"Group Byの対象にする(対象にする場合、集約関数以外のフィールドすべてをもれなく設定)"),
			"", "", true);
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dafuncselecttargetfield->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafuncselecttargetfield->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafuncselecttargetfield->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafuncselecttargetfield->dafuncPID); ?>">
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($dafuncselecttargetfield->PID); ?>">
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
	adjust_list_order_of_select_target_fields_and_show_message($dafuncselecttargetfield->ProjectPID, $dafuncselecttargetfield->daPID, $dafuncselecttargetfield->dafuncPID);
	?>
	<p><a href="da_func_select_target_fields.php?ProjectPID=<?php print urlencode($dafuncselecttargetfield->ProjectPID); ?>&DAPID=<?php print urlencode($dafuncselecttargetfield->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncselecttargetfield->dafuncPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function's Select Target Field List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
