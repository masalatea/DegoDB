<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ApacheSettingDBAccess
{
	public function __construct() {
	}
	
	public function GetApacheSettingList($param_ApacheSetting_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetApacheSettingList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetApacheSettingList ==
		
		$last_sql_command_for_mtooldb = "select ApacheSetting.PID, ApacheSetting.SettingGroupPID, ApacheSetting.name, ApacheSetting.ServerPID, ApacheSetting.DropboxBaseFolderPID, ApacheSetting.CreateTargetDir, Server.LocalServerName, DropboxBaseFolder.Name, DropboxSetting.PID from ApacheSetting LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID LEFT OUTER JOIN DropboxBaseFolder ON ApacheSetting.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where ApacheSetting.SettingGroupPID = '" . $mtooldb->real_escape_string($param_ApacheSetting_SettingGroupPID_where) . "' order by ApacheSetting.name,ApacheSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->ServerPID = $thisline[3];
			$thisresult->DropboxBaseFolderPID = $thisline[4];
			$thisresult->CreateTargetDir = $thisline[5];
			$thisresult->ServerLocalServerName = $thisline[6];
			$thisresult->DropboxBaseFolderName = $thisline[7];
			$thisresult->DropboxSettingPID = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetApacheSetting($param_ApacheSetting_PID_where, $param_ApacheSetting_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetApacheSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION GetApacheSetting ==
		
		$last_sql_command_for_mtooldb = "select ApacheSetting.PID, ApacheSetting.SettingGroupPID, ApacheSetting.name, ApacheSetting.ServerPID, ApacheSetting.DropboxBaseFolderPID, ApacheSetting.CreateTargetDir, Server.LocalServerName, DropboxBaseFolder.Name, DropboxSetting.PID from ApacheSetting LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID LEFT OUTER JOIN DropboxBaseFolder ON ApacheSetting.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where ApacheSetting.PID = '" . $mtooldb->real_escape_string($param_ApacheSetting_PID_where) . "' and ApacheSetting.SettingGroupPID = '" . $mtooldb->real_escape_string($param_ApacheSetting_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->ServerPID = $thisline[3];
			$thisresult->DropboxBaseFolderPID = $thisline[4];
			$thisresult->CreateTargetDir = $thisline[5];
			$thisresult->ServerLocalServerName = $thisline[6];
			$thisresult->DropboxBaseFolderName = $thisline[7];
			$thisresult->DropboxSettingPID = $thisline[8];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertApacheSetting($ApacheSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertApacheSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertApacheSetting ==
		
		$last_sql_command_for_mtooldb = "insert into ApacheSetting (SettingGroupPID, name, ServerPID, DropboxBaseFolderPID, CreateTargetDir) values('" . $mtooldb->real_escape_string($ApacheSettingObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($ApacheSettingObj->name) . "', '" . $mtooldb->real_escape_string($ApacheSettingObj->ServerPID) . "', '" . $mtooldb->real_escape_string($ApacheSettingObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($ApacheSettingObj->CreateTargetDir) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateApacheSetting($ApacheSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateApacheSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateApacheSetting ==
		
		$last_sql_command_for_mtooldb = "update ApacheSetting SET name = '" . $mtooldb->real_escape_string($ApacheSettingObj->name) . "', ServerPID = '" . $mtooldb->real_escape_string($ApacheSettingObj->ServerPID) . "', DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($ApacheSettingObj->DropboxBaseFolderPID) . "', CreateTargetDir = '" . $mtooldb->real_escape_string($ApacheSettingObj->CreateTargetDir) . "' where ApacheSetting.PID = '" . $mtooldb->real_escape_string($ApacheSettingObj->PID) . "' and ApacheSetting.SettingGroupPID = '" . $mtooldb->real_escape_string($ApacheSettingObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteApacheSetting($ApacheSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteApacheSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteApacheSetting ==
		
		$last_sql_command_for_mtooldb = "delete from ApacheSetting where ApacheSetting.PID = '" . $mtooldb->real_escape_string($ApacheSettingObj->PID) . "' and ApacheSetting.SettingGroupPID = '" . $mtooldb->real_escape_string($ApacheSettingObj->SettingGroupPID) . "'";
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