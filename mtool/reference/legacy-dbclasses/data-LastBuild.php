<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LastBuildData
{
	public $PID;
	public $ProjectPID;
	public $ProjectSourceOutputPID;
	public $BuildClassType;
	public $EachTargetPID;
	public $ToTempFolder;
	public $OutputAfterCopyToTempFolder;
	public $LastBuildDT;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

class LastBuildBuildClassTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DATACLASS = "dataclass";
	static $DA = "da";
	static $PROXYSERVER = "proxyserver";
	static $PROXYCLIENT = "proxyclient";
	static $CUSTOMPROXYSERVER = "customproxyserver";
	static $CUSTOMPROXYCLIENT = "customproxyclient";
	static $HTML = "html";
	static $LANGUAGERESOURCE = "LanguageResource";
}

?>