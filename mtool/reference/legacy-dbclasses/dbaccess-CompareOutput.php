<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class CompareOutputDBAccess
{
	public function __construct() {
	}
	
	public function GetCompareOutputList($param_CompareOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCompareOutputList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCompareOutputList ==
		
		$last_sql_command_for_mtooldb = "select CompareOutput.PID, CompareOutput.ProjectPID, CompareOutput.DropboxBaseFolderPID, CompareOutput.OutputFilePath, CompareOutput.OutputFileType, CompareOutput.ComparePath, CompareOutput.CompareToolFilePath from CompareOutput where CompareOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_CompareOutput_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new CompareOutputData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->OutputFilePath = $thisline[3];
			$thisresult->OutputFileType = $thisline[4];
			$thisresult->ComparePath = $thisline[5];
			$thisresult->CompareToolFilePath = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetCompareOutput($param_CompareOutput_PID_where, $param_CompareOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCompareOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCompareOutput ==
		
		$last_sql_command_for_mtooldb = "select CompareOutput.PID, CompareOutput.ProjectPID, CompareOutput.DropboxBaseFolderPID, CompareOutput.OutputFilePath, CompareOutput.OutputFileType, CompareOutput.ComparePath, CompareOutput.CompareToolFilePath from CompareOutput where CompareOutput.PID = '" . $mtooldb->real_escape_string($param_CompareOutput_PID_where) . "' and CompareOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_CompareOutput_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new CompareOutputData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->DropboxBaseFolderPID = $thisline[2];
			$thisresult->OutputFilePath = $thisline[3];
			$thisresult->OutputFileType = $thisline[4];
			$thisresult->ComparePath = $thisline[5];
			$thisresult->CompareToolFilePath = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertCompareOutput($CompareOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertCompareOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertCompareOutput ==
		
		$last_sql_command_for_mtooldb = "insert into CompareOutput (ProjectPID, DropboxBaseFolderPID, OutputFilePath, OutputFileType, ComparePath, CompareToolFilePath) values('" . $mtooldb->real_escape_string($CompareOutputObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($CompareOutputObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($CompareOutputObj->OutputFilePath) . "', '" . $mtooldb->real_escape_string($CompareOutputObj->OutputFileType) . "', '" . $mtooldb->real_escape_string($CompareOutputObj->ComparePath) . "', '" . $mtooldb->real_escape_string($CompareOutputObj->CompareToolFilePath) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateCompareOutput($CompareOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateCompareOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateCompareOutput ==
		
		$last_sql_command_for_mtooldb = "update CompareOutput SET DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($CompareOutputObj->DropboxBaseFolderPID) . "', OutputFilePath = '" . $mtooldb->real_escape_string($CompareOutputObj->OutputFilePath) . "', OutputFileType = '" . $mtooldb->real_escape_string($CompareOutputObj->OutputFileType) . "', ComparePath = '" . $mtooldb->real_escape_string($CompareOutputObj->ComparePath) . "', CompareToolFilePath = '" . $mtooldb->real_escape_string($CompareOutputObj->CompareToolFilePath) . "' where CompareOutput.PID = '" . $mtooldb->real_escape_string($CompareOutputObj->PID) . "' and CompareOutput.ProjectPID = '" . $mtooldb->real_escape_string($CompareOutputObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteCompareOutput($CompareOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteCompareOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteCompareOutput ==
		
		$last_sql_command_for_mtooldb = "delete from CompareOutput where CompareOutput.PID = '" . $mtooldb->real_escape_string($CompareOutputObj->PID) . "' and CompareOutput.ProjectPID = '" . $mtooldb->real_escape_string($CompareOutputObj->ProjectPID) . "'";
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