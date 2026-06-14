<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestDBAccess
{
	public function __construct() {
	}
	
	public function GetTest($param_Test_PID_where, $param_Test_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTest ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTest ==
		
		$last_sql_command_for_mtooldb = "select Test.ProjectPID, Test.TestGroupPID, Test.PID, Test.name, Test.UnitTestTargetClassName from Test where Test.PID = '" . $mtooldb->real_escape_string($param_Test_PID_where) . "' and Test.ProjectPID = '" . $mtooldb->real_escape_string($param_Test_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->UnitTestTargetClassName = $thisline[4];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertTest($TestObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertTest ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertTest ==
		
		$last_sql_command_for_mtooldb = "insert into Test (ProjectPID, TestGroupPID, name, UnitTestTargetClassName) values('" . $mtooldb->real_escape_string($TestObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($TestObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($TestObj->name) . "', '" . $mtooldb->real_escape_string($TestObj->UnitTestTargetClassName) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateTest($TestObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateTest ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateTest ==
		
		$last_sql_command_for_mtooldb = "update Test SET name = '" . $mtooldb->real_escape_string($TestObj->name) . "', UnitTestTargetClassName = '" . $mtooldb->real_escape_string($TestObj->UnitTestTargetClassName) . "' where Test.PID = '" . $mtooldb->real_escape_string($TestObj->PID) . "' and Test.ProjectPID = '" . $mtooldb->real_escape_string($TestObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteTest($TestObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteTest ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteTest ==
		
		$last_sql_command_for_mtooldb = "delete from Test where Test.PID = '" . $mtooldb->real_escape_string($TestObj->PID) . "' and Test.ProjectPID = '" . $mtooldb->real_escape_string($TestObj->ProjectPID) . "'";
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