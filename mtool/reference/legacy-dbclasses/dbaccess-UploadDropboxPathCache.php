<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class UploadDropboxPathCacheDBAccess
{
	public function __construct() {
	}
	
	public function InsertUploadDropboxPathCache($UploadDropboxPathCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertUploadDropboxPathCache ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertUploadDropboxPathCache ==
		
		$last_sql_command_for_mtooldb = "insert into UploadDropboxPathCache (DropboxBaseFolderPID, DropboxAccessToken, DropboxPath, LocalPath, ShowDeepCountLimit) values('" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->DropboxAccessToken) . "', '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->DropboxPath) . "', '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->LocalPath) . "', '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->ShowDeepCountLimit) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteOld()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteOld ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteOld ==
		
		$last_sql_command_for_mtooldb = "delete from UploadDropboxPathCache where UploadDropboxPathCache.CreatedTimestamp <= (now() - interval 30 day)";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUploadDropboxPathCache($UploadDropboxPathCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUploadDropboxPathCache ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUploadDropboxPathCache ==
		
		$last_sql_command_for_mtooldb = "delete from UploadDropboxPathCache where UploadDropboxPathCache.DropboxAccessToken = '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->DropboxAccessToken) . "' and UploadDropboxPathCache.DropboxPath = '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->DropboxPath) . "' and UploadDropboxPathCache.LocalPath = '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->LocalPath) . "' and UploadDropboxPathCache.ShowDeepCountLimit = '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->ShowDeepCountLimit) . "' and UploadDropboxPathCache.DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($UploadDropboxPathCacheObj->DropboxBaseFolderPID) . "'";
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