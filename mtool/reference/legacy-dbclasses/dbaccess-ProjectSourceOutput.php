<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectSourceOutputDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectSourceOutputList($param_ProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectSourceOutputList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectSourceOutputList ==
		
		$last_sql_command_for_mtooldb = "select ProjectSourceOutput.ProjectPID, ProjectSourceOutput.PID, ProjectSourceOutput.ProgramLanguage, ProjectSourceOutput.CustomFileExtention, ProjectSourceOutput.ClassType, ProjectSourceOutput.ReleaseTargetType, ProjectSourceOutput.SourceTemplateDir, ProjectSourceOutput.SourceOutputDir, ProjectSourceOutput.SourceTempOutputDir, ProjectSourceOutput.ProxyBaseURL, ProjectSourceOutput.UnitTestTemplateDir, ProjectSourceOutput.UnitTestOutputDir, ProjectSourceOutput.AutoloadFilenameSuffix, ProjectSourceOutput.TargetServerProjectSourceOutputPID, ProjectSourceOutput.ProjectSourceOutputListOrder, ProjectSourceOutput.SourceTextCharCode, ProjectSourceOutput.CSNameSpace, ProjectSourceOutput.JavaPackageName, ProjectSourceOutput.DropboxBaseFolderPID, ProjectSourceOutput.AutoLoadFilePathForPHP, ProjectSourceOutput.JavaFunctionType, ProjectSourceOutput.DotNetLanguageResourceType, TargtServerPSO.ProgramLanguage, TargtServerPSO.CustomFileExtention, TargtServerPSO.ProxyBaseURL, DropboxBaseFolder.Name from ProjectSourceOutput LEFT OUTER JOIN ProjectSourceOutput as TargtServerPSO ON ProjectSourceOutput.TargetServerProjectSourceOutputPID = TargtServerPSO.PID and ProjectSourceOutput.ProjectPID = TargtServerPSO.ProjectPID LEFT OUTER JOIN DropboxBaseFolder ON ProjectSourceOutput.DropboxBaseFolderPID = DropboxBaseFolder.PID where ProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectSourceOutput_ProjectPID_where) . "' order by ProjectSourceOutput.ProjectSourceOutputListOrder,ProjectSourceOutput.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectSourceOutputData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->ProgramLanguage = $thisline[2];
			$thisresult->CustomFileExtention = $thisline[3];
			$thisresult->ClassType = $thisline[4];
			$thisresult->ReleaseTargetType = $thisline[5];
			$thisresult->SourceTemplateDir = $thisline[6];
			$thisresult->SourceOutputDir = $thisline[7];
			$thisresult->SourceTempOutputDir = $thisline[8];
			$thisresult->ProxyBaseURL = $thisline[9];
			$thisresult->UnitTestTemplateDir = $thisline[10];
			$thisresult->UnitTestOutputDir = $thisline[11];
			$thisresult->AutoloadFilenameSuffix = $thisline[12];
			$thisresult->TargetServerProjectSourceOutputPID = $thisline[13];
			$thisresult->ProjectSourceOutputListOrder = $thisline[14];
			$thisresult->SourceTextCharCode = $thisline[15];
			$thisresult->CSNameSpace = $thisline[16];
			$thisresult->JavaPackageName = $thisline[17];
			$thisresult->DropboxBaseFolderPID = $thisline[18];
			$thisresult->AutoLoadFilePathForPHP = $thisline[19];
			$thisresult->JavaFunctionType = $thisline[20];
			$thisresult->DotNetLanguageResourceType = $thisline[21];
			$thisresult->TargtServerPSOProgramLanguage = $thisline[22];
			$thisresult->TargtServerPSOCustomFileExtention = $thisline[23];
			$thisresult->TargtServerPSOProxyBaseURL = $thisline[24];
			$thisresult->DropboxBaseFolderName = $thisline[25];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectSourceOutput($param_ProjectSourceOutput_PID_where, $param_ProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "select ProjectSourceOutput.ProjectPID, ProjectSourceOutput.PID, ProjectSourceOutput.ProgramLanguage, ProjectSourceOutput.CustomFileExtention, ProjectSourceOutput.ClassType, ProjectSourceOutput.ReleaseTargetType, ProjectSourceOutput.SourceTemplateDir, ProjectSourceOutput.SourceOutputDir, ProjectSourceOutput.SourceTempOutputDir, ProjectSourceOutput.ProxyBaseURL, ProjectSourceOutput.UnitTestTemplateDir, ProjectSourceOutput.UnitTestOutputDir, ProjectSourceOutput.AutoloadFilenameSuffix, ProjectSourceOutput.TargetServerProjectSourceOutputPID, ProjectSourceOutput.ProjectSourceOutputListOrder, ProjectSourceOutput.SourceTextCharCode, ProjectSourceOutput.CSNameSpace, ProjectSourceOutput.JavaPackageName, ProjectSourceOutput.DropboxBaseFolderPID, ProjectSourceOutput.AutoLoadFilePathForPHP, ProjectSourceOutput.JavaFunctionType, ProjectSourceOutput.DotNetLanguageResourceType, TargtServerPSO.ProgramLanguage, TargtServerPSO.CustomFileExtention, TargtServerPSO.ProxyBaseURL, DropboxBaseFolder.Name from ProjectSourceOutput LEFT OUTER JOIN ProjectSourceOutput as TargtServerPSO ON ProjectSourceOutput.TargetServerProjectSourceOutputPID = TargtServerPSO.PID and ProjectSourceOutput.ProjectPID = TargtServerPSO.ProjectPID LEFT OUTER JOIN DropboxBaseFolder ON ProjectSourceOutput.DropboxBaseFolderPID = DropboxBaseFolder.PID where ProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($param_ProjectSourceOutput_PID_where) . "' and ProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectSourceOutput_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectSourceOutputData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->ProgramLanguage = $thisline[2];
			$thisresult->CustomFileExtention = $thisline[3];
			$thisresult->ClassType = $thisline[4];
			$thisresult->ReleaseTargetType = $thisline[5];
			$thisresult->SourceTemplateDir = $thisline[6];
			$thisresult->SourceOutputDir = $thisline[7];
			$thisresult->SourceTempOutputDir = $thisline[8];
			$thisresult->ProxyBaseURL = $thisline[9];
			$thisresult->UnitTestTemplateDir = $thisline[10];
			$thisresult->UnitTestOutputDir = $thisline[11];
			$thisresult->AutoloadFilenameSuffix = $thisline[12];
			$thisresult->TargetServerProjectSourceOutputPID = $thisline[13];
			$thisresult->ProjectSourceOutputListOrder = $thisline[14];
			$thisresult->SourceTextCharCode = $thisline[15];
			$thisresult->CSNameSpace = $thisline[16];
			$thisresult->JavaPackageName = $thisline[17];
			$thisresult->DropboxBaseFolderPID = $thisline[18];
			$thisresult->AutoLoadFilePathForPHP = $thisline[19];
			$thisresult->JavaFunctionType = $thisline[20];
			$thisresult->DotNetLanguageResourceType = $thisline[21];
			$thisresult->TargtServerPSOProgramLanguage = $thisline[22];
			$thisresult->TargtServerPSOCustomFileExtention = $thisline[23];
			$thisresult->TargtServerPSOProxyBaseURL = $thisline[24];
			$thisresult->DropboxBaseFolderName = $thisline[25];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertProjectSourceOutput($ProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectSourceOutput (ProjectPID, ProgramLanguage, CustomFileExtention, ClassType, ReleaseTargetType, SourceTemplateDir, SourceOutputDir, SourceTempOutputDir, ProxyBaseURL, UnitTestTemplateDir, UnitTestOutputDir, AutoloadFilenameSuffix, TargetServerProjectSourceOutputPID, ProjectSourceOutputListOrder, SourceTextCharCode, CSNameSpace, JavaPackageName, DropboxBaseFolderPID, AutoLoadFilePathForPHP, JavaFunctionType, DotNetLanguageResourceType) values('" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProgramLanguage) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->CustomFileExtention) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ClassType) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ReleaseTargetType) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceTemplateDir) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceOutputDir) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceTempOutputDir) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProxyBaseURL) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->UnitTestTemplateDir) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->UnitTestOutputDir) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->AutoloadFilenameSuffix) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->TargetServerProjectSourceOutputPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProjectSourceOutputListOrder) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceTextCharCode) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->CSNameSpace) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->JavaPackageName) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->AutoLoadFilePathForPHP) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->JavaFunctionType) . "', '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->DotNetLanguageResourceType) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectSourceOutput($ProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "update ProjectSourceOutput SET ProgramLanguage = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProgramLanguage) . "', CustomFileExtention = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->CustomFileExtention) . "', ClassType = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ClassType) . "', ReleaseTargetType = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ReleaseTargetType) . "', SourceTemplateDir = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceTemplateDir) . "', SourceOutputDir = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceOutputDir) . "', SourceTempOutputDir = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceTempOutputDir) . "', ProxyBaseURL = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProxyBaseURL) . "', UnitTestTemplateDir = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->UnitTestTemplateDir) . "', UnitTestOutputDir = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->UnitTestOutputDir) . "', AutoloadFilenameSuffix = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->AutoloadFilenameSuffix) . "', TargetServerProjectSourceOutputPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->TargetServerProjectSourceOutputPID) . "', SourceTextCharCode = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->SourceTextCharCode) . "', CSNameSpace = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->CSNameSpace) . "', JavaPackageName = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->JavaPackageName) . "', DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->DropboxBaseFolderPID) . "', AutoLoadFilePathForPHP = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->AutoLoadFilePathForPHP) . "', JavaFunctionType = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->JavaFunctionType) . "', DotNetLanguageResourceType = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->DotNetLanguageResourceType) . "' where ProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->PID) . "' and ProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectSourceOutputListOrder($param_ProjectSourceOutput_ProjectSourceOutputListOrder_update, $param_ProjectSourceOutput_PID_where, $param_ProjectSourceOutput_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectSourceOutputListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectSourceOutputListOrder ==
		
		$last_sql_command_for_mtooldb = "update ProjectSourceOutput SET ProjectSourceOutputListOrder = '" . $mtooldb->real_escape_string($param_ProjectSourceOutput_ProjectSourceOutputListOrder_update) . "' where ProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($param_ProjectSourceOutput_PID_where) . "' and ProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectSourceOutput_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProjectSourceOutput($ProjectSourceOutputObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectSourceOutput where ProjectSourceOutput.PID = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->PID) . "' and ProjectSourceOutput.ProjectPID = '" . $mtooldb->real_escape_string($ProjectSourceOutputObj->ProjectPID) . "'";
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