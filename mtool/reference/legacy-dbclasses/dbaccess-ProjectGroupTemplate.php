<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectGroupTemplateDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectGroupTemplate($param_ProjectGroupTemplate_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectGroupTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectGroupTemplate ==
		
		$last_sql_command_for_mtooldb = "select ProjectGroupTemplate.PID, ProjectGroupTemplate.ProjectGroupType, ProjectGroupTemplate.ProjectGroupNamePrefix, ProjectGroupTemplate.SettingGroupPID, ProjectGroupTemplate.MainServerPID, ProjectGroupTemplate.ServerPID, ProjectGroupTemplate.DropboxSettingPID, ProjectGroupTemplate.ApacheHostSettingTemplatePID, ProjectGroupTemplate.DropboxBaseDir, ProjectGroupTemplate.LocalBaseDir, ProjectGroupTemplate.ProxyBaseURL, ProjectGroupTemplate.UploaderURLSuffix, ProjectGroupTemplate.DBManagerURLSuffix, ProjectGroupTemplate.proxy_header_of_access_control_allow_origin, ProjectGroupTemplate.proxy_header_of_access_control_allow_headers from ProjectGroupTemplate where ProjectGroupTemplate.PID = '" . $mtooldb->real_escape_string($param_ProjectGroupTemplate_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectGroupTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectGroupType = $thisline[1];
			$thisresult->ProjectGroupNamePrefix = $thisline[2];
			$thisresult->SettingGroupPID = $thisline[3];
			$thisresult->MainServerPID = $thisline[4];
			$thisresult->ServerPID = $thisline[5];
			$thisresult->DropboxSettingPID = $thisline[6];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[7];
			$thisresult->DropboxBaseDir = $thisline[8];
			$thisresult->LocalBaseDir = $thisline[9];
			$thisresult->ProxyBaseURL = $thisline[10];
			$thisresult->UploaderURLSuffix = $thisline[11];
			$thisresult->DBManagerURLSuffix = $thisline[12];
			$thisresult->proxy_header_of_access_control_allow_origin = $thisline[13];
			$thisresult->proxy_header_of_access_control_allow_headers = $thisline[14];
			return $thisresult;
		}
		return NULL;
	}
	public function GetProjectGroupTemplateByProjectGroupType($param_ProjectGroupTemplate_ProjectGroupType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectGroupTemplateByProjectGroupType ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectGroupTemplateByProjectGroupType ==
		
		$last_sql_command_for_mtooldb = "select ProjectGroupTemplate.PID, ProjectGroupTemplate.ProjectGroupType, ProjectGroupTemplate.ProjectGroupNamePrefix, ProjectGroupTemplate.SettingGroupPID, ProjectGroupTemplate.MainServerPID, ProjectGroupTemplate.ServerPID, ProjectGroupTemplate.DropboxSettingPID, ProjectGroupTemplate.ApacheHostSettingTemplatePID, ProjectGroupTemplate.DropboxBaseDir, ProjectGroupTemplate.LocalBaseDir, ProjectGroupTemplate.ProxyBaseURL, ProjectGroupTemplate.UploaderURLSuffix, ProjectGroupTemplate.DBManagerURLSuffix, ProjectGroupTemplate.proxy_header_of_access_control_allow_origin, ProjectGroupTemplate.proxy_header_of_access_control_allow_headers from ProjectGroupTemplate where ProjectGroupTemplate.ProjectGroupType = '" . $mtooldb->real_escape_string($param_ProjectGroupTemplate_ProjectGroupType_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectGroupTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectGroupType = $thisline[1];
			$thisresult->ProjectGroupNamePrefix = $thisline[2];
			$thisresult->SettingGroupPID = $thisline[3];
			$thisresult->MainServerPID = $thisline[4];
			$thisresult->ServerPID = $thisline[5];
			$thisresult->DropboxSettingPID = $thisline[6];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[7];
			$thisresult->DropboxBaseDir = $thisline[8];
			$thisresult->LocalBaseDir = $thisline[9];
			$thisresult->ProxyBaseURL = $thisline[10];
			$thisresult->UploaderURLSuffix = $thisline[11];
			$thisresult->DBManagerURLSuffix = $thisline[12];
			$thisresult->proxy_header_of_access_control_allow_origin = $thisline[13];
			$thisresult->proxy_header_of_access_control_allow_headers = $thisline[14];
			return $thisresult;
		}
		return NULL;
	}
	public function GetProjectGroupTemplateList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectGroupTemplateList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectGroupTemplateList ==
		
		$last_sql_command_for_mtooldb = "select ProjectGroupTemplate.PID, ProjectGroupTemplate.ProjectGroupType, ProjectGroupTemplate.ProjectGroupNamePrefix, ProjectGroupTemplate.SettingGroupPID, ProjectGroupTemplate.MainServerPID, ProjectGroupTemplate.ServerPID, ProjectGroupTemplate.DropboxSettingPID, ProjectGroupTemplate.ApacheHostSettingTemplatePID, ProjectGroupTemplate.DropboxBaseDir, ProjectGroupTemplate.LocalBaseDir, ProjectGroupTemplate.ProxyBaseURL, ProjectGroupTemplate.UploaderURLSuffix, ProjectGroupTemplate.DBManagerURLSuffix, ProjectGroupTemplate.proxy_header_of_access_control_allow_origin, ProjectGroupTemplate.proxy_header_of_access_control_allow_headers from ProjectGroupTemplate order by ProjectGroupTemplate.ProjectGroupType";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectGroupTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectGroupType = $thisline[1];
			$thisresult->ProjectGroupNamePrefix = $thisline[2];
			$thisresult->SettingGroupPID = $thisline[3];
			$thisresult->MainServerPID = $thisline[4];
			$thisresult->ServerPID = $thisline[5];
			$thisresult->DropboxSettingPID = $thisline[6];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[7];
			$thisresult->DropboxBaseDir = $thisline[8];
			$thisresult->LocalBaseDir = $thisline[9];
			$thisresult->ProxyBaseURL = $thisline[10];
			$thisresult->UploaderURLSuffix = $thisline[11];
			$thisresult->DBManagerURLSuffix = $thisline[12];
			$thisresult->proxy_header_of_access_control_allow_origin = $thisline[13];
			$thisresult->proxy_header_of_access_control_allow_headers = $thisline[14];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertProjectGroupTemplate($ProjectGroupTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectGroupTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectGroupTemplate ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectGroupTemplate (ProjectGroupType, ProjectGroupNamePrefix, SettingGroupPID, MainServerPID, ServerPID, DropboxSettingPID, ApacheHostSettingTemplatePID, DropboxBaseDir, LocalBaseDir, ProxyBaseURL, UploaderURLSuffix, DBManagerURLSuffix, proxy_header_of_access_control_allow_origin, proxy_header_of_access_control_allow_headers) values('" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ProjectGroupType) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ProjectGroupNamePrefix) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->MainServerPID) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ServerPID) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->DropboxSettingPID) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ApacheHostSettingTemplatePID) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->DropboxBaseDir) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->LocalBaseDir) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ProxyBaseURL) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->UploaderURLSuffix) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->DBManagerURLSuffix) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->proxy_header_of_access_control_allow_origin) . "', '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->proxy_header_of_access_control_allow_headers) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectGroupTemplate($ProjectGroupTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectGroupTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectGroupTemplate ==
		
		$last_sql_command_for_mtooldb = "update ProjectGroupTemplate SET ProjectGroupType = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ProjectGroupType) . "', ProjectGroupNamePrefix = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ProjectGroupNamePrefix) . "', SettingGroupPID = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->SettingGroupPID) . "', MainServerPID = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->MainServerPID) . "', ServerPID = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ServerPID) . "', DropboxSettingPID = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->DropboxSettingPID) . "', ApacheHostSettingTemplatePID = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ApacheHostSettingTemplatePID) . "', DropboxBaseDir = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->DropboxBaseDir) . "', LocalBaseDir = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->LocalBaseDir) . "', ProxyBaseURL = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->ProxyBaseURL) . "', UploaderURLSuffix = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->UploaderURLSuffix) . "', DBManagerURLSuffix = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->DBManagerURLSuffix) . "', proxy_header_of_access_control_allow_origin = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->proxy_header_of_access_control_allow_origin) . "', proxy_header_of_access_control_allow_headers = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->proxy_header_of_access_control_allow_headers) . "' where ProjectGroupTemplate.PID = '" . $mtooldb->real_escape_string($ProjectGroupTemplateObj->PID) . "'";
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