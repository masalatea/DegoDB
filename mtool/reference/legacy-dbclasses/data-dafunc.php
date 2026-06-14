<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncData
{
	public $ProjectPID;
	public $daPID;
	public $PID;
	public $name;
	public $ActionType;
	public $InsertUpdateDeleteTargetTable;
	public $InsertUpdateDeleteParamType;
	public $SelectByDistinct;
	public $SortOrderColumns;
	public $DataClassBaseNameForSelectAction;
	public $FunctionListOrder;
	public $memo;
	public $limitParameterType;
	public $limitFixedParameter;
	public $ORGroupType;
	public $SingleProxy_AuthType;
	public $SingleProxy_SingleGetFuncPID;
	public $IsBlobTarget;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	function IsInsertUpdateDeleteTargetVal()
	{
		return ($this->InsertUpdateDeleteParamType == dafuncInsertUpdateDeleteParamTypeEnum::$VAL);
	}
	function IsInsertUpdateDeleteTargetClassObject()
	{
		return ($this->InsertUpdateDeleteParamType == dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT);
	}
	function IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()
	{
		return ($this->InsertUpdateDeleteParamType == dafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE);
	}
	
	function GetBaseDataClassName()
	{
		switch($this->ActionType) {
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
				if ($this->DataClassBaseNameForSelectAction != "") {
					return $this->DataClassBaseNameForSelectAction;
				} else if ($this->name != "") {
					return $this->name;
				}
				break;
			case dafuncActionTypeEnum::$INSERT:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				if ($this->InsertUpdateDeleteTargetTable != "") {
					return $this->InsertUpdateDeleteTargetTable;
				} else if ($this->name != "") {
					// This is default
					return $this->name;
				}
				break;
			default:
				print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
				break;
		}
		return "";
	}
	function GetInsertUpdateDeleteTargetTable()
	{
		switch($this->ActionType) {
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
				break;
			case dafuncActionTypeEnum::$INSERT:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				if ($this->InsertUpdateDeleteTargetTable != "") {
					return $this->InsertUpdateDeleteTargetTable;
				} else if ($this->name != "") {
					// This is default
					return $this->name;
				}
				break;
			default:
				print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
				break;
		}
		return "";
	}
	
	function IsInsertFunction()
	{
		switch($this->ActionType) {
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			case dafuncActionTypeEnum::$INSERT:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
				break;
		}
		return false;
	}
	
	function IsLoginByLoginCookieToken()
	{
		switch($this->SingleProxy_AuthType) {
			case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
			case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
			case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
			case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
			case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
				break;
			case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Auth Type: " . $this->SingleProxy_AuthType . "\n";
		}
		return false;
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetdafuncInsertUpdateDeleteParamTypeCaption($paramType)
{
	switch($paramType)
	{
		case dafuncInsertUpdateDeleteParamTypeEnum::$DEFAULT:
			return "Not Selected";
		case dafuncInsertUpdateDeleteParamTypeEnum::$VAL:
			return "Value";
		case dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT:
			return "Class Object";
		case dafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE:
			return "Class Object for Set Target / Value for Where";
	}
	return $paramType;
}

function GetdafuncORGroupTypeCaption($paramType)
{
	switch($paramType)
	{
		case dafuncORGroupTypeEnum::$DEFAULT:
			return "Not Selected";
		case dafuncORGroupTypeEnum::$ORANDOR:
			return "(.. or ..) and (.. or ..)";
		case dafuncORGroupTypeEnum::$ANDORAND:
			return "(.. and ..) or (.. and ..)";
	}
	return $paramType;
}
function GetWorkingORGroupType($paramType)
{
	switch($paramType)
	{
		case dafuncORGroupTypeEnum::$DEFAULT:
			return dafuncORGroupTypeEnum::$ORANDOR;
		case dafuncORGroupTypeEnum::$ORANDOR:
			return $paramType;
		case dafuncORGroupTypeEnum::$ANDORAND:
			return $paramType;
	}
	return $paramType;
}

function GetDAFuncActionTypeCaption($ActionType)
{
	switch($ActionType) {
		case dafuncActionTypeEnum::$SELECTSINGLE:
			return "Select(Single)";
		case dafuncActionTypeEnum::$SELECTLIST:
			return "Select(List)";
		case dafuncActionTypeEnum::$INSERT:
			return "Insert";
		case dafuncActionTypeEnum::$UPDATE:
			return "Update";
		case dafuncActionTypeEnum::$DELETE:
			return "Delete";
		default:
			print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
			return "";
	}
}

function GetSingleProxyAuthTypeCaption($value)
{
	switch($value) {
		case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
			return "Default";
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
			return "Project's Token (default)";
		case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
			return "Get Function";
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			return "Project's Token or Get Function";
		case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
			return "No Security";
		case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
			return "Manual";
		case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
			return "Login Cookie Token";
		default:
			print "INTERNAL ERROR! Unknown Auth Type: " . $value . "\n";
			return "";
	}
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class dafuncActionTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $SELECTSINGLE = "selectsingle";
	static $SELECTLIST = "selectlist";
	static $INSERT = "insert";
	static $UPDATE = "update";
	static $DELETE = "delete";
}

class dafuncInsertUpdateDeleteParamTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $VAL = "val";
	static $CLASSOBJECT = "classobject";
	static $SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE = "SetByClassObjectAndWhereByValForUpdate";
}

class dafunclimitParameterTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $ARGUMENT = "argument";
	static $FIXED = "fixed";
}

class dafuncORGroupTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $ORANDOR = "orandor";
	static $ANDORAND = "andorand";
}

class dafuncSingleProxy_AuthTypeEnum
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