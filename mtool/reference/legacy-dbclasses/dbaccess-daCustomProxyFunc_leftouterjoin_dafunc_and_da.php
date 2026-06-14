<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccess
{
	public function __construct() {
	}
	
	public function GetAlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForProjectSourceOutputList($param_daCustomProxyFunc_ProjectPID_where, $param_daCustomProxySourceOutputTarget_ProjectSourceOutputPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetAlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForProjectSourceOutputList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetAlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForProjectSourceOutputList ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxyFunc.ProjectPID, daCustomProxyFunc.daCustomProxyPID, daCustomProxyFunc.PID, daCustomProxyFunc.dafuncPID, daCustomProxyFunc.IsList, daCustomProxyFunc.FunctionListOrder, daCustomProxyFunc.AddIndentCount, daCustomProxyFunc.AddIndentType, dafunc.name, dafunc.ActionType, da.name, da.PID from daCustomProxyFunc LEFT OUTER JOIN dafunc ON daCustomProxyFunc.dafuncPID = dafunc.PID join daCustomProxySourceOutputTarget LEFT OUTER JOIN da ON dafunc.daPID = da.PID where daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "' and daCustomProxyFunc.ProjectPID = daCustomProxySourceOutputTarget.ProjectPID and daCustomProxyFunc.daCustomProxyPID = daCustomProxySourceOutputTarget.daCustomProxyPID and daCustomProxySourceOutputTarget.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_daCustomProxySourceOutputTarget_ProjectSourceOutputPID_where) . "' order by daCustomProxyFunc.FunctionListOrder,daCustomProxyFunc.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyFunc_leftouterjoin_dafunc_and_daData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->dafuncPID = $thisline[3];
			$thisresult->IsList = $thisline[4];
			$thisresult->FunctionListOrder = $thisline[5];
			$thisresult->AddIndentCount = $thisline[6];
			$thisresult->AddIndentType = $thisline[7];
			$thisresult->dafuncname = $thisline[8];
			$thisresult->dafuncActionType = $thisline[9];
			$thisresult->daname = $thisline[10];
			$thisresult->daPID = $thisline[11];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList($param_daCustomProxyFunc_ProjectPID_where, $param_daCustomProxyFunc_daCustomProxyPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxyFunc.ProjectPID, daCustomProxyFunc.daCustomProxyPID, daCustomProxyFunc.PID, daCustomProxyFunc.dafuncPID, daCustomProxyFunc.IsList, daCustomProxyFunc.FunctionListOrder, daCustomProxyFunc.AddIndentCount, daCustomProxyFunc.AddIndentType, dafunc.name, dafunc.ActionType, da.name, da.PID from daCustomProxyFunc LEFT OUTER JOIN dafunc ON daCustomProxyFunc.dafuncPID = dafunc.PID LEFT OUTER JOIN da ON dafunc.daPID = da.PID where daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "' and daCustomProxyFunc.daCustomProxyPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_daCustomProxyPID_where) . "' order by daCustomProxyFunc.FunctionListOrder,daCustomProxyFunc.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyFunc_leftouterjoin_dafunc_and_daData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->dafuncPID = $thisline[3];
			$thisresult->IsList = $thisline[4];
			$thisresult->FunctionListOrder = $thisline[5];
			$thisresult->AddIndentCount = $thisline[6];
			$thisresult->AddIndentType = $thisline[7];
			$thisresult->dafuncname = $thisline[8];
			$thisresult->dafuncActionType = $thisline[9];
			$thisresult->daname = $thisline[10];
			$thisresult->daPID = $thisline[11];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdaCustomProxyFunc_leftouterjoin_dafunc_and_da($param_daCustomProxyFunc_PID_where, $param_daCustomProxyFunc_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFunc_leftouterjoin_dafunc_and_da ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyFunc_leftouterjoin_dafunc_and_da ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxyFunc.ProjectPID, daCustomProxyFunc.daCustomProxyPID, daCustomProxyFunc.PID, daCustomProxyFunc.dafuncPID, daCustomProxyFunc.IsList, daCustomProxyFunc.FunctionListOrder, daCustomProxyFunc.AddIndentCount, daCustomProxyFunc.AddIndentType, dafunc.name, dafunc.ActionType, da.name, da.PID from daCustomProxyFunc LEFT OUTER JOIN dafunc ON daCustomProxyFunc.dafuncPID = dafunc.PID LEFT OUTER JOIN da ON dafunc.daPID = da.PID where daCustomProxyFunc.PID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_PID_where) . "' and daCustomProxyFunc.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxyFunc_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyFunc_leftouterjoin_dafunc_and_daData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->dafuncPID = $thisline[3];
			$thisresult->IsList = $thisline[4];
			$thisresult->FunctionListOrder = $thisline[5];
			$thisresult->AddIndentCount = $thisline[6];
			$thisresult->AddIndentType = $thisline[7];
			$thisresult->dafuncname = $thisline[8];
			$thisresult->dafuncActionType = $thisline[9];
			$thisresult->daname = $thisline[10];
			$thisresult->daPID = $thisline[11];
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