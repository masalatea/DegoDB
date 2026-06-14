<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildTokenTemplateCacheDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildTokenTemplateCache($param_BuildTokenTemplateCache_TemplateKey_where, $param_BuildTokenTemplateCache_ProjectPID_where, $param_BuildTokenTemplateCache_BuildTokenPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildTokenTemplateCache ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildTokenTemplateCache ==
		
		$last_sql_command_for_mtooldb = "select BuildTokenTemplateCache.PID, BuildTokenTemplateCache.ProjectPID, BuildTokenTemplateCache.BuildTokenPID, BuildTokenTemplateCache.TemplateKey, BuildTokenTemplateCache.FileExist, BuildTokenTemplateCache.Source from BuildTokenTemplateCache where BuildTokenTemplateCache.TemplateKey = '" . $mtooldb->real_escape_string($param_BuildTokenTemplateCache_TemplateKey_where) . "' and BuildTokenTemplateCache.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildTokenTemplateCache_ProjectPID_where) . "' and BuildTokenTemplateCache.BuildTokenPID = '" . $mtooldb->real_escape_string($param_BuildTokenTemplateCache_BuildTokenPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildTokenTemplateCacheData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->BuildTokenPID = $thisline[2];
			$thisresult->TemplateKey = $thisline[3];
			$thisresult->FileExist = $thisline[4];
			$thisresult->Source = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertBuildTokenTemplateCache($BuildTokenTemplateCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildTokenTemplateCache ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildTokenTemplateCache ==
		
		$last_sql_command_for_mtooldb = "insert into BuildTokenTemplateCache (ProjectPID, BuildTokenPID, TemplateKey, FileExist, Source) values('" . $mtooldb->real_escape_string($BuildTokenTemplateCacheObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($BuildTokenTemplateCacheObj->BuildTokenPID) . "', '" . $mtooldb->real_escape_string($BuildTokenTemplateCacheObj->TemplateKey) . "', '" . $mtooldb->real_escape_string($BuildTokenTemplateCacheObj->FileExist) . "', '" . $mtooldb->real_escape_string($BuildTokenTemplateCacheObj->Source) . "')";
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
		
		$last_sql_command_for_mtooldb = "delete from BuildTokenTemplateCache where BuildTokenTemplateCache.BuildTokenPID not in (select PID from BuildToken)";
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