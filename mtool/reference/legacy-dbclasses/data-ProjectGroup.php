<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectGroupData
{
	public $PID;
	public $username;
	public $ProjectGroupType;
	public $UniqueDirName;
	public $UniqueDBName;
	public $UniqueDBUserName;
	public $Name;
	public $CreatedDateTime;
	public $CreateSettingGroup;
	public $SettingGroupPID;
	public $CreateSettingGroupUser;
	public $SettingGroupUserPID;
	public $CreateServer;
	public $ServerPID;
	public $CreateMainServer;
	public $MainServerPID;
	public $CreateDBConnection;
	public $DBConnectionPID;
	public $CreateDBUser;
	public $DBUserPID;
	public $CreateDBUserClientHost;
	public $DBUserClientHostPID;
	public $CreateDBUserClientHostFromMain;
	public $DBUserClientHostPIDFromMain;
	public $CreateDropboxBaseFolder;
	public $DropboxBaseFolderPID;
	public $CreateApacheSetting;
	public $ApacheSettingPID;
	public $CreateApacheHostSetting;
	public $ApacheHostSettingPID;
	public $CreateBuildApacheHostSetting;
	public $CreateUploadServer;
	public $UploadServerPID;
	public $CreateUploadServerPath;
	public $UploadServerPathPID;
	public $CreateUploadServerPathForBeta;
	public $UploadServerPathPIDForBeta;
	public $CreateUploadGroup;
	public $UploadGroupPID;
	public $CreateUploadGroupAssignedServerPath;
	public $UploadGroupAssignedServerPathPID;
	public $CreateUploadGroupAssignedServerPathForBeta;
	public $UploadGroupAssignedServerPathPIDForBeta;
	public $CreateUploadGroupAssignedUser;
	public $UploadGroupAssignedUserPID;
	public $CreateProject;
	public $ProjectPID;
	public $CreateProjectSourceOutputForDA;
	public $DAProjectSourceOutputPID;
	public $CreateProjectSourceOutputForProxyServer;
	public $ProxyServerProjectSourceOutputPID;
	public $CreateProjectSourceOutputForDAForBeta;
	public $DAProjectSourceOutputPIDForBeta;
	public $CreateProjectSourceOutputForProxyServerForBeta;
	public $ProxyServerProjectSourceOutputPIDForBeta;
	public $CreateDatabase;
	public $SettingGroupName;
	public $Projectname;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetProjectGroupProjectGroupTypeCaption($value)
{
	switch($value)
	{
		case ProjectGroupProjectGroupTypeEnum::$SANDBOX:
			return "Sandbox";
		case ProjectGroupProjectGroupTypeEnum::$SHAREDSERVER:
			return "Shared Server";
		case ProjectGroupProjectGroupTypeEnum::$VPS:
			return "VPS";
		default:
			die("Unknown Value: " . $value);
	}
	return $value;
}

function GetProjectGroupProjectGroupTypeFromTemplate($group_type_value_for_template)
{
	$group_type_value = NULL;
	switch($group_type_value_for_template)
	{
		case ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX:
			$group_type_value = ProjectGroupProjectGroupTypeEnum::$SANDBOX;
			break;
		case ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER:
			$group_type_value = ProjectGroupProjectGroupTypeEnum::$SHAREDSERVER;
			break;
		case ProjectGroupTemplateProjectGroupTypeEnum::$VPS:
			$group_type_value = ProjectGroupProjectGroupTypeEnum::$VPS;
			break;
		default:
			die("Unknown Value: " . $group_type_value_for_template);
	}
	return $group_type_value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class ProjectGroupProjectGroupTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $SANDBOX = "Sandbox";
	static $SHAREDSERVER = "SharedServer";
	static $VPS = "VPS";
}

?>