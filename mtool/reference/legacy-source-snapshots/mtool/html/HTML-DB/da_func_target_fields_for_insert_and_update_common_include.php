<?php

// dafuncinserttargetfield と dafuncupdatetargetfield の両方がターゲット。
// 両方とも targetTableColumnName プロパティがある。

include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("da_func_insert_or_update_target_fields_update_list_order_lib.php");
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));

$DoSyncAll = trim(GetParam("DoSyncAll"));

// Array Parameter
$ActionTargetList = GetParam("ActionTargetList");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Class PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAFuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}

$DAdafunc = new dafuncDBAccess();
$dafunc = NULL;

$DAdataclass = new dataclassDBAccess();
$dataclass = NULL;
$BaseDataClassName = "";

$DAdataclassfields = new dataclassfieldsDBAccess();
$DAdafuncinserttargetfields = new dafuncinserttargetfieldsDBAccess();
$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
$DAdbtable = new dbtableDBAccess();
$DAdbtablecolumns = new dbtablecolumnsDBAccess();

function GetCorrespondingDBTableColumn($dataclassfield, $CorrespondingDBTableColumnList)
{
	$CorrespondingDBTableColumn = NULL;
	if ($CorrespondingDBTableColumnList != NULL) {
		for ( $j = 0 ; $j < count($CorrespondingDBTableColumnList); $j++) {
			$thisDBTableColumn = $CorrespondingDBTableColumnList[$j];
			
			if ($dataclassfield->name == $thisDBTableColumn->name) {
				$CorrespondingDBTableColumn = $thisDBTableColumn;
				break;
			}
		}
	}
	return $CorrespondingDBTableColumn;
}

function GetCorrespondingTargetField($dataclassfield, $dafunctargetfieldlist)
{
	// $dafunctargetfieldlist は dafuncinserttargetfield あるいは dafuncupdatetargetfield のいずれか。
	// 両方とも targetTableColumnName プロパティがある。
	
	for ($j = 0 ; $j < count($dafunctargetfieldlist); $j++) {
		$dafunctargetfield = $dafunctargetfieldlist[$j];
		
		if ($dafunctargetfield->targetTableColumnName == $dataclassfield->name) {
			return $dafunctargetfield;
		}
	}
	return NULL;
}

function GetWarningFieldTargetList($project, $dafunctargetfieldlist, $dataclassfieldlist, $CorrespondingDBTableColumnList)
{
	// $dafunctargetfieldlist は dafuncinserttargetfield あるいは dafuncupdatetargetfield のいずれか。
	// 両方とも targetTableColumnName プロパティがある。
	
	$warningFieldTargetList = array();
	for ($i = 0 ; $i < count($dafunctargetfieldlist); $i++) {
		$dafunctargetfield = $dafunctargetfieldlist[$i];
		
		$thisExist = false;
		for ($j = 0 ; $j < count($dataclassfieldlist); $j++) {
			$dataclassfield = $dataclassfieldlist[$j];
			
			if ($dafunctargetfield->targetTableColumnName == $dataclassfield->name) {
				
				$CorrespondingDBTableColumn = GetCorrespondingDBTableColumn($dataclassfield, $CorrespondingDBTableColumnList);
				if ($CorrespondingDBTableColumn) {
					if ($CorrespondingDBTableColumn->IsAutoIncrement() ||
					    $CorrespondingDBTableColumn->NotSupportedDataTypeForInsertOrUpdateBasedOnDBType($project)) {
						// In this case, this column should not be created. so, virtually "not exist";
					} else {
						// Other case (normally here)
						$thisExist = true;
						break;
					}
				}
			}
		}
		if (!$thisExist) {
			array_push($warningFieldTargetList, $dafunctargetfield);
		}
	}
	return $warningFieldTargetList;
}

