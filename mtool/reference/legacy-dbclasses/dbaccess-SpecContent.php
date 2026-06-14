<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class SpecContentDBAccess
{
	public function __construct() {
	}
	
	public function GetSpecContentList($param_SpecContent_ProjectPID_where, $param_SpecContent_SpecPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecContentList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecContentList ==
		
		$last_sql_command_for_mtooldb = "select SpecContent.ProjectPID, SpecContent.SpecPID, SpecContent.PID, SpecContent.Depth, SpecContent.ContentOrder, SpecContent.Title, SpecContent.Description from SpecContent where SpecContent.ProjectPID = '" . $mtooldb->real_escape_string($param_SpecContent_ProjectPID_where) . "' and SpecContent.SpecPID = '" . $mtooldb->real_escape_string($param_SpecContent_SpecPID_where) . "' order by SpecContent.ContentOrder,SpecContent.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecContentData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->SpecPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->Depth = $thisline[3];
			$thisresult->ContentOrder = $thisline[4];
			$thisresult->Title = $thisline[5];
			$thisresult->Description = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecContent($param_SpecContent_PID_where, $param_SpecContent_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecContent ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecContent ==
		
		$last_sql_command_for_mtooldb = "select SpecContent.ProjectPID, SpecContent.SpecPID, SpecContent.PID, SpecContent.Depth, SpecContent.ContentOrder, SpecContent.Title, SpecContent.Description from SpecContent where SpecContent.PID = '" . $mtooldb->real_escape_string($param_SpecContent_PID_where) . "' and SpecContent.ProjectPID = '" . $mtooldb->real_escape_string($param_SpecContent_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecContentData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->SpecPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->Depth = $thisline[3];
			$thisresult->ContentOrder = $thisline[4];
			$thisresult->Title = $thisline[5];
			$thisresult->Description = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertSpecContent($SpecContentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertSpecContent ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertSpecContent ==
		
		$last_sql_command_for_mtooldb = "insert into SpecContent (ProjectPID, SpecPID, Depth, Title, Description) values('" . $mtooldb->real_escape_string($SpecContentObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($SpecContentObj->SpecPID) . "', '" . $mtooldb->real_escape_string($SpecContentObj->Depth) . "', '" . $mtooldb->real_escape_string($SpecContentObj->Title) . "', '" . $mtooldb->real_escape_string($SpecContentObj->Description) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSpecContent($SpecContentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSpecContent ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSpecContent ==
		
		$last_sql_command_for_mtooldb = "update SpecContent SET Depth = '" . $mtooldb->real_escape_string($SpecContentObj->Depth) . "', Title = '" . $mtooldb->real_escape_string($SpecContentObj->Title) . "', Description = '" . $mtooldb->real_escape_string($SpecContentObj->Description) . "' where SpecContent.PID = '" . $mtooldb->real_escape_string($SpecContentObj->PID) . "' and SpecContent.ProjectPID = '" . $mtooldb->real_escape_string($SpecContentObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSpecContentOrder($param_SpecContent_ContentOrder_update, $param_SpecContent_PID_where, $param_SpecContent_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSpecContentOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSpecContentOrder ==
		
		$last_sql_command_for_mtooldb = "update SpecContent SET ContentOrder = '" . $mtooldb->real_escape_string($param_SpecContent_ContentOrder_update) . "' where SpecContent.PID = '" . $mtooldb->real_escape_string($param_SpecContent_PID_where) . "' and SpecContent.ProjectPID = '" . $mtooldb->real_escape_string($param_SpecContent_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteSpecContent($SpecContentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteSpecContent ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteSpecContent ==
		
		$last_sql_command_for_mtooldb = "delete from SpecContent where SpecContent.PID = '" . $mtooldb->real_escape_string($SpecContentObj->PID) . "' and SpecContent.ProjectPID = '" . $mtooldb->real_escape_string($SpecContentObj->ProjectPID) . "'";
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