<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceGroupProjectSourceOutputDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceGroupProjectSourceOutputList($param_LanguageResourceGroupProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupProjectSourceOutputList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupProjectSourceOutputList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceGroup.Name, LanguageResourceGroup.LastModifiedDT, LanguageResourceGroup.FunctionNamePrefix, LanguageResourceGroup.FunctionNameSuffix, LanguageResourceGroup.FilenameSuffix, LanguageResourceGroup.FilenameForXcode, LanguageResourceGroup.FilenameSuffixForPHP, LanguageResourceGroupProjectSourceOutput.PID, LanguageResourceGroupProjectSourceOutput.ProjectPID, LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID, LanguageResourceGroupProjectSourceOutput.ProjectSourceOutputPID from LanguageResourceGroup join LanguageResourceGroupProjectSourceOutput where LanguageResourceGroupProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupProjectSourceOutput_ProjectPID_where) . "' and LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID = LanguageResourceGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceGroupProjectSourceOutputData();
			$thisresult->LanguageResourceGroupName = $thisline[0];
			$thisresult->LanguageResourceGroupLastModifiedDT = $thisline[1];
			$thisresult->LanguageResourceGroupFunctionNamePrefix = $thisline[2];
			$thisresult->LanguageResourceGroupFunctionNameSuffix = $thisline[3];
			$thisresult->LanguageResourceGroupFilenameSuffix = $thisline[4];
			$thisresult->LanguageResourceGroupFilenameForXcode = $thisline[5];
			$thisresult->LanguageResourceGroupFilenameSuffixForPHP = $thisline[6];
			$thisresult->PID = $thisline[7];
			$thisresult->ProjectPID = $thisline[8];
			$thisresult->LanguageResourceGroupPID = $thisline[9];
			$thisresult->ProjectSourceOutputPID = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLanguageResourceGroupProjectSourceOutputForTheGroupList($param_LanguageResourceGroupProjectSourceOutput_LanguageResourceGroupPID_where, $param_LanguageResourceGroupProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupProjectSourceOutputForTheGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupProjectSourceOutputForTheGroupList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceGroup.Name, LanguageResourceGroup.LastModifiedDT, LanguageResourceGroup.FunctionNamePrefix, LanguageResourceGroup.FunctionNameSuffix, LanguageResourceGroup.FilenameSuffix, LanguageResourceGroup.FilenameForXcode, LanguageResourceGroup.FilenameSuffixForPHP, LanguageResourceGroupProjectSourceOutput.PID, LanguageResourceGroupProjectSourceOutput.ProjectPID, LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID, LanguageResourceGroupProjectSourceOutput.ProjectSourceOutputPID from LanguageResourceGroup join LanguageResourceGroupProjectSourceOutput where LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupProjectSourceOutput_LanguageResourceGroupPID_where) . "' and LanguageResourceGroupProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupProjectSourceOutput_ProjectPID_where) . "' and LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID = LanguageResourceGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceGroupProjectSourceOutputData();
			$thisresult->LanguageResourceGroupName = $thisline[0];
			$thisresult->LanguageResourceGroupLastModifiedDT = $thisline[1];
			$thisresult->LanguageResourceGroupFunctionNamePrefix = $thisline[2];
			$thisresult->LanguageResourceGroupFunctionNameSuffix = $thisline[3];
			$thisresult->LanguageResourceGroupFilenameSuffix = $thisline[4];
			$thisresult->LanguageResourceGroupFilenameForXcode = $thisline[5];
			$thisresult->LanguageResourceGroupFilenameSuffixForPHP = $thisline[6];
			$thisresult->PID = $thisline[7];
			$thisresult->ProjectPID = $thisline[8];
			$thisresult->LanguageResourceGroupPID = $thisline[9];
			$thisresult->ProjectSourceOutputPID = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLanguageResourceGroupProjectSourceOutput($param_LanguageResourceGroupProjectSourceOutput_PID_where, $param_LanguageResourceGroupProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceGroup.Name, LanguageResourceGroup.LastModifiedDT, LanguageResourceGroup.FunctionNamePrefix, LanguageResourceGroup.FunctionNameSuffix, LanguageResourceGroup.FilenameSuffix, LanguageResourceGroup.FilenameForXcode, LanguageResourceGroup.FilenameSuffixForPHP, LanguageResourceGroupProjectSourceOutput.PID, LanguageResourceGroupProjectSourceOutput.ProjectPID, LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID, LanguageResourceGroupProjectSourceOutput.ProjectSourceOutputPID from LanguageResourceGroup join LanguageResourceGroupProjectSourceOutput where LanguageResourceGroupProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupProjectSourceOutput_PID_where) . "' and LanguageResourceGroupProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupProjectSourceOutput_ProjectPID_where) . "' and LanguageResourceGroupProjectSourceOutput.LanguageResourceGroupPID = LanguageResourceGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceGroupProjectSourceOutputData();
			$thisresult->LanguageResourceGroupName = $thisline[0];
			$thisresult->LanguageResourceGroupLastModifiedDT = $thisline[1];
			$thisresult->LanguageResourceGroupFunctionNamePrefix = $thisline[2];
			$thisresult->LanguageResourceGroupFunctionNameSuffix = $thisline[3];
			$thisresult->LanguageResourceGroupFilenameSuffix = $thisline[4];
			$thisresult->LanguageResourceGroupFilenameForXcode = $thisline[5];
			$thisresult->LanguageResourceGroupFilenameSuffixForPHP = $thisline[6];
			$thisresult->PID = $thisline[7];
			$thisresult->ProjectPID = $thisline[8];
			$thisresult->LanguageResourceGroupPID = $thisline[9];
			$thisresult->ProjectSourceOutputPID = $thisline[10];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertLanguageResourceGroupProjectSourceOutput($LanguageResourceGroupProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceGroupProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceGroupProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "insert into LanguageResourceGroupProjectSourceOutput (ProjectPID, LanguageResourceGroupPID, ProjectSourceOutputPID) values('" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->LanguageResourceGroupPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->ProjectSourceOutputPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLanguageResourceGroupProjectSourceOutput($LanguageResourceGroupProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLanguageResourceGroupProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLanguageResourceGroupProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "update LanguageResourceGroupProjectSourceOutput SET LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->LanguageResourceGroupPID) . "', ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->ProjectSourceOutputPID) . "' where LanguageResourceGroupProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->PID) . "' and LanguageResourceGroupProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteLanguageResourceGroupProjectSourceOutput($LanguageResourceGroupProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceGroupProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceGroupProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "delete from LanguageResourceGroupProjectSourceOutput where LanguageResourceGroupProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->PID) . "' and LanguageResourceGroupProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupProjectSourceOutputObj->ProjectPID) . "'";
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