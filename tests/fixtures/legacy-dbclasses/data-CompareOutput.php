<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class CompareOutputData
{
	public $PID;
	public $ProjectPID;
	public $DropboxBaseFolderPID;
	public $OutputFilePath;
	public $OutputFileType;
	public $ComparePath;
	public $CompareToolFilePath;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetCompareOutputOutputFileTypeCaption($value)
{
	switch($value)
	{
		case CompareOutputOutputFileTypeEnum::$TEXT:
			return "Text";
		case CompareOutputOutputFileTypeEnum::$WINDOWSBATCH:
			return "Windows Batch";
		case CompareOutputOutputFileTypeEnum::$MACCOMMAND:
			return "Mac Command";
	}
	return $value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class CompareOutputOutputFileTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $TEXT = "Text";
	static $WINDOWSBATCH = "WindowsBatch";
	static $MACCOMMAND = "MacCommand";
}

?>