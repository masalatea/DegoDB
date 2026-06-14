<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceGroupDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceGroupList($param_LanguageResourceGroup_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceGroup.PID, LanguageResourceGroup.ProjectPID, LanguageResourceGroup.Name, LanguageResourceGroup.FunctionNamePrefix, LanguageResourceGroup.FunctionNameSuffix, LanguageResourceGroup.FilenameSuffixForPHP, LanguageResourceGroup.FilenameSuffix, LanguageResourceGroup.FilenameForXcode, LanguageResourceGroup.LastModifiedDT from LanguageResourceGroup where LanguageResourceGroup.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroup_ProjectPID_where) . "' order by LanguageResourceGroup.Name,LanguageResourceGroup.ProjectPID,LanguageResourceGroup.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->FunctionNamePrefix = $thisline[3];
			$thisresult->FunctionNameSuffix = $thisline[4];
			$thisresult->FilenameSuffixForPHP = $thisline[5];
			$thisresult->FilenameSuffix = $thisline[6];
			$thisresult->FilenameForXcode = $thisline[7];
			$thisresult->LastModifiedDT = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLanguageResourceGroup($param_LanguageResourceGroup_PID_where, $param_LanguageResourceGroup_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroup ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceGroup.PID, LanguageResourceGroup.ProjectPID, LanguageResourceGroup.Name, LanguageResourceGroup.FunctionNamePrefix, LanguageResourceGroup.FunctionNameSuffix, LanguageResourceGroup.FilenameSuffixForPHP, LanguageResourceGroup.FilenameSuffix, LanguageResourceGroup.FilenameForXcode, LanguageResourceGroup.LastModifiedDT from LanguageResourceGroup where LanguageResourceGroup.PID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroup_PID_where) . "' and LanguageResourceGroup.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroup_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->FunctionNamePrefix = $thisline[3];
			$thisresult->FunctionNameSuffix = $thisline[4];
			$thisresult->FilenameSuffixForPHP = $thisline[5];
			$thisresult->FilenameSuffix = $thisline[6];
			$thisresult->FilenameForXcode = $thisline[7];
			$thisresult->LastModifiedDT = $thisline[8];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertLanguageResourceGroup($LanguageResourceGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceGroup ==
		
		$last_sql_command_for_mtooldb = "insert into LanguageResourceGroup (ProjectPID, Name, FunctionNamePrefix, FunctionNameSuffix, FilenameSuffixForPHP, FilenameSuffix, FilenameForXcode) values('" . $mtooldb->real_escape_string($LanguageResourceGroupObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->Name) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FunctionNamePrefix) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FunctionNameSuffix) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FilenameSuffixForPHP) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FilenameSuffix) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FilenameForXcode) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLanguageResourceGroup($LanguageResourceGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLanguageResourceGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLanguageResourceGroup ==
		
		$last_sql_command_for_mtooldb = "update LanguageResourceGroup SET Name = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->Name) . "', FunctionNamePrefix = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FunctionNamePrefix) . "', FunctionNameSuffix = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FunctionNameSuffix) . "', FilenameSuffixForPHP = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FilenameSuffixForPHP) . "', FilenameSuffix = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FilenameSuffix) . "', FilenameForXcode = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->FilenameForXcode) . "' where LanguageResourceGroup.PID = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->PID) . "' and LanguageResourceGroup.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLastModifiedDT($param_LanguageResourceGroup_PID_where, $param_LanguageResourceGroup_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		
		$last_sql_command_for_mtooldb = "update LanguageResourceGroup SET LastModifiedDT = now() where LanguageResourceGroup.PID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroup_PID_where) . "' and LanguageResourceGroup.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroup_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteLanguageResourceGroup($LanguageResourceGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceGroup ==
		
		$last_sql_command_for_mtooldb = "delete from LanguageResourceGroup where LanguageResourceGroup.PID = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->PID) . "' and LanguageResourceGroup.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupObj->ProjectPID) . "'";
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