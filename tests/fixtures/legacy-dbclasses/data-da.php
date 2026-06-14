<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daData
{
	public $ProjectPID;
	public $PID;
	public $name;
	public $StoreBasePath;
	public $IsAutoload;
	public $LastModifiedDT;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function NormalizeIsAutoloadProperty()
	{
		if ($this->IsAutoload == "0" ||
		    $this->IsAutoload == "1") {
			// OK
		} else if ($this->IsAutoload == "") {
			$this->IsAutoload = "0";
		} else {
			$this->IsAutoload = "1";		// Default
		}
	}
	public function GetIsAutoloadBoolean()
	{
		if ($this->IsAutoload == "1") {
			return true;
		}
		return false;
	}
	public function GetIsAutoloadCaption()
	{
		if ($this->GetIsAutoloadBoolean()) {
			return "Yes";
		}
		return "No";
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>