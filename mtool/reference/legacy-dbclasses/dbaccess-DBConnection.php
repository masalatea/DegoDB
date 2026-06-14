<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DBConnectionDBAccess
{
	public function __construct() {
	}
	
	public function GetDBConnectionList($param_DBConnection_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBConnectionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBConnectionList ==
		
		$last_sql_command_for_mtooldb = "select DBConnection.PID, DBConnection.SettingGroupPID, DBConnection.ServerPID, DBConnection.DBServerType, DBConnection.DBName, DBConnection.ObjectNameForPHP, Server.LocalServerName, Server.IP from DBConnection LEFT OUTER JOIN Server ON DBConnection.ServerPID = Server.PID where DBConnection.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBConnection_SettingGroupPID_where) . "' order by DBConnection.ServerPID,DBConnection.DBName,DBConnection.ObjectNameForPHP,DBConnection.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBConnectionData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->DBServerType = $thisline[3];
			$thisresult->DBName = $thisline[4];
			$thisresult->ObjectNameForPHP = $thisline[5];
			$thisresult->ServerLocalServerName = $thisline[6];
			$thisresult->ServerIP = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDBConnection($param_DBConnection_PID_where, $param_DBConnection_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBConnection ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBConnection ==
		
		$last_sql_command_for_mtooldb = "select DBConnection.PID, DBConnection.SettingGroupPID, DBConnection.ServerPID, DBConnection.DBServerType, DBConnection.DBName, DBConnection.ObjectNameForPHP, Server.LocalServerName, Server.IP from DBConnection LEFT OUTER JOIN Server ON DBConnection.ServerPID = Server.PID where DBConnection.PID = '" . $mtooldb->real_escape_string($param_DBConnection_PID_where) . "' and DBConnection.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBConnection_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBConnectionData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->DBServerType = $thisline[3];
			$thisresult->DBName = $thisline[4];
			$thisresult->ObjectNameForPHP = $thisline[5];
			$thisresult->ServerLocalServerName = $thisline[6];
			$thisresult->ServerIP = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDBConnection($DBConnectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDBConnection ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDBConnection ==
		
		$last_sql_command_for_mtooldb = "insert into DBConnection (SettingGroupPID, ServerPID, DBServerType, DBName, ObjectNameForPHP) values('" . $mtooldb->real_escape_string($DBConnectionObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($DBConnectionObj->ServerPID) . "', '" . $mtooldb->real_escape_string($DBConnectionObj->DBServerType) . "', '" . $mtooldb->real_escape_string($DBConnectionObj->DBName) . "', '" . $mtooldb->real_escape_string($DBConnectionObj->ObjectNameForPHP) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBConnection($DBConnectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBConnection ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBConnection ==
		
		$last_sql_command_for_mtooldb = "update DBConnection SET SettingGroupPID = '" . $mtooldb->real_escape_string($DBConnectionObj->SettingGroupPID) . "', ServerPID = '" . $mtooldb->real_escape_string($DBConnectionObj->ServerPID) . "', DBServerType = '" . $mtooldb->real_escape_string($DBConnectionObj->DBServerType) . "', DBName = '" . $mtooldb->real_escape_string($DBConnectionObj->DBName) . "', ObjectNameForPHP = '" . $mtooldb->real_escape_string($DBConnectionObj->ObjectNameForPHP) . "' where DBConnection.PID = '" . $mtooldb->real_escape_string($DBConnectionObj->PID) . "' and DBConnection.SettingGroupPID = '" . $mtooldb->real_escape_string($DBConnectionObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDBConnection($DBConnectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDBConnection ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDBConnection ==
		
		$last_sql_command_for_mtooldb = "delete from DBConnection where DBConnection.PID = '" . $mtooldb->real_escape_string($DBConnectionObj->PID) . "' and DBConnection.SettingGroupPID = '" . $mtooldb->real_escape_string($DBConnectionObj->SettingGroupPID) . "'";
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