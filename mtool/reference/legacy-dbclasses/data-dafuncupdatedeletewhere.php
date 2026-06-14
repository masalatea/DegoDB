<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncupdatedeletewhereData
{
	public $ProjectPID;
	public $daPID;
	public $dafuncPID;
	public $PID;
	public $targetTableColumnName;
	public $ParameterType;
	public $ParameterDataType;
	public $FixedParameter;
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
	public function GetParameterDataTypeCaption()
	{
		return GetParameterDataTypeCaptionCommon($this->ParameterDataType);
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
// == END OF EDITABLE AREA FOR BOTTOM ==

class dafuncupdatedeletewhereParameterTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $ARGUMENT = "argument";
	static $FIXED = "fixed";
}

class dafuncupdatedeletewhereParameterDataTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $RAW = "raw";
}

?>