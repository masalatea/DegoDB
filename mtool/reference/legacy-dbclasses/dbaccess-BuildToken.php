<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildTokenDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildToken($param_BuildToken_Token_where, $param_BuildToken_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildToken ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildToken ==
		
		$last_sql_command_for_mtooldb = "select BuildToken.PID, BuildToken.Token, BuildToken.ProjectPID, BuildToken.IsOutputToTempFolder, BuildToken.IsOutputAfterCopyToTempFolder, BuildToken.IsQuickBuild, BuildToken.IsOutputDebugMessage, BuildToken.CreatedDateTime from BuildToken where BuildToken.Token = '" . $mtooldb->real_escape_string($param_BuildToken_Token_where) . "' and BuildToken.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildToken_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildTokenData();
			$thisresult->PID = $thisline[0];
			$thisresult->Token = $thisline[1];
			$thisresult->ProjectPID = $thisline[2];
			$thisresult->IsOutputToTempFolder = $thisline[3];
			$thisresult->IsOutputAfterCopyToTempFolder = $thisline[4];
			$thisresult->IsQuickBuild = $thisline[5];
			$thisresult->IsOutputDebugMessage = $thisline[6];
			$thisresult->CreatedDateTime = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertBuildToken($BuildTokenObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildToken ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildToken ==
		
		$last_sql_command_for_mtooldb = "insert into BuildToken (Token, ProjectPID, IsOutputToTempFolder, IsOutputAfterCopyToTempFolder, IsQuickBuild, IsOutputDebugMessage) values('" . $mtooldb->real_escape_string($BuildTokenObj->Token) . "', '" . $mtooldb->real_escape_string($BuildTokenObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($BuildTokenObj->IsOutputToTempFolder) . "', '" . $mtooldb->real_escape_string($BuildTokenObj->IsOutputAfterCopyToTempFolder) . "', '" . $mtooldb->real_escape_string($BuildTokenObj->IsQuickBuild) . "', '" . $mtooldb->real_escape_string($BuildTokenObj->IsOutputDebugMessage) . "')";
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
		
		$last_sql_command_for_mtooldb = "delete from BuildToken where BuildToken.CreatedDateTime < (now() - interval 3 day)";
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