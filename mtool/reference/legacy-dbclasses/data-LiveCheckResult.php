<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LiveCheckResultData
{
	public $PID;
	public $CheckDT;
	public $CheckTargetURL;
	public $CheckOriginServerName;
	public $LiveCheckType;
	public $LiveCheckResult;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

class LiveCheckResultLiveCheckTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $WEB = "web";
	static $WEBANDDB = "webanddb";
}

class LiveCheckResultLiveCheckResultEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $OK = "OK";
	static $NG = "NG";
}

?>