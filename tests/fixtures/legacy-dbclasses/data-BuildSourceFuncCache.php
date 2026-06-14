<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildSourceFuncCacheData
{
	public $PID;
	public $ProjectPID;
	public $CreatedDateTime;
	public $daPID;
	public $dafuncPID;
	public $daCustomProxyPID;
	public $BuildTargetType;
	public $ReleaseTargetType;
	public $FunctionName;
	public $AutoloadFilename;
	public $SourceCode;
	public $ParameterListString;
	public $ParameterListStringForProxyBasedOnDA;
	public $ParameterListStringForProxyBasedOnDAForExample;
	public $ExampleCodeForCreatingObject;
	public $DataClassName;
	public $DAName;
	public $DAClassName;
	public $ProxyURL;
	public $ProxyParameterFormat;
	public $ProxyParameterExample;
	public $ProxyResultFormat;
	public $ProxyResultExample;
	public $ProxyParameterForJquery;
	public $ProxyParameterExampleForJquery;
	public $ProxyParameterExampleForPHP;
	public $ProxyParameterExampleForPerl;
	public $ProxyParameterExampleForRuby;
	public $ProxyResultFormatForJquery;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetAllBuildSourceFuncCacheReleaseTargetTypeEnumList()
{
	return array(
		BuildSourceFuncCacheReleaseTargetTypeEnum::$RELEASE,
		BuildSourceFuncCacheReleaseTargetTypeEnum::$BETA
		);
}
function GetAllBuildSourceFuncCacheReleaseTargetTypeEnumCaption($value)
{
	switch($value)
	{
		case BuildSourceFuncCacheReleaseTargetTypeEnum::$RELEASE:
			return "Release";
		case BuildSourceFuncCacheReleaseTargetTypeEnum::$BETA:
			return "Beta";
		default:
			die("Unknown BuildSourceFuncCacheReleaseTargetTypeEnum: " . $value);
	}
	return $value;
}

function GetBuildSourceFuncCacheReleaseTargetTypeFromProjectSourceOutputReleaseTargetType($value)
{
	switch($value)
	{
		case ProjectSourceOutputReleaseTargetTypeEnum::$RELEASE:
			return BuildSourceFuncCacheReleaseTargetTypeEnum::$RELEASE;
		case ProjectSourceOutputReleaseTargetTypeEnum::$BETA:
			return BuildSourceFuncCacheReleaseTargetTypeEnum::$BETA;
		// default:
		// 	die("Unknown Release Target Type: " . $value);
	}
	return $value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class BuildSourceFuncCacheBuildTargetTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DA = "DA";
	static $PROXYSERVER = "ProxyServer";
	static $CUSTOMPROXYSERVER = "CustomProxyServer";
}

class BuildSourceFuncCacheReleaseTargetTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $RELEASE = "Release";
	static $BETA = "Beta";
}

?>
