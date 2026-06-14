<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceData
{
	public $PID;
	public $ProjectPID;
	public $LanguageResourceGroupPID;
	public $KeyForUpdate;
	public $SortGroup;
	public $KeyName;
	public $KeyNameForXcode;
	public $UWPTargetProperty;
	public $IsResourceFixed;
	public $UseDefaultIfCaptionIsBlank;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function GetUWPTargetPropertyWithDot()
	{
		if (trim($this->UWPTargetProperty) != "") {
			if (!preg_match("/^\./", $this->UWPTargetProperty)) {
				// Add Dot for Property
				return "." . $this->UWPTargetProperty;
			}
		}
		return $this->UWPTargetProperty;
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>