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

function CheckIfAllColumnExistInClassAndDefinitionMatched($project, $dbtablecolumnlist, $dataclassfieldlist, &$MatchOrder)
{
	if ($dbtablecolumnlist == NULL) {
		return true;
	}
	if ($dbtablecolumnlist == NULL && $dataclassfieldlist == NULL) {
		return true;
	}
	if ($dbtablecolumnlist != NULL && $dataclassfieldlist != NULL) {
		if (is_array($dbtablecolumnlist) && is_array($dataclassfieldlist)) {
			for ($i = 0 ; $i < count($dbtablecolumnlist) ; $i++) {
				$dbtablecolumn = $dbtablecolumnlist[$i];
				
				$correspondingDataClassField = NULL;
				$isMatched = false;
				$MatchOrder = false;
				check_mtool_dataclasses_field_match_order($project, $correspondingDataClassField, $isMatched, $MatchOrder, $dataclassfieldlist, $dbtablecolumn, $i);
				
				if (!$isMatched) {
					return false;
				}
				
				// if ($dataclassfieldlist) {
				// 	for ($j = 0 ; $j < count($dataclassfieldlist); $j++) {
				// 		$dataclassfield = $dataclassfieldlist[$j];
				// 		
				// 		if ($dbtablecolumn->name == $dataclassfield->name) {
				// 			if (GetSourceDataTypeFromDatabaseDataTypeForGeneral($project, $dbtablecolumn->datatype) == $dataclassfield->datatype) {
				// 				$isMatched = true;
				// 				$MatchOrder = ($i == $j);
				// 			}
				// 		}
				// 	}
				// }
			}
			return true;
		}
	}
	return false;
}

class DataClassSyncForImport
{
	public $dbtable;
	public $correspondingDataClass;
	public $IsMatched;
	public $MatchOrder;
	
