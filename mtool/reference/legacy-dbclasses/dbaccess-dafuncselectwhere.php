<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncselectwhereDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncselectwhereList($param_dafuncselectwhere_ProjectPID_where, $param_dafuncselectwhere_daPID_where, $param_dafuncselectwhere_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncselectwhereList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncselectwhereList ==
		
		$last_sql_command_for_mtooldb = "select dafuncselectwhere.ProjectPID, dafuncselectwhere.daPID, dafuncselectwhere.dafuncPID, dafuncselectwhere.PID, dafuncselectwhere.targetTableName, dafuncselectwhere.targetTableAliasName, dafuncselectwhere.targetTableColumnName, dafuncselectwhere.ParameterType, dafuncselectwhere.ParameterDataType, dafuncselectwhere.FixedParameter, dafuncselectwhere.AnotherTableName, dafuncselectwhere.AnotherTableAliasName, dafuncselectwhere.AnotherFieldName, dafuncselectwhere.JoinType, dafuncselectwhere.ORGroup, dafuncselectwhere.RelationalOperator, dafuncselectwhere.WhereOrder from dafuncselectwhere where dafuncselectwhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_ProjectPID_where) . "' and dafuncselectwhere.daPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_daPID_where) . "' and dafuncselectwhere.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_dafuncPID_where) . "' order by dafuncselectwhere.WhereOrder,dafuncselectwhere.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselectwhereData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableName = $thisline[4];
			$thisresult->targetTableAliasName = $thisline[5];
			$thisresult->targetTableColumnName = $thisline[6];
			$thisresult->ParameterType = $thisline[7];
			$thisresult->ParameterDataType = $thisline[8];
			$thisresult->FixedParameter = $thisline[9];
			$thisresult->AnotherTableName = $thisline[10];
			$thisresult->AnotherTableAliasName = $thisline[11];
			$thisresult->AnotherFieldName = $thisline[12];
			$thisresult->JoinType = $thisline[13];
			$thisresult->ORGroup = $thisline[14];
			$thisresult->RelationalOperator = $thisline[15];
			$thisresult->WhereOrder = $thisline[16];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdafuncselectwhere($param_dafuncselectwhere_PID_where, $param_dafuncselectwhere_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafuncselectwhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafuncselectwhere ==
		
		$last_sql_command_for_mtooldb = "select dafuncselectwhere.ProjectPID, dafuncselectwhere.daPID, dafuncselectwhere.dafuncPID, dafuncselectwhere.PID, dafuncselectwhere.targetTableName, dafuncselectwhere.targetTableAliasName, dafuncselectwhere.targetTableColumnName, dafuncselectwhere.ParameterType, dafuncselectwhere.ParameterDataType, dafuncselectwhere.FixedParameter, dafuncselectwhere.AnotherTableName, dafuncselectwhere.AnotherTableAliasName, dafuncselectwhere.AnotherFieldName, dafuncselectwhere.JoinType, dafuncselectwhere.ORGroup, dafuncselectwhere.RelationalOperator, dafuncselectwhere.WhereOrder from dafuncselectwhere where dafuncselectwhere.PID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_PID_where) . "' and dafuncselectwhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncselectwhereData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableName = $thisline[4];
			$thisresult->targetTableAliasName = $thisline[5];
			$thisresult->targetTableColumnName = $thisline[6];
			$thisresult->ParameterType = $thisline[7];
			$thisresult->ParameterDataType = $thisline[8];
			$thisresult->FixedParameter = $thisline[9];
			$thisresult->AnotherTableName = $thisline[10];
			$thisresult->AnotherTableAliasName = $thisline[11];
			$thisresult->AnotherFieldName = $thisline[12];
			$thisresult->JoinType = $thisline[13];
			$thisresult->ORGroup = $thisline[14];
			$thisresult->RelationalOperator = $thisline[15];
			$thisresult->WhereOrder = $thisline[16];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdafuncselectwhere($dafuncselectwhereObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafuncselectwhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafuncselectwhere ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncselectwhere (ProjectPID, daPID, dafuncPID, targetTableName, targetTableAliasName, targetTableColumnName, ParameterType, ParameterDataType, FixedParameter, AnotherTableName, AnotherTableAliasName, AnotherFieldName, JoinType, ORGroup, RelationalOperator) values('" . $mtooldb->real_escape_string($dafuncselectwhereObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->targetTableName) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->targetTableAliasName) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->targetTableColumnName) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ParameterType) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ParameterDataType) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->FixedParameter) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->AnotherTableName) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->AnotherTableAliasName) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->AnotherFieldName) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->JoinType) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ORGroup) . "', '" . $mtooldb->real_escape_string($dafuncselectwhereObj->RelationalOperator) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafuncselectwhere($dafuncselectwhereObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafuncselectwhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafuncselectwhere ==
		
		$last_sql_command_for_mtooldb = "update dafuncselectwhere SET targetTableName = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->targetTableName) . "', targetTableAliasName = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->targetTableAliasName) . "', targetTableColumnName = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->targetTableColumnName) . "', ParameterType = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ParameterType) . "', ParameterDataType = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ParameterDataType) . "', FixedParameter = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->FixedParameter) . "', AnotherTableName = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->AnotherTableName) . "', AnotherTableAliasName = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->AnotherTableAliasName) . "', AnotherFieldName = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->AnotherFieldName) . "', JoinType = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->JoinType) . "', ORGroup = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ORGroup) . "', RelationalOperator = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->RelationalOperator) . "' where dafuncselectwhere.PID = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->PID) . "' and dafuncselectwhere.ProjectPID = '" . $mtooldb->real_escape_string($dafuncselectwhereObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafuncselectwhere($param_dafuncselectwhere_PID_where, $param_dafuncselectwhere_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafuncselectwhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafuncselectwhere ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncselectwhere where dafuncselectwhere.PID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_PID_where) . "' and dafuncselectwhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedafuncselectwhereOrder($param_dafuncselectwhere_WhereOrder_update, $param_dafuncselectwhere_PID_where, $param_dafuncselectwhere_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedafuncselectwhereOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedafuncselectwhereOrder ==
		
		$last_sql_command_for_mtooldb = "update dafuncselectwhere SET WhereOrder = " . $param_dafuncselectwhere_WhereOrder_update . " where dafuncselectwhere.PID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_PID_where) . "' and dafuncselectwhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAPIDforMovingFunction($param_dafuncselectwhere_daPID_update, $param_dafuncselectwhere_ProjectPID_where, $param_dafuncselectwhere_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		
		$last_sql_command_for_mtooldb = "update dafuncselectwhere SET daPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_daPID_update) . "' where dafuncselectwhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_ProjectPID_where) . "' and dafuncselectwhere.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncselectwhere_dafuncPID_where) . "'";
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