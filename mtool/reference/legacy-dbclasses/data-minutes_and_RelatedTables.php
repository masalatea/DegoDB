<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class minutes_and_RelatedTablesData extends minutesData
{
	public $Projectname;
	public $chattopicname;
	public $ReqUserRequest;
	public $ReqSummary;
	public $Specname;
	public $SpecContentDepth;
	public $SpecContentTitle;
	public $TestGroupname;
	public $Testname;
	public $daname;
	public $dafuncname;
	public $dafuncActionType;
	public $dataclassname;
	public $dbtablename;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	function GetRequirementTitle()
	{
		if (trim($this->ReqSummary) != "") {
			return trim($this->ReqSummary);
		}
		return trim($this->ReqUserRequest);
	}
	
	function GetdafuncFunctionName()
	{
		return GetFunctionNameFromFunctionActionType($this->dafuncname, $this->dafuncActionType);
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>