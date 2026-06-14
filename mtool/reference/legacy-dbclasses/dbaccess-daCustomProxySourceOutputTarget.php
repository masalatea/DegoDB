<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daCustomProxySourceOutputTargetDBAccess
{
	public function __construct() {
	}
	
	public function GetdaCustomProxySourceOutputTargetList($param_daCustomProxySourceOutputTarget_ProjectPID_where, $param_daCustomProxySourceOutputTarget_daCustomProxyPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxySourceOutputTargetList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxySourceOutputTargetList ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxySourceOutputTarget.ProjectPID, daCustomProxySourceOutputTarget.daCustomProxyPID, daCustomProxySourceOutputTarget.PID, daCustomProxySourceOutputTarget.ProjectSourceOutputPID from daCustomProxySourceOutputTarget where daCustomProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxySourceOutputTarget_ProjectPID_where) . "' and daCustomProxySourceOutputTarget.daCustomProxyPID = '" . $mtooldb->real_escape_string($param_daCustomProxySourceOutputTarget_daCustomProxyPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxySourceOutputTargetData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->ProjectSourceOutputPID = $thisline[3];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdaCustomProxySourceOutputTargetForOneOutputSource($param_daCustomProxySourceOutputTarget_ProjectPID_where, $param_daCustomProxySourceOutputTarget_daCustomProxyPID_where, $param_daCustomProxySourceOutputTarget_ProjectSourceOutputPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxySourceOutputTargetForOneOutputSource ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxySourceOutputTargetForOneOutputSource ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxySourceOutputTarget.ProjectPID, daCustomProxySourceOutputTarget.daCustomProxyPID, daCustomProxySourceOutputTarget.PID, daCustomProxySourceOutputTarget.ProjectSourceOutputPID from daCustomProxySourceOutputTarget where daCustomProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxySourceOutputTarget_ProjectPID_where) . "' and daCustomProxySourceOutputTarget.daCustomProxyPID = '" . $mtooldb->real_escape_string($param_daCustomProxySourceOutputTarget_daCustomProxyPID_where) . "' and daCustomProxySourceOutputTarget.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_daCustomProxySourceOutputTarget_ProjectSourceOutputPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxySourceOutputTargetData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->daCustomProxyPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->ProjectSourceOutputPID = $thisline[3];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertdaCustomProxySourceOutputTarget($daCustomProxySourceOutputTargetObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertdaCustomProxySourceOutputTarget ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertdaCustomProxySourceOutputTarget ==
		
		$last_sql_command_for_mtooldb = "insert into daCustomProxySourceOutputTarget (ProjectPID, daCustomProxyPID, ProjectSourceOutputPID) values('" . $mtooldb->real_escape_string($daCustomProxySourceOutputTargetObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($daCustomProxySourceOutputTargetObj->daCustomProxyPID) . "', '" . $mtooldb->real_escape_string($daCustomProxySourceOutputTargetObj->ProjectSourceOutputPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletedaCustomProxySourceOutputTarget($daCustomProxySourceOutputTargetObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletedaCustomProxySourceOutputTarget ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletedaCustomProxySourceOutputTarget ==
		
		$last_sql_command_for_mtooldb = "delete from daCustomProxySourceOutputTarget where daCustomProxySourceOutputTarget.PID = '" . $mtooldb->real_escape_string($daCustomProxySourceOutputTargetObj->PID) . "' and daCustomProxySourceOutputTarget.ProjectPID = '" . $mtooldb->real_escape_string($daCustomProxySourceOutputTargetObj->ProjectPID) . "'";
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