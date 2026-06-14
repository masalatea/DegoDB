<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DBUserClientHostDBAccess
{
	public function __construct() {
	}
	
	public function GetDBUserClientHostList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBUserClientHostList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBUserClientHostList ==
		
		$last_sql_command_for_mtooldb = "select DBUserClientHost.PID, DBUserClientHost.DBUserPID, DBUserClientHost.ServerPID, Server.LocalServerName, Server.IP, DBConnection.DBName from DBUserClientHost LEFT OUTER JOIN DBUser ON DBUserClientHost.DBUserPID = DBUser.PID join Server LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID where DBUserClientHost.ServerPID = Server.PID order by DBUserClientHost.DBUserPID,Server.LocalServerName";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBUserClientHostData();
			$thisresult->PID = $thisline[0];
			$thisresult->DBUserPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->ServerLocalServerName = $thisline[3];
			$thisresult->ServerIP = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDBUserClientHostByUserList($param_DBUserClientHost_DBUserPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBUserClientHostByUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBUserClientHostByUserList ==
		
		$last_sql_command_for_mtooldb = "select DBUserClientHost.PID, DBUserClientHost.DBUserPID, DBUserClientHost.ServerPID, Server.LocalServerName, Server.IP, DBConnection.DBName from DBUserClientHost LEFT OUTER JOIN DBUser ON DBUserClientHost.DBUserPID = DBUser.PID join Server LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID where DBUserClientHost.DBUserPID = '" . $mtooldb->real_escape_string($param_DBUserClientHost_DBUserPID_where) . "' and DBUserClientHost.ServerPID = Server.PID order by DBUserClientHost.DBUserPID,Server.LocalServerName";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBUserClientHostData();
			$thisresult->PID = $thisline[0];
			$thisresult->DBUserPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->ServerLocalServerName = $thisline[3];
			$thisresult->ServerIP = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDBUserClientHost($param_DBUserClientHost_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBUserClientHost ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBUserClientHost ==
		
		$last_sql_command_for_mtooldb = "select DBUserClientHost.PID, DBUserClientHost.DBUserPID, DBUserClientHost.ServerPID, Server.LocalServerName, Server.IP, DBConnection.DBName from DBUserClientHost LEFT OUTER JOIN DBUser ON DBUserClientHost.DBUserPID = DBUser.PID join Server LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID where DBUserClientHost.PID = '" . $mtooldb->real_escape_string($param_DBUserClientHost_PID_where) . "' and DBUserClientHost.ServerPID = Server.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBUserClientHostData();
			$thisresult->PID = $thisline[0];
			$thisresult->DBUserPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->ServerLocalServerName = $thisline[3];
			$thisresult->ServerIP = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDBUserClientHost($DBUserClientHostObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDBUserClientHost ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDBUserClientHost ==
		
		$last_sql_command_for_mtooldb = "insert into DBUserClientHost (DBUserPID, ServerPID) values('" . $mtooldb->real_escape_string($DBUserClientHostObj->DBUserPID) . "', '" . $mtooldb->real_escape_string($DBUserClientHostObj->ServerPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDBUserClientHost($DBUserClientHostObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDBUserClientHost ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDBUserClientHost ==
		
		$last_sql_command_for_mtooldb = "delete from DBUserClientHost where DBUserClientHost.DBUserPID = '" . $mtooldb->real_escape_string($DBUserClientHostObj->DBUserPID) . "' and DBUserClientHost.ServerPID = '" . $mtooldb->real_escape_string($DBUserClientHostObj->ServerPID) . "'";
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