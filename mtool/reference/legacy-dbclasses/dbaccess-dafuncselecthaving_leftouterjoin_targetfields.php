<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncselecthaving_leftouterjoin_targetfieldsDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncselecthavingList($param_dafuncselecthaving_ProjectPID_where, $param_dafuncselecthaving_daPID_where, $param_dafuncselecthaving_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncselecthavingList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncselecthavingList ==
		
		$last_sql_command_for_mtooldb = "select dafuncselecthaving.ProjectPID, dafuncselecthaving.daPID, dafuncselecthaving.dafuncPID, dafuncselecthaving.PID, dafuncselecthaving.LeftTargetPrefix, dafuncselecthaving.LeftTargetFieldPID, dafuncselecthaving.LeftTargetSuffix, dafuncselecthaving.RelationalOperator, dafuncselecthaving.RightTargetPrefix, dafuncselecthaving.RightParameterType, dafuncselecthaving.RightParameterDataType, dafuncselecthaving.RightFixedParameter, dafuncselecthaving.RightTargetFieldPID, dafuncselecthaving.RightTargetSuffix, dafuncselecthaving.HavingListOrder, LeftTargetField.targetTableName, LeftTargetField.targetTableAliasName, LeftTargetField.targetTableColumnName, LeftTargetField.targetTableColumnPrefix, LeftTargetField.targetTableColumnSuffix, LeftTargetField.storeClassFieldName, LeftTargetField.GroupByTarget, RightTargetField.targetTableName, RightTargetField.targetTableAliasName, RightTargetField.targetTableColumnName, RightTargetField.targetTableColumnPrefix, RightTargetField.targetTableColumnSuffix, RightTargetField.storeClassFieldName, RightTargetField.GroupByTarget from dafuncselecthaving LEFT OUTER JOIN dafuncselecttargetfields as LeftTargetField ON dafuncselecthaving.ProjectPID = LeftTargetField.ProjectPID and dafuncselecthaving.LeftTargetFieldPID = LeftTargetField.PID LEFT OUTER JOIN dafuncselecttargetfields as RightTargetField ON dafuncselecthaving.ProjectPID = RightTargetField.ProjectPID and dafuncselecthaving.LeftTargetFieldPID = RightTargetField.PID where dafuncselecthaving.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_ProjectPID_where) . "' and dafuncselecthaving.daPID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_daPID_where) . "' and dafuncselecthaving.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_dafuncPID_where) . "' order by dafuncselecthaving.HavingListOrder,dafuncselecthaving.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselecthaving_leftouterjoin_targetfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->LeftTargetPrefix = $thisline[4];
			$thisresult->LeftTargetFieldPID = $thisline[5];
			$thisresult->LeftTargetSuffix = $thisline[6];
			$thisresult->RelationalOperator = $thisline[7];
			$thisresult->RightTargetPrefix = $thisline[8];
			$thisresult->RightParameterType = $thisline[9];
			$thisresult->RightParameterDataType = $thisline[10];
			$thisresult->RightFixedParameter = $thisline[11];
			$thisresult->RightTargetFieldPID = $thisline[12];
			$thisresult->RightTargetSuffix = $thisline[13];
			$thisresult->HavingListOrder = $thisline[14];
			$thisresult->LeftTargetFieldtargetTableName = $thisline[15];
			$thisresult->LeftTargetFieldtargetTableAliasName = $thisline[16];
			$thisresult->LeftTargetFieldtargetTableColumnName = $thisline[17];
			$thisresult->LeftTargetFieldtargetTableColumnPrefix = $thisline[18];
			$thisresult->LeftTargetFieldtargetTableColumnSuffix = $thisline[19];
			$thisresult->LeftTargetFieldstoreClassFieldName = $thisline[20];
			$thisresult->LeftTargetFieldGroupByTarget = $thisline[21];
			$thisresult->RightTargetFieldtargetTableName = $thisline[22];
			$thisresult->RightTargetFieldtargetTableAliasName = $thisline[23];
			$thisresult->RightTargetFieldtargetTableColumnName = $thisline[24];
			$thisresult->RightTargetFieldtargetTableColumnPrefix = $thisline[25];
			$thisresult->RightTargetFieldtargetTableColumnSuffix = $thisline[26];
			$thisresult->RightTargetFieldstoreClassFieldName = $thisline[27];
			$thisresult->RightTargetFieldGroupByTarget = $thisline[28];
			array_push($result, $thisresult);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>