<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ReqData
{
	public $PID;
	public $TargetProjectPID;
	public $UserRequest;
	public $Summary;
	public $Analyzed;
	public $RequestIsBug;
	public $RequestIsFunc;
	public $RequestIsNonFuncUserbility;
	public $RequestIsNonFuncPerformance;
	public $RequestIsNonFuncReliability;
	public $RequestIsNonFuncSecurity;
	public $RequestIsNonFuncServiceability;
	public $RequestIsNonFuncInteroperability;
	public $RequestIsNonFuncSystemConstraints;
	public $Priority;
	public $status;
	public $AddedDateTime;
	public $UpdatedDateTime;
	public $StakeHolders;
	public $AssignedTo;
	public $ScheduledStartDate;
	public $Deadline;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	function GetTitle()
	{
		if (trim($this->Summary) != "") {
			return trim($this->Summary);
		}
		return trim($this->UserRequest);
	}
	
	function ClearZeroDateTime()
	{
		$ZeroDatePattern = "0000-00-00";
		if ($this->ScheduledStartDate == $ZeroDatePattern) {
			$this->ScheduledStartDate = "";
		}
		if ($this->Deadline == $ZeroDatePattern) {
			$this->Deadline = "";
		}
	}
	
	function CheckIfRequirementStatusIsNotInitial()
	{
		switch($this->status) {
			case "received":
				return false;
			case "declined":
			case "finished":
			case "postponed":
			case "ongoing":
				return true;
		}
		if ($this->status != "") {
			return true;
		}
		return false;
	}
	
	function GetRequirementTypeCaption($glue)
	{
		$ReqCaptionList = array();
		
		if ($this->RequestIsBug == "1") {
			array_push($ReqCaptionList, getres("RequestIsBug"));
		}
		if ($this->RequestIsFunc == "1") {
			array_push($ReqCaptionList, getres("RequestIsFunc"));
		}
		if ($this->RequestIsNonFuncUserbility == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncUserbility"));
		}
		if ($this->RequestIsNonFuncPerformance == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncPerformance"));
		}
		if ($this->RequestIsNonFuncReliability == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncReliability"));
		}
		if ($this->RequestIsNonFuncSecurity == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncSecurity"));
		}
		if ($this->RequestIsNonFuncServiceability == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncServiceability"));
		}
		if ($this->RequestIsNonFuncInteroperability == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncInteroperability"));
		}
		if ($this->RequestIsNonFuncSystemConstraints == "1") {
			array_push($ReqCaptionList, getres("RequestIsNonFuncSystemConstraints"));
		}
		return implode($glue, $ReqCaptionList);
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetRequirementStatusCaption($status)
{
	switch($status) {
		case "received":
			return "Received";
		case "declined":
			return "Declined";
		case "finished":
			return "Finished";
		case "postponed":
			return "Postponed";
		case "ongoing":
			return "Ongoing";
	}
	return $thstatus;
}

function GetReqPriorityCaption($priority)
{
	switch($priority)
	{
		case ReqPriorityEnum::$DEFAULT:
			return "Normal";
		case ReqPriorityEnum::$IMMEDIATE:
			return "Immediate";
		case ReqPriorityEnum::$HIGH:
			return "High";
		case ReqPriorityEnum::$LOW:
			return "Low";
	}
	return $priority;
}
// == END OF EDITABLE AREA FOR BOTTOM ==

class ReqPriorityEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $IMMEDIATE = "immediate";
	static $HIGH = "high";
	static $LOW = "low";
}

class ReqstatusEnum
{
	static $UNKNOWN = "Unknown";
	static $RECEIVED = "received";
	static $DECLINED = "declined";
	static $FINISHED = "finished";
	static $POSTPONED = "postponed";
	static $ONGOING = "ongoing";
}

?>