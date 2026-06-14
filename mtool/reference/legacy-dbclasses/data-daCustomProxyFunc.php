<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daCustomProxyFuncData
{
	public $ProjectPID;
	public $daCustomProxyPID;
	public $PID;
	public $dafuncPID;
	public $IsList;
	public $FunctionListOrder;
	public $AddIndentCount;
	public $AddIndentType;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function ForList()
	{
		return ($this->IsList == 1);
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetCustomProxyFuncAddIndentTypeEnumCaption($value)
{
	switch($value)
	{
		case daCustomProxyFuncAddIndentTypeEnum::$DEFAULT:
			return "Start Indent and End Indent";
		case daCustomProxyFuncAddIndentTypeEnum::$START:
			return "Start Indent";
		case daCustomProxyFuncAddIndentTypeEnum::$END:
			return "End Indent";
		case daCustomProxyFuncAddIndentTypeEnum::$CONTINUE:
			return "Continue Indent";
	}
	return $value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class daCustomProxyFuncAddIndentTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $START = "start";
	static $END = "end";
	static $CONTINUE = "continue";
}

?>