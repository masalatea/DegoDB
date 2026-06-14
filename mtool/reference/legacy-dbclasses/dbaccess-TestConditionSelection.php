<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestConditionSelectionDBAccess
{
	public function __construct() {
	}
	
	public function GetTestConditionSelectionList($param_TestConditionSelection_ProjectPID_where, $param_TestConditionSelection_TestGroupPID_where, $param_TestConditionSelection_TestPID_where, $param_TestConditionSelection_TestConditionPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestConditionSelectionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestConditionSelectionList ==
		
		$last_sql_command_for_mtooldb = "select TestConditionSelection.ProjectPID, TestConditionSelection.TestGroupPID, TestConditionSelection.TestPID, TestConditionSelection.TestConditionPID, TestConditionSelection.PID, TestConditionSelection.Selection, TestConditionSelection.SelectionOrder, TestConditionSelection.IsNewest, TestConditionSelection.ResultExists from TestConditionSelection where TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_ProjectPID_where) . "' and TestConditionSelection.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestGroupPID_where) . "' and TestConditionSelection.TestPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestPID_where) . "' and TestConditionSelection.TestConditionPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestConditionPID_where) . "' order by TestConditionSelection.SelectionOrder,TestConditionSelection.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestConditionSelectionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestConditionPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->Selection = $thisline[5];
			$thisresult->SelectionOrder = $thisline[6];
			$thisresult->IsNewest = $thisline[7];
			$thisresult->ResultExists = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetNewestTestConditionSelectionList($param_TestConditionSelection_ProjectPID_where, $param_TestConditionSelection_TestGroupPID_where, $param_TestConditionSelection_TestPID_where, $param_TestConditionSelection_TestConditionPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetNewestTestConditionSelectionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetNewestTestConditionSelectionList ==
		
		$last_sql_command_for_mtooldb = "select TestConditionSelection.ProjectPID, TestConditionSelection.TestGroupPID, TestConditionSelection.TestPID, TestConditionSelection.TestConditionPID, TestConditionSelection.PID, TestConditionSelection.Selection, TestConditionSelection.SelectionOrder, TestConditionSelection.IsNewest, TestConditionSelection.ResultExists from TestConditionSelection where TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_ProjectPID_where) . "' and TestConditionSelection.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestGroupPID_where) . "' and TestConditionSelection.TestPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestPID_where) . "' and TestConditionSelection.TestConditionPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestConditionPID_where) . "' and TestConditionSelection.IsNewest = '" . $mtooldb->real_escape_string("1") . "' order by TestConditionSelection.SelectionOrder,TestConditionSelection.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestConditionSelectionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestConditionPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->Selection = $thisline[5];
			$thisresult->SelectionOrder = $thisline[6];
			$thisresult->IsNewest = $thisline[7];
			$thisresult->ResultExists = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetNewestOrHasResultTestConditionSelectionList($param_TestConditionSelection_ProjectPID_where, $param_TestConditionSelection_TestGroupPID_where, $param_TestConditionSelection_TestPID_where, $param_TestConditionSelection_TestConditionPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetNewestOrHasResultTestConditionSelectionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetNewestOrHasResultTestConditionSelectionList ==
		
		$last_sql_command_for_mtooldb = "select TestConditionSelection.ProjectPID, TestConditionSelection.TestGroupPID, TestConditionSelection.TestPID, TestConditionSelection.TestConditionPID, TestConditionSelection.PID, TestConditionSelection.Selection, TestConditionSelection.SelectionOrder, TestConditionSelection.IsNewest, TestConditionSelection.ResultExists from TestConditionSelection where TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_ProjectPID_where) . "' and TestConditionSelection.TestGroupPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestGroupPID_where) . "' and TestConditionSelection.TestPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestPID_where) . "' and TestConditionSelection.TestConditionPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_TestConditionPID_where) . "' and (TestConditionSelection.IsNewest = '" . $mtooldb->real_escape_string("1") . "' or TestConditionSelection.ResultExists = '" . $mtooldb->real_escape_string("1") . "') order by TestConditionSelection.SelectionOrder,TestConditionSelection.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestConditionSelectionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestConditionPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->Selection = $thisline[5];
			$thisresult->SelectionOrder = $thisline[6];
			$thisresult->IsNewest = $thisline[7];
			$thisresult->ResultExists = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetTestConditionSelection($param_TestConditionSelection_PID_where, $param_TestConditionSelection_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestConditionSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestConditionSelection ==
		
		$last_sql_command_for_mtooldb = "select TestConditionSelection.ProjectPID, TestConditionSelection.TestGroupPID, TestConditionSelection.TestPID, TestConditionSelection.TestConditionPID, TestConditionSelection.PID, TestConditionSelection.Selection, TestConditionSelection.SelectionOrder, TestConditionSelection.IsNewest, TestConditionSelection.ResultExists from TestConditionSelection where TestConditionSelection.PID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_PID_where) . "' and TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($param_TestConditionSelection_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestConditionSelectionData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->TestPID = $thisline[2];
			$thisresult->TestConditionPID = $thisline[3];
			$thisresult->PID = $thisline[4];
			$thisresult->Selection = $thisline[5];
			$thisresult->SelectionOrder = $thisline[6];
			$thisresult->IsNewest = $thisline[7];
			$thisresult->ResultExists = $thisline[8];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTestConditionSelection($TestConditionSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTestConditionSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTestConditionSelection ==
		
		$last_sql_command_for_mtooldb = "insert into TestConditionSelection (ProjectPID, TestGroupPID, TestPID, TestConditionPID, Selection, SelectionOrder, IsNewest, ResultExists) values('" . $mtooldb->real_escape_string($TestConditionSelectionObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->TestPID) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->TestConditionPID) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->Selection) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->SelectionOrder) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->IsNewest) . "', '" . $mtooldb->real_escape_string($TestConditionSelectionObj->ResultExists) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestConditionSelection($TestConditionSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestConditionSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestConditionSelection ==
		
		$last_sql_command_for_mtooldb = "update TestConditionSelection SET Selection = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->Selection) . "', SelectionOrder = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->SelectionOrder) . "', IsNewest = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->IsNewest) . "' where TestConditionSelection.PID = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->PID) . "' and TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTestConditionSelectionSetToOld($TestConditionSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTestConditionSelectionSetToOld ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTestConditionSelectionSetToOld ==
		
		$last_sql_command_for_mtooldb = "update TestConditionSelection SET IsNewest = '" . $mtooldb->real_escape_string("0") . "' where TestConditionSelection.PID = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->PID) . "' and TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTestConditionSelection($TestConditionSelectionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTestConditionSelection ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTestConditionSelection ==
		
		$last_sql_command_for_mtooldb = "delete from TestConditionSelection where TestConditionSelection.PID = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->PID) . "' and TestConditionSelection.ProjectPID = '" . $mtooldb->real_escape_string($TestConditionSelectionObj->ProjectPID) . "'";
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