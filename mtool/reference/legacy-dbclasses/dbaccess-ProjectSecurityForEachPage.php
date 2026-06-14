<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectSecurityForEachPageDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectSecurityForEachPageList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectSecurityForEachPageList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectSecurityForEachPageList ==
		
		$last_sql_command_for_mtooldb = "select ProjectSecurityForEachPage.PID, ProjectSecurityForEachPage.SERVER_NAME, ProjectSecurityForEachPage.SCRIPT_NAME from ProjectSecurityForEachPage order by ProjectSecurityForEachPage.SERVER_NAME,ProjectSecurityForEachPage.SCRIPT_NAME";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectSecurityForEachPageData();
			$thisresult->PID = $thisline[0];
			$thisresult->SERVER_NAME = $thisline[1];
			$thisresult->SCRIPT_NAME = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectSecurityForEachPage($param_ProjectSecurityForEachPage_SERVER_NAME_where, $param_ProjectSecurityForEachPage_SCRIPT_NAME_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectSecurityForEachPage ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectSecurityForEachPage ==
		
		$last_sql_command_for_mtooldb = "select ProjectSecurityForEachPage.PID, ProjectSecurityForEachPage.SERVER_NAME, ProjectSecurityForEachPage.SCRIPT_NAME from ProjectSecurityForEachPage where ProjectSecurityForEachPage.SERVER_NAME = '" . $mtooldb->real_escape_string($param_ProjectSecurityForEachPage_SERVER_NAME_where) . "' and ProjectSecurityForEachPage.SCRIPT_NAME = '" . $mtooldb->real_escape_string($param_ProjectSecurityForEachPage_SCRIPT_NAME_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectSecurityForEachPageData();
			$thisresult->PID = $thisline[0];
			$thisresult->SERVER_NAME = $thisline[1];
			$thisresult->SCRIPT_NAME = $thisline[2];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertProjectSecurityForEachPage($ProjectSecurityForEachPageObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectSecurityForEachPage ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectSecurityForEachPage ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectSecurityForEachPage (SERVER_NAME, SCRIPT_NAME) values('" . $mtooldb->real_escape_string($ProjectSecurityForEachPageObj->SERVER_NAME) . "', '" . $mtooldb->real_escape_string($ProjectSecurityForEachPageObj->SCRIPT_NAME) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProjectSecurityForEachPage($ProjectSecurityForEachPageObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProjectSecurityForEachPage ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProjectSecurityForEachPage ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectSecurityForEachPage where ProjectSecurityForEachPage.SERVER_NAME = '" . $mtooldb->real_escape_string($ProjectSecurityForEachPageObj->SERVER_NAME) . "' and ProjectSecurityForEachPage.SCRIPT_NAME = '" . $mtooldb->real_escape_string($ProjectSecurityForEachPageObj->SCRIPT_NAME) . "'";
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