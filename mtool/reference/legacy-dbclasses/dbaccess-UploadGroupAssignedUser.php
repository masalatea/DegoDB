<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadGroupAssignedUserDBAccess
{
	public function __construct() {
	}
	
	public function GetUploadGroupAssignedUserList($param_UploadGroupAssignedUser_UploadGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedUserList ==
		
		$last_sql_command_for_mtooldb = "select UploadGroupAssignedUser.PID, UploadGroupAssignedUser.UploadGroupPID, UploadGroupAssignedUser.username from UploadGroupAssignedUser where UploadGroupAssignedUser.UploadGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedUser_UploadGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupAssignedUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadGroupPID = $thisline[1];
			$thisresult->username = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertUploadGroupAssignedUser($UploadGroupAssignedUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadGroupAssignedUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadGroupAssignedUser ==
		
		$last_sql_command_for_mtooldb = "insert into UploadGroupAssignedUser (UploadGroupPID, username) values('" . $mtooldb->real_escape_string($UploadGroupAssignedUserObj->UploadGroupPID) . "', '" . $mtooldb->real_escape_string($UploadGroupAssignedUserObj->username) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadGroupAssignedUser($UploadGroupAssignedUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadGroupAssignedUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadGroupAssignedUser ==
		
		$last_sql_command_for_mtooldb = "delete from UploadGroupAssignedUser where UploadGroupAssignedUser.UploadGroupPID = '" . $mtooldb->real_escape_string($UploadGroupAssignedUserObj->UploadGroupPID) . "' and UploadGroupAssignedUser.username = '" . $mtooldb->real_escape_string($UploadGroupAssignedUserObj->username) . "'";
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