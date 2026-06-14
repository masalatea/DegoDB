<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadServerPathDBAccess
{
	public function __construct() {
	}
	
	public function GetUploadServerPathList($param_UploadServerPath_UploadServerPID_where, $param_UploadServer_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadServerPathList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadServerPathList ==
		
		$last_sql_command_for_mtooldb = "select UploadServerPath.PID, UploadServerPath.UploadServerPID, UploadServerPath.DropboxBaseFolderPID, UploadServerPath.CategoryName, UploadServerPath.DropboxPath, UploadServerPath.LocalPath, UploadServerPath.ShowDeepCountLimit, Server.LocalServerName, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.PID from UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadServerPath.UploadServerPID = '" . $mtooldb->real_escape_string($param_UploadServerPath_UploadServerPID_where) . "' and UploadServerPath.UploadServerPID = UploadServer.PID and UploadServer.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadServer_SettingGroupPID_where) . "' order by UploadServerPath.CategoryName,DropboxSetting.name,DropboxBaseFolder.Name,Server.LocalServerName,UploadServerPath.DropboxPath,UploadServerPath.LocalPath";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadServerPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->DropboxPath = $thisline[4];
			$thisresult->LocalPath = $thisline[5];
			$thisresult->ShowDeepCountLimit = $thisline[6];
			$thisresult->ServerLocalServerName = $thisline[7];
			$thisresult->DropboxBaseFolderName = $thisline[8];
			$thisresult->DropboxSettingname = $thisline[9];
			$thisresult->DropboxSettingPID = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetAllUploadServerPathList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetAllUploadServerPathList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetAllUploadServerPathList ==
		
		$last_sql_command_for_mtooldb = "select UploadServerPath.PID, UploadServerPath.UploadServerPID, UploadServerPath.DropboxBaseFolderPID, UploadServerPath.CategoryName, UploadServerPath.DropboxPath, UploadServerPath.LocalPath, UploadServerPath.ShowDeepCountLimit, Server.LocalServerName, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.PID from UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadServerPath.UploadServerPID = UploadServer.PID order by UploadServerPath.CategoryName,DropboxSetting.name,DropboxBaseFolder.Name,Server.LocalServerName,UploadServerPath.DropboxPath,UploadServerPath.LocalPath";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadServerPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->DropboxPath = $thisline[4];
			$thisresult->LocalPath = $thisline[5];
			$thisresult->ShowDeepCountLimit = $thisline[6];
			$thisresult->ServerLocalServerName = $thisline[7];
			$thisresult->DropboxBaseFolderName = $thisline[8];
			$thisresult->DropboxSettingname = $thisline[9];
			$thisresult->DropboxSettingPID = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadServerPathForUserList($param_UploadServer_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadServerPathForUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadServerPathForUserList ==
		
		$last_sql_command_for_mtooldb = "select UploadServerPath.PID, UploadServerPath.UploadServerPID, UploadServerPath.DropboxBaseFolderPID, UploadServerPath.CategoryName, UploadServerPath.DropboxPath, UploadServerPath.LocalPath, UploadServerPath.ShowDeepCountLimit, Server.LocalServerName, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.PID from UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadServerPath.UploadServerPID = UploadServer.PID and UploadServer.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadServer_SettingGroupPID_where) . "' order by UploadServerPath.CategoryName,DropboxSetting.name,DropboxBaseFolder.Name,Server.LocalServerName,UploadServerPath.DropboxPath,UploadServerPath.LocalPath";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadServerPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->DropboxPath = $thisline[4];
			$thisresult->LocalPath = $thisline[5];
			$thisresult->ShowDeepCountLimit = $thisline[6];
			$thisresult->ServerLocalServerName = $thisline[7];
			$thisresult->DropboxBaseFolderName = $thisline[8];
			$thisresult->DropboxSettingname = $thisline[9];
			$thisresult->DropboxSettingPID = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadServerPath($param_UploadServerPath_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadServerPath ==
		
		$last_sql_command_for_mtooldb = "select UploadServerPath.PID, UploadServerPath.UploadServerPID, UploadServerPath.DropboxBaseFolderPID, UploadServerPath.CategoryName, UploadServerPath.DropboxPath, UploadServerPath.LocalPath, UploadServerPath.ShowDeepCountLimit, Server.LocalServerName, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.PID from UploadServerPath LEFT OUTER JOIN DropboxBaseFolder ON UploadServerPath.DropboxBaseFolderPID = DropboxBaseFolder.PID join UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where UploadServerPath.PID = '" . $mtooldb->real_escape_string($param_UploadServerPath_PID_where) . "' and UploadServerPath.UploadServerPID = UploadServer.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadServerPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadServerPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->DropboxPath = $thisline[4];
			$thisresult->LocalPath = $thisline[5];
			$thisresult->ShowDeepCountLimit = $thisline[6];
			$thisresult->ServerLocalServerName = $thisline[7];
			$thisresult->DropboxBaseFolderName = $thisline[8];
			$thisresult->DropboxSettingname = $thisline[9];
			$thisresult->DropboxSettingPID = $thisline[10];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertUploadServerPath($UploadServerPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadServerPath ==
		
		$last_sql_command_for_mtooldb = "insert into UploadServerPath (UploadServerPID, DropboxBaseFolderPID, CategoryName, DropboxPath, LocalPath, ShowDeepCountLimit) values('" . $mtooldb->real_escape_string($UploadServerPathObj->UploadServerPID) . "', '" . $mtooldb->real_escape_string($UploadServerPathObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($UploadServerPathObj->CategoryName) . "', '" . $mtooldb->real_escape_string($UploadServerPathObj->DropboxPath) . "', '" . $mtooldb->real_escape_string($UploadServerPathObj->LocalPath) . "', '" . $mtooldb->real_escape_string($UploadServerPathObj->ShowDeepCountLimit) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadServerPath($UploadServerPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadServerPath ==
		
		$last_sql_command_for_mtooldb = "update UploadServerPath SET UploadServerPID = '" . $mtooldb->real_escape_string($UploadServerPathObj->UploadServerPID) . "', DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($UploadServerPathObj->DropboxBaseFolderPID) . "', CategoryName = '" . $mtooldb->real_escape_string($UploadServerPathObj->CategoryName) . "', DropboxPath = '" . $mtooldb->real_escape_string($UploadServerPathObj->DropboxPath) . "', LocalPath = '" . $mtooldb->real_escape_string($UploadServerPathObj->LocalPath) . "', ShowDeepCountLimit = '" . $mtooldb->real_escape_string($UploadServerPathObj->ShowDeepCountLimit) . "' where UploadServerPath.PID = '" . $mtooldb->real_escape_string($UploadServerPathObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadServerPath($UploadServerPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadServerPath ==
		
		$last_sql_command_for_mtooldb = "delete from UploadServerPath where UploadServerPath.PID = '" . $mtooldb->real_escape_string($UploadServerPathObj->PID) . "'";
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