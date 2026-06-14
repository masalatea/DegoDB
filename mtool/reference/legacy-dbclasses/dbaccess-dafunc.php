<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncDBAccess
{
	public function __construct() {
	}
	
	public function GetdafuncList($param_dafunc_ProjectPID_where, $param_dafunc_daPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncList ==
		
		$last_sql_command_for_mtooldb = "select dafunc.ProjectPID, dafunc.daPID, dafunc.PID, dafunc.name, dafunc.ActionType, dafunc.InsertUpdateDeleteTargetTable, dafunc.InsertUpdateDeleteParamType, dafunc.SelectByDistinct, dafunc.SortOrderColumns, dafunc.DataClassBaseNameForSelectAction, dafunc.FunctionListOrder, dafunc.memo, dafunc.limitParameterType, dafunc.limitFixedParameter, dafunc.ORGroupType, dafunc.SingleProxy_AuthType, dafunc.SingleProxy_SingleGetFuncPID, dafunc.IsBlobTarget from dafunc where dafunc.ProjectPID = '" . $mtooldb->real_escape_string($param_dafunc_ProjectPID_where) . "' and dafunc.daPID = '" . $mtooldb->real_escape_string($param_dafunc_daPID_where) . "' order by dafunc.FunctionListOrder,dafunc.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->ActionType = $thisline[4];
			$thisresult->InsertUpdateDeleteTargetTable = $thisline[5];
			$thisresult->InsertUpdateDeleteParamType = $thisline[6];
			$thisresult->SelectByDistinct = $thisline[7];
			$thisresult->SortOrderColumns = $thisline[8];
			$thisresult->DataClassBaseNameForSelectAction = $thisline[9];
			$thisresult->FunctionListOrder = $thisline[10];
			$thisresult->memo = $thisline[11];
			$thisresult->limitParameterType = $thisline[12];
			$thisresult->limitFixedParameter = $thisline[13];
			$thisresult->ORGroupType = $thisline[14];
			$thisresult->SingleProxy_AuthType = $thisline[15];
			$thisresult->SingleProxy_SingleGetFuncPID = $thisline[16];
			$thisresult->IsBlobTarget = $thisline[17];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdafunc($param_dafunc_PID_where, $param_dafunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdafunc ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdafunc ==
		
		$last_sql_command_for_mtooldb = "select dafunc.ProjectPID, dafunc.daPID, dafunc.PID, dafunc.name, dafunc.ActionType, dafunc.InsertUpdateDeleteTargetTable, dafunc.InsertUpdateDeleteParamType, dafunc.SelectByDistinct, dafunc.SortOrderColumns, dafunc.DataClassBaseNameForSelectAction, dafunc.FunctionListOrder, dafunc.memo, dafunc.limitParameterType, dafunc.limitFixedParameter, dafunc.ORGroupType, dafunc.SingleProxy_AuthType, dafunc.SingleProxy_SingleGetFuncPID, dafunc.IsBlobTarget from dafunc where dafunc.PID = '" . $mtooldb->real_escape_string($param_dafunc_PID_where) . "' and dafunc.ProjectPID = '" . $mtooldb->real_escape_string($param_dafunc_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->ActionType = $thisline[4];
			$thisresult->InsertUpdateDeleteTargetTable = $thisline[5];
			$thisresult->InsertUpdateDeleteParamType = $thisline[6];
			$thisresult->SelectByDistinct = $thisline[7];
			$thisresult->SortOrderColumns = $thisline[8];
			$thisresult->DataClassBaseNameForSelectAction = $thisline[9];
			$thisresult->FunctionListOrder = $thisline[10];
			$thisresult->memo = $thisline[11];
			$thisresult->limitParameterType = $thisline[12];
			$thisresult->limitFixedParameter = $thisline[13];
			$thisresult->ORGroupType = $thisline[14];
			$thisresult->SingleProxy_AuthType = $thisline[15];
			$thisresult->SingleProxy_SingleGetFuncPID = $thisline[16];
			$thisresult->IsBlobTarget = $thisline[17];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdafunc($dafuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdafunc ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdafunc ==
		
		$last_sql_command_for_mtooldb = "insert into dafunc (ProjectPID, daPID, name, ActionType, InsertUpdateDeleteTargetTable, InsertUpdateDeleteParamType, SelectByDistinct, SortOrderColumns, DataClassBaseNameForSelectAction, memo, limitParameterType, limitFixedParameter, ORGroupType, IsBlobTarget) values('" . $mtooldb->real_escape_string($dafuncObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncObj->name) . "', '" . $mtooldb->real_escape_string($dafuncObj->ActionType) . "', '" . $mtooldb->real_escape_string($dafuncObj->InsertUpdateDeleteTargetTable) . "', '" . $mtooldb->real_escape_string($dafuncObj->InsertUpdateDeleteParamType) . "', '" . $mtooldb->real_escape_string($dafuncObj->SelectByDistinct) . "', '" . $mtooldb->real_escape_string($dafuncObj->SortOrderColumns) . "', '" . $mtooldb->real_escape_string($dafuncObj->DataClassBaseNameForSelectAction) . "', '" . $mtooldb->real_escape_string($dafuncObj->memo) . "', '" . $mtooldb->real_escape_string($dafuncObj->limitParameterType) . "', '" . $mtooldb->real_escape_string($dafuncObj->limitFixedParameter) . "', '" . $mtooldb->real_escape_string($dafuncObj->ORGroupType) . "', '" . $mtooldb->real_escape_string($dafuncObj->IsBlobTarget) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedafunc($dafuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedafunc ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedafunc ==
		
		$last_sql_command_for_mtooldb = "update dafunc SET name = '" . $mtooldb->real_escape_string($dafuncObj->name) . "', ActionType = '" . $mtooldb->real_escape_string($dafuncObj->ActionType) . "', InsertUpdateDeleteTargetTable = '" . $mtooldb->real_escape_string($dafuncObj->InsertUpdateDeleteTargetTable) . "', InsertUpdateDeleteParamType = '" . $mtooldb->real_escape_string($dafuncObj->InsertUpdateDeleteParamType) . "', SelectByDistinct = '" . $mtooldb->real_escape_string($dafuncObj->SelectByDistinct) . "', SortOrderColumns = '" . $mtooldb->real_escape_string($dafuncObj->SortOrderColumns) . "', DataClassBaseNameForSelectAction = '" . $mtooldb->real_escape_string($dafuncObj->DataClassBaseNameForSelectAction) . "', memo = '" . $mtooldb->real_escape_string($dafuncObj->memo) . "', limitParameterType = '" . $mtooldb->real_escape_string($dafuncObj->limitParameterType) . "', limitFixedParameter = '" . $mtooldb->real_escape_string($dafuncObj->limitFixedParameter) . "', ORGroupType = '" . $mtooldb->real_escape_string($dafuncObj->ORGroupType) . "', IsBlobTarget = '" . $mtooldb->real_escape_string($dafuncObj->IsBlobTarget) . "' where dafunc.PID = '" . $mtooldb->real_escape_string($dafuncObj->PID) . "' and dafunc.ProjectPID = '" . $mtooldb->real_escape_string($dafuncObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSingleProxySetting($dafuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSingleProxySetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSingleProxySetting ==
		
		$last_sql_command_for_mtooldb = "update dafunc SET SingleProxy_AuthType = '" . $mtooldb->real_escape_string($dafuncObj->SingleProxy_AuthType) . "', SingleProxy_SingleGetFuncPID = '" . $mtooldb->real_escape_string($dafuncObj->SingleProxy_SingleGetFuncPID) . "' where dafunc.PID = '" . $mtooldb->real_escape_string($dafuncObj->PID) . "' and dafunc.ProjectPID = '" . $mtooldb->real_escape_string($dafuncObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedafunc($param_dafunc_PID_where, $param_dafunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedafunc ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedafunc ==
		
		$last_sql_command_for_mtooldb = "delete from dafunc where dafunc.PID = '" . $mtooldb->real_escape_string($param_dafunc_PID_where) . "' and dafunc.ProjectPID = '" . $mtooldb->real_escape_string($param_dafunc_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedafuncSortOrderColumns($dafuncObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedafuncSortOrderColumns ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedafuncSortOrderColumns ==
		
		$last_sql_command_for_mtooldb = "update dafunc SET SortOrderColumns = '" . $mtooldb->real_escape_string($dafuncObj->SortOrderColumns) . "' where dafunc.PID = '" . $mtooldb->real_escape_string($dafuncObj->PID) . "' and dafunc.ProjectPID = '" . $mtooldb->real_escape_string($dafuncObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedafuncFunctionListOrder($param_dafunc_FunctionListOrder_update, $param_dafunc_PID_where, $param_dafunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedafuncFunctionListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedafuncFunctionListOrder ==
		
		$last_sql_command_for_mtooldb = "update dafunc SET FunctionListOrder = " . $param_dafunc_FunctionListOrder_update . " where dafunc.PID = '" . $mtooldb->real_escape_string($param_dafunc_PID_where) . "' and dafunc.ProjectPID = '" . $mtooldb->real_escape_string($param_dafunc_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAPIDforMovingFunction($param_dafunc_daPID_update, $param_dafunc_ProjectPID_where, $param_dafunc_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAPIDforMovingFunction ==
		
		$last_sql_command_for_mtooldb = "update dafunc SET daPID = '" . $mtooldb->real_escape_string($param_dafunc_daPID_update) . "' where dafunc.ProjectPID = '" . $mtooldb->real_escape_string($param_dafunc_ProjectPID_where) . "' and dafunc.PID = '" . $mtooldb->real_escape_string($param_dafunc_PID_where) . "'";
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