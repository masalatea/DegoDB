<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daCustomProxyFuncDBAccess
{
	public function __construct() {
	}
	
	public function GetdaCustomProxyFuncList($param_daCustomProxyFunc_ProjectPID_where, $param_daCustomProxyFunc_daCustomProxyPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFuncList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFuncList ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxyFunc.ProjectPID, daCustomProxyFunc.daCustomProxyPID, daCustomProxyFunc.PID, daCustomProxyFunc.dafuncPID, daCustomProxyFunc.IsList, daCustomProxyFunc.FunctionListOrder, daCustomProxyFunc.AddIndentCount, daCustomProxyFunc.AddIndentType from daCustomProxyFunc where daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "' and daCustomProxyFunc.daCustomProxyPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_daCustomProxyPID_where) . "' order by daCustomProxyFunc.FunctionListOrder,daCustomProxyFunc.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyFuncData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->dafuncPID = $thisline[3];
			$thisresult->IsList = $thisline[4];
			$thisresult->FunctionListOrder = $thisline[5];
			$thisresult->AddIndentCount = $thisline[6];
			$thisresult->AddIndentType = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdaCustomProxyFunc($param_daCustomProxyFunc_PID_where, $param_daCustomProxyFunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFunc ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFunc ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxyFunc.ProjectPID, daCustomProxyFunc.daCustomProxyPID, daCustomProxyFunc.PID, daCustomProxyFunc.dafuncPID, daCustomProxyFunc.IsList, daCustomProxyFunc.FunctionListOrder, daCustomProxyFunc.AddIndentCount, daCustomProxyFunc.AddIndentType from daCustomProxyFunc where daCustomProxyFunc.PID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_PID_where) . "' and daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyFuncData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->dafuncPID = $thisline[3];
			$thisresult->IsList = $thisline[4];
			$thisresult->FunctionListOrder = $thisline[5];
			$thisresult->AddIndentCount = $thisline[6];
			$thisresult->AddIndentType = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function GetdaCustomProxyFuncByDAFuncList($param_daCustomProxyFunc_dafuncPID_where, $param_daCustomProxyFunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFuncByDAFuncList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFuncByDAFuncList ==
		
		$last_sql_command_for_mtooldb = "select distinct daCustomProxyFunc.ProjectPID, daCustomProxyFunc.daCustomProxyPID, daCustomProxyFunc.PID, daCustomProxyFunc.dafuncPID, daCustomProxyFunc.IsList, daCustomProxyFunc.FunctionListOrder, daCustomProxyFunc.AddIndentCount, daCustomProxyFunc.AddIndentType from daCustomProxyFunc where daCustomProxyFunc.dafuncPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_dafuncPID_where) . "' and daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyFuncData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->dafuncPID = $thisline[3];
			$thisresult->IsList = $thisline[4];
			$thisresult->FunctionListOrder = $thisline[5];
			$thisresult->AddIndentCount = $thisline[6];
			$thisresult->AddIndentType = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertdaCustomProxyFunc($daCustomProxyFuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertdaCustomProxyFunc ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertdaCustomProxyFunc ==
		
		$last_sql_command_for_mtooldb = "insert into daCustomProxyFunc (ProjectPID, daCustomProxyPID, dafuncPID, IsList, AddIndentCount, AddIndentType) values('" . $mtooldb->real_escape_string($daCustomProxyFuncObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->daCustomProxyPID) . "', '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->IsList) . "', '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->AddIndentCount) . "', '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->AddIndentType) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedaCustomProxyFunc($daCustomProxyFuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedaCustomProxyFunc ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedaCustomProxyFunc ==
		
		$last_sql_command_for_mtooldb = "update daCustomProxyFunc SET dafuncPID = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->dafuncPID) . "', IsList = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->IsList) . "', AddIndentCount = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->AddIndentCount) . "', AddIndentType = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->AddIndentType) . "' where daCustomProxyFunc.PID = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->PID) . "' and daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletedaCustomProxyFunc($daCustomProxyFuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletedaCustomProxyFunc ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletedaCustomProxyFunc ==
		
		$last_sql_command_for_mtooldb = "delete from daCustomProxyFunc where daCustomProxyFunc.PID = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->PID) . "' and daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($daCustomProxyFuncObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedaCustomProxyFuncFunctionListOrder($param_daCustomProxyFunc_FunctionListOrder_update, $param_daCustomProxyFunc_PID_where, $param_daCustomProxyFunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedaCustomProxyFuncFunctionListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedaCustomProxyFuncFunctionListOrder ==
		
		$last_sql_command_for_mtooldb = "update daCustomProxyFunc SET FunctionListOrder = " . $param_daCustomProxyFunc_FunctionListOrder_update . " where daCustomProxyFunc.PID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_PID_where) . "' and daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "'";
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