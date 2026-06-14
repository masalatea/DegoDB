<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DBBackupDBAccess
{
	public function __construct() {
	}
	
	public function GetDBBackupList($param_DBBackup_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBBackupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBBackupList ==
		
		$last_sql_command_for_mtooldb = "select DBBackup.PID, DBBackup.SettingGroupPID, DBBackup.DropboxBaseFolderPID, DBBackup.ShellOutputFile, DropboxBaseFolder.Name, DropboxSetting.PID from DBBackup join DropboxBaseFolder join DropboxSetting where DBBackup.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBBackup_SettingGroupPID_where) . "' and DBBackup.DropboxBaseFolderPID = DropboxBaseFolder.PID and DBBackup.SettingGroupPID = DropboxBaseFolder.SettingGroupPID and DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID order by DBBackup.SettingGroupPID,DBBackup.DropboxBaseFolderPID,DBBackup.ShellOutputFile,DBBackup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBBackupData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->ShellOutputFile = $thisline[3];
			$thisresult->DropboxBaseFolderName = $thisline[4];
			$thisresult->DropboxSettingPID = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDBBackup($param_DBBackup_PID_where, $param_DBBackup_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDBBackup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDBBackup ==
		
		$last_sql_command_for_mtooldb = "select DBBackup.PID, DBBackup.SettingGroupPID, DBBackup.DropboxBaseFolderPID, DBBackup.ShellOutputFile, DropboxBaseFolder.Name, DropboxSetting.PID from DBBackup join DropboxBaseFolder join DropboxSetting where DBBackup.PID = '" . $mtooldb->real_escape_string($param_DBBackup_PID_where) . "' and DBBackup.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DBBackup_SettingGroupPID_where) . "' and DBBackup.DropboxBaseFolderPID = DropboxBaseFolder.PID and DBBackup.SettingGroupPID = DropboxBaseFolder.SettingGroupPID and DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DBBackupData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->ShellOutputFile = $thisline[3];
			$thisresult->DropboxBaseFolderName = $thisline[4];
			$thisresult->DropboxSettingPID = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDBBackup($DBBackupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDBBackup ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDBBackup ==
		
		$last_sql_command_for_mtooldb = "insert into DBBackup (SettingGroupPID, DropboxBaseFolderPID, ShellOutputFile) values('" . $mtooldb->real_escape_string($DBBackupObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($DBBackupObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($DBBackupObj->ShellOutputFile) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBBackup($DBBackupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBBackup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBBackup ==
		
		$last_sql_command_for_mtooldb = "update DBBackup SET DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($DBBackupObj->DropboxBaseFolderPID) . "', ShellOutputFile = '" . $mtooldb->real_escape_string($DBBackupObj->ShellOutputFile) . "' where DBBackup.PID = '" . $mtooldb->real_escape_string($DBBackupObj->PID) . "' and DBBackup.SettingGroupPID = '" . $mtooldb->real_escape_string($DBBackupObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDBBackup($DBBackupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDBBackup ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDBBackup ==
		
		$last_sql_command_for_mtooldb = "delete from DBBackup where DBBackup.PID = '" . $mtooldb->real_escape_string($DBBackupObj->PID) . "' and DBBackup.SettingGroupPID = '" . $mtooldb->real_escape_string($DBBackupObj->SettingGroupPID) . "'";
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