<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestPatternExecuteResultDBAccess
{
	public function __construct() {
	}
	
	public function GetTestPatternExecuteResultList($param_TestPatternExecuteResult_ProjectPID_where, $param_TestPatternExecuteResult_TestGroupPID_where, $param_TestPatternExecuteResult_TestPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestPatternExecuteResultList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestPatternExecuteResultList ==
		
		$last_sql_command_for_mtooldb = "select TestPatternExecuteResult.ProjectPID, TestPatternExecuteResult.TestGroupPID, TestPatternExecuteResult.TestPID, TestPatternExecuteResult.TestPatternPID, TestPatternExecuteResult.PID, TestPatternExecuteResult.ExecuteResult, TestPatternExecuteResult.Comment from TestPatternExecuteResult where TestPatternExecuteResult.ProjectPID = '" . $mtooldb->real_escape_string($param_TestPatternExecuteResult_ProjectPID_where) . "' and TestPatternExecuteResult.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestPatternExecuteResult_TestGroupPID_where) . "' and TestPatternExecuteResult.TestPID = '" . $mtooldb->real_escape_string($param_TestPatternExecuteResult_TestPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestPatternExecuteResultData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestPatternPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->ExecuteResult = $thisline[5];
			$thisresult->Comment = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetTestPatternExecuteResult($param_TestPatternExecuteResult_PID_where, $param_TestPatternExecuteResult_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestPatternExecuteResult ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestPatternExecuteResult ==
		
		$last_sql_command_for_mtooldb = "select TestPatternExecuteResult.ProjectPID, TestPatternExecuteResult.TestGroupPID, TestPatternExecuteResult.TestPID, TestPatternExecuteResult.TestPatternPID, TestPatternExecuteResult.PID, TestPatternExecuteResult.ExecuteResult, TestPatternExecuteResult.Comment from TestPatternExecuteResult where TestPatternExecuteResult.PID = '" . $mtooldb->real_escape_string($param_TestPatternExecuteResult_PID_where) . "' and TestPatternExecuteResult.ProjectPID = '" . $mtooldb->real_escape_string($param_TestPatternExecuteResult_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestPatternExecuteResultData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestPatternPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->ExecuteResult = $thisline[5];
			$thisresult->Comment = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTestPatternExecuteResult($TestPatternExecuteResultObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTestPatternExecuteResult ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTestPatternExecuteResult ==
		
		$last_sql_command_for_mtooldb = "insert into TestPatternExecuteResult (ProjectPID, TestGroupPID, TestPID, TestPatternPID, ExecuteResult, Comment) values('" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->TestPID) . "', '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->TestPatternPID) . "', '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->ExecuteResult) . "', '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->Comment) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestPatternExecuteResult($TestPatternExecuteResultObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestPatternExecuteResult ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestPatternExecuteResult ==
		
		$last_sql_command_for_mtooldb = "update TestPatternExecuteResult SET ExecuteResult = '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->ExecuteResult) . "', Comment = '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->Comment) . "' where TestPatternExecuteResult.PID = '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->PID) . "' and TestPatternExecuteResult.ProjectPID = '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTestPatternExecuteResult($TestPatternExecuteResultObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTestPatternExecuteResult ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTestPatternExecuteResult ==
		
		$last_sql_command_for_mtooldb = "delete from TestPatternExecuteResult where TestPatternExecuteResult.PID = '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->PID) . "' and TestPatternExecuteResult.ProjectPID = '" . $mtooldb->real_escape_string($TestPatternExecuteResultObj->ProjectPID) . "'";
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