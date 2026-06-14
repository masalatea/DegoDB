<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectList ==
		
		$last_sql_command_for_mtooldb = "select Project.PID, Project.name, Project.StorageType, Project.DropboxBaseFolderPID, Project.DBType, Project.DBUserPID, Project.SQLServerConnectionString, Project.SettingDir, Project.DBManagerURL, Project.TokenForProxyAccess, Project.proxy_header_of_access_control_allow_origin, Project.proxy_header_of_access_control_allow_headers, Project.option_automatically_create_simple_proxy, Project.option_automatically_create_custom_proxy, Project.option_show_proxy_link, Project.option_auto_upload_after_build, Project.option_show_source, Project.option_show_detail, Project.option_show_recommended_column_warning, Project.option_all_source_include, Project.option_user_can_change_da_func_order, Project.option_show_source_output_setting, Project.option_restrict_proxy_server_to_single, Project.option_show_language_resource, Project.option_build_dataclass_for_proxy_client_only_if_proxy_exist, Project.option_show_output_temp_folder_on_build, Project.option_default_output_temp_folder_on_build_is_true, Project.option_IsCompareOutputTarget, DBUser.User, DBUser.Password, DBConnection.DBServerType, DBConnection.DBName, DBConnection.ObjectNameForPHP, Server.LocalServerName, Server.IP, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.AccessToken from Project LEFT OUTER JOIN DBUser ON Project.DBUserPID = DBUser.PID LEFT OUTER JOIN DropboxBaseFolder ON Project.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID LEFT OUTER JOIN Server ON DBConnection.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID order by Project.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->StorageType = $thisline[2];
			$thisresult->DropboxBaseFolderPID = $thisline[3];
			$thisresult->DBType = $thisline[4];
			$thisresult->DBUserPID = $thisline[5];
			$thisresult->SQLServerConnectionString = $thisline[6];
			$thisresult->SettingDir = $thisline[7];
			$thisresult->DBManagerURL = $thisline[8];
			$thisresult->TokenForProxyAccess = $thisline[9];
			$thisresult->proxy_header_of_access_control_allow_origin = $thisline[10];
			$thisresult->proxy_header_of_access_control_allow_headers = $thisline[11];
			$thisresult->option_automatically_create_simple_proxy = $thisline[12];
			$thisresult->option_automatically_create_custom_proxy = $thisline[13];
			$thisresult->option_show_proxy_link = $thisline[14];
			$thisresult->option_auto_upload_after_build = $thisline[15];
			$thisresult->option_show_source = $thisline[16];
			$thisresult->option_show_detail = $thisline[17];
			$thisresult->option_show_recommended_column_warning = $thisline[18];
			$thisresult->option_all_source_include = $thisline[19];
			$thisresult->option_user_can_change_da_func_order = $thisline[20];
			$thisresult->option_show_source_output_setting = $thisline[21];
			$thisresult->option_restrict_proxy_server_to_single = $thisline[22];
			$thisresult->option_show_language_resource = $thisline[23];
			$thisresult->option_build_dataclass_for_proxy_client_only_if_proxy_exist = $thisline[24];
			$thisresult->option_show_output_temp_folder_on_build = $thisline[25];
			$thisresult->option_default_output_temp_folder_on_build_is_true = $thisline[26];
			$thisresult->option_IsCompareOutputTarget = $thisline[27];
			$thisresult->DBUserUser = $thisline[28];
			$thisresult->DBUserPassword = $thisline[29];
			$thisresult->DBConnectionDBServerType = $thisline[30];
			$thisresult->DBConnectionDBName = $thisline[31];
			$thisresult->DBConnectionObjectNameForPHP = $thisline[32];
			$thisresult->ServerLocalServerName = $thisline[33];
			$thisresult->ServerIP = $thisline[34];
			$thisresult->DropboxBaseFolderName = $thisline[35];
			$thisresult->DropboxSettingname = $thisline[36];
			$thisresult->DropboxSettingAccessToken = $thisline[37];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectbyOwnerOrUserSecurityList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectbyOwnerOrUserSecurityList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectbyOwnerOrUserSecurityList ==
		
		$last_sql_command_for_mtooldb = "select Project.PID, Project.name, Project.StorageType, Project.DropboxBaseFolderPID, Project.DBType, Project.DBUserPID, Project.SQLServerConnectionString, Project.SettingDir, Project.DBManagerURL, Project.TokenForProxyAccess, Project.proxy_header_of_access_control_allow_origin, Project.proxy_header_of_access_control_allow_headers, Project.option_automatically_create_simple_proxy, Project.option_automatically_create_custom_proxy, Project.option_show_proxy_link, Project.option_auto_upload_after_build, Project.option_show_source, Project.option_show_detail, Project.option_show_recommended_column_warning, Project.option_all_source_include, Project.option_user_can_change_da_func_order, Project.option_show_source_output_setting, Project.option_restrict_proxy_server_to_single, Project.option_show_language_resource, Project.option_build_dataclass_for_proxy_client_only_if_proxy_exist, Project.option_show_output_temp_folder_on_build, Project.option_default_output_temp_folder_on_build_is_true, Project.option_IsCompareOutputTarget, DBUser.User, DBUser.Password, DBConnection.DBServerType, DBConnection.DBName, DBConnection.ObjectNameForPHP, Server.LocalServerName, Server.IP, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.AccessToken from Project LEFT OUTER JOIN DBUser ON Project.DBUserPID = DBUser.PID LEFT OUTER JOIN DropboxBaseFolder ON Project.DropboxBaseFolderPID = DropboxBaseFolder.PID join ProjectUser LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID LEFT OUTER JOIN Server ON DBConnection.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and Project.PID = ProjectUser.ProjectPID order by Project.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->StorageType = $thisline[2];
			$thisresult->DropboxBaseFolderPID = $thisline[3];
			$thisresult->DBType = $thisline[4];
			$thisresult->DBUserPID = $thisline[5];
			$thisresult->SQLServerConnectionString = $thisline[6];
			$thisresult->SettingDir = $thisline[7];
			$thisresult->DBManagerURL = $thisline[8];
			$thisresult->TokenForProxyAccess = $thisline[9];
			$thisresult->proxy_header_of_access_control_allow_origin = $thisline[10];
			$thisresult->proxy_header_of_access_control_allow_headers = $thisline[11];
			$thisresult->option_automatically_create_simple_proxy = $thisline[12];
			$thisresult->option_automatically_create_custom_proxy = $thisline[13];
			$thisresult->option_show_proxy_link = $thisline[14];
			$thisresult->option_auto_upload_after_build = $thisline[15];
			$thisresult->option_show_source = $thisline[16];
			$thisresult->option_show_detail = $thisline[17];
			$thisresult->option_show_recommended_column_warning = $thisline[18];
			$thisresult->option_all_source_include = $thisline[19];
			$thisresult->option_user_can_change_da_func_order = $thisline[20];
			$thisresult->option_show_source_output_setting = $thisline[21];
			$thisresult->option_restrict_proxy_server_to_single = $thisline[22];
			$thisresult->option_show_language_resource = $thisline[23];
			$thisresult->option_build_dataclass_for_proxy_client_only_if_proxy_exist = $thisline[24];
			$thisresult->option_show_output_temp_folder_on_build = $thisline[25];
			$thisresult->option_default_output_temp_folder_on_build_is_true = $thisline[26];
			$thisresult->option_IsCompareOutputTarget = $thisline[27];
			$thisresult->DBUserUser = $thisline[28];
			$thisresult->DBUserPassword = $thisline[29];
			$thisresult->DBConnectionDBServerType = $thisline[30];
			$thisresult->DBConnectionDBName = $thisline[31];
			$thisresult->DBConnectionObjectNameForPHP = $thisline[32];
			$thisresult->ServerLocalServerName = $thisline[33];
			$thisresult->ServerIP = $thisline[34];
			$thisresult->DropboxBaseFolderName = $thisline[35];
			$thisresult->DropboxSettingname = $thisline[36];
			$thisresult->DropboxSettingAccessToken = $thisline[37];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProject($param_Project_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProject ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProject ==
		
		$last_sql_command_for_mtooldb = "select Project.PID, Project.name, Project.StorageType, Project.DropboxBaseFolderPID, Project.DBType, Project.DBUserPID, Project.SQLServerConnectionString, Project.SettingDir, Project.DBManagerURL, Project.TokenForProxyAccess, Project.proxy_header_of_access_control_allow_origin, Project.proxy_header_of_access_control_allow_headers, Project.option_automatically_create_simple_proxy, Project.option_automatically_create_custom_proxy, Project.option_show_proxy_link, Project.option_auto_upload_after_build, Project.option_show_source, Project.option_show_detail, Project.option_show_recommended_column_warning, Project.option_all_source_include, Project.option_user_can_change_da_func_order, Project.option_show_source_output_setting, Project.option_restrict_proxy_server_to_single, Project.option_show_language_resource, Project.option_build_dataclass_for_proxy_client_only_if_proxy_exist, Project.option_show_output_temp_folder_on_build, Project.option_default_output_temp_folder_on_build_is_true, Project.option_IsCompareOutputTarget, DBUser.User, DBUser.Password, DBConnection.DBServerType, DBConnection.DBName, DBConnection.ObjectNameForPHP, Server.LocalServerName, Server.IP, DropboxBaseFolder.Name, DropboxSetting.name, DropboxSetting.AccessToken from Project LEFT OUTER JOIN DBUser ON Project.DBUserPID = DBUser.PID LEFT OUTER JOIN DropboxBaseFolder ON Project.DropboxBaseFolderPID = DropboxBaseFolder.PID LEFT OUTER JOIN DBConnection ON DBUser.DBConnectionPID = DBConnection.PID LEFT OUTER JOIN Server ON DBConnection.ServerPID = Server.PID LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where Project.PID = '" . $mtooldb->real_escape_string($param_Project_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->StorageType = $thisline[2];
			$thisresult->DropboxBaseFolderPID = $thisline[3];
			$thisresult->DBType = $thisline[4];
			$thisresult->DBUserPID = $thisline[5];
			$thisresult->SQLServerConnectionString = $thisline[6];
			$thisresult->SettingDir = $thisline[7];
			$thisresult->DBManagerURL = $thisline[8];
			$thisresult->TokenForProxyAccess = $thisline[9];
			$thisresult->proxy_header_of_access_control_allow_origin = $thisline[10];
			$thisresult->proxy_header_of_access_control_allow_headers = $thisline[11];
			$thisresult->option_automatically_create_simple_proxy = $thisline[12];
			$thisresult->option_automatically_create_custom_proxy = $thisline[13];
			$thisresult->option_show_proxy_link = $thisline[14];
			$thisresult->option_auto_upload_after_build = $thisline[15];
			$thisresult->option_show_source = $thisline[16];
			$thisresult->option_show_detail = $thisline[17];
			$thisresult->option_show_recommended_column_warning = $thisline[18];
			$thisresult->option_all_source_include = $thisline[19];
			$thisresult->option_user_can_change_da_func_order = $thisline[20];
			$thisresult->option_show_source_output_setting = $thisline[21];
			$thisresult->option_restrict_proxy_server_to_single = $thisline[22];
			$thisresult->option_show_language_resource = $thisline[23];
			$thisresult->option_build_dataclass_for_proxy_client_only_if_proxy_exist = $thisline[24];
			$thisresult->option_show_output_temp_folder_on_build = $thisline[25];
			$thisresult->option_default_output_temp_folder_on_build_is_true = $thisline[26];
			$thisresult->option_IsCompareOutputTarget = $thisline[27];
			$thisresult->DBUserUser = $thisline[28];
			$thisresult->DBUserPassword = $thisline[29];
			$thisresult->DBConnectionDBServerType = $thisline[30];
			$thisresult->DBConnectionDBName = $thisline[31];
			$thisresult->DBConnectionObjectNameForPHP = $thisline[32];
			$thisresult->ServerLocalServerName = $thisline[33];
			$thisresult->ServerIP = $thisline[34];
			$thisresult->DropboxBaseFolderName = $thisline[35];
			$thisresult->DropboxSettingname = $thisline[36];
			$thisresult->DropboxSettingAccessToken = $thisline[37];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertProject($ProjectObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProject ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProject ==
		
		$last_sql_command_for_mtooldb = "insert into Project (name, StorageType, DropboxBaseFolderPID, DBType, DBUserPID, SQLServerConnectionString, SettingDir, DBManagerURL, TokenForProxyAccess, proxy_header_of_access_control_allow_origin, proxy_header_of_access_control_allow_headers, option_automatically_create_simple_proxy, option_automatically_create_custom_proxy, option_show_proxy_link, option_auto_upload_after_build, option_show_source, option_show_detail, option_show_recommended_column_warning, option_all_source_include, option_user_can_change_da_func_order, option_show_source_output_setting, option_restrict_proxy_server_to_single, option_show_language_resource, option_build_dataclass_for_proxy_client_only_if_proxy_exist, option_show_output_temp_folder_on_build, option_default_output_temp_folder_on_build_is_true, option_IsCompareOutputTarget) values('" . $mtooldb->real_escape_string($ProjectObj->name) . "', '" . $mtooldb->real_escape_string($ProjectObj->StorageType) . "', '" . $mtooldb->real_escape_string($ProjectObj->DropboxBaseFolderPID) . "', '" . $mtooldb->real_escape_string($ProjectObj->DBType) . "', '" . $mtooldb->real_escape_string($ProjectObj->DBUserPID) . "', '" . $mtooldb->real_escape_string($ProjectObj->SQLServerConnectionString) . "', '" . $mtooldb->real_escape_string($ProjectObj->SettingDir) . "', '" . $mtooldb->real_escape_string($ProjectObj->DBManagerURL) . "', '" . $mtooldb->real_escape_string($ProjectObj->TokenForProxyAccess) . "', '" . $mtooldb->real_escape_string($ProjectObj->proxy_header_of_access_control_allow_origin) . "', '" . $mtooldb->real_escape_string($ProjectObj->proxy_header_of_access_control_allow_headers) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_automatically_create_simple_proxy) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_automatically_create_custom_proxy) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_proxy_link) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_auto_upload_after_build) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_source) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_detail) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_recommended_column_warning) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_all_source_include) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_user_can_change_da_func_order) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_source_output_setting) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_restrict_proxy_server_to_single) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_language_resource) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_build_dataclass_for_proxy_client_only_if_proxy_exist) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_show_output_temp_folder_on_build) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_default_output_temp_folder_on_build_is_true) . "', '" . $mtooldb->real_escape_string($ProjectObj->option_IsCompareOutputTarget) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProject($ProjectObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProject ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProject ==
		
		$last_sql_command_for_mtooldb = "update Project SET name = '" . $mtooldb->real_escape_string($ProjectObj->name) . "', StorageType = '" . $mtooldb->real_escape_string($ProjectObj->StorageType) . "', SettingDir = '" . $mtooldb->real_escape_string($ProjectObj->SettingDir) . "', DBManagerURL = '" . $mtooldb->real_escape_string($ProjectObj->DBManagerURL) . "', TokenForProxyAccess = '" . $mtooldb->real_escape_string($ProjectObj->TokenForProxyAccess) . "', proxy_header_of_access_control_allow_origin = '" . $mtooldb->real_escape_string($ProjectObj->proxy_header_of_access_control_allow_origin) . "', proxy_header_of_access_control_allow_headers = '" . $mtooldb->real_escape_string($ProjectObj->proxy_header_of_access_control_allow_headers) . "', option_automatically_create_simple_proxy = '" . $mtooldb->real_escape_string($ProjectObj->option_automatically_create_simple_proxy) . "', option_automatically_create_custom_proxy = '" . $mtooldb->real_escape_string($ProjectObj->option_automatically_create_custom_proxy) . "', option_show_proxy_link = '" . $mtooldb->real_escape_string($ProjectObj->option_show_proxy_link) . "', option_auto_upload_after_build = '" . $mtooldb->real_escape_string($ProjectObj->option_auto_upload_after_build) . "', option_show_source = '" . $mtooldb->real_escape_string($ProjectObj->option_show_source) . "', option_show_detail = '" . $mtooldb->real_escape_string($ProjectObj->option_show_detail) . "', option_show_recommended_column_warning = '" . $mtooldb->real_escape_string($ProjectObj->option_show_recommended_column_warning) . "', option_all_source_include = '" . $mtooldb->real_escape_string($ProjectObj->option_all_source_include) . "', option_user_can_change_da_func_order = '" . $mtooldb->real_escape_string($ProjectObj->option_user_can_change_da_func_order) . "', option_show_source_output_setting = '" . $mtooldb->real_escape_string($ProjectObj->option_show_source_output_setting) . "', option_restrict_proxy_server_to_single = '" . $mtooldb->real_escape_string($ProjectObj->option_restrict_proxy_server_to_single) . "', option_show_language_resource = '" . $mtooldb->real_escape_string($ProjectObj->option_show_language_resource) . "', option_build_dataclass_for_proxy_client_only_if_proxy_exist = '" . $mtooldb->real_escape_string($ProjectObj->option_build_dataclass_for_proxy_client_only_if_proxy_exist) . "', option_show_output_temp_folder_on_build = '" . $mtooldb->real_escape_string($ProjectObj->option_show_output_temp_folder_on_build) . "', option_default_output_temp_folder_on_build_is_true = '" . $mtooldb->real_escape_string($ProjectObj->option_default_output_temp_folder_on_build_is_true) . "', option_IsCompareOutputTarget = '" . $mtooldb->real_escape_string($ProjectObj->option_IsCompareOutputTarget) . "' where Project.PID = '" . $mtooldb->real_escape_string($ProjectObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectForAdmin($ProjectObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectForAdmin ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectForAdmin ==
		
		$last_sql_command_for_mtooldb = "update Project SET name = '" . $mtooldb->real_escape_string($ProjectObj->name) . "', StorageType = '" . $mtooldb->real_escape_string($ProjectObj->StorageType) . "', DropboxBaseFolderPID = '" . $mtooldb->real_escape_string($ProjectObj->DropboxBaseFolderPID) . "', DBType = '" . $mtooldb->real_escape_string($ProjectObj->DBType) . "', DBUserPID = '" . $mtooldb->real_escape_string($ProjectObj->DBUserPID) . "', SQLServerConnectionString = '" . $mtooldb->real_escape_string($ProjectObj->SQLServerConnectionString) . "', SettingDir = '" . $mtooldb->real_escape_string($ProjectObj->SettingDir) . "', DBManagerURL = '" . $mtooldb->real_escape_string($ProjectObj->DBManagerURL) . "', TokenForProxyAccess = '" . $mtooldb->real_escape_string($ProjectObj->TokenForProxyAccess) . "', proxy_header_of_access_control_allow_origin = '" . $mtooldb->real_escape_string($ProjectObj->proxy_header_of_access_control_allow_origin) . "', proxy_header_of_access_control_allow_headers = '" . $mtooldb->real_escape_string($ProjectObj->proxy_header_of_access_control_allow_headers) . "', option_automatically_create_simple_proxy = '" . $mtooldb->real_escape_string($ProjectObj->option_automatically_create_simple_proxy) . "', option_automatically_create_custom_proxy = '" . $mtooldb->real_escape_string($ProjectObj->option_automatically_create_custom_proxy) . "', option_show_proxy_link = '" . $mtooldb->real_escape_string($ProjectObj->option_show_proxy_link) . "', option_auto_upload_after_build = '" . $mtooldb->real_escape_string($ProjectObj->option_auto_upload_after_build) . "', option_show_source = '" . $mtooldb->real_escape_string($ProjectObj->option_show_source) . "', option_show_detail = '" . $mtooldb->real_escape_string($ProjectObj->option_show_detail) . "', option_show_recommended_column_warning = '" . $mtooldb->real_escape_string($ProjectObj->option_show_recommended_column_warning) . "', option_all_source_include = '" . $mtooldb->real_escape_string($ProjectObj->option_all_source_include) . "', option_user_can_change_da_func_order = '" . $mtooldb->real_escape_string($ProjectObj->option_user_can_change_da_func_order) . "', option_show_source_output_setting = '" . $mtooldb->real_escape_string($ProjectObj->option_show_source_output_setting) . "', option_restrict_proxy_server_to_single = '" . $mtooldb->real_escape_string($ProjectObj->option_restrict_proxy_server_to_single) . "', option_show_language_resource = '" . $mtooldb->real_escape_string($ProjectObj->option_show_language_resource) . "', option_build_dataclass_for_proxy_client_only_if_proxy_exist = '" . $mtooldb->real_escape_string($ProjectObj->option_build_dataclass_for_proxy_client_only_if_proxy_exist) . "', option_show_output_temp_folder_on_build = '" . $mtooldb->real_escape_string($ProjectObj->option_show_output_temp_folder_on_build) . "', option_default_output_temp_folder_on_build_is_true = '" . $mtooldb->real_escape_string($ProjectObj->option_default_output_temp_folder_on_build_is_true) . "', option_IsCompareOutputTarget = '" . $mtooldb->real_escape_string($ProjectObj->option_IsCompareOutputTarget) . "' where Project.PID = '" . $mtooldb->real_escape_string($ProjectObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProject($param_Project_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProject ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProject ==
		
		$last_sql_command_for_mtooldb = "delete from Project where Project.PID = '" . $mtooldb->real_escape_string($param_Project_PID_where) . "'";
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