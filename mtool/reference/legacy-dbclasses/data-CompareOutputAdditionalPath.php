<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class CompareOutputAdditionalPathData
{
	public $PID;
	public $CompareOutputPID;
	public $ProjectPID;
	public $PathA_DropboxBaseFolderPID;
	public $PathA;
	public $PathB_DropboxBaseFolderPID;
	public $PathB;
	public $IsSameFilenameOnly;
	public $DropboxBaseFolderAName;
	public $DropboxBaseFolderBName;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	public function GetPathAWithoutLastSlush()
	{
		return preg_replace("/\/$/", "", $this->PathA);
	}
	public function GetPathBWithoutLastSlush()
	{
		return preg_replace("/\/$/", "", $this->PathB);
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>