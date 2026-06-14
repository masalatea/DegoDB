<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DropboxOauth2StatusHashDBAccess
{
	public function __construct() {
	}
	
	public function GetByHashKey($param_DropboxOauth2StatusHash_HashKey_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetByHashKey ==
		// == END OF EDITABLE AREA FOR FUNCTION GetByHashKey ==
		
		$last_sql_command_for_mtooldb = "select DropboxOauth2StatusHash.PID, DropboxOauth2StatusHash.HashKey, DropboxOauth2StatusHash.AddedTime, DropboxOauth2StatusHash.TargetDropboxSettingPID from DropboxOauth2StatusHash where DropboxOauth2StatusHash.HashKey = '" . $mtooldb->real_escape_string($param_DropboxOauth2StatusHash_HashKey_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxOauth2StatusHashData();
			$thisresult->PID = $thisline[0];
			$thisresult->HashKey = $thisline[1];
			$thisresult->AddedTime = $thisline[2];
			$thisresult->TargetDropboxSettingPID = $thisline[3];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDropboxOauth2StatusHash($DropboxOauth2StatusHashObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDropboxOauth2StatusHash ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDropboxOauth2StatusHash ==
		
		$last_sql_command_for_mtooldb = "insert into DropboxOauth2StatusHash (HashKey, TargetDropboxSettingPID) values('" . $mtooldb->real_escape_string($DropboxOauth2StatusHashObj->HashKey) . "', '" . $mtooldb->real_escape_string($DropboxOauth2StatusHashObj->TargetDropboxSettingPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteOldHash($DropboxOauth2StatusHashObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteOldHash ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteOldHash ==
		
		$last_sql_command_for_mtooldb = "delete from DropboxOauth2StatusHash where DropboxOauth2StatusHash.AddedTime <= (now() - interval 90 minute)";
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