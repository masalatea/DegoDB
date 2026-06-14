<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class chattopicDBAccess
{
	public function __construct() {
	}
	
	public function GetchattopicList($param_chattopic_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetchattopicList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetchattopicList ==
		
		$last_sql_command_for_mtooldb = "select chattopic.ProjectPID, chattopic.PID, chattopic.name, chattopic.IsOld from chattopic where chattopic.ProjectPID = '" . $mtooldb->real_escape_string($param_chattopic_ProjectPID_where) . "' order by chattopic.IsOld,chattopic.name,chattopic.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new chattopicData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->IsOld = $thisline[3];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getchattopic($param_chattopic_PID_where, $param_chattopic_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getchattopic ==
		// == END OF EDITABLE AREA FOR FUNCTION Getchattopic ==
		
		$last_sql_command_for_mtooldb = "select chattopic.ProjectPID, chattopic.PID, chattopic.name, chattopic.IsOld from chattopic where chattopic.PID = '" . $mtooldb->real_escape_string($param_chattopic_PID_where) . "' and chattopic.ProjectPID = '" . $mtooldb->real_escape_string($param_chattopic_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new chattopicData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->IsOld = $thisline[3];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertchattopic($chattopicObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertchattopic ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertchattopic ==
		
		$last_sql_command_for_mtooldb = "insert into chattopic (ProjectPID, name, IsOld) values('" . $mtooldb->real_escape_string($chattopicObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($chattopicObj->name) . "', '" . $mtooldb->real_escape_string($chattopicObj->IsOld) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatechattopic($chattopicObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatechattopic ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatechattopic ==
		
		$last_sql_command_for_mtooldb = "update chattopic SET name = '" . $mtooldb->real_escape_string($chattopicObj->name) . "', IsOld = '" . $mtooldb->real_escape_string($chattopicObj->IsOld) . "' where chattopic.PID = '" . $mtooldb->real_escape_string($chattopicObj->PID) . "' and chattopic.ProjectPID = '" . $mtooldb->real_escape_string($chattopicObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletechattopic($chattopicObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletechattopic ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletechattopic ==
		
		$last_sql_command_for_mtooldb = "delete from chattopic where chattopic.PID = '" . $mtooldb->real_escape_string($chattopicObj->PID) . "' and chattopic.ProjectPID = '" . $mtooldb->real_escape_string($chattopicObj->ProjectPID) . "'";
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