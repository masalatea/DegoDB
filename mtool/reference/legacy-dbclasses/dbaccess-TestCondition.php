<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestConditionDBAccess
{
	public function __construct() {
	}
	
	public function GetTestConditionList($param_TestCondition_ProjectPID_where, $param_TestCondition_TestGroupPID_where, $param_TestCondition_TestPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestConditionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestConditionList ==
		
		$last_sql_command_for_mtooldb = "select TestCondition.ProjectPID, TestCondition.TestGroupPID, TestCondition.TestPID, TestCondition.PID, TestCondition.Title, TestCondition.Description from TestCondition where TestCondition.ProjectPID = '" . $mtooldb->real_escape_string($param_TestCondition_ProjectPID_where) . "' and TestCondition.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestCondition_TestGroupPID_where) . "' and TestCondition.TestPID = '" . $mtooldb->real_escape_string($param_TestCondition_TestPID_where) . "' order by TestCondition.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestConditionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->Title = $thisline[4];
			$thisresult->Description = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetTestCondition($param_TestCondition_PID_where, $param_TestCondition_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestCondition ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestCondition ==
		
		$last_sql_command_for_mtooldb = "select TestCondition.ProjectPID, TestCondition.TestGroupPID, TestCondition.TestPID, TestCondition.PID, TestCondition.Title, TestCondition.Description from TestCondition where TestCondition.PID = '" . $mtooldb->real_escape_string($param_TestCondition_PID_where) . "' and TestCondition.ProjectPID = '" . $mtooldb->real_escape_string($param_TestCondition_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestConditionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->PID = $thisline[3];
			$thisresult->Title = $thisline[4];
			$thisresult->Description = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTestCondition($TestConditionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTestCondition ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTestCondition ==
		
		$last_sql_command_for_mtooldb = "insert into TestCondition (ProjectPID, TestGroupPID, TestPID, Title, Description) values('" . $mtooldb->real_escape_string($TestConditionObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestConditionObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($TestConditionObj->TestPID) . "', '" . $mtooldb->real_escape_string($TestConditionObj->Title) . "', '" . $mtooldb->real_escape_string($TestConditionObj->Description) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestCondition($TestConditionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestCondition ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestCondition ==
		
		$last_sql_command_for_mtooldb = "update TestCondition SET Title = '" . $mtooldb->real_escape_string($TestConditionObj->Title) . "', Description = '" . $mtooldb->real_escape_string($TestConditionObj->Description) . "' where TestCondition.PID = '" . $mtooldb->real_escape_string($TestConditionObj->PID) . "' and TestCondition.ProjectPID = '" . $mtooldb->real_escape_string($TestConditionObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestConditionOrder($param_TestCondition_ConditionOrder_update, $param_TestCondition_PID_where, $param_TestCondition_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestConditionOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestConditionOrder ==
		
		$last_sql_command_for_mtooldb = "update TestCondition SET ConditionOrder = '" . $mtooldb->real_escape_string($param_TestCondition_ConditionOrder_update) . "' where TestCondition.PID = '" . $mtooldb->real_escape_string($param_TestCondition_PID_where) . "' and TestCondition.ProjectPID = '" . $mtooldb->real_escape_string($param_TestCondition_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTestCondition($TestConditionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTestCondition ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTestCondition ==
		
		$last_sql_command_for_mtooldb = "delete from TestCondition where TestCondition.PID = '" . $mtooldb->real_escape_string($TestConditionObj->PID) . "' and TestCondition.ProjectPID = '" . $mtooldb->real_escape_string($TestConditionObj->ProjectPID) . "'";
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