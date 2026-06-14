<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LastBuildDBAccess
{
	public function __construct() {
	}
	
	public function GetLastBuild($param_LastBuild_ProjectPID_where, $param_LastBuild_ProjectSourceOutputPID_where, $param_LastBuild_BuildClassType_where, $param_LastBuild_EachTargetPID_where, $param_LastBuild_ToTempFolder_where, $param_LastBuild_OutputAfterCopyToTempFolder_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLastBuild ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLastBuild ==
		
		$last_sql_command_for_mtooldb = "select LastBuild.PID, LastBuild.ProjectPID, LastBuild.ProjectSourceOutputPID, LastBuild.BuildClassType, LastBuild.EachTargetPID, LastBuild.ToTempFolder, LastBuild.OutputAfterCopyToTempFolder, LastBuild.LastBuildDT from LastBuild where LastBuild.ProjectPID = '" . $mtooldb->real_escape_string($param_LastBuild_ProjectPID_where) . "' and LastBuild.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_LastBuild_ProjectSourceOutputPID_where) . "' and LastBuild.BuildClassType = '" . $mtooldb->real_escape_string($param_LastBuild_BuildClassType_where) . "' and LastBuild.EachTargetPID = '" . $mtooldb->real_escape_string($param_LastBuild_EachTargetPID_where) . "' and LastBuild.ToTempFolder = '" . $mtooldb->real_escape_string($param_LastBuild_ToTempFolder_where) . "' and LastBuild.OutputAfterCopyToTempFolder = '" . $mtooldb->real_escape_string($param_LastBuild_OutputAfterCopyToTempFolder_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LastBuildData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->ProjectSourceOutputPID = $thisline[2];
			$thisresult->BuildClassType = $thisline[3];
			$thisresult->EachTargetPID = $thisline[4];
			$thisresult->ToTempFolder = $thisline[5];
			$thisresult->OutputAfterCopyToTempFolder = $thisline[6];
			$thisresult->LastBuildDT = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertLastBuild($LastBuildObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLastBuild ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLastBuild ==
		
		$last_sql_command_for_mtooldb = "insert into LastBuild (ProjectPID, ProjectSourceOutputPID, BuildClassType, EachTargetPID, ToTempFolder, OutputAfterCopyToTempFolder, LastBuildDT) values('" . $mtooldb->real_escape_string($LastBuildObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LastBuildObj->ProjectSourceOutputPID) . "', '" . $mtooldb->real_escape_string($LastBuildObj->BuildClassType) . "', '" . $mtooldb->real_escape_string($LastBuildObj->EachTargetPID) . "', '" . $mtooldb->real_escape_string($LastBuildObj->ToTempFolder) . "', '" . $mtooldb->real_escape_string($LastBuildObj->OutputAfterCopyToTempFolder) . "', now())";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteLastBuild($LastBuildObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteLastBuild ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteLastBuild ==
		
		$last_sql_command_for_mtooldb = "delete from LastBuild where LastBuild.ProjectPID = '" . $mtooldb->real_escape_string($LastBuildObj->ProjectPID) . "' and LastBuild.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($LastBuildObj->ProjectSourceOutputPID) . "' and LastBuild.BuildClassType = '" . $mtooldb->real_escape_string($LastBuildObj->BuildClassType) . "' and LastBuild.EachTargetPID = '" . $mtooldb->real_escape_string($LastBuildObj->EachTargetPID) . "' and LastBuild.ToTempFolder = '" . $mtooldb->real_escape_string($LastBuildObj->ToTempFolder) . "' and LastBuild.OutputAfterCopyToTempFolder = '" . $mtooldb->real_escape_string($LastBuildObj->OutputAfterCopyToTempFolder) . "'";
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