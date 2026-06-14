<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectData
{
	public $PID;
	public $name;
	public $StorageType;
	public $DropboxBaseFolderPID;
	public $DBType;
	public $DBUserPID;
	public $SQLServerConnectionString;
	public $SettingDir;
	public $DBManagerURL;
	public $TokenForProxyAccess;
	public $proxy_header_of_access_control_allow_origin;
	public $proxy_header_of_access_control_allow_headers;
	public $option_automatically_create_simple_proxy;
	public $option_automatically_create_custom_proxy;
	public $option_show_proxy_link;
	public $option_auto_upload_after_build;
	public $option_show_source;
	public $option_show_detail;
	public $option_show_recommended_column_warning;
	public $option_all_source_include;
	public $option_user_can_change_da_func_order;
	public $option_show_source_output_setting;
	public $option_restrict_proxy_server_to_single;
	public $option_show_language_resource;
	public $option_build_dataclass_for_proxy_client_only_if_proxy_exist;
	public $option_show_output_temp_folder_on_build;
	public $option_default_output_temp_folder_on_build_is_true;
	public $option_IsCompareOutputTarget;
	public $DropboxSettingname;
	public $DropboxSettingAccessToken;
	public $DBConnectionDBServerType;
	public $DBConnectionDBName;
	public $DBConnectionObjectNameForPHP;
	public $ServerLocalServerName;
	public $ServerIP;
	public $DBUserUser;
	public $DBUserPassword;
	public $DropboxBaseFolderName;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function Getoption_automatically_create_simple_proxy()
	{
		return ($this->option_automatically_create_simple_proxy == 1);
	}
	public function Getoption_automatically_create_custom_proxy()
	{
		return ($this->option_automatically_create_custom_proxy == 1);
	}
	public function Getoption_show_proxy_link()
	{
		return ($this->option_show_proxy_link == 1);
	}
	public function Getoption_auto_upload_after_build()
	{
		return ($this->option_auto_upload_after_build == 1);
	}
	public function Getoption_show_source()
	{
		return ($this->option_show_source == 1);
	}
	public function Getoption_show_detail()
	{
		return ($this->option_show_detail == 1);
	}
	public function Getoption_show_recommended_column_warning()
	{
		return ($this->option_show_recommended_column_warning == 1);
	}
	public function Getoption_all_source_include()
	{
		return ($this->option_all_source_include == 1);
	}
	public function Getoption_user_can_change_da_func_order()
	{
		return ($this->option_user_can_change_da_func_order == 1);
	}
	public function Getoption_build_dataclass_for_proxy_client_only_if_proxy_exist()
	{
		return ($this->option_build_dataclass_for_proxy_client_only_if_proxy_exist == 1);
	}
	public function IsMySQL()
	{
		return ($this->DBType == ProjectDBTypeEnum::$MYSQLONCLOUD);
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetProjectStorageTypeCaption($storagetype)
{
	switch($storagetype)
	{
		case ProjectStorageTypeEnum::$DROPBOX:
			return "DropBox";
	}
	return $storagetype;
}

function GetProjectDBTypeCaption($dbtype)
{
	switch($dbtype)
	{
		case ProjectDBTypeEnum::$DEFAULT:
			return "Default";
		case ProjectDBTypeEnum::$MYSQLONCLOUD:
			return "MySQL on Cloud";
		case ProjectDBTypeEnum::$SQLSERVER:
			return "SQL Server";
	}
	return $dbtype;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class ProjectStorageTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DROPBOX = "DropBox";
}

class ProjectDBTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $MYSQLONCLOUD = "MySQLOnCloud";
	static $SQLSERVER = "SQLServer";
}

?>