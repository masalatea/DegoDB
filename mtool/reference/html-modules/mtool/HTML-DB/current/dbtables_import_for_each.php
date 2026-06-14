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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbtable_import.php");
include_once("dbtables_import_common.php");

$TableName = trim(GetParam("TableName"));
$FieldName = trim(GetParam("FieldName"));
$DoImport = trim(GetParam("DoImport"));
$DoImportAll = trim(GetParam("DoImportAll"));
$IncludeOrder = trim(GetParam("IncludeOrder"));

if ($NoError) {
	
	printPathOnTopForDBTable("Import DB Table", $ProjectPID, "", "");
	
	$DAdbtable = new dbtableDBAccess();
	$ImportDBTableList = $DAdbtable->GetdbtableList($ProjectPID); 
	
	if (array_key_exists($TableName, $ImportDBTableList)) {
		?>
		<H3><font color="red">Specified Table Name is not exist in Import Target. Something Strange. Please ask administrator if this continues. </font></H3>
		<?php
		$NoError = false;
	}
}

InitializeOutputShortenedStringWithExpansion();

if ($NoError) {
	?>
	<h3>Table: <?php print htmlspecialchars($TableName); ?></H3>
	<?php
	
	if ($DoImport != "" || $DoImportAll != "") {
		do_datable_import($importMySQL, $ProjectPID, $TableName, $FieldName, $DoImport, $DoImportAll, $IncludeOrder);
	}
	
	$DAdbtable = new dbtableDBAccess();
	$DAdbtablecolumns = new dbtablecolumnsDBAccess();
	
	$correspondingTable = NULL;
	$correspondingTableColumnExist = false;
	$correspondingTableColumnList = NULL;
	$tablelist = NULL;
	
	InitializeImportCurrentTableInfo($ProjectPID, $TableName, $correspondingTable, $correspondingTableColumnExist, $correspondingTableColumnList, $tablelist);
	
	$DAdataclass = new dataclassDBAccess();
	$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $TableName);
	
	$DAMySQLShowColumn = new MySQLShowColumnDBAccess();
	$DAMySQLShowColumn->Initialize($importMySQL);
	$ImportDBTableColumnList = $DAMySQLShowColumn->GetTableColumns($TableName);
	if (count($ImportDBTableColumnList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th colspan="6" align="center" bgcolor="#E5E5E5">Table in Import Target</th>
			  <th colspan="6">Mtool Design</th>
			  <th rowspan="2">Memo</th>
			  <th rowspan="2">Matched? (without order)</th>
			  <th rowspan="2">Exactly Matched? (include order)</th>
			  <th rowspan="2"></th>
			  </tr>
			<tr bgcolor="#ECECEC">
			  <th bgcolor="#E5E5E5">Field</th>
			  <th bgcolor="#E5E5E5">Type</th>
			  <th bgcolor="#E5E5E5">Null</th>
			  <th bgcolor="#E5E5E5">Key</th>
			  <th bgcolor="#E5E5E5">Default</th>
			  <th bgcolor="#E5E5E5">Extra</th>
			  <th>Column</th>
			  <th>Data Type</th>
			  <th>Null</th>
			  <th>Key</th>
			  <th>Default</th>
			  <th>Extra</th>
			  </tr>
            </thead>
            <tbody>
		<?php
		
		for($i = 0 ; $i < count($ImportDBTableColumnList); $i++) {
			$ImportDBTableColumn = $ImportDBTableColumnList[$i];
			$correspondingTableColumn = GetCorrespondingTableColumn($ImportDBTableColumn->Field, $correspondingTableColumnList);
			?>
			<tr>
			  <td><?php print htmlspecialchars($ImportDBTableColumn->Field); ?></td>
			  <td><?php OutputShortenedStringWithExpansion($ImportDBTableColumn->Type, 20); ?></td>
			  <td><?php print htmlspecialchars($ImportDBTableColumn->IsNull); ?></td>
			  <td><?php print htmlspecialchars($ImportDBTableColumn->IsKey); ?></td>
			  <td><?php print htmlspecialchars($ImportDBTableColumn->IsDefault); ?></td>
			  <td><?php print htmlspecialchars($ImportDBTableColumn->Extra); ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  print $correspondingTableColumn->name;
			  }
			  if ($correspondingTableColumnExist == false) {
				  print "No Table Design";
			  }
			  ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  OutputShortenedStringWithExpansion($correspondingTableColumn->datatype, 20);
			  }
			  ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  print $correspondingTableColumn->IsNull;
			  }
			  ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  print $correspondingTableColumn->IsKey;
			  }
			  ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  print $correspondingTableColumn->IsDefault;
			  }
			  ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  print $correspondingTableColumn->Extra;
			  }
			  ?></td>
			  <td><?php
              if ($correspondingTableColumn != NULL) {
				  print $correspondingTableColumn->memo;
			  }
			  ?></td>
			  <td>
              <?php
				$IsSameTableColumnExcludeOrder = CheckIfSameTableColumnExcludeOrder($ImportDBTableColumn, $correspondingTableColumn);
				if ($IsSameTableColumnExcludeOrder) {
					print "Matched";
				} else {
					?>
					<font color="red">Not Matched</font>
					<a href="dbtables_import_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&TableName=<?php print urlencode($TableName); ?>&FieldName=<?php print urlencode($ImportDBTableColumn->Field); ?>&DoImport=y&IncludeOrder=&<?php print makeRandStr(8); ?>">Import This Column (Exclude Order)</a><br>
					<?php
				  }
				  ?>
              </td>
			  <td>
				<?php
                $ExpectedColumnListOrder = ($i + 1);
                $IsSameTableColumnIncludeOrder = CheckIfSameTableColumnIncludeOrder($ImportDBTableColumn, $correspondingTableColumn, $ExpectedColumnListOrder);
                if ($IsSameTableColumnIncludeOrder) {
	                print "Matched";
                } else {
					?>
					Not Matched (you can ignore)
					<?php
                }
                ?>
              </td>
			</tr>
			<?php
		}
			?>
			<tr>
			  <td colspan="13"></td>
			  <td bgcolor="#FFFDDB"><a href="dbtables_import_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&TableName=<?php print urlencode($TableName); ?>&DoImportAll=y&IncludeOrder=&<?php print makeRandStr(8); ?>">Import All (Exclude Order)</a></td>
			  <td bgcolor="#FFFDDB">
				<a href="dbtables_import_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&TableName=<?php print urlencode($TableName); ?>&DoImportAll=y&IncludeOrder=y&<?php print makeRandStr(8); ?>">Import All (Include Order)</a>
              </td>
			</tr>
        	</tbody>
		</table>
        
		<?php
		
		$NotExistTableColumnList = array();
		if ($ImportDBTableColumnList != NULL && $correspondingTableColumnList != NULL) {
			if (is_array($ImportDBTableColumnList) && is_array($correspondingTableColumnList)) {
				for ($i = 0 ; $i < count($correspondingTableColumnList); $i++) {
					$correspondingTableColumn = $correspondingTableColumnList[$i];
					
					$isExist = false;
					for($j = 0 ; $j < count($ImportDBTableColumnList); $j++) {
						$ImportDBTableColumn = $ImportDBTableColumnList[$j];
						
						if ($ImportDBTableColumn->Field == $correspondingTableColumn->name) {
							$isExist = true;
							break;
						}
					}
					if (!$isExist) {
						array_push($NotExistTableColumnList, $correspondingTableColumn);
					}
				}
			}
		}
		if (count($NotExistTableColumnList) > 0) {
			?>
            <H3><font color="red">WARNING: Those Column are exists in Mtool design but not exists in Import Target</font></H3>
            <h4><font color="red">
            <?php
			for ($i = 0 ; $i < count($NotExistTableColumnList); $i++) {
				$NotExistTableColumn = $NotExistTableColumnList[$i];
				print "Column: " . $NotExistTableColumn->name;
				?>
				(This column will be deleted by "Import All".)
				<?php
				print "<br>";
			}
			?>
            </font></h4>
            <?php
		}
		
	} else {
		?>
<p>none</p>
		<?php
	}
	?>
    <br>
    <br>
    <br>
    <p><a href="./dbtables_import.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Import Table List</a></p>
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