	public function Initialize($project, $dbtable, $dataclasslist, &$any_not_matched)
	{
		$this->dbtable = $dbtable;
		
		$this->correspondingDataClass = NULL;
		for($j = 0 ; $j < count($dataclasslist); $j++) {
			$dataclass = $dataclasslist[$j];
			
			if ($this->dbtable->name == $dataclass->name) {
				$this->correspondingDataClass = $dataclass;
			}
		}
		if (!$this->correspondingDataClass != NULL) {
			$any_not_matched = true;
		}
		
		$this->IsMatched = false;
		$this->MatchOrder = false;
		if ($this->correspondingDataClass != NULL) {
			$DAdataclassfields = new dataclassfieldsDBAccess();
			$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($project->PID, $this->correspondingDataClass->PID);
			
			$DAdbtablecolumns = new dbtablecolumnsDBAccess();
			$dbtablecolumnslist = $DAdbtablecolumns->GetdbtablecolumnsList($project->PID, $this->dbtable->PID); 
			
			$this->IsMatched = CheckIfAllColumnExistInClassAndDefinitionMatched($project, $dbtablecolumnslist, $dataclassfieldlist, $this->MatchOrder);
		}
		if (!$this->IsMatched) {
			$any_not_matched = true;
		}
		if ($this->IsMatched && $this->MatchOrder) {
			// Matched
		} else {
			$any_not_matched = true;
		}
	}
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DATA_CLASS_SYNC_WITH_DBTABLE"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dataclasses_sync.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dataclasse_fields_sync.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DoSyncAllClasses = trim(GetParam("DoSyncAllClasses"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAProject = new ProjectDBAccess();
$project = NULL;
if ($NoError) {
	$project = $DAProject->GetProject($ProjectPID);
	if ($project == NULL) {
		?>
		<H3><font color="red">Unknown Project. Please check Project Setting.</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($project->PID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForSyncDataClassWithDBTable("Sync Data Class with DB Table", $project->PID, "");
	
	$DAdbtable = new dbtableDBAccess();
	$dbtablelist = $DAdbtable->GetdbtableList($project->PID); 
	
	$DAdataclass = new dataclassDBAccess();
	$dataclasslist = $DAdataclass->GetdataclassList($project->PID); 
	
	$any_not_matched = false;
	
	if (count($dbtablelist) > 0) {
		
		if ($DBWritePermission) {
			if ($DoSyncAllClasses != "") {
				// --------------------------------
				// Synchronize Data Class from DB Table
				for($i = 0 ; $i < count($dbtablelist); $i++) {
					$dbtable = $dbtablelist[$i];
					
					mtool_dataclasses_sync_for_each($project, $dbtable, $dbtable->name, "", "", "y", "y");
				}
				// Reload Data
				$dataclasslist = $DAdataclass->GetdataclassList($project->PID); 
				
				// --------------------------------
				// Synchronize Inherit Fields
				$show_first_message_for_synchronize_inherit_field = true;
				for($i = 0 ; $i < count($dataclasslist); $i++) {
					$dataclass = $dataclasslist[$i];
					
                    if (trim($dataclass->InheritParentDataClassName) != "") {
						if ($show_first_message_for_synchronize_inherit_field) {
							?>
                            <h4>Synchronize Inherit Fields</h4>
                            <?php
							$show_first_message_for_synchronize_inherit_field = false;
						}
						mtool_dataclass_fields_sync_do($project->PID, $dataclass->PID, "y", "y");
					}
				}
			}
		}
		$DataClassSyncForImportList = array();
		for($i = 0 ; $i < count($dbtablelist); $i++) {
			$dbtable = $dbtablelist[$i];
			
			$DataClassSyncForImportData = new DataClassSyncForImport();
			$DataClassSyncForImportData->Initialize($project, $dbtable, $dataclasslist, $any_not_matched);
			
			array_push($DataClassSyncForImportList, $DataClassSyncForImportData);
		}
		if ($DBWritePermission) {
			
			// Check if need to synchronize Dataclass Inherit Fields
			for($i = 0 ; $i < count($dataclasslist); $i++) {
				$dataclass = $dataclasslist[$i];
				
				$IsSynghronizeTarget = false;
				$NotAllFieldExistForInheritClassFlag = false;
				$NotAllFieldExistAndOrderMatchedForInheritClassFlag = false;
				check_mtool_dataclass_field_sync_status($ProjectPID, $dataclass, $IsSynghronizeTarget, $NotAllFieldExistForInheritClassFlag, $NotAllFieldExistAndOrderMatchedForInheritClassFlag);
				
				if ($NotAllFieldExistForInheritClassFlag || $NotAllFieldExistAndOrderMatchedForInheritClassFlag) {
					$any_not_matched = true;
					break;
				}
			}
			
			if ($any_not_matched) {
				?>
                <div style="background-color:#00F; margin:2px; padding:10px">
                <form action="dataclasses_sync.php" method="get">
                <input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
                <input name="DoSyncAllClasses" type="hidden" value="y">
                <input name="r" type="hidden" value="<?php print htmlspecialchars(makeRandStr(8)); ?>">
                <input name="DoImport" type="submit" value="Do All Syncronize">
                </form>
				</div>
				<?php
			} else {
				?>
				<p>Note: All corresponding Data Class is exist and synchronized.</p>
				<?php
			}
		}
		
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>DB Table Name</th>
			  <th>Corresponding Data Class</th>
			  <th>Matched? (without order)</th>
			  <th>Exactly Matched? (include order)</th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($DataClassSyncForImportList); $i++) {
			$DataClassSyncForImportData = $DataClassSyncForImportList[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($DataClassSyncForImportData->dbtable->name); ?></td>
              <td><?php
				if ($DataClassSyncForImportData->correspondingDataClass != NULL) {
					?>
					Exist
					<?php
				} else {
					?>
					<font color="red">Not Exist</font>
					<?php
				}
			  ?></td>
			  <td>
              <?php
				if ($DataClassSyncForImportData->IsMatched) {
					?>
					Matched
					<?php
				} else {
					?>
					<font color="red">Not Matched</font>
					<?php
				}
			  ?>
              </td>
			  <td>
              <?php
				if ($DataClassSyncForImportData->IsMatched && $DataClassSyncForImportData->MatchOrder) {
					?>
					Matched
					<?php
				} else {
					?>
					Not Matched (you can ignore)
					<?php
				}
			  ?>
              </td>
              <td><a href="dataclasses_sync_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ClassName=<?php print urlencode($DataClassSyncForImportData->dbtable->name); ?>&<?php print makeRandStr(8); ?>">Syncronize for Each</a></td>
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
