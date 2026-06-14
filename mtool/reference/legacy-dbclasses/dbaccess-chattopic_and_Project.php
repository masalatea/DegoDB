<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class chattopic_and_ProjectDBAccess
{
	public function __construct() {
	}
	
	public function GetchattopicList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetchattopicList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetchattopicList ==
		
		$last_sql_command_for_mtooldb = "select Project.name, chattopic.ProjectPID, chattopic.PID, chattopic.name, chattopic.IsOld from Project join chattopic join ProjectUser where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.ProjectPID = Project.PID and ProjectUser.ProjectPID = chattopic.ProjectPID order by chattopic.IsOld,chattopic.name,chattopic.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new chattopic_and_ProjectData();
			$thisresult->Projectname = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->IsOld = $thisline[4];
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