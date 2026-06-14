<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DBBackupUserDBAccess
{
	public function __construct() {
	}
	
	public function GetDBBackupUserList($param_DBBackupUser_DBBackupPID_where, $param_DBBackupUser_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBBackupUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBBackupUserList ==
		
		$last_sql_command_for_mtooldb = "select DBBackupUser.PID, DBBackupUser.SettingGroupPID, DBBackupUser.DBBackupPID, DBBackupUser.DBConnectionPID, DBBackupUser.DBUserPID, DBConnection.DBName, DBUser.User, DBUser.Password from DBBackupUser join DBConnection join DBUser join SettingGroup join Server where DBBackupUser.DBBackupPID = '" . $mtooldb->real_escape_string($param_DBBackupUser_DBBackupPID_where) . "' and DBBackupUser.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBBackupUser_SettingGroupPID_where) . "' and DBBackupUser.SettingGroupPID = SettingGroup.PID and DBBackupUser.DBConnectionPID = DBConnection.PID and SettingGroup.PID = DBConnection.SettingGroupPID and Server.PID = DBConnection.ServerPID and DBBackupUser.DBUserPID = DBUser.PID and SettingGroup.PID = DBUser.SettingGroupPID and DBConnection.PID = DBUser.DBConnectionPID order by DBBackupUser.SettingGroupPID,DBBackupUser.DBBackupPID,DBConnection.DBName,DBUser.User,DBBackupUser.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBBackupUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->DBBackupPID = $thisline[2];
			$thisresult->DBConnectionPID = $thisline[3];
			$thisresult->DBUserPID = $thisline[4];
			$thisresult->DBConnectionDBName = $thisline[5];
			$thisresult->DBUserUser = $thisline[6];
			$thisresult->DBUserPassword = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertDBBackupUser($DBBackupUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDBBackupUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDBBackupUser ==
		
		$last_sql_command_for_mtooldb = "insert into DBBackupUser (SettingGroupPID, DBBackupPID, DBConnectionPID, DBUserPID) values('" . $mtooldb->real_escape_string($DBBackupUserObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($DBBackupUserObj->DBBackupPID) . "', '" . $mtooldb->real_escape_string($DBBackupUserObj->DBConnectionPID) . "', '" . $mtooldb->real_escape_string($DBBackupUserObj->DBUserPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDBBackupUser($DBBackupUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDBBackupUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDBBackupUser ==
		
		$last_sql_command_for_mtooldb = "delete from DBBackupUser where DBBackupUser.SettingGroupPID = '" . $mtooldb->real_escape_string($DBBackupUserObj->SettingGroupPID) . "' and DBBackupUser.DBBackupPID = '" . $mtooldb->real_escape_string($DBBackupUserObj->DBBackupPID) . "' and DBBackupUser.DBConnectionPID = '" . $mtooldb->real_escape_string($DBBackupUserObj->DBConnectionPID) . "' and DBBackupUser.DBUserPID = '" . $mtooldb->real_escape_string($DBBackupUserObj->DBUserPID) . "'";
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