<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectSecurityForEachPageDetailsDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectSecurityForEachPageDetailsList($param_ProjectSecurityForEachPage_SERVER_NAME_where, $param_ProjectSecurityForEachPage_SCRIPT_NAME_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectSecurityForEachPageDetailsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectSecurityForEachPageDetailsList ==
		
		$last_sql_command_for_mtooldb = "select ProjectSecurityForEachPageDetails.EachPagePID, ProjectSecurityForEachPageDetails.PID, ProjectSecurityForEachPageDetails.SecurityType from ProjectSecurityForEachPageDetails join ProjectSecurityForEachPage where ProjectSecurityForEachPage.SERVER_NAME = '" . $mtooldb->real_escape_string($param_ProjectSecurityForEachPage_SERVER_NAME_where) . "' and ProjectSecurityForEachPage.SCRIPT_NAME = '" . $mtooldb->real_escape_string($param_ProjectSecurityForEachPage_SCRIPT_NAME_where) . "' and ProjectSecurityForEachPage.PID = ProjectSecurityForEachPageDetails.EachPagePID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectSecurityForEachPageDetailsData();
			$thisresult->EachPagePID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->SecurityType = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertProjectSecurityForEachPageDetails($ProjectSecurityForEachPageDetailsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectSecurityForEachPageDetails ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectSecurityForEachPageDetails ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectSecurityForEachPageDetails (EachPagePID, SecurityType) values('" . $mtooldb->real_escape_string($ProjectSecurityForEachPageDetailsObj->EachPagePID) . "', '" . $mtooldb->real_escape_string($ProjectSecurityForEachPageDetailsObj->SecurityType) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProjectSecurityForEachPageDetails($ProjectSecurityForEachPageDetailsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProjectSecurityForEachPageDetails ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProjectSecurityForEachPageDetails ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectSecurityForEachPageDetails where ProjectSecurityForEachPageDetails.PID = '" . $mtooldb->real_escape_string($ProjectSecurityForEachPageDetailsObj->PID) . "' and ProjectSecurityForEachPageDetails.EachPagePID = '" . $mtooldb->real_escape_string($ProjectSecurityForEachPageDetailsObj->EachPagePID) . "'";
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