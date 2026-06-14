<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestPatternDBAccess
{
	public function __construct() {
	}
	
	public function GetTestPatternList($param_TestPattern_ProjectPID_where, $param_TestPattern_TestGroupPID_where, $param_TestPattern_TestPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestPatternList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestPatternList ==
		
		$last_sql_command_for_mtooldb = "select TestPattern.ProjectPID, TestPattern.TestGroupPID, TestPattern.TestPID, TestPattern.PID, TestPattern.ExpectedResult from TestPattern where TestPattern.ProjectPID = '" . $mtooldb->real_escape_string($param_TestPattern_ProjectPID_where) . "' and TestPattern.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestPattern_TestGroupPID_where) . "' and TestPattern.TestPID = '" . $mtooldb->real_escape_string($param_TestPattern_TestPID_where) . "' order by TestPattern.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestPatternData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->ExpectedResult = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetTestPattern($param_TestPattern_PID_where, $param_TestPattern_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestPattern ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestPattern ==
		
		$last_sql_command_for_mtooldb = "select TestPattern.ProjectPID, TestPattern.TestGroupPID, TestPattern.TestPID, TestPattern.PID, TestPattern.ExpectedResult from TestPattern where TestPattern.PID = '" . $mtooldb->real_escape_string($param_TestPattern_PID_where) . "' and TestPattern.ProjectPID = '" . $mtooldb->real_escape_string($param_TestPattern_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestPatternData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->ExpectedResult = $thisline[4];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTestPattern($TestPatternObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTestPattern ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTestPattern ==
		
		$last_sql_command_for_mtooldb = "insert into TestPattern (ProjectPID, TestGroupPID, TestPID, ExpectedResult) values('" . $mtooldb->real_escape_string($TestPatternObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestPatternObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($TestPatternObj->TestPID) . "', '" . $mtooldb->real_escape_string($TestPatternObj->ExpectedResult) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestPattern($TestPatternObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestPattern ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestPattern ==
		
		$last_sql_command_for_mtooldb = "update TestPattern SET ExpectedResult = '" . $mtooldb->real_escape_string($TestPatternObj->ExpectedResult) . "' where TestPattern.PID = '" . $mtooldb->real_escape_string($TestPatternObj->PID) . "' and TestPattern.ProjectPID = '" . $mtooldb->real_escape_string($TestPatternObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTestPattern($TestPatternObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTestPattern ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTestPattern ==
		
		$last_sql_command_for_mtooldb = "delete from TestPattern where TestPattern.PID = '" . $mtooldb->real_escape_string($TestPatternObj->PID) . "' and TestPattern.ProjectPID = '" . $mtooldb->real_escape_string($TestPatternObj->ProjectPID) . "'";
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