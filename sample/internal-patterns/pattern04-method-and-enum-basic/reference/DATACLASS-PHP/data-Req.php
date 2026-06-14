<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-ReqBase.php';

class ReqData extends ReqDataBase
{
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
}
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

?>