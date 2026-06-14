<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectGroupDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectGroupList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectGroupList ==
		
		$last_sql_command_for_mtooldb = "select ProjectGroup.PID, ProjectGroup.username, ProjectGroup.ProjectGroupType, ProjectGroup.UniqueDirName, ProjectGroup.UniqueDBName, ProjectGroup.UniqueDBUserName, ProjectGroup.Name, ProjectGroup.CreatedDateTime, ProjectGroup.CreateSettingGroup, ProjectGroup.SettingGroupPID, ProjectGroup.CreateSettingGroupUser, ProjectGroup.SettingGroupUserPID, ProjectGroup.CreateServer, ProjectGroup.ServerPID, ProjectGroup.CreateMainServer, ProjectGroup.MainServerPID, ProjectGroup.CreateDBConnection, ProjectGroup.DBConnectionPID, ProjectGroup.CreateDBUser, ProjectGroup.DBUserPID, ProjectGroup.CreateDBUserClientHost, ProjectGroup.DBUserClientHostPID, ProjectGroup.CreateDBUserClientHostFromMain, ProjectGroup.DBUserClientHostPIDFromMain, ProjectGroup.CreateDropboxBaseFolder, ProjectGroup.DropboxBaseFolderPID, ProjectGroup.CreateApacheSetting, ProjectGroup.ApacheSettingPID, ProjectGroup.CreateApacheHostSetting, ProjectGroup.ApacheHostSettingPID, ProjectGroup.CreateBuildApacheHostSetting, ProjectGroup.CreateUploadServer, ProjectGroup.UploadServerPID, ProjectGroup.CreateUploadServerPath, ProjectGroup.UploadServerPathPID, ProjectGroup.CreateUploadServerPathForBeta, ProjectGroup.UploadServerPathPIDForBeta, ProjectGroup.CreateUploadGroup, ProjectGroup.UploadGroupPID, ProjectGroup.CreateUploadGroupAssignedServerPath, ProjectGroup.UploadGroupAssignedServerPathPID, ProjectGroup.CreateUploadGroupAssignedServerPathForBeta, ProjectGroup.UploadGroupAssignedServerPathPIDForBeta, ProjectGroup.CreateUploadGroupAssignedUser, ProjectGroup.UploadGroupAssignedUserPID, ProjectGroup.CreateProject, ProjectGroup.ProjectPID, ProjectGroup.CreateProjectSourceOutputForDA, ProjectGroup.DAProjectSourceOutputPID, ProjectGroup.CreateProjectSourceOutputForProxyServer, ProjectGroup.ProxyServerProjectSourceOutputPID, ProjectGroup.CreateProjectSourceOutputForDAForBeta, ProjectGroup.DAProjectSourceOutputPIDForBeta, ProjectGroup.CreateProjectSourceOutputForProxyServerForBeta, ProjectGroup.ProxyServerProjectSourceOutputPIDForBeta, ProjectGroup.CreateDatabase, SettingGroup.Name, Project.name from ProjectGroup LEFT OUTER JOIN SettingGroup ON ProjectGroup.SettingGroupPID = SettingGroup.PID LEFT OUTER JOIN Project ON ProjectGroup.ProjectPID = Project.PID order by ProjectGroup.CreatedDateTime";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->username = $thisline[1];
			$thisresult->ProjectGroupType = $thisline[2];
			$thisresult->UniqueDirName = $thisline[3];
			$thisresult->UniqueDBName = $thisline[4];
			$thisresult->UniqueDBUserName = $thisline[5];
			$thisresult->Name = $thisline[6];
			$thisresult->CreatedDateTime = $thisline[7];
			$thisresult->CreateSettingGroup = $thisline[8];
			$thisresult->SettingGroupPID = $thisline[9];
			$thisresult->CreateSettingGroupUser = $thisline[10];
			$thisresult->SettingGroupUserPID = $thisline[11];
			$thisresult->CreateServer = $thisline[12];
			$thisresult->ServerPID = $thisline[13];
			$thisresult->CreateMainServer = $thisline[14];
			$thisresult->MainServerPID = $thisline[15];
			$thisresult->CreateDBConnection = $thisline[16];
			$thisresult->DBConnectionPID = $thisline[17];
			$thisresult->CreateDBUser = $thisline[18];
			$thisresult->DBUserPID = $thisline[19];
			$thisresult->CreateDBUserClientHost = $thisline[20];
			$thisresult->DBUserClientHostPID = $thisline[21];
			$thisresult->CreateDBUserClientHostFromMain = $thisline[22];
			$thisresult->DBUserClientHostPIDFromMain = $thisline[23];
			$thisresult->CreateDropboxBaseFolder = $thisline[24];
			$thisresult->DropboxBaseFolderPID = $thisline[25];
			$thisresult->CreateApacheSetting = $thisline[26];
			$thisresult->ApacheSettingPID = $thisline[27];
			$thisresult->CreateApacheHostSetting = $thisline[28];
			$thisresult->ApacheHostSettingPID = $thisline[29];
			$thisresult->CreateBuildApacheHostSetting = $thisline[30];
			$thisresult->CreateUploadServer = $thisline[31];
			$thisresult->UploadServerPID = $thisline[32];
			$thisresult->CreateUploadServerPath = $thisline[33];
			$thisresult->UploadServerPathPID = $thisline[34];
			$thisresult->CreateUploadServerPathForBeta = $thisline[35];
			$thisresult->UploadServerPathPIDForBeta = $thisline[36];
			$thisresult->CreateUploadGroup = $thisline[37];
			$thisresult->UploadGroupPID = $thisline[38];
			$thisresult->CreateUploadGroupAssignedServerPath = $thisline[39];
			$thisresult->UploadGroupAssignedServerPathPID = $thisline[40];
			$thisresult->CreateUploadGroupAssignedServerPathForBeta = $thisline[41];
			$thisresult->UploadGroupAssignedServerPathPIDForBeta = $thisline[42];
			$thisresult->CreateUploadGroupAssignedUser = $thisline[43];
			$thisresult->UploadGroupAssignedUserPID = $thisline[44];
			$thisresult->CreateProject = $thisline[45];
			$thisresult->ProjectPID = $thisline[46];
			$thisresult->CreateProjectSourceOutputForDA = $thisline[47];
			$thisresult->DAProjectSourceOutputPID = $thisline[48];
			$thisresult->CreateProjectSourceOutputForProxyServer = $thisline[49];
			$thisresult->ProxyServerProjectSourceOutputPID = $thisline[50];
			$thisresult->CreateProjectSourceOutputForDAForBeta = $thisline[51];
			$thisresult->DAProjectSourceOutputPIDForBeta = $thisline[52];
			$thisresult->CreateProjectSourceOutputForProxyServerForBeta = $thisline[53];
			$thisresult->ProxyServerProjectSourceOutputPIDForBeta = $thisline[54];
			$thisresult->CreateDatabase = $thisline[55];
			$thisresult->SettingGroupName = $thisline[56];
			$thisresult->Projectname = $thisline[57];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectGroup($param_ProjectGroup_username_where, $param_ProjectGroup_ProjectGroupType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectGroup ==
		
		$last_sql_command_for_mtooldb = "select ProjectGroup.PID, ProjectGroup.username, ProjectGroup.ProjectGroupType, ProjectGroup.UniqueDirName, ProjectGroup.UniqueDBName, ProjectGroup.UniqueDBUserName, ProjectGroup.Name, ProjectGroup.CreatedDateTime, ProjectGroup.CreateSettingGroup, ProjectGroup.SettingGroupPID, ProjectGroup.CreateSettingGroupUser, ProjectGroup.SettingGroupUserPID, ProjectGroup.CreateServer, ProjectGroup.ServerPID, ProjectGroup.CreateMainServer, ProjectGroup.MainServerPID, ProjectGroup.CreateDBConnection, ProjectGroup.DBConnectionPID, ProjectGroup.CreateDBUser, ProjectGroup.DBUserPID, ProjectGroup.CreateDBUserClientHost, ProjectGroup.DBUserClientHostPID, ProjectGroup.CreateDBUserClientHostFromMain, ProjectGroup.DBUserClientHostPIDFromMain, ProjectGroup.CreateDropboxBaseFolder, ProjectGroup.DropboxBaseFolderPID, ProjectGroup.CreateApacheSetting, ProjectGroup.ApacheSettingPID, ProjectGroup.CreateApacheHostSetting, ProjectGroup.ApacheHostSettingPID, ProjectGroup.CreateBuildApacheHostSetting, ProjectGroup.CreateUploadServer, ProjectGroup.UploadServerPID, ProjectGroup.CreateUploadServerPath, ProjectGroup.UploadServerPathPID, ProjectGroup.CreateUploadServerPathForBeta, ProjectGroup.UploadServerPathPIDForBeta, ProjectGroup.CreateUploadGroup, ProjectGroup.UploadGroupPID, ProjectGroup.CreateUploadGroupAssignedServerPath, ProjectGroup.UploadGroupAssignedServerPathPID, ProjectGroup.CreateUploadGroupAssignedServerPathForBeta, ProjectGroup.UploadGroupAssignedServerPathPIDForBeta, ProjectGroup.CreateUploadGroupAssignedUser, ProjectGroup.UploadGroupAssignedUserPID, ProjectGroup.CreateProject, ProjectGroup.ProjectPID, ProjectGroup.CreateProjectSourceOutputForDA, ProjectGroup.DAProjectSourceOutputPID, ProjectGroup.CreateProjectSourceOutputForProxyServer, ProjectGroup.ProxyServerProjectSourceOutputPID, ProjectGroup.CreateProjectSourceOutputForDAForBeta, ProjectGroup.DAProjectSourceOutputPIDForBeta, ProjectGroup.CreateProjectSourceOutputForProxyServerForBeta, ProjectGroup.ProxyServerProjectSourceOutputPIDForBeta, ProjectGroup.CreateDatabase, SettingGroup.Name, Project.name from ProjectGroup LEFT OUTER JOIN SettingGroup ON ProjectGroup.SettingGroupPID = SettingGroup.PID LEFT OUTER JOIN Project ON ProjectGroup.ProjectPID = Project.PID where ProjectGroup.username = '" . $mtooldb->real_escape_string($param_ProjectGroup_username_where) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($param_ProjectGroup_ProjectGroupType_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->username = $thisline[1];
			$thisresult->ProjectGroupType = $thisline[2];
			$thisresult->UniqueDirName = $thisline[3];
			$thisresult->UniqueDBName = $thisline[4];
			$thisresult->UniqueDBUserName = $thisline[5];
			$thisresult->Name = $thisline[6];
			$thisresult->CreatedDateTime = $thisline[7];
			$thisresult->CreateSettingGroup = $thisline[8];
			$thisresult->SettingGroupPID = $thisline[9];
			$thisresult->CreateSettingGroupUser = $thisline[10];
			$thisresult->SettingGroupUserPID = $thisline[11];
			$thisresult->CreateServer = $thisline[12];
			$thisresult->ServerPID = $thisline[13];
			$thisresult->CreateMainServer = $thisline[14];
			$thisresult->MainServerPID = $thisline[15];
			$thisresult->CreateDBConnection = $thisline[16];
			$thisresult->DBConnectionPID = $thisline[17];
			$thisresult->CreateDBUser = $thisline[18];
			$thisresult->DBUserPID = $thisline[19];
			$thisresult->CreateDBUserClientHost = $thisline[20];
			$thisresult->DBUserClientHostPID = $thisline[21];
			$thisresult->CreateDBUserClientHostFromMain = $thisline[22];
			$thisresult->DBUserClientHostPIDFromMain = $thisline[23];
			$thisresult->CreateDropboxBaseFolder = $thisline[24];
			$thisresult->DropboxBaseFolderPID = $thisline[25];
			$thisresult->CreateApacheSetting = $thisline[26];
			$thisresult->ApacheSettingPID = $thisline[27];
			$thisresult->CreateApacheHostSetting = $thisline[28];
			$thisresult->ApacheHostSettingPID = $thisline[29];
			$thisresult->CreateBuildApacheHostSetting = $thisline[30];
			$thisresult->CreateUploadServer = $thisline[31];
			$thisresult->UploadServerPID = $thisline[32];
			$thisresult->CreateUploadServerPath = $thisline[33];
			$thisresult->UploadServerPathPID = $thisline[34];
			$thisresult->CreateUploadServerPathForBeta = $thisline[35];
			$thisresult->UploadServerPathPIDForBeta = $thisline[36];
			$thisresult->CreateUploadGroup = $thisline[37];
			$thisresult->UploadGroupPID = $thisline[38];
			$thisresult->CreateUploadGroupAssignedServerPath = $thisline[39];
			$thisresult->UploadGroupAssignedServerPathPID = $thisline[40];
			$thisresult->CreateUploadGroupAssignedServerPathForBeta = $thisline[41];
			$thisresult->UploadGroupAssignedServerPathPIDForBeta = $thisline[42];
			$thisresult->CreateUploadGroupAssignedUser = $thisline[43];
			$thisresult->UploadGroupAssignedUserPID = $thisline[44];
			$thisresult->CreateProject = $thisline[45];
			$thisresult->ProjectPID = $thisline[46];
			$thisresult->CreateProjectSourceOutputForDA = $thisline[47];
			$thisresult->DAProjectSourceOutputPID = $thisline[48];
			$thisresult->CreateProjectSourceOutputForProxyServer = $thisline[49];
			$thisresult->ProxyServerProjectSourceOutputPID = $thisline[50];
			$thisresult->CreateProjectSourceOutputForDAForBeta = $thisline[51];
			$thisresult->DAProjectSourceOutputPIDForBeta = $thisline[52];
			$thisresult->CreateProjectSourceOutputForProxyServerForBeta = $thisline[53];
			$thisresult->ProxyServerProjectSourceOutputPIDForBeta = $thisline[54];
			$thisresult->CreateDatabase = $thisline[55];
			$thisresult->SettingGroupName = $thisline[56];
			$thisresult->Projectname = $thisline[57];
			return $thisresult;
		}
		return NULL;
	}
	public function GetProjectGroupByUserAndProject($param_ProjectGroup_username_where, $param_ProjectGroup_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectGroupByUserAndProject ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectGroupByUserAndProject ==
		
		$last_sql_command_for_mtooldb = "select ProjectGroup.PID, ProjectGroup.username, ProjectGroup.ProjectGroupType, ProjectGroup.UniqueDirName, ProjectGroup.UniqueDBName, ProjectGroup.UniqueDBUserName, ProjectGroup.Name, ProjectGroup.CreatedDateTime, ProjectGroup.CreateSettingGroup, ProjectGroup.SettingGroupPID, ProjectGroup.CreateSettingGroupUser, ProjectGroup.SettingGroupUserPID, ProjectGroup.CreateServer, ProjectGroup.ServerPID, ProjectGroup.CreateMainServer, ProjectGroup.MainServerPID, ProjectGroup.CreateDBConnection, ProjectGroup.DBConnectionPID, ProjectGroup.CreateDBUser, ProjectGroup.DBUserPID, ProjectGroup.CreateDBUserClientHost, ProjectGroup.DBUserClientHostPID, ProjectGroup.CreateDBUserClientHostFromMain, ProjectGroup.DBUserClientHostPIDFromMain, ProjectGroup.CreateDropboxBaseFolder, ProjectGroup.DropboxBaseFolderPID, ProjectGroup.CreateApacheSetting, ProjectGroup.ApacheSettingPID, ProjectGroup.CreateApacheHostSetting, ProjectGroup.ApacheHostSettingPID, ProjectGroup.CreateBuildApacheHostSetting, ProjectGroup.CreateUploadServer, ProjectGroup.UploadServerPID, ProjectGroup.CreateUploadServerPath, ProjectGroup.UploadServerPathPID, ProjectGroup.CreateUploadServerPathForBeta, ProjectGroup.UploadServerPathPIDForBeta, ProjectGroup.CreateUploadGroup, ProjectGroup.UploadGroupPID, ProjectGroup.CreateUploadGroupAssignedServerPath, ProjectGroup.UploadGroupAssignedServerPathPID, ProjectGroup.CreateUploadGroupAssignedServerPathForBeta, ProjectGroup.UploadGroupAssignedServerPathPIDForBeta, ProjectGroup.CreateUploadGroupAssignedUser, ProjectGroup.UploadGroupAssignedUserPID, ProjectGroup.CreateProject, ProjectGroup.ProjectPID, ProjectGroup.CreateProjectSourceOutputForDA, ProjectGroup.DAProjectSourceOutputPID, ProjectGroup.CreateProjectSourceOutputForProxyServer, ProjectGroup.ProxyServerProjectSourceOutputPID, ProjectGroup.CreateProjectSourceOutputForDAForBeta, ProjectGroup.DAProjectSourceOutputPIDForBeta, ProjectGroup.CreateProjectSourceOutputForProxyServerForBeta, ProjectGroup.ProxyServerProjectSourceOutputPIDForBeta, ProjectGroup.CreateDatabase, SettingGroup.Name, Project.name from ProjectGroup LEFT OUTER JOIN SettingGroup ON ProjectGroup.SettingGroupPID = SettingGroup.PID LEFT OUTER JOIN Project ON ProjectGroup.ProjectPID = Project.PID where ProjectGroup.username = '" . $mtooldb->real_escape_string($param_ProjectGroup_username_where) . "' and ProjectGroup.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectGroup_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectGroupData();
			$thisresult->PID = $thisline[0];
			$thisresult->username = $thisline[1];
			$thisresult->ProjectGroupType = $thisline[2];
			$thisresult->UniqueDirName = $thisline[3];
			$thisresult->UniqueDBName = $thisline[4];
			$thisresult->UniqueDBUserName = $thisline[5];
			$thisresult->Name = $thisline[6];
			$thisresult->CreatedDateTime = $thisline[7];
			$thisresult->CreateSettingGroup = $thisline[8];
			$thisresult->SettingGroupPID = $thisline[9];
			$thisresult->CreateSettingGroupUser = $thisline[10];
			$thisresult->SettingGroupUserPID = $thisline[11];
			$thisresult->CreateServer = $thisline[12];
			$thisresult->ServerPID = $thisline[13];
			$thisresult->CreateMainServer = $thisline[14];
			$thisresult->MainServerPID = $thisline[15];
			$thisresult->CreateDBConnection = $thisline[16];
			$thisresult->DBConnectionPID = $thisline[17];
			$thisresult->CreateDBUser = $thisline[18];
			$thisresult->DBUserPID = $thisline[19];
			$thisresult->CreateDBUserClientHost = $thisline[20];
			$thisresult->DBUserClientHostPID = $thisline[21];
			$thisresult->CreateDBUserClientHostFromMain = $thisline[22];
			$thisresult->DBUserClientHostPIDFromMain = $thisline[23];
			$thisresult->CreateDropboxBaseFolder = $thisline[24];
			$thisresult->DropboxBaseFolderPID = $thisline[25];
			$thisresult->CreateApacheSetting = $thisline[26];
			$thisresult->ApacheSettingPID = $thisline[27];
			$thisresult->CreateApacheHostSetting = $thisline[28];
			$thisresult->ApacheHostSettingPID = $thisline[29];
			$thisresult->CreateBuildApacheHostSetting = $thisline[30];
			$thisresult->CreateUploadServer = $thisline[31];
			$thisresult->UploadServerPID = $thisline[32];
			$thisresult->CreateUploadServerPath = $thisline[33];
			$thisresult->UploadServerPathPID = $thisline[34];
			$thisresult->CreateUploadServerPathForBeta = $thisline[35];
			$thisresult->UploadServerPathPIDForBeta = $thisline[36];
			$thisresult->CreateUploadGroup = $thisline[37];
			$thisresult->UploadGroupPID = $thisline[38];
			$thisresult->CreateUploadGroupAssignedServerPath = $thisline[39];
			$thisresult->UploadGroupAssignedServerPathPID = $thisline[40];
			$thisresult->CreateUploadGroupAssignedServerPathForBeta = $thisline[41];
			$thisresult->UploadGroupAssignedServerPathPIDForBeta = $thisline[42];
			$thisresult->CreateUploadGroupAssignedUser = $thisline[43];
			$thisresult->UploadGroupAssignedUserPID = $thisline[44];
			$thisresult->CreateProject = $thisline[45];
			$thisresult->ProjectPID = $thisline[46];
			$thisresult->CreateProjectSourceOutputForDA = $thisline[47];
			$thisresult->DAProjectSourceOutputPID = $thisline[48];
			$thisresult->CreateProjectSourceOutputForProxyServer = $thisline[49];
			$thisresult->ProxyServerProjectSourceOutputPID = $thisline[50];
			$thisresult->CreateProjectSourceOutputForDAForBeta = $thisline[51];
			$thisresult->DAProjectSourceOutputPIDForBeta = $thisline[52];
			$thisresult->CreateProjectSourceOutputForProxyServerForBeta = $thisline[53];
			$thisresult->ProxyServerProjectSourceOutputPIDForBeta = $thisline[54];
			$thisresult->CreateDatabase = $thisline[55];
			$thisresult->SettingGroupName = $thisline[56];
			$thisresult->Projectname = $thisline[57];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertProjectGroup($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectGroup ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectGroup (username, ProjectGroupType, UniqueDirName, UniqueDBName, UniqueDBUserName, Name) values('" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "', '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "', '" . $mtooldb->real_escape_string($ProjectGroupObj->UniqueDirName) . "', '" . $mtooldb->real_escape_string($ProjectGroupObj->UniqueDBName) . "', '" . $mtooldb->real_escape_string($ProjectGroupObj->UniqueDBUserName) . "', '" . $mtooldb->real_escape_string($ProjectGroupObj->Name) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSettingGroup($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSettingGroup ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateSettingGroup = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateSettingGroup) . "', SettingGroupPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->SettingGroupPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSettingGroupUser($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSettingGroupUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSettingGroupUser ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateSettingGroupUser = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateSettingGroupUser) . "', SettingGroupUserPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->SettingGroupUserPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateServer($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateServer ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateServer ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateServer = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateServer) . "', ServerPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->ServerPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateMainServer($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateMainServer ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateMainServer ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateMainServer = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateMainServer) . "', MainServerPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->MainServerPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBConnection($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBConnection ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBConnection ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateDBConnection = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateDBConnection) . "', DBConnectionPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->DBConnectionPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBUser($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBUser ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateDBUser = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateDBUser) . "', DBUserPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->DBUserPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBUserClientHost($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBUserClientHost ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBUserClientHost ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateDBUserClientHost = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateDBUserClientHost) . "', DBUserClientHostPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->DBUserClientHostPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDBUserClientHostFromMain($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDBUserClientHostFromMain ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDBUserClientHostFromMain ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateDBUserClientHostFromMain = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateDBUserClientHostFromMain) . "', DBUserClientHostPIDFromMain = '" . $mtooldb->real_escape_string($ProjectGroupObj->DBUserClientHostPIDFromMain) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDropboxBaseFolder($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolder ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateDropboxBaseFolder = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateDropboxBaseFolder) . "', DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->DropboxBaseFolderPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateApacheSetting($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateApacheSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateApacheSetting ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateApacheSetting = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateApacheSetting) . "', ApacheSettingPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->ApacheSettingPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateApacheHostSetting($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateApacheHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateApacheHostSetting ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateApacheHostSetting = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateApacheHostSetting) . "', ApacheHostSettingPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->ApacheHostSettingPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateBuildApacheHostSetting($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateBuildApacheHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateBuildApacheHostSetting ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateBuildApacheHostSetting = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateBuildApacheHostSetting) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadServer($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadServer ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadServer ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadServer = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadServer) . "', UploadServerPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadServerPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadServerPath($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadServerPath ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadServerPath = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadServerPath) . "', UploadServerPathPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadServerPathPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadServerPathForBeta($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadServerPathForBeta ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadServerPathForBeta ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadServerPathForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadServerPathForBeta) . "', UploadServerPathPIDForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadServerPathPIDForBeta) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroup($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroup ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadGroup = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadGroup) . "', UploadGroupPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadGroupPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroupAssignedServerPath($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPath ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPath ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadGroupAssignedServerPath = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadGroupAssignedServerPath) . "', UploadGroupAssignedServerPathPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadGroupAssignedServerPathPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroupAssignedServerPathForBeta($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPathForBeta ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedServerPathForBeta ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadGroupAssignedServerPathForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadGroupAssignedServerPathForBeta) . "', UploadGroupAssignedServerPathPIDForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadGroupAssignedServerPathPIDForBeta) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateUploadGroupAssignedUser($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedUser ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateUploadGroupAssignedUser ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateUploadGroupAssignedUser = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateUploadGroupAssignedUser) . "', UploadGroupAssignedUserPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->UploadGroupAssignedUserPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectInfo($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectInfo ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectInfo ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateProject = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateProject) . "', ProjectPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAProjectSourceOutput($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateProjectSourceOutputForDA = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateProjectSourceOutputForDA) . "', DAProjectSourceOutputPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->DAProjectSourceOutputPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProxyServerProjectSourceOutput($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProxyServerProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProxyServerProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateProjectSourceOutputForProxyServer = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateProjectSourceOutputForProxyServer) . "', ProxyServerProjectSourceOutputPID = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProxyServerProjectSourceOutputPID) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDAProjectSourceOutputForBeta($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDAProjectSourceOutputForBeta ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDAProjectSourceOutputForBeta ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateProjectSourceOutputForDAForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateProjectSourceOutputForDAForBeta) . "', DAProjectSourceOutputPIDForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->DAProjectSourceOutputPIDForBeta) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProxyServerProjectSourceOutputForBeta($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProxyServerProjectSourceOutputForBeta ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProxyServerProjectSourceOutputForBeta ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateProjectSourceOutputForProxyServerForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateProjectSourceOutputForProxyServerForBeta) . "', ProxyServerProjectSourceOutputPIDForBeta = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProxyServerProjectSourceOutputPIDForBeta) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateCreateDatabase($ProjectGroupObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateCreateDatabase ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateCreateDatabase ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroup SET CreateDatabase = '" . $mtooldb->real_escape_string($ProjectGroupObj->CreateDatabase) . "' where ProjectGroup.PID = '" . $mtooldb->real_escape_string($ProjectGroupObj->PID) . "' and ProjectGroup.username = '" . $mtooldb->real_escape_string($ProjectGroupObj->username) . "' and ProjectGroup.ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupObj->ProjectGroupType) . "'";
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