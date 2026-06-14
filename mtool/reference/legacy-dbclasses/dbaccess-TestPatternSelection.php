<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestPatternSelectionDBAccess
{
	public function __construct() {
	}
	
	public function GetTestPatternSelectionList($param_TestPatternSelection_ProjectPID_where, $param_TestPatternSelection_TestGroupPID_where, $param_TestPatternSelection_TestPID_where, $param_TestPatternSelection_TestPatternPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestPatternSelectionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestPatternSelectionList ==
		
		$last_sql_command_for_mtooldb = "select TestPatternSelection.ProjectPID, TestPatternSelection.TestGroupPID, TestPatternSelection.TestPID, TestPatternSelection.TestPatternPID, TestPatternSelection.PID, TestPatternSelection.Selection from TestPatternSelection where TestPatternSelection.ProjectPID = '" . $mtooldb->real_escape_string($param_TestPatternSelection_ProjectPID_where) . "' and TestPatternSelection.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestPatternSelection_TestGroupPID_where) . "' and TestPatternSelection.TestPID = '" . $mtooldb->real_escape_string($param_TestPatternSelection_TestPID_where) . "' and TestPatternSelection.TestPatternPID = '" . $mtooldb->real_escape_string($param_TestPatternSelection_TestPatternPID_where) . "' order by TestPatternSelection.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestPatternSelectionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestPatternPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->Selection = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetTestPatternSelection($param_TestPatternSelection_PID_where, $param_TestPatternSelection_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestPatternSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestPatternSelection ==
		
		$last_sql_command_for_mtooldb = "select TestPatternSelection.ProjectPID, TestPatternSelection.TestGroupPID, TestPatternSelection.TestPID, TestPatternSelection.TestPatternPID, TestPatternSelection.PID, TestPatternSelection.Selection from TestPatternSelection where TestPatternSelection.PID = '" . $mtooldb->real_escape_string($param_TestPatternSelection_PID_where) . "' and TestPatternSelection.ProjectPID = '" . $mtooldb->real_escape_string($param_TestPatternSelection_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestPatternSelectionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestPatternPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->Selection = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTestPatternSelection($TestPatternSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTestPatternSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTestPatternSelection ==
		
		$last_sql_command_for_mtooldb = "insert into TestPatternSelection (ProjectPID, TestGroupPID, TestPID, TestPatternPID, Selection) values('" . $mtooldb->real_escape_string($TestPatternSelectionObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestPatternSelectionObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($TestPatternSelectionObj->TestPID) . "', '" . $mtooldb->real_escape_string($TestPatternSelectionObj->TestPatternPID) . "', '" . $mtooldb->real_escape_string($TestPatternSelectionObj->Selection) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestPatternSelection($TestPatternSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestPatternSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestPatternSelection ==
		
		$last_sql_command_for_mtooldb = "update TestPatternSelection SET Selection = '" . $mtooldb->real_escape_string($TestPatternSelectionObj->Selection) . "' where TestPatternSelection.PID = '" . $mtooldb->real_escape_string($TestPatternSelectionObj->PID) . "' and TestPatternSelection.ProjectPID = '" . $mtooldb->real_escape_string($TestPatternSelectionObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTestPatternSelection($TestPatternSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTestPatternSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTestPatternSelection ==
		
		$last_sql_command_for_mtooldb = "delete from TestPatternSelection where TestPatternSelection.PID = '" . $mtooldb->real_escape_string($TestPatternSelectionObj->PID) . "' and TestPatternSelection.ProjectPID = '" . $mtooldb->real_escape_string($TestPatternSelectionObj->ProjectPID) . "'";
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