<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncselecttargetfieldsDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncselecttargetfieldsList($param_dafuncselecttargetfields_ProjectPID_where, $param_dafuncselecttargetfields_daPID_where, $param_dafuncselecttargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncselecttargetfieldsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncselecttargetfieldsList ==
		
		$last_sql_command_for_mtooldb = "select dafuncselecttargetfields.ProjectPID, dafuncselecttargetfields.daPID, dafuncselecttargetfields.dafuncPID, dafuncselecttargetfields.PID, dafuncselecttargetfields.targetTableName, dafuncselecttargetfields.targetTableAliasName, dafuncselecttargetfields.targetTableColumnName, dafuncselecttargetfields.targetTableColumnPrefix, dafuncselecttargetfields.targetTableColumnSuffix, dafuncselecttargetfields.storeClassFieldName, dafuncselecttargetfields.GroupByTarget from dafuncselecttargetfields where dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_ProjectPID_where) . "' and dafuncselecttargetfields.daPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_daPID_where) . "' and dafuncselecttargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_dafuncPID_where) . "' order by dafuncselecttargetfields.FieldListOrder,dafuncselecttargetfields.targetTableName,dafuncselecttargetfields.targetTableAliasName,dafuncselecttargetfields.targetTableColumnName,dafuncselecttargetfields.storeClassFieldName,dafuncselecttargetfields.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselecttargetfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableName = $thisline[4];
			$thisresult->targetTableAliasName = $thisline[5];
			$thisresult->targetTableColumnName = $thisline[6];
			$thisresult->targetTableColumnPrefix = $thisline[7];
			$thisresult->targetTableColumnSuffix = $thisline[8];
			$thisresult->storeClassFieldName = $thisline[9];
			$thisresult->GroupByTarget = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdafuncselecttargetfields($param_dafuncselecttargetfields_PID_where, $param_dafuncselecttargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafuncselecttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafuncselecttargetfields ==
		
		$last_sql_command_for_mtooldb = "select dafuncselecttargetfields.ProjectPID, dafuncselecttargetfields.daPID, dafuncselecttargetfields.dafuncPID, dafuncselecttargetfields.PID, dafuncselecttargetfields.targetTableName, dafuncselecttargetfields.targetTableAliasName, dafuncselecttargetfields.targetTableColumnName, dafuncselecttargetfields.targetTableColumnPrefix, dafuncselecttargetfields.targetTableColumnSuffix, dafuncselecttargetfields.storeClassFieldName, dafuncselecttargetfields.GroupByTarget from dafuncselecttargetfields where dafuncselecttargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_PID_where) . "' and dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselecttargetfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableName = $thisline[4];
			$thisresult->targetTableAliasName = $thisline[5];
			$thisresult->targetTableColumnName = $thisline[6];
			$thisresult->targetTableColumnPrefix = $thisline[7];
			$thisresult->targetTableColumnSuffix = $thisline[8];
			$thisresult->storeClassFieldName = $thisline[9];
			$thisresult->GroupByTarget = $thisline[10];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdafuncselecttargetfields($dafuncselecttargetfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafuncselecttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafuncselecttargetfields ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncselecttargetfields (ProjectPID, daPID, dafuncPID, targetTableName, targetTableAliasName, targetTableColumnName, targetTableColumnPrefix, targetTableColumnSuffix, storeClassFieldName, GroupByTarget) values('" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableName) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableAliasName) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableColumnName) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableColumnPrefix) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableColumnSuffix) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->storeClassFieldName) . "', '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->GroupByTarget) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafuncselecttargetfields($dafuncselecttargetfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafuncselecttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafuncselecttargetfields ==
		
		$last_sql_command_for_mtooldb = "update dafuncselecttargetfields SET targetTableName = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableName) . "', targetTableAliasName = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableAliasName) . "', targetTableColumnName = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableColumnName) . "', targetTableColumnPrefix = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableColumnPrefix) . "', targetTableColumnSuffix = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->targetTableColumnSuffix) . "', storeClassFieldName = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->storeClassFieldName) . "', GroupByTarget = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->GroupByTarget) . "' where dafuncselecttargetfields.PID = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->PID) . "' and dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($dafuncselecttargetfieldsObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafuncselecttargetfields($param_dafuncselecttargetfields_PID_where, $param_dafuncselecttargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafuncselecttargetfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafuncselecttargetfields ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncselecttargetfields where dafuncselecttargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_PID_where) . "' and dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAPIDforMovingFunction($param_dafuncselecttargetfields_daPID_update, $param_dafuncselecttargetfields_ProjectPID_where, $param_dafuncselecttargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		
		$last_sql_command_for_mtooldb = "update dafuncselecttargetfields SET daPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_daPID_update) . "' where dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_ProjectPID_where) . "' and dafuncselecttargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_dafuncPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSelectTargetFieldListOrder($param_dafuncselecttargetfields_FieldListOrder_update, $param_dafuncselecttargetfields_PID_where, $param_dafuncselecttargetfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSelectTargetFieldListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSelectTargetFieldListOrder ==
		
		$last_sql_command_for_mtooldb = "update dafuncselecttargetfields SET FieldListOrder = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_FieldListOrder_update) . "' where dafuncselecttargetfields.PID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_PID_where) . "' and dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function GetGroupByTargetCount($param_dafuncselecttargetfields_ProjectPID_where, $param_dafuncselecttargetfields_daPID_where, $param_dafuncselecttargetfields_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetGroupByTargetCount ==
		// == END OF EDITABLE AREA FOR FUNCTION GetGroupByTargetCount ==
		
		$last_sql_command_for_mtooldb = "select count(dafuncselecttargetfields.GroupByTarget) from dafuncselecttargetfields where dafuncselecttargetfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_ProjectPID_where) . "' and dafuncselecttargetfields.daPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_daPID_where) . "' and dafuncselecttargetfields.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncselecttargetfields_dafuncPID_where) . "' and dafuncselecttargetfields.GroupByTarget = '" . $mtooldb->real_escape_string("1") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselecttargetfieldsData();
			$thisresult->GroupByTarget = $thisline[0];
			return $thisresult;
		}
		return NULL;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>