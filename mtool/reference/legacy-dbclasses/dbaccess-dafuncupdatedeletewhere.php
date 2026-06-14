<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncupdatedeletewhereDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncupdatedeletewhereList($param_dafuncupdatedeletewhere_ProjectPID_where, $param_dafuncupdatedeletewhere_daPID_where, $param_dafuncupdatedeletewhere_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncupdatedeletewhereList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncupdatedeletewhereList ==
		
		$last_sql_command_for_mtooldb = "select dafuncupdatedeletewhere.ProjectPID, dafuncupdatedeletewhere.daPID, dafuncupdatedeletewhere.dafuncPID, dafuncupdatedeletewhere.PID, dafuncupdatedeletewhere.targetTableColumnName, dafuncupdatedeletewhere.ParameterType, dafuncupdatedeletewhere.ParameterDataType, dafuncupdatedeletewhere.FixedParameter, dafuncupdatedeletewhere.ORGroup, dafuncupdatedeletewhere.RelationalOperator, dafuncupdatedeletewhere.WhereOrder from dafuncupdatedeletewhere where dafuncupdatedeletewhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_ProjectPID_where) . "' and dafuncupdatedeletewhere.daPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_daPID_where) . "' and dafuncupdatedeletewhere.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_dafuncPID_where) . "' order by dafuncupdatedeletewhere.WhereOrder,dafuncupdatedeletewhere.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncupdatedeletewhereData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableColumnName = $thisline[4];
			$thisresult->ParameterType = $thisline[5];
			$thisresult->ParameterDataType = $thisline[6];
			$thisresult->FixedParameter = $thisline[7];
			$thisresult->ORGroup = $thisline[8];
			$thisresult->RelationalOperator = $thisline[9];
			$thisresult->WhereOrder = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdafuncupdatedeletewhere($param_dafuncupdatedeletewhere_PID_where, $param_dafuncupdatedeletewhere_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafuncupdatedeletewhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafuncupdatedeletewhere ==
		
		$last_sql_command_for_mtooldb = "select dafuncupdatedeletewhere.ProjectPID, dafuncupdatedeletewhere.daPID, dafuncupdatedeletewhere.dafuncPID, dafuncupdatedeletewhere.PID, dafuncupdatedeletewhere.targetTableColumnName, dafuncupdatedeletewhere.ParameterType, dafuncupdatedeletewhere.ParameterDataType, dafuncupdatedeletewhere.FixedParameter, dafuncupdatedeletewhere.ORGroup, dafuncupdatedeletewhere.RelationalOperator, dafuncupdatedeletewhere.WhereOrder from dafuncupdatedeletewhere where dafuncupdatedeletewhere.PID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_PID_where) . "' and dafuncupdatedeletewhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncupdatedeletewhereData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->targetTableColumnName = $thisline[4];
			$thisresult->ParameterType = $thisline[5];
			$thisresult->ParameterDataType = $thisline[6];
			$thisresult->FixedParameter = $thisline[7];
			$thisresult->ORGroup = $thisline[8];
			$thisresult->RelationalOperator = $thisline[9];
			$thisresult->WhereOrder = $thisline[10];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdafuncupdatedeletewhere($dafuncupdatedeletewhereObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafuncupdatedeletewhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafuncupdatedeletewhere ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncupdatedeletewhere (ProjectPID, daPID, dafuncPID, targetTableColumnName, ParameterType, ParameterDataType, FixedParameter, ORGroup, RelationalOperator) values('" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->targetTableColumnName) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ParameterType) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ParameterDataType) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->FixedParameter) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ORGroup) . "', '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->RelationalOperator) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafuncupdatedeletewhere($dafuncupdatedeletewhereObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafuncupdatedeletewhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafuncupdatedeletewhere ==
		
		$last_sql_command_for_mtooldb = "update dafuncupdatedeletewhere SET targetTableColumnName = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->targetTableColumnName) . "', ParameterType = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ParameterType) . "', ParameterDataType = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ParameterDataType) . "', FixedParameter = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->FixedParameter) . "', ORGroup = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ORGroup) . "', RelationalOperator = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->RelationalOperator) . "' where dafuncupdatedeletewhere.PID = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->PID) . "' and dafuncupdatedeletewhere.ProjectPID = '" . $mtooldb->real_escape_string($dafuncupdatedeletewhereObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafuncupdatedeletewhere($param_dafuncupdatedeletewhere_PID_where, $param_dafuncupdatedeletewhere_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafuncupdatedeletewhere ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafuncupdatedeletewhere ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncupdatedeletewhere where dafuncupdatedeletewhere.PID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_PID_where) . "' and dafuncupdatedeletewhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedafuncupdatedeletewhereOrder($param_dafuncupdatedeletewhere_WhereOrder_update, $param_dafuncupdatedeletewhere_PID_where, $param_dafuncupdatedeletewhere_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedafuncupdatedeletewhereOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedafuncupdatedeletewhereOrder ==
		
		$last_sql_command_for_mtooldb = "update dafuncupdatedeletewhere SET WhereOrder = " . $param_dafuncupdatedeletewhere_WhereOrder_update . " where dafuncupdatedeletewhere.PID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_PID_where) . "' and dafuncupdatedeletewhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAPIDforMovingFunction($param_dafuncupdatedeletewhere_daPID_update, $param_dafuncupdatedeletewhere_ProjectPID_where, $param_dafuncupdatedeletewhere_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		
		$last_sql_command_for_mtooldb = "update dafuncupdatedeletewhere SET daPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_daPID_update) . "' where dafuncupdatedeletewhere.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_ProjectPID_where) . "' and dafuncupdatedeletewhere.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncupdatedeletewhere_dafuncPID_where) . "'";
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