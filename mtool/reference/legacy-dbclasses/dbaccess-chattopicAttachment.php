<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class chattopicAttachmentDBAccess
{
	public function __construct() {
	}
	
	public function GetchattopicAttachment($param_chattopicAttachment_PID_where, $param_chattopicAttachment_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetchattopicAttachment ==
		// == END OF EDITABLE AREA FOR FUNCTION GetchattopicAttachment ==
		
		$last_sql_command_for_mtooldb = "select chattopicAttachment.ProjectPID, chattopicAttachment.chattopicPID, chattopicAttachment.PID, chattopicAttachment.name from chattopicAttachment where chattopicAttachment.PID = '" . $mtooldb->real_escape_string($param_chattopicAttachment_PID_where) . "' and chattopicAttachment.ProjectPID = '" . $mtooldb->real_escape_string($param_chattopicAttachment_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new chattopicAttachmentData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->chattopicPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			return $thisresult;
		}
		return NULL;
	}
	public function GetchattopicAttachmentList($param_chattopicAttachment_ProjectPID_where, $param_chattopicAttachment_chattopicPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetchattopicAttachmentList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetchattopicAttachmentList ==
		
		$last_sql_command_for_mtooldb = "select chattopicAttachment.ProjectPID, chattopicAttachment.chattopicPID, chattopicAttachment.PID, chattopicAttachment.name from chattopicAttachment where chattopicAttachment.ProjectPID = '" . $mtooldb->real_escape_string($param_chattopicAttachment_ProjectPID_where) . "' and chattopicAttachment.chattopicPID = '" . $mtooldb->real_escape_string($param_chattopicAttachment_chattopicPID_where) . "' order by chattopicAttachment.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new chattopicAttachmentData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->chattopicPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertchattopicAttachment($chattopicAttachmentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertchattopicAttachment ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertchattopicAttachment ==
		
		$last_sql_command_for_mtooldb = "insert into chattopicAttachment (ProjectPID, chattopicPID, PID, name) values('" . $mtooldb->real_escape_string($chattopicAttachmentObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($chattopicAttachmentObj->chattopicPID) . "', '" . $mtooldb->real_escape_string($chattopicAttachmentObj->PID) . "', '" . $mtooldb->real_escape_string($chattopicAttachmentObj->name) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatechattopicAttachment($chattopicAttachmentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatechattopicAttachment ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatechattopicAttachment ==
		
		$last_sql_command_for_mtooldb = "update chattopicAttachment SET name = '" . $mtooldb->real_escape_string($chattopicAttachmentObj->name) . "' where chattopicAttachment.PID = '" . $mtooldb->real_escape_string($chattopicAttachmentObj->PID) . "' and chattopicAttachment.ProjectPID = '" . $mtooldb->real_escape_string($chattopicAttachmentObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletechattopicAttachment($chattopicAttachmentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletechattopicAttachment ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletechattopicAttachment ==
		
		$last_sql_command_for_mtooldb = "delete from chattopicAttachment where chattopicAttachment.PID = '" . $mtooldb->real_escape_string($chattopicAttachmentObj->PID) . "' and chattopicAttachment.ProjectPID = '" . $mtooldb->real_escape_string($chattopicAttachmentObj->ProjectPID) . "'";
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