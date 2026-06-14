<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncselecthavingData
{
	public $ProjectPID;
	public $daPID;
	public $dafuncPID;
	public $PID;
	public $LeftTargetPrefix;
	public $LeftTargetFieldPID;
	public $LeftTargetSuffix;
	public $RelationalOperator;
	public $RightTargetPrefix;
	public $RightParameterType;
	public $RightParameterDataType;
	public $RightFixedParameter;
	public $RightTargetFieldPID;
	public $RightTargetSuffix;
	public $HavingListOrder;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	public function GetRelationalOperatorSQL()
	{
		return GetRelationalOperatorSQLCommon($this->RelationalOperator);
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
function GetdafuncselecthavingRightParameterTypeCaption($parametertype)
{
	switch($parametertype)
	{
		case dafuncselecthavingRightParameterTypeEnum::$ARGUMENT:
			return "Argument";
		case dafuncselecthavingRightParameterTypeEnum::$FIXED:
			return "Fixed";
		case dafuncselecthavingRightParameterTypeEnum::$FIELD:
			return "Field";
	}
	return $parametertype;
}
function GetdafuncselecthavingRightParameterDataTypeCaption($datatype)
{
	switch($datatype)
	{
		case dafuncselecthavingRightParameterDataTypeEnum::$DEFAULT:
			return "String";
		case dafuncselecthavingRightParameterDataTypeEnum::$RAW;
			return "Raw";
	}
	return $datatype;
}
// == END OF EDITABLE AREA FOR BOTTOM ==

class dafuncselecthavingRightParameterTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $ARGUMENT = "argument";
	static $FIXED = "fixed";
	static $FIELD = "field";
}

class dafuncselecthavingRightParameterDataTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $RAW = "raw";
}

?>