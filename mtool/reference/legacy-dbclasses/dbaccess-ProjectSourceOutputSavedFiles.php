<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectSourceOutputSavedFilesDBAccess
{
	public function __construct() {
	}
	
	public function InsertProjectSourceOutputSavedFiles($ProjectSourceOutputSavedFilesObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectSourceOutputSavedFiles ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectSourceOutputSavedFiles ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectSourceOutputSavedFiles (BuildTokenPID, ProjectPID, ProjectSourceOutputPID, TargetDropboxBaseFolderPID, FilePathOnTarget, SourceText) values('" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->BuildTokenPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->ProjectSourceOutputPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->TargetDropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->FilePathOnTarget) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->SourceText) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProjectSourceOutputSavedFiles($ProjectSourceOutputSavedFilesObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProjectSourceOutputSavedFiles ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProjectSourceOutputSavedFiles ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectSourceOutputSavedFiles where ProjectSourceOutputSavedFiles.ProjectPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->ProjectPID) . "' and ProjectSourceOutputSavedFiles.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->ProjectSourceOutputPID) . "' and ProjectSourceOutputSavedFiles.TargetDropboxBaseFolderPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->TargetDropboxBaseFolderPID) . "' and ProjectSourceOutputSavedFiles.FilePathOnTarget = '" . $mtooldb->real_escape_string($ProjectSourceOutputSavedFilesObj->FilePathOnTarget) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteOld()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteOld ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteOld ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectSourceOutputSavedFiles where (ProjectSourceOutputSavedFiles.BuildTokenPID not in (select PID from BuildToken) or ProjectSourceOutputSavedFiles.ProjectPID not in (select PID from Project) or ProjectSourceOutputSavedFiles.ProjectSourceOutputPID not in (select PID from ProjectSourceOutput) or ProjectSourceOutputSavedFiles.TargetDropboxBaseFolderPID not in (select PID from DropboxBaseFolder))";
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