<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncselectwhereData
{
	public $ProjectPID;
	public $daPID;
	public $dafuncPID;
	public $PID;
	public $targetTableName;
	public $targetTableAliasName;
	public $targetTableColumnName;
	public $ParameterType;
	public $ParameterDataType;
	public $FixedParameter;
	public $AnotherTableName;
	public $AnotherTableAliasName;
	public $AnotherFieldName;
	public $JoinType;
	public $ORGroup;
	public $RelationalOperator;
	public $WhereOrder;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == "fixed") {
			return $this->FixedParameter;
		}
		return "";
	}
	public function GetParameterDataTypeCaptionIfParameterTypeIsNotAnotherField()
	{
		if ($this->ParameterType != "anotherfield") {
			return GetParameterDataTypeCaptionCommon($this->ParameterDataType);
		}
		return "";
	}
	public function GetRelationalOperatorCaption()
	{
		return GetRelationalOperatorCaptionCommon($this->RelationalOperator);
	}
	public function GetRelationalOperatorSQL()
	{
		return GetRelationalOperatorSQLCommon($this->RelationalOperator);
	}

	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetdafuncselectwhereJoinTypeCaption($jointype)
{
	switch($jointype) {
		case "":
			return "Where";
		case dafuncselectwhereJoinTypeEnum::$INNER:
			return "Inner Join";
		case dafuncselectwhereJoinTypeEnum::$LEFT:
			return "Left Outer Join";
		case dafuncselectwhereJoinTypeEnum::$RIGHT:
			return "Right Outer Join";
		case dafuncselectwhereJoinTypeEnum::$HAVING:
			return "Having";
	}
	return $jointype;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class dafuncselectwhereParameterTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $ARGUMENT = "argument";
	static $FIXED = "fixed";
	static $ANOTHERFIELD = "anotherfield";
}

class dafuncselectwhereParameterDataTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $RAW = "raw";
}

class dafuncselectwhereJoinTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $INNER = "inner";
	static $LEFT = "left";
	static $RIGHT = "right";
}

?>