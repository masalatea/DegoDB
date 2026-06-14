<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadDropboxPathCacheItemsDBAccess
{
	public function __construct() {
	}
	
	public function GetUploadDropboxPathCacheItemsList($param_UploadDropboxPathCache_DropboxAccessToken_where, $param_UploadDropboxPathCache_DropboxBaseFolderPID_where, $param_UploadDropboxPathCache_DropboxPath_where, $param_UploadDropboxPathCache_LocalPath_where, $param_UploadDropboxPathCache_ShowDeepCountLimit_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetUploadDropboxPathCacheItemsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetUploadDropboxPathCacheItemsList ==
		
		$last_sql_command_for_mtooldb = "select UploadDropboxPathCacheItems.PID, UploadDropboxPathCacheItems.UploadDropboxPathCachePID, UploadDropboxPathCacheItems.DropboxPath, UploadDropboxPathCacheItems.LocalPath from UploadDropboxPathCacheItems join UploadDropboxPathCache where UploadDropboxPathCache.DropboxAccessToken = '" . $mtooldb->real_escape_string($param_UploadDropboxPathCache_DropboxAccessToken_where) . "' and UploadDropboxPathCache.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($param_UploadDropboxPathCache_DropboxBaseFolderPID_where) . "' and UploadDropboxPathCache.DropboxPath = '" . $mtooldb->real_escape_string($param_UploadDropboxPathCache_DropboxPath_where) . "' and UploadDropboxPathCache.LocalPath = '" . $mtooldb->real_escape_string($param_UploadDropboxPathCache_LocalPath_where) . "' and UploadDropboxPathCache.ShowDeepCountLimit = '" . $mtooldb->real_escape_string($param_UploadDropboxPathCache_ShowDeepCountLimit_where) . "' and UploadDropboxPathCache.PID = UploadDropboxPathCacheItems.UploadDropboxPathCachePID order by UploadDropboxPathCacheItems.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new UploadDropboxPathCacheItemsData();
			$thisresult->PID = $thisline[0];
			$thisresult->UploadDropboxPathCachePID = $thisline[1];
			$thisresult->DropboxPath = $thisline[2];
			$thisresult->LocalPath = $thisline[3];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertUploadDropboxPathCacheItems($UploadDropboxPathCacheItemsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadDropboxPathCacheItems ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadDropboxPathCacheItems ==
		
		$last_sql_command_for_mtooldb = "insert into UploadDropboxPathCacheItems (UploadDropboxPathCachePID, DropboxPath, LocalPath) values('" . $mtooldb->real_escape_string($UploadDropboxPathCacheItemsObj->UploadDropboxPathCachePID) . "', '" . $mtooldb->real_escape_string($UploadDropboxPathCacheItemsObj->DropboxPath) . "', '" . $mtooldb->real_escape_string($UploadDropboxPathCacheItemsObj->LocalPath) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadDropboxPathCacheItems()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadDropboxPathCacheItems ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadDropboxPathCacheItems ==
		
		$last_sql_command_for_mtooldb = "delete from UploadDropboxPathCacheItems where UploadDropboxPathCacheItems.UploadDropboxPathCachePID not in (select PID from UploadDropboxPathCache)";
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