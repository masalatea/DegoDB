<?php

// dafuncinserttargetfield と dafuncupdatetargetfield の両方がターゲット。

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

function adjust_list_order_of_insert_or_update_target_fields_and_show_message($ProjectPID, $DAPID, $DAFuncPID)
{
	$update_something = adjust_list_order_of_insert_or_update_target_fields($ProjectPID, $DAPID, $DAFuncPID);
	if ($update_something) {
		$additional_message = "";
		$DAdafunc = new dafuncDBAccess();
		$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
		if ($dafunc) {
			 $additional_message = " for function: " . htmlspecialchars($dafunc->name) . " for " . htmlspecialchars(GetDAFuncActionTypeCaption($dafunc->ActionType));
		}
		?>
        <p><font color="red">Notice: Field's List order was updated automatically<?php print $additional_message; ?>.</font></p>
        <?php
		update_da_LastModifiedDT($DAPID, $ProjectPID);
		update_custom_proxy_LastModifiedDT_by_dbfunc($DAFuncPID, $ProjectPID);
	}
}

function adjust_list_order_of_insert_or_update_target_fields($ProjectPID, $DAPID, $DAFuncPID)
{
	global $mtooldb;
	
	$update_something = false;
	$thisListOrder = 1;
	
	$DAdafunc = new dafuncDBAccess();
	$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
	if ($dafunc) {
			
		$dataclassname = "";
		$thisBaseDataClassName = $dafunc->GetBaseDataClassName();
		if ($thisBaseDataClassName != "") {
			$dataclassname = CreateDataClassName($thisBaseDataClassName);
		}
		
		$DAdataclass = new dataclassDBAccess();
		$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $thisBaseDataClassName);
		if ($dataclass) {
			$DAdataclassfields = new dataclassfieldsDBAccess();
			$dataclassfieldsList = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $dataclass->PID);
			
			$DAdafuncinserttargetfields = new dafuncinserttargetfieldsDBAccess();
			$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
			
			$dafunctargetfield_for_insert_or_update_list = NULL;
			switch($dafunc->ActionType) {
				case dafuncActionTypeEnum::$SELECTSINGLE:
				case dafuncActionTypeEnum::$SELECTLIST:
				case dafuncActionTypeEnum::$DELETE:
					die("Something Strange. It is not expected. Action Type: " . $dafunc->ActionType);
				case dafuncActionTypeEnum::$INSERT:
					$dafunctargetfield_for_insert_or_update_list = $DAdafuncinserttargetfields->GetdafuncinserttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID);
					break;
				case dafuncActionTypeEnum::$UPDATE:
					$dafunctargetfield_for_insert_or_update_list = $DAdafuncupdatetargetfields->GetdafuncupdatetargetfieldsList($ProjectPID, $DAPID, $DAFuncPID);
					break;
				default:
					print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
					return "";
			}
			if ($dafunctargetfield_for_insert_or_update_list) {
				
				// $target_table_list = array();
				// for($i = 0 ; $i < count($dafunctargetfield_for_insert_or_update_list); $i++) {
				// 	$dafunctargetfield_for_insert_or_update = $dafunctargetfield_for_insert_or_update_list[$i];
				// 	
				// 	AddToUnduplicatedList($target_table_list, $dafunctargetfield_for_insert_or_update->targetTableName);
				// }
				// sort($target_table_list);
				
				$already_set_PID_HT = array();
				
				// for ($j = 0 ; $j < count($target_table_list) ; $j++) {
				// 	$target_table = $target_table_list[$j];
					
					// print "$j: target_table: $target_table<br>\n";
					
					if ($dataclassfieldsList) {
						for($k = 0 ; $k < count($dataclassfieldsList) ; $k++) {
							$dataclassfield = $dataclassfieldsList[$k];
							
							for($i = 0 ; $i < count($dafunctargetfield_for_insert_or_update_list); $i++) {
								$dafunctargetfield_for_insert_or_update = $dafunctargetfield_for_insert_or_update_list[$i];
								if (array_key_exists($dafunctargetfield_for_insert_or_update->PID, $already_set_PID_HT)) {
									continue;
								}
								// if ($target_table == $dafunctargetfield_for_insert_or_update->targetTableName) {
									if ($dataclassfield->name == $dafunctargetfield_for_insert_or_update->targetTableColumnName) {
										
										// print "List order of Field $dafunctargetfield_for_insert_or_update->targetTableColumnName is set to $thisListOrder<br>\n";
										
										switch($dafunc->ActionType) {
											case dafuncActionTypeEnum::$SELECTSINGLE:
											case dafuncActionTypeEnum::$SELECTLIST:
											case dafuncActionTypeEnum::$DELETE:
												die("Something Strange. It is not expected. Action Type: " . $dafunc->ActionType);
											case dafuncActionTypeEnum::$INSERT:
												$DAdafuncinserttargetfields->UpdateSelectTargetFieldListOrder($thisListOrder, $dafunctargetfield_for_insert_or_update->PID, $ProjectPID);
												break;
											case dafuncActionTypeEnum::$UPDATE:
												$DAdafuncupdatetargetfields->UpdateSelectTargetFieldListOrder($thisListOrder, $dafunctargetfield_for_insert_or_update->PID, $ProjectPID);
												break;
											default:
												print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
												return "";
										}
										
										$thisListOrder++;
										
										if (mysqli_affected_rows($mtooldb) > 0) {
											$update_something = true;
										}
										$already_set_PID_HT[$dafunctargetfield_for_insert_or_update->PID] = true;
										
										break;
									}
								// }
							}
						}
					}
				// }
				
				// Reset List Order of the rest
				for($i = 0 ; $i < count($dafunctargetfield_for_insert_or_update_list); $i++) {
					$dafunctargetfield_for_insert_or_update = $dafunctargetfield_for_insert_or_update_list[$i];
					
					if (array_key_exists($dafunctargetfield_for_insert_or_update->PID, $already_set_PID_HT)) {
						continue;
					}
					switch($dafunc->ActionType) {
						case dafuncActionTypeEnum::$SELECTSINGLE:
						case dafuncActionTypeEnum::$SELECTLIST:
						case dafuncActionTypeEnum::$DELETE:
							die("Something Strange. It is not expected. Action Type: " . $dafunc->ActionType);
						case dafuncActionTypeEnum::$INSERT:
							$DAdafuncinserttargetfields->UpdateSelectTargetFieldListOrder("Default(FieldListOrder)", $dafunctargetfield_for_insert_or_update->PID, $ProjectPID);
							break;
						case dafuncActionTypeEnum::$UPDATE:
							$DAdafuncupdatetargetfields->UpdateSelectTargetFieldListOrder("Default(FieldListOrder)", $dafunctargetfield_for_insert_or_update->PID, $ProjectPID);
							break;
						default:
							print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
							return "";
					}
					
					// print "List order of Field $dafunctargetfield_for_insert_or_update->targetTableColumnName is set to default<br>\n";
					
					if (mysqli_affected_rows($mtooldb) > 0) {
						$update_something = true;
					}
				}
			}
		} else {
			print "Data Class is not found<br>\n";
		}
		return $update_something;
	}
}

?>