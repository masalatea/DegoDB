<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class Test_leftouterjoin_ProjectDBAccess
{
	public function __construct() {
	}
	
	public function GetTestList($param_Test_ProjectPID_where, $param_Test_TestGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestList ==
		
		$last_sql_command_for_mtooldb = "select Test.ProjectPID, Test.TestGroupPID, Test.PID, Test.name, Test.UnitTestTargetClassName, Project.name, TestGroup.name from Test LEFT OUTER JOIN Project ON Test.ProjectPID = Project.PID LEFT OUTER JOIN TestGroup ON Test.TestGroupPID = TestGroup.PID where Test.ProjectPID = '" . $mtooldb->real_escape_string($param_Test_ProjectPID_where) . "' and Test.TestGroupPID = '" . $mtooldb->real_escape_string($param_Test_TestGroupPID_where) . "' order by Test.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new Test_leftouterjoin_ProjectData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->TestGroupPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->UnitTestTargetClassName = $thisline[4];
			$thisresult->Projectname = $thisline[5];
			$thisresult->TestGroupname = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>