function InitializeDAFuncTargetFieldList()
{
	global $currentActionType;
	global $DAdafuncinserttargetfields;
	global $DAdafuncupdatetargetfields;
	global $ProjectPID;
	global $DAPID;
	global $DAFuncPID;
	
	$dafunctargetfieldlist = array();
	
	switch($currentActionType) {
		case dafuncActionTypeEnum::$INSERT:
			$dafunctargetfieldlist = $DAdafuncinserttargetfields->GetdafuncinserttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID);
			break;
		case dafuncActionTypeEnum::$UPDATE:
			$dafunctargetfieldlist = $DAdafuncupdatetargetfields->GetdafuncupdatetargetfieldsList($ProjectPID, $DAPID, $DAFuncPID);
			break;
		default:
			die("INTERNAL ERROR! Unknown Action Type: " . $currentActionType);
	}
	return $dafunctargetfieldlist;
}
if ($NoError) {
	$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
	if ($dafunc == NULL) {
		?>
		<H3><font color="red">DB Access Function is not found. Please ask administrator if this continues.</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	printPathOnTopForDBAccessClass($PathCaption, $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	if ($dafunc->ActionType != $currentActionType) {
		?>
		<H3><font color="red">Error! This is only for <?php print $currentActionType; ?> function.</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {	
	
	$BaseDataClassName = $dafunc->GetBaseDataClassName();
	if (trim($BaseDataClassName) == "") {
		?>
		<H3><font color="red">Stop process. Data Class Name can't be determined from Name/Class Base Name for Select Action</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {	
	
	$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $BaseDataClassName);
	if ($dataclass == NULL) {
		?>
		<H3><font color="red">Stop process. Corresponding Data Class is not exist.</font></H3>
		<?php
		$NoError = false;
	}
}

InitializeOutputShortenedStringWithExpansion();

if ($NoError) {	
	?>
    <h3><font color="red">Corresponding Data Class exists: <?php print $dataclass->name; ?></font></h3>
	<?php
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	
	$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $dataclass->PID); 
	
	$dafunctargetfieldlist = InitializeDAFuncTargetFieldList();
	
	$CorrespondingDBTable = $DAdbtable->GetdbtableByName($ProjectPID, $dataclass->name);
	$CorrespondingDBTableColumnList = NULL;
	if ($CorrespondingDBTable != NULL) {
		$CorrespondingDBTableColumnList = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $CorrespondingDBTable->PID); 
	}
	
	if ($DoSyncAll != "") {
		
		for($i = 0 ; $i < count($dataclassfieldlist); $i++) {
			$dataclassfield = $dataclassfieldlist[$i];
			
			$ThisColumnShouldBeExist = false;
			for ($j = 0 ; $j < count($ActionTargetList); $j++) {
				$TargetField = $ActionTargetList[$j];
				
				if ($dataclassfield->name == $TargetField) {
					$ThisColumnShouldBeExist = true;
				}
			}
			
			$correspondingTargetField = GetCorrespondingTargetField($dataclassfield, $dafunctargetfieldlist);
			
			if ($ThisColumnShouldBeExist) {
				// This column should be exist
				$dafunctargetfield = NULL;
				switch($currentActionType) {
					case dafuncActionTypeEnum::$INSERT:
						$dafunctargetfield = new dafuncinserttargetfieldsData();
						break;
					case dafuncActionTypeEnum::$UPDATE:
						$dafunctargetfield = new dafuncupdatetargetfieldsData();
						break;
					default:
						die("INTERNAL ERROR! Unknown Action Type: " . $currentActionType);
				}
				$dafunctargetfield->ProjectPID = $ProjectPID;
				$dafunctargetfield->daPID = $DAPID;
				$dafunctargetfield->dafuncPID = $DAFuncPID;
				$dafunctargetfield->targetTableColumnName = $dataclassfield->name;
				$dafunctargetfield->ParameterType = "argument";
				$dafunctargetfield->FixedParameter = "";
				$dafunctargetfield->ParameterDataType = "";
				if ($correspondingTargetField == NULL) {
					// Insert
					$dafunctargetfield->PID = "";
					
					switch($currentActionType) {
						case dafuncActionTypeEnum::$INSERT:
							if($DAdafuncinserttargetfields->Insertdafuncinserttargetfields($dafunctargetfield) === FALSE) {
								?>
								<h3><font color="red">Failed to insert Insert Target Field <?php print $dataclassfield->name; ?>. Something strange. Please ask administrator if this continues.</font></h3>
								<?php
							} else {
								$dafunctargetfield->PID = $mtooldb->insert_id;;
								?>
								<h3><font color="red">Insert Target Field <?php print $dataclassfield->name; ?> was added</font></h3>
								<?php
								update_da_LastModifiedDT($DAPID, $ProjectPID);
								update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
							}
							break;
						case dafuncActionTypeEnum::$UPDATE:
							if($DAdafuncupdatetargetfields->Insertdafuncupdatetargetfields($dafunctargetfield) === FALSE) {
								?>
								<h3><font color="red">Failed to insert Update Target Field <?php print $dataclassfield->name; ?>. Something strange. Please ask administrator if this continues.</font></h3>
								<?php
							} else {
								$dafunctargetfield->PID = $mtooldb->insert_id;;
								?>
								<h3><font color="red">Update Target Field <?php print $dataclassfield->name; ?> was added</font></h3>
								<?php
								update_da_LastModifiedDT($DAPID, $ProjectPID);
								update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
							}
							break;
						default:
							die("INTERNAL ERROR! Unknown Action Type: " . $currentActionType);
					}
					
				} else {
					// Update
					// => Nothing to update
				}
			} else {
				// This column should not be exist.
				if ($correspondingTargetField != NULL) {
					switch($currentActionType) {
						case dafuncActionTypeEnum::$INSERT:
							if($DAdafuncinserttargetfields->Deletedafuncinserttargetfields($correspondingTargetField->PID, $ProjectPID) === FALSE) {
								// Failed
								?>
								<h3><font color="red">Error! Failed to delete Insert Target Fields. Something strange. Please ask administrator if this continues.</font></h3>
								<?php
								
							} else {
								// Success
								?>
								<h3><font color="red">Column <?php print $dataclassfield->name; ?> was deleted</font></h3>
								<?php
								update_da_LastModifiedDT($DAPID, $ProjectPID);
								update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
							}
							break;
						case dafuncActionTypeEnum::$UPDATE:
							if($DAdafuncupdatetargetfields->Deletedafuncupdatetargetfields($correspondingTargetField->PID, $ProjectPID) === FALSE) {
								// Failed
								?>
								<h3><font color="red">Error! Failed to delete Update Target Fields. Something strange. Please ask administrator if this continues.</font></h3>
								<?php
								
							} else {
								// Success
								?>
								<h3><font color="red">Column <?php print $dataclassfield->name; ?> was deleted</font></h3>
								<?php
								update_da_LastModifiedDT($DAPID, $ProjectPID);
								update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
							}
							break;
						default:
							die("INTERNAL ERROR! Unknown Action Type: " . $currentActionType);
					}
				}
			}
		}
		
		// Initialize Again
		$dafunctargetfieldlist = InitializeDAFuncTargetFieldList();
	}
	
	if (count($dataclassfieldlist) > 0) {
		?>
        <form action="<?php print $FormTarget; ?>" method="post">
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th rowspan="2" bgcolor="#DDDDDD">Data Class's Field Name</th>
			  <th rowspan="2" bgcolor="#DDDDDD">
              <?php
				switch($currentActionType) {
					case dafuncActionTypeEnum::$INSERT:
						print "Insert Target?";
						break;
					case dafuncActionTypeEnum::$UPDATE:
						print "Update Target?";
						break;
					default:
						die("INTERNAL ERROR! Unknown Action Type: " . $currentActionType);
				}
				mtool_output_checkbox_for_select_all();
			  ?>
              </th>
			  <th colspan="6">Corresponding DB Table Info with Same Name</th>
			  <th></th>
			</tr>
			<tr bgcolor="#ECECEC">
			  <th>DB Table Name</th>
			  <th>Data Type</th>
			  <th>Null</th>
			  <th>Key</th>
			  <th>Default</th>
			  <th>Extra</th>
			  <th></th>
			</tr>
          </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($dataclassfieldlist); $i++) {
			$dataclassfield = $dataclassfieldlist[$i];
			
			$CorrespondingDBTableColumn = GetCorrespondingDBTableColumn($dataclassfield, $CorrespondingDBTableColumnList);
			
			$ThisIsAutoIncrement = false;
			$ThisIsNotSupportedDataTypeForInsertOrUpdateBasedOnDBType = false;
			if ($CorrespondingDBTableColumn != NULL) {
				$ThisIsAutoIncrement = $CorrespondingDBTableColumn->IsAutoIncrement();
				$ThisIsNotSupportedDataTypeForInsertOrUpdateBasedOnDBType = $CorrespondingDBTableColumn->NotSupportedDataTypeForInsertOrUpdateBasedOnDBType($project);
			}
			
			$correspondingTargetField = GetCorrespondingTargetField($dataclassfield, $dafunctargetfieldlist);
			
			?>
			<tr>
			  <td><?php print htmlspecialchars($dataclassfield->name); ?></td>
			  <td>
				<?php
                if ($ThisIsAutoIncrement) {
					?>
					<font size="-2">This column is Auto Increment. So, it can't be asigned automatically</font>
                    <?php
				} else if ($ThisIsNotSupportedDataTypeForInsertOrUpdateBasedOnDBType) {
					?>
					<font size="-2">This Data Type is not supported for insert/update for this DataBase.</font>
                    <?php
				} else {
					if ($CorrespondingDBTableColumn != NULL) {
						?>
						<input name="ActionTargetList[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox(); ?>value="<?php print htmlspecialchars($dataclassfield->name); ?>"<?php if ($correspondingTargetField != NULL) { print " checked"; } ?>>
						<?php
					}
				}
                ?>
              </td>
			  <td><?php
				if ($CorrespondingDBTableColumn != NULL) {
					print $CorrespondingDBTableColumn->name;
				} else {
					?>
                    <font color="red">Not Exist</font>
                    <?php
				}
			   ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { OutputShortenedStringWithExpansion($CorrespondingDBTableColumn->datatype, 20); } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->IsNull; } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->IsKey; } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->IsDefault; } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->Extra; } ?></td>
			</tr>
			<?php
		}
		if (count($dataclassfieldlist) > 0) {
		?>
			<tr>
			  <td></td>
			  <td><input name="DoSyncAll" type="submit" value="UPDATE"></td>
			  <td></td>
			  <td></td>
			  <td></td>
			  <td></td>
			  <td></td>
			  <td></td>
			</tr>
        <?php
		}
		?>

        	</tbody>
		</table>
        <?php
		mtool_output_script_tag_for_multi_checkbox();
		?>
        <input name="ProjectPID" type="hidden" value="<?php print $ProjectPID; ?>">
        <input name="DAPID" type="hidden" value="<?php print $DAPID; ?>">
        <input name="DAFuncPID" type="hidden" value="<?php print $DAFuncPID; ?>">
        </form>
		<?php
		
		if ($dafunctargetfieldlist != NULL) {
			$warningFieldTargetList = GetWarningFieldTargetList($project, $dafunctargetfieldlist, $dataclassfieldlist, $CorrespondingDBTableColumnList);
			
			if (count($warningFieldTargetList) > 0) {
				?>
                <h3><font color="red">WARNING: Following Fields will be deleted upon update.</font></h3>
                <?php
				
				for ($i = 0 ; $i < count($warningFieldTargetList); $i++) {
					$warningField = $warningFieldTargetList[$i];
					
					if ($DoSyncAll != "") {
						switch($currentActionType) {
							case dafuncActionTypeEnum::$INSERT:
								if($DAdafuncinserttargetfields->Deletedafuncinserttargetfields($warningField->PID, $warningField->ProjectPID) === FALSE) {
									// Failed
									?>
									<h3><font color="red">Error! Failed to delete Insert Target Fields. Something strange. Please ask administrator if this continues.</font></h3>
									<?php
									
								} else {
									// Success
									?>
									<h3><font color="red">Column <?php print $warningField->targetTableColumnName; ?> was deleted</font></h3>
									<?php
									update_da_LastModifiedDT($DAPID, $ProjectPID);
									update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
								}
								break;
							case dafuncActionTypeEnum::$UPDATE:
								if($DAdafuncupdatetargetfields->Deletedafuncupdatetargetfields($warningField->PID, $warningField->ProjectPID) === FALSE) {
									// Failed
									?>
									<h3><font color="red">Error! Failed to delete Update Target Fields. Something strange. Please ask administrator if this continues.</font></h3>
									<?php
									
								} else {
									// Success
									?>
									<h3><font color="red">Column <?php print $warningField->targetTableColumnName; ?> was deleted</font></h3>
									<?php
									update_da_LastModifiedDT($DAPID, $ProjectPID);
									update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
								}
								break;
							default:
								die("INTERNAL ERROR! Unknown Action Type: " . $currentActionType);
						}
					} else {
						?>
						<h4><font color="red">Field Name: <?php print $warningField->targetTableColumnName; ?></font></h4>
						<?php
					}
				}
			}
		}
		
	} else {
		?>
<p>none</p>
		<?php
	}
	adjust_list_order_of_insert_or_update_target_fields_and_show_message($ProjectPID, $DAPID, $DAFuncPID);
	?>
    <br>
    <br>
    <br>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
    <?php

}

?>
