<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class Req_and_ProjectDBAccess
{
	public function __construct() {
	}
	
	public function GetReqbyOwnerOrUserSecurityList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetReqbyOwnerOrUserSecurityList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetReqbyOwnerOrUserSecurityList ==
		
		$last_sql_command_for_mtooldb = "select Req.PID, Req.TargetProjectPID, Req.UserRequest, Req.Summary, Req.Analyzed, Req.RequestIsBug, Req.RequestIsFunc, Req.RequestIsNonFuncUserbility, Req.RequestIsNonFuncPerformance, Req.RequestIsNonFuncReliability, Req.RequestIsNonFuncSecurity, Req.RequestIsNonFuncServiceability, Req.RequestIsNonFuncInteroperability, Req.RequestIsNonFuncSystemConstraints, Req.Priority, Req.status, Req.AddedDateTime, Req.UpdatedDateTime, Req.StakeHolders, Req.AssignedTo, Req.ScheduledStartDate, Req.Deadline, Project.name from Req LEFT OUTER JOIN Project ON Req.TargetProjectPID = Project.PID join ProjectUser where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.ProjectPID = Req.TargetProjectPID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new Req_and_ProjectData();
			$thisresult->PID = $thisline[0];
			$thisresult->TargetProjectPID = $thisline[1];
			$thisresult->UserRequest = $thisline[2];
			$thisresult->Summary = $thisline[3];
			$thisresult->Analyzed = $thisline[4];
			$thisresult->RequestIsBug = $thisline[5];
			$thisresult->RequestIsFunc = $thisline[6];
			$thisresult->RequestIsNonFuncUserbility = $thisline[7];
			$thisresult->RequestIsNonFuncPerformance = $thisline[8];
			$thisresult->RequestIsNonFuncReliability = $thisline[9];
			$thisresult->RequestIsNonFuncSecurity = $thisline[10];
			$thisresult->RequestIsNonFuncServiceability = $thisline[11];
			$thisresult->RequestIsNonFuncInteroperability = $thisline[12];
			$thisresult->RequestIsNonFuncSystemConstraints = $thisline[13];
			$thisresult->Priority = $thisline[14];
			$thisresult->status = $thisline[15];
			$thisresult->AddedDateTime = $thisline[16];
			$thisresult->UpdatedDateTime = $thisline[17];
			$thisresult->StakeHolders = $thisline[18];
			$thisresult->AssignedTo = $thisline[19];
			$thisresult->ScheduledStartDate = $thisline[20];
			$thisresult->Deadline = $thisline[21];
			$thisresult->Projectname = $thisline[22];
			array_push($result, $thisresult);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>