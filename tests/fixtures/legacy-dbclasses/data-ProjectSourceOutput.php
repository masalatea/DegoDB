<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectSourceOutputData
{
	public $ProjectPID;
	public $PID;
	public $ProgramLanguage;
	public $CustomFileExtention;
	public $ClassType;
	public $ReleaseTargetType;
	public $SourceTemplateDir;
	public $SourceOutputDir;
	public $SourceTempOutputDir;
	public $ProxyBaseURL;
	public $UnitTestTemplateDir;
	public $UnitTestOutputDir;
	public $AutoloadFilenameSuffix;
	public $TargetServerProjectSourceOutputPID;
	public $ProjectSourceOutputListOrder;
	public $SourceTextCharCode;
	public $CSNameSpace;
	public $JavaPackageName;
	public $DropboxBaseFolderPID;
	public $AutoLoadFilePathForPHP;
	public $JavaFunctionType;
	public $DotNetLanguageResourceType;
	public $TargtServerPSOProgramLanguage;
	public $TargtServerPSOCustomFileExtention;
	public $TargtServerPSOProxyBaseURL;
	public $DropboxBaseFolderName;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	function GetOneLineShortCaptionForHtml()
	{
		return $this->SourceOutputDir;		// ひとまずシンプルにパスだけ出力
	}
	function GetOneLineShortCaptionForLanguageResource()
	{
		return $this->SourceOutputDir;		// ひとまずシンプルにパスだけ出力
	}
	
	function IsProxyServer()
	{
		switch($this->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$HTML:
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
				return true;
				
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
		return false;
	}
	function IsProxyClient()
	{
		switch($this->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$HTML:
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
				return true;
				
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
		return false;
	}
	
	function IsDBaaSProxy()
	{
		switch($this->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$HTML:
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
				return true;
				
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
		return false;
	}
	function IsNonDBaaSProxy()
	{
		switch($this->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$HTML:
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
				return true;
				
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
		return false;
	}
	function IsXCode()
	{
		switch($this->ProgramLanguage)
		{
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
			case ProjectSourceOutputProgramLanguageEnum::$CS:
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				return true;
		}
		return false;
	}
	function IsDotNetUWP()
	{
		switch($this->ProgramLanguage)
		{
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				switch($this->DotNetLanguageResourceType)
				{
					case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
						break;
					case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
						return true;
					case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
						break;
				}
				break;
		}
		return false;
	}
	
	function GetCSNameSpaceByConsideringDefault()
	{
		if (trim($this->CSNameSpace) == "") {
			return "DB";
		}
		return $this->CSNameSpace;
	}
	function GetJavaPackageNameByConsideringDefault()
	{
		if (trim($this->JavaPackageName) == "") {
			return "DB";
		}
		return $this->JavaPackageName;
	}
	
	function GetTargtServerPSOProxyBaseURLWithLastSlush()
	{
		$thisurl = $this->TargtServerPSOProxyBaseURL;
		if (!endsWith($thisurl, "/")) {
			$thisurl .= "/";
		}
		return $thisurl;
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetProjectSourceOutputProgramLanguageCaption($lang)
{
	switch($lang)
	{
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "PHP";
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			return "C#";
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			return "Java";
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			return "Objective-C Header";
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			return "Objective-C Implementation";
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return "SWIFT";
	}
	return $lang;
}
function GetProjectSourceOutputClassTypeCaption($classtype)
{
	switch($classtype)
	{
		case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			return "Database Access";
		case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			return "Proxy Server";
		case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			return "Proxy Client";
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			return "Proxy Server for DBaaS";
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			return "Proxy Client for DBaaS";
		case ProjectSourceOutputClassTypeEnum::$HTML:
			return "Html";
		case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
			return "Language Resource";
	}
	return $classtype;
}

function GetProjectSourceOutputReleaseTargetTypeCaption($value)
{
	switch($value)
	{
		case ProjectSourceOutputReleaseTargetTypeEnum::$RELEASE:
			return "Release";
		case ProjectSourceOutputReleaseTargetTypeEnum::$BETA:
			return "Beta";
		// default:
		//	die("Unknown Release Target Type: " . $value);
	}
	return $value;
}

function GetProjectSourceOutputJavaFunctionTypeCaption($value)
{
	switch($value)
	{
		case ProjectSourceOutputJavaFunctionTypeEnum::$DEFAULT:
			return "Default (Both)";
		case ProjectSourceOutputJavaFunctionTypeEnum::$BOTH:
			return "Both";
		case ProjectSourceOutputJavaFunctionTypeEnum::$ANDROIDASYNCTASKLOADERONLY;
			return "Android Axync Task Loader Only";
		case ProjectSourceOutputJavaFunctionTypeEnum::$DIRECTONLY:
			return "Direct Only";
	}
	return $value;
}

function GetProjectSourceOutputDotNetLanguageResourceTypeCaption($value)
{
	switch($value)
	{
		case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
			return "Default";
		case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
			return "Universal Windows Platform(UWP)";
		case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
			return "By Code";
	}
	return $value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class ProjectSourceOutputProgramLanguageEnum
{
	static $UNKNOWN = "Unknown";
	static $PHP = "php";
	static $CS = "cs";
	static $JAVA = "java";
	static $OBJECTIVECH = "objectivech";
	static $OBJECTIVECM = "objectivecm";
	static $SWIFT = "swift";
}

class ProjectSourceOutputClassTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DBACCESS = "DBAccess";
	static $PROXYSERVER = "ProxyServer";
	static $PROXYCLIENT = "ProxyClient";
	static $DBAASPROXYSERVER = "DBaaSProxyServer";
	static $DBAASPROXYCLIENT = "DBaaSProxyClient";
	static $HTML = "html";
	static $LANGUAGERESOURCE = "LanguageResource";
}

class ProjectSourceOutputReleaseTargetTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $RELEASE = "Release";
	static $BETA = "Beta";
}

class ProjectSourceOutputJavaFunctionTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $BOTH = "Both";
	static $ANDROIDASYNCTASKLOADERONLY = "AndroidAsyncTaskLoaderOnly";
	static $DIRECTONLY = "DirectOnly";
}

class ProjectSourceOutputDotNetLanguageResourceTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $UWP = "UWP";
	static $BYCODE = "ByCode";
}

?>
