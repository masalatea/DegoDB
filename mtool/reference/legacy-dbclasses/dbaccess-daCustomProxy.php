<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daCustomProxyDBAccess
{
	public function __construct() {
	}
	
	public function GetdaCustomProxyList($param_daCustomProxy_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxyList ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxy.ProjectPID, daCustomProxy.PID, daCustomProxy.basename, daCustomProxy.name, daCustomProxy.InTransaction, daCustomProxy.AuthType, daCustomProxy.SingleGetFuncPID, daCustomProxy.LastModifiedDT, daCustomProxy.ContinueEvenIfFailedToInsert from daCustomProxy where daCustomProxy.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxy_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->basename = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->InTransaction = $thisline[4];
			$thisresult->AuthType = $thisline[5];
			$thisresult->SingleGetFuncPID = $thisline[6];
			$thisresult->LastModifiedDT = $thisline[7];
			$thisresult->ContinueEvenIfFailedToInsert = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetdaCustomProxy($param_daCustomProxy_PID_where, $param_daCustomProxy_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaCustomProxy ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaCustomProxy ==
		
		$last_sql_command_for_mtooldb = "select daCustomProxy.ProjectPID, daCustomProxy.PID, daCustomProxy.basename, daCustomProxy.name, daCustomProxy.InTransaction, daCustomProxy.AuthType, daCustomProxy.SingleGetFuncPID, daCustomProxy.LastModifiedDT, daCustomProxy.ContinueEvenIfFailedToInsert from daCustomProxy where daCustomProxy.PID = '" . $mtooldb->real_escape_string($param_daCustomProxy_PID_where) . "' and daCustomProxy.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxy_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daCustomProxyData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->basename = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->InTransaction = $thisline[4];
			$thisresult->AuthType = $thisline[5];
			$thisresult->SingleGetFuncPID = $thisline[6];
			$thisresult->LastModifiedDT = $thisline[7];
			$thisresult->ContinueEvenIfFailedToInsert = $thisline[8];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertdaCustomProxy($daCustomProxyObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertdaCustomProxy ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertdaCustomProxy ==
		
		$last_sql_command_for_mtooldb = "insert into daCustomProxy (ProjectPID, basename, name, InTransaction, AuthType, SingleGetFuncPID, ContinueEvenIfFailedToInsert) values('" . $mtooldb->real_escape_string($daCustomProxyObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($daCustomProxyObj->basename) . "', '" . $mtooldb->real_escape_string($daCustomProxyObj->name) . "', '" . $mtooldb->real_escape_string($daCustomProxyObj->InTransaction) . "', '" . $mtooldb->real_escape_string($daCustomProxyObj->AuthType) . "', '" . $mtooldb->real_escape_string($daCustomProxyObj->SingleGetFuncPID) . "', '" . $mtooldb->real_escape_string($daCustomProxyObj->ContinueEvenIfFailedToInsert) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedaCustomProxy($daCustomProxyObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedaCustomProxy ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedaCustomProxy ==
		
		$last_sql_command_for_mtooldb = "update daCustomProxy SET basename = '" . $mtooldb->real_escape_string($daCustomProxyObj->basename) . "', name = '" . $mtooldb->real_escape_string($daCustomProxyObj->name) . "', InTransaction = '" . $mtooldb->real_escape_string($daCustomProxyObj->InTransaction) . "', AuthType = '" . $mtooldb->real_escape_string($daCustomProxyObj->AuthType) . "', SingleGetFuncPID = '" . $mtooldb->real_escape_string($daCustomProxyObj->SingleGetFuncPID) . "', LastModifiedDT = now(), ContinueEvenIfFailedToInsert = '" . $mtooldb->real_escape_string($daCustomProxyObj->ContinueEvenIfFailedToInsert) . "' where daCustomProxy.PID = '" . $mtooldb->real_escape_string($daCustomProxyObj->PID) . "' and daCustomProxy.ProjectPID = '" . $mtooldb->real_escape_string($daCustomProxyObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLastModifiedDT($param_daCustomProxy_PID_where, $param_daCustomProxy_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		
		$last_sql_command_for_mtooldb = "update daCustomProxy SET LastModifiedDT = now() where daCustomProxy.PID = '" . $mtooldb->real_escape_string($param_daCustomProxy_PID_where) . "' and daCustomProxy.ProjectPID = '" . $mtooldb->real_escape_string($param_daCustomProxy_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletedaCustomProxy($daCustomProxyObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletedaCustomProxy ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletedaCustomProxy ==
		
		$last_sql_command_for_mtooldb = "delete from daCustomProxy where daCustomProxy.PID = '" . $mtooldb->real_escape_string($daCustomProxyObj->PID) . "' and daCustomProxy.ProjectPID = '" . $mtooldb->real_escape_string($daCustomProxyObj->ProjectPID) . "'";
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