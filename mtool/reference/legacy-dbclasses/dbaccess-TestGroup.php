<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestGroupDBAccess
{
	public function __construct() {
	}
	
	public function GetTestGroupList($param_TestGroup_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestGroupList ==
		
		$last_sql_command_for_mtooldb = "select TestGroup.ProjectPID, TestGroup.PID, TestGroup.name, TestGroup.UnitTestTemplateBaseDir, TestGroup.UnitTestWorkingDir from TestGroup where TestGroup.ProjectPID = '" . $mtooldb->real_escape_string($param_TestGroup_ProjectPID_where) . "' order by TestGroup.name,TestGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestGroupData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->UnitTestTemplateBaseDir = $thisline[3];
			$thisresult->UnitTestWorkingDir = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetTestGroup($param_TestGroup_PID_where, $param_TestGroup_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestGroup ==
		
		$last_sql_command_for_mtooldb = "select TestGroup.ProjectPID, TestGroup.PID, TestGroup.name, TestGroup.UnitTestTemplateBaseDir, TestGroup.UnitTestWorkingDir from TestGroup where TestGroup.PID = '" . $mtooldb->real_escape_string($param_TestGroup_PID_where) . "' and TestGroup.ProjectPID = '" . $mtooldb->real_escape_string($param_TestGroup_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestGroupData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->UnitTestTemplateBaseDir = $thisline[3];
			$thisresult->UnitTestWorkingDir = $thisline[4];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTestGroup($TestGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTestGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTestGroup ==
		
		$last_sql_command_for_mtooldb = "insert into TestGroup (ProjectPID, name, UnitTestTemplateBaseDir, UnitTestWorkingDir) values('" . $mtooldb->real_escape_string($TestGroupObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestGroupObj->name) . "', '" . $mtooldb->real_escape_string($TestGroupObj->UnitTestTemplateBaseDir) . "', '" . $mtooldb->real_escape_string($TestGroupObj->UnitTestWorkingDir) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestGroup($TestGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestGroup ==
		
		$last_sql_command_for_mtooldb = "update TestGroup SET name = '" . $mtooldb->real_escape_string($TestGroupObj->name) . "', UnitTestTemplateBaseDir = '" . $mtooldb->real_escape_string($TestGroupObj->UnitTestTemplateBaseDir) . "', UnitTestWorkingDir = '" . $mtooldb->real_escape_string($TestGroupObj->UnitTestWorkingDir) . "' where TestGroup.PID = '" . $mtooldb->real_escape_string($TestGroupObj->PID) . "' and TestGroup.ProjectPID = '" . $mtooldb->real_escape_string($TestGroupObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTestGroup($TestGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTestGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTestGroup ==
		
		$last_sql_command_for_mtooldb = "delete from TestGroup where TestGroup.PID = '" . $mtooldb->real_escape_string($TestGroupObj->PID) . "' and TestGroup.ProjectPID = '" . $mtooldb->real_escape_string($TestGroupObj->ProjectPID) . "'";
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