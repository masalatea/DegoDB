<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildTokenProjectSourceOutputDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildTokenProjectSourceOutputList($param_BuildTokenProjectSourceOutput_BuildTokenPID_where, $param_BuildTokenProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildTokenProjectSourceOutputList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildTokenProjectSourceOutputList ==
		
		$last_sql_command_for_mtooldb = "select BuildTokenProjectSourceOutput.PID, BuildTokenProjectSourceOutput.ProjectPID, BuildTokenProjectSourceOutput.BuildTokenPID, BuildTokenProjectSourceOutput.ProjectSourceOutputPID, BuildTokenProjectSourceOutput.BuildTargetType, BuildTokenProjectSourceOutput.IsPartlyCompleted, BuildTokenProjectSourceOutput.IsCompleted from BuildTokenProjectSourceOutput where BuildTokenProjectSourceOutput.BuildTokenPID = '" . $mtooldb->real_escape_string($param_BuildTokenProjectSourceOutput_BuildTokenPID_where) . "' and BuildTokenProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildTokenProjectSourceOutput_ProjectPID_where) . "' and BuildTokenProjectSourceOutput.IsCompleted = '" . $mtooldb->real_escape_string("0") . "' order by BuildTokenProjectSourceOutput.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildTokenProjectSourceOutputData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->BuildTokenPID = $thisline[2];
			$thisresult->ProjectSourceOutputPID = $thisline[3];
			$thisresult->BuildTargetType = $thisline[4];
			$thisresult->IsPartlyCompleted = $thisline[5];
			$thisresult->IsCompleted = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertBuildTokenProjectSourceOutput($BuildTokenProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildTokenProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildTokenProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "insert into BuildTokenProjectSourceOutput (ProjectPID, BuildTokenPID, ProjectSourceOutputPID, BuildTargetType) values('" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->BuildTokenPID) . "', '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->ProjectSourceOutputPID) . "', '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->BuildTargetType) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatePartlyCompletedFlag($BuildTokenProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatePartlyCompletedFlag ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatePartlyCompletedFlag ==
		
		$last_sql_command_for_mtooldb = "update BuildTokenProjectSourceOutput SET IsPartlyCompleted = '" . $mtooldb->real_escape_string("1") . "' where BuildTokenProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->PID) . "' and BuildTokenProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->ProjectPID) . "' and BuildTokenProjectSourceOutput.BuildTokenPID = '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->BuildTokenPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateCompletedFlag($BuildTokenProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateCompletedFlag ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateCompletedFlag ==
		
		$last_sql_command_for_mtooldb = "update BuildTokenProjectSourceOutput SET IsCompleted = '" . $mtooldb->real_escape_string("1") . "' where BuildTokenProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->PID) . "' and BuildTokenProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->ProjectPID) . "' and BuildTokenProjectSourceOutput.BuildTokenPID = '" . $mtooldb->real_escape_string($BuildTokenProjectSourceOutputObj->BuildTokenPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildTokenProjectSourceOutput()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildTokenProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildTokenProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "delete from BuildTokenProjectSourceOutput where BuildTokenProjectSourceOutput.BuildTokenPID not in (select PID from BuildToken)";
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