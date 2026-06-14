<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dafuncinserttargetfieldsData
{
	public $ProjectPID;
	public $daPID;
	public $dafuncPID;
	public $PID;
	public $targetTableColumnName;
	public $ParameterType;
	public $ParameterDataType;
	public $FixedParameter;
	public $FieldListOrder;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == dafuncinserttargetfieldsParameterTypeEnum::$FIXED) {
			return $this->FixedParameter;
		}
		return "";
	}
	public function GetParameterDataTypeCaption()
	{
		return GetParameterDataTypeCaptionCommon($this->ParameterDataType);
	}
	public function IsFileDataType()
	{
		return ($this->ParameterDataType == dafuncinserttargetfieldsParameterDataTypeEnum::$FILE);
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

class dafuncinserttargetfieldsParameterTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $ARGUMENT = "argument";
	static $FIXED = "fixed";
}

class dafuncinserttargetfieldsParameterDataTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $RAW = "raw";
	static $FILE = "file";
}

?>