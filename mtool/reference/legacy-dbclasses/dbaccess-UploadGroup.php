<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadGroupDBAccess
{
	public function __construct() {
	}
	
	public function GetUploadGroupList($param_UploadGroup_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupList ==
		
		$last_sql_command_for_mtooldb = "select UploadGroup.PID, UploadGroup.SettingGroupPID, UploadGroup.Name from UploadGroup join SettingGroup where UploadGroup.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroup_SettingGroupPID_where) . "' and UploadGroup.SettingGroupPID = SettingGroup.PID order by UploadGroup.ListOrder,UploadGroup.Name,UploadGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadGroupByUserList($param_UploadGroupAssignedUser_username_where, $param_UploadGroup_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupByUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupByUserList ==
		
		$last_sql_command_for_mtooldb = "select distinct UploadGroup.PID, UploadGroup.SettingGroupPID, UploadGroup.Name from UploadGroup join UploadGroupAssignedUser join SettingGroup where UploadGroupAssignedUser.username = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedUser_username_where) . "' and UploadGroupAssignedUser.UploadGroupPID = UploadGroup.PID and UploadGroup.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroup_SettingGroupPID_where) . "' and UploadGroup.SettingGroupPID = SettingGroup.PID order by UploadGroup.ListOrder,UploadGroup.Name,UploadGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadGroupByUserForAnySettingGroupList($param_UploadGroupAssignedUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupByUserForAnySettingGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupByUserForAnySettingGroupList ==
		
		$last_sql_command_for_mtooldb = "select UploadGroup.PID, UploadGroup.SettingGroupPID, UploadGroup.Name from UploadGroup join UploadGroupAssignedUser join SettingGroup where UploadGroupAssignedUser.username = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedUser_username_where) . "' and UploadGroupAssignedUser.UploadGroupPID = UploadGroup.PID and UploadGroup.SettingGroupPID = SettingGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadGroup($param_UploadGroup_PID_where, $param_UploadGroup_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroup ==
		
		$last_sql_command_for_mtooldb = "select UploadGroup.PID, UploadGroup.SettingGroupPID, UploadGroup.Name from UploadGroup where UploadGroup.PID = '" . $mtooldb->real_escape_string($param_UploadGroup_PID_where) . "' and UploadGroup.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroup_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertUploadGroup($UploadGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadGroup ==
		
		$last_sql_command_for_mtooldb = "insert into UploadGroup (SettingGroupPID, Name) values('" . $mtooldb->real_escape_string($UploadGroupObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($UploadGroupObj->Name) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroup($UploadGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroup ==
		
		$last_sql_command_for_mtooldb = "update UploadGroup SET Name = '" . $mtooldb->real_escape_string($UploadGroupObj->Name) . "' where UploadGroup.PID = '" . $mtooldb->real_escape_string($UploadGroupObj->PID) . "' and UploadGroup.SettingGroupPID = '" . $mtooldb->real_escape_string($UploadGroupObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroupOrder($param_UploadGroup_ListOrder_update, $param_UploadGroup_PID_where, $param_UploadGroup_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupOrder ==
		
		$last_sql_command_for_mtooldb = "update UploadGroup SET ListOrder = '" . $mtooldb->real_escape_string($param_UploadGroup_ListOrder_update) . "' where UploadGroup.PID = '" . $mtooldb->real_escape_string($param_UploadGroup_PID_where) . "' and UploadGroup.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroup_SettingGroupPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadGroup($UploadGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadGroup ==
		
		$last_sql_command_for_mtooldb = "delete from UploadGroup where UploadGroup.PID = '" . $mtooldb->real_escape_string($UploadGroupObj->PID) . "' and UploadGroup.SettingGroupPID = '" . $mtooldb->real_escape_string($UploadGroupObj->SettingGroupPID) . "'";
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