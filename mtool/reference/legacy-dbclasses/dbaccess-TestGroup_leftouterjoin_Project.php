<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class TestGroup_leftouterjoin_ProjectDBAccess
{
	public function __construct() {
	}
	
	public function GetTestGroupByOwnerOrUserSecurityList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetTestGroupByOwnerOrUserSecurityList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetTestGroupByOwnerOrUserSecurityList ==
		
		$last_sql_command_for_mtooldb = "select Project.name, TestGroup.ProjectPID, TestGroup.PID, TestGroup.name, TestGroup.UnitTestTemplateBaseDir, TestGroup.UnitTestWorkingDir from Project join TestGroup join ProjectUser where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.ProjectPID = Project.PID and ProjectUser.ProjectPID = TestGroup.ProjectPID order by TestGroup.name,TestGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new TestGroup_leftouterjoin_ProjectData();
			$thisresult->Projectname = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->UnitTestTemplateBaseDir = $thisline[4];
			$thisresult->UnitTestWorkingDir = $thisline[5];
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