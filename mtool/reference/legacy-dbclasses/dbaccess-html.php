<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlDBAccess
{
	public function __construct() {
	}
	
	public function Gethtml($param_html_PID_where, $param_html_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Gethtml ==
		// == END OF EDITABLE AREA FOR FUNCTION Gethtml ==
		
		$last_sql_command_for_mtooldb = "select html.ProjectPID, html.PID, html.name, html.ProjectSourceOutputPID, html.htmlTemplatePID, html.LastModifiedDT from html where html.PID = '" . $mtooldb->real_escape_string($param_html_PID_where) . "' and html.ProjectPID = '" . $mtooldb->real_escape_string($param_html_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->ProjectSourceOutputPID = $thisline[3];
			$thisresult->htmlTemplatePID = $thisline[4];
			$thisresult->LastModifiedDT = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function Inserthtml($htmlObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Inserthtml ==
		// == END OF EDITABLE AREA FOR FUNCTION Inserthtml ==
		
		$last_sql_command_for_mtooldb = "insert into html (ProjectPID, name, ProjectSourceOutputPID, htmlTemplatePID, LastModifiedDT) values('" . $mtooldb->real_escape_string($htmlObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($htmlObj->name) . "', '" . $mtooldb->real_escape_string($htmlObj->ProjectSourceOutputPID) . "', '" . $mtooldb->real_escape_string($htmlObj->htmlTemplatePID) . "', now())";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatehtml($htmlObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatehtml ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatehtml ==
		
		$last_sql_command_for_mtooldb = "update html SET name = '" . $mtooldb->real_escape_string($htmlObj->name) . "', ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($htmlObj->ProjectSourceOutputPID) . "', htmlTemplatePID = '" . $mtooldb->real_escape_string($htmlObj->htmlTemplatePID) . "', LastModifiedDT = now() where html.PID = '" . $mtooldb->real_escape_string($htmlObj->PID) . "' and html.ProjectPID = '" . $mtooldb->real_escape_string($htmlObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLastModifiedDT($param_html_PID_where, $param_html_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		
		$last_sql_command_for_mtooldb = "update html SET LastModifiedDT = now() where html.PID = '" . $mtooldb->real_escape_string($param_html_PID_where) . "' and html.ProjectPID = '" . $mtooldb->real_escape_string($param_html_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletehtml($htmlObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletehtml ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletehtml ==
		
		$last_sql_command_for_mtooldb = "delete from html where html.PID = '" . $mtooldb->real_escape_string($htmlObj->PID) . "' and html.ProjectPID = '" . $mtooldb->real_escape_string($htmlObj->ProjectPID) . "'";
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