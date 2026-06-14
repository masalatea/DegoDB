<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class SettingGroupUserDBAccess
{
	public function __construct() {
	}
	
	public function GetSettingGroupUserList($param_SettingGroupUser_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSettingGroupUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSettingGroupUserList ==
		
		$last_sql_command_for_mtooldb = "select SettingGroupUser.PID, SettingGroupUser.SettingGroupPID, SettingGroupUser.username, SettingGroupUser.IsAdmin from SettingGroupUser where SettingGroupUser.SettingGroupPID = '" . $mtooldb->real_escape_string($param_SettingGroupUser_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SettingGroupUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsAdmin = $thisline[3];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertSettingGroupUser($SettingGroupUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertSettingGroupUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertSettingGroupUser ==
		
		$last_sql_command_for_mtooldb = "insert into SettingGroupUser (SettingGroupPID, username, IsAdmin) values('" . $mtooldb->real_escape_string($SettingGroupUserObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($SettingGroupUserObj->username) . "', '" . $mtooldb->real_escape_string($SettingGroupUserObj->IsAdmin) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSettingGroupUser($SettingGroupUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSettingGroupUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSettingGroupUser ==
		
		$last_sql_command_for_mtooldb = "update SettingGroupUser SET SettingGroupPID = '" . $mtooldb->real_escape_string($SettingGroupUserObj->SettingGroupPID) . "', username = '" . $mtooldb->real_escape_string($SettingGroupUserObj->username) . "', IsAdmin = '" . $mtooldb->real_escape_string($SettingGroupUserObj->IsAdmin) . "' where SettingGroupUser.SettingGroupPID = '" . $mtooldb->real_escape_string($SettingGroupUserObj->SettingGroupPID) . "' and SettingGroupUser.username = '" . $mtooldb->real_escape_string($SettingGroupUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteSettingGroupUser($SettingGroupUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteSettingGroupUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteSettingGroupUser ==
		
		$last_sql_command_for_mtooldb = "delete from SettingGroupUser where SettingGroupUser.SettingGroupPID = '" . $mtooldb->real_escape_string($SettingGroupUserObj->SettingGroupPID) . "' and SettingGroupUser.username = '" . $mtooldb->real_escape_string($SettingGroupUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUnassignedUser()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUnassignedUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUnassignedUser ==
		
		$last_sql_command_for_mtooldb = "delete from SettingGroupUser where SettingGroupUser.SettingGroupPID not in (select PID from SettingGroup)";
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