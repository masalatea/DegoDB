<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectGroupTemplateData
{
	public $PID;
	public $ProjectGroupType;
	public $ProjectGroupNamePrefix;
	public $SettingGroupPID;
	public $MainServerPID;
	public $ServerPID;
	public $DropboxSettingPID;
	public $ApacheHostSettingTemplatePID;
	public $DropboxBaseDir;
	public $LocalBaseDir;
	public $ProxyBaseURL;
	public $UploaderURLSuffix;
	public $DBManagerURLSuffix;
	public $proxy_header_of_access_control_allow_origin;
	public $proxy_header_of_access_control_allow_headers;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetProjectGroupTemplateProjectGroupTypeCaption($value)
{
	switch($value)
	{
		case ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX:
			return "Sandbox";
		case ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER:
			return "Shared Server";
		case ProjectGroupTemplateProjectGroupTypeEnum::$VPS:
			return "VPS";
		default:
			die("Unknown Value: " . $value);
	}
	return $value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class ProjectGroupTemplateProjectGroupTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $SANDBOX = "Sandbox";
	static $SHAREDSERVER = "SharedServer";
	static $VPS = "VPS";
}

?>