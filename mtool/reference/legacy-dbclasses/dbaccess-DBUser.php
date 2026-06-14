<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DBUserDBAccess
{
	public function __construct() {
	}
	
	public function GetDBUserList($param_DBConnection_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBUserList ==
		
		$last_sql_command_for_mtooldb = "select DBUser.PID, DBUser.SettingGroupPID, DBUser.DBConnectionPID, DBUser.User, DBUser.Password, DBConnection.DBName from DBUser LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID where DBConnection.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBConnection_SettingGroupPID_where) . "' order by DBConnection.ServerPID,DBConnection.DBName,DBUser.User,DBUser.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->DBConnectionPID = $thisline[2];
			$thisresult->User = $thisline[3];
			$thisresult->Password = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDBUser($param_DBUser_PID_where, $param_DBConnection_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBUser ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBUser ==
		
		$last_sql_command_for_mtooldb = "select DBUser.PID, DBUser.SettingGroupPID, DBUser.DBConnectionPID, DBUser.User, DBUser.Password, DBConnection.DBName from DBUser LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID where DBUser.PID = '" . $mtooldb->real_escape_string($param_DBUser_PID_where) . "' and DBConnection.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBConnection_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->DBConnectionPID = $thisline[2];
			$thisresult->User = $thisline[3];
			$thisresult->Password = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function GetDBUserForAnySettingGroup($param_DBUser_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBUserForAnySettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBUserForAnySettingGroup ==
		
		$last_sql_command_for_mtooldb = "select DBUser.PID, DBUser.SettingGroupPID, DBUser.DBConnectionPID, DBUser.User, DBUser.Password, DBConnection.DBName from DBUser LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID where DBUser.PID = '" . $mtooldb->real_escape_string($param_DBUser_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->DBConnectionPID = $thisline[2];
			$thisresult->User = $thisline[3];
			$thisresult->Password = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDBUser($DBUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDBUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDBUser ==
		
		$last_sql_command_for_mtooldb = "insert into DBUser (SettingGroupPID, DBConnectionPID, User, Password) values('" . $mtooldb->real_escape_string($DBUserObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($DBUserObj->DBConnectionPID) . "', '" . $mtooldb->real_escape_string($DBUserObj->User) . "', '" . $mtooldb->real_escape_string($DBUserObj->Password) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBUser($DBUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBUser ==
		
		$last_sql_command_for_mtooldb = "update DBUser SET DBConnectionPID = '" . $mtooldb->real_escape_string($DBUserObj->DBConnectionPID) . "', User = '" . $mtooldb->real_escape_string($DBUserObj->User) . "', Password = '" . $mtooldb->real_escape_string($DBUserObj->Password) . "' where DBUser.PID = '" . $mtooldb->real_escape_string($DBUserObj->PID) . "' and DBUser.SettingGroupPID = '" . $mtooldb->real_escape_string($DBUserObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDBUser($DBUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDBUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDBUser ==
		
		$last_sql_command_for_mtooldb = "delete from DBUser where DBUser.PID = '" . $mtooldb->real_escape_string($DBUserObj->PID) . "' and DBUser.SettingGroupPID = '" . $mtooldb->real_escape_string($DBUserObj->SettingGroupPID) . "'";
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