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
<title><?php print getres("TITLE_DA_FUNC_SELECT_TARGET_FIELDS_SYNC"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

include_once("da_func_select_target_fields_update_list_order_lib.php");
include_once("da_func_select_alias_lib.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));

$DoNext = trim(GetParam("DoNext"));
$UPDATE = trim(GetParam("UPDATE"));

// Array Parameter
$DBTableList = GetParam("DBTableList");
$SelectTargetList = GetParam("SelectTargetList");

$VALUE_SEPARATOR = "-----TABLE-AND-COLUMN-----";

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
$dataclassTheMostParent = NULL;
$DAdataclassfields = new dataclassfieldsDBAccess();
$dataclassfieldlist = NULL;

$DAdbtable = new dbtableDBAccess();
$dbtable = NULL;

$thisBaseDataClassName = "";
$dataclass = NULL;
$dbtable = NULL;

$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
$dafuncselecttargetfieldlist = NULL;

$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
$dafuncselectwherelist = NULL;

$DAdbtable = new dbtableDBAccess();
$alltablelistinproject = NULL;

$DAdbtablecolumns = new dbtablecolumnsDBAccess();

$DAdataclass = new dataclassDBAccess();
$dataclasslist = NULL;

if ($NoError) {
	printPathOnTopForDBAccessClass("Synchronize Select Target Field(s)", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
	if ($dafunc == NULL) {
		?>
		<H3><font color="red">DB Access Function is not found. Please ask administrator if this continues.</font></H3>
		<?php
		$NoError = false;
	}
}

$DAProject = new ProjectDBAccess();
$project = $DAProject->GetProject($ProjectPID);
if (!$project) {
	die("Something strange. Project is not found\n");
}

if ($NoError) {
	
	$thisBaseDataClassName = $dafunc->GetBaseDataClassName();
	if ($thisBaseDataClassName != "") {
		$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $thisBaseDataClassName);
		$dbtable = $DAdbtable->GetdbtableByName($ProjectPID, $thisBaseDataClassName);
		
		$dataclassTheMostParent = GetDataClassTheMostParent($ProjectPID, $dataclass);
		
		$dbtableTheMostParent = $dbtable;
		if ($dataclassTheMostParent != NULL) {
			$dbtableTheMostParent = $DAdbtable->GetdbtableByName($ProjectPID, $dataclassTheMostParent->name);
		}
	}
	$dataclassfieldlist = NULL;
	if ($dataclass != NULL) {
		$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $dataclass->PID);
	}
	
	$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID); 
	$dafuncselectwherelist = $DAdafuncselectwhere->GetdafuncselectwhereList($ProjectPID, $DAPID, $DAFuncPID); 
	$alltablelistinproject = $DAdbtable->GetdbtableList($ProjectPID);
	
	InitializeTargetTableAliasNameHTFromSumit($alltablelistinproject);
	// $TargetTableAliasNameInfoListが初期化される時は以下も初期化が必要
	$alltablelistinprojectByConsideringAliasList = InitializeAlltablelistinprojectByConsideringAlias($alltablelistinproject);
	
	$dataclasslist = $DAdataclass->GetdataclassList($ProjectPID); 
	
	foreach($TargetTableAliasNameInfoList as $thisTableName => $TargetTableAliasNameInfo) {
		for($i = 0 ; $i < count($TargetTableAliasNameInfo->AliasNameList) ; $i++) {
			$thisAliasName = $TargetTableAliasNameInfo->AliasNameList[$i];
			
			for($j = 0 ; $j < count($dataclasslist) ; $j++) {
				$thisdataclass = $dataclasslist[$j];
				
				if (trim($thisdataclass->name) == trim($thisAliasName)) {
					?>
                    <h3><font color="red">ERROR! Alias name "<?php print $thisAliasName; ?>" is as same as one of Data Class Name. Please define another name.</font></h3>
                    <?php
					$NoError = false;
				} else if (strtoupper(trim($thisdataclass->name)) == strtoupper(trim($thisAliasName))) {
					?>
                    <h3><font color="red">ERROR! Alias name "<?php print $thisAliasName; ?>" is similar to one of Data Class Name. Please define another name.</font></h3>
                    <?php
					$NoError = false;
				}
			}
		}
	}
}

