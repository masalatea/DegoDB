<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class minutes_and_RelatedTablesDBAccess
{
	public function __construct() {
	}
	
	public function GetminutesByOwnerOrUserSecurityList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetminutesByOwnerOrUserSecurityList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetminutesByOwnerOrUserSecurityList ==
		
		$last_sql_command_for_mtooldb = "select Project.name, minutes.ProjectPID, minutes.PID, minutes.AddedDateTime, minutes.UpdatedDateTime, minutes.Title, minutes.Overview, minutes.IsBrainstorming, minutes.Brainstorming, minutes.IsDecisionMaking, minutes.DecisionMaking, minutes.IsLearning, minutes.Learning, minutes.chattopicPID, minutes.ReqPID, minutes.SpecPID, minutes.SpecContentPID, minutes.TestGroupPID, minutes.TestPID, minutes.daPID, minutes.dafuncPID, minutes.dataclassPID, minutes.dbtablePID, chattopic.name, Req.UserRequest, Req.Summary, Spec.name, SpecContent.Depth, SpecContent.Title, TestGroup.name, Test.name, da.name, dafunc.name, dafunc.ActionType, dataclass.name, dbtable.name from Project join minutes LEFT OUTER JOIN chattopic ON minutes.chattopicPID = chattopic.PID and minutes.ProjectPID = chattopic.ProjectPID LEFT OUTER JOIN Req ON minutes.ReqPID = Req.PID and minutes.ProjectPID = Req.TargetProjectPID LEFT OUTER JOIN Spec ON minutes.SpecPID = Spec.PID and minutes.ProjectPID = Spec.ProjectPID LEFT OUTER JOIN SpecContent ON minutes.SpecContentPID = SpecContent.PID and minutes.ProjectPID = SpecContent.ProjectPID LEFT OUTER JOIN TestGroup ON minutes.TestGroupPID = TestGroup.PID and minutes.ProjectPID = TestGroup.ProjectPID LEFT OUTER JOIN Test ON minutes.TestPID = Test.PID and minutes.ProjectPID = Test.ProjectPID LEFT OUTER JOIN da ON minutes.daPID = da.PID and minutes.ProjectPID = da.ProjectPID LEFT OUTER JOIN dafunc ON minutes.dafuncPID = dafunc.PID and minutes.ProjectPID = dafunc.ProjectPID LEFT OUTER JOIN dataclass ON minutes.dataclassPID = dataclass.PID and minutes.ProjectPID = dataclass.ProjectPID LEFT OUTER JOIN dbtable ON minutes.dbtablePID = dbtable.PID and minutes.ProjectPID = dbtable.ProjectPID join ProjectUser where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.ProjectPID = Project.PID and ProjectUser.ProjectPID = minutes.ProjectPID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new minutes_and_RelatedTablesData();
			$thisresult->Projectname = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->AddedDateTime = $thisline[3];
			$thisresult->UpdatedDateTime = $thisline[4];
			$thisresult->Title = $thisline[5];
			$thisresult->Overview = $thisline[6];
			$thisresult->IsBrainstorming = $thisline[7];
			$thisresult->Brainstorming = $thisline[8];
			$thisresult->IsDecisionMaking = $thisline[9];
			$thisresult->DecisionMaking = $thisline[10];
			$thisresult->IsLearning = $thisline[11];
			$thisresult->Learning = $thisline[12];
			$thisresult->chattopicPID = $thisline[13];
			$thisresult->ReqPID = $thisline[14];
			$thisresult->SpecPID = $thisline[15];
			$thisresult->SpecContentPID = $thisline[16];
			$thisresult->TestGroupPID = $thisline[17];
			$thisresult->TestPID = $thisline[18];
			$thisresult->daPID = $thisline[19];
			$thisresult->dafuncPID = $thisline[20];
			$thisresult->dataclassPID = $thisline[21];
			$thisresult->dbtablePID = $thisline[22];
			$thisresult->chattopicname = $thisline[23];
			$thisresult->ReqUserRequest = $thisline[24];
			$thisresult->ReqSummary = $thisline[25];
			$thisresult->Specname = $thisline[26];
			$thisresult->SpecContentDepth = $thisline[27];
			$thisresult->SpecContentTitle = $thisline[28];
			$thisresult->TestGroupname = $thisline[29];
			$thisresult->Testname = $thisline[30];
			$thisresult->daname = $thisline[31];
			$thisresult->dafuncname = $thisline[32];
			$thisresult->dafuncActionType = $thisline[33];
			$thisresult->dataclassname = $thisline[34];
			$thisresult->dbtablename = $thisline[35];
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