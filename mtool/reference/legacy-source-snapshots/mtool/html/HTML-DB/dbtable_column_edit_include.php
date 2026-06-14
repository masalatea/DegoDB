<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dbtablecolumn = new dbtablecolumnsData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$dbtablecolumn->ProjectPID = trim(GetParam("ProjectPID"));
$dbtablecolumn->dbtablePID = trim(GetParam("DBTablePID"));
$dbtablecolumn->PID = trim(GetParam("DBTableColumnPID"));
$dbtablecolumn->name = trim(GetParam("name"));
$dbtablecolumn->datatype = trim(GetParam("datatype"));
$dbtablecolumn->IsNull = trim(GetParam("IsNull"));
$dbtablecolumn->IsKey = trim(GetParam("IsKey"));
$dbtablecolumn->IsDefault = trim(GetParam("IsDefault"));
$dbtablecolumn->Extra = trim(GetParam("Extra"));
$dbtablecolumn->memo = trim(GetParam("memo"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($dbtablecolumn->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dbtablecolumn->dbtablePID)) {
	?>
    <H3><font color="red">DB Table is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$dbtablecolumn->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($dbtablecolumn->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdbtablecolumns = new dbtablecolumnsDBAccess();
			$insertResult = $DAdbtablecolumns->Insertdbtablecolumns($dbtablecolumn);
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
				$dbtablecolumn->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DBTABLE_COLUMN"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $dbtablecolumn->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($dbtablecolumn->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $dbtablecolumn->PID)) {
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
		
	} else if (is_numeric($dbtablecolumn->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdbtablecolumns = new dbtablecolumnsDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdbtablecolumns->UpdatedbtablecolumnsExcludeColumnListOrder($dbtablecolumn);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DBTABLE_COLUMN"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdbtablecolumns->Deletedbtablecolumns($dbtablecolumn->PID, $dbtablecolumn->ProjectPID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DBTABLE_COLUMN"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$dbtablecolumn = $DAdbtablecolumns->Getdbtablecolumns($dbtablecolumn->PID, $dbtablecolumn->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DBTable PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($dbtablecolumn->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DBTABLE_COLUMN");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DBTABLE_COLUMN");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $dbtablecolumn != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBTable($HeaderCaption, $dbtablecolumn->ProjectPID, $dbtablecolumn->dbtablePID, $dbtablecolumn->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="dbtable_column_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $dbtablecolumn->name,
			array($LANG_ENGLISH=>"Column Name", $LANG_JAPANESE=>"カラム名"),
			array($LANG_ENGLISH=>"Please input Column Name of DB Table", $LANG_JAPANESE=>"DBテーブルのカラム名を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("datatype", $dbtablecolumn->datatype,
			array($LANG_ENGLISH=>"Data Type", $LANG_JAPANESE=>"データ種類"),
			array($LANG_ENGLISH=>"Please input Data Type", $LANG_JAPANESE=>"データ種類を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("IsNull", $dbtablecolumn->IsNull,
			array($LANG_ENGLISH=>"Null", $LANG_JAPANESE=>"Null"),
			array($LANG_ENGLISH=>"Please input Null", $LANG_JAPANESE=>"Nullを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("IsKey", $dbtablecolumn->IsKey,
			array($LANG_ENGLISH=>"Key", $LANG_JAPANESE=>"Key"),
			array($LANG_ENGLISH=>"Please input Key", $LANG_JAPANESE=>"Keyを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("IsDefault", $dbtablecolumn->IsDefault,
			array($LANG_ENGLISH=>"Default", $LANG_JAPANESE=>"Default"),
			array($LANG_ENGLISH=>"Please input Default", $LANG_JAPANESE=>"Defaultを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("Extra", $dbtablecolumn->Extra,
			array($LANG_ENGLISH=>"Extra", $LANG_JAPANESE=>"Extra"),
			array($LANG_ENGLISH=>"Please input Extra", $LANG_JAPANESE=>"Extraを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("memo", $dbtablecolumn->memo,
			array($LANG_ENGLISH=>"Memo", $LANG_JAPANESE=>"メモ"),
			array($LANG_ENGLISH=>"Please input Memo", $LANG_JAPANESE=>"メモを入力して下さい"),
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($dbtablecolumn->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dbtablecolumn->ProjectPID); ?>">
		<input name="DBTablePID" type="hidden" value="<?php print htmlspecialchars($dbtablecolumn->dbtablePID); ?>">
		<input name="DBTableColumnPID" type="hidden" value="<?php print htmlspecialchars($dbtablecolumn->PID); ?>">
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
    <p><a href="dbtable_columns.php?ProjectPID=<?php print urlencode($dbtablecolumn->ProjectPID); ?>&DBTablePID=<?php print urlencode($dbtablecolumn->dbtablePID); ?>&<?php print makeRandStr(8); ?>">Back to Column List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
