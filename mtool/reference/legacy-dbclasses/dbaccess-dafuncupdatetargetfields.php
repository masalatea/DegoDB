<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncupdatetargetfieldsDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncupdatetargetfieldsList($param_dafuncupdatetargetfields_ProjectPID_where, $param_dafuncupdatetargetfields_daPID_where, $param_dafuncupdatetargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncupdatetargetfieldsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncupdatetargetfieldsList ==
		
		$last_sql_command_for_mtooldb = "select dafuncupdatetargetfields.ProjectPID, dafuncupdatetargetfields.daPID, dafuncupdatetargetfields.dafuncPID, dafuncupdatetargetfields.PID, dafuncupdatetargetfields.targetTableColumnName, dafuncupdatetargetfields.ParameterType, dafuncupdatetargetfields.ParameterDataType, dafuncupdatetargetfields.FixedParameter from dafuncupdatetargetfields where dafuncupdatetargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_ProjectPID_where) . "' and dafuncupdatetargetfields.daPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_daPID_where) . "' and dafuncupdatetargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_dafuncPID_where) . "' order by dafuncupdatetargetfields.FieldListOrder,dafuncupdatetargetfields.ProjectPID,dafuncupdatetargetfields.daPID,dafuncupdatetargetfields.dafuncPID,dafuncupdatetargetfields.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncupdatetargetfieldsData();
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
	public function Getdafuncupdatetargetfields($param_dafuncupdatetargetfields_PID_where, $param_dafuncupdatetargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafuncupdatetargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafuncupdatetargetfields ==
		
		$last_sql_command_for_mtooldb = "select dafuncupdatetargetfields.ProjectPID, dafuncupdatetargetfields.daPID, dafuncupdatetargetfields.dafuncPID, dafuncupdatetargetfields.PID, dafuncupdatetargetfields.targetTableColumnName, dafuncupdatetargetfields.ParameterType, dafuncupdatetargetfields.ParameterDataType, dafuncupdatetargetfields.FixedParameter from dafuncupdatetargetfields where dafuncupdatetargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_PID_where) . "' and dafuncupdatetargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncupdatetargetfieldsData();
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
	public function Insertdafuncupdatetargetfields($dafuncupdatetargetfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafuncupdatetargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafuncupdatetargetfields ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncupdatetargetfields (ProjectPID, daPID, dafuncPID, targetTableColumnName, ParameterType, ParameterDataType, FixedParameter) values('" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->targetTableColumnName) . "', '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->ParameterType) . "', '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->ParameterDataType) . "', '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->FixedParameter) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafuncupdatetargetfields($dafuncupdatetargetfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafuncupdatetargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafuncupdatetargetfields ==
		
		$last_sql_command_for_mtooldb = "update dafuncupdatetargetfields SET targetTableColumnName = '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->targetTableColumnName) . "', ParameterType = '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->ParameterType) . "', ParameterDataType = '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->ParameterDataType) . "', FixedParameter = '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->FixedParameter) . "' where dafuncupdatetargetfields.PID = '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->PID) . "' and dafuncupdatetargetfields.ProjectPID = '" . $mtooldb->real_escape_string($dafuncupdatetargetfieldsObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSelectTargetFieldListOrder($param_dafuncupdatetargetfields_FieldListOrder_update, $param_dafuncupdatetargetfields_PID_where, $param_dafuncupdatetargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSelectTargetFieldListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSelectTargetFieldListOrder ==
		
		$last_sql_command_for_mtooldb = "update dafuncupdatetargetfields SET FieldListOrder = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_FieldListOrder_update) . "' where dafuncupdatetargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_PID_where) . "' and dafuncupdatetargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafuncupdatetargetfields($param_dafuncupdatetargetfields_PID_where, $param_dafuncupdatetargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafuncupdatetargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafuncupdatetargetfields ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncupdatetargetfields where dafuncupdatetargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_PID_where) . "' and dafuncupdatetargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAPIDforMovingFunction($param_dafuncupdatetargetfields_daPID_update, $param_dafuncupdatetargetfields_ProjectPID_where, $param_dafuncupdatetargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		
		$last_sql_command_for_mtooldb = "update dafuncupdatetargetfields SET daPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_daPID_update) . "' where dafuncupdatetargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_ProjectPID_where) . "' and dafuncupdatetargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncupdatetargetfields_dafuncPID_where) . "'";
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