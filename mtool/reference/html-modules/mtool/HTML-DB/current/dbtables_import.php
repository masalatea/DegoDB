<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_USER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
	
class CompareDBTableForImport
{
	public $ImportDBTableName;
	public $TableAlreadyExists;
	public $IsSameWithoutOrder;
	public $IsSameIncludeOrder;
	public $RecommendedColumnExists;
	public $RecommendedColumnWarning;
	
	function Initialize($ProjectPID, $DAMySQLShowColumn, $import_db_table_name, $tablelist, &$any_not_matched)
	{
		$this->ImportDBTableName = $import_db_table_name;
		$this->TableAlreadyExists = false;
		$correspondingTable = NULL;
		for($j = 0 ; $j < count($tablelist); $j++) {
			$table = $tablelist[$j];
			
			if ($table->name == $this->ImportDBTableName) {
				$this->TableAlreadyExists = true;
				$correspondingTable = $table;
				break;
			}
		}
		if (!$this->TableAlreadyExists) {
			$any_not_matched = true;
		}

		$correspondingTableColumnList = array();
		$this->IsSameWithoutOrder = false;
		$this->IsSameIncludeOrder = false;
		if ($correspondingTable != NULL) {
			$ImportDBTableColumnList = $DAMySQLShowColumn->GetTableColumns($this->ImportDBTableName);
			
			$DAdbtablecolumns = new dbtablecolumnsDBAccess();
			$correspondingTableColumnList = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $correspondingTable->PID);
			$this->IsSameWithoutOrder = CheckIfSameTableColumnForAll($ImportDBTableColumnList, $correspondingTableColumnList, false);
			$this->IsSameIncludeOrder = CheckIfSameTableColumnForAll($ImportDBTableColumnList, $correspondingTableColumnList, true);
		}
		if (!$this->IsSameWithoutOrder) {
			$any_not_matched = true;
		}
		if (!$this->IsSameIncludeOrder) {
			$any_not_matched = true;
		}
		$this->RecommendedColumnExists = CheckIfRequiredColumnExistInTable($correspondingTableColumnList, $this->RecommendedColumnWarning);
	}
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DBTABLE_IMPORT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbtable_import.php");
include_once("dbtables_import_common.php");

$DoImportAllTable = trim(GetParam("DoImportAllTable"));

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	
	printPathOnTopForDBTable("Import DB Table", $ProjectPID, "", "");
	
	$DAdbtable = new dbtableDBAccess();
	$DAdbtablecolumns = new dbtablecolumnsDBAccess();
	$tablelist = $DAdbtable->GetdbtableList($ProjectPID); 
	
	$DAMySQLShowColumn = new MySQLShowColumnDBAccess();
	$DAMySQLShowColumn->Initialize($importMySQL);
	
	$ImportDBTableNameList = $DAMySQLShowColumn->GetTables();
	
	$any_not_matched = false;
	
	if (count($ImportDBTableNameList) > 0) {
		
		if ($DBWritePermission) {
			if ($DoImportAllTable != "") {
				for($i = 0 ; $i < count($ImportDBTableNameList); $i++) {
					$ImportDBTableName = $ImportDBTableNameList[$i];
					
					do_datable_import($importMySQL, $ProjectPID, $ImportDBTableName, "", "", "y", "y");				
				}
				// Reload Data
				$tablelist = $DAdbtable->GetdbtableList($ProjectPID); 
			}
		}
		$CompareDBTableForImportList = array();
		for($i = 0 ; $i < count($ImportDBTableNameList); $i++) {
			$ImportDBTableName = $ImportDBTableNameList[$i];
			
			$CompareDBTableForImportData = new CompareDBTableForImport();
			$CompareDBTableForImportData->Initialize($ProjectPID, $DAMySQLShowColumn, $ImportDBTableName, $tablelist, $any_not_matched);
			array_push($CompareDBTableForImportList, $CompareDBTableForImportData);
		}
		if ($DBWritePermission) {
			if ($any_not_matched) {
				?>
                <div style="background-color:#00F; margin:2px; padding:10px">
                <form action="dbtables_import.php" method="get">
                <input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
                <input name="DoImportAllTable" type="hidden" value="y">
                <input name="r" type="hidden" value="<?php print htmlspecialchars(makeRandStr(8)); ?>">
                <input name="DoImport" type="submit" value="Do All Import">
                </form>
				</div>
				<?php
			} else {
				?>
				<p>Note: All corresponding Database Table definition is exist and synchronized.</p>
				<?php
			}
		}
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>DB Table Name in Import Target</th>
			  <th>Table Design Already Exists in Mtool?</th>
			  <th>Matched? (without order)</th>
			  <th>Exactly Matched? (include order)</th>
              <?php if ($show_recommended_column_warning) { ?>
			  <th>Required Column Exist?</th>
              <?php } ?>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($CompareDBTableForImportList); $i++) {
			$CompareDBTableForImportData = $CompareDBTableForImportList[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($CompareDBTableForImportData->ImportDBTableName); ?></td>
			  <td><?php
			  	if ($CompareDBTableForImportData->TableAlreadyExists) {
					?>
					Exist
					<?php
				} else {
					?>
					<font color="red">Not Exist</font>
					<?php
				}
			  ?></td>
			  <td><?php
				if ($CompareDBTableForImportData->IsSameWithoutOrder) {
					?>
					Matched
					<?php
				} else {
					?>
					<font color="red">Not Matched</font>
					<?php
				}
			  ?></td>
              <td><?php
				if ($CompareDBTableForImportData->IsSameIncludeOrder) {
					?>
					Matched
					<?php
				} else {
					?>
					Not Matched
					<?php
					if ($CompareDBTableForImportData->IsSameWithoutOrder) {
						?>
                        <font size="-1">(You can ignore)</font>
                        <?php
					}
				}
			  ?>
              </td>
              <?php if ($show_recommended_column_warning) { ?>
              <td><?php
				if ($CompareDBTableForImportData->RecommendedColumnExists) {
					?>
                    Exists
					<?php
				} else {
					?>
                    <font color="red"><?php print $CompareDBTableForImportData->RecommendedColumnWarning; ?></font>
                    <?php
				}
			  ?>
              </td>
              <?php } ?>
			  <td><a href="dbtables_import_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&TableName=<?php print urlencode($CompareDBTableForImportData->ImportDBTableName); ?>&<?php print makeRandStr(8); ?>">Import for Each</a></td>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
		<?php
		
	} else {
		?>
    <p>none</p>
		<?php
	}
	
	?>
	<br>
	<br>
	<br>
	<p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
    <?php
}
?>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content
