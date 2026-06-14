<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncselecthavingDBAccess
{
	public function __construct() {
	}
	
	public function Getdafuncselecthaving($param_dafuncselecthaving_PID_where, $param_dafuncselecthaving_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafuncselecthaving ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafuncselecthaving ==
		
		$last_sql_command_for_mtooldb = "select dafuncselecthaving.ProjectPID, dafuncselecthaving.daPID, dafuncselecthaving.dafuncPID, dafuncselecthaving.PID, dafuncselecthaving.LeftTargetPrefix, dafuncselecthaving.LeftTargetFieldPID, dafuncselecthaving.LeftTargetSuffix, dafuncselecthaving.RelationalOperator, dafuncselecthaving.RightTargetPrefix, dafuncselecthaving.RightParameterType, dafuncselecthaving.RightParameterDataType, dafuncselecthaving.RightFixedParameter, dafuncselecthaving.RightTargetFieldPID, dafuncselecthaving.RightTargetSuffix, dafuncselecthaving.HavingListOrder from dafuncselecthaving where dafuncselecthaving.PID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_PID_where) . "' and dafuncselecthaving.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselecthavingData();
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
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdafuncselecthaving($dafuncselecthavingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafuncselecthaving ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafuncselecthaving ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncselecthaving (ProjectPID, daPID, dafuncPID, LeftTargetPrefix, LeftTargetFieldPID, LeftTargetSuffix, RelationalOperator, RightTargetPrefix, RightParameterType, RightParameterDataType, RightFixedParameter, RightTargetFieldPID, RightTargetSuffix) values('" . $mtooldb->real_escape_string($dafuncselecthavingObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->LeftTargetPrefix) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->LeftTargetFieldPID) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->LeftTargetSuffix) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RelationalOperator) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightTargetPrefix) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightParameterType) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightParameterDataType) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightFixedParameter) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightTargetFieldPID) . "', '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightTargetSuffix) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafuncselecthaving($dafuncselecthavingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafuncselecthaving ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafuncselecthaving ==
		
		$last_sql_command_for_mtooldb = "update dafuncselecthaving SET LeftTargetPrefix = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->LeftTargetPrefix) . "', LeftTargetFieldPID = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->LeftTargetFieldPID) . "', LeftTargetSuffix = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->LeftTargetSuffix) . "', RelationalOperator = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RelationalOperator) . "', RightTargetPrefix = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightTargetPrefix) . "', RightParameterType = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightParameterType) . "', RightParameterDataType = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightParameterDataType) . "', RightFixedParameter = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightFixedParameter) . "', RightTargetFieldPID = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightTargetFieldPID) . "', RightTargetSuffix = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->RightTargetSuffix) . "' where dafuncselecthaving.PID = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->PID) . "' and dafuncselecthaving.ProjectPID = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafuncselecthaving($dafuncselecthavingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafuncselecthaving ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafuncselecthaving ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncselecthaving where dafuncselecthaving.PID = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->PID) . "' and dafuncselecthaving.ProjectPID = '" . $mtooldb->real_escape_string($dafuncselecthavingObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedafuncselecthavingOrder($param_dafuncselecthaving_HavingListOrder_update, $param_dafuncselecthaving_PID_where, $param_dafuncselecthaving_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedafuncselecthavingOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedafuncselecthavingOrder ==
		
		$last_sql_command_for_mtooldb = "update dafuncselecthaving SET HavingListOrder = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_HavingListOrder_update) . "' where dafuncselecthaving.PID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_PID_where) . "' and dafuncselecthaving.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecthaving_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>