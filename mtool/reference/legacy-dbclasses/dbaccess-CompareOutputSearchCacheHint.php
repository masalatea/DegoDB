<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class CompareOutputSearchCacheHintDBAccess
{
	public function __construct() {
	}
	
	public function GetCompareOutputSearchCacheHint($param_CompareOutputSearchCacheHint_DropboxSettingPID_where, $param_CompareOutputSearchCacheHint_UpdatedFileWhenUpload_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCompareOutputSearchCacheHint ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCompareOutputSearchCacheHint ==
		
		$last_sql_command_for_mtooldb = "select CompareOutputSearchCacheHint.PID, CompareOutputSearchCacheHint.DropboxSettingPID, CompareOutputSearchCacheHint.UpdatedFileWhenUpload, CompareOutputSearchCacheHint.UpdatedDT from CompareOutputSearchCacheHint where CompareOutputSearchCacheHint.DropboxSettingPID = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCacheHint_DropboxSettingPID_where) . "' and CompareOutputSearchCacheHint.UpdatedFileWhenUpload = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCacheHint_UpdatedFileWhenUpload_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new CompareOutputSearchCacheHintData();
			$thisresult->PID = $thisline[0];
			$thisresult->DropboxSettingPID = $thisline[1];
			$thisresult->UpdatedFileWhenUpload = $thisline[2];
			$thisresult->UpdatedDT = $thisline[3];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertCompareOutputSearchCacheHint($CompareOutputSearchCacheHintObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertCompareOutputSearchCacheHint ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertCompareOutputSearchCacheHint ==
		
		$last_sql_command_for_mtooldb = "insert into CompareOutputSearchCacheHint (DropboxSettingPID, UpdatedFileWhenUpload) values('" . $mtooldb->real_escape_string($CompareOutputSearchCacheHintObj->DropboxSettingPID) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheHintObj->UpdatedFileWhenUpload) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteCompareOutputSearchCacheHint($CompareOutputSearchCacheHintObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteCompareOutputSearchCacheHint ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteCompareOutputSearchCacheHint ==
		
		$last_sql_command_for_mtooldb = "delete from CompareOutputSearchCacheHint where CompareOutputSearchCacheHint.DropboxSettingPID = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheHintObj->DropboxSettingPID) . "' and CompareOutputSearchCacheHint.UpdatedFileWhenUpload = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheHintObj->UpdatedFileWhenUpload) . "'";
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