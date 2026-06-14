<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlParameterDBAccess
{
	public function __construct() {
	}
	
	public function GethtmlParameterList($param_htmlParameter_ProjectPID_where, $param_htmlParameter_htmlPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlParameterList ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlParameterList ==
		
		$last_sql_command_for_mtooldb = "select htmlParameter.ProjectPID, htmlParameter.htmlPID, htmlParameter.PID, htmlParameter.ParameterName, htmlParameter.ParameterValue from htmlParameter where htmlParameter.ProjectPID = '" . $mtooldb->real_escape_string($param_htmlParameter_ProjectPID_where) . "' and htmlParameter.htmlPID = '" . $mtooldb->real_escape_string($param_htmlParameter_htmlPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlParameterData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->htmlPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->ParameterName = $thisline[3];
			$thisresult->ParameterValue = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GethtmlParameter($param_htmlParameter_PID_where, $param_htmlParameter_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlParameter ==
		
		$last_sql_command_for_mtooldb = "select htmlParameter.ProjectPID, htmlParameter.htmlPID, htmlParameter.PID, htmlParameter.ParameterName, htmlParameter.ParameterValue from htmlParameter where htmlParameter.PID = '" . $mtooldb->real_escape_string($param_htmlParameter_PID_where) . "' and htmlParameter.ProjectPID = '" . $mtooldb->real_escape_string($param_htmlParameter_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlParameterData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->htmlPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->ParameterName = $thisline[3];
			$thisresult->ParameterValue = $thisline[4];
			return $thisresult;
		}
		return NULL;
	}
	public function InserthtmlParameter($htmlParameterObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InserthtmlParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION InserthtmlParameter ==
		
		$last_sql_command_for_mtooldb = "insert into htmlParameter (ProjectPID, htmlPID, ParameterName, ParameterValue) values('" . $mtooldb->real_escape_string($htmlParameterObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($htmlParameterObj->htmlPID) . "', '" . $mtooldb->real_escape_string($htmlParameterObj->ParameterName) . "', '" . $mtooldb->real_escape_string($htmlParameterObj->ParameterValue) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatehtmlParameter($htmlParameterObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatehtmlParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatehtmlParameter ==
		
		$last_sql_command_for_mtooldb = "update htmlParameter SET ParameterName = '" . $mtooldb->real_escape_string($htmlParameterObj->ParameterName) . "', ParameterValue = '" . $mtooldb->real_escape_string($htmlParameterObj->ParameterValue) . "' where htmlParameter.PID = '" . $mtooldb->real_escape_string($htmlParameterObj->PID) . "' and htmlParameter.ProjectPID = '" . $mtooldb->real_escape_string($htmlParameterObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletehtmlParameter($htmlParameterObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletehtmlParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletehtmlParameter ==
		
		$last_sql_command_for_mtooldb = "delete from htmlParameter where htmlParameter.PID = '" . $mtooldb->real_escape_string($htmlParameterObj->PID) . "' and htmlParameter.ProjectPID = '" . $mtooldb->real_escape_string($htmlParameterObj->ProjectPID) . "'";
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