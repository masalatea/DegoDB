<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
$CONTENT_DEPTH_MAX = 7;
$OUTPUT_SECTION_NUMBER_START = 2;
// == END OF EDITABLE AREA FOR ABOVE ==

class SpecContentData
{
	public $ProjectPID;
	public $SpecPID;
	public $PID;
	public $Depth;
	public $ContentOrder;
	public $Title;
	public $Description;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function GetDepthCaption()
	{
		return GetDepthCaptionCommon($this->Depth);
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetDepthCaptionCommon($DepthValue)
{
	switch($DepthValue)
	{
		case "0":
			return "Undefined";
		case "1":
			return "Part";
		case "2":
			return "Chapter";
		case "3":
			return "Section";
		case "4":
			return "Sub Section";
		case "5":
			return "Sub Sub Section";
		case "6":
			return "Paragraph";
		case "7":
			return "Sub Paragraph";
	}
	return "Depth: " . $DepthValue;
}
// == END OF EDITABLE AREA FOR BOTTOM ==

?>