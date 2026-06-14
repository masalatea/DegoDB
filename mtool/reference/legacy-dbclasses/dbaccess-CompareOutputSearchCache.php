<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class CompareOutputSearchCacheDBAccess
{
	public function __construct() {
	}
	
	public function GetCompareOutputSearchCacheList($param_CompareOutputSearchCache_CompareOutputPID_where, $param_CompareOutputSearchCache_ProjectPID_where, $param_CompareOutputSearchCache_DropboxBaseFolderPID_where, $param_CompareOutputSearchCache_SearchKey_DropboxAccessToken_where, $param_CompareOutputSearchCache_SearchKey_DropboxBaseFolderPath_where, $param_CompareOutputSearchCache_SearchKey_DropboxFullPath_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCompareOutputSearchCacheList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCompareOutputSearchCacheList ==
		
		$last_sql_command_for_mtooldb = "select CompareOutputSearchCache.PID, CompareOutputSearchCache.CompareOutputPID, CompareOutputSearchCache.ProjectPID, CompareOutputSearchCache.DropboxBaseFolderPID, CompareOutputSearchCache.SearchKey_DropboxAccessToken, CompareOutputSearchCache.SearchKey_DropboxBaseFolderPath, CompareOutputSearchCache.SearchKey_DropboxFullPath, CompareOutputSearchCache.Result_MetaData, CompareOutputSearchCache.Result_DropboxBaseFolder from CompareOutputSearchCache where CompareOutputSearchCache.CompareOutputPID = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCache_CompareOutputPID_where) . "' and CompareOutputSearchCache.ProjectPID = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCache_ProjectPID_where) . "' and CompareOutputSearchCache.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCache_DropboxBaseFolderPID_where) . "' and CompareOutputSearchCache.SearchKey_DropboxAccessToken = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCache_SearchKey_DropboxAccessToken_where) . "' and CompareOutputSearchCache.SearchKey_DropboxBaseFolderPath = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCache_SearchKey_DropboxBaseFolderPath_where) . "' and CompareOutputSearchCache.SearchKey_DropboxFullPath = '" . $mtooldb->real_escape_string($param_CompareOutputSearchCache_SearchKey_DropboxFullPath_where) . "' order by CompareOutputSearchCache.SearchKey_DropboxAccessToken,CompareOutputSearchCache.SearchKey_DropboxBaseFolderPath,CompareOutputSearchCache.SearchKey_DropboxFullPath,CompareOutputSearchCache.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new CompareOutputSearchCacheData();
			$thisresult->PID = $thisline[0];
			$thisresult->CompareOutputPID = $thisline[1];
			$thisresult->ProjectPID = $thisline[2];
			$thisresult->DropboxBaseFolderPID = $thisline[3];
			$thisresult->SearchKey_DropboxAccessToken = $thisline[4];
			$thisresult->SearchKey_DropboxBaseFolderPath = $thisline[5];
			$thisresult->SearchKey_DropboxFullPath = $thisline[6];
			$thisresult->Result_MetaData = $thisline[7];
			$thisresult->Result_DropboxBaseFolder = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertCompareOutputSearchCache($CompareOutputSearchCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertCompareOutputSearchCache ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertCompareOutputSearchCache ==
		
		$last_sql_command_for_mtooldb = "insert into CompareOutputSearchCache (CompareOutputPID, ProjectPID, DropboxBaseFolderPID, SearchKey_DropboxAccessToken, SearchKey_DropboxBaseFolderPath, SearchKey_DropboxFullPath, Result_MetaData, Result_DropboxBaseFolder) values('" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->CompareOutputPID) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->SearchKey_DropboxAccessToken) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->SearchKey_DropboxBaseFolderPath) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->SearchKey_DropboxFullPath) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->Result_MetaData) . "', '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->Result_DropboxBaseFolder) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteCompareOutputSearchCache($CompareOutputSearchCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteCompareOutputSearchCache ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteCompareOutputSearchCache ==
		
		$last_sql_command_for_mtooldb = "delete from CompareOutputSearchCache where CompareOutputSearchCache.CompareOutputPID = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->CompareOutputPID) . "' and CompareOutputSearchCache.ProjectPID = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->ProjectPID) . "' and CompareOutputSearchCache.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->DropboxBaseFolderPID) . "' and CompareOutputSearchCache.SearchKey_DropboxAccessToken = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->SearchKey_DropboxAccessToken) . "' and CompareOutputSearchCache.SearchKey_DropboxBaseFolderPath = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->SearchKey_DropboxBaseFolderPath) . "' and CompareOutputSearchCache.SearchKey_DropboxFullPath = '" . $mtooldb->real_escape_string($CompareOutputSearchCacheObj->SearchKey_DropboxFullPath) . "'";
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