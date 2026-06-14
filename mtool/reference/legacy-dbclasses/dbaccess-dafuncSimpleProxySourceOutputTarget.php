<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncSimpleProxySourceOutputTargetDBAccess
{
	public function __construct() {
	}
	
	public function GetAlldafuncSimpleProxySourceOutputTargetForProjectSourceOutputList($param_dafuncSimpleProxySourceOutputTarget_ProjectPID_where, $param_dafuncSimpleProxySourceOutputTarget_ProjectSourceOutputPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetAlldafuncSimpleProxySourceOutputTargetForProjectSourceOutputList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetAlldafuncSimpleProxySourceOutputTargetForProjectSourceOutputList ==
		
		$last_sql_command_for_mtooldb = "select dafuncSimpleProxySourceOutputTarget.ProjectPID, dafuncSimpleProxySourceOutputTarget.daPID, dafuncSimpleProxySourceOutputTarget.dafuncPID, dafuncSimpleProxySourceOutputTarget.PID, dafuncSimpleProxySourceOutputTarget.ProjectSourceOutputPID, da.name, dafunc.name from dafuncSimpleProxySourceOutputTarget LEFT OUTER JOIN da ON dafuncSimpleProxySourceOutputTarget.daPID = da.PID LEFT OUTER JOIN dafunc ON dafuncSimpleProxySourceOutputTarget.dafuncPID = dafunc.PID where dafuncSimpleProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_ProjectPID_where) . "' and dafuncSimpleProxySourceOutputTarget.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_ProjectSourceOutputPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncSimpleProxySourceOutputTargetData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->ProjectSourceOutputPID = $thisline[4];
			$thisresult->daname = $thisline[5];
			$thisresult->dafuncname = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdafuncSimpleProxySourceOutputTargetList($param_dafuncSimpleProxySourceOutputTarget_ProjectPID_where, $param_dafuncSimpleProxySourceOutputTarget_daPID_where, $param_dafuncSimpleProxySourceOutputTarget_dafuncPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncSimpleProxySourceOutputTargetList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncSimpleProxySourceOutputTargetList ==
		
		$last_sql_command_for_mtooldb = "select dafuncSimpleProxySourceOutputTarget.ProjectPID, dafuncSimpleProxySourceOutputTarget.daPID, dafuncSimpleProxySourceOutputTarget.dafuncPID, dafuncSimpleProxySourceOutputTarget.PID, dafuncSimpleProxySourceOutputTarget.ProjectSourceOutputPID, da.name, dafunc.name from dafuncSimpleProxySourceOutputTarget LEFT OUTER JOIN da ON dafuncSimpleProxySourceOutputTarget.daPID = da.PID LEFT OUTER JOIN dafunc ON dafuncSimpleProxySourceOutputTarget.dafuncPID = dafunc.PID where dafuncSimpleProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_ProjectPID_where) . "' and dafuncSimpleProxySourceOutputTarget.daPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_daPID_where) . "' and dafuncSimpleProxySourceOutputTarget.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_dafuncPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncSimpleProxySourceOutputTargetData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->ProjectSourceOutputPID = $thisline[4];
			$thisresult->daname = $thisline[5];
			$thisresult->dafuncname = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdafuncSimpleProxyForOneOutputSource($param_dafuncSimpleProxySourceOutputTarget_ProjectPID_where, $param_dafuncSimpleProxySourceOutputTarget_daPID_where, $param_dafuncSimpleProxySourceOutputTarget_dafuncPID_where, $param_dafuncSimpleProxySourceOutputTarget_ProjectSourceOutputPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdafuncSimpleProxyForOneOutputSource ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdafuncSimpleProxyForOneOutputSource ==
		
		$last_sql_command_for_mtooldb = "select dafuncSimpleProxySourceOutputTarget.ProjectPID, dafuncSimpleProxySourceOutputTarget.daPID, dafuncSimpleProxySourceOutputTarget.dafuncPID, dafuncSimpleProxySourceOutputTarget.PID, dafuncSimpleProxySourceOutputTarget.ProjectSourceOutputPID, da.name, dafunc.name from dafuncSimpleProxySourceOutputTarget LEFT OUTER JOIN da ON dafuncSimpleProxySourceOutputTarget.daPID = da.PID LEFT OUTER JOIN dafunc ON dafuncSimpleProxySourceOutputTarget.dafuncPID = dafunc.PID where dafuncSimpleProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_ProjectPID_where) . "' and dafuncSimpleProxySourceOutputTarget.daPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_daPID_where) . "' and dafuncSimpleProxySourceOutputTarget.dafuncPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_dafuncPID_where) . "' and dafuncSimpleProxySourceOutputTarget.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_dafuncSimpleProxySourceOutputTarget_ProjectSourceOutputPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dafuncSimpleProxySourceOutputTargetData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daPID = $thisline[1];
			$thisresult->dafuncPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->ProjectSourceOutputPID = $thisline[4];
			$thisresult->daname = $thisline[5];
			$thisresult->dafuncname = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertdafuncSimpleProxySourceOutputTarget($dafuncSimpleProxySourceOutputTargetObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertdafuncSimpleProxySourceOutputTarget ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertdafuncSimpleProxySourceOutputTarget ==
		
		$last_sql_command_for_mtooldb = "insert into dafuncSimpleProxySourceOutputTarget (ProjectPID, daPID, dafuncPID, ProjectSourceOutputPID) values('" . $mtooldb->real_escape_string($dafuncSimpleProxySourceOutputTargetObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dafuncSimpleProxySourceOutputTargetObj->daPID) . "', '" . $mtooldb->real_escape_string($dafuncSimpleProxySourceOutputTargetObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($dafuncSimpleProxySourceOutputTargetObj->ProjectSourceOutputPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletedafuncSimpleProxySourceOutputTarget($dafuncSimpleProxySourceOutputTargetObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletedafuncSimpleProxySourceOutputTarget ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletedafuncSimpleProxySourceOutputTarget ==
		
		$last_sql_command_for_mtooldb = "delete from dafuncSimpleProxySourceOutputTarget where dafuncSimpleProxySourceOutputTarget.PID = '" . $mtooldb->real_escape_string($dafuncSimpleProxySourceOutputTargetObj->PID) . "' and dafuncSimpleProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($dafuncSimpleProxySourceOutputTargetObj->ProjectPID) . "'";
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