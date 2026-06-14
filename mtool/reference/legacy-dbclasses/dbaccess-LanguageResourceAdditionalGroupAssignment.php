<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceAdditionalGroupAssignmentDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceAdditionalGroupAssignmentList($param_LanguageResourceAdditionalGroupAssignment_LanguageResourcePID_where, $param_LanguageResourceAdditionalGroupAssignment_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceAdditionalGroupAssignmentList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceAdditionalGroupAssignmentList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceAdditionalGroupAssignment.PID, LanguageResourceAdditionalGroupAssignment.ProjectPID, LanguageResourceAdditionalGroupAssignment.LanguageResourcePID, LanguageResourceAdditionalGroupAssignment.LanguageResourceGroupPID, LanguageResourceGroup.Name from LanguageResourceAdditionalGroupAssignment LEFT OUTER JOIN LanguageResourceGroup ON LanguageResourceAdditionalGroupAssignment.LanguageResourceGroupPID = LanguageResourceGroup.PID and LanguageResourceAdditionalGroupAssignment.ProjectPID = LanguageResourceGroup.ProjectPID where LanguageResourceAdditionalGroupAssignment.LanguageResourcePID = '" . $mtooldb->real_escape_string($param_LanguageResourceAdditionalGroupAssignment_LanguageResourcePID_where) . "' and LanguageResourceAdditionalGroupAssignment.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceAdditionalGroupAssignment_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceAdditionalGroupAssignmentData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourcePID = $thisline[2];
			$thisresult->LanguageResourceGroupPID = $thisline[3];
			$thisresult->LanguageResourceGroupName = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertLanguageResourceAdditionalGroupAssignment($LanguageResourceAdditionalGroupAssignmentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceAdditionalGroupAssignment ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceAdditionalGroupAssignment ==
		
		$last_sql_command_for_mtooldb = "insert into LanguageResourceAdditionalGroupAssignment (ProjectPID, LanguageResourcePID, LanguageResourceGroupPID) values('" . $mtooldb->real_escape_string($LanguageResourceAdditionalGroupAssignmentObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceAdditionalGroupAssignmentObj->LanguageResourcePID) . "', '" . $mtooldb->real_escape_string($LanguageResourceAdditionalGroupAssignmentObj->LanguageResourceGroupPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteLanguageResourceAdditionalGroupAssignment($LanguageResourceAdditionalGroupAssignmentObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceAdditionalGroupAssignment ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceAdditionalGroupAssignment ==
		
		$last_sql_command_for_mtooldb = "delete from LanguageResourceAdditionalGroupAssignment where LanguageResourceAdditionalGroupAssignment.LanguageResourcePID = '" . $mtooldb->real_escape_string($LanguageResourceAdditionalGroupAssignmentObj->LanguageResourcePID) . "' and LanguageResourceAdditionalGroupAssignment.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceAdditionalGroupAssignmentObj->ProjectPID) . "' and LanguageResourceAdditionalGroupAssignment.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($LanguageResourceAdditionalGroupAssignmentObj->LanguageResourceGroupPID) . "'";
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