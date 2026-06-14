<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

function adjust_list_order_of_select_target_fields_and_show_message($ProjectPID, $DAPID, $DAFuncPID)
{
	$update_something = adjust_list_order_of_select_target_fields($ProjectPID, $DAPID, $DAFuncPID);
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

function adjust_list_order_of_select_target_fields($ProjectPID, $DAPID, $DAFuncPID)
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
			
			$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
			$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID);
			if ($dafuncselecttargetfieldlist) {
				
				$target_table_list = array();
				for($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
					$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
					
					AddToUnduplicatedList($target_table_list, $dafuncselecttargetfield->targetTableName);
				}
				sort($target_table_list);
				
				// Move Outer Join's table to last
				$new_target_table_last_list = array();
				$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
				$dafuncselectwherelist = $DAdafuncselectwhere->GetdafuncselectwhereList($ProjectPID, $DAPID, $DAFuncPID);
				if ($dafuncselectwherelist) {
					for($l = 0 ; $l < count($dafuncselectwherelist); $l++) {
						$dafuncselectwhere = $dafuncselectwherelist[$l];
						
						switch($dafuncselectwhere->JoinType) {
							case "":
							case dafuncselectwhereJoinTypeEnum::$INNER:
								break;
							case dafuncselectwhereJoinTypeEnum::$LEFT:
							case dafuncselectwhereJoinTypeEnum::$RIGHT:
								// Move to last of list if matched
								for($m = 0 ; $m < count($target_table_list) ; $m++) {
									if ($target_table_list[$m] == $dafuncselectwhere->AnotherTableName) {
										array_push($new_target_table_last_list, $dafuncselectwhere->AnotherTableName);
									}
								}
								break;
							default:
								die("Fatal error. Unknown Join Type");
						}
					}
				}
				if (count($new_target_table_last_list) > 0) {
					$new_target_table_list = array();
					for ($j = 0 ; $j < count($target_table_list) ; $j++) {
						$target_table = $target_table_list[$j];
						
						if (!check_if_include_in_array_for_adjust_list_order_of_select_target_fields($target_table, $new_target_table_last_list)) {
							array_push($new_target_table_list, $target_table);
						}
					}
					
					for ($j = 0 ; $j < count($new_target_table_last_list) ; $j++) {
						$target_table_last = $new_target_table_last_list[$j];
						
						if (check_if_include_in_array_for_adjust_list_order_of_select_target_fields($target_table_last, $target_table_list)) {
							array_push($new_target_table_list, $target_table_last);
						}
					}
					$target_table_list = $new_target_table_list;
				}
				
				$already_set_PID_HT = array();
				
				for ($j = 0 ; $j < count($target_table_list) ; $j++) {
					$target_table = $target_table_list[$j];
					
					// print "$j: target_table: $target_table<br>\n";
					
					if ($dataclassfieldsList) {
						for($k = 0 ; $k < count($dataclassfieldsList) ; $k++) {
							$dataclassfield = $dataclassfieldsList[$k];
							
							for($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
								$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
								if (array_key_exists($dafuncselecttargetfield->PID, $already_set_PID_HT)) {
									continue;
								}
								if ($target_table == $dafuncselecttargetfield->targetTableName) {
									if ($dataclassfield->name == $dafuncselecttargetfield->storeClassFieldName) {
										
										// print "List order of Field $dafuncselecttargetfield->storeClassFieldName is set to $thisListOrder<br>\n";
										
										$DAdafuncselecttargetfields->UpdateSelectTargetFieldListOrder($thisListOrder, $dafuncselecttargetfield->PID, $ProjectPID);
										$thisListOrder++;
										
										if (mysqli_affected_rows($mtooldb) > 0) {
											$update_something = true;
										}
										$already_set_PID_HT[$dafuncselecttargetfield->PID] = true;
										
										break;
									}
								}
							}
						}
					}
				}
				// Reset List Order of the rest
				for($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
					$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
					
					if (array_key_exists($dafuncselecttargetfield->PID, $already_set_PID_HT)) {
						continue;
					}
					$DAdafuncselecttargetfields->UpdateSelectTargetFieldListOrder("Default(FieldListOrder)", $dafuncselecttargetfield->PID, $ProjectPID);
					
					// print "List order of Field $dafuncselecttargetfield->storeClassFieldName is set to default<br>\n";
					
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

function check_if_include_in_array_for_adjust_list_order_of_select_target_fields($target_table, $new_target_table_last_list)
{
	return in_array($target_table, $new_target_table_last_list);
}

?>