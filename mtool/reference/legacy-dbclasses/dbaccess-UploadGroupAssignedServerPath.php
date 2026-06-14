<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadGroupAssignedServerPathDBAccess
{
	public function __construct() {
	}
	
	public function GetUploadGroupAssignedServerPathList($param_UploadGroupAssignedServerPath_UploadGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedServerPathList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedServerPathList ==
		
		$last_sql_command_for_mtooldb = "select UploadGroupAssignedServerPath.PID, UploadGroupAssignedServerPath.UploadGroupPID, UploadGroupAssignedServerPath.UploadServerPID, UploadGroupAssignedServerPath.UploadServerPathPID, UploadServer.UploaderURL, UploadServerPath.DropboxPath, UploadServerPath.LocalPath, UploadServerPath.ShowDeepCountLimit, UploadServerPath.CategoryName, Server.LocalServerName, DropboxBaseFolder.Name, DropboxBaseFolder.PID, DropboxSetting.name, DropboxSetting.AccessToken, DropboxSetting.PID from UploadGroupAssignedServerPath join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID join UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadGroupAssignedServerPath.UploadGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedServerPath_UploadGroupPID_where) . "' and UploadGroupAssignedServerPath.UploadServerPID = UploadServer.PID and UploadGroupAssignedServerPath.UploadServerPID = UploadServerPath.UploadServerPID and UploadGroupAssignedServerPath.UploadServerPathPID = UploadServerPath.PID order by UploadGroupAssignedServerPath.ListOrder,Server.LocalServerName,UploadServerPath.DropboxPath,UploadGroupAssignedServerPath.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupAssignedServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadGroupPID = $thisline[1];
			$thisresult->UploadServerPID = $thisline[2];
			$thisresult->UploadServerPathPID = $thisline[3];
			$thisresult->UploadServerUploaderURL = $thisline[4];
			$thisresult->UploadServerPathDropboxPath = $thisline[5];
			$thisresult->UploadServerPathLocalPath = $thisline[6];
			$thisresult->UploadServerPathShowDeepCountLimit = $thisline[7];
			$thisresult->UploadServerPathCategoryName = $thisline[8];
			$thisresult->ServerLocalServerName = $thisline[9];
			$thisresult->DropboxBaseFolderName = $thisline[10];
			$thisresult->DropboxBaseFolderPID = $thisline[11];
			$thisresult->DropboxSettingname = $thisline[12];
			$thisresult->DropboxSettingAccessToken = $thisline[13];
			$thisresult->DropboxSettingPID = $thisline[14];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadGroupAssignedServerPathByUserAndGroupList($param_UploadGroupAssignedUser_username_where, $param_UploadGroupAssignedUser_UploadGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedServerPathByUserAndGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedServerPathByUserAndGroupList ==
		
		$last_sql_command_for_mtooldb = "select distinct UploadGroupAssignedServerPath.PID, UploadGroupAssignedServerPath.UploadGroupPID, UploadGroupAssignedServerPath.UploadServerPID, UploadGroupAssignedServerPath.UploadServerPathPID, UploadServer.UploaderURL, UploadServerPath.DropboxPath, UploadServerPath.LocalPath, UploadServerPath.ShowDeepCountLimit, UploadServerPath.CategoryName, Server.LocalServerName, DropboxBaseFolder.Name, DropboxBaseFolder.PID, DropboxSetting.name, DropboxSetting.AccessToken, DropboxSetting.PID from UploadGroupAssignedServerPath join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID join UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID join UploadGroupAssignedUser join UploadGroup LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadGroupAssignedUser.username = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedUser_username_where) . "' and UploadGroupAssignedUser.UploadGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedUser_UploadGroupPID_where) . "' and UploadGroupAssignedUser.UploadGroupPID = UploadGroup.PID and UploadGroup.PID = UploadGroupAssignedServerPath.UploadGroupPID and UploadGroupAssignedServerPath.UploadServerPID = UploadServer.PID and UploadGroupAssignedServerPath.UploadServerPID = UploadServerPath.UploadServerPID and UploadGroupAssignedServerPath.UploadServerPathPID = UploadServerPath.PID order by UploadGroupAssignedServerPath.ListOrder,Server.LocalServerName,UploadServerPath.DropboxPath,UploadGroupAssignedServerPath.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupAssignedServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadGroupPID = $thisline[1];
			$thisresult->UploadServerPID = $thisline[2];
			$thisresult->UploadServerPathPID = $thisline[3];
			$thisresult->UploadServerUploaderURL = $thisline[4];
			$thisresult->UploadServerPathDropboxPath = $thisline[5];
			$thisresult->UploadServerPathLocalPath = $thisline[6];
			$thisresult->UploadServerPathShowDeepCountLimit = $thisline[7];
			$thisresult->UploadServerPathCategoryName = $thisline[8];
			$thisresult->ServerLocalServerName = $thisline[9];
			$thisresult->DropboxBaseFolderName = $thisline[10];
			$thisresult->DropboxBaseFolderPID = $thisline[11];
			$thisresult->DropboxSettingname = $thisline[12];
			$thisresult->DropboxSettingAccessToken = $thisline[13];
			$thisresult->DropboxSettingPID = $thisline[14];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadGroupAssignedServerPath($param_UploadGroupAssignedServerPath_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadGroupAssignedServerPath ==
		
		$last_sql_command_for_mtooldb = "select UploadGroupAssignedServerPath.PID, UploadGroupAssignedServerPath.UploadGroupPID, UploadGroupAssignedServerPath.UploadServerPID, UploadGroupAssignedServerPath.UploadServerPathPID, UploadServer.UploaderURL, Server.LocalServerName, DropboxBaseFolder.Name, DropboxBaseFolder.PID, DropboxSetting.name, DropboxSetting.AccessToken, DropboxSetting.PID from UploadGroupAssignedServerPath join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID join UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadGroupAssignedServerPath.PID = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedServerPath_PID_where) . "' and UploadGroupAssignedServerPath.UploadServerPID = UploadServer.PID and UploadGroupAssignedServerPath.UploadServerPID = UploadServerPath.UploadServerPID and UploadGroupAssignedServerPath.UploadServerPathPID = UploadServerPath.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadGroupAssignedServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadGroupPID = $thisline[1];
			$thisresult->UploadServerPID = $thisline[2];
			$thisresult->UploadServerPathPID = $thisline[3];
			$thisresult->UploadServerUploaderURL = $thisline[4];
			$thisresult->ServerLocalServerName = $thisline[5];
			$thisresult->DropboxBaseFolderName = $thisline[6];
			$thisresult->DropboxBaseFolderPID = $thisline[7];
			$thisresult->DropboxSettingname = $thisline[8];
			$thisresult->DropboxSettingAccessToken = $thisline[9];
			$thisresult->DropboxSettingPID = $thisline[10];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertUploadGroupAssignedServerPath($UploadGroupAssignedServerPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadGroupAssignedServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadGroupAssignedServerPath ==
		
		$last_sql_command_for_mtooldb = "insert into UploadGroupAssignedServerPath (UploadGroupPID, UploadServerPID, UploadServerPathPID) values('" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->UploadGroupPID) . "', '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->UploadServerPID) . "', '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->UploadServerPathPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroupAssignedServerPath($UploadGroupAssignedServerPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPath ==
		
		$last_sql_command_for_mtooldb = "update UploadGroupAssignedServerPath SET UploadGroupPID = '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->UploadGroupPID) . "', UploadServerPID = '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->UploadServerPID) . "', UploadServerPathPID = '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->UploadServerPathPID) . "' where UploadGroupAssignedServerPath.PID = '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroupAssignedServerPathOrder($param_UploadGroupAssignedServerPath_ListOrder_update, $param_UploadGroupAssignedServerPath_PID_where, $param_UploadGroupAssignedServerPath_UploadGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPathOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPathOrder ==
		
		$last_sql_command_for_mtooldb = "update UploadGroupAssignedServerPath SET ListOrder = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedServerPath_ListOrder_update) . "' where UploadGroupAssignedServerPath.PID = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedServerPath_PID_where) . "' and UploadGroupAssignedServerPath.UploadGroupPID = '" . $mtooldb->real_escape_string($param_UploadGroupAssignedServerPath_UploadGroupPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadGroupAssignedServerPath($UploadGroupAssignedServerPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadGroupAssignedServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadGroupAssignedServerPath ==
		
		$last_sql_command_for_mtooldb = "delete from UploadGroupAssignedServerPath where UploadGroupAssignedServerPath.PID = '" . $mtooldb->real_escape_string($UploadGroupAssignedServerPathObj->PID) . "'";
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