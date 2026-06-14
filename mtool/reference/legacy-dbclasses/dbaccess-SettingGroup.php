<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class SettingGroupDBAccess
{
	public function __construct() {
	}
	
	public function GetSettingGroupList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSettingGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSettingGroupList ==
		
		$last_sql_command_for_mtooldb = "select SettingGroup.PID, SettingGroup.Name from SettingGroup";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SettingGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->Name = $thisline[1];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSettingGroupIfYouAreAdminList($param_SettingGroupUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSettingGroupIfYouAreAdminList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSettingGroupIfYouAreAdminList ==
		
		$last_sql_command_for_mtooldb = "select SettingGroup.PID, SettingGroup.Name from SettingGroup join SettingGroupUser where SettingGroupUser.username = '" . $mtooldb->real_escape_string($param_SettingGroupUser_username_where) . "' and SettingGroupUser.IsAdmin = '" . $mtooldb->real_escape_string("1") . "' and SettingGroupUser.SettingGroupPID = SettingGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SettingGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->Name = $thisline[1];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSettingGroupIfYouAreUserList($param_SettingGroupUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSettingGroupIfYouAreUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSettingGroupIfYouAreUserList ==
		
		$last_sql_command_for_mtooldb = "select SettingGroup.PID, SettingGroup.Name from SettingGroup join SettingGroupUser where SettingGroupUser.username = '" . $mtooldb->real_escape_string($param_SettingGroupUser_username_where) . "' and SettingGroupUser.SettingGroupPID = SettingGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SettingGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->Name = $thisline[1];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSettingGroup($param_SettingGroup_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSettingGroup ==
		
		$last_sql_command_for_mtooldb = "select SettingGroup.PID, SettingGroup.Name from SettingGroup where SettingGroup.PID = '" . $mtooldb->real_escape_string($param_SettingGroup_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SettingGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->Name = $thisline[1];
			return $thisresult;
		}
		return NULL;
	}
	public function GetSettingGroupIfYouAreAdmin($param_SettingGroupUser_SettingGroupPID_where, $param_SettingGroupUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSettingGroupIfYouAreAdmin ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSettingGroupIfYouAreAdmin ==
		
		$last_sql_command_for_mtooldb = "select SettingGroup.PID, SettingGroup.Name from SettingGroup join SettingGroupUser where SettingGroupUser.SettingGroupPID = '" . $mtooldb->real_escape_string($param_SettingGroupUser_SettingGroupPID_where) . "' and SettingGroupUser.username = '" . $mtooldb->real_escape_string($param_SettingGroupUser_username_where) . "' and SettingGroupUser.IsAdmin = '" . $mtooldb->real_escape_string("1") . "' and SettingGroupUser.SettingGroupPID = SettingGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SettingGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->Name = $thisline[1];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertSettingGroup($SettingGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertSettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertSettingGroup ==
		
		$last_sql_command_for_mtooldb = "insert into SettingGroup (Name) values('" . $mtooldb->real_escape_string($SettingGroupObj->Name) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSettingGroup($SettingGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSettingGroup ==
		
		$last_sql_command_for_mtooldb = "update SettingGroup SET Name = '" . $mtooldb->real_escape_string($SettingGroupObj->Name) . "' where SettingGroup.PID = '" . $mtooldb->real_escape_string($SettingGroupObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteSettingGroup($SettingGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteSettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteSettingGroup ==
		
		$last_sql_command_for_mtooldb = "delete from SettingGroup where SettingGroup.PID = '" . $mtooldb->real_escape_string($SettingGroupObj->PID) . "'";
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