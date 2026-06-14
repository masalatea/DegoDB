<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlTemplateParameterData
{
	public $htmlTemplatePID;
	public $PID;
	public $ParameterName;
	public $TargetValueType;
	public $TargetVariableOrClassObject;
	public $TargetPropertyOfClassObject;
	public $AnotherTemplatePID;
	public $TrimLastSpace;
	public $TrimLastReturn;
	public $DataType;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function GetTargetVariable()
	{
		$variable = '$' . trim($this->TargetVariableOrClassObject);
		if (trim($this->TargetPropertyOfClassObject) != "") {
			$variable .= "->" . trim($this->TargetPropertyOfClassObject);
		}
		return $variable;
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GethtmlTemplateParameterTargetValueTypeCaption($targetvalue)
{
	switch($targetvalue)
	{
		case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
			return "Each HTML (for each Project)";
		case htmlTemplateParameterTargetValueTypeEnum::$CODE:
			return "Code (common for all Project)";
		case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
			return "Another Template";
	}
	return $targetvalue;
}

function GethtmlTemplateParameterDataTypeCaption($datatype)
{
	switch($datatype)
	{
		case htmlTemplateParameterDataTypeEnum::$DEFAULT:
			return "String";
		case htmlTemplateParameterDataTypeEnum::$DATACLASSNAME:
			return "Data Class";
		case htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME:
			return "DB Access Class";
	}
	return $datatype;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class htmlTemplateParameterTargetValueTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $EACHHTML = "EachHTML";
	static $CODE = "code";
	static $ANOTHERTEMPLATE = "AnotherTemplate";
}

class htmlTemplateParameterDataTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $DATACLASSNAME = "dataclassname";
	static $DBACCESSCLASSNAME = "dbaccessclassname";
}

?>