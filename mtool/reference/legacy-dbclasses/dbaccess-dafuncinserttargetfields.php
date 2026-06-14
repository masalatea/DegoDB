<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncinserttargetfieldsDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncinserttargetfieldsList($param_dafuncinserttargetfields_ProjectPID_where, $param_dafuncinserttargetfields_daPID_where, $param_dafuncinserttargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncinserttargetfieldsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncinserttargetfieldsList ==
		
		$last_sql_command_for_mtooldb = "select dafuncinserttargetfields.ProjectPID, dafuncinserttargetfields.daPID, dafuncinserttargetfields.dafuncPID, dafuncinserttargetfields.PID, dafuncinserttargetfields.targetTableColumnName, dafuncinserttargetfields.ParameterType, dafuncinserttargetfields.ParameterDataType, dafuncinserttargetfields.FixedParameter from dafuncinserttargetfields where dafuncinserttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_ProjectPID_where) . "' and dafuncinserttargetfields.daPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_daPID_where) . "' and dafuncinserttargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_dafuncPID_where) . "' order by dafuncinserttargetfields.FieldListOrder,dafuncinserttargetfields.ProjectPID,dafuncinserttargetfields.daPID,dafuncinserttargetfields.dafuncPID,dafuncinserttargetfields.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncinserttargetfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableColumnName = $thisline[4];
			$thisresult->ParameterType = $thisline[5];
			$thisresult->ParameterDataType = $thisline[6];
			$thisresult->FixedParameter = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdafuncinserttargetfields($param_dafuncinserttargetfields_PID_where, $param_dafuncinserttargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafuncinserttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafuncinserttargetfields ==
		
		$last_sql_command_for_mtooldb = "select dafuncinserttargetfields.ProjectPID, dafuncinserttargetfields.daPID, dafuncinserttargetfields.dafuncPID, dafuncinserttargetfields.PID, dafuncinserttargetfields.targetTableColumnName, dafuncinserttargetfields.ParameterType, dafuncinserttargetfields.ParameterDataType, dafuncinserttargetfields.FixedParameter from dafuncinserttargetfields where dafuncinserttargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_PID_where) . "' and dafuncinserttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncinserttargetfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableColumnName = $thisline[4];
			$thisresult->ParameterType = $thisline[5];
			$thisresult->ParameterDataType = $thisline[6];
			$thisresult->FixedParameter = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdafuncinserttargetfields($dafuncinserttargetfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafuncinserttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafuncinserttargetfields ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncinserttargetfields (ProjectPID, daPID, dafuncPID, targetTableColumnName, ParameterType, ParameterDataType, FixedParameter) values('" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->targetTableColumnName) . "', '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->ParameterType) . "', '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->ParameterDataType) . "', '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->FixedParameter) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafuncinserttargetfields($dafuncinserttargetfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafuncinserttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafuncinserttargetfields ==
		
		$last_sql_command_for_mtooldb = "update dafuncinserttargetfields SET targetTableColumnName = '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->targetTableColumnName) . "', ParameterType = '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->ParameterType) . "', ParameterDataType = '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->ParameterDataType) . "', FixedParameter = '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->FixedParameter) . "' where dafuncinserttargetfields.PID = '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->PID) . "' and dafuncinserttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($dafuncinserttargetfieldsObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSelectTargetFieldListOrder($param_dafuncinserttargetfields_FieldListOrder_update, $param_dafuncinserttargetfields_PID_where, $param_dafuncinserttargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSelectTargetFieldListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSelectTargetFieldListOrder ==
		
		$last_sql_command_for_mtooldb = "update dafuncinserttargetfields SET FieldListOrder = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_FieldListOrder_update) . "' where dafuncinserttargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_PID_where) . "' and dafuncinserttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafuncinserttargetfields($param_dafuncinserttargetfields_PID_where, $param_dafuncinserttargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafuncinserttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafuncinserttargetfields ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncinserttargetfields where dafuncinserttargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_PID_where) . "' and dafuncinserttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAPIDforMovingFunction($param_dafuncinserttargetfields_daPID_update, $param_dafuncinserttargetfields_ProjectPID_where, $param_dafuncinserttargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		
		$last_sql_command_for_mtooldb = "update dafuncinserttargetfields SET daPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_daPID_update) . "' where dafuncinserttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_ProjectPID_where) . "' and dafuncinserttargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncinserttargetfields_dafuncPID_where) . "'";
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