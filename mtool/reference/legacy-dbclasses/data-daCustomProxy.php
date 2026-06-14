<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daCustomProxyData
{
	public $ProjectPID;
	public $PID;
	public $basename;
	public $name;
	public $InTransaction;
	public $AuthType;
	public $SingleGetFuncPID;
	public $LastModifiedDT;
	public $ContinueEvenIfFailedToInsert;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	function IsLoginByLoginCookieToken()
	{
		switch($this->AuthType) {
			case daCustomProxyAuthTypeEnum::$DEFAULT:
			case daCustomProxyAuthTypeEnum::$PROJECTTOKEN:
			case daCustomProxyAuthTypeEnum::$GETFUNC:
			case daCustomProxyAuthTypeEnum::$PROJECTTOKENORGETFUNC:
			case daCustomProxyAuthTypeEnum::$NOSECURITY:
			case daCustomProxyAuthTypeEnum::$MANUAL:
				break;
			case daCustomProxyAuthTypeEnum::$LOGINCOOKIETOKEN:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Auth Type: " . $this->SingleProxy_AuthType . "\n";
		}
		return false;
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

// Memo: daCustomProxyAuthTypeEnum must be same as dafuncSingleProxy_AuthTypeEnum

// == END OF EDITABLE AREA FOR BOTTOM ==

class daCustomProxyAuthTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $PROJECTTOKEN = "ProjectToken";
	static $GETFUNC = "GetFunc";
	static $PROJECTTOKENORGETFUNC = "ProjectTokenOrGetFunc";
	static $NOSECURITY = "NoSecurity";
	static $MANUAL = "Manual";
	static $LOGINCOOKIETOKEN = "LoginCookieToken";
}

?>