if ($NoError) {
	if ($UPDATE != "") {
		
		$UpdateTargetdafuncselecttargetfieldsDataList = array();
		for ($i = 0 ; $i < count($SelectTargetList); $i++) {
			$thisSelectTargetCombination = $SelectTargetList[$i];
			$tmp = preg_split("/$VALUE_SEPARATOR/", $thisSelectTargetCombination);
			$thisTableName = $tmp[0];
			$thisAliasTableName = $tmp[1];
			$thisColumnName = $tmp[2];
			$thisStoreFieldName = $tmp[3];
			
			if (trim($thisTableName) != "" && trim($thisColumnName) != "" && trim($thisStoreFieldName) != "") {
				
				$UpdateTargetdafuncselecttargetfieldsData = new dafuncselecttargetfieldsData();
				$UpdateTargetdafuncselecttargetfieldsData->ProjectPID = $ProjectPID;
				$UpdateTargetdafuncselecttargetfieldsData->daPID = $DAPID;
				$UpdateTargetdafuncselecttargetfieldsData->dafuncPID = $DAFuncPID;
				$UpdateTargetdafuncselecttargetfieldsData->PID = "";
				$UpdateTargetdafuncselecttargetfieldsData->targetTableName = $thisTableName;
				$UpdateTargetdafuncselecttargetfieldsData->targetTableAliasName = $thisAliasTableName;
				$UpdateTargetdafuncselecttargetfieldsData->targetTableColumnName = $thisColumnName;
				$UpdateTargetdafuncselecttargetfieldsData->storeClassFieldName = $thisStoreFieldName;
				
				array_push($UpdateTargetdafuncselecttargetfieldsDataList, $UpdateTargetdafuncselecttargetfieldsData);
				
				if ($dafuncselecttargetfieldlist != NULL) {
					for($j = 0 ; $j < count($dafuncselecttargetfieldlist); $j++) {
						$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$j];
						
						if ($thisTableName == $dafuncselecttargetfield->targetTableName &&
						    $thisAliasTableName == $dafuncselecttargetfield->targetTableAliasName &&
							$thisColumnName == $dafuncselecttargetfield->targetTableColumnName &&
							$thisStoreFieldName == $dafuncselecttargetfield->storeClassFieldName)
						{
							$UpdateTargetdafuncselecttargetfieldsData->PID = $dafuncselecttargetfield->PID;
							break;
						}
					}
				}
				if ($UpdateTargetdafuncselecttargetfieldsData->PID == NULL) {
					// Need to Insert
					if($DAdafuncselecttargetfields->Insertdafuncselecttargetfields($UpdateTargetdafuncselecttargetfieldsData) === FALSE) {
						// Failed
						?>
						<h3><font color="red">Error! Failed to insert. Something strange. Please ask adminmistrator if this continues.</font></h3>
						<?php
					} else {
						// Success
						$UpdateTargetdafuncselecttargetfieldsData->PID = $mtooldb->insert_id;
						?>
						<h3><font color="red">Added Select Target. Table: <?php print $UpdateTargetdafuncselecttargetfieldsData->targetTableName; ?> Column:<?php print $UpdateTargetdafuncselecttargetfieldsData->targetTableColumnName; ?>  Store Class Field Name(of Data Class):<?php print $UpdateTargetdafuncselecttargetfieldsData->storeClassFieldName; ?></font></h3>
						<?php
						update_da_LastModifiedDT($DAPID, $ProjectPID);
						update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
					}
					
				} else {
					// Already Exists. Nothing to update (for now)
				}
			}
		}
		
		// Delete Unnecessary Field
		for ($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
			$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
			
			$shouldBeExist = false;
			
			if (trim($dafuncselecttargetfield->targetTableColumnName) == "*") {
				// Pass
				$shouldBeExist = true;
			} else {
				// Check if exist in Database
				for ($j = 0 ; $j < count($UpdateTargetdafuncselecttargetfieldsDataList) ; $j++) {
					$UpdateTargetdafuncselecttargetfieldsData = $UpdateTargetdafuncselecttargetfieldsDataList[$j];
					
					if ($dafuncselecttargetfield->PID == $UpdateTargetdafuncselecttargetfieldsData->PID) {
						$shouldBeExist = true;
						break;
					}
				}
			}
			$needToDelete = !$shouldBeExist;
			
			if ($needToDelete) {
				if($DAdafuncselecttargetfields->Deletedafuncselecttargetfields($dafuncselecttargetfield->PID, $dafuncselecttargetfield->ProjectPID) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to delete. Please ask adminmistrator if this continues.</font></h3>
					<?php
					
				} else {
					// Success
					?>
					<h4><font color="red">Deleted Select Target. Table: <?php print $dafuncselecttargetfield->targetTableName; ?> Column:<?php print $dafuncselecttargetfield->targetTableColumnName; ?>  Store Class Field Name(of Data Class):<?php print $dafuncselecttargetfield->storeClassFieldName; ?></font></h4>
					<?php
					update_da_LastModifiedDT($DAPID, $dafuncselecttargetfield->ProjectPID);
					update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $dafuncselecttargetfield->ProjectPID);
				}
			}
		}
		
		// Initialize Again
		$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID); 
	}
	if ($DoNext != "") {
		// Check before going to Next
		if (($DBTableList == NULL) ||
			 !is_array($DBTableList) ||
			 count($DBTableList) == 0
			) {
			?>
			<H3><font color="red">WARNING: Please select one or more DB Table.</font></H3>
			<?php
			$DoNext = "";
		}
	}
	if ($DoNext == "" && $UPDATE == "") {		// Initial or Reset
		$RelatedDBTableNameList = GetRelatedDBTableList($alltablelistinproject, $dbtable, $dafuncselecttargetfieldlist, $dafuncselectwherelist, $dafunc, $dataclasslist);
		
		$DBTableList = array();
		for($i = 0 ; $i < count($RelatedDBTableNameList) ; $i++) {
			$RelatedDBTableName = $RelatedDBTableNameList[$i];
			array_push($DBTableList, $RelatedDBTableName->TableName);
		}
		
		InitializeTargetTableAliasNameHTFromDB($dafuncselecttargetfieldlist);
		// $TargetTableAliasNameInfoListが初期化される時は以下も初期化が必要
		$alltablelistinprojectByConsideringAliasList = InitializeAlltablelistinprojectByConsideringAlias($alltablelistinproject);
	}
	
	if (count($alltablelistinproject) > 0) {
		
		?>
        <form action="da_func_select_target_fields_sync.php" method="post">
        
        <?php
		if ($DoNext == "" && $UPDATE == "") {
			?>
			<h3>Select DB Table</h3>
            
            <div class="LoadingArea">Loading...</div>
            <div class="LoadCompleteArea"<?php print ' style="display:none"'; ?>>
            
            <p><input name="DoNext" type="submit" value="Next"></p>
            
            <table class="table">
                <thead>
                <tr bgcolor="#ECECEC">
                  <th>Table Name</th>
                  <th>Alias Name [Optional](*1)</th>
                </tr>
              </thead>
                <tbody>
			<?php
			
			for($i = 0 ; $i < count($alltablelistinproject); $i++) {
				$thisdbtable = $alltablelistinproject[$i];
				
				$isSelected = false;
				$AliasTableNameListString = "";
				if (in_array($thisdbtable->name, $DBTableList)) {
					$isSelected = true;
					
					$AliasTableNameListString = GetAliasTableNameListString($RelatedDBTableNameList, $thisdbtable->name);
				}
				?>
                <tr>
                    <td>
                    <span class="checkbox"><label><input name="DBTableList[]" class="DBTableListCheck" type="checkbox" value="<?php print $thisdbtable->name; ?>"<?php if($isSelected) { print " checked"; } ?> AnotherNameInputName="AnotherNameArea<?php print $thisdbtable->PID; ?>"><?php print $thisdbtable->name; ?></label></span>
                    </td>
                    <td>
                    <span id="AnotherNameArea<?php print $thisdbtable->PID; ?>"<?php if(!$isSelected) { print " style=\"display:none\""; } ?>><input name="AnotherName<?php print $thisdbtable->PID; ?>" type="text" value="<?php print $AliasTableNameListString; ?>" size="40"> </span>
                    </td>
                </tr>
				<?php
			}
			?>
                </tbody>
            </table>
	        </div>
<script>

$(function(){
	$('.DBTableListCheck').change(function(){
		var AnotherNameInputName = $(this).attr("AnotherNameInputName");
		if ($(this).is(':checked')) {
			$("#" + AnotherNameInputName).show();
		} else {
			$("#" + AnotherNameInputName).hide();
		}
	});
});

</script>        

            <?php
			include_once("da_func_explanation_for_alias_include.php");
			
		} else {
			
			
			?>
			<h3>Select the Target(s)</h3>
            
            <table class="table">
                <thead>
                <tr bgcolor="#ECECEC">
                  <th>Table Name</th>
                  <th>Alias Name</th>
                  <th>Column Name</th>
                  <th>Store Class Field Name</th>
                  <th>Target of Select? <?php mtool_output_checkbox_for_select_all(); ?></th>
                </tr>
              </thead>
                <tbody>
            <?php
			for($i = 0 ; $i < count($alltablelistinprojectByConsideringAliasList); $i++) {
				$alltablelistinprojectByConsideringAlias = $alltablelistinprojectByConsideringAliasList[$i];
				
				$thisAliasTableName = $alltablelistinprojectByConsideringAlias->AliasName;
				$thisAliasInfo = $alltablelistinprojectByConsideringAlias->AliasInfo;
				$thisdbtable   = $alltablelistinprojectByConsideringAlias->DBTableInfo;
				
				$isSelected = false;
				if (in_array($thisdbtable->name, $DBTableList)) {
					$isSelected = true;
				}
				if ($isSelected) {
					
					$thisTargetTableNameConsideringAliasName = $thisdbtable->name;
					if (trim($thisAliasTableName) != "") {
						$thisTargetTableNameConsideringAliasName = trim($thisAliasTableName);
					}
					
					$dbtablecolumnslist = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $thisdbtable->PID); 
					
					for($j = 0 ; $j < count($dbtablecolumnslist); $j++) {
						$dbtablecolumns = $dbtablecolumnslist[$j];
						
						$isMainTargetHighlightTag = "";
						$targetStoreClassFieldName = "";
						$targetStoreClassFieldNameTag = "";
						if (($dbtableTheMostParent == NULL || $thisdbtable->PID == $dbtableTheMostParent->PID) &&
						    (trim($thisAliasTableName) == "")) {
							$targetStoreClassFieldName = $dbtablecolumns->name;
							$targetStoreClassFieldNameTag = $targetStoreClassFieldName;
							$isMainTargetHighlightTag = " <font color=red>[Main Target]</font>";
						} else {
							$targetStoreClassFieldName = GetTargetStoreClassFieldNameForCombination($thisTargetTableNameConsideringAliasName, $dbtablecolumns->name, false);
							$targetStoreClassFieldNameTag = GetTargetStoreClassFieldNameForCombination($thisTargetTableNameConsideringAliasName, $dbtablecolumns->name, true);
						}
						
						$isChecked = false;
						if ($dafuncselecttargetfieldlist != NULL) {
							for($k = 0 ; $k < count($dafuncselecttargetfieldlist); $k++) {
								$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$k];
								
								if ($thisdbtable->name == $dafuncselecttargetfield->targetTableName &&
								    $thisAliasTableName == $dafuncselecttargetfield->targetTableAliasName &&
								    $dbtablecolumns->name == $dafuncselecttargetfield->targetTableColumnName &&
									$targetStoreClassFieldName == $dafuncselecttargetfield->storeClassFieldName)
								{
									$isChecked = true;
									break;
								}
							}
						}
						?>
                        <tr>
                          <td><?php
                          print htmlspecialchars($thisdbtable->name); 
						  print $isMainTargetHighlightTag;
						  ?></td>
                          <td><?php print htmlspecialchars($thisAliasTableName); ?></td>
                          <td><?php print htmlspecialchars($dbtablecolumns->name); ?></td>
                          <td><?php print $targetStoreClassFieldNameTag; 
						  
						  if ($isChecked) {
							  if ($targetStoreClassFieldName != $dbtablecolumns->name) {
								  
								  $newrefdataclassname = $thisdbtable->name;
								  $newreffieldname = $dbtablecolumns->name;
								  
								  $isExist = false;
								  $dataTypeMatched = false;
								  $correspondingdataclassfield = NULL;
								  $datatypeInProgLang = GetSourceDataTypeFromDatabaseDataTypeForGeneral($project, $dbtablecolumns->datatype);
								  for($k = 0 ; $k < count($dataclassfieldlist) ; $k++) {
									  $thisdataclassfield = $dataclassfieldlist[$k];
									  if ($thisdataclassfield->name == $targetStoreClassFieldName) {
										  $isExist = true;
										  
										  $dataTypeMatched = ($thisdataclassfield->datatype == $datatypeInProgLang);
										  $correspondingdataclassfield = $thisdataclassfield;
										  break;
									  }
								  }
								  
								  $needToAdd = false;
								  $needToUpdateField = false;
								  $AddOrUpdateCaption = "";
								  if ($isExist) {
									  // Exist
									  $warningMessage = "";
									  if (!$dataTypeMatched) {
										  // Data Type is not matched
										  $needToUpdateField = true;
										  $warningMessage .= "This field is exist but data type is not matched.";
									  }
									  if ($correspondingdataclassfield != NULL) {
										  if ($correspondingdataclassfield->RefDataClassName != $newrefdataclassname) {
											  $needToUpdateField = true;
											  $warningMessage .= "Referencing Data Class Name is not matched.";
										  }
										  if ($correspondingdataclassfield->RefDataClassFieldName != $newreffieldname) {
											  $needToUpdateField = true;
											  $warningMessage .= "Referencing Field Name is not matched.";
										  }
									  }
									  
									  if ($needToUpdateField) {
										  $AddOrUpdateCaption = "Update Data";
										  ?>
									      <font color="red">WANING: <?php print $warningMessage; ?> Need to update manually</font>
                                          <?php
									  }
								  } else {
									  // Not Exist
									  $needToAdd = true;
									  $AddOrUpdateCaption = "Add New Field";
									  ?>
									  <font color="red">WARNING: This field must be exist in data class. Need to make manually</font> 
									  <?php
								  }
								  if ($needToAdd || $needToUpdateField) {
									  $dataclassPID = "";
									  if ($dataclass) {
										  $dataclassPID = $dataclass->PID;
									  }
									  $correspondingdataclassfieldPID = "";
									  if ($correspondingdataclassfield) {
										  $correspondingdataclassfield = $dataclass->PID;
									  }
									  ?>
                                      [<a href="dataclass_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclassPID); ?>&DataClassFieldPID=<?php print urlencode($correspondingdataclassfieldPID); ?>&name=<?php print urlencode($targetStoreClassFieldName); ?>&datatype=<?php print urlencode($datatypeInProgLang); ?>&RefDataClassName=<?php print urlencode($newrefdataclassname); ?>&RefDataClassFieldName=<?php print urlencode($newreffieldname); ?>&overrideByNewData=y&<?php print makeRandStr(8); ?>"><?php print $AddOrUpdateCaption; ?></a>]
                                      <?php
								  }
							  }
						  }
						  
						  ?></td>
                          <td><input name="SelectTargetList[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox(); ?> value="<?php print $thisdbtable->name . $VALUE_SEPARATOR . $thisAliasTableName . $VALUE_SEPARATOR . $dbtablecolumns->name . $VALUE_SEPARATOR . $targetStoreClassFieldName; ?>"<?php if ($isChecked) { print " checked"; } ?>></td>
                        </tr>
						<?php
					}
				}
			}
			?>
                </tbody>
            </table>
            <?php
			mtool_output_script_tag_for_multi_checkbox();
			?>
            
	        <input name="UPDATE" type="submit" value="UPDATE">
            <?php
			for($i = 0 ; $i < count($DBTableList) ; $i++) {
				?>
		        <input name="DBTableList[]" type="hidden" value="<?php print $DBTableList[$i]; ?>">
                <?php
			}
			
			for($i = 0 ; $i < count($alltablelistinproject); $i++) {
				$thisdbtable = $alltablelistinproject[$i];
				
				if (array_key_exists($thisdbtable->name, $TargetTableAliasNameInfoList)) {
					$TargetTableAliasNameInfo = $TargetTableAliasNameInfoList[$thisdbtable->name];
					?>
					<input name="AnotherName<?php print $thisdbtable->PID; ?>" type="hidden" value="<?php print $TargetTableAliasNameInfo->GetAliasNameList(); ?>">
					<?php
				}
			}
		}
        ?>
        <input name="ProjectPID" type="hidden" value="<?php print $ProjectPID; ?>">
        <input name="DAPID" type="hidden" value="<?php print $DAPID; ?>">
        <input name="DAFuncPID" type="hidden" value="<?php print $DAFuncPID; ?>">
        </form>
        
        
		<?php
		
		$CanNotBeExistList = array();
		
		$dbtablecolumnslistHT = array();
		for ($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
			$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
			
			$canBeExist = false;
			
			if (trim($dafuncselecttargetfield->targetTableColumnName) == "*") {
				// Pass
				$canBeExist = true;
			} else {
				// Check if exist in Database
				for($j = 0 ; $j < count($alltablelistinprojectByConsideringAliasList); $j++) {
					$alltablelistinprojectByConsideringAlias = $alltablelistinprojectByConsideringAliasList[$j];
					
					$thisAliasTableName = $alltablelistinprojectByConsideringAlias->AliasName;
					$thisAliasInfo = $alltablelistinprojectByConsideringAlias->AliasInfo;
					$thisdbtable   = $alltablelistinprojectByConsideringAlias->DBTableInfo;
					
					if ($thisdbtable->name == $dafuncselecttargetfield->targetTableName &&
						$thisAliasTableName == $dafuncselecttargetfield->targetTableAliasName) {
						
						$dbtablecolumnslist = NULL;
						$key = $thisdbtable->PID . "__with_alias__" . $thisAliasTableName;
						if (array_key_exists($key, $dbtablecolumnslistHT)) {
							$dbtablecolumnslist = $dbtablecolumnslistHT[$key];
						} else {
							$dbtablecolumnslist = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $thisdbtable->PID); 
							$dbtablecolumnslistHT[$key] = $dbtablecolumnslist;
						}
						if ($dbtablecolumnslist != NULL && is_array($dbtablecolumnslist)) {
							for ($k = 0 ; $k < count($dbtablecolumnslist); $k++) {
								$dbtablecolumns = $dbtablecolumnslist[$k];
								
								if ($dbtablecolumns->name == $dafuncselecttargetfield->targetTableColumnName) {
									$canBeExist = true;
									break;
								}
							}
						}
					}
					if ($canBeExist) {
						break;
					}
				}
			}
			if (!$canBeExist) {
				array_push($CanNotBeExistList, $dafuncselecttargetfield);
			}
		}
		
		if (count($CanNotBeExistList)) {
			?>
            <h3><font color="red">WARNING: Following definition will be deleted when Update</font></h3>
            <?php
			for ($i = 0 ; $i < count($CanNotBeExistList); $i++) {
				$CanNotBeExist = $CanNotBeExistList[$i];
				?>
                <p><font color="red">Definition: Target Table: <?php print $CanNotBeExist->targetTableName; ?><?php 
				if ($CanNotBeExist->targetTableAliasName != "") {
					?>
                    Alias Table Name: 
                    <?php
					print $CanNotBeExist->targetTableAliasName;
				}
				?> Target Column: <?php print $CanNotBeExist->targetTableColumnName; ?> Store Field Name: <?php print $CanNotBeExist->storeClassFieldName; ?></font></p>
                <?php
			}
		}
		
	} else {
		?>
    <p>none</p>
		<?php
	}
	adjust_list_order_of_select_target_fields_and_show_message($ProjectPID, $DAPID, $DAFuncPID);
	?>
    <br>
    <br>
    <br>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
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
