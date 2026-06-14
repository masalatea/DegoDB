<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class CompareOutputAdditionalPathDBAccess
{
	public function __construct() {
	}
	
	public function GetCompareOutputAdditionalPathList($param_CompareOutputAdditionalPath_CompareOutputPID_where, $param_CompareOutputAdditionalPath_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCompareOutputAdditionalPathList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCompareOutputAdditionalPathList ==
		
		$last_sql_command_for_mtooldb = "select CompareOutputAdditionalPath.PID, CompareOutputAdditionalPath.CompareOutputPID, CompareOutputAdditionalPath.ProjectPID, CompareOutputAdditionalPath.PathA_DropboxBaseFolderPID, CompareOutputAdditionalPath.PathA, CompareOutputAdditionalPath.PathB_DropboxBaseFolderPID, CompareOutputAdditionalPath.PathB, CompareOutputAdditionalPath.IsSameFilenameOnly, DropboxBaseFolderA.Name, DropboxBaseFolderB.Name from CompareOutputAdditionalPath LEFT OUTER JOIN DropboxBaseFolder as DropboxBaseFolderA ON CompareOutputAdditionalPath.PathA_DropboxBaseFolderPID = DropboxBaseFolderA.PID LEFT OUTER JOIN DropboxBaseFolder as DropboxBaseFolderB ON CompareOutputAdditionalPath.PathB_DropboxBaseFolderPID = DropboxBaseFolderB.PID where CompareOutputAdditionalPath.CompareOutputPID = '" . $mtooldb->real_escape_string($param_CompareOutputAdditionalPath_CompareOutputPID_where) . "' and CompareOutputAdditionalPath.ProjectPID = '" . $mtooldb->real_escape_string($param_CompareOutputAdditionalPath_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new CompareOutputAdditionalPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->CompareOutputPID = $thisline[1];
			$thisresult->ProjectPID = $thisline[2];
			$thisresult->PathA_DropboxBaseFolderPID = $thisline[3];
			$thisresult->PathA = $thisline[4];
			$thisresult->PathB_DropboxBaseFolderPID = $thisline[5];
			$thisresult->PathB = $thisline[6];
			$thisresult->IsSameFilenameOnly = $thisline[7];
			$thisresult->DropboxBaseFolderAName = $thisline[8];
			$thisresult->DropboxBaseFolderBName = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetCompareOutputAdditionalPath($param_CompareOutputAdditionalPath_PID_where, $param_CompareOutputAdditionalPath_CompareOutputPID_where, $param_CompareOutputAdditionalPath_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCompareOutputAdditionalPath ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCompareOutputAdditionalPath ==
		
		$last_sql_command_for_mtooldb = "select CompareOutputAdditionalPath.PID, CompareOutputAdditionalPath.CompareOutputPID, CompareOutputAdditionalPath.ProjectPID, CompareOutputAdditionalPath.PathA_DropboxBaseFolderPID, CompareOutputAdditionalPath.PathA, CompareOutputAdditionalPath.PathB_DropboxBaseFolderPID, CompareOutputAdditionalPath.PathB, CompareOutputAdditionalPath.IsSameFilenameOnly, DropboxBaseFolderA.Name, DropboxBaseFolderB.Name from CompareOutputAdditionalPath LEFT OUTER JOIN DropboxBaseFolder as DropboxBaseFolderA ON CompareOutputAdditionalPath.PathA_DropboxBaseFolderPID = DropboxBaseFolderA.PID LEFT OUTER JOIN DropboxBaseFolder as DropboxBaseFolderB ON CompareOutputAdditionalPath.PathB_DropboxBaseFolderPID = DropboxBaseFolderB.PID where CompareOutputAdditionalPath.PID = '" . $mtooldb->real_escape_string($param_CompareOutputAdditionalPath_PID_where) . "' and CompareOutputAdditionalPath.CompareOutputPID = '" . $mtooldb->real_escape_string($param_CompareOutputAdditionalPath_CompareOutputPID_where) . "' and CompareOutputAdditionalPath.ProjectPID = '" . $mtooldb->real_escape_string($param_CompareOutputAdditionalPath_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new CompareOutputAdditionalPathData();
			$thisresult->PID = $thisline[0];
			$thisresult->CompareOutputPID = $thisline[1];
			$thisresult->ProjectPID = $thisline[2];
			$thisresult->PathA_DropboxBaseFolderPID = $thisline[3];
			$thisresult->PathA = $thisline[4];
			$thisresult->PathB_DropboxBaseFolderPID = $thisline[5];
			$thisresult->PathB = $thisline[6];
			$thisresult->IsSameFilenameOnly = $thisline[7];
			$thisresult->DropboxBaseFolderAName = $thisline[8];
			$thisresult->DropboxBaseFolderBName = $thisline[9];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertCompareOutputAdditionalPath($CompareOutputAdditionalPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertCompareOutputAdditionalPath ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertCompareOutputAdditionalPath ==
		
		$last_sql_command_for_mtooldb = "insert into CompareOutputAdditionalPath (CompareOutputPID, ProjectPID, PathA_DropboxBaseFolderPID, PathA, PathB_DropboxBaseFolderPID, PathB, IsSameFilenameOnly) values('" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->CompareOutputPID) . "', '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathA_DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathA) . "', '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathB_DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathB) . "', '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->IsSameFilenameOnly) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateCompareOutputAdditionalPath($CompareOutputAdditionalPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateCompareOutputAdditionalPath ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateCompareOutputAdditionalPath ==
		
		$last_sql_command_for_mtooldb = "update CompareOutputAdditionalPath SET PathA_DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathA_DropboxBaseFolderPID) . "', PathA = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathA) . "', PathB_DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathB_DropboxBaseFolderPID) . "', PathB = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PathB) . "', IsSameFilenameOnly = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->IsSameFilenameOnly) . "' where CompareOutputAdditionalPath.PID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PID) . "' and CompareOutputAdditionalPath.CompareOutputPID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->CompareOutputPID) . "' and CompareOutputAdditionalPath.ProjectPID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteCompareOutputAdditionalPath($CompareOutputAdditionalPathObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteCompareOutputAdditionalPath ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteCompareOutputAdditionalPath ==
		
		$last_sql_command_for_mtooldb = "delete from CompareOutputAdditionalPath where CompareOutputAdditionalPath.PID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->PID) . "' and CompareOutputAdditionalPath.CompareOutputPID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->CompareOutputPID) . "' and CompareOutputAdditionalPath.ProjectPID = '" . $mtooldb->real_escape_string($CompareOutputAdditionalPathObj->ProjectPID) . "'";
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