<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DropboxBaseFolderUserDBAccess
{
	public function __construct() {
	}
	
	public function GetDropboxBaseFolderUserList($param_DropboxBaseFolderUser_DropboxBaseFolderPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderUserList ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolderUser.PID, DropboxBaseFolderUser.DropboxBaseFolderPID, DropboxBaseFolderUser.username, DropboxBaseFolderUser.EmailForDropboxSharing, DropboxBaseFolderUser.IsReadOnly, DropboxBaseFolder.Name, DropboxSetting.AccessToken from DropboxBaseFolderUser LEFT OUTER JOIN DropboxBaseFolder ON DropboxBaseFolderUser.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.SettingGroupPID = DropboxSetting.PID where DropboxBaseFolderUser.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolderUser_DropboxBaseFolderPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->DropboxBaseFolderPID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->EmailForDropboxSharing = $thisline[3];
			$thisresult->IsReadOnly = $thisline[4];
			$thisresult->DropboxBaseFolderName = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDropboxBaseFolderUser($param_DropboxBaseFolderUser_DropboxBaseFolderPID_where, $param_DropboxBaseFolderUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderUser ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderUser ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolderUser.PID, DropboxBaseFolderUser.DropboxBaseFolderPID, DropboxBaseFolderUser.username, DropboxBaseFolderUser.EmailForDropboxSharing, DropboxBaseFolderUser.IsReadOnly, DropboxBaseFolder.Name, DropboxSetting.AccessToken from DropboxBaseFolderUser LEFT OUTER JOIN DropboxBaseFolder ON DropboxBaseFolderUser.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.SettingGroupPID = DropboxSetting.PID where DropboxBaseFolderUser.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolderUser_DropboxBaseFolderPID_where) . "' and DropboxBaseFolderUser.username = '" . $mtooldb->real_escape_string($param_DropboxBaseFolderUser_username_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderUserData();
			$thisresult->PID = $thisline[0];
			$thisresult->DropboxBaseFolderPID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->EmailForDropboxSharing = $thisline[3];
			$thisresult->IsReadOnly = $thisline[4];
			$thisresult->DropboxBaseFolderName = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDropboxBaseFolderUser($DropboxBaseFolderUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDropboxBaseFolderUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDropboxBaseFolderUser ==
		
		$last_sql_command_for_mtooldb = "insert into DropboxBaseFolderUser (DropboxBaseFolderPID, username, EmailForDropboxSharing, IsReadOnly) values('" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->username) . "', '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->EmailForDropboxSharing) . "', '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->IsReadOnly) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDropboxBaseFolderUser($DropboxBaseFolderUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolderUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolderUser ==
		
		$last_sql_command_for_mtooldb = "update DropboxBaseFolderUser SET DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->DropboxBaseFolderPID) . "', username = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->username) . "', EmailForDropboxSharing = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->EmailForDropboxSharing) . "', IsReadOnly = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->IsReadOnly) . "' where DropboxBaseFolderUser.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->DropboxBaseFolderPID) . "' and DropboxBaseFolderUser.username = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDropboxBaseFolderUser($DropboxBaseFolderUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDropboxBaseFolderUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDropboxBaseFolderUser ==
		
		$last_sql_command_for_mtooldb = "delete from DropboxBaseFolderUser where DropboxBaseFolderUser.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->DropboxBaseFolderPID) . "' and DropboxBaseFolderUser.username = '" . $mtooldb->real_escape_string($DropboxBaseFolderUserObj->username) . "'";
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