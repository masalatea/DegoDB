<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadServerDBAccess
{
	public function __construct() {
	}
	
	public function GetUploadServerList($param_UploadServer_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadServerList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadServerList ==
		
		$last_sql_command_for_mtooldb = "select UploadServer.PID, UploadServer.SettingGroupPID, UploadServer.ServerPID, UploadServer.UploaderURL, Server.LocalServerName from UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID where UploadServer.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadServer_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->UploaderURL = $thisline[3];
			$thisresult->ServerLocalServerName = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetUploadServer($param_UploadServer_PID_where, $param_UploadServer_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadServer ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadServer ==
		
		$last_sql_command_for_mtooldb = "select UploadServer.PID, UploadServer.SettingGroupPID, UploadServer.ServerPID, UploadServer.UploaderURL, Server.LocalServerName from UploadServer LEFT OUTER JOIN Server ON UploadServer.ServerPID = Server.PID where UploadServer.PID = '" . $mtooldb->real_escape_string($param_UploadServer_PID_where) . "' and UploadServer.SettingGroupPID = '" . $mtooldb->real_escape_string($param_UploadServer_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->ServerPID = $thisline[2];
			$thisresult->UploaderURL = $thisline[3];
			$thisresult->ServerLocalServerName = $thisline[4];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertUploadServer($UploadServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadServer ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadServer ==
		
		$last_sql_command_for_mtooldb = "insert into UploadServer (SettingGroupPID, ServerPID, UploaderURL) values('" . $mtooldb->real_escape_string($UploadServerObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($UploadServerObj->ServerPID) . "', '" . $mtooldb->real_escape_string($UploadServerObj->UploaderURL) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadServer($UploadServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadServer ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadServer ==
		
		$last_sql_command_for_mtooldb = "update UploadServer SET SettingGroupPID = '" . $mtooldb->real_escape_string($UploadServerObj->SettingGroupPID) . "', ServerPID = '" . $mtooldb->real_escape_string($UploadServerObj->ServerPID) . "', UploaderURL = '" . $mtooldb->real_escape_string($UploadServerObj->UploaderURL) . "' where UploadServer.PID = '" . $mtooldb->real_escape_string($UploadServerObj->PID) . "' and UploadServer.SettingGroupPID = '" . $mtooldb->real_escape_string($UploadServerObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadServer($UploadServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadServer ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadServer ==
		
		$last_sql_command_for_mtooldb = "delete from UploadServer where UploadServer.PID = '" . $mtooldb->real_escape_string($UploadServerObj->PID) . "' and UploadServer.SettingGroupPID = '" . $mtooldb->real_escape_string($UploadServerObj->SettingGroupPID) . "'";
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