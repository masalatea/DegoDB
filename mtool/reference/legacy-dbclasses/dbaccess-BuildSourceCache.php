<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildSourceCacheDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildSourceCacheByDataClassList($param_BuildSourceCache_ProjectPID_where, $param_BuildSourceCache_dataclassPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildSourceCacheByDataClassList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildSourceCacheByDataClassList ==
		
		$last_sql_command_for_mtooldb = "select BuildSourceCache.ProjectPID, BuildSourceCache.PID, BuildSourceCache.SourceType, BuildSourceCache.CreatedDateTime, BuildSourceCache.dataclassPID, BuildSourceCache.daPID, BuildSourceCache.Filename, BuildSourceCache.SourceCode from BuildSourceCache where BuildSourceCache.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildSourceCache_ProjectPID_where) . "' and BuildSourceCache.dataclassPID = '" . $mtooldb->real_escape_string($param_BuildSourceCache_dataclassPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildSourceCacheData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->SourceType = $thisline[2];
			$thisresult->CreatedDateTime = $thisline[3];
			$thisresult->dataclassPID = $thisline[4];
			$thisresult->daPID = $thisline[5];
			$thisresult->Filename = $thisline[6];
			$thisresult->SourceCode = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetBuildSourceCacheByDAList($param_BuildSourceCache_ProjectPID_where, $param_BuildSourceCache_daPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildSourceCacheByDAList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildSourceCacheByDAList ==
		
		$last_sql_command_for_mtooldb = "select BuildSourceCache.ProjectPID, BuildSourceCache.PID, BuildSourceCache.SourceType, BuildSourceCache.CreatedDateTime, BuildSourceCache.dataclassPID, BuildSourceCache.daPID, BuildSourceCache.Filename, BuildSourceCache.SourceCode from BuildSourceCache where BuildSourceCache.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildSourceCache_ProjectPID_where) . "' and BuildSourceCache.daPID = '" . $mtooldb->real_escape_string($param_BuildSourceCache_daPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildSourceCacheData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->SourceType = $thisline[2];
			$thisresult->CreatedDateTime = $thisline[3];
			$thisresult->dataclassPID = $thisline[4];
			$thisresult->daPID = $thisline[5];
			$thisresult->Filename = $thisline[6];
			$thisresult->SourceCode = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetBuildSourceCache($param_BuildSourceCache_PID_where, $param_BuildSourceCache_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildSourceCache ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildSourceCache ==
		
		$last_sql_command_for_mtooldb = "select BuildSourceCache.ProjectPID, BuildSourceCache.PID, BuildSourceCache.SourceType, BuildSourceCache.CreatedDateTime, BuildSourceCache.dataclassPID, BuildSourceCache.daPID, BuildSourceCache.Filename, BuildSourceCache.SourceCode from BuildSourceCache where BuildSourceCache.PID = '" . $mtooldb->real_escape_string($param_BuildSourceCache_PID_where) . "' and BuildSourceCache.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildSourceCache_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildSourceCacheData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->SourceType = $thisline[2];
			$thisresult->CreatedDateTime = $thisline[3];
			$thisresult->dataclassPID = $thisline[4];
			$thisresult->daPID = $thisline[5];
			$thisresult->Filename = $thisline[6];
			$thisresult->SourceCode = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertBuildSourceCache($BuildSourceCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildSourceCache ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildSourceCache ==
		
		$last_sql_command_for_mtooldb = "insert into BuildSourceCache (ProjectPID, SourceType, dataclassPID, daPID, Filename, SourceCode) values('" . $mtooldb->real_escape_string($BuildSourceCacheObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($BuildSourceCacheObj->SourceType) . "', '" . $mtooldb->real_escape_string($BuildSourceCacheObj->dataclassPID) . "', '" . $mtooldb->real_escape_string($BuildSourceCacheObj->daPID) . "', '" . $mtooldb->real_escape_string($BuildSourceCacheObj->Filename) . "', '" . $mtooldb->real_escape_string($BuildSourceCacheObj->SourceCode) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildSourceCacheByDataClass($BuildSourceCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceCacheByDataClass ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceCacheByDataClass ==
		
		$last_sql_command_for_mtooldb = "delete from BuildSourceCache where BuildSourceCache.ProjectPID = '" . $mtooldb->real_escape_string($BuildSourceCacheObj->ProjectPID) . "' and BuildSourceCache.dataclassPID = '" . $mtooldb->real_escape_string($BuildSourceCacheObj->dataclassPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildSourceCacheByDA($BuildSourceCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceCacheByDA ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceCacheByDA ==
		
		$last_sql_command_for_mtooldb = "delete from BuildSourceCache where BuildSourceCache.ProjectPID = '" . $mtooldb->real_escape_string($BuildSourceCacheObj->ProjectPID) . "' and BuildSourceCache.daPID = '" . $mtooldb->real_escape_string($BuildSourceCacheObj->daPID) . "'";
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