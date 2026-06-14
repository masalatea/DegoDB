<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DropboxUploadTokenDBAccess
{
	public function __construct() {
	}
	
	public function GetDropboxUploadToken($param_DropboxUploadToken_token_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxUploadToken ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxUploadToken ==
		
		$last_sql_command_for_mtooldb = "select DropboxUploadToken.PID, DropboxUploadToken.token, DropboxUploadToken.DropboxBaseFolderPID, DropboxUploadToken.DropBoxPath, DropboxUploadToken.TargetLocalPath, DropboxUploadToken.MostDeepest, DropboxUploadToken.CreatedDateTime, DropboxUploadToken.IsNeedToCheckUserSecurity from DropboxUploadToken where DropboxUploadToken.token = '" . $mtooldb->real_escape_string($param_DropboxUploadToken_token_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxUploadTokenData();
			$thisresult->PID = $thisline[0];
			$thisresult->token = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->DropBoxPath = $thisline[3];
			$thisresult->TargetLocalPath = $thisline[4];
			$thisresult->MostDeepest = $thisline[5];
			$thisresult->CreatedDateTime = $thisline[6];
			$thisresult->IsNeedToCheckUserSecurity = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDropboxUploadToken($DropboxUploadTokenObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDropboxUploadToken ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDropboxUploadToken ==
		
		$last_sql_command_for_mtooldb = "insert into DropboxUploadToken (token, DropboxBaseFolderPID, DropBoxPath, TargetLocalPath, MostDeepest, IsNeedToCheckUserSecurity) values('" . $mtooldb->real_escape_string($DropboxUploadTokenObj->token) . "', '" . $mtooldb->real_escape_string($DropboxUploadTokenObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($DropboxUploadTokenObj->DropBoxPath) . "', '" . $mtooldb->real_escape_string($DropboxUploadTokenObj->TargetLocalPath) . "', '" . $mtooldb->real_escape_string($DropboxUploadTokenObj->MostDeepest) . "', '" . $mtooldb->real_escape_string($DropboxUploadTokenObj->IsNeedToCheckUserSecurity) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteOldToken()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteOldToken ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteOldToken ==
		
		$last_sql_command_for_mtooldb = "delete from DropboxUploadToken where DropboxUploadToken.CreatedDateTime < (now() - interval 3 day)";
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