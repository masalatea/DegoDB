<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ReqDBAccess
{
	public function __construct() {
	}
	
	public function GetReqList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetReqList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetReqList ==
		
		$last_sql_command_for_mtooldb = "select Req.PID, Req.TargetProjectPID, Req.UserRequest, Req.Summary, Req.Analyzed, Req.RequestIsBug, Req.RequestIsFunc, Req.RequestIsNonFuncUserbility, Req.RequestIsNonFuncPerformance, Req.RequestIsNonFuncReliability, Req.RequestIsNonFuncSecurity, Req.RequestIsNonFuncServiceability, Req.RequestIsNonFuncInteroperability, Req.RequestIsNonFuncSystemConstraints, Req.Priority, Req.status, Req.AddedDateTime, Req.UpdatedDateTime, Req.StakeHolders, Req.AssignedTo, Req.ScheduledStartDate, Req.Deadline from Req order by Req.UpdatedDateTime desc,Req.AddedDateTime desc";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ReqData();
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
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetReqOfTargetProjectList($param_Req_TargetProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetReqOfTargetProjectList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetReqOfTargetProjectList ==
		
		$last_sql_command_for_mtooldb = "select Req.PID, Req.TargetProjectPID, Req.UserRequest, Req.Summary, Req.Analyzed, Req.RequestIsBug, Req.RequestIsFunc, Req.RequestIsNonFuncUserbility, Req.RequestIsNonFuncPerformance, Req.RequestIsNonFuncReliability, Req.RequestIsNonFuncSecurity, Req.RequestIsNonFuncServiceability, Req.RequestIsNonFuncInteroperability, Req.RequestIsNonFuncSystemConstraints, Req.Priority, Req.status, Req.AddedDateTime, Req.UpdatedDateTime, Req.StakeHolders, Req.AssignedTo, Req.ScheduledStartDate, Req.Deadline from Req where Req.TargetProjectPID = '" . $mtooldb->real_escape_string($param_Req_TargetProjectPID_where) . "' order by Req.UpdatedDateTime desc,Req.AddedDateTime desc";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ReqData();
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
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetReq($param_Req_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetReq ==
		// == END OF EDITABLE AREA FOR FUNCTION GetReq ==
		
		$last_sql_command_for_mtooldb = "select Req.PID, Req.TargetProjectPID, Req.UserRequest, Req.Summary, Req.Analyzed, Req.RequestIsBug, Req.RequestIsFunc, Req.RequestIsNonFuncUserbility, Req.RequestIsNonFuncPerformance, Req.RequestIsNonFuncReliability, Req.RequestIsNonFuncSecurity, Req.RequestIsNonFuncServiceability, Req.RequestIsNonFuncInteroperability, Req.RequestIsNonFuncSystemConstraints, Req.Priority, Req.status, Req.AddedDateTime, Req.UpdatedDateTime, Req.StakeHolders, Req.AssignedTo, Req.ScheduledStartDate, Req.Deadline from Req where Req.PID = '" . $mtooldb->real_escape_string($param_Req_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ReqData();
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
			return $thisresult;
		}
		return NULL;
	}
	public function InsertReq($ReqObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertReq ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertReq ==
		
		$last_sql_command_for_mtooldb = "insert into Req (TargetProjectPID, UserRequest, Summary, Analyzed, RequestIsBug, RequestIsFunc, RequestIsNonFuncUserbility, RequestIsNonFuncPerformance, RequestIsNonFuncReliability, RequestIsNonFuncSecurity, RequestIsNonFuncServiceability, RequestIsNonFuncInteroperability, RequestIsNonFuncSystemConstraints, Priority, status, AddedDateTime, UpdatedDateTime, StakeHolders, AssignedTo, ScheduledStartDate, Deadline) values('" . $mtooldb->real_escape_string($ReqObj->TargetProjectPID) . "', '" . $mtooldb->real_escape_string($ReqObj->UserRequest) . "', '" . $mtooldb->real_escape_string($ReqObj->Summary) . "', '" . $mtooldb->real_escape_string($ReqObj->Analyzed) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsBug) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsFunc) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncUserbility) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncPerformance) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncReliability) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncSecurity) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncServiceability) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncInteroperability) . "', '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncSystemConstraints) . "', '" . $mtooldb->real_escape_string($ReqObj->Priority) . "', '" . $mtooldb->real_escape_string($ReqObj->status) . "', '" . $mtooldb->real_escape_string($ReqObj->AddedDateTime) . "', '" . $mtooldb->real_escape_string($ReqObj->UpdatedDateTime) . "', '" . $mtooldb->real_escape_string($ReqObj->StakeHolders) . "', '" . $mtooldb->real_escape_string($ReqObj->AssignedTo) . "', '" . $mtooldb->real_escape_string($ReqObj->ScheduledStartDate) . "', '" . $mtooldb->real_escape_string($ReqObj->Deadline) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateReq($ReqObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateReq ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateReq ==
		
		$last_sql_command_for_mtooldb = "update Req SET TargetProjectPID = '" . $mtooldb->real_escape_string($ReqObj->TargetProjectPID) . "', UserRequest = '" . $mtooldb->real_escape_string($ReqObj->UserRequest) . "', Summary = '" . $mtooldb->real_escape_string($ReqObj->Summary) . "', Analyzed = '" . $mtooldb->real_escape_string($ReqObj->Analyzed) . "', RequestIsBug = '" . $mtooldb->real_escape_string($ReqObj->RequestIsBug) . "', RequestIsFunc = '" . $mtooldb->real_escape_string($ReqObj->RequestIsFunc) . "', RequestIsNonFuncUserbility = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncUserbility) . "', RequestIsNonFuncPerformance = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncPerformance) . "', RequestIsNonFuncReliability = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncReliability) . "', RequestIsNonFuncSecurity = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncSecurity) . "', RequestIsNonFuncServiceability = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncServiceability) . "', RequestIsNonFuncInteroperability = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncInteroperability) . "', RequestIsNonFuncSystemConstraints = '" . $mtooldb->real_escape_string($ReqObj->RequestIsNonFuncSystemConstraints) . "', Priority = '" . $mtooldb->real_escape_string($ReqObj->Priority) . "', status = '" . $mtooldb->real_escape_string($ReqObj->status) . "', UpdatedDateTime = '" . $mtooldb->real_escape_string($ReqObj->UpdatedDateTime) . "', StakeHolders = '" . $mtooldb->real_escape_string($ReqObj->StakeHolders) . "', AssignedTo = '" . $mtooldb->real_escape_string($ReqObj->AssignedTo) . "', ScheduledStartDate = '" . $mtooldb->real_escape_string($ReqObj->ScheduledStartDate) . "', Deadline = '" . $mtooldb->real_escape_string($ReqObj->Deadline) . "' where Req.PID = '" . $mtooldb->real_escape_string($ReqObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteReq($param_Req_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteReq ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteReq ==
		
		$last_sql_command_for_mtooldb = "delete from Req where Req.PID = '" . $mtooldb->real_escape_string($param_Req_PID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>