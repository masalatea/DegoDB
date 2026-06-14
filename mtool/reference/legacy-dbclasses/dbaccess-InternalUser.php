<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class InternalUserDBAccess
{
	public function __construct() {
	}
	
	public function GetInternalUserList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetInternalUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetInternalUserList ==
		
		$last_sql_command_for_mtooldb = "select InternalUser.PID, InternalUser.username, InternalUser.IsSystemAdmin from InternalUser";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new InternalUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->username = $thisline[1];
			$thisresult->IsSystemAdmin = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetInternalUser($param_InternalUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetInternalUser ==
		// == END OF EDITABLE AREA FOR FUNCTION GetInternalUser ==
		
		$last_sql_command_for_mtooldb = "select InternalUser.PID, InternalUser.username, InternalUser.IsSystemAdmin from InternalUser where InternalUser.username = '" . $mtooldb->real_escape_string($param_InternalUser_username_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new InternalUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->username = $thisline[1];
			$thisresult->IsSystemAdmin = $thisline[2];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertInternalUser($InternalUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertInternalUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertInternalUser ==
		
		$last_sql_command_for_mtooldb = "insert into InternalUser (username, IsSystemAdmin) values('" . $mtooldb->real_escape_string($InternalUserObj->username) . "', '" . $mtooldb->real_escape_string($InternalUserObj->IsSystemAdmin) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateInternalUser($InternalUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateInternalUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateInternalUser ==
		
		$last_sql_command_for_mtooldb = "update InternalUser SET username = '" . $mtooldb->real_escape_string($InternalUserObj->username) . "', IsSystemAdmin = '" . $mtooldb->real_escape_string($InternalUserObj->IsSystemAdmin) . "' where InternalUser.username = '" . $mtooldb->real_escape_string($InternalUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteInternalUser($InternalUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteInternalUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteInternalUser ==
		
		$last_sql_command_for_mtooldb = "delete from InternalUser where InternalUser.username = '" . $mtooldb->real_escape_string($InternalUserObj->username) . "'";
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