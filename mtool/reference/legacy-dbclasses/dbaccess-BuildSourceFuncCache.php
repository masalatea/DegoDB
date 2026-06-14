<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildSourceFuncCacheDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildSourceFuncCacheByDAFunc($param_BuildSourceFuncCache_ProjectPID_where, $param_BuildSourceFuncCache_daPID_where, $param_BuildSourceFuncCache_dafuncPID_where, $param_BuildSourceFuncCache_BuildTargetType_where, $param_BuildSourceFuncCache_ReleaseTargetType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildSourceFuncCacheByDAFunc ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildSourceFuncCacheByDAFunc ==
		
		$last_sql_command_for_mtooldb = "select BuildSourceFuncCache.PID, BuildSourceFuncCache.ProjectPID, BuildSourceFuncCache.CreatedDateTime, BuildSourceFuncCache.daPID, BuildSourceFuncCache.dafuncPID, BuildSourceFuncCache.daCustomProxyPID, BuildSourceFuncCache.BuildTargetType, BuildSourceFuncCache.ReleaseTargetType, BuildSourceFuncCache.FunctionName, BuildSourceFuncCache.AutoloadFilename, BuildSourceFuncCache.SourceCode, BuildSourceFuncCache.ParameterListString, BuildSourceFuncCache.ParameterListStringForProxyBasedOnDA, BuildSourceFuncCache.ParameterListStringForProxyBasedOnDAForExample, BuildSourceFuncCache.ExampleCodeForCreatingObject, BuildSourceFuncCache.DataClassName, BuildSourceFuncCache.DAName, BuildSourceFuncCache.DAClassName, BuildSourceFuncCache.ProxyURL, BuildSourceFuncCache.ProxyParameterFormat, BuildSourceFuncCache.ProxyParameterExample, BuildSourceFuncCache.ProxyResultFormat, BuildSourceFuncCache.ProxyResultExample, BuildSourceFuncCache.ProxyParameterForJquery, BuildSourceFuncCache.ProxyParameterExampleForJquery, BuildSourceFuncCache.ProxyParameterExampleForPHP, BuildSourceFuncCache.ProxyParameterExampleForPerl, BuildSourceFuncCache.ProxyParameterExampleForRuby, BuildSourceFuncCache.ProxyResultFormatForJquery from BuildSourceFuncCache where BuildSourceFuncCache.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_ProjectPID_where) . "' and BuildSourceFuncCache.daPID = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_daPID_where) . "' and BuildSourceFuncCache.dafuncPID = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_dafuncPID_where) . "' and BuildSourceFuncCache.BuildTargetType = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_BuildTargetType_where) . "' and BuildSourceFuncCache.ReleaseTargetType = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_ReleaseTargetType_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildSourceFuncCacheData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->CreatedDateTime = $thisline[2];
			$thisresult->daPID = $thisline[3];
			$thisresult->dafuncPID = $thisline[4];
			$thisresult->daCustomProxyPID = $thisline[5];
			$thisresult->BuildTargetType = $thisline[6];
			$thisresult->ReleaseTargetType = $thisline[7];
			$thisresult->FunctionName = $thisline[8];
			$thisresult->AutoloadFilename = $thisline[9];
			$thisresult->SourceCode = $thisline[10];
			$thisresult->ParameterListString = $thisline[11];
			$thisresult->ParameterListStringForProxyBasedOnDA = $thisline[12];
			$thisresult->ParameterListStringForProxyBasedOnDAForExample = $thisline[13];
			$thisresult->ExampleCodeForCreatingObject = $thisline[14];
			$thisresult->DataClassName = $thisline[15];
			$thisresult->DAName = $thisline[16];
			$thisresult->DAClassName = $thisline[17];
			$thisresult->ProxyURL = $thisline[18];
			$thisresult->ProxyParameterFormat = $thisline[19];
			$thisresult->ProxyParameterExample = $thisline[20];
			$thisresult->ProxyResultFormat = $thisline[21];
			$thisresult->ProxyResultExample = $thisline[22];
			$thisresult->ProxyParameterForJquery = $thisline[23];
			$thisresult->ProxyParameterExampleForJquery = $thisline[24];
			$thisresult->ProxyParameterExampleForPHP = $thisline[25];
			$thisresult->ProxyParameterExampleForPerl = $thisline[26];
			$thisresult->ProxyParameterExampleForRuby = $thisline[27];
			$thisresult->ProxyResultFormatForJquery = $thisline[28];
			return $thisresult;
		}
		return NULL;
	}
	public function GetBuildSourceFuncCacheByCustomProxy($param_BuildSourceFuncCache_ProjectPID_where, $param_BuildSourceFuncCache_daCustomProxyPID_where, $param_BuildSourceFuncCache_BuildTargetType_where, $param_BuildSourceFuncCache_ReleaseTargetType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildSourceFuncCacheByCustomProxy ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildSourceFuncCacheByCustomProxy ==
		
		$last_sql_command_for_mtooldb = "select BuildSourceFuncCache.PID, BuildSourceFuncCache.ProjectPID, BuildSourceFuncCache.CreatedDateTime, BuildSourceFuncCache.daPID, BuildSourceFuncCache.dafuncPID, BuildSourceFuncCache.daCustomProxyPID, BuildSourceFuncCache.BuildTargetType, BuildSourceFuncCache.ReleaseTargetType, BuildSourceFuncCache.FunctionName, BuildSourceFuncCache.AutoloadFilename, BuildSourceFuncCache.SourceCode, BuildSourceFuncCache.ParameterListString, BuildSourceFuncCache.ParameterListStringForProxyBasedOnDA, BuildSourceFuncCache.ParameterListStringForProxyBasedOnDAForExample, BuildSourceFuncCache.ExampleCodeForCreatingObject, BuildSourceFuncCache.DataClassName, BuildSourceFuncCache.DAName, BuildSourceFuncCache.DAClassName, BuildSourceFuncCache.ProxyURL, BuildSourceFuncCache.ProxyParameterFormat, BuildSourceFuncCache.ProxyParameterExample, BuildSourceFuncCache.ProxyResultFormat, BuildSourceFuncCache.ProxyResultExample, BuildSourceFuncCache.ProxyParameterForJquery, BuildSourceFuncCache.ProxyParameterExampleForJquery, BuildSourceFuncCache.ProxyParameterExampleForPHP, BuildSourceFuncCache.ProxyParameterExampleForPerl, BuildSourceFuncCache.ProxyParameterExampleForRuby, BuildSourceFuncCache.ProxyResultFormatForJquery from BuildSourceFuncCache where BuildSourceFuncCache.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_ProjectPID_where) . "' and BuildSourceFuncCache.daCustomProxyPID = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_daCustomProxyPID_where) . "' and BuildSourceFuncCache.BuildTargetType = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_BuildTargetType_where) . "' and BuildSourceFuncCache.ReleaseTargetType = '" . $mtooldb->real_escape_string($param_BuildSourceFuncCache_ReleaseTargetType_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildSourceFuncCacheData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->CreatedDateTime = $thisline[2];
			$thisresult->daPID = $thisline[3];
			$thisresult->dafuncPID = $thisline[4];
			$thisresult->daCustomProxyPID = $thisline[5];
			$thisresult->BuildTargetType = $thisline[6];
			$thisresult->ReleaseTargetType = $thisline[7];
			$thisresult->FunctionName = $thisline[8];
			$thisresult->AutoloadFilename = $thisline[9];
			$thisresult->SourceCode = $thisline[10];
			$thisresult->ParameterListString = $thisline[11];
			$thisresult->ParameterListStringForProxyBasedOnDA = $thisline[12];
			$thisresult->ParameterListStringForProxyBasedOnDAForExample = $thisline[13];
			$thisresult->ExampleCodeForCreatingObject = $thisline[14];
			$thisresult->DataClassName = $thisline[15];
			$thisresult->DAName = $thisline[16];
			$thisresult->DAClassName = $thisline[17];
			$thisresult->ProxyURL = $thisline[18];
			$thisresult->ProxyParameterFormat = $thisline[19];
			$thisresult->ProxyParameterExample = $thisline[20];
			$thisresult->ProxyResultFormat = $thisline[21];
			$thisresult->ProxyResultExample = $thisline[22];
			$thisresult->ProxyParameterForJquery = $thisline[23];
			$thisresult->ProxyParameterExampleForJquery = $thisline[24];
			$thisresult->ProxyParameterExampleForPHP = $thisline[25];
			$thisresult->ProxyParameterExampleForPerl = $thisline[26];
			$thisresult->ProxyParameterExampleForRuby = $thisline[27];
			$thisresult->ProxyResultFormatForJquery = $thisline[28];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertBuildSourceFuncCache($BuildSourceFuncCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildSourceFuncCache ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildSourceFuncCache ==
		
		$last_sql_command_for_mtooldb = "insert into BuildSourceFuncCache (ProjectPID, daPID, dafuncPID, daCustomProxyPID, BuildTargetType, ReleaseTargetType, FunctionName, AutoloadFilename, SourceCode, ParameterListString, ParameterListStringForProxyBasedOnDA, ParameterListStringForProxyBasedOnDAForExample, ExampleCodeForCreatingObject, DataClassName, DAName, DAClassName, ProxyURL, ProxyParameterFormat, ProxyParameterExample, ProxyResultFormat, ProxyResultExample, ProxyParameterForJquery, ProxyParameterExampleForJquery, ProxyParameterExampleForPHP, ProxyParameterExampleForPerl, ProxyParameterExampleForRuby, ProxyResultFormatForJquery) values('" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->daPID) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->daCustomProxyPID) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->BuildTargetType) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ReleaseTargetType) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->FunctionName) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->AutoloadFilename) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->SourceCode) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ParameterListString) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ParameterListStringForProxyBasedOnDA) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ParameterListStringForProxyBasedOnDAForExample) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ExampleCodeForCreatingObject) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->DataClassName) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->DAName) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->DAClassName) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyURL) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterFormat) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterExample) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyResultFormat) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyResultExample) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterForJquery) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterExampleForJquery) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterExampleForPHP) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterExampleForPerl) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyParameterExampleForRuby) . "', '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProxyResultFormatForJquery) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildSourceFuncCacheByDAFunc($BuildSourceFuncCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceFuncCacheByDAFunc ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceFuncCacheByDAFunc ==
		
		$last_sql_command_for_mtooldb = "delete from BuildSourceFuncCache where BuildSourceFuncCache.ProjectPID = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProjectPID) . "' and BuildSourceFuncCache.daPID = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->daPID) . "' and BuildSourceFuncCache.dafuncPID = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->dafuncPID) . "' and BuildSourceFuncCache.BuildTargetType = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->BuildTargetType) . "' and BuildSourceFuncCache.ReleaseTargetType = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ReleaseTargetType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildSourceFuncCacheByCustomProxy($BuildSourceFuncCacheObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceFuncCacheByCustomProxy ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildSourceFuncCacheByCustomProxy ==
		
		$last_sql_command_for_mtooldb = "delete from BuildSourceFuncCache where BuildSourceFuncCache.ProjectPID = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ProjectPID) . "' and BuildSourceFuncCache.daCustomProxyPID = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->daCustomProxyPID) . "' and BuildSourceFuncCache.BuildTargetType = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->BuildTargetType) . "' and BuildSourceFuncCache.ReleaseTargetType = '" . $mtooldb->real_escape_string($BuildSourceFuncCacheObj->ReleaseTargetType) . "'";
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